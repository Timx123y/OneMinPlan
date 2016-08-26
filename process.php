<?php 
//require 'connect.php';
include 'functions.php';
include 'ta_functions.php';	
//include 'kint/Kint.class.php';
session_start();

$hasAlert = false;
if ((isset($_GET['day'])) && !empty($_GET['day'])){
	include 'fetchSession.php';
	$today = $_GET['day'];		
	$_SESSION['today'] = $today;		
	// if ($_SESSION['addhotel'] !==""){	
	// 	$outputtext = "<script> console.log('addhotel: ".  $_SESSION['addhotel']  . "');</script>";
	// 	echo 	$outputtext ; 
	// 	tagHotel($_SESSION['addhotel']);	
	// }

} else if ((isset($_GET['removename'])) && !empty($_GET['removename'])) {
	include 'fetchSession.php';
	include 'pushSession.php';
	$removename = trim($_GET['removename']);
	//$removename = str_replace("+"," ",$removename);	
	$removename=urldecode($removename);
	
	array_push($_SESSION["removed_by_user_name"],$removename);
	
			
	$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('". $removename . "');</script>";
	echo 	$outputtext ;

        foreach($itin_node_ids as $this_day=>$itin_today){
		if(array_search($attractions_id[$removename],$itin_today)!==false){$attraction_on_day=$this_day;break;}
	}
	$removeind = array_search($removename,$attractions);
	$attractions = array_diff($attractions,array($removename));
	$attractions = array_values($attractions);
    //print_r($attractions_id);
	//echo "<br>";
	
	if (array_key_exists($removename,$attractions_id)){
		$to_be_remove = array_fill_keys(array($removename),$attractions_id[$removename]);
		$attractions_id = array_diff($attractions_id,$to_be_remove);
	}	
	//$outputtext = "<script> console.log('". $removeind . "');</script>";
	//echo 	$outputtext ;
	$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	echo 	$outputtext ;
						
	require 'gen_itin2.php';	
	
	$_SESSION['itin_edge_ids'] = $itin_edge_ids;
	$_SESSION['itin_node_ids'] = $itin_node_ids;
	
	$_SESSION['days'] = $days;
	$_SESSION['attractions'] = $attractions ;
	$_SESSION['attractions_id'] = $attractions_id;
	$_SESSION['food_itin'] = $food_itin;
	
	$sql = "SELECT category FROM attractions_hk_ta WHERE name = \"".mysqli_real_escape_string($connection,$removename)."\"" ;
	$result = mysqli_query($connection, $sql);
	$row = mysqli_fetch_assoc($result);
	// if ($row["category"]==="Hotel") { $_SESSION['addhotel'] = "";}
	// if ($_SESSION['addhotel'] !==""){tagHotel($_SESSION['addhotel']);}

	$hasAlert = true;
	$alertType = 'success';
	$alertMsg = "Successfully removed attraction <strong>$removename</strong> on day $attraction_on_day.<a href='?undo=undo' >undo</a> ";

} else if ((isset($_GET['addname'])) && !empty($_GET['addname'])) {
	include 'fetchSession.php';
	include 'pushSession.php';
	$addname = trim($_GET['addname']);
	//$addname = str_replace("+"," ",$addname);
	
	$addname = urldecode($addname);
	
	array_push($_SESSION["added_by_user_name"],$addname);
	$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('". $addname . "');</script>";
	echo 	$outputtext ;

	if ((array_search($addname, $attractions))===false){
		//$ind = count($attractions);
		//$attractions[$ind]=$addname;		
		array_push($attractions,$addname);
		$sql = "SELECT node_id FROM attractions_hk_ta WHERE name = \"".mysqli_real_escape_string($connection,$addname)."\"" ;
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
		echo 	$outputtext ;
		$outputtext = "<script> console.log('". $addname . "');</script>";
		echo 	$outputtext ;
		
		$attractions_id[$addname]=$row["node_id"];
	}

	$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	echo 	$outputtext ;
					
	require 'gen_itin2.php';	
	$add_success=false;
	foreach($itin_node_ids as $this_day=>$itin_today){
	//	if(array_search($attractions_id[$addname],$itin_today)!==false){$add_success=true;$added_on_day=$this_day;break;}
		if (array_key_exists($addname,$attractions_id) AND (array_search($attractions_id[$addname],$itin_today)!==false)){
			
			$add_success=true;
			$added_on_day=$this_day;
			break;}
	}

	$_SESSION['itin_edge_ids'] = $itin_edge_ids;
	$_SESSION['itin_node_ids'] = $itin_node_ids;
	
	$_SESSION['days'] = $days;
	$_SESSION['attractions'] = $attractions ;
	$_SESSION['attractions_id']=$attractions_id;
	$_SESSION['food_itin']=$food_itin;
	
	// if ($_SESSION['addhotel'] !==""){tagHotel($_SESSION['addhotel']);}
        if ($add_success){
		$hasAlert = true;
		$alertType = 'success';
		$alertMsg = "Successfully added attraction <strong>$addname</strong> on day $added_on_day. <a href='?undo=undo' >undo</a>";
	}
	else{
		$hasAlert = true;
		$alertType = 'danger';
		$alertMsg = "Failed to add attraction <strong>$addname</strong>. Remove some other attractions before adding new ones.";
	}	
} else if ((isset($_GET['addhotel'])) && !empty($_GET['addhotel'])) {
	include 'fetchSession.php';
	//include 'pushSession.php';
	$hotel_id = $_GET['addhotel'];
	$_SESSION['hasHotel'] = true;
	$sql = "SELECT * FROM hotels_hk_ta WHERE hotel_id=$hotel_id;";
	$result = mysqli_query($connection,$sql);
	if ($result) {
		$hotelinfo =  mysqli_fetch_assoc($result);
		$_SESSION['hotelinfo'] = $hotelinfo;
                $hotelname = $hotelinfo["name"];
	} else {
		echo "<p>Error fetching hotel info from db<br>$sql</p>";
	}

	$hasAlert = true;
	$alertType = 'success';
	$alertMsg = "Successfully added hotel <strong>$hotelname</strong>.";
	// $addhotel = trim($_GET['addhotel']);
	// $addhotel = str_replace("+"," ",$addhotel);
	// $outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	// echo 	$outputtext ;
	// $outputtext = "<script> console.log('". $addhotel . "');</script>";
	// echo 	$outputtext ;
	
	//tagHotel($addhotel);
	

	// $outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	// echo 	$outputtext ;
						
	//require 'gen_itin.php';	
	
 //    $_SESSION['addhotel'] = $addhotel;
	// //$_SESSION['itin_edge_ids'] = $itin_edge_ids;
	// //$_SESSION['itin_node_ids'] = $itin_node_ids;
	// $_SESSION['days'] = $days;
	// $_SESSION['attractions'] = $attractions ;
	// $_SESSION['attractions_id']=$attractions_id;	
} else if (isset($_GET['removehotel']) && !empty($_GET['removehotel'])) {
	include 'fetchSession.php';
	//include 'pushSession.php';
        $hotelname = $hotelinfo["name"];
	$_SESSION['hasHotel'] = false;
	$hasAlert = true;
	$alertType = 'success';
	$alertMsg = "Successfully removed hotel <strong>$hotelname</strong>.";
} else if ((isset($_GET['addlunch']) && !empty($_GET['addlunch']))  or(isset($_GET['adddinner']) && !empty($_GET['adddinner'])))  {
	include 'fetchSession.php';
	include 'pushSession.php';
	
	//print_r($itin_node_ids[$today]);
	if (!empty($_GET['addlunch'])){$addrestaurant = $_GET['addlunch']; $lunch_or_dinner = "L";}
	else {$addrestaurant = $_GET['adddinner'];$lunch_or_dinner="D";}
	$addrestaurant = trim($addrestaurant);
	//$addrestaurant = str_replace("+"," ",$addrestaurant);
	
	$addrestaurant= urldecode($addrestaurant);
	
	
	//print $addrestaurant;
	//$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	//echo 	$outputtext ;
	//$outputtext = "<script> console.log('". $addname . "');</script>";
	//echo 	$outputtext ;

	/*
	if ((array_search($addname, $attractions))===false){
		$ind = count($attractions);
		$attractions[$ind]=$addname;		
		$sql = "SELECT node_id FROM attractions_hk_ta WHERE name = \"".mysqli_real_escape_string($connection,$addname)."\"" ;
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
		echo 	$outputtext ;
		$outputtext = "<script> console.log('". $addname . "');</script>";
		echo 	$outputtext ;
		
		$attractions_id[$addname]=$row["node_id"];
		
	}
	*/
	

	//$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
	//echo 	$outputtext ;

	// replace lunch or dinner with that restaurant
	// and replace edge

	
	$restaurant_index = array_search( $lunch_or_dinner , $itin_node_ids[$today] );
	//print_r( $itin_node_ids[$today]);
	
	if ($restaurant_index !==false){
		//print "restaurant index<br>";
		//print $restaurant_index;
		
		// search database
		$current_point = $addrestaurant;
		$ind = count($attractions);
		$attractions[$ind]=$addrestaurant;		
		$sql = "SELECT node_id FROM attractions_hk_ta WHERE name = \"".mysqli_real_escape_string($connection,$addrestaurant)."\"" ;
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		//print $sql;
		$outputtext = "<script> console.log('$sql". implode( ',' , $attractions ) . "');</script>";
		echo 	$outputtext ;
		$outputtext = "<script> console.log('". $addrestaurant . "');</script>";
		echo 	$outputtext ;
		
		$attractions_id[$addrestaurant]=$row["node_id"];
		$itin_node_ids[$today][$restaurant_index]=$row["node_id"];
		
		// add edges
		
		// if there is a previous point
		if ($restaurant_index>=1) {
			$previous_point_id = $itin_node_ids[$today][$restaurant_index-1];
			$sql = "SELECT * FROM attractions_hk_ta WHERE node_id = $previous_point_id" ;
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$previous_point = $row["name"];
			//print $previous_point;
			
			// first search for walk edge
			// only look for walk edge that is at most 20 min
			$sql = "SELECT * from graph_hk_ta WHERE start_point= \"". mysqli_real_escape_string($connection,$previous_point). "\" and end_point = \"". mysqli_real_escape_string($connection,$current_point)  ."\" and travel_mode = \"walk\" and  travel_time>0 order by travel_time";
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			if (is_null($row)) {
				$walk_edge_id=addEdgeDB($previous_point,$current_point,"walk");
				updateEdgeDB($walk_edge_id,"walk");		
				$sql = "SELECT * from graph_hk_ta where edge_id = $walk_edge_id";
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
			}
			$walk_time = $row["travel_time"];
			$walk_edge_id = $row["edge_id"];
			
			if ($walk_time>20) {
				$sql = "SELECT * from graph_hk_ta WHERE start_point= \"". mysqli_real_escape_string($connection,$previous_point). "\" and end_point = \"". mysqli_real_escape_string($connection,$current_point)  ."\" and  travel_time>0 and travel_mode != \"walk\" order by travel_time";
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				
				if (is_null($row)){
					$transit_edge_id=addEdgeDB($previous_point,$current_point,"transit");
					updateEdgeDB($transit_edge_id,"transit");					
					$sql = "SELECT * from graph_hk_ta where edge_id = $transit_edge_id";
					$result = mysqli_query($connection, $sql);
					$row = mysqli_fetch_assoc($result);
				}
				$transit_time = $row["travel_time"];
				$transit_edge_id = $row["edge_id"];		
				$itin_edge_ids[$today][$restaurant_index-1] = $transit_edge_id;	
			} else {
				$itin_edge_ids[$today][$restaurant_index-1] = $walk_edge_id;
			}
		}

		// if there is a next point
		
		if (($restaurant_index+1)<  count( $itin_node_ids[$today])){
			$outputtext = "<script> console.log('add restaurant(line 300): ADDING NEXT POINT restuarant_index = $restaurant_index');</script>";
			echo 	$outputtext ;
			
			$next_point_id = $itin_node_ids[$today][$restaurant_index+1];
			$sql = "SELECT * FROM attractions_hk_ta WHERE node_id = $next_point_id" ;
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$next_point = $row["name"];		
			//print $next_point;
			// first search for walk edge
			// only look for walk edge that is at most 20 min
			$sql = "SELECT * from graph_hk_ta WHERE start_point= \"". mysqli_real_escape_string($connection,$current_point). "\" and end_point = \"". mysqli_real_escape_string($connection,$next_point)  ."\" and travel_mode = \"walk\" and  travel_time>0 order by travel_time";
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			if (is_null($row)) {
				$walk_edge_id=addEdgeDB($current_point,$next_point,"walk");
				updateEdgeDB($walk_edge_id,"walk");		
				$sql = "SELECT * from graph_hk_ta where edge_id = $walk_edge_id";
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
			}
			$walk_time = $row["travel_time"];
			$walk_edge_id = $row["edge_id"];
			
			if ($walk_time>20) {
				$sql = "SELECT * from graph_hk_ta WHERE start_point= \"". mysqli_real_escape_string($connection,$current_point). "\" and end_point = \"". mysqli_real_escape_string($connection,$next_point)  ."\" and  travel_time>0 and travel_mode != \"walk\" order by travel_time";
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				
				if (is_null($row)){
					$transit_edge_id=addEdgeDB($current_point,$next_point,"transit");
					updateEdgeDB($transit_edge_id,"transit");					
					$sql = "SELECT * from graph_hk_ta where edge_id = $transit_edge_id";
					$result = mysqli_query($connection, $sql);
					$row = mysqli_fetch_assoc($result);
				}
				$transit_time = $row["travel_time"];
				$transit_edge_id = $row["edge_id"];		
				$itin_edge_ids[$today][$restaurant_index] = $transit_edge_id;	
			} else {
				$itin_edge_ids[$today][$restaurant_index] = $walk_edge_id;
			}	
		}
	}
	$outputtext = "<script> console.log('add restaurant(line 350): itin_edge_ids[$today]: ". implode( ',' , $itin_edge_ids[$today] ) . "');</script>";
	echo 	$outputtext ;
	
	// require 'gen_itin.php';	
	/*
	$add_success=false;
	foreach($itin_node_ids as $this_day=>$itin_today){
		if(array_search($attractions_id[$addname],$itin_today)!==false){$add_success=true;$added_on_day=$this_day;break;}
	}
	*/
	
	$_SESSION['itin_edge_ids'] = $itin_edge_ids;
	$_SESSION['itin_node_ids'] = $itin_node_ids;

	$_SESSION['days'] = $days;
	$_SESSION['attractions'] = $attractions ;
	$_SESSION['attractions_id']=$attractions_id;
	$_SESSION['food_itin']=$food_itin;
	
	/*
	if ($add_success){
	$hasAlert = true;
	$alertType = 'success';
	$alertMsg = "Successfully added attraction <strong>$addname</strong> on day $added_on_day. <a href='?undo=undo' >undo</a>";
	}
	*/
} else if (isset($_GET['undo']) && !empty($_GET['undo']))  {
	// load previous itin
	include 'fetchOldSession.php';	
} else if (isset($_GET['likename']) && !empty($_GET['likename']))  {

	include 'fetchSession.php';
	$likename = urldecode($_GET['likename']);
	//$likename = str_replace("+"," ",$likename);
	
	array_push($_SESSION['liked_by_user_name'],$likename);
	//include 'fetchOldSession.php';	
}
else {
	/* first time visited from index.php */
	$today = 1;
	// return new attraction
	// $itin2d is in the form of an array of array, 
	// $itin2d[$i] represent the itin on the ($i+1) day
	// $itin2d[$i][$j] represent the ($j+1)-th attraction on the ($i+1) day


	require 'get_user_preference.php';
	require 'gen_itin2.php';
	
	$itin_info_time_by_name = compute_itin_time();
	
	// tweak_itin();// this is a function in gen_itin.php, which tweak the itin such that it contains at least one attraction per user preference (if this is indeed possible)
	for($x=1;$x<=$days;$x+=1){
	$outputtext = "<script> console.log('itin edge id [$x] ". implode( ',' , $itin_edge_ids[$x] ) . "');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('itin node id [$x] ". implode( ',' , $itin_node_ids[$x] ) . "');</script>";
	echo 	$outputtext ;
	
	$new_array=array();
	foreach($itin_node_ids[$x] as $y=>$y_value){
		if (($y_value!=="L") and ($y_value !=="D")){
		$new_array[$y] = $attractions_info_by_id[$y_value]["name"];		
		}
		else {	$new_array[$y]=$y_value;}
	}
	
	$outputtext = "<script> console.log('itin node id (with name after tweaking) [$x] ". implode( ',' , $new_array ) . "');</script>";
	echo 	$outputtext ;
	
	$attractions_visited=array_merge($attractions_visited,idToName($itin_node_ids[$x]));		
}	


	
	//print_r($itin_edge_ids) ;
	
    // $_SESSION['addhotel'] = "";
	$_SESSION['itin_edge_ids'] = $itin_edge_ids;
	$_SESSION['itin_node_ids'] = $itin_node_ids;
	$_SESSION['hasHotel'] = false;

	$_SESSION['today'] = $today;
	$_SESSION['days'] = $days;
    $_SESSION['pace'] = $pace;
	$_SESSION['attractions'] = $attractions;
	$_SESSION['attractions_id'] = $attractions_id;
	// Config start time
	$starttime = array();
	$starttime['h'] = $starttimeh;
	$starttime['m'] = 0;
	$_SESSION['starttimeh'] = $starttimeh;
	$_SESSION['food_itin']=$food_itin;
	
	$_SESSION["removed_by_user_name"]=array();
	$_SESSION["added_by_user_name"]=array();
	$_SESSION["liked_by_user_name"]=array();
	
	$_SESSION["selected_by_algo_and_routed_name"]=$selected_by_algo_and_routed_name;
	$_SESSION["selected_by_algo_name"]=$selected_by_algo_name;
	
	
	
	
}

