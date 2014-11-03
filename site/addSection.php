<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == false) {
	// the user is not logged in, redirect them to the homepage
	echo "Please log in.";
}
// Check if there was a valid section to add
if($_GET['s'] != "") {
	if ($login->databaseConnection()) {
		// Insert new row is sectionStudents
		
		$query_addSection = $login->db_connection->prepare('INSERT INTO sectionStudents (is_active, userID, sectionID, is_pass_fail, is_satisfactory_fail, is_no_credit) VALUES(:is_active, :userID, :sectionID, :is_pass_fail, :is_satisfactory_fail, :is_no_credit)') or die(mysqli_error($db_connection_insert));
		$query_addSection->bindValue(':is_active', 1, PDO::PARAM_STR);
		$query_addSection->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
		$query_addSection->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
		$query_addSection->bindValue(':is_pass_fail', 0, PDO::PARAM_STR);
		$query_addSection->bindValue(':is_satisfactory_fail', 0, PDO::PARAM_STR);
		$query_addSection->bindValue(':is_no_credit', 0, PDO::PARAM_STR);
		$query_addSection->execute();

		// If there were no rows returned, then the data did not get inserted correctly
		if($query_addSection->rowCount() > 0) { 
			echo "You are now enrolled in this section!";
		} else {
			echo "There was an error and you were not enrolled in the section.";
		}
	}
} else {
	echo "There was an error, please try again later.";
}
?>