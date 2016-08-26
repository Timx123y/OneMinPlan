<?php
// gen_itin

// $starttime of start time expressed in minutes after midnight
// $itin_node_id is an array of POI(s) ID
// $itin_edge_id is an array of edge(s) ID connecting POI(s)
function getItinTime($itin_node_id,$itin_edge_id){
	global $connection;
	$totaltime=0;	

	$itin_node_id_str =  implode(',', array_values(array_diff($itin_node_id, array("D","L"))));

	if ($itin_node_id_str !== ""){
		$sql = "SELECT duration from attractions_hk_ta WHERE node_id IN ($itin_node_id_str)";
		//print $sql;
		$outputtext = "<script> console.log('time calculation, attractions". $sql. "');</script>";
		echo 	$outputtext ;
		$result = mysqli_query($connection, $sql);
				
		while($row = mysqli_fetch_assoc($result)) {
			$totaltime += $row["duration"]*60;		// duration counted in hours
		}

		// search for special node like D or L, if there is one, add 1hr to total time
		foreach($itin_node_id as $node_id){
			if (($node_id==="D")or ($node_id==="L")){$totaltime +=60;}
		}
	}
			
	$itin_edge_id_str = implode(',', array_values(array_diff($itin_edge_id,array(-1))));
	if ($itin_edge_id_str !== ""){
		$sql = "SELECT travel_time from graph_hk_ta WHERE edge_id IN (" . $itin_edge_id_str . ") ";
		$outputtext = "<script> console.log('gen_itin time calculation, edge(line 35): ". $sql. "');</script>";
		echo 	$outputtext ;
		$result = mysqli_query($connection, $sql);

		while($row = mysqli_fetch_assoc($result)) {
			$totaltime += $row["travel_time"]; // travel_time in minutes		
		}

		foreach($itin_edge_id as $edge_id){
			if ($edge_id === -1){$totaltime +=15;}
		}
	}
	return $totaltime;	
}
// end of function getItinTime

//////////////////////////////////////////////////////////////////////////////////////////////////////////
// return the time indexed by attraction name
// and it will ignore D(Dinner) and L(Lunch)
function compute_itin_time(){
	global $itin_node_ids,$itin_edge_ids;
	global $connection;
	global $starttimeh;
	global $attractions_info_by_id;
	global $previous_node_name;
	global $is_prev_node_restaurant;
	$totaltime=0;	
	
	$itin_time_array=array();
	$previous_node_name=array();
	$is_prev_node_restaurant=array();
	foreach ($itin_node_ids as $day=>$today_itin_node_ids){
		$today_itin_edge_ids = $itin_edge_ids[$day];
		// initialize start time for each day
		$current_time = $starttimeh*60;
		
	
		foreach($today_itin_node_ids as $index=>$current_point_id){
			
			
			if ($index ===0){ $previous_node_name[$current_point_id]="";  }
			if ($index>0){
				if (($today_itin_node_ids[$index]!=="D") and ($today_itin_node_ids[$index]!=="L")){
					if (($today_itin_node_ids[$index-1]!=="D") and ($today_itin_node_ids[$index-1]!=="L")){
						$this_node_id = $today_itin_node_ids[$index];
						$this_node_name = $attractions_info_by_id[$this_node_id]["name"];	
						$prev_node_id = $today_itin_node_ids[$index-1];
						$prev_node_name = $attractions_info_by_id[$prev_node_id]["name"];	
						$previous_node_name[$this_node_name] = $prev_node_name;
						$is_prev_node_restaurant[$this_node_name] = false;
					}
					else{
						if($index===1){
							$previous_node_name[$this_node_name] = $today_itin_node_ids[$index-1];
							$is_prev_node_restaurant[$this_node_name] = true;
						} else{
							$this_node_id = $today_itin_node_ids[$index];
							$this_node_name = $attractions_info_by_id[$this_node_id]["name"];	
							$prev_node_id = $today_itin_node_ids[$index-2];
							$prev_node_name = $attractions_info_by_id[$prev_node_id]["name"];	
							$previous_node_name[$this_node_name] = $prev_node_name;
							$is_prev_node_restaurant[$this_node_name] = true;
							
						}
						
					}
				}
				
				$this_edge_id = $today_itin_edge_ids[$index-1];
				
				if ($this_edge_id !==-1){
					$sql = "SELECT travel_time from graph_hk_ta WHERE edge_id = $this_edge_id " ;
					$outputtext = "<script> console.log('gen_itin time calculation, edge(line 67): ". $sql. "');</script>";
					echo 	$outputtext ;
					$result = mysqli_query($connection, $sql);
					$row = mysqli_fetch_assoc($result) ;
					$current_time += $row["travel_time"]; // travel_time in minutes		
				}
				else {	$current_time += 15;}
			}
			$this_node_id = $today_itin_node_ids[$index];

			if (($this_node_id !=="D") and ($this_node_id!=="L")){	
				$this_node_name = $attractions_info_by_id[$this_node_id]["name"];			
				$current_time += $attractions_info_by_id[$this_node_id]["duration"]*60;// duration in hour
				$itin_time_array[$this_node_name] = $current_time;
			}
			else {$current_time +=60;}
		}
	}
	return $itin_time_array;	
		
}
	

	
	

