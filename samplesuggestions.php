<?php
//  list of nearby attractions

$attractions_id = $_SESSION['attractions_id'];
$current_attractions_str = implode(',', $attractions_id);

//$categories = array('Arts & Culture','Food','Fun & Entertainment','Shopping','Nature','Sights');
$categories = array('artsculture','food','fun','shopping','nature','sights');
$ind=0;
$sugg_node_ids=array();

/*
$outputtext = "<script> console.log('suggestion panel current_attractions_str: ".  $current_attractions_str  . "');</script>";
echo 	$outputtext ;
foreach($categories as $category) {
	$query = "SELECT node_id from attractions_hk_ta WHERE (category LIKE \"%$category%\") AND (node_id NOT IN ($current_attractions_str ) ) AND (NOT (category=\"Hotel\")) ORDER BY popular_ranking LIMIT 3";
	$outputtext = "<script> console.log('suggestion panel query: ".  $query  . "');</script>";
	echo 	$outputtext ;
	//echo $query ."<br>";
	$result = mysqli_query($connection, $query);

	if (!$result) {
		die("Database query failed.");
	}


		// output data of each row
		while($row = mysqli_fetch_assoc($result)) {
			if (array_search($row["node_id"],$sugg_node_ids)===false){
				$sugg_node_ids[$ind] = $row["node_id"];
				$ind += 1;
			}
		}
}
*/

//$sugg_node_ids =array_diff($all_node,$attractions_id);

$forbidden_node_ids=$attractions_id;
// Retrieve the info of the sugg nodes and categorize them
$sugg_nodes = array();
$sugg_nodes['artsculture'] = array();
$sugg_nodes['food'] = array();
$sugg_nodes['fun'] = array();
$sugg_nodes['shopping'] = array();
$sugg_nodes['nature'] = array();
$sugg_nodes['sights'] = array();
// foreach($sugg_node_ids as $sugg_node_id)
foreach($categories as $category) {
	$query = "SELECT * from attractions_hk_ta WHERE (category LIKE \"%$category%\") AND (node_id NOT IN ( ".implode(',', $forbidden_node_ids)."  ))  AND (NOT (category=\"Hotel\")) ORDER BY popular_ranking LIMIT 3";
	$result = mysqli_query($connection, $query);
	while($row = mysqli_fetch_assoc($result)){
		array_push($sugg_nodes[$category],$row);	
		array_push($forbidden_node_ids,$row["node_id"]);
	}

} // for each sugg_node_ids as sugg_node_id
// Output the accordion open tag
echo "<div class='panel-group' id='accordion' role='tablist' aria-multiselectable='true'>";
$first = 'in'; // the first panel is open by default

foreach ($categories as $shortlabel) {
	global $displayLabels; // from functions.php
	$displayLabel = $displayLabels[$shortlabel];
	echo
	"
	<div class='panel panel-default'>
		<div id='heading-$shortlabel' class='panel-heading' role='tab' >
			<h4 class='panel-title'>
				<a role='button' data-toggle='collapse' data-parent='#accordion' href='#collapse-$shortlabel' aria-expanded='true' aria-controls='heading-$shortlabel'>
	      			$displayLabel
	   			</a>
			</h4><!-- panel-title -->
		</div><!-- #heading-$shortlabel .panel-heading -->
		<div id='collapse-$shortlabel' class='panel-collapse collapse $first' role='tabpanel' aria-labelledby='heading-$shortlabel'>
			<div class='panel-body'>
	";
	if (count($sugg_nodes[$shortlabel]) > 0) {
		foreach ($sugg_nodes[$shortlabel] as $sugg_node) {
			$name = $sugg_node['name'];
			//$nameplus = str_replace(' ','+',$name);
			$nameplus=urlencode($name);
			$ranking = $sugg_node['popular_ranking'];
			$description= $sugg_node['description'];
			echo 
			"
			<article class='attraction row'>
				<div class='col-xs-11'>
					<h5>$name</h5>
					<small>#$ranking in Hong Kong</small>
					<blockquote><q>$description</q></blockquote>
				</div>
				<div class='col-xs-1'>
					<a href='?addname=$nameplus' role='button' tab-index='0' class='glyphicon glyphicon-plus' data-toggle='tooltip' title='Add'></a>
				</div>
			</article><!-- .attraction.row -->
			";
		}
	} else {
		echo "<p class='noresults'>No suggested attractions in this category.</p>";
	}
	
	echo 
	"
			</div><!-- panel-body -->
		</div><!-- #collapse-$shortlabel .panel-collapse -->
	</div><!-- panel -->
	";	
	$first = '';
}



// Output the accordion close tag
echo "</div><!-- #accordion .panel-group -->";
?>
