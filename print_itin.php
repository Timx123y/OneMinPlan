<?php
// print_itin.php

include_once 'functions.php';
include_once 'ta_functions.php';	
//include 'kint/Kint.class.php';
session_start();

include 'fetchSession.php';

// check if request is UP, DOWN, or CHANGE_TIME



$hasAlert=$_SESSION['hasAlert'] ;

if ($hasAlert){
$alertType=$_SESSION['alertType'];
$alertMsg=$_SESSION['alertMsg'];
}





if (array_key_exists("UP",$_REQUEST)){

	$UP_node = $_REQUEST["UP"];
	// move that node up
	// search for that node
	foreach($itin_node_ids as $that_day=>$this_day_node_ids){
		if ( array_search($UP_node,$this_day_node_ids)!==False){
			$node_day=$that_day;
			$node_order=array_search($UP_node,$this_day_node_ids);
		}
	}

	//$node_day=1;
	//$node_order =3;// 0 indexed
	//if node_order ==0,  do nothing
	
	// for an edge B-food-C, (B-food) has edge id -1, food -C has edge id (B-C)
	//(food-food) has edge id -1
	if ($node_order>0){
		// look at node m2,m1,0,p1 (minus2,minus1,0,plus1)
				
		
		// swap node_order and (node_order-1)
		$temp = $itin_node_ids[$node_day][$node_order];
		$itin_node_ids[$node_day][$node_order]=$itin_node_ids[$node_day][$node_order-1];
		$itin_node_ids[$node_day][$node_order-1]=$temp;

		// if id are not L or D
		$node_m1_id = $itin_node_ids[$node_day][$node_order-1];
		$node_0_id = $itin_node_ids[$node_day][$node_order];
		if ( $node_order>1){ $node_m2_id =$itin_node_ids[$node_day][$node_order-2];}
		if ( $node_order< count($itin_node_ids[$node_day])-1){$node_p1_id =$itin_node_ids[$node_day][$node_order+1];}
		
		if ( ($node_0_id==="D") or ($node_0_id ==="L")){
			$itin_edge_ids[$node_day][$node_order-1] = -1;			
		} elseif( ($node_m1_id==="D") or ($node_m1_id ==="L")){
			if ( $node_order>1) {
				if ( ($node_m2_id==="D") or ($node_m2_id ==="L")){
					$itin_edge_ids[$node_day][$node_order-1] = -1;	
				} else {
					$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m2_id";
					$result = mysqli_query($connection, $query);
					$row = mysqli_fetch_assoc($result);	
					$start_name =  $row["name"];
					
					$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
					$result = mysqli_query($connection, $query);
					$row = mysqli_fetch_assoc($result);	
					$end_name =  $row["name"];	
					$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);					
					
				}
			} else {$itin_edge_ids[$node_day][$node_order-1] = -1;	}
			
		} else{
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m1_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$start_name =  $row["name"];
			
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$end_name =  $row["name"];	
			$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);				
		}
		
		
		
		/*
		$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $start_id";
		$result = mysqli_query($connection, $query);
		$row = mysqli_fetch_assoc($result);	
		$start_name =  $row["name"];
		
		$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $end_id";
		$result = mysqli_query($connection, $query);
		$row = mysqli_fetch_assoc($result);	
		$end_name =  $row["name"];	
		$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);
		*/
		
		// if node_order>1, so there is a node at order-2
		if ( $node_order>1){
			if ( ($node_m1_id==="D") or ($node_m1_id ==="L")){
				$itin_edge_ids[$node_day][$node_order-2] = -1;			
			} elseif( ($node_m2_id==="D") or ($node_m2_id ==="L")){
				if ( $node_order>2) {
					$node_m3_id=$itin_node_ids[$node_day][$node_order-3];
					
					if ( ($node_m3_id==="D") or ($node_m3_id ==="L")){
						$itin_edge_ids[$node_day][$node_order-2] = -1;	
					} else {
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m3_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$start_name =  $row["name"];
						
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m1_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$end_name =  $row["name"];	
						$itin_edge_ids[$node_day][$node_order-2] = get_edge($start_name,$end_name);					
						
					}
				} else {$itin_edge_ids[$node_day][$node_order-2] = -1;	}
				
			} else{
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m2_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$start_name =  $row["name"];
				
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m1_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$end_name =  $row["name"];	
				$itin_edge_ids[$node_day][$node_order-2] = get_edge($start_name,$end_name);				
			}
		
			/*
			$start_id = $itin_node_ids[$node_day][$node_order-2];
			$end_id = $itin_node_ids[$node_day][$node_order-1];
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $start_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$start_name =  $row["name"];
			
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $end_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$end_name =  $row["name"];	
			$itin_edge_ids[$node_day][$node_order-2] = get_edge($start_name,$end_name);	
			*/
		}
		
		// if node_order<N-1, i.e. not the last node, then there is a node at order +1
		if ( $node_order< count($itin_node_ids[$node_day])-1){
			if ( ($node_p1_id==="D") or ($node_p1_id ==="L")){
				$itin_edge_ids[$node_day][$node_order] = -1;			
			} elseif( ($node_0_id==="D") or ($node_0_id ==="L")){
				if ( $node_order>0) {
					
					
					if ( ($node_m1_id==="D") or ($node_m1_id ==="L")){
						$itin_edge_ids[$node_day][$node_order] = -1;	
					} else {
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m1_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$start_name =  $row["name"];
						
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p1_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$end_name =  $row["name"];	
						$itin_edge_ids[$node_day][$node_order] = get_edge($start_name,$end_name);					
						
					}
				} else {$itin_edge_ids[$node_day][$node_order] = -1;	}
				
			} else{
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$start_name =  $row["name"];
				
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p1_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$end_name =  $row["name"];	
				$itin_edge_ids[$node_day][$node_order] = get_edge($start_name,$end_name);				
			}
			
			/*
			$start_id = $itin_node_ids[$node_day][$node_order];
			$end_id = $itin_node_ids[$node_day][$node_order+1];
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $start_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$start_name =  $row["name"];
			
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $end_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$end_name =  $row["name"];	
			$itin_edge_ids[$node_day][$node_order] = get_edge($start_name,$end_name);		
			*/
		}
	}
}

