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
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments INNER JOIN assignments ON sectionAssignments.assignmentID = assignments.assignmentID WHERE sectionAssignments.sectionID = :sectionID ORDER BY due_time ASC');
				$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAssignments->execute();
			if($query_sectionAssignments->rowCount() == 0) {
				echo "There are no assignments for this section!";
			}
			$i = 0;
			// get result row as an object, so we can itenerate through the sections
			while($assignment = $query_sectionAssignments->fetchObject()) {
				// Set default to submittable
				$submittable = true;
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
						$submittable = false;
					// Late policy based on hours
					} else if($latePolicy->period == "HOUR") {
						$hoursLate = ceil(abs($timeRemaining)/3600);
						// Late policy based on percent/day
						if($latePolicy->is_percent) {
							$zeroCreditHours = 100/($latePolicy->rate*$hoursLate);
							// Zero credit should be given
							if($hoursLate >= $zeroCreditHours) {
								$submittable = false;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$hoursLate ."% on the final grade.";
						} else {
							$zeroCreditHours = $assignment->maxScore-($latePolicy->rate*$hoursLate);
							// Zero credit should be given
							if($zeroCreditHours <= 0) {
								$submittable = false;
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
								$submittable = false;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$daysLate ."% on the final grade.";
						// Late policy based on points/day
						} else {
							$zeroCreditDays = $assignment->maxScore-($latePolicy->rate*$daysLate);
							// Zero credit should be given
							if($zeroCreditDays <= 0) {
								$submittable = false;
							}
							$valueLost = "you will lose ". $latePolicy->rate*$daysLate ." points on the final grade.";
						}
					}
				}
				if($submittable) {
					// Get all submissions for the assignment (This needs to be here so we can correctly display a check mark)
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID ORDER BY submit_time ASC');
						$query_submissions->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();

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
					if($query_submissions->rowCount() > 0) {
						echo "&#10003";
					}
					echo "</div>";
					echo "</div>";
					echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
					echo "<h3>Submissions</h3>";
					
					if($query_submissions->rowCount() == 0) {
						echo "You have not submitted anything for this assignment.<br>";
					}
					// Used to keep track of the attempt
					$j = 0;
					// loop through all of the submissions
					while($submission = $query_submissions->fetchObject()) {
						$j++;
						$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
						$query_file->bindValue(':fileID', $submission->fileID, PDO::PARAM_STR);
						$query_file->execute();
						$file = $query_file->fetchObject();
						echo "<div id='assignmentsAssignmentSubmissionContainer' class='assignmentsAssignmentSubmissionContainer'>";
						echo "Attempt ". $j ."<br>";
						// When file is uploaded, it should change to the id to find the file, otherwise collisions will happen
						echo "URL: <a href='../../users/download.php?id=". $file->fileID ."'>". $file->fileID .".". $file->extension ."</a><br>";
						echo "Title: ". $file->title ."<br>";
						echo "Comment: ". $submission->comment ."<br>";
						echo "Submitted at: ". date('D, F j \a\t g:i a', $submission->submit_time) ."<br>";
						echo "</div>";
					}
					echo "<h3>Add new submission</h3>";

					?>
					<div id="newSubmissionContainer" class="newSubmissionContainer" enctype="multipart/form-data">
						<form method="post" action="/users/" name="submitSubmission" id="submitSubmission" class="submitSubmission">
						<label for="comment">Comment</label>
						<br />
						<textarea id="comment" type="textarea" name="comment" rows="4" cols="50"></textarea>
						<br />
						<br />
						<input id="file" type="file" name="file"/>
						<br />
						<br />
						<input id="assignment" type="hidden" name="assignment" value="<?php echo $assignment->assignmentID; ?>"/>
						<?php
						if($valueLost != NULL) {
							echo "Note, ". $valueLost ."<br /><br />";
						}
						?>
						<input type="submit" name="submit" value="Submit" />
						<br />
						<br />
						</form>
					</div>
					<?php
					// echo "Curve : ";
					// if($assignment->curveType == "ADD_PERCENT") { 
					// 	echo $assignment->curveParam ."%";
					// } elseif($assignment->curveType == "ADD_CONSTANT") { 
					// 	echo $assignment->curveParam ." points";
					// } elseif($assignment->curveParam == "REDUCE_MAX") {
					// 	echo "Graded out of ". $assignment->maxScore - $assignment->curveParam ."";
					// } else {
					// 	echo "None" ."";
					// }
					// echo "Category: ". $assignment->category ."";
					// echo "Comment: ". $assignment->comment ."";
					echo "</div>";
					echo "</div>";
				}
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
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments INNER JOIN assignments ON sectionAssignments.assignmentID = assignments.assignmentID WHERE sectionAssignments.sectionID = :sectionID ORDER BY due_time ASC');
				$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAssignments->execute();
			if($query_sectionAssignments->rowCount() == 0) {
				echo "There are no assignments for this section!";
			}

			$i = 0;
			// get result row as an object, so we can itenerate through the sections
			while($assignment = $query_sectionAssignments->fetchObject()) {
				// Set gradeable to false
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
				if(!$gradeable) {
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
					
					// Allow the professor to start grading the assignments now that the due date has passed
					echo "<h3>Student Submissions</h3>";
					// a query below that will show the most recent submission from every user
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE submit_time In(SELECT MAX(submit_time) FROM submissions WHERE assignmentID = :assignmentID GROUP BY userID)');
						$query_submissions->bindValue(':assignmentID', $assignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();
					if($query_submissions->rowCount() == 0) {
						echo "There are no submissions for this assignment yet.<br>";
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

						// Get the submitters first and last name
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
						echo "</div>";
					}
					echo "</div>";
					echo "</div>";
				}
			}
			?>
			<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer'>
				<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>
					<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>
						New Assignment
					</div>
					<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>
					&nbsp;
					</div>
				</div>
				<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>
					<form method="post" id="newAssignment" name="newAssignment">
						<input id="name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="name" placeholder="Name" required />

						<label for="category">Category: </label>
						<select id="category" name="category" required>
							<option selected=""></option>
							<option value="TEST">Test</option>
							<option value="LAB">Lab</option>
							<option value="QUIZ">Quiz</option>
							<option value="MINIQUIZ">Mini Quiz</option>
							<option value="REPORT">Report</option>
							<option value="ESSAY">Essay</option>
							<option value="HOMEWORK">Homework</option>
							<option value="PARTICIPATION">Participation</option>
							<option value="MIDTERM">Midterm</option>
							<option value="FINAL">Final</option>
							<option value="OTHER">Other</option>
						</select>

						<input id="maxScore" type="text" name="maxScore" placeholder="Max Score" required />

						<label for="curve_type">Curve Type: </label>
						<!-- Need to add no curve -->
						<select id="curve_type" name="curve_type" >
							<option value="" selected="">None</option>
							<option value="ADD_PERCENT">Add percent</option>
							<option value="ADD_CONSTANT">Add constant</option>
							<option value="REDUCE_MAX">Reduce Max</option>
						</select>

						<input id="curve_param" type="text" name="curve_param" placeholder="Curve Value"/>

						<label for="datetimepicker">Time Due: </label><br />
						<input type="text" id="datetimepicker" name="datetimepicker"/><br />

						<label for="show_letter">Show Letter: </label>
						<select id="show_letter" name="show_letter" required>
							<option selected="" value="0">No</option>
							<option value="1">Yes</option>
						</select>
						<br />
						<textarea id="comment" type="textarea" name="comment" rows="4" cols="50" placeholder="Comment"></textarea>
						<br />
						<br />
						<label for="late_policy">Late Policy: </label>
						<select id="late_policy" name="late_policy" required>
							<option disabled="" selected=""></option>
							<?php
							$query_latePolicy = $login->db_connection->prepare('SELECT * FROM latePolicies WHERE creatorID = :creatorID ');
							$query_latePolicy->bindValue(':creatorID', $_SESSION['userID'], PDO::PARAM_STR);
							$query_latePolicy->execute();
							// get result row as an object, so we can itenerate through the sections
							while($latePolicies = $query_latePolicy->fetchObject()) {
								echo "<option value=\"". $latePolicies->latePolicyID ."\">". $latePolicies->title ."</option>";
							}
							?>
							</select>
						<label for="file">File: </label>
						<input id="file" type="text" name="file" />
						<br />
						<input type="submit" value="Create Assignment" />
						<br />
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
$('#newAssignment').on('submit', function(e) {
	e.preventDefault();
	var postData = $(this).serializeArray();
	var error = null;
	if(postData[0].value == "") {
		error = "name";
		$('#name').addClass('error');
	} else {
		$('#name').removeClass('error');
	}
	if(postData[1].value == "") {
		error = "categoy";
		$('#category').addClass('error');
	} else {
		$('#category').removeClass('error');
	}
	if(postData[2].value == "") {
		error = "maxScore";
		$('#maxScore').addClass('error');
	} else {
		$('#maxScore').removeClass('error');
	}
	if(postData[5].value == "") {
		error = "due_time";
		$('#due_time').addClass('error');
	} else {
		$('#due_time').removeClass('error');
	}
	if(postData[6].value == "") {
		error = "show_letter";
		$('#show_letter').addClass('error');
	} else {
		$('#show_letter').removeClass('error');
	}
	if(postData[7].value == "") {
		error = "comment";
		$('#comment').addClass('error');
	} else {
		$('#comment').removeClass('error');
	}
	if(postData[8].value == "") {
		error = "late_policy";
		$('#late_policy').addClass('error');
	} else {
		$('#late_policy').removeClass('error');
	}
	var formURL = '../../ajax/courses/newAssignment.php';
	if(error == null) {
		fd = postData;
		var sectionID = {name:"sectionID", value:"<?php echo $_GET['s']; ?>"};
		fd.push(sectionID);
		$.ajax(
		{
			url : formURL,
			type: "POST",
			data : fd,
			success:function(data, textStatus, jqXHR) 
			{
				//data: return data from server
				$('#mainContentContainerContent').html(data);
				setTimeout(function(){window.location.reload(true)}, 1000);
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				//if fails
				$('#mainContentContainerContent').html(data);
			}
		})
	}
});
$('#datetimepicker').datetimepicker({
	inline:true,
	formatTime:'g:i a',
	format:'unixtime',
	formatDate:'m/d/Y',
	minDate:'-01/01/1970',
	maxDate:'+01/01/1971',
	yearStart: 2014,
	yearEnd: 2015,
	id:'due_time'
});
// Expand the assignments div
$('.assignmentsAssignmentHeader').click(function(e) {
	$(this.parentNode).toggleClass('assignmentsAssignmentContainer');
	$(this.parentNode).toggleClass('assignmentsAssignmentContainerExpanded');
});
$(".submitSubmission").on('submit', function( e ) {
	e.preventDefault();
	var f = e.target;
    var fd = new FormData(f);
	$.ajax({
		url: '../../ajax/uploadSubmission.php',
		type: 'POST',
		data: fd,
		processData: false,
		contentType: false,
		success:function(data, textStatus, jqXHR) 
		{
			//data: return data from server
			$(e.target.parentNode).html(data);
			setTimeout(function(){window.location.reload(true)}, 1000);
		},
		error: function(jqXHR, textStatus, errorThrown) 
		{
			//if fails
			$(e.target.parentNode).html(data);
		}
	});
});
</script>