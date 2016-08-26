<?php
// Create a database connection
$dbhost = "localhost";
$dbuser = "timoth32_oneminp";
$dbpass = "p200430187";
$dbname = "timoth32_oneminplan";
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Test if connection occurred
if(mysqli_connect_errno()) {
	die("Database connection failed: " 
		. mysqli_connect_error() 
		. " (" . mysqli_connect_errno() . ")");
}

function displayAttraction($poi) {
	echo "<li>";
	$name = $poi->name;
	$ta_id = $poi->location_id;
	echo "<h3>$name<small> (TA ID: $ta_id)</small></h3>";
	// $latitude = $poi->latitude;
	// $longitude = $poi->longitude;
	// echo "<p>Coordinates: $latitude, $longitude</p>";
	echo "<ul>";
	$ranking = $poi->ranking_data->ranking;
	$rankingstr = $poi->ranking_data->ranking_string;
	if (empty($rankingstr)) {
		echo "<li>No ranking info</li>";
	} else {
		echo "<li>$rankingstr</li>";
	}
	// $rating_img_url = $poi->rating_image_url;
	// if (!empty($rating_img_url)) {
	// 	echo "<p>Rating image: <img src='$rating_img_url' /></p>";
	// } else {
	// 	echo "<p>No rating info</p>";
	// }
	$category = $poi->category->name;
	$category_localized = $poi->category->localized_name;
	$category_name = $category_localized;
	echo "<li>TA category name: $category_localized</li>";
	$subcategory = $poi->subcategory;
	$subs = array();
	foreach($subcategory as $sub) {
		$subs[] = $sub->localized_name;
	}
	$subs_str = implode(',',$subs);
	echo "<li>TA subcategory name(s): $subs_str</li>";	
	$attraction_types = $poi->attraction_types;	
	if (!empty($attraction_types)) {
		$types = array();
		foreach($attraction_types as $type) {
			$types[] = $type->localized_name;
		}
		$types_str = implode(',',$types);
		echo "<li>TA Attraction type name(s): $types_str</li>";
	}
	echo "</ul>";
	echo "</li>";
}

