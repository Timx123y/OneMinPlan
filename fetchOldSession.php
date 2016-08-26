<?php
// fetch old $_SESSION
$pace = $_SESSION['old_pace'];
$days = $_SESSION['old_days'];
$starttimeh = $_SESSION['old_starttimeh'];

// fetch itinerary info
$itin_edge_ids = $_SESSION['old_itin_edge_ids'];
$itin_node_ids = $_SESSION['old_itin_node_ids'];
$attractions = $_SESSION['old_attractions'];
$attractions_id = $_SESSION['old_attractions_id'];
$today = $_SESSION['old_today'];

// these are unchanging
$price_level = $_SESSION['price_level'];
$cuisine_type = $_SESSION['cuisine_type'];

if (array_key_exists("old_food_itin",$_SESSION)){
	$food_itin=$_SESSION["old_food_itin"];	
}

// fetch hotel info
//$hotelinfo = $_SESSION['hotelinfo'];
if (array_key_exists("old_hotelinfo",$_SESSION)){
	$hotelinfo = $_SESSION['old_hotelinfo'];
}
?>