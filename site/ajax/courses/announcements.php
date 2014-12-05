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
			echo "Student Announcements";
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
			?>
			<link rel="stylesheet" type="text/css" href="../../included/css/announcements.css">
			<div class="announceNav">
				<div class='announceNavButton' id="newAnnouncement">+ Announcement</div>
			</div>
			<div class="announceContainer">
				<div class="newAnnouncement" style="display: none">
					<table> <!-- only way to do this, I swear. -->
					<tr class="announceInput"><td>Title</td><td><input></td></tr>
					<tr class="announceInput"><td>Content</td><td><input></td></tr>
					<submit>
					</table>
<?php
// on sumbit, connect to database and send using framework from addignments submission page
?>
				</div>
				<div class="pastAnnouncements">
<?php
// Get past assignments (copied from grades.php)
//$query_sectionAnnouncements = $login->db_connection->prepare('SELECT * FROM assignments INNER JOIN assignments ON sectionAssignments.assignmentID = assignments.assignmentID WHERE sectionAssignments.sectionID = :sectionID ORDER BY due_time DESC');
//	$query_sectionAssignments->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
//	$query_sectionAssignments->execute();

// get result row as an object, so we can itenerate through the sections
//while($assignment = $query_sectionAssignments->fetchObject()) {
//	$query_sectionAnnouncements = $login->db_connection->prepare('SELECT * FROM announcements WHERE date_create=*');
	// title
//	$query_title->bindValue(':latePolicyID', $assignment->latePolicyID, PDO::PARAM_STR);
	// body
//	$query_latePolicies->execute();
//	$latePolicy = $query_latePolicies->fetchObject();
//}
?>
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
// Expand the announcements div
$('#newAnnouncement').click(function(e) {
	$('.newAnnouncement').toggle();
	$('.pastAnnouncements').toggle();
});
</script>