function process_TA_subs_types($TA_subs,$TA_types) {
	global $connection;
	// Takes in TA subcategories and types (arrays)
	// Returns an object with
	// 1. ta_subs_str, a | separated list of TA_subs (string)
	// 2. ta_subs_display_str, same as above in human readable form (string)
	// 3. ta_types_str, a | separated list of TA_types (string)
	// 4. ta_types_display_str, same as above in human readable form (string)
	// 5. cats_str, a | separated list of our categories (string)
	// 6. subs_str, a | separeted list of our subcategories (string)
	$ta_subs_str = '';
	$ta_subs_display_str = '';
	$ta_types_str = '';
	$ta_types_display_str = '';
	$cats = array();
	$subs = array();
	foreach($TA_subs as $TA_sub) {
		if ($ta_subs_str != '') {
			$ta_subs_str .= '|';
		}
		if ($ta_subs_display_str != '') {
			$ta_subs_display_str .= '|';
		}
		$ta_sub_name = $TA_sub->name;
		$ta_subs_str .= $ta_sub_name;
		$ta_sub_display = $TA_sub->localized_name;
		$ta_subs_display_str .= $ta_sub_display;
		// Check if this ta_sub is already in our database
		$sql = "SELECT * FROM ta_subs
				WHERE ta_sub_name = '$ta_sub_name';";
		$results = mysqli_query($connection, $sql);
		if (mysqli_num_rows($results) == 0) {
			// Add a new TA Subcategory 
			$sql = "INSERT INTO ta_subs (ta_sub_name,ta_sub_display)
				VALUES ('$ta_sub_name','$ta_sub_display');";
			if (mysqli_query($connection, $sql)) {
			    echo "<p style='color:cyan'>Added new ta_sub. $ta_sub_name ($ta_sub_display)</p>";
			} else {
			    echo "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
			}
		} else {
			// Fetch equivalent 1MP cats and subs
			$row = mysqli_fetch_assoc($results);
			$fetched_cats = explode('|',$row['cats']);
			foreach($fetched_cats as $fetched_cat) {
				if (!empty($fetched_cat)) {
					if (!in_array($fetched_cat,$cats)) {
						$cats[] = $fetched_cat;
					}
				}	
			}
			$fetched_subs = explode('|',$row['subs']);
			foreach($fetched_subs as $fetched_sub) {
				if (!empty($fetched_sub)) {
					if (!in_array($fetched_sub,$subs)) {
						$subs[] = $fetched_sub;
					}
				}
			}
			
		}

	}

	foreach($TA_types as $TA_type) {
		if ($ta_types_str != '') {
			$ta_types_str .= '|';
		}
		if ($ta_types_display_str != '') {
			$ta_types_display_str .= '|';
		}
		$ta_type_name = $TA_type->name;
		$ta_types_str .= $ta_type_name;
		$ta_type_display = $TA_type->localized_name;
		$ta_types_display_str .= $ta_type_display;
		// Check if this ta_type is already in our database
		$sql = "SELECT * FROM ta_types
				WHERE ta_type_name = '$ta_type_name';";
		$results = mysqli_query($connection, $sql);
		if (mysqli_num_rows($results) == 0) {
			// Add a new TA Attraction Type 
			$sql = "INSERT INTO ta_types (ta_type_name,ta_type_display)
				VALUES ('$ta_type_name','$ta_type_display');";
			if (mysqli_query($connection, $sql)) {
			    echo "<p style='color:cyan'>Added new ta_sub. $ta_type_name ($ta_type_display)</p>";
			} else {
			    echo "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
			}
		} else {
			// Fetch equivalent 1MP categories and subs
			$row = mysqli_fetch_assoc($results);
			$fetched_cats = explode('|',$row['cats']);
			foreach($fetched_cats as $fetched_cat) {
				if (!empty($fetched_cat)) {
					if (!in_array($fetched_cat,$cats)) {
						$cats[] = $fetched_cat;
					}
				}	
			}
			$fetched_subs = explode('|',$row['subs']);
			foreach($fetched_subs as $fetched_sub) {
				if (!empty($fetched_sub)) {
					if (!in_array($fetched_sub,$subs)) {
						$subs[] = $fetched_sub;
					}
				}
			}
		}

	}


	$return_obj = array();
	$return_obj['ta_subs_str'] = $ta_subs_str;
	$return_obj['ta_subs_display_str'] = $ta_subs_display_str;
	$return_obj['ta_types_str'] = $ta_types_str;
	$return_obj['ta_types_display_str'] = $ta_types_display_str;
	$return_obj['cats_str'] = implode('|',$cats);
	$return_obj['subs_str'] = implode('|',$subs);
	return $return_obj;
}

function getCategories($TA_subs,$TA_types) {
	global $connection;
	// Takes in TA subcategories and types (arrays)
	// Converts them into 1MP main categories string to store

	$categories = '';
	$first = true;
	foreach($TA_subs as $TA_sub) {
		if ($first) {
			$first = false;
		} else {
			$categories .= '|';
		}
		$ta_sub_name = $TA_sub->name;
		$ta_sub_display = $TA_sub->localized_name;
		// Check if this ta_sub is already in our database
		$sql = "SELECT * FROM ta_subs
				WHERE ta_sub_name = '$ta_sub_name';";
		$results = mysqli_query($connection, $sql);
		if (mysqli_num_rows($results) == 0) {
			$sql = "INSERT INTO ta_subs (ta_sub_name,ta_sub_display)
				VALUES ('$ta_sub_name','$ta_sub_display');";
			if (mysqli_query($connection, $sql)) {
			    echo "<p style='color:cyan'>Added new ta_sub. $ta_sub_name ($ta_sub_display)</p>";
			} else {
			    echo "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
			}
		}
		//
		$categories .= $ta_sub_name;
	}

	return $categories;
}

