<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();
if ($login->databaseConnection()) {

	// Get all of the section users
	$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE sectionID = :sectionID');
		$query_sectionStudents->bindValue(':sectionID', $_POST['section'], PDO::PARAM_STR);
		$query_sectionStudents->execute();

	// Loop through all of the users assigned to this section
	while($sectionStudents = $query_sectionStudents->fetchObject()) {

		// Insert a new assignment for every user in this section
		$query_newAnnouncement = $login->db_connection->prepare('INSERT INTO announcements (time, type, sectionID, userID, title, comment) VALUES(:time, :type, :sectionID, :userID, :title, :comment) ') or die(mysqli_error($db_connection_insert));
			$query_newAnnouncement->bindValue(':time', time(), PDO::PARAM_INT);
			$query_newAnnouncement->bindValue(':type', "ANNOUNCEMENT", PDO::PARAM_INT);
			$query_newAnnouncement->bindValue(':sectionID', $_POST['section'], PDO::PARAM_INT);
			$query_newAnnouncement->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_INT);
			$query_newAnnouncement->bindValue(':title', $_POST['name'], PDO::PARAM_INT);
			$query_newAnnouncement->bindValue(':comment', $_POST['comment'], PDO::PARAM_INT);

			$query_newAnnouncement->execute();
	}
	echo "Your announcement was successfully sent. Reloading...";
} else {
	echo "Database connection failed. Reloading...";
}
?>