if (array_key_exists("DOWN",$_REQUEST)){

	$DOWN_node = $_REQUEST["DOWN"];
	// move that node up
	// search for that node
	foreach($itin_node_ids as $that_day=>$this_day_node_ids){
		if ( array_search($DOWN_node,$this_day_node_ids)!==False){
			$node_day=$that_day;
			$node_order=array_search($DOWN_node,$this_day_node_ids);
		}
	}
	
	//$node_day=1;
	//$node_order =3;// 0 indexed
	//if node_order ==0,  do nothing
	
	// for an edge B-food-C, (B-food) has edge id -1, food -C has edge id (B-C)
	//(food-food) has edge id -1
	if ( $node_order< count($itin_node_ids[$node_day])-1){
		// look at node m2,m1,0,p1 (minus2,minus1,0,plus1)
				
		
		// swap node_order and (node_order-1)
		$temp = $itin_node_ids[$node_day][$node_order];
		$itin_node_ids[$node_day][$node_order]=$itin_node_ids[$node_day][$node_order+1];
		$itin_node_ids[$node_day][$node_order+1]=$temp;

		// if id are not L or D
		$node_p1_id = $itin_node_ids[$node_day][$node_order+1];
		$node_0_id = $itin_node_ids[$node_day][$node_order];
		if ( $node_order>0){ $node_m1_id =$itin_node_ids[$node_day][$node_order-1];}
		if ( $node_order< count($itin_node_ids[$node_day])-2){$node_p2_id =$itin_node_ids[$node_day][$node_order+2];}
		
		if ( ($node_p1_id==="D") or ($node_p1_id ==="L")){
			$itin_edge_ids[$node_day][$node_order] = -1;			
		} elseif( ($node_0_id==="D") or ($node_0_id ==="L")){
			if ( $node_order>0) {
				if ( ($node_m1_id==="D") or ($node_m1_id ==="L")){
					$itin_edge_ids[$node_day][$node_order] = -1;	
				} else {
					$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m1_id";
					$result = mysqli_query($connection, $query);
					$row = mysqli_fetch_assoc($result);	
					$start_name =  $row["name"];
					
					$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p1_id";
					$result = mysqli_query($connection, $query);
					$row = mysqli_fetch_assoc($result);	
					$end_name =  $row["name"];	
					$itin_edge_ids[$node_day][$node_order] = get_edge($start_name,$end_name);					
					
				}
			} else {$itin_edge_ids[$node_day][$node_order] = -1;	}
			
		} else{
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$start_name =  $row["name"];
			
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p1_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$end_name =  $row["name"];	
			$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);				
		}
		
		
		
		/*
		$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $start_id";
		$result = mysqli_query($connection, $query);
		$row = mysqli_fetch_assoc($result);	
		$start_name =  $row["name"];
		
		$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $end_id";
		$result = mysqli_query($connection, $query);
		$row = mysqli_fetch_assoc($result);	
		$end_name =  $row["name"];	
		$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);
		*/
		
		// if node_order>0, so there is a node at order-1
		if ( $node_order>0){
			if ( ($node_0_id==="D") or ($node_0_id ==="L")){
				$itin_edge_ids[$node_day][$node_order-1] = -1;			
			} elseif( ($node_m1_id==="D") or ($node_m1_id ==="L")){
				if ( $node_order>1) {
					$node_m2_id=$itin_node_ids[$node_day][$node_order-2];
					
					if ( ($node_m2_id==="D") or ($node_m2_id ==="L")){
						$itin_edge_ids[$node_day][$node_order-1] = -1;	
					} else {
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m2_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$start_name =  $row["name"];
						
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$end_name =  $row["name"];	
						$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);					
						
					}
				} else {$itin_edge_ids[$node_day][$node_order-1] = -1;	}
				
			} else{
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_m1_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$start_name =  $row["name"];
				
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$end_name =  $row["name"];	
				$itin_edge_ids[$node_day][$node_order-1] = get_edge($start_name,$end_name);				
			}
		
			/*
			$start_id = $itin_node_ids[$node_day][$node_order-2];
			$end_id = $itin_node_ids[$node_day][$node_order-1];
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $start_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$start_name =  $row["name"];
			
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $end_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$end_name =  $row["name"];	
			$itin_edge_ids[$node_day][$node_order-2] = get_edge($start_name,$end_name);	
			*/
		}
		
		// if node_order<N-2, i.e. not the second last node, then there is a node at order +2
		if ( $node_order< count($itin_node_ids[$node_day])-2){
			if ( ($node_p2_id==="D") or ($node_p2_id ==="L")){
				$itin_edge_ids[$node_day][$node_order+1] = -1;			
			} elseif( ($node_p1_id==="D") or ($node_p1_id ==="L")){
				if ( $node_order>=0) {
					
					
					if ( ($node_0_id==="D") or ($node_0_id ==="L")){
						$itin_edge_ids[$node_day][$node_order+1] = -1;	
					} else {
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_0_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$start_name =  $row["name"];
						
						$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p2_id";
						$result = mysqli_query($connection, $query);
						$row = mysqli_fetch_assoc($result);	
						$end_name =  $row["name"];	
						$itin_edge_ids[$node_day][$node_order+1] = get_edge($start_name,$end_name);					
						
					}
				} else {$itin_edge_ids[$node_day][$node_order+1] = -1;	}
				
			} else{
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p1_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$start_name =  $row["name"];
				
				$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $node_p2_id";
				$result = mysqli_query($connection, $query);
				$row = mysqli_fetch_assoc($result);	
				$end_name =  $row["name"];	
				$itin_edge_ids[$node_day][$node_order+1] = get_edge($start_name,$end_name);				
			}
			
			/*
			$start_id = $itin_node_ids[$node_day][$node_order];
			$end_id = $itin_node_ids[$node_day][$node_order+1];
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $start_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$start_name =  $row["name"];
			
			$query = "SELECT * FROM attractions_hk_ta WHERE node_id = $end_id";
			$result = mysqli_query($connection, $query);
			$row = mysqli_fetch_assoc($result);	
			$end_name =  $row["name"];	
			$itin_edge_ids[$node_day][$node_order] = get_edge($start_name,$end_name);		
			*/
		}
	}
}

