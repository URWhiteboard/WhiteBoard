<?php
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
if ($login->databaseConnection()) {
	if(isset($_GET['c'])) {
		$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
			$query_course->bindValue(':courseID', $_GET['c'], PDO::PARAM_STR);
			$query_course->execute();
		$course = $query_course->fetchObject();

		// get all of the course sections
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
			
			// Main
			echo "<li class='title'>". $course->title ."</li>";
			echo "<li>Instructor: ". $sectionTeacherData->name_first . " ". $sectionTeacherData->name_last ."</li>";
			
			// General Information
			echo "<li>Time: ". $section->day . " ". $section->time_start . " - " . $section->time_end . "</li>";
			echo "<li>Location: ". $section->building . " " .$section->room . "</li>";
			echo "<li>Description: <br>". $course->description ."</li>";
			
			// Additional Information
			echo "<li><table class='infoTable'>";
			echo "<tr id='additional' onclick='expand();'><td>Additional Information</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Section ID</td><td><a href='../../courses/?s=". $section->sectionID."'>". $section->sectionID ."</a></td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course ID</td><td>". $course->courseID ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Reference Number (CRN)</td><td>". $section->CRN ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>School</td><td>". $course->school ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Subject</td><td>". $course->department ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Number</td><td>". $course->number ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Type</td><td>". $course->type ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Credits</td><td>". $course->credits ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Section Enroll</td><td>". $section->enroll ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Section Cap</td><td>". $section->enrollCap ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Info</td><td>". $section->info ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Requirements</td><td>". $course->requirements ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Prerequisites</td><td>". $course->prerequisites ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Clusters</td><td>". $course->clusters ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Term</td><td>". $section->term ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Year</td><td>". $section->year ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Status</td><td>". $section->status ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Cross Listed</td><td>". $course->crossListed ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>URL</td><td>". $section->url ."</td></tr>";
			echo "</table></li>";
			
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

			// Main
			echo "<li class='title'>". $course->title ."</li>";
			echo "<li>Instructor: ". $sectionTeacherData->name_first . " ". $sectionTeacherData->name_last ."</li>";
			
			// General Information
			echo "<li>Time: ". $section->day . " ". $section->time_start . " - " . $section->time_end . "</li>";
			echo "<li>Location: ". $section->building . " " .$section->room . "</li>";
			echo "<li>Description: <br>". $course->description ."</li>";
			
			echo "<li>";
			if($login->getType() == "TEACHER"){
				// Teacher control panel
				echo "Roster: ";
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
				// echo "You are enrolled in this section";
			} else {
				echo "You are an admin!";
			}
			echo "</li>";
			
			// Additional Information
			echo "<li><table>";
			echo "<tr id='additional' onclick='expand();'><td>Additional Information</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Section ID</td><td><a href='../../courses/?s=". $section->sectionID."'>". $section->sectionID ."</a></td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course ID</td><td>". $course->courseID ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Reference Number (CRN)</td><td>". $section->CRN ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>School</td><td>". $course->school ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Subject</td><td>". $course->department ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Number</td><td>". $course->number ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Type</td><td>". $course->type ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Credits</td><td>". $course->credits ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Section Enroll</td><td>". $section->enroll ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Section Cap</td><td>". $section->enrollCap ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Course Info</td><td>". $section->info ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Requirements</td><td>". $course->requirements ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Prerequisites</td><td>". $course->prerequisites ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Clusters</td><td>". $course->clusters ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Term</td><td>". $section->term ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Year</td><td>". $section->year ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Status</td><td>". $section->status ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>Cross Listed</td><td>". $course->crossListed ."</td></tr>";
			echo "<tr class='infoAdditionalInfo' style='display: none;'><td>URL</td><td>". $section->url ."</td></tr>";
			echo "</table></li>";

	} else {
		echo "There was an error, please try again later.";
	}
} else {
	echo "Database connection failed, please try again later.";
}
?>
