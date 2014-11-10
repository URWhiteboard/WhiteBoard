<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();
if ($login->databaseConnection()) {
	// Insert new row is sectionStudents
	$query_addSection = $login->db_connection->prepare('INSERT INTO letterScales (a, am, bp, b, bm, cp, c, cm, dp, d, dm) VALUES(93, 90, 87, 83, 80, 77, 73, 70, 67, 63, 60) ') or die(mysqli_error($db_connection_insert));
	$query_addSection->execute();
	// If there were no rows returned, then the data did not get inserted correctly
	if($query_addSection->rowCount() > 0) { 
		echo "You have added a new letter scale!";
	} else {
		echo "There was an error and you did not add a new letter scale.";
	}
} else {
	echo "Database connection failed";
}
?>