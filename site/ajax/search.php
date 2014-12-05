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
			// if there is at least one result
			if($query_courses->rowCount() > 0) { 
				// Keep track of the result number
				$i = 0;
				//get result row as an object
				while($course = $query_courses->fetchObject()) {
					// Get all the sections of the course
					$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE courseID = :courseID');
						$query_section->bindValue(':courseID', $course->courseID, PDO::PARAM_STR);
						$query_section->execute();

					// Loop through all of the sections
					while($section = $query_section->fetchObject()) {
						echo "<a href='../courses/?s=". $section->sectionID ."' class='noUnderline'>";
					}
					echo "<div id='navbarSearchResultsSectionContainer' class='navbarSearchResultsSectionContainer' ". ((!$i++)? "style='border-top: none;'" : "") .">";
					// Replaces the found search term and makes it bold
					echo str_ireplace(strtoupper($_GET['s']), "<strong>". strtoupper($_GET['s']) ."</strong>", $course->title) ."<br>";
					echo str_ireplace(strtoupper($_GET['s']), "<strong>". strtoupper($_GET['s']) ."</strong>", $course->department ." ". $course->number)  ."<br>";
					echo "</div>";
					echo "</a>";
				}
			// There were no matches for the query
			} else {
				echo "<div id='navbarSearchResultsSectionContainer' class='navbarSearchResultsSectionContainer' ". ((!$i++)? "style='border-top: none;'" : "") .">";
				echo "There were no matches for ". $_GET['s'];
				echo "</div>";
			}
		}
	// The user has clicked in the box, but hasn't typed anything
	} else {
		echo "<div id='navbarSearchResultsSectionContainer' class='navbarSearchResultsSectionContainer' ". ((!$i++)? "style='border-top: none;'" : "") .">";
		echo "Start typing to search...";
		echo "</div>";
	}
	echo "</div>";
}
?>