//////////////////////////////////////////////////////////////////////////////////////////////////////////
function get_distance($lat1,$long1,$lat2,$long2){
		$earthR=6371000;// radius in meter
		return $earthR * sqrt( pow(deg2rad($lat1-$lat2),2) +pow(cos($lat1)*deg2rad($long1-$long2),2  ));	
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////

// assign district based on current point distance to district central point
function assign_district($lat,$long){
	// conversion table
	$lat_a=array("Central and Western"=>22.279991,"Eastern"=>22.273389,"Islands"=>22.262792,"Kowloon City"=>22.33066,"Kwai Tsing"=>22.354908,"Kwun Tong"=>22.310369,"North"=>22.500908,"Sai Kung"=>22.383689,"Sha Tin"=>22.37713,"Sham Shui Po"=>22.32859,"Southern"=>22.243216,"Tai Po"=>22.442322,"Tsuen Wan"=>22.371323,"Tuen Mun"=>22.390829,"Wan Chai"=>22.276022,"Wong Tai Sin"=>22.342962,"Yau Tsim Mong"=>22.311603,"Yuen Long"=>22.444538);
	$long_a=array("Central and Western"=>114.158798,"Eastern"=>114.236078,"Islands"=>113.965542,"Kowloon City"=>114.192017,"Kwai Tsing"=>114.126099,"Kwun Tong"=>114.222703,"North"=>114.155826,"Sai Kung"=>114.270787,"Sha Tin"=>114.19744,"Sham Shui Po"=>114.160285,"Southern"=>114.19744,"Tai Po"=>114.165506,"Tsuen Wan"=>114.11416,"Tuen Mun"=>113.972513,"Wan Chai"=>114.175147,"Wong Tai Sin"=>114.192981,"Yau Tsim Mong"=>114.170688,"Yuen Long"=>114.022208);

	$distsq_min=100;
	$current_district="";
	// find nearest point and assign that to be this district
	foreach($lat_a as $district=>$this_lat){
		$this_long = $long_a[$district];
		$latmean = deg2rad(($lat+$this_lat)/2);
		$latdiff = deg2rad($lat-$this_lat);
		$longdiff = deg2rad($long-$this_long);
		$distsq = pow($latdiff,2) + pow(pow(cos($latmean),2)*$longdiff,2);
		if ($distsq<$distsq_min){
			$distsq_min=$distsq;
			$current_district=$district;
		}
	}
	return $current_district;
	
}
// end of function assign_district

// assign district into a few main categories
// main purpose is to categorized located on a continuous piece of land into one cat
// e.g. into New Territories, Kowloon, HK Island, 

// given a location, return an array of district in sorted order
function sort_district($this_json_district){
	// json_district, where all letters in lower case and space is replaced by underscore
	$lat_a=array("central_and_western"=>22.279991,"eastern"=>22.273389,"islands"=>22.262792,"kowloon_city"=>22.33066,"kwai_tsing"=>22.354908,"kwun_tong"=>22.310369,"north"=>22.500908,"sai_kung"=>22.383689,"sha_tin"=>22.37713,"sham_shui_po"=>22.32859,"southern"=>22.243216,"tai_po"=>22.442322,"tsuen_wan"=>22.371323,"tuen_mun"=>22.390829,"wan_chai"=>22.276022,"wong_tai_sin"=>22.342962,"yau_tsim_mong"=>22.311603,"yuen_long"=>22.444538);
	$long_a=array("central_and_western"=>114.158798,"eastern"=>114.236078,"islands"=>113.965542,"kowloon_city"=>114.192017,"kwai_tsing"=>114.126099,"kwun_tong"=>114.222703,"north"=>114.155826,"sai_kung"=>114.270787,"sha_tin"=>114.19744,"sham_shui_po"=>114.160285,"southern"=>114.19744,"tai_po"=>114.165506,"tsuen_wan"=>114.11416,"tuen_mun"=>113.972513,"wan_chai"=>114.175147,"wong_tai_sin"=>114.192981,"yau_tsim_mong"=>114.170688,"yuen_long"=>114.022208);
	
	// look up	
	$district_cat=array();
	$district_cat["hk_island"]=array("wan_chai","eastern","central_and_western","southern");
	$district_cat["outlying_islands"]=array("islands");
	$district_cat["kowloon"]= array("kowloon_city","yau_tsim_mong","sham_shui_po","wong_tai_sin","kwun_tong");
	$district_cat["new_territories"] = array("yuen_long","tuen_mun","tsuen_wan","tai_po","sha_tin","sai_kung","north","kwai_tsing");
	
	//reverse look up
	$district_rev_table=array();
	foreach ($district_cat as $big_district=>$dist_array){
		foreach($dist_array as $dist_name){
			$district_rev_table[$dist_name]=$big_district;
		}		
	}
	// the ordered list of big districts that should be searched
	// i.e. if I am at hk_island, then I should search for next attractions in big district "hk_island","kowloon","new_territories","outlying_islands", in that order
	$big_district_array=array();
	$big_district_array["hk_island"]=array("hk_island","kowloon","new_territories","outlying_islands");
	$big_district_array["kowloon"]=array("kowloon","hk_island","new_territories","outlying_islands");
	$big_district_array["new_territories"]=array("new_territories","kowloon","hk_island","outlying_islands");
	$big_district_array["outlying_islands"]=array("outlying_islands","kowloon","new_territories","hk_island");

	$lat = $lat_a[$this_json_district];
	$long = $long_a[$this_json_district];
	
	$this_big_district = $district_rev_table[$this_json_district];
	$this_big_district_array = $big_district_array[$this_big_district];
	
	$distsq=array();
	$final_dist=array();
	foreach($this_big_district_array as $big_district){
		$distsq[$big_district]=array();
		$small_district_array = $district_cat[$big_district];
		foreach($small_district_array as $small_district){
			$this_lat = $lat_a[$small_district];
			$this_long = $long_a[$small_district];
			$latmean = deg2rad(($lat+$this_lat)/2);
			$latdiff = deg2rad($lat-$this_lat);
			$longdiff = deg2rad($long-$this_long);
			$distsq[$big_district][$small_district]=pow($latdiff,2) + pow(pow(cos($latmean),2)*$longdiff,2);			
		}
		asort($distsq[$big_district]);
		$final_dist =array_merge($final_dist,$distsq[$big_district]);
	}
	return array_keys($final_dist);
}

// convert node_id to name of attractions
// input: array of node_id
// output: array of names
function idToName($id_array){
	global $connection;
	$name_array=array();	
	
	$id_array_str =  implode(',', array_values(array_diff($id_array,array("D","L"))));
	if ($id_array_str !== ""){
		$sql = "Select name from attractions_hk_ta where node_id in ($id_array_str)";
		$result = mysqli_query($connection, $sql);	
		while($row = mysqli_fetch_assoc($result)) {
			array_push($name_array,$row["name"]); 	// push attractions name
		}
	}	
	return $name_array;
}

// Input: latitude, longitude pair
// Output: a list of nearby restaurants
// price level is a list of prices e.g. [3,4], or [1], or no preference [](empty list)
// cuisine_type is a list of cuisine type

function get_restaurants($current_lat,$current_long,$price_level,$cuisine_type){
		global $connection;
		
		$earthR=6371000;// radius in meter
		$lat_bin = rad2deg(300/$earthR);
		$long_bin = rad2deg(300/$earthR/cos(deg2rad($current_lat)));
		$lat_up = $current_lat+$lat_bin;
		$lat_low = $current_lat-$lat_bin;
		$long_up = $current_long + $long_bin;
		$long_low = $current_long - $long_bin;

		// first search through database
		// with restaurants of appropriate criteria
		
		// $price_level_string="";
		
		$price_level_list = array();
		
		/*
		for($i=0;$i<$price_level;$i+=1){
			$price_level_string.="$";
		}
		*/
		
		foreach ($price_level as $this_level){
			if ($this_level===1){
				array_push($price_level_list,"$");
			}elseif($this_level===2){
				array_push($price_level_list,"$$");
			}elseif($this_level===3){
				array_push($price_level_list,"$$$");
			}elseif($this_level===4){
				array_push($price_level_list,"$$$$");
			}			
		}
		$price_level_num_str = implode(',', $price_level);
		if (count($price_level)===0){$price_level_num_str="1,2,3,4";}
		
		$price_level_string=implode(',',$price_level_list);
		
		$cuisine_type_list = array();
		foreach($cuisine_type as $item){
			array_push($cuisine_type_list,"cuisine_str like '%$item%'");
		}
		$cuisine_type_str="";
		if (count($cuisine_type)>0){
			$cuisine_type_str = " and (" . implode(" or ",$cuisine_type_list) . ")";
		}
		
		$price_sql_list = array();
		foreach($price_level_list as $this_str){
			array_push($price_sql_list," price_level ='$this_str' ");
		}
		
		$price_sql_str ="";
		if (count($price_level_list)>0){
			$price_sql_str  = " and (". implode(" or ", $price_sql_list) .")";
		}
		
		$sql = "SELECT * from attractions_hk_ta where category = 'restaurant' and latitude<=$lat_up and latitude >= $lat_low and longitude <=$long_up and longitude >= $long_low $price_sql_str  $cuisine_type_str order by num_reviews DESC";
		//$cuisine_type_str
		$outputtext = "<script> console.log('sql restaurant sql (line 271) in next line ');</script>";
		echo $outputtext ;
		
		$outputtext = "<script> console.log('sql restaurant sql (line 271): ". $sql ." ');</script>";
		echo $outputtext ;

		$outputtext = "<script> console.log('sql restaurant sql current lat , current long(line 273): ". $current_lat ."," . $current_long ." ');</script>";
		echo $outputtext ;
	
		$result = mysqli_query($connection, $sql);
		$restaurant_list=array();
		$restaurant_names_list=array();
		$ind = 0;
		while (($ind<=4) and ($row = mysqli_fetch_assoc($result))){
			array_push($restaurant_list, $row);
			array_push($restaurant_names_list, $row["name"]);
			$outputtext = "<script> console.log('sql restaurant(line 277): ". $row["name"] ." ');</script>";
			echo $outputtext ;
			$ind+=1;
		}
		if ($ind>=5){
			$outputtext = "<script> console.log('data base restaurant list (line 292): ". $restaurant_list[0]["name"] ." ');</script>";
			echo $outputtext ;
			return $restaurant_list;
		}	
		
		// else return 
		else {
			$cuisine_list= implode(',',$cuisine_type);
		// if there is no appropriate restaurants, then query tripAdvisor
			$takey = '0ebaa50fe6154de1b56d9674555cbc14';
			$calltype = 'restaurants';
			$distance = 1000*0.000621371;// meter in miles
			//$url = "http://api.tripadvisor.com/api/partner/2.0/map/$current_lat,$current_long/$calltype?key=$takey&prices=1,2,3,4&distance=$distance";
			
			if ($cuisine_list===""){
				$url = "http://api.tripadvisor.com/api/partner/2.0/map/$current_lat,$current_long/$calltype?key=$takey&prices=$price_level_num_str&distance=$distance";
			}
			else{
				$url = "http://api.tripadvisor.com/api/partner/2.0/map/$current_lat,$current_long/$calltype?key=$takey&prices=$price_level_num_str&cuisines=$cuisine_list&distance=$distance";}
			$outputtext = "<script> console.log('sql restaurant(line 164): ask trip advisor url ". $url." ');</script>";
			echo $outputtext ;
					
			$json = file_get_contents($url);

			$sql = 		"INSERT INTO restaurants_json 
						(url,restaurant_json)
					VALUES 
						('". mysqli_real_escape_string($connection,$json) ."','$url');
					";	
			// mysqli_query($connection, $sql);
			$results = json_decode($json);
			$data = $results->data;

			if (count($data)>0){
				foreach($data as $poi) {
					$this_restaurant_id=insertRestaurant($poi);
					$sql = 		"SELECT * FROM attractions_hk_ta WHERE name  = '".mysqli_real_escape_string($connection,$poi->name) ."'";
					
					$result = mysqli_query($connection, $sql);
					$row = mysqli_fetch_assoc($result);
					//array_key_exists($attractions_pt,$attractions_info_by_name))){
					if ((!is_null($row)) and (array_search($row["name"],$restaurant_names_list)===false)and (count($restaurant_list)<5)) {
						array_push($restaurant_list, $row);
						array_push($restaurant_names_list, $row["name"]);
					}
				}
			}			
		
			if (count($restaurant_list)<5){// if there is no nearby restaurants, search without being so restrcitive
				$distance = 1000*0.000621371;
				if ($cuisine_list===""){
					$url = "http://api.tripadvisor.com/api/partner/2.0/map/$current_lat,$current_long/$calltype?key=$takey&prices=1,2,3,4&distance=$distance";
				}
				else{
					$url = "http://api.tripadvisor.com/api/partner/2.0/map/$current_lat,$current_long/$calltype?key=$takey&prices=1,2,3,4&cuisines=$cuisine_list&distance=$distance";}
				$json = file_get_contents($url);
				$outputtext = "<script> console.log('sql restaurant(line 164): ask trip advisor url ". $url." ');</script>";
				echo $outputtext ;
				$results = json_decode($json);
				$data = $results->data;
				
				if (count($data)>0){
					foreach($data as $poi) {
						$this_restaurant_id=insertRestaurant($poi);
						$sql = 		"SELECT * FROM attractions_hk_ta WHERE name  = '".mysqli_real_escape_string($connection,$poi->name) ."'";
						
						$result = mysqli_query($connection, $sql);
						$row = mysqli_fetch_assoc($result);
						if ((!is_null($row)) and (array_search($row["name"],$restaurant_names_list)===false)and (count($restaurant_list)<5)) {
							array_push($restaurant_list, $row);
							array_push($restaurant_names_list, $row["name"]);
						}
					}
				}
				
				$outputtext = "<script> console.log('restaurant list size(line 368): ". count($restaurant_list) ." ');</script>";
			echo $outputtext ;
					
			}
			if (count($restaurant_names_list)<5){// if there is no nearby restaurants, search larger
				$outputtext = "<script> console.log('restaurant name list size(line 372): ". count($restaurant_names_list) ." ');</script>";
				echo $outputtext ;
				$distance = 5000*0.000621371;//5km in miles
				$url = "http://api.tripadvisor.com/api/partner/2.0/map/$current_lat,$current_long/$calltype?key=$takey&prices=1,2,3,4&distance=$distance";
				$json = file_get_contents($url);
				$outputtext = "<script> console.log('sql restaurant(line 373): ask trip advisor url ". $url." ');</script>";
				echo $outputtext ;
				$results = json_decode($json);
				$data = $results->data;		
				if (count($data)>0){
					foreach($data as $poi) {
						$this_restaurant_id=insertRestaurant($poi);
						$sql = 		"SELECT * FROM attractions_hk_ta WHERE name  = '".mysqli_real_escape_string($connection,$poi->name) ."'";
						
						$result = mysqli_query($connection, $sql);
						$row = mysqli_fetch_assoc($result);
						if ((!is_null($row)) and (array_search($row["name"],$restaurant_names_list)===false) and (count($restaurant_list)<5)) {
							array_push($restaurant_list, $row);
							array_push($restaurant_names_list, $row["name"]);
						}
					}
				}				
			}
			
			$outputtext = "<script> console.log('restaurant data size(line 348): ". count($data) ." ');</script>";
			echo $outputtext ;
			$outputtext = "<script> console.log('restaurant list size(line 348): ". count($restaurant_list) ." ');</script>";
			echo $outputtext ;
			

			
			return $restaurant_list;
		}
}

// function selectWithConstraint()
// given an array of potential attractions, return an array of appropriate attractions
function selectWithConstraint($attractions_unvisited,$itin_current_time,$itin_node_ids, $itin_edge_ids, $this_day){
	// include $current_point in function argument
	global $attractions_night,$attractions_arts,$attractions_food;
	global $had_lunch, $had_dinner;
	global $connection;
	global $attractions, $attractions_id;
	global $district_index;
	global $attractions_info;
	
	// if there is none attractions_unvisited , return empty list
	if (count($attractions_unvisited)===0){		
		$outputtext = "<script> console.log('no unvisited attractions (line 325): ');</script>";
		echo 	$outputtext ;
		return array();		
	}
	
	$current_point="";
	// if not start of day
	if (count($itin_node_ids[$this_day])>0){
		$last_node_id = $itin_node_ids[$this_day][count($itin_node_ids[$this_day])-1];
		$current_index= count($itin_node_ids[$this_day])-1;
		// make sure 
		while (( ($last_node_id === "D") or ($last_node_id === "L"))and ($current_index>=1)){
			$current_index-=1;
			$last_node_id = $itin_node_ids[$this_day][$current_index];
		}
		if ( ($last_node_id === "D") or ($last_node_id === "L")) {
			return array();
		}
		$sql = "SELECT * from attractions_hk_ta WHERE node_id  = $last_node_id";
		//$outputtext = "<script> console.log('sql selectWithConstraint(line 158): ". $sql . "');</script>";
		//echo 	$outputtext ;
		
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$current_point = $row["name"];
	}

	$outputtext = "<script> console.log('sql current time (line 229): ". $itin_current_time . "');</script>";
	echo 	$outputtext ;
	
	$attractions_list2=array();
	// select attractions that matches time constraint
	// loop through attractions and see whether one matches
	foreach($attractions_unvisited as $attractions_point){
		// we do an optimistic estimate and let later functions further filter 
		$outputtext = "<script> console.log('looking at attraction (line 343): ". $attractions_point . "');</script>";
		echo 	$outputtext ;
		if ($attractions_info["$attractions_point"]["time_constraint"]!== ""){
			if (($attractions_info["$attractions_point"]["start_time"]<= $itin_current_time+60)  and ($attractions_info["$attractions_point"]["end_time"]>= $itin_current_time+$attractions_info["$attractions_point"]["duration"]*60)){

			array_push($attractions_list2,$attractions_point);
			}	
		}	
		else { array_push($attractions_list2,$attractions_point);}
	}
	
	// only choose restaurant if it is NOT the start of day
	if   ((count($itin_node_ids[$this_day])>0)AND (!$had_lunch and ($itin_current_time>=11*60+30)AND ($itin_current_time<=14*60))  OR(!$had_dinner and ($itin_current_time>=17*60+45)AND ($itin_current_time<=20*60+15)) )  {
		// return a symbol for food

		if (($itin_current_time>=11*60+30)AND ($itin_current_time<=14*60)) {$attractions_list2= array("L");}
		if (($itin_current_time>=17*60+45)AND ($itin_current_time<=20*60+15)) {$attractions_list2=array("D");}
	}

	$attractions_list2=array_values($attractions_list2);
	return $attractions_list2;
}

// function selectPOI
// given list of attractions and attractions visited, current time and day, return an array of appropriate POI name for TODAY
// if none if appropriate, then return false

// selection criteria
// choose attractions in this district first
// choose attractions of the right type appropriately, that is, if time is appropriate, select say night attractions, restaurants, museums etc. that have time constraints
// else just choose the nearest neighbor

// cannot choose attractions that does not fit in the time
// e.g. museums cannot be placed beyond their opening hour
// night attractions cannot be placed in the morning

// if lunch/dinner time, select together with new attractions

function selectPOI($attractions, $attractions_visited, $itin_node_ids, $itin_edge_ids, $this_day){
	global $connection;
	global $attractions_seq_1d,$attractions_seq,$day_end_time,$starttimeh;
	global $attractions_night,$attractions_arts,$attractions_food;
	global $had_lunch, $had_dinner;
	global $food_itin;
	global $attractions_info;
	global $price_level,$cuisine_type;
	// global $district_index

	// sort attractions by district every time select POI is used
	
	// district_index[yau_tsim_mong] is a list of attractions in yau_tsim_mong
	$district_index = array();
	$all_district_array = array("central_and_western","eastern","islands","kowloon_city","kwai_tsing","kwun_tong","north","sai_kung","sha_tin","sham_shui_po","southern","tai_po","tsuen_wan","tuen_mun","wan_chai","wong_tai_sin","yau_tsim_mong","yuen_long");
	
	foreach($all_district_array as $district){
		$district_index[$district]=array();
	}
	foreach($attractions as $attraction_pt){
		$this_district = $attractions_info[$attraction_pt]["json_district"];
		//print "<br> district - $this_district <br>";
		array_push($district_index[$this_district],$attraction_pt);		
	}
	
	$add_lunch_index = false;
	$add_dinner_index = false;
	// if there are no more unvisited attractions, return false
	// if there are no more unvisited attractions, then add some more attractions

	
	if (count(array_diff($attractions,$attractions_visited))===0){
			$outputtext = "<script> console.log('(line 427) no more attractions;');</script>";
			echo 	$outputtext ;
	
		return false;
	}

	
	

	$attractions_unvisited = array_diff($attractions,$attractions_visited);
	
	$itin_current_time = 60*$starttimeh + getItinTime($itin_node_ids[$this_day],$itin_edge_ids[$this_day]);
	
	$outputtext = "<script> console.log('(line 299) itin_node_ids = ". implode(',',$itin_node_ids[$this_day]) .";');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('(line 301) itin_edge_ids = ". implode(',',$itin_edge_ids[$this_day]) .";');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('(line 303) start current time = $itin_current_time;');</script>";
	echo 	$outputtext ;
	
	// $attractions_available = $attractions_unvisited;
	// if start of day, then may select any POI
	// preferably the start of a sequence, e.g. the peak or Ngong Ping	
	if (count($itin_node_ids[$this_day])==0){
		// if contain anything in sequence, select that first
		if (count(array_intersect($attractions_unvisited,$attractions_seq_1d)>0)){
			//return that sequence
			for ($x=0;$x<count($attractions_seq);$x+=1){
				  if (count(array_intersect($attractions_seq[$x],$attractions_unvisited))>0){
					  return array_values(array_intersect($attractions_seq[$x],$attractions_unvisited));
				  }
			}
			$attractions_available = $attractions_unvisited;	
		}
		else {
			$attractions_available = $attractions_unvisited;
		}	
	}
	// else today's itin is non-empty
	// compute current time
	// avoid selecting attraction in sequence if possible
	else {
		// assume last node is NOT a restaurant
		$current_index = count($itin_node_ids[$this_day])-1;
		$last_node_id = $itin_node_ids[$this_day][count($itin_node_ids[$this_day])-1];
		

		while (( ($last_node_id === "D") or ($last_node_id === "L"))and ($current_index>=1)){
			$current_index-=1;
			$last_node_id = $itin_node_ids[$this_day][$current_index];
		}
		if  (($last_node_id === "D") or ($last_node_id === "L")){
			return false;
		}
		
		$sql = "SELECT * from attractions_hk_ta WHERE node_id  = $last_node_id";
		$outputtext = "<script> console.log('sql attractions(line 111): ". $sql . "');</script>";
		echo 	$outputtext ;
		
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$current_point = $row["name"];
		$current_district = $row["json_district"];
		$attractions_this_district = $district_index[$current_district];
		
		$sorted_districts = sort_district($current_district);

		// based decision on current time
		// if too late, return false
		// priority: this district, night attraction, arts and culture, food, none
		
		// too late
		if ($itin_current_time>$day_end_time){
			$outputtext = "<script> console.log('(line 490) too late;');</script>";
			echo 	$outputtext ;
	
			return false;
		}
		
		// check whether there are still attraction in this district
		if (count(array_diff($attractions_this_district,$attractions_visited))>0){
			$attractions_available = array_diff($attractions_this_district,$attractions_visited);			
		}
		else {$attractions_available=$attractions_unvisited;}			
	}

	$new_itin_current_time = $itin_current_time;
	
	// here select with a preference of district
	if (count($itin_node_ids[$this_day])>0){
		// $attractions_list2_selected_flag = false;
		
		// loop through all district until one finds an appropriate attraction
		
		foreach($sorted_districts as $district){
			if (!array_key_exists($district,$district_index)){continue;}
			$attractions_loop_district = $district_index[$district];
			$attractions_ok = array_diff($attractions_loop_district,$attractions_visited);
			$attractions_ok = array_values($attractions_ok);
			
			// if not start of the day, do not choose attractions in sequence
			$attractions_ok = array_diff($attractions_ok,$attractions_seq_1d);
			$attractions_ok = array_values($attractions_ok);

			$attractions_list = selectWithConstraint($attractions_ok,$itin_current_time,$itin_node_ids, $itin_edge_ids, $this_day);

			if (count($attractions_list)==0) {continue;}
			
			// if returned array is D or L, then put that in itin 
			if ($attractions_list[0] == "D") {
				// add a signal signifying to return an array of D and the next attraction
				// advance time by 60+30min
				$itin_current_time += 90;
				$had_dinner = true;
				$add_dinner_index = true;
				
				// if not start of the day, do not choose attractions in sequence
				// does not matter
				$attractions_ok = array_diff($attractions_ok,$attractions_seq_1d);
				$attractions_ok = array_values($attractions_ok);
			
				$attractions_list = selectWithConstraint($attractions_ok,$itin_current_time,$itin_node_ids, $itin_edge_ids, $this_day);
				
				// set an entry saying that today's dinner place should be 300m around $current_point
				$food_itin[$this_day]["D"] = $last_node_id;
				$sql = "SELECT * from attractions_hk_ta WHERE node_id  = $last_node_id";
				//$outputtext = "<script> console.log('sql attractions(line 111): ". $sql . "');</script>";
				//echo 	$outputtext ;
				
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);

				$food_itin[$this_day]["dinner_list"] = get_restaurants($row["latitude"],$row["longitude"],$price_level,$cuisine_type);
				
			}			
			elseif ($attractions_list[0] == "L") {
				// add a signal signifying to return an array of L and the next attraction
				// advance time by 60+30min
				$itin_current_time += 90;
				$had_lunch = true;
				$add_lunch_index = true;
				// if not start of the day, do not choose attractions in sequence

				$attractions_ok = array_diff($attractions_ok,$attractions_seq_1d);
				$attractions_ok = array_values($attractions_ok);
				
				// assume that there is no restaurant but time has advanced by 90 minutes
				// continue routing
				
				$attractions_list = selectWithConstraint($attractions_ok,$itin_current_time,$itin_node_ids, $itin_edge_ids, $this_day);
				
				// set an entry saying that today's lunch place should be 300m around $current_point
				$food_itin[$this_day]["L"] = $last_node_id;
				// search for food nearby
				$sql = "SELECT * from attractions_hk_ta WHERE node_id  = $last_node_id";

				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				$food_itin[$this_day]["lunch_list"] = get_restaurants($row["latitude"],$row["longitude"],$price_level,$cuisine_type);

			}
			
			if (count($attractions_list)==0) {continue;}
			do{
				$attractions_str = "\"". implode('","', $attractions_list) . "\"";	
				$attractions_list = array_values($attractions_list);
				
				$attractions_list_escape = array_fill(0,count($attractions_list),"");
				for($i=0;$i<count($attractions_list);$i+=1){
					$attractions_list_escape[$i]=mysqli_real_escape_string($connection,$attractions_list[$i]);
				}
				$attractions_str_escape = "\"". implode('","', $attractions_list_escape) . "\"";	
				// before asking database, add those edges to database
				// and ask google direction API to supply travel time, details etc.
				
				// search for 2 nearest attractions
				// compute distance
				// sort distance array
				// took first 2 entries, and add corresponding edges to database
				$distsq_array=array();
				
				$sql = "SELECT * from attractions_hk_ta WHERE name= \"". mysqli_real_escape_string($connection,$current_point). "\"";
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				
				$current_lat = $row["latitude"];
				$current_long = $row["longitude"];
		
				$sql = "SELECT * from attractions_hk_ta WHERE name in ($attractions_str_escape)";
				
				$outputtext = "<script> console.log('select POI, for loop, line 352: ". $sql . "');</script>";
				echo 	$outputtext ;
		
				$result = mysqli_query($connection, $sql);
				while($row = mysqli_fetch_assoc($result)){
					$this_lat = $row["latitude"];
					$this_long = $row["longitude"];
					$latmean = deg2rad(($current_lat+$this_lat)/2);
					$latdiff = deg2rad($current_lat-$this_lat);
					$longdiff = deg2rad($current_long-$this_long);
					$distsq = ($latdiff^2 + (cos($latmean)^2*$longdiff)^2)*40589641000000;// in meter square	
					$this_name = $row["name"];
					$distsq_array[$this_name]=$distsq;			
				}
				asort($distsq_array);
				//pick the first 2
				$index=0;
				$str="";
				foreach($distsq_array as $name=>$distsq){
					if($index>=2){break;}	
					// if there is no such edge, add it
					
					$sql = "SELECT * from graph_hk_ta WHERE start_point= \"". mysqli_real_escape_string($connection,$current_point). "\" and end_point = \"". mysqli_real_escape_string($connection,$name)  ."\" and  travel_time>0 ";
					$outputtext = "<script> console.log('sql edge line 373: ". $sql . "');</script>";
					echo 	$outputtext ;
					//echo "<p>$sql </p>";
					$result = mysqli_query($connection, $sql);
					$row = mysqli_fetch_assoc($result);
					
					if ((is_null($row))or ($row["travel_time"]===0)) {
						$outputtext = "<script> console.log('sql edge line 380 asked name ". $name ."');</script>";
						echo 	$outputtext ;
					
						$this_edge_id=addEdgeDB($current_point,$name,"transit");
						
						$outputtext = "<script> console.log('line 385 asked edge id $this_edge_id , $current_point,$name ');</script>";
						echo 	$outputtext ;
						
						updateEdgeDB($this_edge_id,"transit");
					
						// if straight line distance <= 1.5km, then also add walk edge						
						if (($distsq <=2250000) and ($distsq>0)){
						//echo "<p>distsq :$distsq</p>";
						$this_edge_id=addEdgeDB($current_point,$name,"walk");
						updateEdgeDB($this_edge_id,"walk");
						}
						$index += 1;
					}
				}
							
				$sql = "SELECT * from graph_hk_ta WHERE start_point = \"".mysqli_real_escape_string($connection,$current_point)."\" AND end_point IN ($attractions_str_escape) AND (NOT (travel_mode = \"\")) order by travel_time limit 1";
				$outputtext = "<script> console.log('sql edge: ". $sql . "');</script>";
				echo 	$outputtext ;
							
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				
				// if there is no result
				$satisfied_time_constraint = false;
				if (is_null($row)){
					
					break;}
				
				else {
				
					$str =  $row["end_point"];
					$new_itin_current_time = $itin_current_time + $row["travel_time"] ;
					
					$this_edge_travel_time = $row["travel_time"];
					
					$sql = "SELECT * from attractions_hk_ta WHERE name = \"".mysqli_real_escape_string($connection,$str)."\"";
					$outputtext = "<script> console.log('sql attractions: ". $sql . "');</script>";
					echo 	$outputtext ;
					$result = mysqli_query($connection, $sql);
					$row = mysqli_fetch_assoc($result);
					
					$new_itin_current_time += $row["duration"]*60;
					
					$attractions_list =array_values(array_diff($attractions_list,array($str)));

					if ($attractions_info["$str"]["time_constraint"]!== ""){
						$outputtext = "<script> console.log('itin current time (line 629): ". $itin_current_time ." this_edge_travel_time: "  . $this_edge_travel_time .  " duration: ". $attractions_info["$str"]["duration"]*60 . "');</script>";
						echo 	$outputtext ;
						$outputtext = "<script> console.log('atraction start time (line 629): ". $attractions_info["$str"]["start_time"]  . "');</script>";
						echo 	$outputtext ;
						
						if (($attractions_info["$str"]["start_time"]<= ($itin_current_time+$this_edge_travel_time))  and ($attractions_info["$str"]["end_time"]>= ($itin_current_time+$this_edge_travel_time+$attractions_info["$str"]["duration"]*60))){
							$satisfied_time_constraint=true;
							
						} else {$satisfied_time_constraint=false;}
					} else {$satisfied_time_constraint = true;}

				}
			// continue search if takes too long or travel time is longer than an hour or does not satisfy time constraint
			} while (($new_itin_current_time>$day_end_time) and (count($attractions_list)>0) and !$satisfied_time_constraint    );
			
			if (($new_itin_current_time<=$day_end_time) and ($str !=="") and $satisfied_time_constraint){
				$outputtext = "<script> console.log('attraction added: $str, current time = $new_itin_current_time, day end time = $day_end_time;');</script>";
				echo 	$outputtext ;
				if ($add_lunch_index) {return array("L",$str);}
				if ($add_dinner_index) {return array("D",$str);}
				return array($str);
			}
			
		}
		if ($add_lunch_index) {return array("L");}
		if ($add_dinner_index) {return array("D");}
		$outputtext = "<script> console.log('(line 712) nothing is at right time constraint;');</script>";
		echo 	$outputtext ;
	
		return false;

	}
	//  fresh start for new day
	else {
		$attractions_list2= selectWithConstraint($attractions_unvisited,$itin_current_time,$itin_node_ids, $itin_edge_ids, $this_day);	
		$attractions_list = selectWithConstraint($attractions_available,$itin_current_time,$itin_node_ids, $itin_edge_ids, $this_day);
		
		// sort through district and select nearest district first
		
		// if there are no appropriate POI, then return false
		// another solution could be add dummy block of an hour and wait till time is appropriate
		// if nothing in this district is appropriate, then select attractions in another district
		if (count($attractions_list)===0){
			if (count($attractions_list2)===0){
				$outputtext = "<script> console.log('(line 730) no appropriate POI;');</script>";
				echo 	$outputtext ;
	
				return false;
			}
			else {$attractions_list=$attractions_list2;}
		}
		
		$attractions_str = "\"". implode('","', $attractions_list) . "\"";	
		$attractions_list = array_values($attractions_list);
		$attractions_list_escape = array_fill(0,count($attractions_list),"");

		for($i=0;$i<count($attractions_list);$i+=1){
			$attractions_list_escape[$i]=mysqli_real_escape_string($connection,$attractions_list[$i]);
		}
		$attractions_str_escape = "\"". implode('","', $attractions_list_escape) . "\"";

		$sql = "SELECT * from attractions_hk_ta WHERE name  IN ($attractions_str_escape) limit 1";
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$str =  $row["name"];
		return array($str);		
	}
}

