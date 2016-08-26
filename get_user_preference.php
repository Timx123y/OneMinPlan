<?php
// this code selects attractions which matches users preference
$conditions = "subcategory LIKE '%core%' and  ";

// initialize attractions and attractions_id
$attractions = array();
//$attractions_info_by_id=array();
//$attractions_info_by_name=array();

// array_fill(0,mysqli_num_rows($result),"");
$attractions_id = array();
$ind=0;

// THE attractions sequence
$attractions_seq_list=array("Peak Tram","Victoria Peak (The Peak)","Peak Tower","Lion's Pavilion at The Peak","Ngong Ping Village (Ngong Ping 360)","Big Buddha","Po Lin (Precious Lotus) Monastery");
$attractions_seq_list1=array("Peak Tram","Victoria Peak (The Peak)","Peak Tower","Lion's Pavilion at The Peak");

$attractions_seq_list_escape = array();
for($i=0;$i<count($attractions_seq_list);$i+=1){
	$attractions_seq_list_escape[$i]=mysqli_real_escape_string($connection,$attractions_seq_list[$i]);
}
$attractions_seq_str_escape = "\"". implode('","', $attractions_seq_list_escape) . "\"";

$attractions_seq_list1_escape = array();
for($i=0;$i<count($attractions_seq_list1);$i+=1){
	$attractions_seq_list1_escape[$i]=mysqli_real_escape_string($connection,$attractions_seq_list1[$i]);
}

$attractions_seq1_str_escape = "\"". implode('","', $attractions_seq_list1_escape) . "\"";

// extract settings from user input
//$days = 3;//default days = 3
$pref_list = array();
$subcatlist = array();
$subcat_attraction = array();

