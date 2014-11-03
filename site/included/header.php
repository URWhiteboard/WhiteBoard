<?php

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == false) {
	// User is not logged in, redirect back home
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
	<link rel="stylesheet" type="text/css" href="../included/css/header.css">
</head>
<body>
	<div id="navBarContainer" class="navBarContainer">
		<a href="../">
		<div id="navBarLogoContainer" class="navBarLogoContainer">
			Whiteboard
		</div>
		</a>
		<div id="navBarSearchContainer" class="navBarSearchContainer">
			<input id="navBarSearchBar" class="navBarSearchBar" placeholder="Click here to start searching..." autocomplete="off">
		</div>
		<div id="navBarSearchResultsContainer" class="navBarSearchResultsContainer">
		Hello
		</div>
		<div id="navBarUserContainer" class="navBarUserContainer">
			<div id="navBarUserName" class="navBarUserName">
				<?php
					echo "&nbsp;" . $_SESSION['name_first'] . " " . $_SESSION['name_last'];
				?>
				<div id="navBarUserAvatarContainer" class="navBarUserAvatarContainer">
					<?php
						echo "<img width='30' height='30' src='". $login->user_gravatar_image_url ."' />";
					?>
				</div>
			</div>
			<a href="/edit/" class="noUnderline">
				<div id="navBarUserOptionsLinkEditData" class="navBarUserOptionsLinks">
					Edit User Data
				</div>
			</a>
			<a href="/?logout" class="noUnderline">
				<div id="navBarUserOptionsLinkLogOut" class="navBarUserOptionsLinks">
					Log Out
				</div>
			</a>
		</div>
	</div>
	<div id="sideBarContainer" class="sideBarContainer">
		<a href="../" class="noUnderline">
		<div id="sideBarHomeContainer" class="sideBarTabContainer">
			Home
		</div>
		</a>
		<a href="../courses/" class="noUnderline">
		<div id="sideBarCoursesContainer" class="sideBarTabContainer">
			Courses
		</div>
		</a>
		<a href="../grades/" class="noUnderline">
		<div id="sideBarGradesContainer" class="sideBarTabContainer">
			Grades
		</div>
		</a>
		<div id="sideBarCalendarContainer" class="sideBarTabContainer">
			Calendar
		</div>
	</div>
	<div id="mainContentContainer" class="mainContentContainer">
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
	
	<?php
	// show potential errors / feedback (from registration object)
	if (isset($registration)) {
		if ($registration->errors) {
			foreach ($registration->errors as $error) {
				echo $error;
			}
		}
		if ($registration->messages) {
			foreach ($registration->messages as $message) {
				echo $message;
			}
		}
	}
	?>
