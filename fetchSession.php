<?php
// fetch user options
$pace = $_SESSION['pace'];
$days = $_SESSION['days'];
$starttimeh = $_SESSION['starttimeh'];

// fetch itinerary info
$itin_edge_ids = $_SESSION['itin_edge_ids'];
$itin_node_ids = $_SESSION['itin_node_ids'];
$attractions = $_SESSION['attractions'];
$attractions_id = $_SESSION['attractions_id'];
$today = $_SESSION['today'];

$price_level = $_SESSION['price_level'];
$cuisine_type = $_SESSION['cuisine_type'];

if (array_key_exists("food_itin",$_SESSION)){
	$food_itin=$_SESSION["food_itin"];	
}

// fetch hotel info
//$hotelinfo = $_SESSION['hotelinfo'];
if (array_key_exists("hotelinfo",$_SESSION)){
	$hotelinfo = $_SESSION['hotelinfo'];
}
?>