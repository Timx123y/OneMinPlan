<?php
// manage json string into information
// make a csv file consist of user data
$dbhost = "localhost";
$dbuser = "oneminpl_admin";
$dbpass = "^uat#Vf(n)[q";
$dbname = "oneminpl_hk";
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

	$sql = "select * from attractions_hk_ta where node_id<=534 order by node_id";
	$result= mysqli_query($connection,$sql);
	
	$all_attractions_name=array();
	while($row = mysqli_fetch_assoc($result)){
		array_push($all_attractions_name, $row["name"]);
	}




?>


<!DOCTYPE html>
<html>
   <head>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   </head>
   <body>
     <p> Oneminplan user selection page</p>
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
						I travel in a 
							<select id="pace" name="pace">
								<option value="relaxed">Relaxed (5-7 hrs)</option>
								<option value="normal" selected>Normal (7-9 hrs)</option>
								<option value="packed">Packed (9-11 hrs)</option>
								<option value="superman">Superman (11-13 hrs)</option>
							</select>
						pace, 
						starting at 
						<select id="starttime" name="starttime">
							<option value="9">9:00</option>
							<option value="10" selected>10:00</option>
							<option value="11">11:00</option>
							<option value="12">12:00</option>
						</select>
						each day.
						<br>
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
					</p>
					
					
					<!--<div class="alert alert-warning fade"></div> -->
				</section><!-- #basicform .container -->
				<section id="finetune" class="container-fluid collapse">
					<h4>What do you like?</h4>
					<div id="finetune-form">
						<div class="btn-group row" data-toggle="buttons">
							<label class="btn btn-label disabled">
								<strong>Arts &amp; Culture</strong>
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
								<strong>Food</strong>
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
								<strong>Fun &amp; Entertainment</strong>
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
								<strong>Shopping</strong>
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
								<strong>Nature</strong>
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
								<strong>Sights</strong>
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
						<div class="alert alert-warning fade"></div>
					</div>
				</section><!-- #finetune .container-fluid -->
				<?php
				foreach($all_attractions_name as $this_attraction){
					
					$url_attraction= urlencode($this_attraction);
					
					echo " <input type=\"checkbox\" name=\"selected_attractions[]\" value=\"$url_attraction\"> $this_attraction<br>";

				}


				?>
				
				<div class="row">
						<input type="submit" id="btn-submit" class="btn btn-submit" value="Show me my itinerary!" />
					</div>

			</form>


   </body>
</html>


