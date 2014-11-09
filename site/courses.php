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
<link rel="stylesheet" type="text/css" href="../../included/css/courses.css">
<?php
if(isset($_GET['c']) || isset($_GET['s'])) {
?>
<div id="coursesNavBarContainer" class="coursesNavBarContainer">
	<a href='../courses/'>
	<div id="courseNavBarBack" class="courseNavBarButton">
	Back
	</div>
	</a>
	<div id="courseNavBarAssignments" class="courseNavBarButton">
	Announcements
	</div>
	<div id="courseNavBarAssignments" class="courseNavBarButton">
	Assignments
	</div>
	<div id="courseNavBarGrades" class="courseNavBarButton">
	Grades
	</div>
	<div id="courseNavBarResources" class="courseNavBarButton">
	Resources
	</div>
</div>
<div id="mainContentContainerContent" class="mainContentContainerContent" style="padding-top:40px;">
<?php
}
if ($login->databaseConnection()) {
	if(isset($_GET['c'])) {
		// if the user is trying to look up a course
		$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
			$query_course->bindValue(':courseID', $_GET['c'], PDO::PARAM_STR);
			$query_course->execute();
		$course = $query_course->fetchObject();

		// get result row (as an object)
		$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE courseID = :courseID');
			$query_section->bindValue(':courseID', $course->courseID, PDO::PARAM_STR);
			$query_section->execute();
		while($section = $query_section->fetchObject()) {
			// query the sectionTeachers to get the teacher of the section
			$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
				$query_sectionTeacher->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
				$query_sectionTeacher->execute();
				$sectionTeacher = $query_sectionTeacher->fetchObject();
			// query the users table for the teachers first and last name
			$query_sectionTeacherData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
				$query_sectionTeacherData->bindValue(':userID', $sectionTeacher->userID, PDO::PARAM_STR);
				$query_sectionTeacherData->execute();
				$sectionTeacherData = $query_sectionTeacherData->fetchObject();

			echo "sectionID: ". $section->sectionID ."<br />";
			echo "courseID: ". $course->courseID ."<br />";
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
			echo "Instructor: ". $sectionTeacherData->name_first . " ". $sectionTeacherData->name_last ."<br />";
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
				$query_sectionStudents->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
				$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionStudents->execute();
				$enrolled = $query_sectionStudents->fetchColumn();
			echo "<div id='sectionEnrollStatus' class='sectionEnrollStatus' data-sID='". $section->sectionID ."'>";
			if($enrolled == 0) {
				echo "Add section";
			} else {
				echo "Drop section";
			}
			echo "</div>";
		}
	} elseif(isset($_GET['s'])) {
		// if the user is trying to look up a section
		$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
				$query_section->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_section->execute();
				$section = $query_section->fetchObject();

		$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
			$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
			$query_course->execute();
			// get result row (as an object)
			$course = $query_course->fetchObject();
		// query the sectionTeachers to get the teacher of the section
		$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
			$query_sectionTeacher->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
			$query_sectionTeacher->execute();
			$sectionTeacher = $query_sectionTeacher->fetchObject();

		$query_sectionTeacherData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
			$query_sectionTeacherData->bindValue(':userID', $sectionTeacher->userID, PDO::PARAM_STR);
			$query_sectionTeacherData->execute();
			$sectionTeacherData = $query_sectionTeacherData->fetchObject();

    	echo "sectionID: ". $section->sectionID ."<br />";
    	echo "courseID: ". $course->courseID ."<br />";
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
		echo "Instructor: ". $sectionTeacherData->name_first . " ". $sectionTeacherData->name_last ."<br />";
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
		if($login->getType() == "TEACHER"){
			// Teacher control panel
			echo "All students enrolled in this course<br />";
			$query_sectionStudents = $login->db_connection->prepare('SELECT userID FROM sectionStudents WHERE sectionID = :sectionID');
			$query_sectionStudents->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
			$query_sectionStudents->execute();
			while($sectionStudents = $query_sectionStudents->fetchObject()) {
				$query_sectionUserData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
				$query_sectionUserData->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_STR);
				$query_sectionUserData->execute();
				$sectionUserData = $query_sectionUserData->fetchObject();
				echo $sectionUserData->name_first ." ". $sectionUserData->name_last .", ";
			}
		} elseif($login->getType() == "STUDENT"){
			// Student control panel
			// query database, check if the user is enrolled in the section
			$query_sectionStudents = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
				$query_sectionStudents->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionStudents->execute();
				$enrolled = $query_sectionStudents->fetchColumn();
			echo "<div id='sectionEnrollStatus' class='sectionEnrollStatus' data-sID='". $section->sectionID ."'>";
			if($enrolled == 0) {
				// User is not currently enrolled
				echo "Add section";
			} else {
				// User is currently enrolled
				echo "Drop section";
			}
			echo "</div>";
		} else {
			echo "You are an admin!";
		}
	} else {
		echo "<div id='mainContentContainerContent' class='mainContentContainerContent'>";
		// If the user is a teacher, show them the classes they teach
		if($login->getType() == "TEACHER"){
			// database query, get all of the sections the teacher teaches
			$query_sectionTeachers = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE userID = :userID ORDER BY sectionID ASC');
				$query_sectionTeachers->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionTeachers->execute();

			if($query_sectionTeachers->rowCount() == 0) {
				echo "You do not currently teach any sections";
			}
			// get result row as an object, so we can itenerate through the sections
			while($sectionTeachers = $query_sectionTeachers->fetchObject()) {
				// database query, get all the relevant information about the section
				$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
					$query_section->bindValue(':sectionID', $sectionTeachers->sectionID, PDO::PARAM_STR);
					$query_section->execute();
					$section = $query_section->fetchObject();
				// database query, get all of the relevant information about the course
				$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
					$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
					$query_course->execute();
					// get result row (as an object)
					$course = $query_course->fetchObject();
				// Display all relevant course information
				echo "<a href='../courses/?s=". $section->sectionID ."'>". $course->department ." ". $course->number ." - ". $course->title ."</a><br />";
		    	echo "sectionID: ". $section->sectionID ."</a><br />";
		    	echo "courseID: ". $course->courseID ."<br />";
				echo "Day: ". $section->day ."<br />";
				echo "Time Start: ". $section->time_start ."<br />";
				echo "Time End: ". $section->time_end ."<br />";
				echo "Building: ". $section->building ."<br />";
				echo "Room: ". $section->room ."<br />";
				echo "Instructor: ". $section->instructor ."<br />";
				echo "Term: ". $section->term ."<br />";
				echo "Year: ". $section->year ."<br />";
				echo "<br />";
			}		
		} elseif($login->getType() == "STUDENT"){
			// database query, get all of the sections the user is enrolled in
			$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE userID = :userID ORDER BY sectionID ASC');
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
				// query the sectionTeachers to get the teacher of the section
				$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
					$query_sectionTeacher->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
					$query_sectionTeacher->execute();
					$sectionTeacher = $query_sectionTeacher->fetchObject();

				$query_sectionTeacherData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
					$query_sectionTeacherData->bindValue(':userID', $sectionTeacher->userID, PDO::PARAM_STR);
					$query_sectionTeacherData->execute();
					$sectionTeacherData = $query_sectionTeacherData->fetchObject();

				// Display all relevant course information
				echo "<a href='../courses/?s=". $section->sectionID ."'>". $course->department ." ". $course->number ." - ". $course->title ."</a><br />";
		    	echo "sectionID: ". $section->sectionID ."</a><br />";
		    	echo "courseID: ". $course->courseID ."<br />";
				echo "Day: ". $section->day ."<br />";
				echo "Time Start: ". $section->time_start ."<br />";
				echo "Time End: ". $section->time_end ."<br />";
				echo "Building: ". $section->building ."<br />";
				echo "Room: ". $section->room ."<br />";
				echo "Instructor: ". $sectionTeacherData->name_first . " ". $sectionTeacherData->name_last ."<br />";
				echo "Term: ". $section->term ."<br />";
				echo "Year: ". $section->year ."<br />";
				echo "<br />";
			}
		}
	}
} else {
	// There was no database connection
	echo "There was an error with your request. Please try again later.";
}



// Include the footer page
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>