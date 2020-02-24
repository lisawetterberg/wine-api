<?php
include '../db_connection/DatabaseConnection.php';

// --------- Config
//ini_set('memory_limit', '4000M');

class Main
{

    //-------------------------------
    // Constants
    //-------------------------------
    /**
     * Path to Systembolagets XML assortment API
     */
    const FILE_PATH = 'https://www.systembolaget.se/api/assortment/products/xml';
        
    //-------------------------------
    // Properties
    //-------------------------------
    private $data = null;
    private $DB = null;
    private $countries = array();
    private $regions = array();
    private $producers = array();
    private $suppliers = array();
    private $articleTypes = array();


    //-------------------------------
    // Constructor
    //-------------------------------
    public function __construct()
    {
        $this->data = $this->getData();
        $this->DB = new DatabaseConnection();

        /**
        * Initiate systemarrays
        */
        $this->countries = $this->getArray("country");
        $this->regions = $this->getArray("region");
        $this->producers = $this->getArray("producer");
        $this->suppliers = $this->getArray("supplier");
        $this->articleTypes = $this->getArray("article_type");
    }

    //-------------------------------
    // Public methods
    //-------------------------------

    public function updateDatabase()
    {
        $date = date('Y-m-d H:i:s');
        if ($this->DB->connectionIsValid() == true) {
            $this->discontinueAll();
      

            foreach ($this->data->artikel as $item) {
                if (!isset($item->Varugrupp)) {
                    continue;
                }

                if ($this->checkIfWine($item->Varugrupp)) {
                    $this->insertToDatabase($item, $date);
                }
            }
        }

        return 'db insert complete';
    }

    //-------------------------------
    // Private methods
    //-------------------------------

    private function getArray($table)
    {
        $rows = array();
        $query = "
  				SELECT 
  					*
  				FROM
  					$table
  				";

        $result = $this->DB->getResult($query);

      
        while ($row = $result->fetch_assoc()) {
            $rows[ $row["id"] ] = $row["name"];
        }
    
        return $rows;
    }

    private function discontinueAll()
    {
        $query = "
        UPDATE 
          wine
        SET 
          discontinued = 1;
      ";

        return $this->DB->getResult($query);
    }

    private function getData()
    {
        $get = file_get_contents(self::FILE_PATH);
        $xml = simplexml_load_string($get);

        $json = json_encode($xml);
        $data = json_decode($json);

        return $data;
    }


    private function checkIfWine($type)
    {
        if (preg_match("/\bvin\b/i", $type)) {
            return true;
        } elseif (preg_match("/Aperitif och dessert/i", $type)) {
            return true;
        } else {
            return false;
        }
    }

    private function insertToDatabase($wine, $date)
    {
        $discontinued = 0;
        $country_id = 1;
        $producer_id = 1;
        $supplier_id = 1;
        $region_id = 1;
        $article_type_id = 1;

        $format = "
  			INSERT INTO 
  				wine (nr, article_number, name, second_name, price_inc_tax,
  				price_per_litre, volume_ml, type, style, packaging, vintage, alcohol,
  				organic, ethical, kosher, raw_material, discontinued, 
  				country_id, region_id, producer_id, supplier_id, article_type_id, inserted_at)
  			VALUES (
  				%d,     /* nr */
  				%d,     /* Artikelid */
  				'%s',   /* Namn */
  				'%s',   /* Namn2 */
  				%d,     /* Prisinklmoms */
  				%d,     /* Prisperliter */
  				%d,     /* Volymiml */
  				'%s',   /* Typ */
  				'%s',   /* Stil */
  				'%s',   /* Förpackning */
  				%s,     /* Årgång */
  				'%s',   /* Alkoholhalt */
  				%d,     /* Ekologisk */
  				%d,     /* Etiskt */
  				%d,     /* Koscher */
  				'%s',   /* RåvarorBeskrivning */
  				%d,     /* Utgått (egen vaiabel) */
  				%s,     /* Ursprunglandnamn */      /* Sträng/digit utan fnuttar */
  				%s,     /* Ursprung (Region) */
  				%s,     /* Producent */
  				%s,     /* Leverantör */
  				%s,     /* Varugrupp */
          '$date' /* Timestamp */
  				)
  			ON DUPLICATE KEY UPDATE 
  				nr = VALUES(nr), 
  				article_number = VALUES(article_number), 
  				name = VALUES(name), 
  				second_name = VALUES(second_name), 
  				price_inc_tax = VALUES(price_inc_tax),
  				price_per_litre = VALUES(price_per_litre), 
  				volume_ml = VALUES(volume_ml), 
  				type = VALUES(type), 
  				style = VALUES(style), 
  				packaging = VALUES(packaging), 
  				vintage = VALUES(vintage), 
  				alcohol = VALUES(alcohol),
  				organic = VALUES(organic), 
  				ethical = VALUES(ethical), 
  				kosher = VALUES(kosher), 
  				raw_material = VALUES(raw_material), 
  				discontinued = VALUES(discontinued), 
  				country_id = VALUES(country_id), 
  				region_id = VALUES(region_id), 
  				producer_id = VALUES(producer_id), 
  				supplier_id = VALUES(supplier_id), 
  				article_type_id = VALUES(article_type_id);";

        $query = sprintf(
            $format,
            $wine->nr,
            $wine->Artikelid,
            $this->escapeString($wine->Namn),
            $this->escapeString($wine->Namn2),
            $wine->Prisinklmoms,
            $wine->PrisPerLiter,
            $wine->Volymiml,
            $this->escapeString($wine->Typ),
            $this->escapeString($wine->Stil),
            $this->escapeString($wine->Forpackning),
            $this->checkIfNull($wine->Argang),
            $wine->Alkoholhalt,
            $wine->Ekologisk,
            $wine->Etiskt,
            $wine->Koscher,
            isset($wine->RavarorBeskrivning) ? $this->escapeString($wine->RavarorBeskrivning) : "",
            0,
            $this->getId($wine->Ursprunglandnamn, $this->countries, "country"),
            $this->getId($wine->Ursprung, $this->regions, "region"),
            isset($wine->Producent) ? $this->getId($wine->Producent, $this->producers, "producer") : "NULL",
            $this->getId($wine->Leverantor, $this->suppliers, "supplier"),
            $this->getId($wine->Varugrupp, $this->articleTypes, "article_type")
        );

        $result = $this->DB->getResult($query);

        //TODO: Felmeddelande?
        if (!$result) {
            printf("%s\n", $this->DB->mysqli->error);
            exit();
        }
    }

    private function checkIfNull($item)
    {
        return is_string($item) ? "'$item'" : "NULL";
    }

    private function escapeString($value)
    {
        if (is_string($value)) {
            return $this->DB->mysqli->real_escape_string($value);
        }
    }

    private function getId($value, &$systemvars, $tablename)
    {
        if (!is_string($value)) {
            return "NULL";
        }

        if (($i = array_search($value, $systemvars, true)) !== false) {
            return $i;
        } else {
            $val = $this->DB->mysqli->real_escape_string($value);
            $query = "
  			INSERT INTO
  				$tablename
  			VALUES (NULL, '$val')
  			";

            $result = $this->DB->getResult($query);
            $systemvars[$this->DB->mysqli->insert_id] = $value;
            return $this->DB->mysqli->insert_id;
        }
    }
}
