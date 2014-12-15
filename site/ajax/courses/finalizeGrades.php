<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();
if ($login->databaseConnection()) {
	// Check if the current user has permission to do this
	$query_sectionTeachers = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionTeachers WHERE sectionID = :sectionID AND userID = :userID');
		$query_sectionTeachers->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
		$query_sectionTeachers->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
		$query_sectionTeachers->execute();
	$teacher = $query_sectionTeachers->rowCount();
	// User has permission to finalize grades
	if($teacher == 1) {
		$gradeIDs = Array();
		$userIDs = Array();
		$error = false;
		// Get all of the users assigned to this section along with their data
		$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM users INNER JOIN sectionStudents ON sectionStudents.userID = users.userID WHERE sectionStudents.sectionID = :sectionID ORDER BY users.name_last, users.name_first ASC');
			$query_sectionStudents->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
			$query_sectionStudents->execute();

		// Loop through all of the users assigned to this section
		while($sectionStudents = $query_sectionStudents->fetchObject()) {
			$userIDs[] = $sectionStudents->userID;

			// Get all of the announcements
			$query_announcement = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID');
				$query_announcement->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_STR);
				$query_announcement->execute();
				$assignment = $query_announcement->fetchObject();

			// Get all of the submissions
			$query_submission = $login->db_connection->prepare('SELECT * FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID');
				$query_submission->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_STR);
				$query_submission->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_STR);
				$query_submission->execute();
				$submission = $query_submission->fetchObject();

			// Check to make sure the user has not received a grade yet
			$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE assignmentID = :assignmentID AND userID = :userID');
				$query_grade->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_STR);
				$query_grade->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_STR);
				$query_grade->execute();
				$grade = $query_grade->fetchObject();

				$pointsScored+=$grade->effective_score;

			// The user has not received a grade
			if($grade==null) {

				// There was not a submission, grade them with a zero
				if($query_submission->rowCount() == 0 && $assignment->submittable) {

				$query_newGrade = $login->db_connection->prepare('INSERT INTO grades (real_score, sectionID, userID, assignmentID, graderID, effective_score, comment) VALUES(:real_score, :sectionID, :userID, :assignmentID, :graderID, :effective_score, :comment) ') or die(mysqli_error($db_connection_insert));
					$query_newGrade->bindValue(':real_score', 0, PDO::PARAM_INT);
					$query_newGrade->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_INT);
					$query_newGrade->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_INT);
					$query_newGrade->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_INT);
					$query_newGrade->bindValue(':graderID', $_SESSION['userID'], PDO::PARAM_INT);
					$query_newGrade->bindValue(':effective_score', 0, PDO::PARAM_INT);
					$query_newGrade->bindValue(':comment', "You did not turn in an assignment.", PDO::PARAM_INT);

					$query_newGrade->execute();

					$gradeIDs[] = $login->db_connection->lastInsertId();
				// There was no submission, fail
				} else {
					$error = true;
				}
			} else {
				$gradeIDs[] = $grade->gradeID;
			}
		}
		// If there were no rows returned, then the data did not get inserted correctly
		if(!$error) { 
			
			// Finalize and show the user their grade
			$query_updateAssignment = $login->db_connection->prepare('UPDATE assignments SET gradeVisible = 1 WHERE assignmentID = :assignmentID');
			$query_updateAssignment->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_INT);
			$query_updateAssignment->execute();

			// Grades were successfully finalized
			if ($query_updateAssignment->rowCount() > 0) {
				// Send out a notification to the users
				for($i = 0; $i < count($gradeIDs); $i++) {
					$query_newAnnouncement = $login->db_connection->prepare('INSERT INTO announcements (time, type, typeID, sectionID, userID) VALUES(:time, :type, :typeID, :sectionID, :userID) ') or die(mysqli_error($db_connection_insert));
						$query_newAnnouncement->bindValue(':time', time(), PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':type', "GRADE", PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':typeID', $gradeIDs[$i], PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':userID', $userIDs[$i], PDO::PARAM_INT);

						$query_newAnnouncement->execute();

					// Update the section grades now that the grades have been finalized
					$query_grades = $login->db_connection->prepare('SELECT * FROM grades WHERE userID = :userID AND sectionID = :sectionID');
						$query_grades->bindValue(':userID',  $userIDs[$i], PDO::PARAM_STR);
						$query_grades->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
						$query_grades->execute();

					$totalPoints = 0;
					$pointsScored = 0;
					// Loop through all of the grades and add them together
					while($assignmentGrade = $query_grades->fetchObject()) {
						$query_assignmentsTotal = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID');
						$query_assignmentsTotal->bindValue(':assignmentID',  $assignmentGrade->assignmentID, PDO::PARAM_STR);
						$query_assignmentsTotal->execute();
						$assignmentTotal = $query_assignmentsTotal->fetchObject();

						$pointsScored += $assignmentGrade->effective_score;
						$totalPoints += $assignmentTotal->maxScore;
					}

					// Calulate the grade
					$grade = "". ($pointsScored/$totalPoints)*100;
					// If the grade is zero
					if($grade == "") {
						$grade = "0.00";
					}
					$query_updateAssignment = $login->db_connection->prepare('UPDATE sectionGrades SET grade = :grade WHERE userID = :userID AND sectionID = :sectionID');
						$query_updateAssignment->bindValue(':grade', $grade);
						$query_updateAssignment->bindValue(':userID', $userIDs[$i], PDO::PARAM_INT);
						$query_updateAssignment->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_INT);
						$query_updateAssignment->execute();

				}
				echo "You have successfully finalized the grades.";
			} else {
				echo "There was an error and grades were not finalized.";
			}
		} else {
			echo "You must grade all of the submissions before you finalize the grades.";
		}
	} else {
		echo "You do not have permission to do this.";
	}
} else {
	echo "Database connection failed.";
}
?>