<?php
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// include the PHPMailer library
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/libraries/PHPMailer.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == false) {
	// the user is not logged in, redirect them to the homepage
	header("location: /");
}
if ($login->databaseConnection()) {
	// Check to make sure the user has permission to see this page
	$query_sectionStudents = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
		$query_sectionStudents->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
		$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
		$query_sectionStudents->execute();
	$enrolled = $query_sectionStudents->fetchColumn();
	if($enrolled==1) {
		// Enrolled, show page
		echo "Grades";
	} else {
		// Not enrolled, redirect back to #info
		echo "Permission Denied!";
	}
}
?>