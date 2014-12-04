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
			// Get all assignments for this section
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments INNER JOIN assignments ON sectionAssignments.assignmentID = assignments.assignmentID WHERE sectionAssignments.sectionID = :sectionID ORDER BY due_time DESC');
				$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAssignments->execute();
			
			$i = 0;
			// get result row as an object, so we can itenerate through the sections
			while($assignment = $query_sectionAssignments->fetchObject()) {
				// Set default to submittable
				$gradeable = false;
				$valueLost = NULL;
				// Calculate the ramaining time
				$timeRemaining = $assignment->due_time-time();
				// Due date has passed, check latepolicy
				if($timeRemaining <= 0) {
					// Check if the assignment submission period is still active
					$query_latePolicies = $login->db_connection->prepare('SELECT * FROM latePolicies WHERE latePolicyID = :latePolicyID');
						$query_latePolicies->bindValue(':latePolicyID', $assignment->latePolicyID, PDO::PARAM_STR);
						$query_latePolicies->execute();
						$latePolicy = $query_latePolicies->fetchObject();
					// No late work accepted, submission period closed
					if($latePolicy->period == "NONE") {
						$gradeable = true;
					// Late policy based on hours
					} else if($latePolicy->period == "HOUR") {
						$hoursLate = ceil(abs($timeRemaining)/3600);
						// Late policy based on percent/day
						if($latePolicy->is_percent) {
							$zeroCreditHours = 100/($latePolicy->rate*$hoursLate);
							// Zero credit should be given
							if($hoursLate >= $zeroCreditHours) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$hoursLate ."% on the final grade.";
						} else {
							$zeroCreditHours = $assignment->maxScore-($latePolicy->rate*$hoursLate);
							// Zero credit should be given
							if($zeroCreditHours <= 0) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$hoursLate ." points on the final grade.";
						}
					// Late policy based on days
					} else if($latePolicy->period == "DAY") {
						$daysLate = ceil(abs($timeRemaining)/86400);
						// Late policy based on percent/day
						if($latePolicy->is_percent) {
							$zeroCreditDays = 100/($latePolicy->rate*$daysLate);
							// Zero credit should be given
							if($daysLate >= $zeroCreditDays) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$daysLate ."% on the final grade.";
						// Late policy based on points/day
						} else {
							$zeroCreditDays = $assignment->maxScore-($latePolicy->rate*$daysLate);
							// Zero credit should be given
							if($zeroCreditDays <= 0) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$daysLate ." points on the final grade.";
						}
					}
				}
				if($gradeable) {
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID ORDER BY submit_time DESC');
						$query_submissions->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();
						$tempSubmission = $query_submissions->fetchObject();
					$query_grades = $login->db_connection->prepare('SELECT * FROM grades WHERE submissionID = :submissionID');
						$query_grades->bindValue(':submissionID',  $tempSubmission->submissionID, PDO::PARAM_STR);
						$query_grades->execute();
						$grade = $query_grades->fetchObject();
					// will create a top border on the first assignment
					echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
					echo "<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>";
					echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";
					echo $assignment->name;
					echo "</div>";
					echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
					echo "Due ". date('D, F j \a\t g:i a', $assignment->due_time) ."";
					echo "</div>";
					echo "<div id='assignmentsAssignmentGrade' class='assignmentsAssignmentGrade'>";
					// Need to check to see if they have a grade for the assignment
					if($grade->real_score != NULL) {
						echo $grade->real_score ." / ". $assignment->maxScore ."";
					} else {
						echo "&ndash; / ". $assignment->maxScore ."";
					}
					echo "</div>";
					echo "</div>";
					echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
					// Check the database for submissions
					echo "<h3>Submissions</h3>";
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE submit_time In(SELECT MAX(submit_time) FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID GROUP BY userID)');
						$query_submissions->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();
					if($query_submissions->rowCount() == 0) {
						echo "You did not submit anything for this assignment.<br>";
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
						echo "URL: <a href='../../users/submissions/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
						echo "Title: ". $file->title ."<br>";
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
						$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE submissionID = :submissionID');
							$query_grade->bindValue(':submissionID', $submission->submissionID, PDO::PARAM_STR);
							$query_grade->execute();
							$grade = $query_grade->fetchObject();
						if($grade!=null) {
							echo "Graded by: ";
							// Get the submitters first and last name
							$query_gradingUserData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
							$query_gradingUserData->bindValue(':userID', $grade->graderID, PDO::PARAM_STR);
							$query_gradingUserData->execute();
							$gradingUser = $query_gradingUserData->fetchObject();
							echo $gradingUser->name_first ." ". $gradingUser->name_last ."<br />";
							echo "Grade: ". $grade->real_score ." / ". $assignment->maxScore ."<br />";
							echo "Actual grade: ". $grade->effective_score ." / ". $assignment->maxScore ."<br />";
							echo "Comments: ". $grade->comment ."<br />";
						} else {
							// Need to add functionality to grade the submission
							echo "This assignment has not been graded yet.";
						}
						
						echo "</div>";
						echo "</div>";
					}
					echo "</div>";
					echo "</div>";
				}
			}
			if($i == 0) {
				echo "There are no grades for this section.";
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
			// Get all assignments for this section
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments INNER JOIN assignments ON sectionAssignments.assignmentID = assignments.assignmentID WHERE sectionAssignments.sectionID = :sectionID ORDER BY due_time DESC');
				$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAssignments->execute();
			
			if($query_sectionAssignments->rowCount() == 0) {
				echo "There are no gradeable assignments for this section!";
			}

			$i = 0;
			// get result row as an object, so we can itenerate through the sections
			while($assignment = $query_sectionAssignments->fetchObject()) {
				// Set gradeable to false
				$graded = false;
				$valueLost = NULL;
				// Calculate the ramaining time
				$timeRemaining = $assignment->due_time-time();
				// Due date has passed, check latepolicy
				if($timeRemaining <= 0) {
					// Check if the assignment submission period is still active
					$query_latePolicies = $login->db_connection->prepare('SELECT * FROM latePolicies WHERE latePolicyID = :latePolicyID');
						$query_latePolicies->bindValue(':latePolicyID', $assignment->latePolicyID, PDO::PARAM_STR);
						$query_latePolicies->execute();
						$latePolicy = $query_latePolicies->fetchObject();
					// No late work accepted, submission period closed
					if($latePolicy->period == "NONE") {
						$gradeable = true;
						// Late policy based on hours
					} else if($latePolicy->period == "HOUR") {
						$hoursLate = ceil(abs($timeRemaining)/3600);
						// Late policy based on percent/day
						if($latePolicy->is_percent) {
							$zeroCreditHours = 100/($latePolicy->rate*$hoursLate);
							// Zero credit should be given
							if($hoursLate >= $zeroCreditHours) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$hoursLate ."% on the final grade.";
						} else {
							$zeroCreditHours = $assignment->maxScore-($latePolicy->rate*$hoursLate);
							// Zero credit should be given
							if($zeroCreditHours <= 0) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$hoursLate ." points on the final grade.";
						}
					// Late policy based on days
					} else if($latePolicy->period == "DAY") {
						$daysLate = ceil(abs($timeRemaining)/86400);
						// Late policy based on percent/day
						if($latePolicy->is_percent) {
							$zeroCreditDays = 100/($latePolicy->rate*$daysLate);
							// Zero credit should be given
							if($daysLate >= $zeroCreditDays) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$daysLate ."% on the final grade.";
						// Late policy based on points/day
						} else {
							$zeroCreditDays = $assignment->maxScore-($latePolicy->rate*$daysLate);
							// Zero credit should be given
							if($zeroCreditDays <= 0) {
								$gradeable = true;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$daysLate ." points on the final grade.";
						}
					}
				}
				if($gradeable) {
					echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
					echo "<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>";
					echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";
					echo $assignment->name;
					echo "</div>";
					echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
					echo "Due ". date('D, F j \a\t g:i a', $assignment->due_time) ."";
					echo "</div>";
					echo "</div>";
					echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
					// Check the database for submissions
					echo "<h3>Submissions</h3>";

					// a query below that will show the most recent submission from every user
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE submit_time In(SELECT MAX(submit_time) FROM submissions WHERE assignmentID = :assignmentID GROUP BY userID)');
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();
					if($query_submissions->rowCount() == 0) {
						echo "There are no submissions to grade.<br>";
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
						echo "URL: <a href='../../users/submissions/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
						echo "Title: ". $file->title ."<br>";
						echo "Submitted at ". date('D, F j \a\t g:i a', $submission->submit_time) ." by ";
						$query_gradingUserData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
						$query_gradingUserData->bindValue(':userID', $submission->userID, PDO::PARAM_STR);
						$query_gradingUserData->execute();
						$gradingUser = $query_gradingUserData->fetchObject();
						echo $gradingUser->name_first ." ". $gradingUser->name_last ."<br />";
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
						$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE submissionID = :submissionID');
							$query_grade->bindValue(':submissionID', $submission->submissionID, PDO::PARAM_STR);
							$query_grade->execute();
							$grade = $query_grade->fetchObject();
						if($grade!=null) {
							echo "Graded by: ";
							// Get the submitters first and last name
							$query_gradingUserData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
							$query_gradingUserData->bindValue(':userID', $grade->graderID, PDO::PARAM_STR);
							$query_gradingUserData->execute();
							$gradingUser = $query_gradingUserData->fetchObject();
							echo $gradingUser->name_first ." ". $gradingUser->name_last ."<br />";
							echo "Grade: ". $grade->real_score ." / ". $assignment->maxScore ."<br />";
							echo "Actual grade: ". $grade->effective_score ." / ". $assignment->maxScore ."<br />";
							echo "Comments: ". $grade->comment ."<br />";
						} else {
							// Need to add functionality to grade the submission
							echo "This assignment has not been graded yet.";
						}
						
						echo "</div>";
						echo "</div>";
					}
					echo "</div>";
					echo "</div>";
				}
			}
		} else {
			// Not enrolled, redirect back to #info
			echo "Permission Denied!";
		}
	}
}
?>
<script>
// Expand the assignments div
$('.assignmentsAssignmentHeader').click(function(e) {
	$(this.parentNode).toggleClass('assignmentsAssignmentContainer');
	$(this.parentNode).toggleClass('assignmentsAssignmentContainerExpanded');
});
</script>