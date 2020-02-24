<?php
	// --------- Config
	ini_set('memory_limit', '4000M');

	include 'Main.php';
	$main = new Main();
	
?>

<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<title>MyWineApp</title>
	</head>

	<body>
		<pre>
			<?php 
				echo $main->updateDatabase();
			?>
		</pre>

	</body>
</html>