$cuisine_type=array();
$price_level=array();
$_SESSION["user_settings"] = $_POST;
$must_route_attractions=array();
foreach ($_POST as $x => $x_value)	{
	
	if ($x == "city"){ $city = $x_value;}
	else if ($x == "days") {$days = $x_value;}
	else if ($x == "starttime") {$starttimeh = $x_value;}
	else if ($x == "pace") {$pace = $x_value;}
	else if ($x == "custom_number"){$custom_number = $x_value;}
	else if ($x == "total_number"){$total_number = $x_value;}
	//else if ($x == "selected_attractions") { $must_route_attractions= $x_value;}
	//else if ($x == "price_level") {$price_level = $x_value;}
	//else if ($x == "cuisine_type") {$cuisine_type = $x_value;}
	//if (!($x == "city" OR $x == "days" OR $x == "starttime" OR $x == "pace"))
	else {
		$pref_list= array_merge( $pref_list,$x_value);
	}
}

	if (count($pref_list)>0){
		foreach ($pref_list as $y=>$y_value){
			//echo $y_value . ", ";
			// translate from html input to actual subcategories in table
			
			/*
			switch ($y_value){
				case "lookoutpoints" :
				  $text_str = "Lookout points";
				  break;
				case "landmarks":
				  $text_str = "Landmarks";
				  break;
				case "scenic":
				  $text_str = "Scenic";
				  break;  
				case "religion":
				  $text_str = "Religion";
				  break;   
				case "malls":
				  $text_str = "Malls";
				  break; 
				case "architecture":
				  $text_str = "Architecture";
				  break; 
				case "museums":
				  $text_str = "Museums";
				  break; 
				case "localgems":
				  $text_str = "Local gems";
				  break; 
				case "gambling":
				  $text_str = "Gambling";
				  break;   
				case "specialtiesgifts":
				  $text_str = "Specialities and gifts";
				  break;  
				case "specialtiesgifts":
				  $text_str = "Specialities and gifts";
				  break;  
				case "parkszoos":
				  $text_str = "Parks";
				  break;    
				default:
					$text_str =$y_value;
			}
			*/
			
			/*

			$sql = "SELECT * from ta_subs where subs like \"%$y_value%\" ";
			$result = mysqli_query($connection, $sql);
			while($row = mysqli_fetch_assoc($result)){
				$conditions =  $conditions ." OR subcategory LIKE " . "'%" . $row["ta_sub_name"] . "%'";
			}
			//$conditions =  $conditions ." OR subcategory LIKE " . "'%" . $text_str . "%'";	
			$sql = "SELECT * from ta_types where subs like \"%$y_value%\" ";
			$result = mysqli_query($connection, $sql);
			while($row = mysqli_fetch_assoc($result)){
				$conditions =  $conditions ." OR subcategory LIKE " . "'%" . $row["ta_type_name"] . "%'";
			}	
			*/
			// $conditions =  $conditions ." OR subcategory LIKE " . "'%" . $y_value . "%'";
			array_push($subcatlist,$y_value);
			
			// for each condition, select relevant attractions
			$limit = ceil($custom_number*$days/count($pref_list));
			$condition_attractions_id ="";
			if (count($attractions_id)>0) {$condition_attractions_id ="AND (NOT (node_id in (" . implode(',', $attractions_id) .   "))) ";}
			$sql = " SELECT * FROM attractions_hk_ta WHERE (subcategory LIKE ".  "'%" . $y_value . "%'"  .")  $condition_attractions_id AND (name != 'Star Ferry') AND (category != 'restaurant') AND (NOT (category=\"Hotel\")) AND (NOT( name in ($attractions_seq_str_escape)))  AND  duration>0  AND custom_rating>0 AND rankingstr like \"%things to do in Hong Kong\"" . " ORDER BY custom_rating DESC, popular_ranking ASC limit $limit" ;
			
			$result = mysqli_query($connection, $sql);
			$subcat_attraction[$y_value]=array();
			while($row = mysqli_fetch_assoc($result)) {
				$attractions[$ind] = $row["name"];
				
				$attractions_id[$row["name"]] = $row["node_id"];
				
				//$attractions_info_by_name[$row["name"]]=$row;
				//$attractions_info_by_id[$row["node_id"]]=$row;
				
				$ind += 1;
				array_push($subcat_attraction[$y_value],$attractions_id[$row["name"]]);
			}
			
		}
		
	} else {
		// if user select no attractions, then select core 
		$all_num = $days*$total_number;
		$sql = " SELECT * FROM attractions_hk_ta WHERE (subcategory LIKE '%core%')  AND (name != 'Star Ferry') AND (category != 'restaurant') AND (NOT (category=\"Hotel\")) AND  duration>0  AND custom_rating>0 AND rankingstr like \"%things to do in Hong Kong\"" . " ORDER BY custom_rating DESC, popular_ranking ASC limit $all_num" ;

		$result = mysqli_query($connection, $sql);
		while($row = mysqli_fetch_assoc($result)) {
		$attractions[$ind] = $row["name"];
		$attractions_id[$row["name"]] = $row["node_id"];
		$ind += 1;
		}
			
			
			
	}
		//echo "<br>";


// Select at most ($days*5) many attractions
/*
$sql = " SELECT * FROM attractions_hk_ta WHERE(" . $conditions . ")  AND (NOT (category=\"Hotel\")) AND  duration>0  AND custom_rating>0 AND rankingstr like \"%things to do in Hong Kong\"" . " ORDER BY custom_rating DESC, popular_ranking ASC limit " . ($days*5);

$result = mysqli_query($connection, $sql);
while($row = mysqli_fetch_assoc($result)) {
  //  //echo "name: " . $row["name"]. " | subcategory: " . $row["subcategory"] . " | popular_ranking:"  . $row["popular_ranking"]. "<br>";
	$attractions[$ind] = $row["name"];
	$attractions_id[$row["name"]] = $row["node_id"];
	$ind += 1;
}
*/




