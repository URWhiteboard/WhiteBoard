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
	// the user is not logged in, redirect them to the homepage
	header("location: /");
}
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/header.php');
?>
<form method="post" action="/edit/" name="user_edit_form_name">
	<label for="user_name"><?php echo "New username (username cannot be empty and must be azAZ09 and 2-64 characters)"; ?></label>
	<input id="user_name" type="text" name="user_name" pattern="[a-zA-Z0-9]{2,64}" required /> (<?php echo "currently"; ?>: <?php echo $_SESSION['user_name']; ?>)
	<input type="submit" name="user_edit_submit_name" value="<?php echo "Change username"; ?>" />
</form><hr/>

<!-- edit form for user email / this form uses HTML5 attributes, like "required" and type="email" -->
<form method="post" action="/edit/" name="user_edit_form_email">
	<label for="user_email"><?php echo "New email"; ?></label>
	<input id="user_email" type="email" name="user_email" required /> (<?php echo "currently"; ?>: <?php echo $_SESSION['user_email']; ?>)
	<input type="submit" name="user_edit_submit_email" value="<?php echo "Change email"; ?>" />
</form><hr/>

<!-- edit form for user's password / this form uses the HTML5 attribute "required" -->
<form method="post" action="/edit/" name="user_edit_form_password">
	<label for="user_password_old"><?php echo "Your OLD Password"; ?></label>
	<input id="user_password_old" type="password" name="user_password_old" autocomplete="off" />

	<label for="user_password_new"><?php echo "New password"; ?></label>
	<input id="user_password_new" type="password" name="user_password_new" autocomplete="off" />

	<label for="user_password_repeat"><?php echo "Repeat new password"; ?></label>
	<input id="user_password_repeat" type="password" name="user_password_repeat" autocomplete="off" />

	<input type="submit" name="user_edit_submit_password" value="<?php echo "Change password"; ?>" />
</form><hr/>

<!-- Include the footer page -->
<?php include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php'); ?>
