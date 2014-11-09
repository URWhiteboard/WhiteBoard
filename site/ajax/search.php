<?php
// Checks to make sure that it was an ajax request
$AJAX = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
if ($AJAX){
	// include the config
	require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

	// load the login class
	require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

	// create a login object. when this object is created, it will do all login/logout stuff automatically
	// so this single line handles the entire login process.
	$login = new Login();

	// ... ask if we are logged in here:
	if ($login->isUserLoggedIn() == false) {
		// the user is not logged in, redirect them to the homepage
		echo "Please log in.";
	}
	if($_GET['s'] != "") {
		if ($login->databaseConnection()) {
			// database query, getting all the info of the selected user
			$query_section = $login->db_connection->prepare("SELECT * FROM courses WHERE concat_ws(' ',department,number) LIKE :department_number OR department LIKE :department OR title LIKE :title OR number LIKE :number LIMIT 10");
			$query_section->bindValue(':department_number', "%". $_GET['s'] ."%", PDO::PARAM_INT);
			$query_section->bindValue(':department', $_GET['s'] ."%", PDO::PARAM_INT);
			$query_section->bindValue(':title', "%". $_GET['s'] ."%", PDO::PARAM_INT);
			$query_section->bindValue(':number', "%". $_GET['s'] ."%", PDO::PARAM_INT);
			$query_section->execute();
			// get result row (as an object)
			if($query_section->rowCount() > 0) { 
				while($course = $query_section->fetchObject()) {
					echo "CourseID: <a href='../courses/?c=". $course->courseID ."'>". $course->courseID ."</a><br>";
					echo "Title: ". $course->title ."<br>";
					echo "Department: ". $course->department ." ". $course->number ."<br>";
					echo "<br>";
				}
			} else {
				echo "There were no matches for ". $_GET['s'];
			}
		}
	} else {
		echo "Start typing to search...";
	}
}
?>