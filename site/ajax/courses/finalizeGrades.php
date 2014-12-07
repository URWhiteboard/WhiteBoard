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
		$error = false;
		// Get all of the users assigned to this section along with their data
		$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM users INNER JOIN sectionStudents ON sectionStudents.userID = users.userID WHERE sectionStudents.sectionID = :sectionID ORDER BY users.name_last, users.name_first ASC');
			$query_sectionStudents->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
			$query_sectionStudents->execute();

		// Loop through all of the users assigned to this section
		while($sectionStudents = $query_sectionStudents->fetchObject()) {
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
				if($query_submission->rowCount() == 0) {

				$query_newGrade = $login->db_connection->prepare('INSERT INTO grades (real_score, sectionID, userID, assignmentID, graderID, effective_score, comment) VALUES(:real_score, :sectionID, :userID, :assignmentID, :graderID, :effective_score, :comment) ') or die(mysqli_error($db_connection_insert));
					$query_newGrade->bindValue(':real_score', 0, PDO::PARAM_INT);
					$query_newGrade->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_INT);
					$query_newGrade->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_INT);
					$query_newGrade->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_INT);
					$query_newGrade->bindValue(':graderID', $_SESSION['userID'], PDO::PARAM_INT);
					$query_newGrade->bindValue(':effective_score', 0, PDO::PARAM_INT);
					$query_newGrade->bindValue(':comment', "You did not turn in an assignment.", PDO::PARAM_INT);

					$query_newGrade->execute();

				// There was no submission, fail
				} else {
					$error = true;
				}
			}
		}

		// If there were no rows returned, then the data did not get inserted correctly
		if(!$error) { 

			$query_updateAssignment = $login->db_connection->prepare('UPDATE assignments SET gradeVisible = 1 WHERE assignmentID = :assignmentID');
			$query_updateAssignment->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_INT);
			$query_updateAssignment->execute();

			if ($query_updateAssignment->rowCount() > 0) {
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