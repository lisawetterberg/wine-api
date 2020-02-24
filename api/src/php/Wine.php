<?php 
include './../db_connection/DatabaseConnection.php';

class Wine {

  	private $DB;

	public function __construct() {

    		$this->DB = new DatabaseConnection();
	}

	public function find($options) {

		$where_str = '';
		
		$where_str = $this->getWhereString($options);
		$order_sort_str = $this->getOrdernSortStr($options->sort_in, $options->order_by);
		$limit_str = $this->getLimitStr($options->limit);

		$query = "
			SELECT 
				w.nr, w.name, w.second_name, w.price_inc_tax, w.price_per_litre, w.volume_ml,
				w.type, w.style, w.packaging, w.vintage, w.alcohol, w.organic, w.ethical, w.kosher,
				w.raw_material, w.discontinued, c.name as country, r.name as region, 
				p.name as producer, s.name as supplier, a.name as article_type, w.inserted_at as added_to_database
			FROM 
				wine w

			left outer join country c on w.country_id = c.id 
			left outer join region r on w.region_id = r.id 
			left outer join producer p on w.producer_id = p.id 
			left outer join supplier s on w.supplier_id = s.id 
			left outer join article_type a on w.article_type_id = a.id 
			
			$where_str

			$order_sort_str

			$limit_str
		";

		//print_r($query);


		$output = array();
		$result = $this->DB->getResult($query);

		if($result !== null) {
			while ($wine = $result->fetch_object()) {
			array_push($output, $wine);
			}
			return $output;
		}

		else {
			return null;
		}
	}	