$_SESSION['hasAlert'] = $hasAlert;

if ($hasAlert){
$_SESSION['alertType'] 	=$alertType;
$_SESSION['alertMsg'] 	=$alertMsg;
}
	// Hardcoded Information for the Core Itinerary
	//$itin_edge_ids = array();
	//$itin_node_ids = array();
	// Day 1
	// edge2 from Peak Tram(1) to The Peak(2)
	// edge3 from The Peak to 2IFC (transfer)
	// edge18 from 2IFC to Star Ferry Pier(3)
	// edge4 from Star Ferry Pier(3) to TST Promenade(4)
	//$itin_edge_ids[1] = array(2,3,18,4);
	//$itin_node_ids[1] = array(1,2,3,4);
	// Day 2
	// edge6 from Ngong Ping 360(6) to Big Buddha(7)
	// edge7 from Big Buddha(7) to Po Lin Monastery(8)
	//$itin_edge_ids[2] = array(6,7);
	//$itin_node_ids[2] = array(6,7,8);
	// Day 3
	// edge8 from Harbour City(9) to Clock Tower(10)
	// edge9 from Clock Tower(10) to Avenue of Stars(11)
	// edge10 from Avenue of Stars(11) to Nathan Road(12)
	// edge11 from Nathan Road(12) to Goldfish Market(13)
	//$itin_edge_ids[3] = array(8,9,10,11);
	//$itin_node_ids[3] = array(9,10,11,12,13);
	// Day 4
	// edge19 from Golden Bauhinia Square(15) to Fleming Road (transfer)
	// edge12 from Fleming Road to Times Square(17)
	// edge14 from Times Square(17) to Sogo(18)
	// edge15 from Sogo(18) to Happy Valley Racecourse(19)
	//$itin_edge_ids[4] = array(19,12,14,15);
	//$itin_node_ids[4] = array(15,17,18,19);
	// Day 5
	// edge 16 from Nan Lian Garden(20) to Chu Lin Nunnery(21)
	//$itin_edge_ids[5] = array(39);
	//$itin_node_ids[5] = array(20,21);
	//updateEdgeDB(4294);


