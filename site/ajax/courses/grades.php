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
					$query_grades = $login->db_connection->prepare('SELECT * FROM grades WHERE assignmentID = :assignmentID AND userID = :userID');
						$query_grades->bindValue(':assignmentID',  $assignment->assignmentID, PDO::PARAM_STR);
						$query_grades->bindValue(':userID',  $_SESSION['userID'], PDO::PARAM_STR);
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
						echo $grade->real_score ."/". $assignment->maxScore ."";
					} else {
						echo "&ndash;/". $assignment->maxScore ."";
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
						} else {
							// Need to add functionality to grade the submission
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
				echo "There are no gradeable assignments for this section.";
			}

			$i = 0;
			// Loop through all of the sections assignments
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
					echo "<div id='assignmentsAssignmentGrade' class='assignmentsAssignmentGrade'>";
					if($assignment->gradeVisible) {
						echo "&#10003";
					}
					echo "</div>";
					echo "</div>";
					echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
					echo "<h3>Submissions</h3>";

					// Set a varaible to keep track of number of graded submissions and submissions
					$gradedSubmissions = 0;
					$submissionCount = 0;
					$pointsScored = 0;
					// Get all of the section users
					$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM users INNER JOIN sectionStudents ON sectionStudents.userID = users.userID WHERE sectionStudents.sectionID = :sectionID ORDER BY users.name_last, users.name_first ASC');
						$query_sectionStudents->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
						$query_sectionStudents->execute();

					// Loop through all of the users assigned to this section
					while($sectionStudents = $query_sectionStudents->fetchObject()) {
						$query_submission = $login->db_connection->prepare('SELECT * FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID ORDER BY submit_time DESC');
							$query_submission->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_STR);
							$query_submission->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
							$query_submission->execute();
							$submission = $query_submission->fetchObject();

						echo "<div id='assignmentsAssignmentSubmissionContainer' class='assignmentsAssignmentSubmissionContainer'>";
						echo "<div id='assignmentsAssignmentSubmissionHeader' class='assignmentsAssignmentSubmissionHeader'>";

						// The user did not have a submission and it was submittable
						if($query_submission->rowCount() == 0 && $assignment->submittable) {
							echo $sectionStudents->name_last .", ". $sectionStudents->name_first;
							echo "<p style='float: right;padding: 0;margin:0;'>&#63</p>";
							echo "</div>";
							echo "<div id='assignmentsAssignmentSubmissionContentContainer' class='assignmentsAssignmentSubmissionContentContainer'>";
							echo "<div id='assignmentsAssignmentSubmissionUser' class='assignmentsAssignmentSubmissionUser'>";
							echo "There was no submission";
							echo "</div>";
						// The user had a submission or if they were not allowed to have a submission
						} else {

							$submissionCount++;
							// Get the grade from the database
							$query_grade = $login->db_connection->prepare('SELECT * FROM grades WHERE assignmentID = :assignmentID AND userID = :userID');
								$query_grade->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
								$query_grade->bindValue(':userID', $sectionStudents->userID, PDO::PARAM_STR);
								$query_grade->execute();
								$grade = $query_grade->fetchObject();

							echo $sectionStudents->name_last .", ". $sectionStudents->name_first;
							if($grade!=null) {
								echo "<p style='float: right;padding: 0;margin:0;'>&#10003</p>";
							} else {
								echo "<p style='float: right;padding: 0;margin:0;'>&ndash;</p>";
							}
							echo "</div>";
							echo "<div id='assignmentsAssignmentSubmissionContentContainer' class='assignmentsAssignmentSubmissionContentContainer'>";
							echo "<div id='assignmentsAssignmentSubmissionUser' class='assignmentsAssignmentSubmissionUser'>";

							// Check to see if the user was allowed to submit anything
							if($assignment->submittable) {
								// Get the submission file
								$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
								$query_file->bindValue(':fileID', $submission->fileID, PDO::PARAM_STR);
								$query_file->execute();
								$file = $query_file->fetchObject();

								echo "URL: <a href='../../users/submissions/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
								echo "Title: ". $file->title ."<br>";
								echo "Submitted at ". date('D, F j \a\t g:i a', $submission->submit_time) ."<br />";
								echo "Comment: ";
								if($submission->comment=="") {
									echo "No Comment";
								} else {
									echo $submission->comment;
								} 
							// no need for a submission, the user was not allwed to upload one
							} else {
								echo "The user was not allowed to submit an assignment.";
							}
							echo "</div>";
							// Div that contains the grade
							echo "<div id='assignmentsAssignmentSubmissionTeacher' class='assignmentsAssignmentSubmissionTeacher'>";
							
							// Check if the submission has been graded yet
							if($grade!=null) {
								$gradedSubmissions++;
								echo "Graded by: ";
								// Get the submitters first and last name
								$query_gradingUserData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
								$query_gradingUserData->bindValue(':userID', $grade->graderID, PDO::PARAM_STR);
								$query_gradingUserData->execute();
								$gradingUser = $query_gradingUserData->fetchObject();
								echo $gradingUser->name_first ." ". $gradingUser->name_last ."<br />";
								echo "Grade: ". $grade->real_score ."/". $assignment->maxScore ."<br />";
								echo "Actual grade: ". $grade->effective_score ."/". $assignment->maxScore ."<br />";
								$pointsScored+=$grade->effective_score;
								echo "Comments: ". $grade->comment ."<br />";
							} else {
								// Allow the teacher to grade the submission
								echo "Grade Assignment";
								?>
								<form method="post" action="/users/" name="submitSubmission" id="addGrade" class="addGrade">
								<input id="real_score" type="text" name="real_score" placeholder="Score" pattern="[-+]?[0-9]*[.,]?[0-9]+" required />
									<input id="assignment" type="hidden" name="assignment" value="<?php echo $assignment->assignmentID; ?>"/>
									<input id="user" type="hidden" name="user" value="<?php echo $sectionStudents->userID; ?>"/>
									<label for="comment">Comment</label>
									<br />
									<textarea id="comment" type="textarea" name="comment" rows="4" cols="50"></textarea>
									<br />
									<input type="submit" name="submit" value="Add Grade" />
									<br />
								</form>
								<?php
							}
							echo "</div>";
						}
						echo "</div>";
						echo "</div>";
					}
					echo "<div id='gradesSubmissionsGraded' class='gradesSubmissionsGraded assignmentsAssignmentSubmissionTeacher'>";
					// Check to see if all of the submissions have been gradedd, if so, let the teacher send the assignments
					if($assignment->gradeVisible) {
						echo "The grades have all been finalized.<br />";
						echo "Average ". $pointsScored/$query_sectionStudents->rowCount() ."/". $assignment->maxScore;
						// Output the stats on the grades!

					} else {
						?>
						<form method="post" action="/users/" name="finalizeGrades" id="finalizeGrades" class="finalizeGrades">
							<input id="assignment" type="hidden" name="assignment" value="<?php echo $assignment->assignmentID; ?>"/>
							<input type="submit" name="submit" value="Finalize Grades" />
						</form>
						<?php
					}
					echo "</div>";
					
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

