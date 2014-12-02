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
	$query_newAssignment = $login->db_connection->prepare('INSERT INTO assignments (name, category, maxScore, creatorID, curveType, curveParam, due_time, show_letter, comment, latePolicyID, fileID) VALUES(:name, :category, :maxScore, :creatorID, :curveType, :curveParam, :due_time, :show_letter, :comment, :latePolicyID, :fileID) ') or die(mysqli_error($db_connection_insert));
	$query_newAssignment->bindValue(':name', $_POST['name'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':category', $_POST['category'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':maxScore', $_POST['maxScore'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':creatorID', $_SESSION['userID'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':curveType', $_POST['curve_type'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':curveParam', $_POST['curve_param'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':due_time', $_POST['datetimepicker'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':show_letter', $_POST['show_letter'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':comment', $_POST['comment'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':latePolicyID', $_POST['late_policy'], PDO::PARAM_INT);
	$query_newAssignment->bindValue(':fileID', $_POST['fileID'], PDO::PARAM_INT);
	$query_newAssignment->execute();

	$assignmentID = $login->db_connection->lastInsertId();

	$query_sectionAssignment = $login->db_connection->prepare('INSERT INTO sectionAssignments (sectionID, assignmentID) VALUES(:sectionID, :assignmentID)');
		$query_sectionAssignment->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
		$query_sectionAssignment->bindValue(':assignmentID', $assignmentID, PDO::PARAM_STR);
		$query_sectionAssignment->execute();
	// If there were no rows returned, then the data did not get inserted correctly
	if($query_newAssignment->rowCount() > 0) { 
		echo "You have created a new assignment!";
	} else {
		echo "There was an error and you did not create a new assignment.";
	}
} else {
	echo "Database connection failed";
}
?>