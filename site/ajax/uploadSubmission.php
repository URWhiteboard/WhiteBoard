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

	$query_new_file = $login->db_connection->prepare('INSERT INTO files (extension, title, upload_time) VALUES(:extension, :title, :upload_time)');
		$query_new_file->bindValue(':extension', $ext, PDO::PARAM_STR);
		$query_new_file->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
		$query_new_file->bindValue(':upload_time', time(), PDO::PARAM_STR);
	$query_new_file->execute();
	// If there were no rows returned, then the data did not get inserted correctly
	if($query_new_file->rowCount() > 0) { 
		// Upload directory
		$uploaddir = '../users/submissions/';
		// create the url to upload the file with the fileID as the file name
		$uploadfile = $uploaddir . $login->db_connection->lastInsertId() .".". $ext;
		// copy the temp uploaded file into the user/submissions directory
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
		    echo "File upload successful.";
		} else {
		    echo "There was an error and your file did not upload.";
		}
	} else {
		echo "There was an error and your file did not upload.";
	}
} else {
	echo "Database connection failed";
}
?>