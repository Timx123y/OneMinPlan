<?php
require 'connect.php';
global $connection;
$search_string = preg_replace("/[^A-Za-z0-9]/", " ", $_POST['query']);
$search_string = mysqli_real_escape_string($connection,$search_string);
echo("Results for <strong>$search_string</strong>:<br>");
$keywords = explode(' ', $search_string);
$likes = '';
foreach ($keywords as $keyword) {
	$likes .= " AND name LIKE '%$keyword%' ";
}
// Check Length More Than One Character
if (strlen($search_string) >= 1 && $search_string !== ' ') {
	// Build query
	$query = "SELECT * FROM hotels_hk_ta 
				WHERE category = 'hotel' $likes
				ORDER BY popular_ranking;";
	//echo "$query<br>";
	$result = mysqli_query($connection,$query);
	if (mysqli_num_rows($result) > 0){
		while($row = mysqli_fetch_assoc($result)) {
			$hotels[] = $row;		
		}
		$returnHTML = '';
		foreach ($hotels as $hotel) {
			$hotel_id = $hotel['hotel_id'];
			$name = $hotel['name'];
			$latitude = $hotel['latitude'];
			$longitude = $hotel['longitude'];
			$rank = $hotel['popular_ranking'];
			$description = $hotel['description'];
			
			$shortname = urlencode($name);
			$returnHTML .= 
			"<article class='hotel container-fluid' data-title='$name' data-latitude='$latitude' data-longitude='$longitude'>
				<div class='row'>
					<div class='col-xs-10'> <h5>$name</h5> </div>
					<div class='col-xs-1'> 
						<a href='?addhotel=$hotel_id' role='button' tab-index='0' class='glyphicon glyphicon-plus' data-toggle='tooltip' title='Add'></a>
					</div>
				</div><!-- row -->
				<div class='row'>
					<div class='col-xs-10'>
						<small>#$rank in Hong Kong</small>
						<p>$description</p>
					</div>
					<div class='col-xs-2 marker-outer'>
						<a href='#' role='button' class='markerlink'>
							<img class='marker img-responsive' src='img/marker-day1.png' />
						</a>
						
					</div>
				</div>
				
			</article>
			";
		}
	} else {
		$returnHTML = 'no results';
	}
	echo $returnHTML;
}
include 'disconnect.php';
?>