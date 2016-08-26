<?php
function displayTime($time) {
	$h = $time['h'];
	$m = $time['m'];	
	$str = $h . ":";
	if ($m < 10) {
		$str .= "0";
	}
	$str .= $m;
	return $str;
}

function addTime($time1,$time2) {
	$time3 = array();
	$time3['h'] = $time1['h'] + $time2['h'];
	$time3['m'] = $time1['m'] + $time2['m'];
	while ($time3['m'] >= 60) {
		$time3['h']++;
		$time3['m'] -= 60;
	}
	if ($time3['h'] >= 24) {
		// just in case
		$time3['h'] -= 24;
	}
	return $time3;
}

$shortlabels = array();
$shortlabels['Arts & Culture'] = 'artsculture';
$shortlabels['Food'] = 'food';
$shortlabels['Fun & Entertainment'] = 'fun';
$shortlabels['Shopping'] = 'shopping';
$shortlabels['Nature'] = 'nature';
$shortlabels['Sights'] = 'sights';
$shortlabels['Hotel'] = 'hotel';

$displayLabels = array();
$displayLabels['artsculture'] = "Arts &amp; Culture";
$displayLabels['food'] = "Food";
$displayLabels['restaurant'] = "Food";
$displayLabels['fun'] = "Fun &amp; Entertainment";
$displayLabels['shopping'] = "Shopping";
$displayLabels['nature'] = "Nature";
$displayLabels['sights'] = "Sights";
$displayLabels['hotel'] = "Hotel";

function hotelHTML($timestr,$hotel_id,$name,$address,$description,$photo) {
	$returnHTML = 
	"
	<section class='row stop'>
	<div class='col-sm-9 details'>
	<div class='row'>
	<div class='col-xs-3 col-sm-1 time-outer'><div class='time'>$timestr</div></div><!-- time-outer -->
	<div class='col-xs-9 col-sm-3 col-sm-offset-0 col-sm-push-8 label-outer'>
	<a class='label label-hotel'>Hotel</a>
	</div><!-- label-outer -->
	<div class='col-xs-12 col-sm-8 col-sm-pull-3 location'>
	<h5>$name</h5>
	<p class='address'><img src='img/location-pin.png' class='icon' alt='address' /> $address</p>
	</div><!-- location -->
	</div><!-- row -->
	<div class='row'>
	<div class='col-xs-1'><img src='img/description.png' class='icon' alt='description' /></div>
	<p class='col-xs-9'>$description</p>
	<div class='col-xs-2 actions'>
	<a href = '?removehotel=$hotel_id'  role='button' tab-index='0' class='glyphicon glyphicon-remove' data-toggle='tooltip' title='Remove'></a>
	</div><!-- actions -->
	</div><!-- row -->

	</div><!-- details -->
	<div class='col-sm-3 photo'>
	";
	if ($photo != '') {
		$returnHTML .= "<img src='$photo' class='img-responsive img-thumbnail'/>";
	}	
	$returnHTML .=
	"</div> 
	</section><!-- row stop -->
	";
	return $returnHTML;
}

