<?php
// push last operation onto $_SESSION

$_SESSION['old_pace']=$pace;
$_SESSION['old_days']=$days;
$_SESSION['old_starttimeh']=$starttimeh;

// fetch itinerary info
$_SESSION['old_itin_edge_ids'] = $itin_edge_ids;
$_SESSION['old_itin_node_ids']=$itin_node_ids;
$_SESSION['old_attractions']=$attractions;
$_SESSION['old_attractions_id']=$attractions_id;
$_SESSION['old_today']=$today;

if (count($food_itin)>0){
	$_SESSION["old_food_itin"]=$food_itin;
}

// fetch hotel info
//$hotelinfo = $_SESSION['hotelinfo'];
if ($_SESSION['hasHotel']){
	$_SESSION['old_hotelinfo']=$hotelinfo;
}
?>