//////////////////////////////////////////////////////////////////////////////////////////////////////

// this function declared in functions.php
/*
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
*/

//////////////////////////////////////////////////////////////////////////////////////////////////////

// this function tweaks the itin to make sure it contains enough customized attraction
// this function should only be run at the first time itin is generated
function tweak_itin(){
	global $itin_node_ids, $itin_edge_ids;
	global $attractions_info,$attractions_info_by_name,$attractions_info_by_id;
	global $subcatlist;
	global $attractions_seq_1d;
	//global $subcat_attraction;
	global $attractions;
	global $itin_info_time_by_name;
	global $previous_node_name;
	global $connection;
	global $is_prev_node_restaurant;
	// $subcatlist
	
	// maintain a pool of attractions
	// at get_user_preference stage, algo should select a pool of high rating attractions with those subcat
	// note that if there are no attractions of that subcategory, we cannot do anything about it

	// first examine the itin and ask whether it has each customized attraction
	$general_attractions_list = array();
	$subcatlist2 = $subcatlist;
	
	$outputtext = "<script> console.log('subcatlist(line 708)  :". implode(',',$subcatlist) . "');</script>";
	echo 	$outputtext ;
			
			
	foreach ($attractions as $attractions_pt){
		if (($attractions_pt !=="D") and ($attractions_pt !=="L") and (array_key_exists($attractions_pt,$attractions_info_by_name))){
			$temparray = explode('|',$attractions_info_by_name[$attractions_pt]["subcategory"]);
			$subcatlist2 = array_diff($subcatlist2, $temparray);
			$outputtext = "<script> console.log('subcatlist2(line 716)  :". implode(',',$subcatlist2) . "');</script>";
			echo 	$outputtext ;
			
			$outputtext = "<script> console.log('attractions_pt(with subcat) (line 712) $attractions_pt :". $attractions_info_by_name[$attractions_pt]["subcategory"] . "');</script>";
			echo 	$outputtext ;

			// if attractions_pt do not have that subcat, put it in general attractions
			// and it is not a restaurant			
			if (($attractions_info_by_name[$attractions_pt]["category"]!=="restaurant")and(count(array_intersect($temparray,$subcatlist))==0)) {
				array_push($general_attractions_list,$attractions_pt);
			}
		}
	}
	
	$general_attractions_list = array_diff($general_attractions_list,$attractions_seq_1d);

	// remaining subcat to take care about
	
	
	// then substitute general attraction by specific attraction
	// afterwards, see if it matches time constraint because timeslot may have moved

	// if it need one more attraction of that subcat, find the nearest general one and replace it with that specific attraction
	
	// if user has chosen N subcat, 

	// for itin, pick out general attractions, that is, attractions that is not core-core, and not user preference
	// e.g. if user only choose mall, then things like church temple would be general attractions
	
	$to_be_replace = array();
	// compute all distance
	
	$outputtext = "<script> console.log('subcatlist2(line 751)   :". implode(',',$subcatlist2) . "');</script>";
	echo 	$outputtext ;
	
	// use database information to update $subcat_attraction
	$subcat_attraction = array();
	foreach($subcatlist2 as $subcat_option){
		$sql = "Select name from attractions_hk_ta where subcategory like '%$subcat_option%' and node_id<=534 and subcategory !='core' ORDER BY custom_rating DESC, popular_ranking ASC";
		$result = mysqli_query($connection, $sql);
		$subcat_attraction[$subcat_option]=array();
		while($row = mysqli_fetch_assoc($result)) {
			array_push($subcat_attraction[$subcat_option], $row["name"] );
		}
		$subcat_attraction[$subcat_option]=array_values(array_diff($subcat_attraction[$subcat_option], $attractions_seq_1d,$attractions ));
	}
	
	foreach($subcatlist2 as $subcat_option){
		$subcat_attraction_list = $subcat_attraction[$subcat_option];
		
		$outputtext = "<script> console.log('subcat_attraction_list(line 754) $subcat_option  :". implode(',',$subcat_attraction_list) . "');</script>";
		echo 	$outputtext ;
		
		$temp_subcat_array=array();	
		$temp_subcat_array_to_general=array();
		$temp_subcat_array_to_dist=array();
		foreach($subcat_attraction_list as $subcat_attraction_point){
			//$subcat_attraction_point = $subcat_attraction_list[0];
			$distance = array();
			
			$lat1 = $attractions_info_by_name[$subcat_attraction_point]["latitude"];
			$long1 = $attractions_info_by_name[$subcat_attraction_point]["longitude"];
			
			foreach($general_attractions_list as $general_attractions_pt){
				// compute distance only if replacing it will fit time constraint
				// compute time at the start of reaching that general attraction

				$current_time = $itin_info_time_by_name[$general_attractions_pt]- $attractions_info_by_name[$general_attractions_pt]["duration"]*60;
				
				$subcat_attraction_point_name = $attractions_info_by_name[$subcat_attraction_point]["name"];
				// use the loose itin time estimation
				// assume at most 60 minutes travel time
				if (($current_time +60>= $attractions_info_by_name[$subcat_attraction_point_name]["start_time"]) and ($current_time+$attractions_info_by_name[$subcat_attraction_point_name]["duration"]*60 <= $attractions_info_by_name[$subcat_attraction_point_name]["end_time"])){
					$lat2 = $attractions_info_by_name[$general_attractions_pt]["latitude"];
					$long2 = $attractions_info_by_name[$general_attractions_pt]["longitude"];
					$distance[$general_attractions_pt] = get_distance($lat1,$long1,$lat2,$long2);
				}
			}
			
			$first_subcat2 = $subcat_attraction_point;
			$found_appropriate_attraction=false;
			if (count($distance)>0){
				asort($distance);
				$first_value = reset($distance);
				$first_key = key($distance);
				$shortest_travel_time = 10000;
				$stored_general_pt = "";
				foreach($distance as $general_attractions_pt=>$this_distance){
					$first_general2 = $general_attractions_pt;
					if ($this_distance > 20000) { break;}// if distance is too long, then break
					// compute actual travel time
					// check if there is a previous node
					// if there is no previous node, i.e. this is first node of the day
					if ($previous_node_name[$general_attractions_pt]===""){
						// check time constraint
						if (($current_time >= $attractions_info_by_name[$subcat_attraction_point]["start_time"]) and ($current_time+$attractions_info_by_name[$subcat_attraction_point]["duration"]*60 <= $attractions_info_by_name[$subcat_attraction_point]["end_time"])){
							$found_appropriate_attraction=true;
							$this_travel_time=0;
							$shortest_travel_time=0;
							$stored_general_pt = $general_attractions_pt;		
							// $stored_subcat_pt = 
							
							break;
						}
					}
					elseif ($is_prev_node_restaurant === false) {
						
						$prev_node_name = $previous_node_name[$general_attractions_pt];
						
						$this_edge_id = get_edge($prev_node_name,$subcat_attraction_point);
						$sql = "Select * from graph_hk_ta where edge_id = $this_edge_id";
						$result = mysqli_query($connection, $sql);
						$row = mysqli_fetch_assoc($result);
						$current_time = $itin_info_time_by_name[$prev_node_name];
						$this_travel_time = $row["travel_time"];
						if (($this_travel_time < 45) and ($current_time +$this_travel_time >= $attractions_info_by_name[$subcat_attraction_point]["start_time"]) and ($current_time + $this_travel_time + $attractions_info_by_name[$subcat_attraction_point]["duration"]*60 <= $attractions_info_by_name[   $subcat_attraction_point]["end_time"])){
							$found_appropriate_attraction=true;
							// break;
							// keep a record of this attraction if it has the travel time from last attraction to this subcat attraction
							if ($this_travel_time < $shortest_travel_time){
								$stored_general_pt = $general_attractions_pt;
							}
							
						}						
					} else {
						// $is_prev_node_restaurant is true
						$prev_node_name = $previous_node_name[$general_attractions_pt];
						if ( ($prev_node_name !== "D") and ($prev_node_name !== "L")){
							$this_edge_id = get_edge($prev_node_name,$subcat_attraction_point);
							$sql = "Select * from graph_hk_ta where edge_id = $this_edge_id";
							$result = mysqli_query($connection, $sql);
							$row = mysqli_fetch_assoc($result);
							$current_time = $itin_info_time_by_name[$prev_node_name]+15+60;// include 15 min to travel to restaurant and 60 min to eat
							$this_travel_time = $row["travel_time"];
							
							if (($this_travel_time < 45) and ($current_time +$this_travel_time >= $attractions_info_by_name[$subcat_attraction_point]["start_time"]) and ($current_time + $this_travel_time + $attractions_info_by_name[$subcat_attraction_point]["duration"]*60 <= $attractions_info_by_name[   $subcat_attraction_point]["end_time"])){
								$found_appropriate_attraction=true;
								// break;
								// keep a record of this attraction if it has the travel time from last attraction to this subcat attraction
								if ($this_travel_time < $shortest_travel_time){
									$stored_general_pt = $general_attractions_pt;
								}
							}
						} else {
							// do not do anything if previous node is restaurant, which has no previous node
						}
					}
					
					
				}
				if ($found_appropriate_attraction)  {
					$to_be_replace[$first_general2] = $attractions_info_by_name[$first_subcat2]["node_id"];
					$general_attractions_list = array_values(array_diff($general_attractions_list,array($first_general2)));
					break;// finish this list of subcat attractions; go to next subcat attraction list
				}
				
				// $temp_subcat_array[$first_key] = $first_value;
				
				// $temp_subcat_array_to_dist[$subcat_attraction_point] = $first_value;
				// $temp_subcat_array_to_general[$subcat_attraction_point] = $first_key;

			}

		}
		/*
		if (count($temp_subcat_array_to_dist)>0){
			asort($temp_subcat_array_to_dist);
			$first_dist2= reset($temp_subcat_array_to_dist);
			$first_subcat2 = key($temp_subcat_array_to_dist);
			$first_general2 = $temp_subcat_array_to_general[$first_subcat2];
			
			// replace first_key by that subcat_attraction_point
			$to_be_replace[$first_general2] = $first_subcat2;
			$general_attractions_list = array_values(array_diff($general_attractions_list,array($first_general2)));
		}
		*/
	}
	
	$outputtext = "<script> console.log('to_be_replace (line 757)  ". implode( ',' , $to_be_replace ) . "');</script>";
	echo 	$outputtext ;
	// find the one with minimum distance
	// for this do a single pass

	// regenerate itin
	
	foreach($itin_node_ids as $day=>$this_day_node_ids){
		
		foreach($this_day_node_ids as $index=>$current_node_ids){
			
			//$result = array_search($attractions_info_by_id["current_node_ids"]["name"],$to_be_replace );
			if  (( $current_node_ids!== "L") and ($current_node_ids!=="D")and ( array_key_exists($attractions_info_by_id[$current_node_ids]["name"],$to_be_replace))){
				$itin_node_ids[$day][$index] = $to_be_replace[$attractions_info_by_id[$current_node_ids]["name"]];
				
				if (($index>0) and ($itin_node_ids[$day][$index-1] !=="D") and ($itin_node_ids[$day][$index-1] !=="L")) {
					// replace previous edge
					$start_point = $attractions_info_by_id[$itin_node_ids[$day][$index-1]]["name"];
					$end_point = $attractions_info_by_id[$itin_node_ids[$day][$index]]["name"];
					$this_edge_id = get_edge($start_point,$end_point);
					$itin_edge_ids[$day][$index-1]=$this_edge_id;
				}
				
				if (($index<count($itin_node_ids[$day]))and ($itin_node_ids[$day][$index+1] !=="D") and ($itin_node_ids[$day][$index+1] !=="L")){
					// replace next edge
					$start_point = $attractions_info_by_id[$itin_node_ids[$day][$index]]["name"];
					$end_point = $attractions_info_by_id[$itin_node_ids[$day][$index+1]]["name"];
					$this_edge_id = get_edge($start_point,$end_point);
					$itin_edge_ids[$day][$index]=$this_edge_id;
				}
			}
		}
		
	}
	
	// update attractions
	
	$attractions_to_be_removed = array_keys($to_be_replace);
	$attractions_to_be_added = array_values($to_be_replace);
	$attractions= array_diff($attractions, $attractions_to_be_removed);
	$attractions= array_values(array_merge($attractions,$attractions_to_be_added));
	
	// right now, assume we only need it such that there only need to be 1 attraction per subcat
	// for each missing subcat, find the highest ranking (custom rating + TA ranking) in that subcat
	
	// for all (subcat attraction, general attraction), compute distance/ travel time
	// which could be geographic distance, or increase in travel time
	
	// find the pair with minimum travel time, and replace it with 
	
	
}