function getSubcategories($TA_subs,$TA_types) {
	global $connection;
	// Takes in TA subcategories and types (arrays)
	// Converts them into 1MP subcategories string to store

	$subs = '';
	$first = true;
	foreach($TA_types as $TA_type) {
		if ($first) {
			$first = false;
		} else {
			$subs .= '|';
		}
		$ta_type_name = $TA_type->name;
		$ta_type_display = $TA_type->localized_name;
		// Check if this ta_type is already in our database
		$sql = "SELECT * FROM ta_types
				WHERE ta_type_name = '$ta_type_name';";
		$results = mysqli_query($connection, $sql);
		if (mysqli_num_rows($results) == 0) {
			$sql = "INSERT INTO ta_types (ta_type_name,ta_type_display)
				VALUES ('$ta_type_name','$ta_type_display');";
			if (mysqli_query($connection, $sql)) {
			    echo "<p style='color:purple'>Added new ta_type. $ta_type_name ($ta_type_display)</p>";
			} else {
			    echo "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
			}
		}
		//
		$subs .= $TA_type->name;
	}
	return $subs;
}

function insertAttraction($poi) {
	// Takes in an attraction object (parsed from TA API returned JSON object)
	// If there is a ranking, inserts into database if no duplicates
	global $connection;
	// $poi 
	$name = mysqli_real_escape_string($connection,$poi->name);
	$ta_id = $poi->location_id;
	$latitude = $poi->latitude;
	$longitude = $poi->longitude;
	$ranking = $poi->ranking_data->ranking;
	$rankingstr = $poi->ranking_data->ranking_string;
	$TA_category = $poi->category->name;
	$TA_subs = $poi->subcategory;
	$TA_types = $poi->attraction_types;
	//$category = getCategories($TA_subs,$TA_types);
	//$subcategory = getSubcategories($TA_subs,$TA_types);
	$cat_info = process_TA_subs_types($TA_subs,$TA_types);
	$cats_str = $cat_info['cats_str'];
	$subs_str = $cat_info['subs_str'];
	$ta_subs_str = $cat_info['ta_subs_str'];
	$ta_subs_display_str = $cat_info['ta_subs_display_str'];
	$ta_types_str = $cat_info['ta_types_str'];
	$ta_types_display_str = $cat_info['ta_types_display_str'];

	$return_str = '';

	if (!empty($ranking)) {
		$sql = "SELECT ta_id from attractions_hk_ta
				WHERE ta_id = $ta_id;";
		$results = mysqli_query($connection, $sql);
		if (mysqli_num_rows($results) > 0) {
			$return_str .= "<p style='color:SkyBlue;'>Record is already in database =D</p>";
		} else {
			$sql = 
			"INSERT INTO attractions_hk_ta 
				(name,ta_id,latitude,longitude,category,subcategory,ta_subs,ta_subs_display,ta_types,ta_types_display,popular_ranking,rankingstr)
			VALUES 
				('$name',$ta_id,'$latitude','$longitude','$cats_str','$subs_str','$ta_subs_str','$ta_subs_display_str','$ta_types_str','$ta_types_display_str',$ranking,'$rankingstr');
			";	
			if (mysqli_query($connection, $sql)) {
			    $return_str .= "<p style='color:Green;'>New record created successfully =)</p>";
			} else {
			    $return_str .= "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
			}
		}

	} else {
		// Attraction has no ranking, so it will not be saved.
		$return_str .= "<p style='color:Orange;'>No ranking :o - record will not be created.</p>";
	}
	return $return_str;
}

