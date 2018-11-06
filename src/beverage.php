<?php
	$beverages = [
		"Orange Juice",
		"Cranberry Juice",
		"Grape Juice",
		"Grapefruit Juice",
		"Prune Juice",
		"Apple Juice",
		"Hot Tea",
		"Iced Tea",
		"Beer",
		"Spirits"
	];
	
	$options = "";
	
	$rand_keys = array_rand($beverages, 3);
	for($i = 0; $i < 3; $i += 1){
		$options .= '<option value="'.$beverages[$rand_keys[$i]].'">'.$beverages[$rand_keys[$i]].'</option>';
	}
?>
<!doctype html>
<html>
	<head>
		<title>Beverages</title>
	</head>
	<body>
		<h1>Beverages</h1>
		<select name="beverages"><?php echo $options; ?></select>
	</body>
</html>