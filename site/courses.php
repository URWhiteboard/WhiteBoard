<?php
// include the config
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
	// user is not logged in, redirect back home
	header("location: /");
}

include($_SERVER['DOCUMENT_ROOT'] .'/included/header.php');
// Show the default page for logged in users

$section = $login->getSections("10826");
for($i = 0; $i < count($section); $i++)
{
	echo "ID: ". $section->ID ."<br />";
	echo "CRN: ". $section->CRN ."<br />";
	echo "School: ". $section->school ."<br />";
	echo "Subject: ". $section->subject ."<br />";
	echo "Course Number: ". $section->courseNumber ."<br />";
	echo "Course Type: ". $section->courseType ."<br />";
	echo "Description: ". $section->description ."<br />";
	echo "Credits: ". $section->credits ."<br />";
	echo "Day: ". $section->day ."<br />";
	echo "Time Start: ". $section->timeStart ."<br />";
	echo "Time End: ". $section->timeEnd ."<br />";
	echo "Building: ". $section->building ."<br />";
	echo "Room: ". $section->room ."<br />";
	echo "Instructor: ". $section->instructor ."<br />";
	echo "Section Enroll: ". $section->sectionEnroll ."<br />";
	echo "Section Cap: ". $section->sectionCap ."<br />";
	echo "Course Info: ". $section->courseInfo ."<br />";
	echo "Requirements: ". $section->requirements ."<br />";
	echo "Prerequisites: ". $section->prerequisites ."<br />";
	echo "Clusters: ". $section->clusters ."<br />";
	echo "Term: ". $section->term ."<br />";
	echo "Year: ". $section->year ."<br />";
	echo "Status: ". $section->status ."<br />";
	echo "Cross Listed: ". $section->crossListed ."<br />";
	echo "url: ". $section->url ."<br />";
	echo "<br />";
}

$CRN = "10826";
var_dump($login->errors);
if ($login->databaseConnection()) {
	// database query, getting all the info of the selected user
	$query_user = $login->db_connection->prepare('SELECT * FROM sections WHERE CRN = :CRN');
	$query_user->bindValue(':CRN', $CRN, PDO::PARAM_STR);
	$query_user->execute();
	// get result row (as an object)
	$sections = $query_user->fetchObject();
} else {
	return false;
}
var_dump($query_user);


// Include the footer page
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>