	private function getWhereString($options) {
		$name = $options->name;
		$price = $options->price_inc_tax;
		$volume_ml = $options->volume_ml;
		$type = $options->type;
		$style = $options->style;
		$packaging = $options->packaging;
		$vintage = $options->vintage;
		$alcohol = $options->alcohol;
		$organic = $options->organic;
		$ethical = $options->ethical;
		$kosher = $options->kosher;
		$country = $options->country;
		$region = $options->region;
		$producer = $options->producer;
		$supplier = $options->supplier;
		$article_type = $options->article_type;
		$search = $options->search;


		$where_str = '';
		$where_arr = array();

		/**
		*	Name
		*/
		if (!empty($name)) {
			if (strpos($name, ",")) {
				$names = explode(",", $name);

				$str = "";
				$len = count($names);

				for($i = 0; $i < $len; $i++) {
					$str .= "w.name LIKE '%$names[$i]%' OR w.second_name LIKE '%$names[$i]%' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "w.name LIKE '%$name%' OR w.second_name LIKE '%$name%'");
		}
		/**
		*	Price
		*/
		if (!empty($price)) {
			if (strpos($price, "-")){
				$prices = explode("-", $price);
				array_push($where_arr, "w.price_inc_tax BETWEEN $prices[0] AND $prices[1]");
			}
			else array_push($where_arr, "w.price_inc_tax = $price");
		}
		/**
		*	Volume
		*/
		if (!empty($volume_ml)) {
			if (strpos($volume_ml, "-")) {
				$volumes = explode("-", $volume_ml);
				array_push($where_arr, "w.volume_ml BETWEEN $volumes[0] AND $volumes[1]");
			}
			else array_push($where_arr, "w.volume_ml = '$volume_ml'");
		}
		/**
		*	Type
		*/
		if (!empty($type)) {
			$type = str_replace("_", " & ", $type);
			if (strpos($type, ",")) {
				$types = explode(",", $type);

				$str = "";
				$len = count($types);

				for($i = 0; $i < $len; $i++) {
					$str .= "w.type = '$types[$i]' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "w.type = '$type'");
		}
		/**
		*	Style
		*/
		if (!empty($style)) {
			$style = str_replace("_", " & ", $style);
			if (strpos($style, ",")) {
				$styles = explode(",", $style);

				$str = "";
				$len = count($styles);

				for($i = 0; $i < $len; $i++) {
					$str .= "w.style = '$styles[$i]' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "w.style = '$style'");
		}
		/**
		*	Packaging
		*/
		if (!empty($packaging)) {
			$packaging = str_replace("_", " + ", $packaging);
			if (strpos($packaging, ",")) {
				$packagings = explode(",", $packaging);

				$str = "";
				$len = count($packagings);

				for($i = 0; $i < $len; $i++) {
					$str .= "w.packaging = '$packagings[$i]' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "w.packaging = '$packaging'");
		}
		/**
		*	Vintage
		*/
		if (!empty($vintage)) {
			if (strpos($vintage, "-")) {
				$vintages = explode("-", $vintage);
				array_push($where_arr, "w.vintage BETWEEN $vintages[0] AND $vintages[1]");
			}
			else array_push($where_arr, "w.vintage = $vintage");
		}
		/**
		*	Alcohol
		*/
		if (!empty($alcohol)) {
			if (strpos($alcohol, ",")) {
				$alcohols = explode(",", $alcohol);

				$str = "";
				$len = count($alcohols);

				for($i = 0; $i < $len; $i++) {
					$str .= "w.alcohol = '$alcohols[$i]%' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "w.alcohol = '$alcohol%'");
		}
		/**
		*	Organic
		*/
		if (!empty($organic)) {
			if($organic == 1){
				array_push($where_arr, "w.organic = $organic");
			}
		}
		/**
		*	Ethical
		*/
		if (!empty($ethical)) {
			if($ethical == 1) {
				array_push($where_arr, "w.ethical = $ethical");
			}
		}
		/**
		*	Kosher
		*/
		if (!empty($kosher)) {
			if ($kosher == 1) {
				array_push($where_arr, "w.kosher = $kosher");
			}
		}
		/**
		*	Country
		*/
		if (!empty($country)) {
			if (strpos($country, ",")) {
				$countries = explode(",", $country);

				$str = "";
				$len = count($countries);

				for($i = 0; $i < $len; $i++) {
					$str .= "c.name = '$countries[$i]' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "c.name = '$country'");
		}
		/**
		*	Region
		*/
		if (!empty($region)) {
			if (strpos($region, ",")) {
				$regions = explode(",", $region);

				$str = "";
				$len = count($regions);

				for($i = 0; $i < $len; $i++) {
					$str .= "r.name LIKE '%$regions[$i]%' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "r.name LIKE '%$region%'");
		}
		/**
		*	Producer
		*/
		if (!empty($producer)) {
			if (strpos($producer, ",")) {
				$producers = explode(",", $producer);

				$str = "";
				$len = count($producers);

				for($i = 0; $i < $len; $i++) {
					$str .= "p.name LIKE '%$producers[$i]%' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "p.name LIKE '%$producer%'");
		}
		/**
		*	Supplier
		*/
		if (!empty($supplier)) {
			if (strpos($supplier, ",")) {
				$suppliers = explode(",", $supplier);

				$str = "";
				$len = count($suppliers);

				for($i = 0; $i < $len; $i++) {
					$str .= "s.name LIKE '%$suppliers[$i]%' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "s.name LIKE '%$supplier%'");
		}
		/**
		*	Article type
		*/
		if (!empty($article_type)) {
			if (strpos($article_type, ",")) {
				$article_types = explode(",", $article_type);

				$str = "";
				$len = count($article_types);

				for($i = 0; $i < $len; $i++) {
					$str .= "a.name LIKE '%$article_types[$i]%' OR ";
				}

				$str = substr($str, 0, -3);
				$str = "(" . $str . ")";
				array_push($where_arr, $str);
			}

			else array_push($where_arr, "a.name LIKE '%$article_type%'");
		}
		/**
		*	Search
		*/
		if (!empty($search)) {
			$str = "";

			if(strpos($search, " ")) {
				$words = explode(" ", $search);
				$len = count($words);

				for($i = 0; $i < $len; $i++) {
					$str .= "AND (w.name LIKE '%$words[$i]%' 
					OR w.second_name LIKE '%$$words[$i]%' 
					OR w.type LIKE '%$words[$i]%' 
					OR w.style LIKE '%$words[$i]%' 
					OR w.raw_material LIKE '%$words[$i]%'
					OR c.name LIKE '%$words[$i]%' 
					OR r.name LIKE '%$words[$i]%' 
					OR p.name LIKE '%$words[$i]%' 
					OR s.name LIKE '%$words[$i]%' 
					OR a.name LIKE '%$words[$i]%')
					";
				}

				//$str .= "LIMIT 5"; TODO: ??
				$str = substr($str, 4);
			}
			else {
				$str = "
				(w.name LIKE '%$search%' 
				OR w.second_name LIKE '%$search%' 
				OR w.type LIKE '%$search%' 
				OR w.style LIKE '%$search%' 
				OR w.raw_material LIKE '%$search%'
				OR c.name LIKE '%$search%' 
				OR r.name LIKE '%$search%' 
				OR p.name LIKE '%$search%' 
				OR s.name LIKE '%$search%' 
				OR a.name LIKE '%$search%')";
			}

			array_push($where_arr, $str);
		}

		$where_str = implode(" AND ", $where_arr);


		if ($where_str == "") {
			return $where_str;
		}
		else {

			$where_str = "WHERE " . $where_str;
			return $where_str;
		}
		
	}

	private function getOrdernSortStr($sort, $order) {
		$str = "";

		if(!empty($sort) && !empty($order)) {
			$str .= "ORDER BY $order $sort";
		}
		else if(!empty($sort)) {
			$str .= "ORDER BY w.nr $sort";
		}

		else if (!empty($order)) {
			$str .= "ORDER BY $order";
		}

		return $str;
	}

	private function getLimitStr($limit) {
		$str = "";

		if(!empty($limit)) {
			$str .= "LIMIT $limit";
		}

		return $str;
	}
}

?>