// FOR now, need not add attraction sequence
// if there is one day, add the first day sequence
// if more than one, add both 2 days sequence
//----------------------------------------------------------------------------------------
/*
$condition_attractions_id ="";
if (count($attractions_id)>0) {$condition_attractions_id ="AND (NOT (node_id in (" . implode(',', $attractions_id) .   "))) ";}
			
if ($days===1){
	$sql = " SELECT * FROM attractions_hk_ta WHERE 1 $condition_attractions_id AND name in ($attractions_seq1_str_escape)";
} else {
	$sql = " SELECT * FROM attractions_hk_ta WHERE 1 $condition_attractions_id AND name in ($attractions_seq_str_escape)";
}
$result = mysqli_query($connection, $sql);
while($row = mysqli_fetch_assoc($result)) {
	$attractions[$ind] = $row["name"];
	$attractions_id[$row["name"]] = $row["node_id"];
	//$attractions_info_by_name[$row["name"]]=$row;
	//$attractions_info_by_id[$row["node_id"]]=$row;
	$ind += 1;
}
*/
//----------------------------------------------------------------------------------------


	
// if not enough, add CORE, high ranking attractions
// attractions not used will be discarded later in the generate itin php

/*
$condition_attractions_id ="";
if (count($attractions_id)>0) {$condition_attractions_id ="AND (NOT (node_id in (" . implode(',', $attractions_id) .   "))) ";}
	
$sql = "SELECT * from attractions_hk_ta WHERE 1 $condition_attractions_id AND (subcategory  LIKE \"%core%\") AND (NOT (category=\"Hotel\")) AND (category != 'restaurant') AND (name != 'Star Ferry') AND  duration>0 AND custom_rating>0 AND  rankingstr like \"%things to do in Hong Kong\" ORDER BY custom_rating DESC, popular_ranking ASC limit " .  max(0,($days*$total_number-count($attractions_id)));

$outputtext = "<script> console.log('user pref sql: ". $sql . "');</script>";
echo 	$outputtext;

$result = mysqli_query($connection, $sql);
while($row = mysqli_fetch_assoc($result)) {
	$attractions[$ind] = $row["name"];
	$attractions_id[$row["name"]] = $row["node_id"];
	//$attractions_info_by_name[$row["name"]]=$row;
	//$attractions_info_by_id[$row["node_id"]]=$row;
	$ind += 1;
}
*/

// restaurants

//local: chinese & asian
//budget: price level 1
//fine dine: price level 3 4 
//drinks: pub

if (array_search("localfood",$pref_list)!==false) { $cuisine_type=array_merge($cuisine_type,array("chinese","asian")); }
if (array_search("budgetstreet",$pref_list)!==false) { $price_level=array_merge($price_level,array(1)); }
if (array_search("finedine",$pref_list)!==false) { $price_level=array_merge($price_level,array(3,4)); }
if (array_search("drinks",$pref_list)!==false) { $cuisine_type=array_merge($cuisine_type,array("pub")); }




// if not enough, add  high ranking attractions until there are 9 attractions per day
// attractions not used will be discarded later in the generate itin php

/*
$condition_attractions_id ="";
if (count($attractions_id)>0) {$condition_attractions_id ="AND (NOT (node_id in (" . implode(',', $attractions_id) .   "))) ";}
	
$sql = "SELECT * from attractions_hk_ta WHERE 1 $condition_attractions_id  AND (NOT (category=\"Hotel\")) AND (category != 'restaurant') AND (name != 'Star Ferry') AND  duration>0 AND custom_rating>0 AND  rankingstr like \"%things to do in Hong Kong\" ORDER BY custom_rating DESC, popular_ranking ASC limit " . max(0,($days*$total_number-count($attractions_id)));

$outputtext = "<script> console.log('user pref sql: ". $sql . "');</script>";
echo 	$outputtext;



$result = mysqli_query($connection, $sql);
while($row = mysqli_fetch_assoc($result)) {
	$attractions[$ind] = $row["name"];
	$attractions_id[$row["name"]] = $row["node_id"];
	//$attractions_info_by_name[$row["name"]]=$row;
	//$attractions_info_by_id[$row["node_id"]]=$row;
	$ind += 1;
}
*/
// get time constraint

$selected_by_algo_name = $attractions;

// add must_route_attractions;
 $_SESSION['price_level']=$price_level;
 $_SESSION['cuisine_type']=$cuisine_type;

?>