function attractionHTML($node_info,$timestr) {
	global $shortlabels;
	global $displayLabels;

	$node_id = $node_info['node_id'];
	$name = $node_info['name'];
	
	$labels = $node_info['category'];
	$subcategory = $node_info['subcategory'];
	$address = $node_info['address'];
	$description = $node_info['description'];
	$tip = $node_info['tip'];
	$duration = $node_info['duration']; // in hours
	
	
	$hasMeal = $node_info['hasMeal'];
	$meal_id = $node_info['meal_id'];
	$photo = $node_info['photo'];

	if ($duration ==0){
		$durationstr="";
	} else if ($duration < 1) {
		$durationstr = $duration * 60;
		$durationstr .= ' mins';
	} else {
		$durationstr = $duration;
		$durationstr = $duration . ' hour';
		if ($duration != 1) {
			$durationstr .= 's';
		}
	}

	$labelsHTML = '';
	$labels = explode('|',$labels);
	foreach ($labels as $label) {
		$label = trim($label);
		// restaurants stored in database have label 'restaurant'
		if ($label == 'restaurant') { $label = 'food';}
		$displayLabel = $displayLabels[$label];
		$labelsHTML .= "<a class='label label-$label'>$displayLabel</a>";									
	}
	$tipHTML = ($tip == '') ? '' :
		"	
		<div class='row'>
		<div class='col-xs-1'>
			<img src='img/light-bulb.jpg' class='icon' alt='tip' />
		</div>
		<p class='col-xs-11'>$tip</p>
		</div><!-- row -->
		";
	$eatHTML = '';
	if ($hasMeal) {
		$meal_word = 'meal';
		if ($meal_id == 'L') {
			$meal_word = 'lunch';
		} else if ($meal_id == 'D') {
			$meal_word = 'dinner';
		}
		$eatHTML = 
		"
		<div class='row'>
		<div class='col-xs-1'>
			<img src='img/light-bulb.jpg' class='icon' alt='meal' />
		</div>
		<p class='col-xs-11'>
		<a data-toggle='collapse' href='#carousel-$meal_id' aria-expanded='false' aria-controls='carousel-$meal_id'>
		Click here to see suggestions for $meal_word!
		</a>
		</p>
		</div><!-- row -->
		";
	}
	$photoHTML = ($photo == '') ? '' : 
		"<img src='$photo' class='img-responsive img-thumbnail'/>";

	$name_no_space = urlencode($name); 
	
	
	$duration_touch_spin = "<input type='text' name='touchspin' data-nodeid='$node_id' value='$duration'>";
	
	// $j = floor($duration*4);
	// $duraction_select_list="";
	// for($i =0;$i<=$j+2;$i+=1){
	// 	$period = $i*15;
	// 	if ($i==$j){
			
	// 	$duraction_select_list .= "<option value='$period' selected='selected'>$period mins</option>";
			
	// 	}else{
	// 		$duraction_select_list .= "<option value='$period' >$period mins</option>";
	// 	}
	// }
	// $duraction_select_list = "<select name='CHANGETIME' id='CHANGETIME' data-nodeid='$node_id' >" . $duraction_select_list.  "</select>";


	
	$returnHTML = 
	"
	<section class='row stop'>
	<div class='col-sm-9 details'>
	<div class='row'>
	<div class='col-xs-3 col-sm-1 time-outer'>
		<div class='time'>
			<span class='fa-stack'>
			<a class='up' role='button' href='#' data-nodeid='$node_id'><i class='fa fa-caret-up fa-stack-1x'></i></a>
			<a class='down' role='button' href='#' data-nodeid='$node_id'><i class='fa fa-caret-down fa-stack-1x'></i></a>
			</span>
			$timestr
		</div>
	</div><!-- time-outer -->
	<div class='col-xs-9 col-sm-3 col-sm-offset-0 col-sm-push-8 label-outer'>
		$labelsHTML
	</div><!-- label-outer -->
	<div class='col-xs-12 col-sm-8 col-sm-pull-3 location'>
	<h5>$name</h5>
	<p class='address'><img src='img/location-pin.png' class='icon' alt='address' /> $address</p>
	</div><!-- location -->
	</div><!-- row -->
	<div class='row'>
	<div class='col-xs-1'><img src='img/description.png' class='icon' alt='description' /></div>
	<p class='col-xs-11'>$description</p>
	</div><!-- row -->
	$tipHTML
	<div class='row'>
	<div class='col-xs-1'><img src='img/clock.png' class='icon' alt='duration' /></div>
	<p class='col-xs-11'>$duration_touch_spin</p>
	</div><!-- row -->
	

	
	
	$eatHTML
	<div class='actions'>
	<a href='?likename=$name_no_space'  role='button' tab-index='0' class='glyphicon glyphicon-heart' data-toggle='tooltip' title='Favourite'></a>
	<a href = '?removename=$name_no_space'  role='button' tab-index='0' class='glyphicon glyphicon-remove' data-toggle='tooltip' title='Remove'></a>
	</div><!-- actions -->
	
	
	

	</div><!-- details -->
	<div class='col-sm-3 photo'>
	$photoHTML
	</div> 
	</section><!-- row stop -->
	";
	return $returnHTML;
} // function attractionHTML()

// this function display the html for restaurants options
// attractionHTML($timestr,$labels,$name,$address,$description,$tip,$duration,$photo)

