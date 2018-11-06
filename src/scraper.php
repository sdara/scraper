<?php

function send_alert( $subject, $data ){
	// @mail( 'to@example.com', $subject, $data, 'From: from@example.com' );
	echo "mail(to@example.com, $subject, $data, 'From: from@example.com' );";
}

function grabJuice( $url = '' ){
	$ret = array(
		'data' => array(),
		'errors' => array()
	);
	
	if( !empty( $url ) ){
		$ret['data'][] = $url;
		
		$doc = new DOMDocument();
		
		ob_start();
			include( $url );
			$file = ob_get_contents();
		ob_end_clean();
		
		$doc->loadHTML( $file );	
		
		$items = $doc->getElementsByTagName( 'select' );
		
		foreach( $items as $item ){
			if( stristr( $item->getAttribute('name'), 'beverages' ) ){
				$options = $item->getElementsByTagName( 'option' );
				foreach( $options as $option ){
					if( strtolower( $option->getAttribute( 'value' ) ) != 'select' ){
						$ret['data'][] = $option->getAttribute( 'value' );
					}
				}
				unset( $option );
				
				break;
			}
		}
		unset( $item );
	}
	
	return $ret;
}

$mysqli = new mysqli("localhost", "root", "", "your-database-name");

if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
	exit;
}

$output = [];

$query = "
	SELECT timestamp, scrape_output
	FROM scrape_tracker
	WHERE scrape_type = 'beverage' 
	AND scrape_hit = 1 
	ORDER BY id DESC 
	LIMIT 1
";

$result = $mysqli->query( $query );

$row = $result->fetch_array(MYSQLI_ASSOC);

$last_seen_text = "Juice has never been discovered.";

$juicesAsText = '';

if($row){
	$last_seen_text = "Juice was last seen on " . date( 'l, F jS, Y', strtotime( $row['timestamp'] ) ) . ".";
	$juicesAsText = $row['scrape_output'];
	$juicesAsText = str_replace( '&amp;', '&', $juicesAsText );
}

/* free result set */
$result->free();

// grab out juice lists
$items1 = grabJuice( 'beverage.php' );

$insJuiceStr = "Items:\n" . implode( "\n", $items1['data'] );

$foundFlavor = stristr( $insJuiceStr, 'apple' ) ? 1 : 0;

$insJuiceStr .= "\n\nFound Apple Juice: " . ( $foundFlavor ? 'Yes' : 'No' );

$output[] = $insJuiceStr;
$output[] = $last_seen_text;

if( !( $juicesAsText && $juicesAsText == $insJuiceStr ) ){
	if( $foundFlavor ){
		send_alert( 'Apple Juice Found', $insJuiceStr );
	}
	
	/* create a prepared statement */
	if ($stmt = $mysqli->prepare("INSERT INTO scrape_tracker ( scrape_type, scrape_output, scrape_hit ) VALUES (?,?,?)")) {
		$juice = "beverage";
		
		/* bind parameters for markers */
		$stmt->bind_param("sss", $juice, $insJuiceStr, $foundFlavor);

		/* execute query */
		if (!$stmt->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			$insJuiceStr .= "\n\n(juice scrape inserted: no - Error)";
		}
		
		/* close statement */
		$stmt->close();
	}
	
	$insJuiceStr .= "\n\n(juice scrape inserted: yes)";
}else{
	$insJuiceStr .= "\n\n(juice scrape inserted: no)";
}

echo implode("<hr>", $output);

$mysqli->close();
?>