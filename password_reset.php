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

// If user is logged in, they don't need to reset their password
// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {
	// the user is logged in, redirect them to the homepage
	header("location: /");
}

// the user has just successfully entered a new password
// so we show the index page = the login page
if ($login->passwordResetWasSuccessful() == true && $login->passwordResetLinkIsValid() != true) {
	Location("/");

} 
// the user wants to reset their password, or they had a mistake last time
include('included/headerout.php');
if ($login->passwordResetLinkIsValid() == true) { ?>
<form method="post" action="/password_reset/" name="new_password_form">
	<input type='hidden' name='user_name' value='<?php echo $_GET['user_name']; ?>' />
	<input type='hidden' name='user_password_reset_hash' value='<?php echo $_GET['verification_code']; ?>' />

	<label for="user_password_new"><?php echo "New password"; ?></label>
	<input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />

	<label for="user_password_repeat"><?php echo "Repeat new password"; ?></label>
	<input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
	<input type="submit" name="submit_new_password" value="<?php echo "Submit new password"; ?>" />
</form>
<!-- no data from a password-reset-mail has been provided, so we simply show the request-a-password-reset form -->
<?php } else { ?>
<form method="post" action="/password_reset/" name="password_reset_form">
	<label for="user_name"><?php echo "Request a password reset. Enter your username and you'll get an email with instructions:"; ?></label>
	<input id="user_name" type="text" name="user_name" required />
	<input type="submit" name="request_password_reset" value="<?php echo "Reset my password"; ?>" />
</form>
<?php } ?>

<a href="/"><?php echo "Back to Login Page"; ?></a>
<?php
// Include footer
include("included/footer.php");
?>