// Input: $food_itin 's this meal information
// it should be an array of rows, where each row is an sql query result of restaurants
function restaurantHTML($food_info,$timestr,$lunch_or_dinner){
	//print_r($food_info);
	global $shortlabels;
	global $displayLabels;
	
	//$row = $food_info[1];
	//$name = $row["name"];
	//$timestr = displayTime($currenttime);
	$labels = 'food';
	//$address = $row["address"];
	//$description = 'food';
	//$tip = '';
	//$duration = 1; // in hours
	$photo = '';
	
	// if ($duration ==0){
	// 	$durationstr="";
	// } else if ($duration < 1) {
	// 	$durationstr = $duration * 60;
	// 	$durationstr .= ' mins';
	// } else {
	// 	$durationstr = $duration;
	// 	$durationstr = $duration . ' hour';
	// 	if ($duration != 1) {
	// 		$durationstr .= 's';
	// 	}
	// }
	
	// $returnHTML .= 
	// "
	// <section class='row stop'>
	// <div class='col-sm-9 details'>
	// <div class='row'>
	// <div class='col-xs-3 col-sm-1 time-outer'><div class='time'>$timestr</div></div><!-- time-outer -->
	// <div class='col-xs-9 col-sm-3 col-sm-offset-0 col-sm-push-8 label-outer'>
	// ";
	
	// $labels = explode('|',$labels);
	// foreach ($labels as $label) {
	// 	$label = trim($label);
	// 	$displayLabel = $displayLabels[$label];
	// 	$returnHTML .= "<a class='label label-$label'>$displayLabel</a>";									
	// }

	// $returnHTML .="
	// <div class='col-xs-1'><img src='img/clock.png' class='icon' alt='duration' /></div>
	// <p class='col-xs-8'>$durationstr</p>";

	// $returnHTML .=
	// "</div> 
	// </section><!-- row stop -->
	// ";

	$returnHTML = 
	"
	<section id='carousel-$lunch_or_dinner' class='carousel slide carousel-food collapse' data-ride='carousel' data-interval='false'>
	";

	
	$returnHTML .= "<div class='carousel-inner' role='listbox'>
	";
	
		
	
	for ($i=0; $i<count($food_info); $i++) {
		$row = $food_info[$i];
		$name = $row["name"];
		//$timestr = displayTime($currenttime);
		$labels = 'food';
		$address = $row["address"];
		$price_level = $row["price_level"];
		$cuisine_str = $row["cuisine_str"];
		$rating=$row["rating"];
		$num_reviews=$row["num_reviews"];
		$description = 'food';
		$tip = '';
		$duration = 1; // in hours
		$photo = '';
		$active = ($i==0) ? 'active' : '';
		$name_no_space = urlencode($name); 
		$addrestaurant = ($lunch_or_dinner==="D") ? "adddinner" :"addlunch";
		$returnHTML .= 
		"
		<div class='item $active'>
			<div class='row'>
				<div class='col-sm-9 details'>
					<h5>$name</h5>
					<p class='address'><img src='img/location-pin.png' class='icon' alt='address' /> $address</p>
					<p> Price level: $price_level, cuisine Type: $cuisine_str, Rating: $rating, Number of Reviews:  $num_reviews</p>
					<a href = '?$addrestaurant=$name_no_space' role='button' tab-index='0' class='glyphicon glyphicon-plus' data-toggle='tooltip' title='Add'></a>
				</div><!--.col-sm-9.details-->
				<div class='col-sm-3 photo'>
				<img class='img-responsive' src='img/dimsum.png' />
				</div><!--.col-sm-3.photo-->
			</div><!--.row-->
		</div><!--.item-->
		";
	}
	$returnHTML .= "</div><!--.carousel-inner-->";

	$returnHTML .= 
	"<ol class='carousel-indicators'>";
	for ($i=0; $i<count($food_info);$i++) {
		$returnHTML .= 
		"<li data-target='#carousel-$lunch_or_dinner' data-slide-to='$i'";
		$returnHTML .= ($i==0) ? " class='active'" : " ";
		$returnHTML .= "></li>";

	}
	$returnHTML .= "</ol><!--.carousel-indicators-->";

	$returnHTML .=
	"<!-- Controls -->
	  <a class='left carousel-control' href='#carousel-$lunch_or_dinner' role='button' data-slide='prev'>
	    <span class='glyphicon glyphicon-chevron-left' aria-hidden='true'></span>
	    <span class='sr-only'>Previous</span>
	  </a>
	  <a class='right carousel-control' href='#carousel-$lunch_or_dinner' role='button' data-slide='next'>
	    <span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
	    <span class='sr-only'>Next</span>
	  </a>";

	$returnHTML .= "</section><!--.carousel-$lunch_or_dinner-->";
	return $returnHTML;
}

function transportHTML($mode,$duration,$walkdist,$route,$towards,$from,$to,$stops,$agency_name,$agency_url) {
	if (strcasecmp($mode ,'walk') == 0 || strcasecmp($mode ,'walking') == 0) {
		$modeimg = 'img/walk.png';
	} else if (strcasecmp($mode ,'minibus') ==0) {
		$modeimg = 'img/bus.png';
	} else if (strcasecmp($mode ,'bus') ==0){
		$modeimg = 'img/bus.png';
	} else if (strcasecmp($mode ,'tram') ==0){
		$modeimg = 'img/tram.png';
	} else if (strcasecmp($mode ,'ferry') ==0){
		$modeimg = 'img/ferry.png';
	} else if (strcasecmp($mode ,'subway') ==0){
		$modeimg = 'img/rail.png';
	} else if (strcasecmp($mode ,'rail') ==0){
		$modeimg = 'img/rail.png';
	} else if (strcasecmp($mode ,'cable_car') ==0){
		$modeimg = 'img/cablecar.png';
	} else if (strcasecmp($mode ,'gondola_lift') ==0){
		$modeimg = 'img/cablecar.png';
	} else { $modeimg = 'img/walk.png';   }
	
	if ($walkdist == 0){ $walkdiststr="";}
	else if ($walkdist >= 1000) {
		$walkdiststr = ", " .($walkdist/1000) . ' km';
	} else {
		$walkdiststr = ", " . $walkdist . ' m';
	}
	$returnHTML = 
	"
	<section class='row trans'>
	<div class='col-sm-9'>
	<div class='row'>
	<div class='col-sm-11 col-sm-offset-1 trans-inner'>
	<img src='$modeimg' class='icon' alt='$mode' />
	";
	if ($mode === 'walk' || $mode ==='WALKING') {
		$returnHTML .= $duration . " min" . $walkdiststr;
	} else {
		$returnHTML .=
		"<a role='button' tabindex='0' data-toggle='popover' title='$route";
		if ($towards != '') {
			$returnHTML .= "<br><small>towards $towards</small>";
		}
		$returnHTML .= "'";
		if (($from != '') || ($to != '')) {
			$returnHTML .= " data-content='";
			if ($from != '') {
				$returnHTML .= "From: $from";
				if ($to != '') {
					$returnHTML .= "<br>";
				}
			}	
			if ($to != '') {
				$returnHTML .= "To: $to";
			}
			$returnHTML .= "'";
		}
		
		
		
		$returnHTML .= ">";
		$returnHTML .= "$duration min, ";
		if ($stops > 0) {
			$returnHTML .= "$stops stop";
			if ($stops > 1) {
				$returnHTML .= "s";
			}
		} else {
			$returnHTML .= "non-stop";
		}
		$returnHTML .= "</a>";
		if ($agency_name != '') {
			$returnHTML .= " <span class='agencyinfo'>information provided by <a href='$agency_url' target='_blank'> $agency_name</a></span>";
		}
	}

	$returnHTML .=
	"			</div><!-- trans-inner -->
	</div><!-- row -->
	</div><!-- col -->
	</section><!-- row transport -->
	";

	return $returnHTML;
} // transportHTML()