// Get the detailed edge infos for today


$outputtext = "<script> console.log('". implode( ',' , $attractions ) . "');</script>";
echo 	$outputtext ;
$outputtext = "<script> console.log('". implode( ',' , $attractions_id ) . "');</script>";
echo 	$outputtext ;

$outputtext = "<script> console.log('itin_edge_ids[$today] ". implode( ',' , $itin_edge_ids[$today] ) . "');</script>";
echo 	$outputtext ;
$outputtext = "<script> console.log('itin_node_ids[$today] ". implode( ',' , $itin_node_ids[$today] ) . "');</script>";
echo 	$outputtext ;

$today_edges = array();
$itin_edge_ids[$today]=array_filter($itin_edge_ids[$today]);
$today_edge_ids = $itin_edge_ids[$today];
//$today_edge_ids=array_values($today_edge_ids);
$outputtext = "<script> console.log('today_edge_ids ". implode( ',' , $today_edge_ids ) . "');</script>";
echo 	$outputtext ;
//print_r($today_edge_id);
//echo "<br>";

$ind=0;
foreach($today_edge_ids as $edge_id) {
	if ($edge_id ==-1){$today_edges[$ind][$edge_id]="-1";}
	else{
	$query = "SELECT * FROM graph_hk_ta WHERE edge_id = $edge_id";
	
	$result = mysqli_query($connection, $query);
	if (!$result) {
		print $query;
		die("Database query failed.");
	}
	$today_edges[$ind][$edge_id] = mysqli_fetch_assoc($result);
	}
	
	$ind+=1;
}
	
