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
	$query_newResource = $login->db_connection->prepare('INSERT INTO resources (name, creatorID, comment, fileID) VALUES(:name, :creatorID, :comment, :fileID) ') or die(mysqli_error($db_connection_insert));
	$query_newResource->bindValue(':name', $_POST['name'], PDO::PARAM_INT);
	$query_newResource->bindValue(':creatorID', $_SESSION['userID'], PDO::PARAM_INT);
	$query_newResource->bindValue(':comment', $_POST['comment'], PDO::PARAM_INT);
	$query_newResource->bindValue(':fileID', $_POST['fileID'], PDO::PARAM_INT);
	$query_newResource->execute();

	$resourceID = $login->db_connection->lastInsertId();

	$query_sectionResource = $login->db_connection->prepare('INSERT INTO sectionResources (sectionID, resourceID) VALUES(:sectionID, :resourceID)');
		$query_sectionResource->bindValue(':sectionID', $_POST['sectionID'], PDO::PARAM_STR);
		$query_sectionResource->bindValue(':resourceID', $resourceID, PDO::PARAM_STR);
		$query_sectionResource->execute();
	// If there were no rows returned, then the data did not get inserted correctly
	if($query_newResource->rowCount() > 0) { 
		echo "You have created a new assignment! Reloading...";
	} else {
		echo "There was an error and you did not create a new assignment. Reloading...";
	}
} else {
	echo "Database connection failed. Reloading...";
}
?>
