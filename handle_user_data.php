<?php
// manage json string into information
$dbhost = "localhost";
$dbuser = "oneminpl_admin";
$dbpass = "^uat#Vf(n)[q";
$dbname = "oneminpl_hk";
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);


	$sql = "select * from attractions_hk_ta where node_id<=534 order by node_id";
	$result= mysqli_query($connection,$sql);

	$attractions_info_by_name=array();
	$attractions_info_by_id=array();
	
	$single_line_array=array("city","days","pace","starttime","custom_number","total_number","itin_node_name","itin_node_ids","itin_edge_ids");
	$single_line_array2=array("city","days","pace","starttime","custom_number","total_number","itin_node_name","itin_node_ids","itin_edge_ids");
	
	
	while($row= mysqli_fetch_assoc($result)){
		$attractions_info_by_name[$row["name"]]=$row;
		$attractions_info_by_id[$row["node_id"]]=$row;
		
		array_push($single_line_array,$row["name"]);
		array_push($single_line_array2,$row["node_id"]);
		
	}	 
	  
	$sql = "Select * from user_data ";
	$result= mysqli_query($connection,$sql);
	
	$data_list = array($single_line_array,$single_line_array2);

	
	while($row = mysqli_fetch_assoc($result)){
		$single_line_array = array();
		$json_string = $row["json_string"]; 
		$user_data = json_decode($json_string,true );//return an array
		$city = $user_data["user_settings"]["city"];
		$days = $user_data["user_settings"]["days"];
		$pace = $user_data["user_settings"]["pace"];
		$starttime = $user_data["user_settings"]["starttime"];
		$custom_number = $user_data["user_settings"]["custom_number"];
		$total_number = $user_data["user_settings"]["total_number"];
		
		$single_line_array=array($city,$days,$pace,$starttime,$custom_number,$total_number);
		
		$removed_by_user_name = $user_data["removed_by_user_name"];
		$added_by_user_name = $user_data["added_by_user_name"];
		$selected_by_algo_and_routed_name = $user_data["selected_by_algo_and_routed_name"];
		$selected_by_algo_name = $user_data["selected_by_algo_name"];
		$selected_but_not_routed_name = array_values(  array_diff($selected_by_algo_name,$selected_by_algo_and_routed_name));
		$liked_by_user_name = $user_data["liked_by_user_name"];
		
		
		$itin_node_ids = $user_data["itin_node_ids"];
		$itin_edge_ids = $user_data["itin_edge_ids"];
		
		$itin_node_name=array();
		$itin_node_name_str="";
		$itin_node_ids_str = "";
		
		$itin_edge_ids_str="";
		
		foreach($itin_node_ids as $day=>$this_day_itin){
			if (count($this_day_itin)==0){continue;}
			$itin_node_name[$day]=array();
			foreach($this_day_itin as $index => $current_id){
				//if (($current_id !=="L") and ($current_id !== "D")){
					
					if (array_key_exists($current_id,$attractions_info_by_id)){
				$itin_node_name[$day][$index]=$attractions_info_by_id[$current_id]["name"];
				}else {$itin_node_name[$day][$index]=$current_id;}
				
			}
			$itin_node_name_str .="|". implode(',',$itin_node_name[$day]);
			$itin_node_ids_str .="|". implode(',',$itin_node_ids[$day]);
			$itin_edge_ids_str .= "|". implode(',',$itin_edge_ids[$day]);	
		
		}
			array_push($single_line_array, $itin_node_name_str);
			array_push($single_line_array, $itin_node_ids_str);
			array_push($single_line_array, $itin_edge_ids_str);

		
		
		
		foreach ($attractions_info_by_name as $name=>$row){
			
			if (array_search($name, $removed_by_user_name)!==False){
				$score = -1;				
			}
			elseif (array_search($name, $liked_by_user_name)!==False){
				$score=3;
			}
			elseif (array_search($name, $selected_but_not_routed_name)!==False){
				$score=1;
			}
			elseif (array_search($name, $selected_by_algo_and_routed_name)!==False){
				$score=2;
			}
			elseif (array_search($name, $added_by_user_name)!==False){
				$score=4;
			}			else {
				$score=0;
			}
				
			
			array_push($single_line_array, $score);
			
		}
		array_push($data_list,$single_line_array);
		
	}





	$fp = fopen('user_data.csv', 'w');

	foreach ($data_list as $fields) {
		fputcsv($fp, $fields);
	}

	fclose($fp);

	echo "finish writing csv";
	echo "<br>";





?>


<!DOCTYPE html>
<html>
   <head>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   </head>
   <body>
     <p> Oneminplan user data</p>
<button type="button" onclick="location.href='download.php'">Download User data</button>


   </body>
</html>


