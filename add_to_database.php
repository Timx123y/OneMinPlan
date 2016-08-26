<?php

$dbhost = "localhost";
$dbuser = "oneminpl_admin";
$dbpass = "^uat#Vf(n)[q";
$dbname = "oneminpl_hk";
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);


foreach ($_POST as $x => $x_value)	{
	
	if ($x == "json_string"){ 

		//echo $x_value;
		$json_string = $x_value;
		$sql = "Insert into user_data (json_string) VALUES ('". mysqli_real_escape_string($connection,$json_string)  ."'  )";
		mysqli_query($connection, $sql);
		
		$sql = "Select * from attractions_hk_ta limit 1";
		$result = mysqli_query($connection, $sql);
		$row = mysqli_fetch_assoc($result);
		
		//echo "name". $row["name"];
		echo "received";

	}

}



?>