if (array_key_exists("CHANGENODE",$_REQUEST)){
		$CHANGENODE = $_GET["CHANGENODE"];
		$CHANGETIME = $_GET["CHANGETIME"];
		
	
	$_SESSION["user_duration_list"][$CHANGENODE]= $CHANGETIME/60;
}
	
	
// store the info back in $_SESSION

$_SESSION['itin_edge_ids'] = $itin_edge_ids;
$_SESSION['itin_node_ids']=$itin_node_ids;








// preparation 
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


$hasAlert = $_SESSION['hasAlert'] ;

			
// display any alerts
if ($hasAlert) {
	echo 
	"
	<div class='alert alert-$alertType alert-dismissible' role='alert'>
		<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
		$alertMsg
	</div>
	";
}

$currenttime = $starttime;
$timestr = displayTime($currenttime);
$hasHotel = (isset($_SESSION['hasHotel']) && $_SESSION['hasHotel'] && isset($hotelinfo) && !empty($hotelinfo));
if (!$hasHotel) 
{
	echo 
	"
	<p id='askhotel' class='alert alert-info role='alert'>
		<strong>Where are you staying?</strong>
		<a class='alert-link' id='showHotelModal' role='button' data-toggle='modal' data-target='#hotelModal'>Search for a hotel</a> 
		<i class='fa fa-info-circle'></i>
	</p>
	"
	;
} else {
	$hotel_id = $hotelinfo['hotel_id'];
	$name = $hotelinfo['name'];
	$address = "[Hotel address here]";
	$description = "[Hotel description here]";
	$photo = "";
	echo hotelHTML($timestr,$hotel_id,$name,$address,$description,$photo);
	$start_lat = $hotelinfo['latitude'];
	$start_long = $hotelinfo['longitude'];
	$node_id = $today_node_ids[0];
	$node = $today_nodes[0][$node_id];
	$end_lat = $node['latitude'];
	$end_long = $node['longitude'];
	// echo "<p>Start: ($start_lat,$start_long) End: ($end_lat,$end_long)</p>";
	$dir = googleDirectionsAPI($start_lat,$start_long,$end_lat,$end_long);
	//d($dir);
	echo transportHTMLarray($dir['mode'],$dir['dur'],$dir['dist'],$dir['route'],$dir['route_s'],$dir['towards'],$dir['from'],$dir['to'],$dir['stops'],$dir['a_name'],$dir['a_url']);
	$travel_time = $dir['totaltime']; // in minutes
	// Update currenttime
	$durationtime = array();
	$durationtime['h'] = 0;
	$durationtime['m'] = $travel_time;
	$currenttime = addTime($currenttime,$durationtime);

} 

