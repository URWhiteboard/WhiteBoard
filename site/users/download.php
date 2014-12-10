<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();
if ($login->databaseConnection()) {
	// Boolean if the user has permission to download the file
	$permissionGranted = false;
	// Check if user is a teacher or a student
	if($login->getType() == "STUDENT") { 
		// check if user uploaded this file, if so let them download it
		$query_userPermission = $login->db_connection->prepare('SELECT * FROM submissions WHERE fileID = :fileID');
			$query_userPermission->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
			$query_userPermission->execute();
			$submission = $query_userPermission->fetchObject();
		// Permission granted
		if($submission->userID == $_SESSION['userID']) {
			$permissionGranted = true;
		// Check if the professor uploaded the file and they are in that section
		} else {
			// The file was uploaded by a teacher
			$query_assignments = $login->db_connection->prepare('SELECT * FROM assignments WHERE fileID = :fileID');
				$query_assignments->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
				$query_assignments->execute();
				$assignment = $query_assignments->fetchObject();

			// Get the section that the assignment was submitted to
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments WHERE assignmentID = :assignmentID');
				$query_sectionAssignments->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
				$query_sectionAssignments->execute();
				$sectionAssignments = $query_sectionAssignments->fetchObject();

			// Check if the professor of this section uploaded the assignment
			$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
				$query_sectionTeacher->bindValue(':sectionID', $sectionAssignments->sectionID, PDO::PARAM_STR);
				$query_sectionTeacher->execute();
				$sectionTeacher = $query_sectionTeacher->fetchObject();
			// Section teacher uploaded the file
			if($sectionTeacher->userID == $assignment->creatorID && $sectionTeacher->userID != NULL) {
				// Check if the user is enrolled in that section
				$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
					$query_sectionStudents->bindValue(':sectionID', $sectionAssignments->sectionID, PDO::PARAM_STR);
					$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
					$query_sectionStudents->execute();
				// User was enrolled in section, permission granted
				if($query_sectionStudents->rowCount() != 0 ) {
					$permissionGranted = true;
				}
			// Check to see if it was a resource
			} else {
			// Get the resource that the assignment was submitted to
			$query_sectionResources = $login->db_connection->prepare('SELECT * FROM sectionResources WHERE fileID = :fileID');
				$query_sectionResources->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
				$query_sectionResources->execute();
				$sectionResources = $query_sectionResources->fetchObject();

				// Check if the professor of this section uploaded the assignment
				$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
					$query_sectionTeacher->bindValue(':sectionID', $sectionResources->sectionID, PDO::PARAM_STR);
					$query_sectionTeacher->execute();
					$sectionTeacher = $query_sectionTeacher->fetchObject();
				// Section teacher uploaded the file
				if($sectionTeacher->userID == $sectionResources->userID) {
					// Check if the user is enrolled in that section
					$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
						$query_sectionStudents->bindValue(':sectionID', $sectionResources->sectionID, PDO::PARAM_STR);
						$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_sectionStudents->execute();
					// User was enrolled in section, permission granted
					if($query_sectionStudents->rowCount() != 0 ) {
						$permissionGranted = true;
					}
				}
			}
		}
	// User is a teacher
	} else if($login->getType() == "TEACHER") {
		// Get the submissionID
		$query_userPermission = $login->db_connection->prepare('SELECT * FROM submissions WHERE fileID = :fileID');
			$query_userPermission->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
			$query_userPermission->execute();
			$submission = $query_userPermission->fetchObject();

		// File was uploaded by a teacher
		if($query_userPermission->rowCount() == 0) {
			$query_assignments = $login->db_connection->prepare('SELECT * FROM assignments WHERE fileID = :fileID');
				$query_assignments->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
				$query_assignments->execute();
				$assignment = $query_assignments->fetchObject();
			// Permission granted
			if($assignment->creatorID == $_SESSION['userID']) {
				$permissionGranted = true;
			} else {
				// Check the sectionResource for the file uploaded
				$query_sectionResources = $login->db_connection->prepare('SELECT * FROM sectionResources WHERE fileID = :fileID AND userID = :userID');
					$query_sectionResources->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
					$query_sectionResources->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
					$query_sectionResources->execute();
					$sectionResources = $query_sectionResources->fetchObject();
				
				if($query_sectionResources->rowCount() > 0) {
					$permissionGranted = true;
				}
			}
		} else {
			// Get who created the submission
			$query_instructorPermission = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID');
				$query_instructorPermission->bindValue(':assignmentID', $submission->assignmentID, PDO::PARAM_STR);
				$query_instructorPermission->execute();
				$assignment = $query_instructorPermission->fetchObject();
			// Creator is the current user, grant permission
			if($assignment->creatorID == $_SESSION['userID']) {
				$permissionGranted = true;
			}
		}
	}
	// If permission was granted, get the file extension and start the download
	if($permissionGranted) {
		$query_file = $login->db_connection->prepare('SELECT extension FROM files WHERE fileID = :fileID');
			$query_file->bindValue(':fileID', $_GET['id'], PDO::PARAM_STR);
			$query_file->execute();
			$file = $query_file->fetchObject();
		$fileURL = $_GET['id'] .".". $file->extension;
		header("Pragma: public"); // required
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Cache-Control: private",false); // required for certain browsers 
		header("Content-Disposition: attachment; filename=\"" . basename($fileURL) . "\"");
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($fileURL));
		header("Connection: close");
	// Tell the user they can't access the file
	} else {
		echo "Permission Denied!";
	}
} else {
	echo "There was an error connecting to the database. Please try again later.";
}
?>