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
//?>
// <link rel="stylesheet" type="text/css" href="../../included/css/resources.css">
//<?php
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
			echo "Student Resources";
			//get all resources for this section
			$query_sectionResources=$login->db_connection->prepare('SELECT * FROM sectionResources INNER JOIN resources ON sectionResources.resourceID=resources.resourceID WHERE sectionResources.sectionID=:sectionID ')
				$query_sectionResources->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionResources->execute();
			if($query_sectionResources->rowCount()==0){
				echo "There are no resources for this section"
			}
			$i=0;
			while($resource=$query_sectionResources->fetchObject()){
				echo "<div id='resourcesResourceContainer' class='resourcesResourceConainter' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : ") ."">";
				echo "<div id='resourcesResourceHeader' class='resourcesResourceHeader'>";
				echo "<div id='resourcesResourceName' class='resourcesResourceName'>";
				echo $resource->name;
				echo "</div>"
				echo "<div id='resourcesResourceFile' class='resourcesResourceFile'>";
				echo "URL: <a href='../../users/resources/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
				echo "</div>";
				if($submission->comment==""){}
				else{
					echo "Comment: ";
					echo $submission->comment;
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
		if($enrolled==1) {
			// Enrolled, show page
			//get all resources for this section
			$query_sectionResources=$login->db_connection->prepare('SELECT * FROM sectionResources INNER JOIN resources ON sectionResources.resourceID=resources.resourceID WHERE sectionResources.sectionID=:sectionID ')
				$query_sectionResources->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionResources->execute();
			if($query_sectionResources->rowCount()==0){
				echo "There are no resources for this section"
			}
			$i=0;
			while($resource=$query_sectionResources->fetchObject()){
				echo "<div id='resourcesResourceContainer' class='resourcesResourceConainter' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : ") ."">";
				echo "<div id='resourcesResourceHeader' class='resourcesResourceHeader'>";
				echo "<div id='resourcesResourceName' class='resourcesResourceName'>";
				echo $resource->name;
				echo "</div>"
				echo "<div id='resourcesResourceFile' class='resourcesResourceFile'>";
				echo "URL: <a href='../../users/resources/". $file->fileID .".". $file->extension ."'>". $file->fileID .".". $file->extension ."</a><br>";
				echo "</div>";
				if($submission->comment==""){}
				else{
					echo "Comment: ";
					echo $submission->comment;
				}
				echo "</div>";
				echo "</div>";
			}
			?>
			<div id='resourcesResourceContainer' class='resourcesResourceCountiner'>
				<div id='resourcesResourceHeader' class='resourcesResourceHeader'>
					<div id='resourcesResourceName' class='resourcesResourceName'>
						New Resource
					</div>
				</div>
				<div id='resourcesNewResourceBody' class='resourcesNewResourceBody'>
					<form method="post" id="newResource" name="newResource">
						<input id="name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="name" placeholder="name" required />
						<br />
						<textarea id="comment" type="textarea" name="comment" rows="4" cols="50" placeholder="Comment"> </textarea>
						<br />
						<br />
						<label for="file"> File: </label>
						<input id="file" type="text" name="file" />
						<br />
						<input type="submit" value"Create Resource">
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
$('#newResource').on('submit', function(e) {
	e.preventDefault();
	var postData=$(this).serializeArray();
	var error=null;
	if(postData[0].value=="") {
		error ="name";
		$('#name').addClass('error');
	} else {
		$('#name').removeClass('error');
	}
	if(postData[1].value==""){
		error="comment";
		$('#comment').addClass('error');
	} else {
		$('#comment').removeClass('error');
	}
	var formURL='../../ajax/courses/newResource.php';
	if(error==null){
		fd=postData;
		var sectionID={name:"sectionID", value "<?php echo $_GET['s']; ?>"};
		fd.push(sectionID);
		$.ajax(
		{
			url: formURL,
			type:"POST",
			data: fd,
			success: function(data, textStatus, jqXHR)
			{
				$('#mainContentContainerContent').html(data);
				setTimout(funtion(){window.location.reload(true)}, 1000);
			},
			error:funtion(jqXHR, textStatus, errorThrown)
			{
				//if fails
				$('mainContentContainerContent').htm(data);
			}
		})
	}
	
})