// Get the detailed node infos for today
$today_nodes = array();
$ind=0;
$itin_node_ids[$today]=array_filter($itin_node_ids[$today]);
$today_node_ids = $itin_node_ids[$today];
//$today_node_ids = array_values($today_node_ids);
$outputtext = "<script> console.log('today_node_ids ". implode( ',' , $today_node_ids ) . "');</script>";
echo 	$outputtext ;
foreach($today_node_ids as $node_id) {
	if (($node_id=="D") or ($node_id=="L")){
		$today_nodes[$ind][$node_id]=$node_id;
	}
	else{
		$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_id";
		$outputtext = "<script> console.log('". $query . "');</script>";
		echo 	$outputtext ;

		$result = mysqli_query($connection, $query);
		if (!$result) {
			print $query;
			die("Database query failed.");
		}
		$today_nodes[$ind][$node_id] = mysqli_fetch_assoc($result);
	}	
	$ind+=1;
}
// Config start time
$starttime = array();
$starttime['h'] = $starttimeh;
$starttime['m'] = 0;
$_SESSION['starttimeh'] = $starttimeh;	
//print_r($today_nodes);


?>

<!DOCTYPE html>
<html lang="en">

<head><meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
  
  <title>OneMinPlan Core Itinerary</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/touchspin.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/prefixfree.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
