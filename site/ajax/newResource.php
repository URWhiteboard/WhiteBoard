<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();
if ($login->databaseConnection()) {
	// Check if a file was submitted
	if($_FILES['file']['name']=="") {
		$query_newResource = $login->db_connection->prepare('INSERT INTO sectionResources (submit_time, comment, name, userID, sectionID) VALUES(:submit_time, :comment, :name, :userID, :sectionID) ') or die(mysqli_error($db_connection_insert));
			$query_newResource->bindValue(':submit_time', time(), PDO::PARAM_INT);
			$query_newResource->bindValue(':comment', $_POST['comment'], PDO::PARAM_INT);
			$query_newResource->bindValue(':name', $_POST['name'], PDO::PARAM_INT);
			$query_newResource->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_INT);
			$query_newResource->bindValue(':sectionID', $_POST['section'], PDO::PARAM_INT);
			$query_newResource->execute();

		echo "Your resource was successfully added. Reloading...";
	} else {

		// Extract the extension from the file name
		$ext = end((explode(".", $_FILES['file']['name'])));
		// Insert file information into the database
		$query_new_file = $login->db_connection->prepare('INSERT INTO files (extension, title, upload_time, userID) VALUES(:extension, :title, :upload_time, :userID)');
			$query_new_file->bindValue(':extension', $ext, PDO::PARAM_STR);
			$query_new_file->bindValue(':title', $_FILES['file']['name'], PDO::PARAM_STR);
			$query_new_file->bindValue(':upload_time', time(), PDO::PARAM_STR);
			$query_new_file->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
			$query_new_file->execute();

		$fileID = $login->db_connection->lastInsertId();
		// If there were no rows returned, then the data did not get inserted correctly
		if($query_new_file->rowCount() > 0) {
			// Insert resource into database
			$query_newResource = $login->db_connection->prepare('INSERT INTO sectionResources (submit_time, comment, name, fileID, userID, sectionID) VALUES(:submit_time, :comment, :name, :fileID, :userID, :sectionID) ') or die(mysqli_error($db_connection_insert));
				$query_newResource->bindValue(':submit_time', time(), PDO::PARAM_INT);
				$query_newResource->bindValue(':comment', $_POST['comment'], PDO::PARAM_INT);
				$query_newResource->bindValue(':name', $_POST['name'], PDO::PARAM_INT);
				$query_newResource->bindValue(':fileID', $fileID, PDO::PARAM_INT);
				$query_newResource->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_INT);
				$query_newResource->bindValue(':sectionID', $_POST['section'], PDO::PARAM_INT);
				$query_newResource->execute();

			$resourceID = $login->db_connection->lastInsertId();

			// Upload directory
			$uploaddir = $_SERVER['DOCUMENT_ROOT']. '/users/resources/';
			// create the url to upload the file with the fileID as the file name
			$uploadfile = $uploaddir . $fileID .".". $ext;
			// copy the temp uploaded file into the user/submissions directory

			if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
				// Get all of the section users
				$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE sectionID = :sectionID');
					$query_sectionStudents->bindValue(':sectionID', $_POST['section'], PDO::PARAM_STR);
					$query_sectionStudents->execute();

				// Loop through all of the users assigned to this section
				while($sectionStudents = $query_sectionStudents->fetchObject()) {

					// Insert a new assignment for every user in this section
					$query_newAnnouncement = $login->db_connection->prepare('INSERT INTO announcements (time, type, typeID, sectionID, userID) VALUES(:time, :type, :typeID, :sectionID, :userID) ') or die(mysqli_error($db_connection_insert));
						$query_newAnnouncement->bindValue(':time', time(), PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':type', "RESOURCE", PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':typeID', $resourceID, PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':sectionID', $_POST['section'], PDO::PARAM_INT);
						$query_newAnnouncement->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_INT);

						$query_newAnnouncement->execute();
				}

				echo "Your resource was successfully added. Reloading...";
			} else {
			 	echo "There was an error and your resource was not added. Reloading...";
			}
		} else {
			echo "There was an error and your resource was not added. Reloading...";
		}
	}
} else {
	echo "Database connection failed. Reloading...";
}
?>