function handlePOI($new_POIs){
	global $this_day,$days,$had_lunch,$had_dinner;
	global $itin_edge_ids,$itin_node_ids,$connection,$attractions_visited;
	global $attractions;
	// tag added attractions to today's itinerary
		// if today's itin is nonempty, add an edge that connect previous POI to current POI
		
		// if that is a dinner or lunch point, then just push the new attraction in
		if (($new_POIs[0] =="D") or ($new_POIs[0]=="L")){
			
			array_push($itin_edge_ids[$this_day], -1);
			array_push($itin_node_ids[$this_day], $new_POIs[0]);
			
			
			if (count($new_POIs)>1) {
				
				$sql = "Select * from attractions_hk_ta where name =\"" . mysqli_real_escape_string($connection,$new_POIs[1]) . "\""; 
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				
				$attractions_id[$new_POIs[1]] = $row["node_id"];
					
				// if POI is new, push POI into attractions
				if (array_search($new_POIs[1],$attractions)===false){ 
					array_push($attractions,$new_POIs[1]);
				}
				array_push($itin_node_ids[$this_day],$row["node_id"]);
				
				
				// if there is a previous attraction
				if (count($itin_node_ids[$this_day])>=3){
				
					$current_node_id = $itin_node_ids[$this_day][count($itin_node_ids[$this_day])-3];
					$current_node_name = array_search($current_node_id,$attractions_id);
				
					$next_node_name = $new_POIs[1];
					

					$this_edge_id= get_edge($current_node_name,$next_node_name);
					array_push($itin_edge_ids[$this_day], $this_edge_id);
					
					// set new POI to attractions visited
					array_push($attractions_visited,$new_POIs[1]);
				}
			}
			else {
				if ($this_day<$days) {
					$this_day +=1;
					$had_lunch = false;
					$had_dinner = false;
				}
				else {
					//break;
				}				
			}
			//continue;
		}
		// set new POI to attractions visited
		$attractions_visited = array_merge($attractions_visited,$new_POIs);
		if (count($itin_node_ids[$this_day])>0){
			
			$last_point_id = $itin_node_ids[$this_day][count($itin_node_ids[$this_day])-1];
			//print($last_point_id);
			$sql = "Select name from attractions_hk_ta where node_id=$last_point_id";
			
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$last_point=$row["name"];
			
			// if POI is new, push POI into attractions
			$sql = "Select * from attractions_hk_ta where name =\"" . mysqli_real_escape_string($connection,$new_POIs[0]) . "\""; 
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			array_push($attractions,$new_POIs[0]);
			$attractions_id[$new_POIs[0]] = $row["node_id"];

			
			$this_edge_id= get_edge($last_point,$new_POIs[0]);
			array_push($itin_edge_ids[$this_day], $this_edge_id);

			
		}
		
		$sql = "Select node_id from attractions_hk_ta where name=\"". mysqli_real_escape_string($connection,$new_POIs[0])."\""; 
		$outputtext = "<script> console.log('itin_node_ids[$this_day], $new_POIs[0],  sql: ". $sql. "');</script>";
		echo 	$outputtext ;
			
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		array_push($itin_node_ids[$this_day], $row["node_id"]);
		
		for($x=1;$x<count($new_POIs);$x +=1){
			$sql = "Select node_id from attractions_hk_ta where name=\"". mysqli_real_escape_string($connection,$new_POIs[$x])."\""; 
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			array_push($itin_node_ids[$this_day], $row["node_id"]);
			$outputtext = "<script> console.log('itin_node_ids[$this_day], $new_POIs[0],  sql: ". $sql. "');</script>";
			echo 	$outputtext ;
				
	
			
			$this_edge_id = get_edge($new_POIs[$x-1],$new_POIs[$x]);
			array_push($itin_edge_ids[$this_day], $this_edge_id);			

		}
		$outputtext = "<script> console.log('itin_edge_ids[$this_day]: ". implode( ',' , $itin_edge_ids[$this_day] ) . "');</script>";
		echo 	$outputtext ;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////

// Initialization
	$had_lunch=false;
	$had_dinner=false;
	$food_itin=array();
	
	//$sql = "SELECT * from attractions_hk_ta WHERE name in ($attractions_str_escape) and district=''";
	//$result = mysqli_query($connection, $sql);
	
	$sql="";
	
	$attractions_info=array();
	
	// initialize $attractions_info_by_name, $attractions_info_by_id 
	$attractions_info_by_name=array();
	$attractions_info_by_id =array();
	$sql = "Select * from attractions_hk_ta where node_id<=534 order by node_id";
	$result = mysqli_query($connection, $sql);
	while($row = mysqli_fetch_assoc($result)) {
		$attractions_info_by_name[$row["name"]]= $row;
		$attractions_info_by_id[$row["node_id"]]= $row;		
		$temptime = intval($row["time_constraint"]);
		$start_t =floor($temptime/10000);
		$end_t = $temptime % 10000;

		$attractions_info_by_name[$row["name"]]["start_time"]= floor($start_t/100)*60+ ($start_t % 100);
		$attractions_info_by_id[$row["node_id"]]["start_time"]= floor($start_t/100)*60+ ($start_t % 100);

		$attractions_info_by_name[$row["name"]]["end_time"]= floor($end_t/100)*60+ ($end_t % 100);
		$attractions_info_by_id[$row["node_id"]]["end_time"]= floor($end_t/100)*60+ ($end_t % 100);
	}
	
	/*
	// urldecode attractions. Important for program to properly handle special character
	foreach($must_route_attractions as $x=>$attractions_pt){
		$decoded_attraction_pt = urldecode($attractions_pt);
		$must_route_attractions[$x] = urldecode($attractions_pt);
		
		
		//$subcat_str = $attractions_info_by_name[$decoded_attraction_pt]["subcategory"];
		//$this_subcat_list= explode('|',$subcat_str);
		//$subcatlist = array_diff($subcatlist,$this_subcat_list);
	}
	
	foreach($must_route_attractions as $this_attraction){
		array_push($attractions,$this_attraction);
		$attractions_id[$this_attraction] = $attractions_info_by_name[$this_attraction]["node_id"];	
	}
	*/
	$attractions_str = "";
	foreach($attractions as $attraction_pt){
		$attractions_str  .=",". urlencode($attraction_pt);
		
	}
	$outputtext = "<script> console.log('get_itin2(line 1222): ');</script>";
	echo 	$outputtext;
	$outputtext = "<script> console.log('get_itin2(line 1224): ". $attractions_str . "');</script>";
	echo 	$outputtext;


	$attractions_str ='"' .  implode('","',$attractions). '"';	
	$attractions_list_escape = array_fill(0,count($attractions),"");
	$attractions = array_values($attractions);
	
	for($i=0;$i<count($attractions);$i+=1){
		$attractions_list_escape[$i]=mysqli_real_escape_string($connection,$attractions[$i]);
	}
	$attractions_str_escape = "\"". implode('","', $attractions_list_escape) . "\"";
	// assign district if not already assigned yet
	$sql = "SELECT * from attractions_hk_ta WHERE name in ($attractions_str_escape) and district=''";
	$result = mysqli_query($connection, $sql);
	
	$sql="";
	if (mysqli_num_rows($result) > 0){
		while($row = mysqli_fetch_assoc($result)) {
			$this_district = assign_district($row["latitude"],$row["longitude"]);
			$this_node_id = $row["node_id"];		
			$sql .= "UPDATE attractions_hk_ta SET district='$this_district' where node_id= $this_node_id ;";
		}
	}
	mysqli_multi_query($connection, $sql);
	
	$sql = "SELECT * from attractions_hk_ta WHERE name in ($attractions_str_escape)";
	$sql = "SELECT * from attractions_hk_ta WHERE node_id<=534";
	$result = mysqli_query($connection, $sql);
	if (mysqli_num_rows($result) > 0){
		while($row = mysqli_fetch_assoc($result)) {
			// fill attractions_info with all information
			$attractions_info[$row["name"]]=$row;
			$temptime = intval($row["time_constraint"]);
			 $start_t =floor($temptime/10000);
			 $end_t = $temptime % 10000;
			 
			$attractions_info[$row["name"]]["start_time"] = floor($start_t/100)*60+ ($start_t % 100);
			
			$attractions_info_by_name[$row["name"]]["start_time"]= floor($start_t/100)*60+ ($start_t % 100);
			$attractions_info_by_id[$row["node_id"]]["start_time"]= floor($start_t/100)*60+ ($start_t % 100);

			$attractions_info[$row["name"]]["end_time"] =floor($end_t/100)*60+ ($end_t % 100);
			$attractions_info_by_name[$row["name"]]["end_time"]= floor($end_t/100)*60+ ($end_t % 100);
			$attractions_info_by_id[$row["node_id"]]["end_time"]= floor($end_t/100)*60+ ($end_t % 100);
			
		}
	}
	//print_r($attractions_info);
	
	
	//return;
	
	// categorize attractions according to districts
	// from $attractions
	
	$sql = "SELECT * from attractions_hk_ta WHERE name in ($attractions_str_escape) Order by json_district";
	$outputtext = "<script> console.log('sql attractions: ". $sql . "');</script>";
	echo 	$outputtext ;
	$result = mysqli_query($connection, $sql);
	$attractions_categorized=array();
	
	if (mysqli_num_rows($result) > 0){
		while($row = mysqli_fetch_assoc($result)) {
			array_push($attractions_categorized , $row);
		}
	}
	
	// set end time to be the latest 11pm							
	if ($pace =="relaxed") {$day_end_time = min(23*60,$starttimeh*60+7*60);}
	elseif ($pace =="normal") {$day_end_time = min(23*60,$starttimeh*60+9*60);}
	elseif ($pace =="packed") {$day_end_time = min(23*60,$starttimeh*60+11*60);}
	elseif ($pace =="superman") {$day_end_time = min(23*60,$starttimeh*60+13*60);}
	else {$day_end_time = 22*60;}
	
	// look at must route attractions, set day end time to be the latest of [starttime of attraction]+[duration]+[2 hour]
	$latest_time =0;
	/*
	foreach($must_route_attractions as $this_point){
		$this_time = $attractions_info_by_name[$this_point]["start_time"]+ $attractions_info_by_name[$this_point]["duration"]+180;
		$latest_time=max($latest_time,$this_time);
	}
	*/
	$day_end_time = max($day_end_time,$latest_time);
	$outputtext = "<script> console.log('this_day(line 1307) : day_end_time $day_end_time');</script>";
	echo 	$outputtext ;
	
	// create district index
	$district_index = array();

	$all_district_array = array("central_and_western","eastern","islands","kowloon_city","kwai_tsing","kwun_tong","north","sai_kung","sha_tin","sham_shui_po","southern","tai_po","tsuen_wan","tuen_mun","wan_chai","wong_tai_sin","yau_tsim_mong","yuen_long");
	
	foreach($all_district_array as $district){
		$district_index[$district]=array();
	}
	
	for($x=0;$x<count($attractions_categorized);$x+=1){
			//$outputtext = "<script> console.log('this_day(line 666) : ". implode(',',$attractions_categorized[$x]) . "');</script>";
			//echo 	$outputtext ;
			
			if (array_search($attractions_categorized[$x]["json_district"],$all_district_array)!==false){
				array_push($district_index[$attractions_categorized[$x]["json_district"]],$attractions_categorized[$x]["name"]);
			}
	}

	/*
	for($x=0;$x<count($attractions_categorized);$x+=1){
		if (array_key_exists($attractions_categorized[$x]["json_district"],$district_index) === false){
			$district_index[$attractions_categorized[$x]["json_district"]]=array();
		}
		array_push($district_index[$attractions_categorized[$x]["json_district"]],$attractions_categorized[$x]["name"]);
	}
	*/
	//print_r($district_index);
	
	// attractions that should be visited in sequence
	$attractions_seq = array();
	$attractions_seq[0] = array("Peak Tram","Victoria Peak (The Peak)","Lion's Pavilion at The Peak");
	$attractions_seq[1] = array("Ngong Ping Village (Ngong Ping 360)","Big Buddha","Po Lin (Precious Lotus) Monastery");
	$attractions_seq_1d = array("Peak Tram","Victoria Peak (The Peak)","Lion's Pavilion at The Peak","Ngong Ping Village (Ngong Ping 360)","Big Buddha","Po Lin (Precious Lotus) Monastery");
	$attractions_seq_id_1d=array();
	foreach($attractions_seq[1] as $name){
		$sql= "SELECT * from attractions_hk_ta where name = '". mysqli_real_escape_string($connection,$name)  ."'";
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		$this_id =$row["node_id"];
	//	print "<br> sql $sql 1337 node id $this_id <br>";
		array_push($attractions_seq_id_1d,$row["node_id"]);
	}
	
	// night attractions
	$attractions_night = array("Symphony of Lights","Happy Valley Racecourse","Sha Tin Racecourse","Take Out Comedy");
	
	$attractions_food = array();
	$sql = "SELECT name from attractions_hk_ta WHERE category LIKE \"%restaurant%\" ";
	$result = mysqli_query($connection, $sql);
	if (mysqli_num_rows($result) > 0){
		while($row = mysqli_fetch_assoc($result)) {
			array_push($attractions_food , $row["name"]);		// push food attractions
		}
	}
	
	// these attractions should have arrival time between 10am and 4pm
	// which will include enough time for travel
	$attractions_arts = array();

	$unvisited_num = count($attractions);
	$this_day = 1;
	// initialize $itin_edge_ids, $itin_node_ids
	$itin_edge_ids=array();
	$itin_node_ids=array();
	// only index 1 to $days of $itin_node_ids are used, initialize index=0 for correctness of code
	for($x=0;$x<=$days;$x+=1){
		$itin_edge_ids[$x] = array();
		$itin_node_ids[$x] = array();
	}

	$attractions_visited=array();
	
// compute itin	
//while($unvisited_num>0)
while (true){
	$outputtext = "<script> console.log('this_day : ". $this_day . "');</script>";
	echo 	$outputtext ;
	$new_POIs = selectPOI($attractions, $attractions_visited, $itin_node_ids, $itin_edge_ids, $this_day);
	// if returned false, then add more attractions temporarily and see if it returns anything new
	if ($new_POIs === false){
		$temp_attractions=$attractions;
		// expand attractions into node_id
		$node_id_list=array();
		foreach($attractions as $attraction_pt){
			array_push($node_id_list, $attractions_info_by_name[$attraction_pt]["node_id"]);
		}
		$temp_id_list= array_merge($attractions_seq_id_1d,$node_id_list);
		$node_id_str = "(" . implode(',',$temp_id_list) .")";
		if (count($temp_id_list)>0){
			$sql = "SELECT * from attractions_hk_ta  where node_id NOT in $node_id_str order by custom_rating DESC, popular_ranking ASC limit 10" ;
			//print $sql;
			$outputtext = "<script> console.log('(line 1377) day $this_day sql for new attractions: $sql ');</script>";
			echo 	$outputtext ;
			
		}else{
			$sql = "SELECT * from attractions_hk_ta order by custom_rating DESC, popular_ranking ASC limit 10" ;
		}
		$result = mysqli_query($connection, $sql);
		while($row = mysqli_fetch_assoc($result)){
			array_push($temp_attractions, $row["name"]);
			//$attractions_id[ $row["name"]]=$row["node_id"];
			
		}	
		//print_r($temp_attractions);
		
		$new_POIs = selectPOI($temp_attractions, $attractions_visited, $itin_node_ids, $itin_edge_ids, $this_day);
	}
	
	if ($new_POIs === false){
		if ($this_day<$days) {
			$this_day +=1;
			$had_lunch = false;
			$had_dinner = false;
		}
		else {
			// reached last day of itin
			break;
		}		
	}
	else {
	// tag added attractions to today's itinerary
		// if today's itin is nonempty, add an edge that connect previous POI to current POI
		
		// if that is a dinner or lunch point, then just push the new attraction in
		if (($new_POIs[0] =="D") or ($new_POIs[0]=="L")){
			
			array_push($itin_edge_ids[$this_day], -1);
			array_push($itin_node_ids[$this_day], $new_POIs[0]);

			if (count($new_POIs)>1) {
				
				$sql = "Select * from attractions_hk_ta where name =\"" . mysqli_real_escape_string($connection,$new_POIs[1]) . "\""; 
				$result = mysqli_query($connection, $sql);
				$row = mysqli_fetch_assoc($result);
				
				$attractions_id[$new_POIs[1]] = $row["node_id"];
					
				// if POI is new, push POI into attractions
				if (array_search($new_POIs[1],$attractions)===false){ 
					array_push($attractions,$new_POIs[1]);
				}
				array_push($itin_node_ids[$this_day],$row["node_id"]);
				
				
				// if there is a previous attraction
				if (count($itin_node_ids[$this_day])>=3){
				
					$current_node_id = $itin_node_ids[$this_day][count($itin_node_ids[$this_day])-3];
					$current_node_name = array_search($current_node_id,$attractions_id);
				
					$next_node_name = $new_POIs[1];
					

					$this_edge_id= get_edge($current_node_name,$next_node_name);
					array_push($itin_edge_ids[$this_day], $this_edge_id);
					
					// set new POI to attractions visited
					array_push($attractions_visited,$new_POIs[1]);
				}
			}
			else {
				// if there is no attractions tag behind, then need to find new attractions
				/*
				if ($this_day<$days) {
					$this_day +=1;
					$had_lunch = false;
					$had_dinner = false;
				}
				else {
					break;
				}
				*/				
			}
			continue;
		}
		// set new POI to attractions visited
		$attractions_visited = array_merge($attractions_visited,$new_POIs);
		if (count($itin_node_ids[$this_day])>0){
			
			$last_point_id = $itin_node_ids[$this_day][count($itin_node_ids[$this_day])-1];
			$current_index= count($itin_node_ids[$this_day])-1;

			while (( ($last_point_id === "D") or ($last_point_id === "L"))and ($current_index>=1)){
				$current_index-=1;
				$last_point_id = $itin_node_ids[$this_day][$current_index];
			}
			if ( ($last_point_id === "D") or ($last_point_id === "L")) {
				continue;
			}
			//print($last_point_id);
			$sql = "Select name from attractions_hk_ta where node_id=$last_point_id";
			
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$last_point=$row["name"];
			
			// if POI is new, push POI into attractions
			$sql = "Select * from attractions_hk_ta where name =\"" . mysqli_real_escape_string($connection,$new_POIs[0]) . "\""; 
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			array_push($attractions,$new_POIs[0]);
			$attractions_id[$new_POIs[0]] = $row["node_id"];

			
			$this_edge_id= get_edge($last_point,$new_POIs[0]);
			array_push($itin_edge_ids[$this_day], $this_edge_id);

			
		}
		
		$sql = "Select node_id from attractions_hk_ta where name=\"". mysqli_real_escape_string($connection,$new_POIs[0])."\""; 
		$outputtext = "<script> console.log('itin_node_ids[$this_day], $new_POIs[0],  sql: ". $sql. "');</script>";
		echo 	$outputtext ;
			
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		array_push($itin_node_ids[$this_day], $row["node_id"]);
		
		for($x=1;$x<count($new_POIs);$x +=1){
			$sql = "Select node_id from attractions_hk_ta where name=\"". mysqli_real_escape_string($connection,$new_POIs[$x])."\""; 
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			array_push($itin_node_ids[$this_day], $row["node_id"]);
			$outputtext = "<script> console.log('itin_node_ids[$this_day], $new_POIs[0],  sql: ". $sql. "');</script>";
			echo 	$outputtext ;
				
	
			
			$this_edge_id = get_edge($new_POIs[$x-1],$new_POIs[$x]);
			array_push($itin_edge_ids[$this_day], $this_edge_id);			

		}
		$outputtext = "<script> console.log('itin_edge_ids[$this_day]: ". implode( ',' , $itin_edge_ids[$this_day] ) . "');</script>";
		echo 	$outputtext ;
	}
	
	
	$unvisited_num = count(array_values(array_diff($attractions,$attractions_visited)));
	
}



// post processing
// if there is a route from yau_tsim_mong to central_and_west, or vice versa
// then add star ferry in between
for($x=1;$x<=$days;$x+=1){
	$outputtext = "<script> console.log('itin edge id [$x] ". implode( ',' , $itin_edge_ids[$x] ) . "');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('itin node id [$x] ". implode( ',' , $itin_node_ids[$x] ) . "');</script>";
	echo 	$outputtext ;
	$attractions_visited=array_merge($attractions_visited,idToName($itin_node_ids[$x]));
	
	
}	


// update $attractions_visited to actual attractions filled in
$attractions_visited=array();
// translation table between id and district
$id_to_district=array();
if (count($attractions_id)>0){
	$sql = "Select * from attractions_hk_ta where node_id in (". implode(',',$attractions_id) .")";
	$result = mysqli_query($connection, $sql);
	while($row = mysqli_fetch_assoc($result)){
		$id_to_district[$row["node_id"]]=$row["json_district"];		
	}
}


$sql = "SELECT * from attractions_hk where name = \"Star Ferry\"";
$result = mysqli_query($connection, $sql);
$row = mysqli_fetch_assoc($result);
$star_ferry_id = $row["node_id"];
/*			
for($x=1;$x<=$days;$x+=1){
	
	$add_star_ferry=false;
	$attractions_visited=array_merge($attractions_visited,idToName($itin_node_ids[$x]));
	for($y=0;$y<count($itin_node_ids[$x])-1;$y+=1){
		$this_id = $itin_node_ids[$x][$y];
		$next_id = $itin_node_ids[$x][$y+1];
		if (($this_id === "L") or ($this_id=="D") or ($next_id == "L") or ($next_id=="D")){
			continue;
		}
		$this_district = $id_to_district[$this_id];
		$next_district = $id_to_district[$next_id];
		$this_name = array_search($this_id,$attractions_id);
		$next_name = array_search($next_id,$attractions_id);
		
		if ( !$add_star_ferry and (  ($this_district === "yau_tsim_mong") and ($next_district === "central_and_west"))OR (  ($next_district === "yau_tsim_mong") and ($this_district === "central_and_west"))){
			// add star ferry in between
			$add_star_ferry=true;
			

			$this_edge_id=addEdgeDB($this_name,$star_ferry_id,"transit");
			updateEdgeDB($this_edge_id);
				
			$next_edge_id=addEdgeDB($star_ferry_id,$next_name,"transit");
			updateEdgeDB($next_edge_id);

			$itin_node_ids[$x] = array_merge(  array_slice($itin_node_ids[$x],0,$y+1), array($star_ferry_id),array_slice($itin_node_ids[$x],$y+1)      );
			$itin_edge_ids[$x] = array_merge( array_slice($itin_edge_ids[$x],0,$y),array($this_edge_id,$next_edge_id),array_slice($itin_edge_ids[$x],$y+1) );
			array_push($attractions,"Star Ferry");
			$attractions_id["Star Ferry"] = $star_ferry_id;
		}
		
	}
		
}	
*/


for($x=1;$x<=$days;$x+=1){
	$outputtext = "<script> console.log('itin edge id [$x] ". implode( ',' , $itin_edge_ids[$x] ) . "');</script>";
	echo 	$outputtext ;
	$outputtext = "<script> console.log('itin node id [$x] ". implode( ',' , $itin_node_ids[$x] ) . "');</script>";
	echo 	$outputtext ;

	$attractions_visited=array_merge($attractions_visited,idToName($itin_node_ids[$x]));		
}	

$outputtext = "<script> console.log('attractions not visited ". implode( ',' , array_diff($attractions,$attractions_visited) ) . "');</script>";
	echo 	$outputtext ;

	
	
$attractions=$attractions_visited;
$attractions_id = array_intersect_key($attractions_id,array_fill_keys($attractions,0));

$selected_by_algo_and_routed_name = $attractions;

if (array_key_exists("user_duration_list",$_SESSION)){
	$user_duration_list=$_SESSION["user_duration_list"];
}else {$user_duration_list=array();}

foreach($itin_node_ids as $today_node_ids){
	foreach($today_node_ids as $node_id){
		if (($node_id !=="D") and ($node_id !== "L")){
			$sql = "SELECT node_id,duration from attractions_hk_ta WHERE node_id =$node_id";
			$result = mysqli_query($connection, $sql);
			$row = mysqli_fetch_assoc($result);
			$user_duration_list[ $row["node_id"]  ] = $row["duration"];
		}
	}
}


// print subcat attraction
foreach($subcat_attraction as $y=>$y_list){
	
		$outputtext = "<script> console.log('line(1717) subcat $y ');</script>";
	echo 	$outputtext ;
	foreach($y_list as $att){
		//echo $att;
		if (array_search($att,$attractions_id)!==false){
			$this_name = $attractions_info_by_id[$att]["name"];
			$outputtext = "<script> console.log('line(1717) subcat $this_name ');</script>";
			
			echo $outputtext;
			//echo "in subcat: " . $attractions_info_by_id[$att]["name"];
		}
	}
	
}
$_SESSION["user_duration_list"]=$user_duration_list;
	
	
			


?>