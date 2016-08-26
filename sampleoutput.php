<?php 
	require 'connect.php';
	include 'functions.php';
	$days = 5;
	if ((isset($_GET['day'])) && !empty($_GET['day'])){
		$today = $_GET['day'];
	} else {
		$today = 1;
	}

	// Hardcoded Information for the Core Itinerary
	$itin_edge_ids = array();
	$itin_node_ids = array();
	// Day 1
	// edge2 from Peak Tram(1) to The Peak(2)
	// edge3 from The Peak to 2IFC (transfer)
	// edge18 from 2IFC to Star Ferry Pier(3)
	// edge4 from Star Ferry Pier(3) to TST Promenade(4)
	$itin_edge_ids[1] = array(2,3,18,4);
	$itin_node_ids[1] = array(1,2,3,4);
	// Day 2
	// edge6 from Ngong Ping 360(6) to Big Buddha(7)
	// edge7 from Big Buddha(7) to Po Lin Monastery(8)
	$itin_edge_ids[2] = array(6,7);
	$itin_node_ids[2] = array(6,7,8);
	// Day 3
	// edge8 from Harbour City(9) to Clock Tower(10)
	// edge9 from Clock Tower(10) to Avenue of Stars(11)
	// edge10 from Avenue of Stars(11) to Nathan Road(12)
	// edge11 from Nathan Road(12) to Goldfish Market(13)
	$itin_edge_ids[3] = array(8,9,10,11);
	$itin_node_ids[3] = array(9,10,11,12,13);
	// Day 4
	// edge19 from Golden Bauhinia Square(15) to Fleming Road (transfer)
	// edge12 from Fleming Road to Times Square(17)
	// edge14 from Times Square(17) to Sogo(18)
	// edge15 from Sogo(18) to Happy Valley Racecourse(19)
	$itin_edge_ids[4] = array(19,12,14,15);
	$itin_node_ids[4] = array(15,17,18,19);
	// Day 5
	// edge 16 from Nan Lian Garden(20) to Chu Lin Nunnery(21)
	$itin_edge_ids[5] = array(16);
	$itin_node_ids[5] = array(20,21);

	// Get the detailed edge infos for today
	$today_edges = array();
	$today_edge_ids = $itin_edge_ids[$today];
	foreach($today_edge_ids as $edge_id) {
		$query = "SELECT * FROM graph_hk WHERE edge_id = $edge_id";
		$result = mysqli_query($connection, $query);
		if (!$result) {
			die("Database query failed.");
		}
		$today_edges[$edge_id] = mysqli_fetch_assoc($result);
	}
	
	// Get the detailed node infos for today
	$today_nodes = array();
	$today_node_ids = $itin_node_ids[$today];
	foreach($today_node_ids as $node_id) {
		$query = "SELECT * FROM attractions_hk WHERE node_id = $node_id";
		$result = mysqli_query($connection, $query);
		if (!$result) {
			die("Database query failed.");
		}
		$today_nodes[$node_id] = mysqli_fetch_assoc($result);
		echo "<br>";
	}
	// Config start time
	$starttime = array();
	$starttime['h'] = 9;
	$starttime['m'] = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>OneMinPlan Core Itinerary</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/results.css">
  <link rel="stylesheet" href="css/output.css">
  <script src="js/prefixfree.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<a class="navbar-brand" href="#main">OneMinPlan</a>
	          <button type="button" class="btn navbar-btn navbar-right">Sign in</button> 
		</div><!-- container -->
	</nav>

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
							echo "Day $today - ";
							echo (4 + $today);
							echo "th August 2015";
						?>
					</h4>
				</header><!-- row -->
				<?php
					$node_i = 0;
					$edge_i = 0;	
					$currenttime = $starttime;
					while ($node_i < count($today_nodes)) {
						// Get info for current node
						$node_id = $today_node_ids[$node_i];
						$node = $today_nodes[$node_id];
						$name = $node['name'];
						$timestr = displayTime($currenttime);
						$labels = $node['category'];
						$address = $node['address'];
						$description = $node['description'];
						$tip = $node['tip'];
						$duration = $node['duration']; // in hours
						$photo = $node['photo'];
						// Output current node
						//echo $name . "<br>";
						echo attractionHTML($timestr,$labels,$name,$address,$description,$tip,$duration,$photo);
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
								$edge = $today_edges[$edge_id];
								$name = $edge['name'];
								$mode = $edge['travel_mode'];
								$duration = $edge['travel_time']; // in minutes
								$walkdist = $edge['walk_dist'];
								$route = $edge['travel_route'];
								$towards = $edge['travel_towards'];
								$from = $edge['travel_from'];
								$to = $edge['travel_to'];
								$stops = $edge['travel_stops'];
								echo transportHTML($mode,$duration,$walkdist,$route,$towards,$from,$to,$stops);
								// Update currenttime
								$durationtime = array();
								$durationtime['h'] = 0;
								$durationtime['m'] = $duration;
								$currenttime = addTime($currenttime,$durationtime);
								// Increment counter
								$edge_i++;	
							} while ($edge['transfer_end']); // repeat if endpoint is transfer
						} // if ($edge_i < count($today_edges))
					} // while ($node_i < count($today_nodes))
				?>
				
				</article><!-- itin -->
			</div><!-- col-md-8 -->
			
			<aside id="suggestions" class="col-md-4">
				<?php include 'samplesuggestions.php'; ?>
			</aside><!-- #suggestions -->
		</div><!-- row -->
	</section><!-- #results -->

	<footer>
		<nav>
			<ul class="nav nav-pills">
				<li><a href="#">About Us</a></li>
				<li><a href="#">Site Map</a></li>
				<li><a href="#">FAQ</a></li>
				<li><a href="#">Contact</a></li>
				<li><a href="#">Join Our Team</a></li>
			</ul>
		</nav>
		<small>&copy; 2015 OneMinPlan · <a href="#">Terms</a> · <a href="#">Privacy</a> · <a href="#">Cookies</a></small>
	</footer>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/output.js"></script>
</body>
<?php
	include 'disconnect.php';
?>