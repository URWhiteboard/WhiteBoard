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
	echo "<div id='navbarSearchResultsContainerContent' class='navbarSearchResultsContainerContent'>";
	// ... ask if we are logged in here:
	if ($login->isUserLoggedIn() == false) {
		// the user is not logged in, redirect them to the homepage
		echo "Please log in.";
	}
	if($_GET['s'] != "") {
		if ($login->databaseConnection()) {
			// database query, getting all the info of the selected user
			$query_courses = $login->db_connection->prepare("SELECT * FROM courses WHERE concat_ws(' ',department,number) LIKE :department_number OR department LIKE :department OR title LIKE :title OR number LIKE :number LIMIT 10");
			$query_courses->bindValue(':department_number', "%". $_GET['s'] ."%", PDO::PARAM_INT);
			$query_courses->bindValue(':department', $_GET['s'] ."%", PDO::PARAM_INT);
			$query_courses->bindValue(':title', "%". $_GET['s'] ."%", PDO::PARAM_INT);
			$query_courses->bindValue(':number', "%". $_GET['s'] ."%", PDO::PARAM_INT);
			$query_courses->execute();
			// get result row (as an object)
			if($query_courses->rowCount() > 0) { 
				$i = 0;
				while($course = $query_courses->fetchObject()) {
					$i++;
					echo "CourseID: <a href='../courses/?c=". $course->courseID ."'>". $course->courseID ."</a><br>";
					// get result row (as an object)
					$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE courseID = :courseID');
						$query_section->bindValue(':courseID', $course->courseID, PDO::PARAM_STR);
						$query_section->execute();
					echo "SectionID: ";
					while($section = $query_section->fetchObject()) {
						echo "<a href='../courses/?s=". $section->sectionID ."'>". $section->sectionID ."</a> ";
					}
					echo "<br>";
					echo "Title: ". $course->title ."<br>";
					echo "Department: ". $course->department ." ". $course->number ."<br>";
					if($i != $query_courses->rowCount()) {
						echo "<br>";
					}
				}
			} else {
				echo "There were no matches for ". $_GET['s'];
			}
		}
	} else {
		echo "Start typing to search...";
	}
	echo "</div>";
}
?>