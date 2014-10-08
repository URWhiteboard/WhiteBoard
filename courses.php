<?php
// include the config
require_once('config/config.php');

// include the PHPMailer library
require_once('included/libraries/PHPMailer.php');

// load the login class
require_once('classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == false) {
	// user is not logged in, redirect back home
	header("location: /");
}
include('included/header.php');
// Put course content here
	echo "Courses";

// Include the footer page
include('included/footer.php');
?>