$node_i = 0;
$edge_i = 0;	

while ($node_i < count($today_nodes)) {
	// Get info for current node
	$node_id = $today_node_ids[$node_i];
	$node = $today_nodes[$node_i][$node_id];
	$timestr = displayTime($currenttime);
	$hasMeal = false;
	if (($node_id==="D") or ($node_id==="L")){
		if ($node_id==="D") {$food_info = $food_itin[$today]["dinner_list"];}
		else {$food_info = $food_itin[$today]["lunch_list"];}
		$duration = 1; // in hours
		echo restaurantHTML($food_info,$timestr,$node_id);
	}
	else{
		$duration = $node['duration'];
		$duration = $_SESSION["user_duration_list"][$node_id];
		$node['duration']=$duration;						
		$next_node_id = ($node_i+1 < count($today_nodes)) ?
			$today_node_ids[$node_i+1] : '';
		if (($next_node_id == 'L') || ($next_node_id == 'D')) {
			$node['meal_id'] = $next_node_id;
			$node['hasMeal'] = true;
			$hasMeal = true;
		} else {
			$node['meal_id'] = '';
			$node['hasMeal'] = false;
		}
		echo attractionHTML($node,$timestr);
	}
	
	// Update currenttime
	$durationtime = array();
	$durationtime['h'] = 0;
	$durationtime['m'] = $duration * 60;
	$currenttime = addTime($currenttime,$durationtime);
	// Increment counter
	$node_i++;
	// Output subsequent edge(s)
	if ($edge_i < count($today_edges)) {
		do {
			$edge_id = $today_edge_ids[$edge_i];
			$edge = $today_edges[$edge_i][$edge_id];
			// if ($edge_id == -1){
			// 		$mode = 'walk';
			// 		$duration = 15; // in minutes
			// 		$travel_time_str = '15';
			// 		$walkdist = 0;
			// 		$route = '';
			// 		$routeshort = '';// ** changed to short name
			// 		$towards = '';
			// 		$from = '';
			// 		$to = '';
			// 		$stops = 0;
			// 		$agency_name = '';
			// 		$agency_url = '';				
			// }
			// else
			if ($edge_id != -1) {
				//$name = $edge['name'];
				$mode = $edge['travel_mode'];
				$duration = $edge['travel_time']; // in minutes
				$travel_time_str = $edge['travel_time_str'];
				$walkdist = $edge['walk_dist'];
				$route = $edge['travel_route'];
				$routeshort = $edge['travel_route_short_name'];// ** changed to short name
				$towards = $edge['travel_towards'];
				$from = $edge['travel_from'];
				$to = $edge['travel_to'];
				$stops = $edge['travel_stops'];
				$agency_name = $edge['agencies_name_str'];
				$agency_url = $edge['agencies_url_str'];
				echo transportHTMLarray($mode,$travel_time_str,$walkdist,$route,$routeshort,$towards,$from,$to,$stops,$agency_name,$agency_url);
				// Update currenttime
				$durationtime = array();
				$durationtime['h'] = 0;
				$durationtime['m'] = $duration;
				$currenttime = addTime($currenttime,$durationtime);
			}
			
			// Increment counter
			$edge_i++;	
		} while (($edge_id !== -1) and ($edge['transfer_end'])); // repeat if endpoint is transfer
	} // if ($edge_i < count($today_edges))
} // while ($node_i < count($today_nodes))
								// End with hotel
