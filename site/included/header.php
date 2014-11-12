<?php

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == false) {
	// User is not logged in, redirect back home
	header("location: /");
}
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// include the PHPMailer library
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/libraries/PHPMailer.php');

// inlucde the Login Class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');
$login = new Login();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Whiteboard</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="/included/javascript/header.js"></script>
	<link rel="stylesheet" type="text/css" href="../../included/css/header.css">
</head>
<body>
	<div id="navBarContainer" class="navBarContainer">
		<a href="../">
		<div id="navBarLogoContainer" class="navBarLogoContainer">
			Whiteboard
		</div>
		</a>
		<div id="navBarSearchContainer" class="navBarSearchContainer">
			<input id="navBarSearchBar" class="navBarSearchBar" placeholder="Click here to start searching..." autocomplete="off" autocorrect="off">
		</div>
		<div id="navBarSearchResultsContainer" class="navBarSearchResultsContainer">
		Start typing to search...
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
		<?php
		if($_SERVER["REQUEST_URI"] == "/") {
			echo "<div id='sideBarHomeContainer' class='sideBarTabContainer selected'>";
		} else {
			echo "<div id='sideBarHomeContainer' class='sideBarTabContainer'>";
		}
		?>
			Home
		</div>
		</a>
		<a href="../../courses/" class="noUnderline">
		<?php
		if(strpos($_SERVER["REQUEST_URI"],'courses') !== false) {
			echo "<div id='sideBarCoursesContainer' class='sideBarTabContainer selected'>";
		} else {
			echo "<div id='sideBarCoursesContainer' class='sideBarTabContainer'>";
		}
		?>
			Courses
		</div>
		</a>
		<!-- <div id="sideBarCoursesSubContainer" class="sideBarCoursesSubContainer">
			<?php
			if ($login->databaseConnection()) {
				$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE userID = :userID AND is_active = 1 ORDER BY sectionID ASC');
				$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionStudents->execute();
				
			// get result row as an object, so we can itenerate through the sections
			while($sectionStudents = $query_sectionStudents->fetchObject()) {
				// database query, get all the relevant information about the section
				$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
					$query_section->bindValue(':sectionID', $sectionStudents->sectionID, PDO::PARAM_STR);
					$query_section->execute();
					$section = $query_section->fetchObject();
				// database query, get all of the relevant information about the course
				$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
					$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
					$query_course->execute();
					// get result row (as an object)
					$course = $query_course->fetchObject();
				echo "<a href='../courses/?s=". $section->sectionID ."' class='noUnderline'>";
				echo "<div id='sideBarCoursesSubBar' class='sideBarCoursesSubBar'>". $course->title ."</div>";
				echo "</a>";
				}
			}
			?>
		</div> -->
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
