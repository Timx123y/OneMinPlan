<!DOCTYPE html>
<html lang="en">

<head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
  
  <title>OneMinPlan</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/select2.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/home.css">
  <script src="js/prefixfree.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
</head>

<body>
	<?php include "nav.php"?>

	<section id="main" class="main-home">
		<div class="bg-overlay">
			<h1>Welcome, explorer.</h1>
			<h3>Zero hassle, maximum fun</h3>
			<h4>Create your own unique, customised itinerary in a blink</h4>
			<form action="process.php" method="post">
				<section id="basicform" class="container-fluid form-group"> 
					<p>
						I am going to 
						<select id="city" name="city">
							<option value="HK" selected>Hong Kong</option>
						</select> 
						for 
						<select id="days" name="days">
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3" selected>3</option>
							<option value="4">4</option>
							<option value="5">5</option>
						</select>
						 days.
						<br>
						I am a 
							<select id="pace" name="pace">
								<option value="relaxed">Relaxed (5-7 hrs)</option>
								<option value="normal" selected>Normal (7-9 hrs)</option>
								<option value="packed">Packed (9-11 hrs)</option>
								<option value="superman">Superman (11-13 hrs)</option>
							</select>
						traveler, 
						starting at 
						<select id="starttime" name="starttime">
							<option value="9">9:00</option>
							<option value="10" selected>10:00</option>
							<option value="11">11:00</option>
							<option value="12">12:00</option>
						</select>
						each day.
						<br>
						<!--
						Each day should have 
							<select id="custom_number" name="custom_number">
								<option value="1">1</option>
								<option value="2" >2</option>
								<option value="3" selected>3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
							</select>
						customized attractions, 
						and a total of 
						<select id="total_number" name="total_number">
							<option value="6">6</option>
							<option value="7" >7</option>
							<option value="8" >8</option>
							<option value="9" selected>9</option>
						</select>
						attractions.
						<br>
						Restaurant price level
							<select id="price_level" name="price_level">
								<option value="1">1</option>
								<option value="2" >2</option>
								<option value="3" selected>3</option>
								<option value="4">4</option>
							</select>			
						<br>						
						-->
						<?php
						//<input type="checkbox" name="cuisine_type[]" id="american" value="american" autocomplete="off"> American
					
						//<input type="checkbox" name="cuisine_type[]" id="asian" value="asian" autocomplete="off"> Asian
						
						//<input type="checkbox" name="cuisine_type[]" id="british" value="british" autocomplete="off"> British
						$food_type_list = array('American' ,'Asian' ,'Bakery', 'Barbecue' ,'British' ,'Cafe' , 'Caribbean' ,'Chinese', 'Continental', 'Delicatessen','Dessert', 'Eastern European', 'European', 'French' ,'German' ,'Greek', 'Indian' ,'Irish', 'Italian' ,'Japanese' ,'Mediterranean', 'Pizza', 'Pub', 'Seafood', 'Soups' , 'Spanish' ,'Steakhouse' ,'Sushi' ,'Thai' ,'Vegetarian', 'Vietnamese');
						
						// foreach($food_type_list as $ind=>$food_type){
							
						// 	$food_type_lower = strtolower($food_type);
						// 	echo '<input type="checkbox" name="cuisine_type[]" id="'. $food_type_lower. '" value="'.$food_type_lower.'" autocomplete="off">'. $food_type_lower;
						// 	echo '
						// 	';
						// 	if (($ind>1) and($ind % 5 ===0) ){echo "<br>";}
							
							
						// }
						
						?>
						
						

						
						
					</p>
					<a id="btn-next" class="btn btn-primary">Next</a>
					
					<!--<div class="alert alert-warning fade"></div> -->
				</section><!-- #basicform .container -->

				<section id="finetune" class="container-fluid collapse">
					<h4>What do you like?</h4>
					<div id="finetune-form">
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								Arts &amp; Culture
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="artsculture[]" id="localarts" value="localarts" autocomplete="off"> Local gems
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="artsculture[]" id="museums" value="museums" autocomplete="off"> Museums
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="artsculture[]" id="history" value="history" autocomplete="off"> History buff
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="artsculture[]" id="architecture" value="architecture" autocomplete="off"> Architecture
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="artsculture[]" id="religion" value="religion" autocomplete="off"> Religion
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="artsculture[]" id="otherarts" value="otherarts" autocomplete="off"> Others
							</label>
						</div><!-- btn-group -->
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								Food
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="food[]" id="localfood" value="localfood" autocomplete="off"> Local
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="food[]" id="budgetstreet" value="budgetstreet" autocomplete="off"> Budget &amp; street food
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="food[]" id="finedine" value="finedine" autocomplete="off"> Fine dine
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="food[]" id="drinks" value="drinks" autocomplete="off"> Drinks
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="food[]" id="otherfood" value="otherfood" autocomplete="off"> Others
							</label>
						</div><!-- btn-group -->
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								Fun &amp; Entertainment
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="fun[]" id="themeparks" value="themeparks" autocomplete="off"> Theme parks
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="fun[]" id="shows" value="shows" autocomplete="off"> Shows
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="fun[]" id="gambling" value="gambling" autocomplete="off"> Gambling
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="fun[]" id="barsclubs" value="barsclubs" autocomplete="off"> Bars &amp; clubs
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="fun[]" id="otherfun" value="otherfun" autocomplete="off"> Others
							</label>
						</div><!-- btn-group -->
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								Shopping
							</label>

							<label class="btn btn-choice">
								<input type="checkbox" name="shopping[]" id="specialtiesgifts" value="specialtiesgifts" autocomplete="off"> Specialties &amp; gifts
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="shopping[]" id="malls" value="malls" autocomplete="off"> Malls
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="shopping[]" id="luxury" value="luxury" autocomplete="off"> Luxury
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="shopping[]" id="othershopping" value="othershopping" autocomplete="off"> Others
							</label>
						</div><!-- btn-group -->
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								Nature
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="nature[]" id="beaches" value="beaches" autocomplete="off"> Beaches
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="nature[]" id="hiking" value="hiking" autocomplete="off"> Hiking
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="nature[]" id="parkszoos" value="parkszoos" autocomplete="off"> Parks &amp; zoos
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="nature[]" id="scenic" value="scenic" autocomplete="off"> Scenic
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="nature[]" id="ecotour" value="ecotour" autocomplete="off"> Ecotour
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="nature[]" id="othernature" value="othernature" autocomplete="off"> Others
							</label>
						</div><!-- btn-group -->
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								Sights
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="sights[]" id="landmarks" value="landmarks" autocomplete="off"> Landmarks
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="sights[]" id="lookoutpoints" value="lookoutpoints" autocomplete="off"> Lookout points
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="sights[]" id="neighbourhoods" value="neighbourhoods" autocomplete="off"> Neighbourhoods
							</label>
							<label class="btn btn-choice">
								<input type="checkbox" name="sights[]" id="othersights" value="othersights" autocomplete="off"> Others
							</label>
						</div><!-- btn-group -->
					</div><!-- #finetune-form -->
					<div class="row">
						<input type="submit" id="btn-submit" class="btn btn-submit" value="Show me my itinerary!" />
					</div>
					<div class="row">
						<div class="alert alert-warning fade"></div>
					</div>
				</section><!-- #finetune .container-fluid -->				
			</form>
		</div><!-- bg-overlay -->
	</section><!-- main -->

	<article id="zerohassle" class="feature">
		<div class="container">
			<h2>Zero hassle. Customised&nbsp;experience.</h2>
			<div class="row">
				<div class="col-sm-6 col-sm-push-6 col-md-offset-2 col-md-4 col-md-push-4">
					<img src="img/photo1-sq.jpg" class="img-responsive" />
				</div>
				<div class="col-sm-6 col-sm-pull-6 col-md-4 col-md-pull-4">
					<h4>Choose your destination and travel style, we will do the rest for you.</h4>
				</div>
			</div><!-- row -->
		</div><!-- container -->
	</article><!-- zerohassle -->

	<article id="customised" class="feature feature-white">
		<div class="container">
			<h2>Customised and automatic.</h2>
			<div class="row">
				<div class="col-sm-6 col-md-4 col-md-offset-2">
					<img src="img/photo2-sq.jpg" class="img-responsive" />
				</div>
				<div class="col-sm-6 col-md-4 col-md-6">
					<h4>Every single trip is a tailored trip. Just tell us what you want by clicking on the options in the table.</h4>
				</div>
			</div><!-- row -->
		</div><!-- container -->			
	</article><!-- customised -->

	<article id="choice" class="feature">
		<div class="container">
			<h2>Don't like this? Here's&nbsp;another&nbsp;choice.</h2>
			<div class="row">
				<div class="col-sm-6 col-sm-push-6 col-md-offset-2 col-md-4 col-md-push-4">
					<img src="img/photo3-sq.jpg" class="img-responsive" />
				</div>
				<div class="col-sm-6 col-sm-pull-6 col-md-4 col-md-pull-4">
					<h4>If you don't like something in the itinerary, just click the cross in the top right corner, and we'll suggest something else.</h4>
				</div>
			</div><!-- row -->
		</div><!-- container -->
	</article><!-- choice -->

	<article id="onthego" class="feature feature-white">
		<div class="container">
			<h2>Keep your itinerary on-the-go.</h2>
			<div class="row">
				<div class="col-sm-6 col-md-4 col-md-offset-2">
					<img src="img/gmail.png" class="img-responsive" />
				</div>
				<div class="col-sm-6 col-md-4">
					<h4>Email or download your customised itinerary for free, so you can access it anytime, anywhere.</h4>
				</div>
			</div><!-- row -->
		</div><!-- container -->
	</article><!-- onthego -->

	<article id="share" class="feature">
		<div class="container">
			<h2>Share the excitement.</h2>
			<div class="row">
				<div class="col-sm-6 col-sm-push-6 col-md-offset-2 col-md-4 col-md-push-4">
					<img src="img/facebook.jpg" class="img-responsive" />
				</div>
				<div class="col-sm-6 col-sm-pull-6 col-md-4 col-md-pull-4">
					<h4>Excited for your trip? Share the joy by telling your friends and family.</h4>
				</div>
			</div><!-- row -->
		</div><!-- container -->
	</article><!-- share -->

	<footer>
		<h2>Sounds good? <a href="#main">Get&nbsp;me&nbsp;started!</a></h2>
		<?php include 'footernav.php' ?>
	</footer>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/select2.min.js"></script>
	<script src="js/home.js"></script>
</body>
