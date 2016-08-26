<?php 
require 'connect.php';
include 'functions.php';
session_start();
include 'fetchSession.php';

// Create helper array to sort attractions by visitday
//$all_node_ids = array();
$node_id_to_visitday = array();
for ($i=1; $i<=sizeof($itin_node_ids); $i++) {
	$dayi_node_ids = $itin_node_ids[$i];
	foreach ($dayi_node_ids as $node_id) {
		$node_id_to_visitday[$node_id] = $i;
		$all_node_ids[] = $node_id;
	}
}

// Get detailed node info for all nodes
$all_nodes_str = implode(',',$attractions_id);
$query = "SELECT * FROM attractions_hk_ta WHERE node_id IN ($all_nodes_str)";
$result = mysqli_query($connection, $query);
if (!$result) {
	var_dump($all_node_ids);
	echo "$query <br>";
	die("Database query failed.");
}
$attractions = array();
$i = 0;
while ($attractions[$i] = mysqli_fetch_assoc($result)) {
	$i++;
}

?>
<!DOCTYPE html>
<html lang="en">

<head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
  
  <title>OneMinPlan Core Overview</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
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
						echo "<li><a href='process.php?day=$i' class='btn btn-day'>Day $i</a></li>";
					}
				?>
				<li><a href="overview.php" class="btn btn-overview active">Overview</a></li>
			</ul>

		</div><!-- bg-overlay -->
	</section><!-- main -->

	<section id="overview" class="container">
		<header class="row">
			<h4>My Trip to Hong Kong</h4>
			<p>Dates: -</p>
			<p>Number of days: <?php echo $days;?></p>
			<p>Attractions visited: <?php echo count($attractions);?></p>
		</header><!-- header.row -->
		<div class="row">
			<div id="map-outer" class="col-sm-8">
				<div id="map-canvas"></div>
				<div id="legend-overlay" class="map-overlay">
					<p>
						<?php
						for ($i=1; $i<=$days; $i++) {
							echo "<img class='marker-sm' src='img/marker-day$i.png' /> Day $i ";
						}
						?>
					</p>
					<p>Click on a marker on the map to learn more!</p>
				</div><!-- #legend-overlay -->
				<div id="attraction-overlay" class="map-overlay">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<section class="row stop">
					<div class="col-sm-9 details">
						<div class="row">
							<div class="col-xs-3 col-sm-1 marker"><img class="img-responsive" src="img/location-pin.png" /></div>
							<div class="col-xs-9 col-sm-3 col-sm-offset-0 col-sm-push-8 label-outer"><a class="label label-food">Food</a></div><!-- label-outer -->
							<div class="col-xs-12 col-sm-8 col-sm-pull-3 location">
								<h5>DimDimSum Dim Sum Specialty Store&nbsp;&nbsp;<a role="button" tab-index="0" class="glyphicon glyphicon-star" title="Star Recommendation" data-toggle="tooltip"></a></h5>
								<p class="address"><img src="img/location-pin.png" class="icon" alt="address" /> 21-23 Man Ying St, Jordan, Kowloon</p>
							</div><!-- location -->
						</div><!-- row -->
						<div class="row">
							<div class="col-xs-1"><img src="img/description.png" class="icon" alt="description" /></div>
							<p class="col-xs-11 description">An award winner diner serving all-day dim sum.</p>
						</div><!-- row -->
						<div class="row">
							<div class="col-xs-1"><img src="img/light-bulb.jpg" class="icon" alt="tip" /></div>
							<p class="col-xs-11 tip">Try their har gaus (shrimp dumplings) and custard buns!</p>
						</div><!-- row -->
						<div class="row">
							<div class="col-xs-1"><img src="img/clock.png" class="icon" alt="duration" /></div>
							<p class="col-xs-11 duration">1 hour</p>
						</div><!-- row -->
					</div><!-- details -->
					<div class="col-sm-3 photo">
						<img src="img/dimsum.png" class="img-responsive img-thumbnail"/>
					</div><!-- photo -->
					</section><!-- row stop -->
				</div><!-- #attraction-overlay -->

			</div><!-- #map-outer -->
			<div id="attractions" class="col-sm-4">
				<h5>I will visit...</h5>
				<div id="attractions-list">
					
					<?php
						for ($i=0; $i < sizeof($attractions); $i++) {
							$attraction = $attractions[$i];
							global $shortlabels;
							$labels = explode('|',$attraction['category']);
							$shortlabel = trim($labels[0]);
							global $displayLabels;
							$displaylabel = $displayLabels[$shortlabel];
							$longitude = $attraction['longitude'];
							$latitude = $attraction['latitude'];
							$name = $attraction['name'];
							$address = $attraction['address'];
							$ranking = $attraction['popular_ranking'];
							$description = $attraction['description'];

							$tip = $attraction['tip'];
							$duration = $attraction['duration'];
							$photo = $attraction['photo'];
							$visitday = $node_id_to_visitday[$attraction['node_id']];
							if ($ranking == 0) {
								continue;
							}

							echo 
							"
							<article class='attraction panel panel-default $shortlabel' 
							data-title='$name' data-address='$address' data-longitude='$longitude' data-latitude='$latitude' data-label='$displaylabel'
							data-description='$description' data-tip='$tip' data-duration='$duration' data-photo='$photo' data-visitday='$visitday'>
								<div id='heading-$i' class='panel-heading row' role='tab'>
									<h6 class='panel-title col-xs-10'>
										<a role='button' data-toggle='collapse' data-parent='#accordion' href='#collapse-$i' aria-expanded='true' aria-controls='heading-$i'>
			      							$name<br>
			      							<small>#$ranking in Hong Kong</small>
			   							</a>
									</h6>
									<div class='col-xs-2 marker-outer'></div>
								</div><!-- #heading-$i.panel-heading -->
								<div id='collapse-$i' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading-$i'>
									<p>$description</p>
								</div><!-- #collapse-$i.panel-collapse -->
							</article><!-- .attraction.panel-default -->
							";
						}
					?>

					
				</div><!-- #accordion .panel-group -->
			</div><!-- #attractions -->
		</div><!-- row -->
	</section><!-- #overview -->

	<footer>
		<?php include 'footernav.php' ?>
	</footer>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?region=hk&key=AIzaSyBQ8d-tYXI8XugQAd_RSd08CFf8aj28Elk "></script>
	<script src="js/sticky-kit.min.js"></script>
	<script src="js/overview.js"></script>
</body>
<?php
	include 'disconnect.php';
?>