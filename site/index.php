<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// include the PHPMailer library
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/libraries/PHPMailer.php');

// inlucde the Login Class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {

	// load the login class
	include($_SERVER['DOCUMENT_ROOT'] .'/included/header.php');
	// Show the default page for logged in users
	?>
<div id="mainContentContainerContent" class="mainContentContainerContent">
<div>
	<h3>You have no new notifications!</h3>
</div>

 <?php

} else {
	include($_SERVER['DOCUMENT_ROOT'] .'/included/headerout.php');
	// the user is not logged in. show the login form and more
?>
	<?php
	echo "Logged out content goes here";
	?>	
<?php
}
// Include footer
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>