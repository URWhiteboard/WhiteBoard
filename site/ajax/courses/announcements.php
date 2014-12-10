<?php
require_once($_SERVER['DOCUMENT_ROOT'] .'/config/config.php');

// load the login class
require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Login.php');

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process.
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == false) {
	// the user is not logged in, redirect them to the homepage
	header("location: /");
}

?>
<link rel="stylesheet" type="text/css" href="../../included/css/assignments.css">
<?php
if ($login->databaseConnection()) {
	// Show the student page
	if($login->getType() == "STUDENT") { 
		// Check to make sure the user has permission to see this page
		$query_sectionStudents = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
			$query_sectionStudents->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
			$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
			$query_sectionStudents->execute();
		$enrolled = $query_sectionStudents->fetchColumn();
		// Enrolled, show page
		if($enrolled==1) {
			// Get all announcments for this section
			$query_sectionAnnouncements = $login->db_connection->prepare('SELECT * FROM announcements WHERE sectionID = :sectionID AND userID = :userID ORDER BY time DESC');
				$query_sectionAnnouncements->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAnnouncements->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionAnnouncements->execute();
			
			if($query_sectionAnnouncements->rowCount() == 0) {
				echo "There are no announcements for this section!";
			}

			$i = 0;
			// Loop through all of the sections announcements
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
			// Get all announcments for this section
			$query_sectionAnnouncements = $login->db_connection->prepare('SELECT * FROM announcements WHERE sectionID = :sectionID GROUP BY time ORDER BY time DESC');
				$query_sectionAnnouncements->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAnnouncements->execute();
			
			if($query_sectionAnnouncements->rowCount() == 0) {
				echo "There are no announcements for this section!";
			}

			$i = 0;
			// Loop through all of the sections announcements
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
				echo "</div>";
				echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";

				// If the announcement is a grade
				if($announcements->type=="GRADE") {

					echo "The assignment has been graded";
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
			?>
			<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer'>
				<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>
					<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>
						New Announcement
					</div>
					<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>
					&nbsp;
					</div>
				</div>
				<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>
					<form method="post" action="/users/" name="addResource" id="addResource" class="addResource">
						<input id="name" type="text" name="name" placeholder="Name" required />
						<br />
						<textarea id="comment" type="textarea" name="comment" placeholder="Comment" rows="4" cols="50"></textarea>
						<br />
						<br />
						<input id="section" type="hidden" name="section" value="<?php echo $_GET['s']; ?>"/>
						<input type="submit" name="submit" value="Send Announcement" />
						<br />
					</form>
				</div>
			</div>
			<?php
		} else {
			// Not enrolled, redirect back to #info
			echo "Permission Denied!";
		}
	}
}
?>
<script>

$(".addResource").on('submit', function( e ) {
	e.preventDefault();
	var f = e.target;
	var fd = new FormData(f);
	$.ajax({
		url: '../../ajax/newAnnouncement.php',
		type: 'POST',
		data: fd,
		processData: false,
		contentType: false,
		success:function(data, textStatus, jqXHR) 
		{
			//data: return data from server
			$(e.target.parentNode).html(data);
			// Reloads the course assignments, not the whole page
			if(data = "Your resource was successfully added. Reloading..."){
				loadTab($('#resources'), 'resources');
			}
		},
		error: function(jqXHR, textStatus, errorThrown) 
		{
			//if fails
			$(e.target.parentNode).html(data);
		}
	});
});
// Expand the assignments div
$('.assignmentsAssignmentHeader').click(function(e) {
	$(this.parentNode).toggleClass('assignmentsAssignmentContainer');
	$(this.parentNode).toggleClass('assignmentsAssignmentContainerExpanded');
});
</script>