<?php
// Load the login class so we can check to make sure the user is logged out
// inlucde the Login Class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');
$login = new Login();
// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {
	// User is logged in, shouldn't be seeing the logged out header, redirect back home
	header("location: /");
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Whiteboard</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script src="../included/javascript/header.js"></script>
	<link rel="stylesheet" type="text/css" href="../included/css/headerout.css">
</head>
<body>
	<div id="headerContainer" class="headerContainer">
		<a href="/">
		<div id="headerLogo" class="headerLogo">
			Whiteboard
		</div>
		</a>
		<div id="headerLoginContainer" class="headerLoginContainer">
			<form method="post" action="../../../" name="loginform">
				<div id="headerLoginContainerForms" class="headerLoginContainerForms">
					<div id="headerLoginContainerFormUsername" class="headerLoginContainerFormUsername">
						<input id="user_name" type="text" name="user_name" placeholder="Username" tabindex="1" required />
						<input type="checkbox" id="user_rememberme" name="user_rememberme" value="1" tabindex="3" />
						<label for="user_rememberme"><?php echo "Keep me logged in (for 2 weeks)"; ?></label>
					</div>
					<div id="headerLoginContainerFormPassword" class="headerLoginContainerFormPassword">
						<input id="user_password" type="password" name="user_password" placeholder="Password" autocomplete="off" tabindex="2" required />
						<input type="submit" name="login" tabindex="4" value="<?php echo "Log in"; ?>" />
						&nbsp; <a href="/password_reset/"><?php echo "I forgot my password"; ?></a>
					</div>
					
				</div>
				
			</form>

			
		</div>
	</div>
	<div id="loggedOutContentContainer" class="loggedOutContentContainer">
	<?php
	// show potential errors / feedback (from login object)
	if (isset($login)) {
		if ($login->errors) {
			foreach ($login->errors as $error) {
				echo $error;
			}
		}
		if ($login->messages) {
			foreach ($login->messages as $message) {
				echo $message;
			}
		}
	}
	?>
<br />
