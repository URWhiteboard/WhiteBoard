<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();
if ($login->databaseConnection()) {
	// Insert new row is sectionStudents
	$query_newAssignment = $login->db_connection->prepare('INSERT INTO assignments (name, category, maxScore, creatorID, curveType, curveParam, due_time, show_letter, comment, latePolicyID, fileID, submittable) VALUES(:name, :category, :maxScore, :creatorID, :curveType, :curveParam, :due_time, :show_letter, :comment, :latePolicyID, :fileID, :submittable) ') or die(mysqli_error($db_connection_insert));
	$query_newAssignment->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':category', $_POST['category'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':maxScore', $_POST['maxScore'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':creatorID', $_SESSION['userID'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':curveType', $_POST['curve_type'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':curveParam', $_POST['curve_param'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':due_time', $_POST['datetimepicker'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':show_letter', $_POST['show_letter'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':comment', $_POST['comment'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':latePolicyID', $_POST['late_policy'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':fileID', $_POST['fileID'], PDO::PARAM_STR);
	$query_newAssignment->bindValue(':submittable', $_POST['submittable'], PDO::PARAM_STR);
	$query_newAssignment->execute();

	$assignmentID = $login->db_connection->lastInsertId();

	$query_sectionAssignment = $login->db_connection->prepare('INSERT INTO sectionAssignments (sectionID, assignmentID) VALUES(:sectionID, :assignmentID)');
		$query_sectionAssignment->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
		$query_sectionAssignment->bindValue(':assignmentID', $assignmentID, PDO::PARAM_STR);
		$query_sectionAssignment->execute();
	// If there were rows returned, then the data got inserted correctly
	if($query_newAssignment->rowCount() > 0) { 
		// Send notification to all of the users in the section
		// Get all of the section users
		$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE sectionID = :sectionID');
			$query_sectionStudents->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
			$query_sectionStudents->execute();

		// Loop through all of the users assigned to this section
		while($sectionStudents = $query_sectionStudents->fetchObject()) {

			// Insert a new assignment for every user in this section
			$query_newAnnouncement = $login->db_connection->prepare('INSERT INTO announcements (time, type, typeID, sectionID, userID) VALUES(:time, :type, :typeID, :sectionID, :userID) ') or die(mysqli_error($db_connection_insert));
				$query_newAnnouncement->bindValue(':time', time(), PDO::PARAM_INT);
				$query_newAnnouncement->bindValue(':type', "ASSIGNMENT", PDO::PARAM_INT);
				$query_newAnnouncement->bindValue(':typeID', $assignmentID, PDO::PARAM_INT);
				$query_newAnnouncement->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_INT);
				$query_newAnnouncement->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_INT);

				$query_newAnnouncement->execute();
		}
		echo "You have created a new assignment! Reloading...";
	} else {
		echo "There was an error and you did not create a new assignment. Reloading...";
	}
} else {
	echo "Database connection failed. Reloading...";
}
?>