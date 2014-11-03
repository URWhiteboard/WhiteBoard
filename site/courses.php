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

if ($login->databaseConnection()) {
	if(isset($_GET['c'])) {
		echo "<a href='../courses/'>Back to my courses</a><br>";
		$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
		$query_course->bindValue(':courseID', $_GET['c'], PDO::PARAM_STR);
		$query_course->execute();
		// get result row (as an object)
		while($course = $query_course->fetchObject()) {
			echo "ID: ". $section->sectionID ."<br />";
			echo "CRN: ". $section->CRN ."<br />";
			echo "School: ". $course->school ."<br />";
			echo "Subject: ". $course->department ."<br />";
			echo "Course Number: ". $course->number ."<br />";
			echo "Course Type: ". $course->type ."<br />";
			echo "Course Title: ". $course->title ."<br />";
			echo "Description: ". $course->description ."<br />";
			echo "Credits: ". $course->credits ."<br />";
			echo "Day: ". $section->day ."<br />";
			echo "Time Start: ". $section->time_start ."<br />";
			echo "Time End: ". $section->time_end ."<br />";
			echo "Building: ". $section->building ."<br />";
			echo "Room: ". $section->room ."<br />";
			echo "Instructor: ". $section->instructor ."<br />";
			echo "Section Enroll: ". $section->enroll ."<br />";
			echo "Section Cap: ". $section->enrollCap ."<br />";
			echo "Course Info: ". $section->info ."<br />";
			echo "Requirements: ". $course->requirements ."<br />";
			echo "Prerequisites: ". $course->prerequisites ."<br />";
			echo "Clusters: ". $course->clusters ."<br />";
			echo "Term: ". $section->term ."<br />";
			echo "Year: ". $section->year ."<br />";
			echo "Status: ". $section->status ."<br />";
			echo "Cross Listed: ". $course->crossListed ."<br />";
			echo "url: ". $section->url ."<br />";
			echo "<br />";
		}
	} elseif(isset($_GET['s'])) {
		echo "<a href='../courses/'>Back to my courses</a><br>";
		$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
				$query_section->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_section->execute();
				$section = $query_section->fetchObject();

			$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
				$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
				$query_course->execute();
				// get result row (as an object)
				$course = $query_course->fetchObject();

	    	echo "ID: ". $section->sectionID ."<br />";
			echo "CRN: ". $section->CRN ."<br />";
			echo "School: ". $course->school ."<br />";
			echo "Subject: ". $course->department ."<br />";
			echo "Course Number: ". $course->number ."<br />";
			echo "Course Type: ". $course->type ."<br />";
			echo "Course Title: ". $course->title ."<br />";
			echo "Description: ". $course->description ."<br />";
			echo "Credits: ". $course->credits ."<br />";
			echo "Day: ". $section->day ."<br />";
			echo "Time Start: ". $section->time_start ."<br />";
			echo "Time End: ". $section->time_end ."<br />";
			echo "Building: ". $section->building ."<br />";
			echo "Room: ". $section->room ."<br />";
			echo "Instructor: ". $section->instructor ."<br />";
			echo "Section Enroll: ". $section->enroll ."<br />";
			echo "Section Cap: ". $section->enrollCap ."<br />";
			echo "Course Info: ". $section->info ."<br />";
			echo "Requirements: ". $course->requirements ."<br />";
			echo "Prerequisites: ". $course->prerequisites ."<br />";
			echo "Clusters: ". $course->clusters ."<br />";
			echo "Term: ". $section->term ."<br />";
			echo "Year: ". $section->year ."<br />";
			echo "Status: ". $section->status ."<br />";
			echo "Cross Listed: ". $course->crossListed ."<br />";
			echo "url: ". $section->url ."<br />";
			echo "<br />";
			$query_sectionStudents = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
				$query_sectionStudents->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionStudents->execute();
				$enrolled = $query_sectionStudents->fetchColumn();
			if($enrolled == 0) {
				echo "Add course";
			} else {
				echo "You are already enrolled in this section";
			}
	} else {

		// database query, getting all the info of the selected user
		$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE userID = :userID ORDER BY sectionID ASC');
			$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
			$query_sectionStudents->execute();
			// get result row (as an object)

		while($sectionStudents = $query_sectionStudents->fetchObject()) {
			$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
				$query_section->bindValue(':sectionID', $sectionStudents->sectionID, PDO::PARAM_STR);
				$query_section->execute();
				$section = $query_section->fetchObject();

			$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
				$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
				$query_course->execute();
				// get result row (as an object)
				$course = $query_course->fetchObject();

	    	echo "ID: <a href='../courses/?s=". $section->sectionID ."'>". $section->sectionID ."</a><br />";
			echo "CRN: ". $section->CRN ."<br />";
			echo "School: ". $course->school ."<br />";
			echo "Subject: ". $course->department ."<br />";
			echo "Course Number: ". $course->number ."<br />";
			echo "Course Type: ". $course->type ."<br />";
			echo "Course Title: ". $course->title ."<br />";
			echo "Description: ". $course->description ."<br />";
			echo "Credits: ". $course->credits ."<br />";
			echo "Day: ". $section->day ."<br />";
			echo "Time Start: ". $section->time_start ."<br />";
			echo "Time End: ". $section->time_end ."<br />";
			echo "Building: ". $section->building ."<br />";
			echo "Room: ". $section->room ."<br />";
			echo "Instructor: ". $section->instructor ."<br />";
			echo "Section Enroll: ". $section->enroll ."<br />";
			echo "Section Cap: ". $section->enrollCap ."<br />";
			echo "Course Info: ". $section->info ."<br />";
			echo "Requirements: ". $course->requirements ."<br />";
			echo "Prerequisites: ". $course->prerequisites ."<br />";
			echo "Clusters: ". $course->clusters ."<br />";
			echo "Term: ". $section->term ."<br />";
			echo "Year: ". $section->year ."<br />";
			echo "Status: ". $section->status ."<br />";
			echo "Cross Listed: ". $course->crossListed ."<br />";
			echo "url: ". $section->url ."<br />";
			echo "<br />";
		}  
	}
} else {
	echo "There was an error with your request. Please try again later.";
}



// Include the footer page
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>