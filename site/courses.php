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
// Put course content here
	echo "Courses";

// Include the footer page
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>