// Expand the submissions div
$('.assignmentsAssignmentSubmissionHeader').click(function(e) {
	$(this.parentNode).children( ".assignmentsAssignmentSubmissionContentContainer" ).toggleClass('assignmentsAssignmentSubmissionContentContainerExpanded');
});

// Functions gives the ability for submissions to be graded
$(".addGrade").on('submit', function(e) {
	e.preventDefault();
	var postData = $(this).serializeArray();
	var sectionID = {name:"sectionID", value:"<?php echo $_GET['s']; ?>"};
	postData.push(sectionID);

	var tempLoc = e.target.parentNode.parentNode.parentNode;
	// Check to make sure the grade has been filled in
	if(postData[0].value == "") {
		$('#real_score').addClass('error');
	} else {
		$.ajax({
			url: '../../ajax/courses/addGrade.php',
			type: 'POST',
			data: postData,
			success:function(data, textStatus, jqXHR) 
			{
				//data: return data from server
				$(e.target.parentNode).html(data);
				if(data!="There was an error and the grade was not added." && data!="Database connection failed.") {
					// Add check mark to div
					$(tempLoc).children("div:first-child").children("p:last-child").html("&#10003");
				}
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				//if fails
				$(e.target.parentNode).html(data);
			}
		});
	}
});

// Function checks if the teacher has graded all of the submissions
$(".finalizeGrades").on('submit', function(e) {
	e.preventDefault();
	var tempLoc = e.target.parentNode.parentNode.parentNode;
	var postData = $(this).serializeArray();
	var sectionID = {name:"sectionID", value:"<?php echo $_GET['s']; ?>"};
	postData.push(sectionID);
	$.ajax({
		url: '../../ajax/courses/finalizeGrades.php',
		type: 'POST',
		data: postData,
		success:function(data, textStatus, jqXHR) 
		{
			//data: return data from server
			if(data == "You have successfully finalized the grades.") {
				$(tempLoc).children("div:first-child").children("div:last-child").html("&#10003");
				$(e.target.parentNode).html(data);
			} else {
				if($(e.target.parentNode).html().search(data) == -1) {
					$(e.target.parentNode).append(data);
				}
			}
		},
		error: function(jqXHR, textStatus, errorThrown) 
		{
			//if fails
			$(e.target.parentNode).html(data);
		}
	});
});
</script>