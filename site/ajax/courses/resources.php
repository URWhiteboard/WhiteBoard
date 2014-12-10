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
			// Get all resources for this section
			$query_sectionResources = $login->db_connection->prepare('SELECT * FROM sectionResources WHERE sectionID = :sectionID ORDER BY submit_time ASC');
				$query_sectionResources->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionResources->execute();
			
			if($query_sectionResources->rowCount() == 0) {
				echo "There are no resources for this section.";
			}

			$i = 0;
			// Loop through all of the sections resources
			while($resources = $query_sectionResources->fetchObject()) {
				echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
				echo "<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>";
				echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";
				echo $resources->name;
				echo "</div>";
				echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
				echo "Added ". date('D, F j \a\t g:i a', $resources->submit_time) ."";
				echo "</div>";
				echo "</div>";
				echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
				echo "Name: ". $resources->name ."<br />";

				// Check if the resource has a fileID, if so, show the link to it.
				if($resources->fileID!=null) {
					$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
					$query_file->bindValue(':fileID', $resources->fileID, PDO::PARAM_STR);
					$query_file->execute();
					$file = $query_file->fetchObject();

					echo "URL: <a href='../../users/download.php?id=". $file->fileID ."'>". $file->fileID .".". $file->extension ."</a><br>";
				}
				echo "Comment: ". $resources->comment ."<br />";

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
			// Get all resources for this section
			$query_sectionResources = $login->db_connection->prepare('SELECT * FROM sectionResources WHERE sectionID = :sectionID ORDER BY submit_time ASC');
				$query_sectionResources->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionResources->execute();

			$i = 0;
			// Loop through all of the sections resources
			while($resources = $query_sectionResources->fetchObject()) {
					echo "<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
					echo "<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>";
					echo "<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>";
					echo $resources->name;
					echo "</div>";
					echo "<div id='assignmentsAssignmentDue' class='assignmentsAssignmentDue'>";
					echo "Added ". date('D, F j \a\t g:i a', $resources->submit_time) ."";
					echo "</div>";
					echo "</div>";
					echo "<div id='assignmentsAssignmentBody' class='assignmentsAssignmentBody'>";
					echo "Name: ". $resources->name ."<br />";

					// Check if the resource has a fileID, if so, show the link to it.
					if($resources->fileID!=null) {
						$query_file = $login->db_connection->prepare('SELECT * FROM files WHERE fileID = :fileID');
						$query_file->bindValue(':fileID', $resources->fileID, PDO::PARAM_STR);
						$query_file->execute();
						$file = $query_file->fetchObject();

						echo "URL: <a href='../../users/download.php?id=". $file->fileID ."'>". $file->fileID .".". $file->extension ."</a><br>";
					}
					echo "Comment: ". $resources->comment ."<br />";

					echo "</div>";
					echo "</div>";
				}
			?>
			<div id='assignmentsAssignmentContainer' class='assignmentsAssignmentContainer'>
				<div id='assignmentsAssignmentHeader' class='assignmentsAssignmentHeader'>
					<div id='assignmentsAssignmentName' class='assignmentsAssignmentName'>
						New Resource
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
						<input id="file" type="file" name="file"/>
						<br />
						<br />
						<input id="section" type="hidden" name="section" value="<?php echo $_GET['s']; ?>"/>
						<input type="submit" name="submit" value="Add Resource" />
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
		url: '../../ajax/newResource.php',
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