// use an array of parameters 
function transportHTMLarray($modestr,$durationstr,$walkdiststr,$routestr,$routeshortstr,$towardsstr,$fromstr,$tostr,$stopsstr,$agency_namestr,$agency_urlstr) {
	// explode pipe separated string
	$modea = explode("|",$modestr);
	$durationa = explode("|",$durationstr);
	$walkdista = explode("|",$walkdiststr);
	$routea = explode("|",$routestr);
	$routeshorta = explode("|",$routeshortstr);
	
	
	$towardsa = explode("|",$towardsstr);
	$froma = explode("|",$fromstr);
	$toa = explode("|",$tostr);
	$stopsa = explode("|",$stopsstr);
	$agency_namea = explode("|",$agency_namestr);
	$agency_urla = explode("|",$agency_urlstr);
	$returnHTML = "";

	if ($modestr == 'transit') {
		return transportHTML($modestr,$durationstr,$walkdiststr,$routestr,$towardsstr,$fromstr,$tostr,$stopsstr,$agency_namestr,$agency_urlstr);
	} else {	
		$returnHTML .= "<div class='trans-multi'>";
		for($i=0;$i<count($modea);$i+=1){
			$mode = "";
			$duration = 0;
			$walkdist=0;
			$route="";
			$towards="";
			$from="";
			$to="";
			$stops=0;
			$agency_name="";
			$agency_url="";
			if (count($modea)>$i) {$mode = $modea[$i];}
			if (count($durationa)>$i) {$duration = $durationa[$i];}
			if (count($walkdista)>$i) {$walkdist = $walkdista[$i];}
			
			if (count($routeshorta)>$i) {$route = $routeshorta[$i];}
			
			if ( ( count($routea)>$i) and ($route==="")){ $route = $routea[$i]; } 
			
			if (count($towardsa)>$i) {$towards = $towardsa[$i];}
			if (count($froma)>$i) {$from = $froma[$i];}
			if (count($toa)>$i) {$to = $toa[$i];}
			if (count($stopsa)>$i) {$stops = $stopsa[$i];}
			if (count($agency_namea)>$i) {$agency_name = $agency_namea[$i];}
			if (count($agency_urla)>$i) {$agency_url = $agency_urla[$i];}
			
			$returnHTML .= transportHTML($mode,$duration,$walkdist,$route,$towards,$from,$to,$stops,$agency_name,$agency_url);
		}
		$returnHTML .= "</div><!-- .trans-multi -->";
		return $returnHTML;
	}
} // transportHTMLarray()

	// add an edge with travel mode $mode to graph database
    // if the edge already exists, then return that edge_id
    // else, add that edge to the database, and return the edge_id of the added edge

	// with $mode, search for an edge with appropriate $mode
	// if there is, then return that one
	// else, return an edge with a default $travel_mode setting
	// assume either is walk or transit
	function addEdgeDB($start_point,$end_point,$mode){
		global $connection;
		$start_point_escape = mysqli_real_escape_string($connection,$start_point);
		$end_point_escape = mysqli_real_escape_string($connection,$end_point);
		
		//check whether edge exists, only check the first such edge

		if ($mode == "walk"){
			$sql = "SELECT * from graph_hk_ta where start_point=\"$start_point_escape\" and end_point = \"$end_point_escape\" and travel_mode =\"walk\" limit 1";}
			// anything not "walk" is interpreted as "transit"
		else {
			$sql = "SELECT * from graph_hk_ta where start_point=\"$start_point_escape\" and end_point = \"$end_point_escape\"  and travel_mode !=\"walk\" limit 1";
		}

		// $sql = "SELECT * from graph_hk_ta where start_point=\"$start_point_escape\" and end_point = \"$end_point_escape\" limit 1";
		$result = mysqli_query($connection, $sql);
		if ($row = mysqli_fetch_assoc($result)){		
			return $row["edge_id"];	
			echo "<script> console.log('return edge  id :". $row["edge_id"] ."');</script>";
		}
		// else there is no such edge
		$sql = 
		"INSERT INTO graph_hk_ta 
				(start_point,end_point)
			VALUES 
				(\"$start_point_escape\",\"$end_point_escape\");
			";	
		if (mysqli_query($connection, $sql)) {
			//echo "<p style='color:Green;'>New record created successfully =)</p>";
			echo "<script> console.log('addEdge: New records created successfully =)');</script>";
		} else {
			echo "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
		}
		$sql = "SELECT edge_id from graph_hk_ta where start_point=\"$start_point_escape\" and end_point = \"$end_point_escape\"  and travel_mode =\"\" order by edge_id DESC limit 1";

		$result = mysqli_query($connection, $sql);		
		$row = mysqli_fetch_assoc($result);
		echo "<script> console.log('created edge  id :". $row["edge_id"] ."');</script>";
		return $row["edge_id"];
	}

// use Google Directions API to ask about travel direction between to coordinates
// returns an object in the form that we use to store in our database
function googleDirectionsAPI($start_lat,$start_long,$end_lat,$end_long) {
	global $connection;
	// get current date
	$departure_time = floor(time()/(24*60*60))*24*60*60;
	// get UTC 1am, i.e. HK 9am the next day
	$departure_time +=24*60*60+ 60*60;
	$api_key = "AIzaSyBZVvRfiXGmfdIuEjTKLB6fyGLcBgyyhDY";
	$travel_mode_main = 'transit';
	$url = "https://maps.googleapis.com/maps/api/directions/json?origin=$start_lat,$start_long&destination=$end_lat,$end_long&mode=$travel_mode_main&departure_time=$departure_time&key=$api_key";
	$data = file_get_contents($url);
	$json_a = json_decode($data,true);
	//d($json_a);
	if ($json_a["status"] !="OK") {return;}
	// select first route and first leg
	$totaltime = $json_a['routes'][0]['legs'][0]['duration']['value'];
	$totaltime = ceil($totaltime/60); // in minutes
	$steps = $json_a['routes'][0]['legs'][0]['steps']; 
	//d($steps);

	$t_mode_array = array();
	$t_route_array = array();
	$t_route_s_array = array();
	$t_stops_array = array();
	$t_towards_array = array();
	$t_from_array = array();
	$t_to_array = array();
	$a_name_array = array();
	$a_url_array = array();
	$dur_array = array();
	$dist_array = array();

	foreach($steps as $step) {
		$t_mode = $step['travel_mode'];
		$t_route = '';
		$t_route_s = '';
		$t_stops = '';
		$t_towards = '';
		$t_from = '';
		$t_to = '';
		$a_name = '';
		$a_url = '';
		$dur = ceil($step['duration']['value']/60); // in minutes
		$dist = '';
		if ($t_mode == 'WALKING') {
			$dist = $step['distance']['value']; // in meters
		} else if ($t_mode == 'TRANSIT') {	
			$t_details = $step['transit_details'];
			$t_mode = $t_details['line']['vehicle']['type'];
			$t_route = $t_details['line']['name'];
			$t_route_s = $t_details['line']['short_name'];
			$t_stops = $t_details['num_stops'];
			$t_towards = $t_details['headsign'];
			$t_from = $t_details['departure_stop']['name'];
			$t_to = $t_details['arrival_stop']['name'];
			$a_name = $t_details['line']['agencies'][0]['name'];
			$a_url = $t_details['line']['agencies'][0]['url'];
		}
		$t_mode_array[] = str_replace("'","&rsquo;",$t_mode);
		$t_route_array[] = str_replace("'","&rsquo;",$t_route);
		$t_route_s_array[] = str_replace("'","&rsquo;",$t_route_s);
		$t_stops_array[] = str_replace("'","&rsquo;",$t_stops);
		$t_towards_array[] = str_replace("'","&rsquo;",$t_towards);
		$t_from_array[] = str_replace("'","&rsquo;",$t_from);
		$t_to_array[] = str_replace("'","&rsquo;",$t_to);
		$a_name_array[] = str_replace("'","&rsquo;",$a_name);
		$a_url_array[] = str_replace("'","&rsquo;",$a_url);
		$dur_array[] = str_replace("'","&rsquo;",$dur);
		$dist_array[] = str_replace("'","&rsquo;",$dist);
	}
	$returnObj = array();
	$returnObj['totaltime'] = $totaltime;
	$returnObj['mode'] = implode('|',$t_mode_array);
	$returnObj['route'] = implode('|',$t_route_array);
	$returnObj['route_s'] = implode('|',$t_route_s_array);
	$returnObj['stops'] = implode('|',$t_stops_array);
	$returnObj['towards'] = implode('|',$t_towards_array);
	$returnObj['from'] = implode('|',$t_from_array);
	$returnObj['to'] = implode('|',$t_to_array);
	$returnObj['a_name'] = implode('|',$a_name_array);
	$returnObj['a_url'] = implode('|',$a_url_array);
	$returnObj['dur'] = implode('|',$dur_array);
	$returnObj['dist'] = implode('|',$dist_array);
	return $returnObj;
}

// use google direction API to ask about travel direction 
	function updateEdgeDB($edge_id,$mode){
		//$mode = "transit";
		global $connection;
		$sql = "SELECT * from graph_hk_ta WHERE edge_id=$edge_id";
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$api_key = "AIzaSyBZVvRfiXGmfdIuEjTKLB6fyGLcBgyyhDY";
		//if ($row["travel_mode"] !== "transit"){ return;}
		//if ($row["travel_route_short_name"] !== ""){ return;}
		// assume that if travel_mode string is non-empty, then the edge contains all necessary information
		
		if ( ($mode == "walk") and ($row["travel_mode"] == "walk")) {return;}
		if ( ($mode == "transit") and ($row["travel_mode"] != "")) {return;}
		
		$travel_mode_main = "transit";
		if ($mode == "walk") {$travel_mode_main = "walking";}
		
		$start_point = $row["start_point"];
		$end_point = $row["end_point"];
		$start_point_escape = mysqli_real_escape_string($connection,$start_point);
		$end_point_escape = mysqli_real_escape_string($connection,$end_point);
		
		
		$sql = "SELECT * from attractions_hk_ta WHERE name=\"$start_point_escape\"";
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);		
		$start_lat = $row["latitude"];
		$start_long = $row["longitude"];
		
		$sql = "SELECT * from attractions_hk_ta WHERE name=\"$end_point_escape\"";
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$end_lat = $row["latitude"];
		$end_long = $row["longitude"];		
	
		// get current date
		$departure_time = floor(time()/(24*60*60))*24*60*60;
		// get UTC 1am, i.e. HK 9am the next day
		$departure_time +=24*60*60+ 60*60;
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=$start_lat,$start_long&destination=$end_lat,$end_long&mode=$travel_mode_main&departure_time=$departure_time&key=$api_key";
		echo "<script> console.log('queried edge_id =". $edge_id ."');</script>";
		$data = file_get_contents($url);
		//echo "<script> console.log('updated  edge_id =". $edge_id ."');</script>";
				
		$json_a = json_decode($data,true);
		
		if ($json_a["status"] !="OK"){
			// try using actual name of address
			$start_point_plus = str_replace(' ','+',$start_point).',hk';
			$end_point_plus = str_replace(' ','+',$end_point).',hk';					
								
			$url = "https://maps.googleapis.com/maps/api/directions/json?origin=$start_point_plus&destination=$end_point_plus&mode=$travel_mode_main&departure_time=$departure_time&key=$api_key";
			echo "<script> console.log('queried url =". $url ."');</script>";
			$data = file_get_contents($url);
			//echo "<script> console.log('updated  edge_id =". $edge_id ."');</script>";
				
		$json_a = json_decode($data,true);
			
		}
		// still not ok
		if ($json_a["status"] !="OK"){return;}
		
		$duration =ceil($json_a["routes"][0]["legs"][0]["duration"]["value"] /60);// return total duration in minutes
		$travel_distance =ceil($json_a["routes"][0]["legs"][0]["distance"]["value"] ); // distance in meter

		$travel_mode = "";
		$travel_route  = "";
		$travel_route_short_name = "";
		$travel_stops  = "";
		$travel_towards  = "";
		$travel_from  = "";
		$travel_to  = "";
		$agencies_name_str  = "";
		$agencies_url_str  = "";
		$travel_time_str = "";
		$walk_dist_str = "";
		
		
		// return a single instance of walking direction
		if ($mode == "walk"){	
			$travel_mode = "walk";
			$walk_dist_str= "$travel_distance";
			$travel_time_str = "$duration";
			$sql = "Update graph_hk_ta SET travel_mode='$travel_mode', travel_route='$travel_route', travel_stops ='$travel_stops', travel_towards ='$travel_towards', travel_to ='$travel_to', travel_from='$travel_from', agencies_name_str ='$agencies_name_str', agencies_url_str='$agencies_url_str', travel_time_str='$travel_time_str', travel_time =$duration, walk_dist='$walk_dist_str', travel_route_short_name='$travel_route_short_name'   where edge_id = $edge_id";

			echo "<script> console.log('" . $sql . "<br>"  ."');</script>";
			if (mysqli_query($connection, $sql)) {
				echo "<script> console.log(' New records created successfully ');</script>";
			} else {
				echo "<script> console.log('". "Error: " . $sql . "<br>"  ."');</script>";					
			}	
			return 0;
		}

		// include both walking and transit direction
		foreach( $json_a["routes"][0]["legs"][0]["steps"] as $tran_step){
			//print_r($tran_step);
			//echo "<br><br>";
			
			// changed it to including walking travelling method
			//$travel_distance = $tran_step["distance"]["value"];
			if (array_key_exists("transit_details",$tran_step)){
				//print_r( $tran_step["transit_details"]);
				$arrival_stop = $tran_step["transit_details"]["arrival_stop"]["name"];
				$departure_stop = $tran_step["transit_details"]["departure_stop"]["name"];
				$headsign  = $tran_step["transit_details"]["headsign"];
				$num_stops = $tran_step["transit_details"]["num_stops"];
				$route_name="";
				if (array_key_exists("name",$tran_step["transit_details"]["line"])){
					$route_name = $tran_step["transit_details"]["line"]["name"];// e.g. Chai Wan Station - Stanley Village
				}				
				$route_short_name="";
				if (array_key_exists("short_name",$tran_step["transit_details"]["line"])){
					$route_short_name = $tran_step["transit_details"]["line"]["short_name"];// e.g. 16x
				}
				$agencies_name = $tran_step["transit_details"]["line"]["agencies"][0]["name"];
				$agencies_url = $tran_step["transit_details"]["line"]["agencies"][0]["url"];
				$vehicle_name = $tran_step["transit_details"]["line"]["vehicle"]["name"];
				$vehicle_type = $tran_step["transit_details"]["line"]["vehicle"]["type"];
				$travel_time_duration = $tran_step["duration"]["value"];
				$travel_distance = $tran_step["distance"]["value"];
				
				$travel_mode .= "|" . $vehicle_type;
				$travel_route .= "|" . 	$route_name;
				$travel_route_short_name .= "|" . 	$route_short_name;
				$travel_stops .= "|" . $num_stops;
				$travel_towards .= "|" . $headsign;
				$travel_from .= "|" . $departure_stop;
				$travel_to .= "|" . $arrival_stop;
				$agencies_name_str .= "|" . $agencies_name;
				$agencies_url_str .= "|" . $agencies_url;
				$travel_time_str .= "|" .  ceil($travel_time_duration/60);//in minutes
				$walk_dist_str .= "|" . $travel_distance ;
				//echo " $arrival_stop, $departure_stop, $headsign, $route_name, $agencies_name, $agencies_url ,$vehicle_name, $vehicle_type <br>";
			}
			// travel mode is not transit, but is walking instead
			else {
				$arrival_stop = $tran_step["end_location"]["lat"]. "," .  $tran_step["end_location"]["lng"];
				$departure_stop = $tran_step["start_location"]["lat"]. "," .  $tran_step["start_location"]["lng"];
				//$headsign  = $tran_step["transit_details"]["headsign"];
				//$num_stops = $tran_step["transit_details"]["num_stops"];
				//$route_name = $tran_step["transit_details"]["line"]["name"];// e.g. Chai Wan Station - Stanley Village
				//$route_short_name = $tran_step["transit_details"]["line"]["short_name"];// e.g. 16x
				//$agencies_name = $tran_step["transit_details"]["line"]["agencies"][0]["name"];
				//$agencies_url = $tran_step["transit_details"]["line"]["agencies"][0]["url"];
				//$vehicle_name = $tran_step["transit_details"]["line"]["vehicle"]["name"];
				//$vehicle_type = $tran_step["transit_details"]["line"]["vehicle"]["type"];
				
				$travel_time_duration = $tran_step["duration"]["value"];
				$travel_distance = $tran_step["distance"]["value"];
				
				$travel_mode .= "|walk" ;
				$travel_route .= "|" ;
				$travel_route_short_name .= "|" ;
				$travel_stops .= "|" ;
				$travel_towards .= "|" ;
				$travel_from .= "|" . $departure_stop;
				$travel_to .= "|" . $arrival_stop;
				$agencies_name_str .= "|" ;
				$agencies_url_str .= "|" ;
				$travel_time_str .= "|" .  ceil($travel_time_duration/60);//in minutes
				$walk_dist_str .= "|" . $travel_distance ;					
				
			}
			//echo "<br><br>";
		}
		
		
		if (!($travel_mode === "")) {
			// remove leading delimiter, i.e. "|"
			$travel_mode = substr($travel_mode,1);
			$travel_route = substr($travel_route,1);
			$travel_route_short_name = substr($travel_route_short_name,1);
			$travel_stops = substr($travel_stops,1);
			$travel_towards = substr($travel_towards,1);
			$travel_from = substr($travel_from,1);
			$travel_to = substr($travel_to,1);
			$agencies_name_str = substr($agencies_name_str,1);
			$agencies_url_str = substr($agencies_url_str,1);
			$travel_time_str = substr($travel_time_str,1);
			$walk_dist_str = substr($walk_dist_str,1);
			
			// remove single quote
			$travel_mode=str_replace("'","",$travel_mode);
			$travel_route=str_replace("'","",$travel_route);
			$travel_stops=str_replace("'","",$travel_stops);
			$travel_towards=str_replace("'","",$travel_towards);
			$travel_from=str_replace("'","",$travel_from);
			$travel_to=str_replace("'","",$travel_to);
			$agencies_name_str=str_replace("'","",$agencies_name_str);
			$agencies_url_str=str_replace("'","",$agencies_url_str);
			$travel_time_str=str_replace("'","",$travel_time_str);
			$walk_dist_str=str_replace("'","",$walk_dist_str);

			$sql = "Update graph_hk_ta SET travel_mode='$travel_mode', travel_route='$travel_route', travel_stops ='$travel_stops', travel_towards ='$travel_towards', travel_to ='$travel_to', travel_from='$travel_from', agencies_name_str ='$agencies_name_str', agencies_url_str='$agencies_url_str', travel_time_str='$travel_time_str', travel_time =$duration, walk_dist='$walk_dist_str', travel_route_short_name='$travel_route_short_name'   where edge_id = $edge_id";

				echo "<script> console.log('" . $sql . "<br>"  ."');</script>";
			if (mysqli_query($connection, $sql)) {
				echo "<script> console.log(' New records created successfully ');</script>";
			} else {
				echo "<script> console.log('". "Error: " . $sql . "<br>"  ."');</script>";					
			}		
	}	
	
}

