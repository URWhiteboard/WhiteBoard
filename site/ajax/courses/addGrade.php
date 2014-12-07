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

	$query_newGrade = $login->db_connection->prepare('INSERT INTO grades (real_score, sectionID, userID, assignmentID, graderID, effective_score, comment) VALUES(:real_score, :sectionID, :userID, :assignmentID, :graderID, :effective_score, :comment) ') or die(mysqli_error($db_connection_insert));
	$query_newGrade->bindValue(':real_score', $_POST['real_score'], PDO::PARAM_INT);
	$query_newGrade->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_INT);
	$query_newGrade->bindValue(':userID', $_POST['user'], PDO::PARAM_INT);
	$query_newGrade->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_INT);
	$query_newGrade->bindValue(':graderID', $_SESSION['userID'], PDO::PARAM_INT);
	$query_newGrade->bindValue(':effective_score', $_POST['real_score'], PDO::PARAM_INT);
	$query_newGrade->bindValue(':comment', $_POST['comment'], PDO::PARAM_INT);

	$query_newGrade->execute();

	$query_assignment = $login->db_connection->prepare('SELECT maxScore FROM assignments WHERE assignmentID = :assignmentID');
		$query_assignment->bindValue(':assignmentID',  $_POST['assignment'], PDO::PARAM_STR);
		$query_assignment->execute();
		$assignment = $query_assignment->fetchObject();

	// If there were no rows returned, then the data did not get inserted correctly
	if($query_newGrade->rowCount() > 0) { 
		echo "Graded by: ";
		echo $_SESSION['name_first'] ." ". $_SESSION['name_last'] ."<br />";
		echo "Grade: ". $_POST['real_score'] ." / ". $assignment->maxScore ."<br />";
		echo "Actual grade: ". $_POST['real_score'] ." / ". $assignment->maxScore ."<br />";
		echo "Comments: ". $_POST['comment'] ."<br />";
	} else {
		echo "There was an error and the grade was not added.";
	}
} else {
	echo "Database connection failed.";
}
?>