// set duration to 1hr
function insertRestaurant($poi) {
	// Takes in an restaurant object (parsed from TA API returned JSON object)
	// If there is a ranking, inserts into database if no duplicates
	global $connection;
	// $poi 
	$name = mysqli_real_escape_string($connection,$poi->name);
	$ta_id = $poi->location_id;
	$num_reviews = $poi->num_reviews;
	$price_level = $poi->price_level;
	$rating = $poi->rating;
	$address_str = mysqli_real_escape_string($connection,$poi->address_obj->address_string); 
	$review_rating_count="";
	// an array with entry from 1 to 5
	if (array_key_exists("review_rating_count",$poi)){
		$review_rating_count = implode('|',array_values($poi->review_rating_count));
	}
	$latitude = $poi->latitude;
	$longitude = $poi->longitude;
	$ranking=0;
	$rankingstr='';
	if (!is_null($poi->ranking_data)){
		$ranking = $poi->ranking_data->ranking;
		$rankingstr = $poi->ranking_data->ranking_string;
	}
	
	$TA_category = $poi->category->name;
	$TA_subs = $poi->subcategory;
	$TA_types='';
	
	if (property_exists ( $poi , "attraction_types" )){
		$TA_types = $poi->attraction_types;
	}
	
	// cuisine type
	$cuisine_array = array();
	$x=0;
	foreach ($poi->cuisine as $cuisine_arr){
		$cuisine_array[$x] = $cuisine_arr->name;
		$x+=1;
	}
	$cuisine_str = mysqli_real_escape_string($connection,implode('|',$cuisine_array));
	/*
	$cat_info = process_TA_subs_types($TA_subs,$TA_types);
	$cats_str = $cat_info['cats_str'];
	$subs_str = $cat_info['subs_str'];
	$ta_subs_str = $cat_info['ta_subs_str'];
	$ta_subs_display_str = $cat_info['ta_subs_display_str'];
	$ta_types_str = $cat_info['ta_types_str'];
	$ta_types_display_str = $cat_info['ta_types_display_str'];
	*/
	
	$cats_str = "";
	$subs_str = "";
	$ta_subs_str = "";
	$ta_subs_display_str = "";
	$ta_types_str = "";
	$ta_types_display_str = "";
	
	
	
	$cats_str = $poi->category->name;
	
	$subcat_array = array();
	$x=0;
	foreach ($poi->subcategory as $subcat_pair){
		$subcat_array[$x] = $subcat_pair->name;
		$x+=1;
	}
	
	$subs_str = mysqli_real_escape_string($connection,implode('|',$subcat_array));
	$return_str = '';

	if (!empty($ranking)) {
		$sql = "SELECT ta_id from attractions_hk_ta
				WHERE ta_id = $ta_id;";
		$results = mysqli_query($connection, $sql);
		if (mysqli_num_rows($results) > 0) {
			$return_str .= "<p style='color:SkyBlue;'>Record is already in database =D</p>";
		} else {
			$sql = 
			"INSERT INTO attractions_hk_ta 
				(name,ta_id,latitude,longitude,category,subcategory,ta_subs,ta_subs_display,ta_types,ta_types_display,popular_ranking,rankingstr,rating,num_reviews,review_rating_count,price_level,cuisine_str,address,duration)
			VALUES 
				('$name',$ta_id,'$latitude','$longitude','$cats_str','$subs_str','$ta_subs_str','$ta_subs_display_str','$ta_types_str','$ta_types_display_str',$ranking,'$rankingstr','$rating','$num_reviews','$review_rating_count','$price_level','$cuisine_str','$address_str',1);
			";	
			if (mysqli_query($connection, $sql)) {
			    $return_str .= "<p style='color:Green;'>New record created successfully =)</p>";
			} else {
			    $return_str .= "<p>Error: " . $sql . "<br>" . mysqli_error($connection) . "</p>";
			}
		}

	} else {
		// Attraction has no ranking, so it will not be saved.
		$return_str .= "<p style='color:Orange;'>No ranking :o - record will not be created.</p>";
	}
	return $return_str;
}
?>