// given an N day finished itinerary, put hotel at start and end of itinerary for each day
function tagHotel($hotel_name){
	global $itin_node_ids,$itin_edge_ids,$connection,$days;
	$sql = "SELECT * from attractions_hk_ta WHERE name =\"$hotel_name\"";
	$result = mysqli_query($connection, $sql);
	$row = mysqli_fetch_assoc($result);
	$hotel_id =  $row["node_id"];
	
	for($x=1;$x<=$days;$x+=1){
		if (count($itin_node_ids[$x])>0){
			$sql = "SELECT * from attractions_hk_ta WHERE node_id=".  $itin_node_ids[$x][0] ;
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$end_point_name1 = $row["name"];
			
			
			$sql = "SELECT * from graph_hk_ta WHERE start_point = \"$hotel_name\" AND end_point =\"" . $end_point_name1 . "\" order by travel_time limit 1";
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$start_edge_id = $row["edge_id"];
			
			$outputtext = "<script> console.log('tag hotel start $days ". $sql . "');</script>";
			echo 	$outputtext ;

			$sql = "SELECT * from attractions_hk_ta WHERE node_id=".  $itin_node_ids[$x][count($itin_node_ids[$x])-1] ;
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$end_point_name2 = $row["name"];
			
			$sql = "SELECT * from graph_hk_ta WHERE end_point = \"$hotel_name\" AND start_point =\"" . $end_point_name2 . "\" order by travel_time limit 1";
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$last_edge_id = $row["edge_id"];			
			$itin_edge_ids[$x] = array_merge(array($start_edge_id),$itin_edge_ids[$x],array($last_edge_id));
			$itin_node_ids[$x] = array_merge(array($hotel_id),$itin_node_ids[$x],array($hotel_id));
			$outputtext = "<script> console.log('tag hotel end $days ". $sql . "');</script>";
			echo 	$outputtext ;			
			
			$outputtext = "<script> console.log('itin_node_ids[$x] ". implode( ',' , $itin_node_ids[$x] ) . "');</script>";
			echo 	$outputtext ;
			$outputtext = "<script> console.log('itin_edge_ids[$x] ". implode( ',' , $itin_edge_ids[$x] ) . "');</script>";
			echo 	$outputtext ;
		}
	}
}