</head>

<body>
	<?php include "nav.php"?>

	<section id="main" class="main-output">
		<div class="bg-overlay">
			<h1>Hong Kong</h1>
			<h3>The Pearl of the Orient</h3>
			<h4>A unique fusion of Chinese and Western cultures. No matter it be food, shopping, nature or architecture, the diversity of Hong Kong is sure to provide something for everyone.</h4>

			<ul class="nav nav-pills nav-days">
				<?php
					for ($i=1; $i<=$days; $i++) {
						echo "<li><a href='?day=$i' class='btn btn-day" 
						. (($i == $today) ? ' active' : '')
						. "'>Day $i</a></li>";
					}
				?>
				<li><a href="overview.php" class="btn btn-overview">Overview</a></li>
			</ul>

		</div><!-- bg-overlay -->
	</section><!-- main -->

	<section id="results" class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<article id="itin">
				<header class="row">
					<h4>
						<?php
							echo "Day $today";
							// echo (4 + $today);
							// echo "th August 2015";
						?>
					</h4>
				</header><!-- row -->

				<!-- <button type="button" onclick="addUserData()">Upload user data</button> -->
				<div id="demo"><h2></h2></div>
							<?php
					
					$user_data=array();
					$user_data["user_settings"]=$_SESSION["user_settings"];
					$user_data["removed_by_user_name"]=$_SESSION["removed_by_user_name"];
					$user_data["added_by_user_name"]=$_SESSION["added_by_user_name"];
					
					$user_data["selected_by_algo_and_routed_name"]=$_SESSION["selected_by_algo_and_routed_name"];
					$user_data["selected_by_algo_name"]=$_SESSION["selected_by_algo_name"];
					$user_data["liked_by_user_name"]=$_SESSION["liked_by_user_name"];
					$user_data["itin_edge_ids"]= $itin_edge_ids;
					$user_data["itin_node_ids"]=$itin_node_ids;
					
					$json_string = json_encode($user_data);

					?>		
					
					
				<script>
				function addUserData() {
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
							document.getElementById("demo").innerHTML = xmlhttp.responseText;
						}
					}

					xmlhttp.open("POST", "add_to_database.php", true);
					xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

					xmlhttp.send("json_string=" +	<?php echo "\"". mysqli_real_escape_string($connection,$json_string). "\"";?>		);
				}
				</script>
				<?php
				//echo $json_string;
				?>
				<?php
				echo "<div id = 'full_itin'>";	
				include "print_itin.php";
				// // display any alerts
				// if ($hasAlert) {
				// 	echo 
				// 	"
				// 	<div class='alert alert-$alertType alert-dismissible' role='alert'>
				// 		<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
				// 		$alertMsg
				// 	</div>
				// 	";
				// }

				// $currenttime = $starttime;
				// $timestr = displayTime($currenttime);
				// $hasHotel = (isset($_SESSION['hasHotel']) && $_SESSION['hasHotel'] && isset($hotelinfo) && !empty($hotelinfo));
				// if (!$hasHotel) 
				// {
				// 	echo 
				// 	"
				// 	<p id='askhotel' class='alert alert-info role='alert'>
				// 		<strong>Where are you staying?</strong>
				// 		<a class='alert-link' id='showHotelModal' role='button' data-toggle='modal' data-target='#hotelModal'>Search for a hotel</a> 
				// 		or <a class='alert-link' id='showAddressModal' role='button' data-toggle='modal' data-target='#addressModal'>input address manually</a>.
				// 		<span class='glyphicon glyphicon-info-sign'></span>
				// 	</p>
				// 	"
				// 	;
				// } else {
				// 	$hotel_id = $hotelinfo['hotel_id'];
				// 	$name = $hotelinfo['name'];
				// 	$address = "[Hotel address here]";
				// 	$description = "[Hotel description here]";
				// 	$photo = "";
				// 	echo hotelHTML($timestr,$hotel_id,$name,$address,$description,$photo);
				// 	$start_lat = $hotelinfo['latitude'];
				// 	$start_long = $hotelinfo['longitude'];
				// 	$node_id = $today_node_ids[0];
				// 	$node = $today_nodes[0][$node_id];
				// 	$end_lat = $node['latitude'];
				// 	$end_long = $node['longitude'];
				// 	// echo "<p>Start: ($start_lat,$start_long) End: ($end_lat,$end_long)</p>";
				// 	$dir = googleDirectionsAPI($start_lat,$start_long,$end_lat,$end_long);
				// 	//d($dir);
				// 	echo transportHTMLarray($dir['mode'],$dir['dur'],$dir['dist'],$dir['route'],$dir['route_s'],$dir['towards'],$dir['from'],$dir['to'],$dir['stops'],$dir['a_name'],$dir['a_url']);
				// 	$travel_time = $dir['totaltime']; // in minutes
				// 	// Update currenttime
				// 	$durationtime = array();
				// 	$durationtime['h'] = 0;
				// 	$durationtime['m'] = $travel_time;
				// 	$currenttime = addTime($currenttime,$durationtime);

				// } 
				
				
				// $node_i = 0;
				// $edge_i = 0;	
				
				// while ($node_i < count($today_nodes)) {
				// 	// Get info for current node
				// 	$node_id = $today_node_ids[$node_i];
				// 	$node = $today_nodes[$node_i][$node_id];
				// 	$timestr = displayTime($currenttime);
				// 	$hasMeal = false;
				// 	if (($node_id==="D") or ($node_id==="L")){
				// 		if ($node_id==="D") {$food_info = $food_itin[$today]["dinner_list"];}
				// 		else {$food_info = $food_itin[$today]["lunch_list"];}
				// 		$duration = 1; // in hours
				// 		echo restaurantHTML($food_info,$timestr,$node_id);
				// 	}
				// 	else{
				// 		$duration = $node['duration'];
				// 		$duration = $_SESSION["user_duration_list"][$node_id];
				// 		$node['duration']=$duration;
				// 		$next_node_id = ($node_i+1 < count($today_nodes)) ?
				// 			$today_node_ids[$node_i+1] : '';
				// 		if (($next_node_id == 'L') || ($next_node_id == 'D')) {
				// 			$node['meal_id'] = $next_node_id;
				// 			$node['hasMeal'] = true;
				// 			$hasMeal = true;
				// 		} else {
				// 			$node['meal_id'] = '';
				// 			$node['hasMeal'] = false;
				// 		}
						
				// 		echo attractionHTML($node,$timestr);
				// 	}
					
					
				// 	// Update currenttime
				// 	$durationtime = array();
				// 	$durationtime['h'] = 0;
				// 	$durationtime['m'] = $duration * 60;
				// 	$currenttime = addTime($currenttime,$durationtime);
				// 	// Increment counter
				// 	$node_i++;
				// 	// Output subsequent edge(s)
				// 	if ($edge_i < count($today_edges)) {
				// 		do {
				// 			$edge_id = $today_edge_ids[$edge_i];
				// 			$edge = $today_edges[$edge_i][$edge_id];
				// 			// if ($edge_id == -1){
				// 			// 		$mode = 'walk';
				// 			// 		$duration = 15; // in minutes
				// 			// 		$travel_time_str = '15';
				// 			// 		$walkdist = 0;
				// 			// 		$route = '';
				// 			// 		$routeshort = '';// ** changed to short name
				// 			// 		$towards = '';
				// 			// 		$from = '';
				// 			// 		$to = '';
				// 			// 		$stops = 0;
				// 			// 		$agency_name = '';
				// 			// 		$agency_url = '';				
				// 			// }
				// 			// else
				// 			if ($edge_id != -1) {
				// 				//$name = $edge['name'];
				// 				$mode = $edge['travel_mode'];
				// 				$duration = $edge['travel_time']; // in minutes
				// 				$travel_time_str = $edge['travel_time_str'];
				// 				$walkdist = $edge['walk_dist'];
				// 				$route = $edge['travel_route'];
				// 				$routeshort = $edge['travel_route_short_name'];// ** changed to short name
				// 				$towards = $edge['travel_towards'];
				// 				$from = $edge['travel_from'];
				// 				$to = $edge['travel_to'];
				// 				$stops = $edge['travel_stops'];
				// 				$agency_name = $edge['agencies_name_str'];
				// 				$agency_url = $edge['agencies_url_str'];
				// 				echo transportHTMLarray($mode,$travel_time_str,$walkdist,$route,$routeshort,$towards,$from,$to,$stops,$agency_name,$agency_url);
				// 				// Update currenttime
				// 				$durationtime = array();
				// 				$durationtime['h'] = 0;
				// 				$durationtime['m'] = $duration;
				// 				$currenttime = addTime($currenttime,$durationtime);
				// 			}
							
				// 			// Increment counter
				// 			$edge_i++;	
				// 		} while (($edge_id !== -1) and ($edge['transfer_end'])); // repeat if endpoint is transfer
				// 	} // if ($edge_i < count($today_edges))
				// } // while ($node_i < count($today_nodes))
							
				
				
				
				// 				// End with hotel
				// if ($hasHotel) {
				// 	$start_lat = $node['latitude'];
				// 	$start_long = $node['longitude'];
				// 	// $node_id = $today_node_ids[0];
				// 	// $node = $today_nodes[0][$node_id];
				// 	$end_lat = $hotelinfo['latitude'];
				// 	$end_long = $hotelinfo['longitude'];
				// 	// echo "<p>Start: ($start_lat,$start_long) End: ($end_lat,$end_long)</p>";
				// 	$dir = googleDirectionsAPI($start_lat,$start_long,$end_lat,$end_long);
				// 	//d($dir);
				// 	echo transportHTMLarray($dir['mode'],$dir['dur'],$dir['dist'],$dir['route'],$dir['route_s'],$dir['towards'],$dir['from'],$dir['to'],$dir['stops'],$dir['a_name'],$dir['a_url']);
				// 	$travel_time = $dir['totaltime']; // in minutes
				// 	// Update currenttime
				// 	$durationtime = array();
				// 	$durationtime['h'] = 0;
				// 	$durationtime['m'] = $travel_time;
				// 	$currenttime = addTime($currenttime,$durationtime);
				// 	$timestr = displayTime($currenttime);
				// 	$hotel_id = $hotelinfo['hotel_id'];
				// 	$name = $hotelinfo['name'];
				// 	$address = "[Hotel address here]";
				// 	$description = "[Hotel description here]";
				// 	$photo = "";
				// 	echo hotelHTML($timestr,$hotel_id,$name,$address,$description,$photo);
					
				// }

				// Previous day and Next day buttons
				echo "<div class='row'>";
				if ($today > 1) {
					$yesterday = $today - 1;
					echo "<a class='btn daysbutton redbutton pull-left' href='?day=$yesterday'><i class='fa fa-paper-plane-o fa-flip-horizontal'></i> Previous day</a>";
				}
				if ($today < $days) {
					$tomorrow = $today + 1;
					echo "<a class='btn daysbutton redbutton pull-right' href='?day=$tomorrow'>Next day <i class='fa fa-paper-plane-o'></i></a>";
				} else {
					echo "<a class='btn daysbutton redbutton pull-right' href='overview.php'>Overview <i class='fa fa-paper-plane-o'></i></a>";
				}
				echo "</div><!-- Previous and Next day buttons -->";
				echo "</div>";
				?>
				
				</article><!-- itin -->
			</div><!-- col-md-8 -->
			
			<aside id="suggestions" class="col-md-4">
				<?php include 'samplesuggestions.php'; ?>
			</aside><!-- #suggestions -->
		</div><!-- row -->
		
	</section><!-- #results -->

	<footer>
		<?php include 'footernav.php'; ?>
	</footer>
	<?php 
	include 'hotelModal.php';
	include 'addressModal.php';
	?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/touchspin.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?region=hk&key=AIzaSyBQ8d-tYXI8XugQAd_RSd08CFf8aj28Elk "></script>
	<script src="js/output.js"></script>
	

	
	
</body>

<?php
	//include 'disconnect.php';
	mysqli_close($connection);
?>