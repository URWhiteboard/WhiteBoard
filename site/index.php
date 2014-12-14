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
		$query_sectionAnnouncements = $login->db_connection->prepare('SELECT * FROM announcements WHERE userID = :userID ORDER BY time DESC');
			$query_sectionAnnouncements->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
			$query_sectionAnnouncements->execute();
		
		if($query_sectionAnnouncements->rowCount() == 0) {
			echo "<h3>You have no new announcements</h3>";
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

					// Check the database for submissions
					echo "<h3>Submissions</h3>";
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE submit_time In(SELECT MAX(submit_time) FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID GROUP BY userID)');
						$query_submissions->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();

					if(!$assignment->submittable) {
						// Div that contains the grade
						echo "<div id='assignmentsAssignmentSubmissionContainer' class='assignmentsAssignmentSubmissionContainer'>";
						echo "<div id='assignmentsAssignmentSubmissionTeacher' class='assignmentsAssignmentSubmissionTeacher'>";
						// Check if the assignment has been graded yet
						$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE assignmentID = :assignmentID AND userID = :userID');
							$query_grade->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
							$query_grade->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
							$query_grade->execute();
							$grade = $query_grade->fetchObject();
						if($grade!=null && $assignment->gradeVisible) {
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

						// Assignment has not yet been graded
						} else {
							echo "This assignment has not been graded yet.";
						}
						echo "</div>";
						echo "</div>";
					} else if($query_submissions->rowCount() == 0) {
						echo "You did not submit anything for this assignment.<br />";
					}
					// loop through all of the submissions
					while($submission = $query_submissions->fetchObject()) {
						$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
						$query_file->bindValue(':fileID', $submission->fileID, PDO::PARAM_STR);
						$query_file->execute();
						$file = $query_file->fetchObject();
						// When file is uploaded, it should change to the id to find the file, otherwise collisions will happen
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
						if($grade!=null && $assignment->gradeVisible) {
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
						} else {
							// Need to add functionality to grade the submission
							echo "This assignment has not been graded yet.";
						}
						echo "</div>";
						echo "</div>";
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
		} else if($login->getType() == "TEACHER") {
			echo "<h3>Welcome back!</h3>";
		}
	} 
} else {
	include($_SERVER['DOCUMENT_ROOT'] .'/included/headerout.php');
	// the user is not logged in. show the login form and more
	// load the registration class
	require_once($_SERVER['DOCUMENT_ROOT'] .'/classes/Registration.php');

	// create the registration object. when this object is created, it will do all registration stuff automatically
	// so this single line handles the entire registration process.
	$registration = new Registration();

?>
	
		<?php
		// show potential errors / feedback (from login object)
		if (isset($login)) {
			if ($login->errors) {
				echo "<div id='loggedOutErrorContainer' class='loggedOutErrorContainer'>";
				foreach ($login->errors as $error) {
					echo $error;
				}
				echo "</div>";
			}
			if ($login->messages) {
				echo "<div id='loggedOutMessageContainer' class='loggedOutMessageContainer'>";
				foreach ($login->messages as $message) {
					echo $message;
				}
				echo "</div>";
			}
		}
		if (isset($registration)) {
			if ($registration->errors) {
				echo "<div id='loggedOutErrorContainer' class='loggedOutErrorContainer'>";
				foreach ($registration->errors as $error) {
					echo $error;
				}
				echo "</div>";
			}
			if ($registration->messages) {
				echo "<div id='loggedOutMessageContainer' class='loggedOutMessageContainer'>";
				foreach ($registration->messages as $message) {
					echo $message;
				}
				echo "</div>";
			}
		}
		?>
	<div id='loggedOutContainer' class='loggedOutContainer'>
		<div id='loggedOutContainerLeft' class='loggedOutContainerLeft'>
			<h2>Keep up to date with grades, assignments, and notifications with WhiteBoard.</h2>
		</div>
		<div id='loggedOutContainerRight' class='loggedOutContainerRight'>
			<h3>Sign up</h3>
			<form method="post" action="/" name="registerform">
			<input id="usernameSU" type="text" pattern="[a-zA-Z0-9]{2,64}" name="usernameSU" placeholder="Username" required />
			<br />
			<input id="name_first" type="text" pattern="[a-zA-Z]{2,30}" name="name_first" placeholder="First Name" required />
			<br />
			<input id="name_last" type="text" pattern="[a-zA-Z]{2,30}" name="name_last" placeholder="Last Name" required />
			<br />
			<input id="name_suffix" type="text" pattern="[a-zA-Z]{2,30}" placeholder="Suffix" name="name_suffix" />
			<br />
			<input id="email" type="email" name="email" placeholder="Email" required />
			<br />
			<label>&nbsp;Birthday</label><br />
			<select id="birth_month" name="birth_month" required>
			<option disabled="" selected="">Month</option>
			<option value="01">January</option>
			<option value="02">Febuary</option>
			<option value="03">March</option>
			<option value="04">April</option>
			<option value="05">May</option>
			<option value="06">June</option>
			<option value="07">July</option>
			<option value="08">August</option>
			<option value="09">September</option>
			<option value="10">October</option>
			<option value="11">November</option>
			<option value="12">December</option>
			</select>
			<select id="birth_day" name="birth_day" required>
			<option disabled="" selected="">Day</option>
			<?php
			for($i = 1; $i < 32; $i++) {
				echo "<option value=\"". $i ."\">". $i ."</option>";
			}
			?>
			</select>
			<select id="birth_year" name="birth_year" required>
			<option disabled="" selected="">Year</option>
			<?php
			for($i = date("Y"); $i > 1899 ; $i--) {
				echo "<option value=\"". $i ."\">". $i ."</option>";
			}
			?>
			</select>
			<br />
			<select id="expected_graduation" name="expected_graduation" required>
			<option disabled="" selected="">Class Of</option>
			<?php
			for($i = date("Y")-4; $i < date("Y")+5; $i++) {
				echo "<option value=\"". $i ."\">". $i ."</option>";
			}
			?>
			</select>
			<br />

			<input id="password_new" type="password" name="password_new" pattern=".{6,}" placeholder="Password (Min 6 characters)" required autocomplete="off" />
			<br />
			<input id="password_repeat" type="password" name="password_repeat" pattern=".{6,}" placeholder="Repeat Password" required autocomplete="off" />
			<br />
			<img src="../tools/showCaptcha.php" alt="captcha" />
			<br />
			<label>Enter these charaters below</label><br />
			<input type="text" name="captcha" required />
			<br />
			<input type="submit" name="register" value="<?php echo "Register"; ?>" />
		</form>
		</div>
	</div>
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