// given start point and end point, update database if necessary, and return an appropriate edge
function get_edge($start_point,$end_point){
		global $connection;
	
		$sql = "Select * from graph_hk_ta where travel_mode =\"walk\"  and start_point=\"". mysqli_real_escape_string($connection,$start_point)."\" and end_point =\"" . mysqli_real_escape_string($connection,$end_point) . "\"  order by travel_time"; 
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		
		if (is_null($row)){
			$walk_edge_id=addEdgeDB($start_point,$end_point,"walk");
			updateEdgeDB($walk_edge_id,"walk");
			$sql = "Select * from graph_hk_ta where edge_id =$walk_edge_id";
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
		}
		$walk_time = $row["travel_time"];
		$walk_edge_id = $row["edge_id"];
		
		// if walk edge is too long, then search/add transit edge
		if ($walk_time>20) {
			$sql = "Select * from graph_hk_ta where start_point=\"". mysqli_real_escape_string($connection,$start_point)."\" and end_point =\"" . mysqli_real_escape_string($connection,$end_point) . "\"  and  travel_time>0 and travel_mode !=\"walk\" order by travel_time"; 
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			if (is_null($row)){
				$transit_edge_id=addEdgeDB($start_point,$end_point,"transit");
				updateEdgeDB($transit_edge_id,"transit");
				$sql = "Select * from graph_hk_ta where edge_id =$transit_edge_id";
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);				
			}
			$transit_time = $row["travel_time"];
			$transit_edge_id = $row["edge_id"];
			return $transit_edge_id;
		} else {
			return $walk_edge_id;
		}

		$outputtext = "<script> console.log('itin_edge_ids[$this_day] sql: ". $sql . "');</script>";
		echo 	$outputtext ;
		
		$outputtext = "<script> console.log('row[edge_id]: ". $row["edge_id"]. "');</script>";
		echo 	$outputtext ;
	
}




?>