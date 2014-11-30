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
		if($enrolled==1) {
			// Enrolled, show page
			// Get all assignments for this section
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments WHERE sectionID = :sectionID');
				$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAssignments->execute();

			if($query_sectionAssignments->rowCount() == 0) {
				echo "There are no assignments for this section!";
			}
			// get result row as an object, so we can itenerate through the sections
			while($sectionAssignment = $query_sectionAssignments->fetchObject()) {
				$query_assignment = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID ORDER BY due_time ASC');
					$query_assignment->bindValue(':assignmentID', $sectionAssignment->assignmentID, PDO::PARAM_STR);
					$query_assignment->execute();
					$assignment = $query_assignment->fetchObject();
					echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer'>";
					echo "<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>";
					echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";
					echo $assignment->name;
					echo "</div>";
					echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
					echo "Due ". date('D, F j \a\t g:i a', $assignment->due_time) ."";
					echo "</div>";
					echo "<div id='assignmentsAssignmentGrade' class='assignmentsAssignmentGrade'>";
					// Need to check to see if they have a grade for the assignment
					echo "&ndash; / ". $assignment->maxScore ."";
					echo "</div>";
					echo "</div>";
					echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
					// Check the database for submissions
					echo "<h3>Submissions</h3>";
					$query_submissions = $login->db_connection->prepare('SELECT * FROM submissions WHERE userID = :userID AND assignmentID = :assignmentID ORDER BY submit_time ASC');
						$query_submissions->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
						$query_submissions->bindValue(':assignmentID', $sectionAssignment->assignmentID, PDO::PARAM_STR);
						$query_submissions->execute();
					if($query_submissions->rowCount() == 0) {
						echo "You have not submitted anything for this assignment.<br>";
					}
					// loop through all of the submissions
					while($submission = $query_submissions->fetchObject()) {
						$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
						$query_file->bindValue(':fileID', $submission->fileID, PDO::PARAM_STR);
						$query_file->execute();
						$file = $query_file->fetchObject();
						// When file is uploaded, it should change to the id to find the file, otherwise collisions will happen
						echo "URL: <a href='../../users/submissions/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
						echo "Title: ". $file->title ."<br>";
						echo "Comment: ". $submission->comment ."<br>";
						echo "Submitted at: ". date('D, F j \a\t g:i a', $submission->submit_time) ."<br><br>";
					}
					if ($assignment->due_time < time()) {
						echo "Sorry, the due date has passed. You cannot add a new submission.";
					} else {
						echo "<h3>Add new submission</h3>";
						?>
						<div id="newSubmissionContainer" class="newSubmissionContainer" enctype="multipart/form-data">
							<form method="post" action="/users/" name="submitAssignment" id="submitAssignment" class="submitAssignment">
							<label for="title"><?php echo "Title (only letters and numbers, 2 to 64 characters)"; ?></label>
							<input id="title" type="text" pattern="[a-zA-Z0-9]{2,64}" name="title"/>
							<label for="comment">Comment</label>
							<textarea id="comment" type="textarea" name="comment" rows="4" cols="50"></textarea>
							<br />
							<label for="file">File</label>
							<input id="file" type="file" name="file"/>
							<br />
							<input id="assignment" type="hidden" name="assignment" value="<?php echo $sectionAssignment->assignmentID; ?>"/>
							<input type="submit" name="submit" value="Submit" />
							</form>
						</div>
						<?php
					}
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
		if($enrolled==1) {
			// Enrolled, show page
			// Get all assignments for this section
			$query_sectionAssignments = $login->db_connection->prepare('SELECT * FROM sectionAssignments WHERE sectionID = :sectionID');
				$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionAssignments->execute();

			if($query_sectionAssignments->rowCount() == 0) {
				echo "There are no assignments for this section!";
			}
			while($sectionAssignment = $query_sectionAssignments->fetchObject()) {
				$query_assignment = $login->db_connection->prepare('SELECT * FROM assignments WHERE assignmentID = :assignmentID');
					$query_assignment->bindValue(':assignmentID', $sectionAssignment->assignmentID, PDO::PARAM_STR);
					$query_assignment->execute();
					$assignment = $query_assignment->fetchObject();
				echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer'>";
				echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";
				echo $assignment->name;
				echo "</div>";
				echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
				echo "". date('D, F j \a\t g:i a', $assignment->due_time) ."";
				echo "</div>";
				echo "<div id='assignmentsAssignmentGrade' class='assignmentsAssignmentGrade'>";
				// Need to check to see if they have a grade for the assignment
				echo "&ndash; / ". $assignment->maxScore ."";
				echo "</div>";
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
			}
			echo "<h4>Create a new assignment!</h4>";
			?>
			<form method="post" id="newAssignment" name="newAssignment">
				<label for="name">Assignment Name (only letters and numbers, 2 to 64 characters): </label>
				<input id="name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="name" required />

				<label for="category">Category: </label>
				<select id="category" name="category" required>
					<option disabled="" selected=""></option>
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

				<label for="maxScore">Max Score: </label>
				<input id="maxScore" type="text" name="maxScore" required />

				<label for="curve_type">Curve Type: </label>
				<!-- Need to add no curve -->
				<select id="curve_type" name="curve_type" >
					<option value="" selected=""></option>
					<option value="ADD_PERCENT">Add percent</option>
					<option value="ADD_CONSTANT">Add constant</option>
					<option value="REDUCE_MAX">Reduce Max</option>
				</select>

				<label for="curve_param">Curve Value: </label>
				<input id="curve_param" type="text" name="curve_param"/>

				<label for="datetimepicker">Time Due: </label><br />
				<input type="text" id="datetimepicker" name="datetimepicker"/><br />

				<label for="show_letter">Show Letter: </label>
				<select id="show_letter" name="show_letter" required>
				<option disabled="" selected=""></option>
				<option value="1">Yes</option>
				<option value="0">No</option>
				</select>
				
				<label for="comment"><?php echo "Comment"; ?></label>
				<input id="comment" type="text" name="comment" required/>
				
				<label for="late_policy">Late Policy: </label>
				<select id="late_policy" name="late_policy" required>
				<option disabled="" selected=""></option>
				<?php
				$query_latePolicy = $login->db_connection->prepare('SELECT * FROM latePolicies WHERE creatorID = :creatorID ');
				$query_latePolicy->bindValue(':creatorID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_latePolicy->execute();
				// get result row as an object, so we can itenerate through the sections
				while($latePolicies = $query_latePolicy->fetchObject()) {
					echo "<option value=\"". $latePolicies->latePolicyID ."\">". $latePolicies->latePolicyID ."</option>";
				}
				?>
				</select>
				<label for="file">File: </label>
				<input id="file" type="text" name="file" />
				
				<input type="submit" value="Create Assignment" />
			</form>

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
	} else if(postData[1].value == "") {
		error = "category";
	} else if(postData[2].value == "") {
		error = "maxScore";
	} else if(postData[5].value == "") {
		error = "due_time";
	} else if(postData[6].value == "") {
		error = "show_letter";
	} else if(postData[7].value == "") {
		error = "comment";
	} else if(postData[8].value == "") {
		error = "late_policy";
	}
	console.log(postData);
	var formURL = '../../ajax/courses/newAssignment.php';
	if(error == null) {
		$.ajax(
		{
			url : formURL,
			type: "POST",
			data : postData,
			success:function(data, textStatus, jqXHR) 
			{
				//data: return data from server
				$('#mainContentContainerContent').html(data);
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				//if fails
				$('#mainContentContainerContent').html(data);
			}
		})
	} else {
		console.log(postData);
		console.log('error '+ error);
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
	yearEnd: 2015
});
// Expand the assignments div
$('.assignmentsAssignmentHeader').click(function(e) {
	$(this.parentNode).toggleClass('assignmentsAssignmentContainer');
	$(this.parentNode).toggleClass('assignmentsAssignmentContainerExpanded');
});
$(".submitAssignment").on('submit', function( e ) {
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
		},
		error: function(jqXHR, textStatus, errorThrown) 
		{
			//if fails
			$(e.target.parentNode).html(data);
		}
	});
});
</script>