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
		$query_new_submission = $login->db_connection->prepare('INSERT INTO submissions (submit_time, comment, assignmentID, fileID, userID) VALUES(:submit_time, :comment, :assignmentID, :fileID, :userID)');
			$query_new_submission->bindValue(':submit_time', time(), PDO::PARAM_STR);
			$query_new_submission->bindValue(':comment', $_POST['comment'], PDO::PARAM_STR);
			$query_new_submission->bindValue(':assignmentID', $_POST['assignment'], PDO::PARAM_STR);
			$query_new_submission->bindValue(':fileID', $fileID, PDO::PARAM_STR);
			$query_new_submission->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
			$query_new_submission->execute();
		// Upload directory
		$uploaddir = $_SERVER['DOCUMENT_ROOT']. '/users/submissions/';
		// create the url to upload the file with the fileID as the file name
		$uploadfile = $uploaddir . $fileID .".". $ext;
		// copy the temp uploaded file into the user/submissions directory

		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
		    echo "Your submission was successfully uploaded. Reloading...";
		} else {
		    echo "There was an error and your file did not upload. Reloading...";
		}
	} else {
		echo "There was an error and your file did not upload. Reloading...";
	}
} else {
	echo "Database connection failed. Reloading...";
}
?>