if ($hasHotel) {
	$start_lat = $node['latitude'];
	$start_long = $node['longitude'];
	// $node_id = $today_node_ids[0];
	// $node = $today_nodes[0][$node_id];
	$end_lat = $hotelinfo['latitude'];
	$end_long = $hotelinfo['longitude'];
	// echo "<p>Start: ($start_lat,$start_long) End: ($end_lat,$end_long)</p>";
	$dir = googleDirectionsAPI($start_lat,$start_long,$end_lat,$end_long);
	//d($dir);
	echo transportHTMLarray($dir['mode'],$dir['dur'],$dir['dist'],$dir['route'],$dir['route_s'],$dir['towards'],$dir['from'],$dir['to'],$dir['stops'],$dir['a_name'],$dir['a_url']);
	$travel_time = $dir['totaltime']; // in minutes
	// Update currenttime
	$durationtime = array();
	$durationtime['h'] = 0;
	$durationtime['m'] = $travel_time;
	$currenttime = addTime($currenttime,$durationtime);
	$timestr = displayTime($currenttime);
	$hotel_id = $hotelinfo['hotel_id'];
	$name = $hotelinfo['name'];
	$address = "[Hotel address here]";
	$description = "[Hotel description here]";
	$photo = "";
	echo hotelHTML($timestr,$hotel_id,$name,$address,$description,$photo);
	
}		

?>