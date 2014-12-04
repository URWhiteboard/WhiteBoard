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
	header("location: /");
}
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/header.php');

?>
<div id="mainContentContainerContent" class="mainContentContainerContent">
<form method="post" action="/edit/" name="edit_form_name">
	<label for="name"><?php echo "New username (username cannot be empty and must be azAZ09 and 2-64 characters)"; ?></label>
	<input id="username" type="text" name="username" pattern="[a-zA-Z0-9]{2,64}" required /> (<?php echo "currently"; ?>: <?php echo $_SESSION['username']; ?>)
	<input type="submit" name="edit_submit_name" value="Change username" />
</form><hr/>

<!-- edit form for user email / this form uses HTML5 attributes, like "required" and type="email" -->
<form method="post" action="/edit/" name="edit_form_email">
	<label for="email"><?php echo "New email"; ?></label>
	<input id="email" type="email" name="email" required /> (<?php echo "currently"; ?>: <?php echo $_SESSION['email']; ?>)
	<input type="submit" name="edit_submit_email" value="Change email" />
</form><hr/>

<!-- edit form for user's password / this form uses the HTML5 attribute "required" -->
<form method="post" action="/edit/" name="edit_form_password">
	<label for="password_old"><?php echo "Your OLD Password"; ?></label>
	<input id="password_old" type="password" name="password_old" autocomplete="off" />

	<label for="password_new"><?php echo "New password"; ?></label>
	<input id="password_new" type="password" name="password_new" autocomplete="off" />

	<label for="password_repeat"><?php echo "Repeat new password"; ?></label>
	<input id="password_repeat" type="password" name="password_repeat" autocomplete="off" />

	<input type="submit" name="edit_submit_password" value="Change password" />
</form><hr/>

<!-- Include the footer page -->
<?php include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php'); ?>
