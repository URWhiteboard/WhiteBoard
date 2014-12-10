<?php
// include the config
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// include the PHPMailer library
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/libraries/PHPMailer.php');

// inlucde the Login Class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();


// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {

	// load the login class
	include($_SERVER['DOCUMENT_ROOT'] .'/included/header.php');
	// Show the default page for logged in users
	?>
<div id="mainContentContainerContent" class="mainContentContainerContent">
<link rel="stylesheet" type="text/css" href="../../included/css/assignments.css">
<?php
if ($login->databaseConnection()) {
	// Show the student page
	if($login->getType() == "STUDENT") { 

			// Get all resources for this section
			$query_sectionAnnouncements = $login->db_connection->prepare('SELECT * FROM announcements WHERE userID = :userID ORDER BY time DESC LIMIT 100');
				$query_sectionAnnouncements->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionAnnouncements->execute();
			
			if($query_sectionAnnouncements->rowCount() == 0) {
				echo "There are no announcements for this section!";
			}

			$i = 0;
			// Loop through all of the sections resources
			while($announcements = $query_sectionAnnouncements->fetchObject()) {
				
				echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
				echo "<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>";
				echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";

				// If the announcement was a new grade
				if($announcements->type=="GRADE") {

					// Find the new grade
					$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE gradeID = :gradeID');
						$query_grade->bindValue(':gradeID', $announcements->typeID, PDO::PARAM_STR);
						$query_grade->execute();
						$grade = $query_grade->fetchObject();

					// Find the assignment that was graded
					$query_assignment = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID');
						$query_assignment->bindValue(':assignmentID', $grade->assignmentID, PDO::PARAM_STR);
						$query_assignment->execute();
						$assignment = $query_assignment->fetchObject();

					echo $assignment->name ." has been graded";

				// If the announcement was an assignment
				} else if($announcements->type=="ASSIGNMENT") {

					// Find the assignment that was posted
					$query_assignment = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID');
						$query_assignment->bindValue(':assignmentID', $announcements->typeID, PDO::PARAM_STR);
						$query_assignment->execute();
						$assignment = $query_assignment->fetchObject();

					echo $assignment->name ." has been assigned";

				// If the announcment was a resource
				} else if($announcements->type=="RESOURCE") {

					// Find the resource that was posted
					$query_resource = $login->db_connection->prepare('SELECT * FROM sectionResources WHERE resourceID = :resourceID');
						$query_resource->bindValue(':resourceID', $announcements->typeID, PDO::PARAM_STR);
						$query_resource->execute();
						$resource = $query_resource->fetchObject();

					echo $resource->name ." has been added to resources";

				// If the announcment was an assignment
				} else if($announcements->type=="ANNOUNCEMENT") {
					echo $announcements->title;
				}
				echo "</div>";
				echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
				echo date('D, F j \a\t g:i a', $announcements->time) ."";
				echo "</div>";
				echo "<div id='assignmentsAssignmentGrade' class='assignmentsAssignmentGrade'>";
				// Display the course that the announcement is from
					$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
						$query_section->bindValue(':sectionID', $announcements->sectionID, PDO::PARAM_STR);
						$query_section->execute();
						$section = $query_section->fetchObject();

					$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
						$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
						$query_course->execute();
						$course = $query_course->fetchObject();

					echo $course->department ." ". $course->number;
				echo "</div>";
				echo "</div>";
				echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";

				// If the announcement is a grade
				if($announcements->type=="GRADE") {

					// Check the database for submissions on this assignment
					echo "<h3>Submissions</h3>";
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE submit_time In(SELECT MAX(submit_time) FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID GROUP BY userID)');
						$query_submissions->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();

					// If there was no submissions
					if($query_submissions->rowCount() == 0) {
						echo "You did not submit anything for this assignment.<br>";
					} else {

						// loop through all of the submissions
						while($submission = $query_submissions->fetchObject()) {
							$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
							$query_file->bindValue(':fileID', $submission->fileID, PDO::PARAM_STR);
							$query_file->execute();
							$file = $query_file->fetchObject();
							// When file is uploaded, it should change to the id to find the file
							echo "<div id='assignmentsAssignmentSubmissionContainer' class='assignmentsAssignmentSubmissionContainer'>";
							echo "<div id='assignmentsAssignmentSubmissionUser' class='assignmentsAssignmentSubmissionUser'>";
							echo "Title: ". $file->title ."<br>";
							echo "URL: <a href='../../users/submissions/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
							echo "Submitted at ". date('D, F j \a\t g:i a', $submission->submit_time) ."<br />";
							echo "Comment: ";
							if($submission->comment=="") {
								echo "No Comment";
							} else {
								echo $submission->comment;
							} 
							echo "</div>";
							// Div that contains the grade
							echo "<div id='assignmentsAssignmentSubmissionTeacher' class='assignmentsAssignmentSubmissionTeacher'>";
							// Check if the assignment has been graded yet
							$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE assignmentID = :assignmentID AND userID = :userID');
								$query_grade->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
								$query_grade->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
								$query_grade->execute();
								$grade = $query_grade->fetchObject();

							echo "Graded by: ";
							// Get the submitters first and last name
							$query_gradingUserData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
							$query_gradingUserData->bindValue(':userID', $grade->graderID, PDO::PARAM_STR);
							$query_gradingUserData->execute();
							$gradingUser = $query_gradingUserData->fetchObject();
							echo $gradingUser->name_first ." ". $gradingUser->name_last ."<br />";
							echo "Grade: ". $grade->real_score ."/". $assignment->maxScore ."<br />";
							echo "Actual grade: ". $grade->effective_score ."/". $assignment->maxScore ."<br />";
							echo "Comments: ". $grade->comment ."<br />";
							echo "</div>";
							echo "</div>";
						}
					}
				// If the announcement type is an assignment
				} else if($announcements->type=="ASSIGNMENT") {
					echo "Due ". date('D, F j \a\t g:i a', $assignment->due_time) ."<br />";
					echo $assignment->comment ."<br />";

				// If the announcement type is a resource
				} else if($announcements->type=="RESOURCE") {

				// Check if the resource has a fileID, if so, show the link to it.
				if($resource->fileID!=null) {
					$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
					$query_file->bindValue(':fileID', $resource->fileID, PDO::PARAM_STR);
					$query_file->execute();
					$file = $query_file->fetchObject();

					echo "URL: <a href='../../users/download.php?id=". $file->fileID ."'>". $file->fileID .".". $file->extension ."</a><br>";
				}
				echo "Comment: ". $resource->comment ."<br />";

				// If the announcement is an announcement
				} else if($announcements->type=="ANNOUNCEMENT") {
					echo $announcements->comment;
				}

				echo "</div>";
				echo "</div>";
			}
		} else {
			// Not enrolled, redirect back to #info
			echo "Permission Denied!";
		}
		// Show the teacher page
	} else if($login->getType() == "TEACHER") {
		// Check to make sure the professor has permission to see this page
		$query_sectionTeachers = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionTeachers WHERE sectionID = :sectionID AND userID = :userID');
			$query_sectionTeachers->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
			$query_sectionTeachers->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
			$query_sectionTeachers->execute();
		$enrolled = $query_sectionTeachers->fetchColumn();
		// Enrolled, show page
		if($enrolled==1) {
			
			?>
			
			<?php
		} else {
			// Not enrolled, redirect back to #info
			echo "Permission Denied!";
		}
	}

} else {
	include($_SERVER['DOCUMENT_ROOT'] .'/included/headerout.php');
	// the user is not logged in. show the login form and more
?>
	<?php
	echo "Logged out content goes here";
	?>	
<?php
}
// Include footer
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>
<script>
// Expand the assignments div
$('.assignmentsAssignmentHeader').click(function(e) {
	$(this.parentNode).toggleClass('assignmentsAssignmentContainer');
	$(this.parentNode).toggleClass('assignmentsAssignmentContainerExpanded');
});
</script>