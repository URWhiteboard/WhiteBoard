<?php
// include the config
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
require_once($_SERVER['DOCUMENT_ROOT'] .'/included/header.php');
?>
<link rel="stylesheet" type="text/css" href="../../included/css/courses.css">
<?php
if(isset($_GET['c']) || isset($_GET['s'])) {
	?>
	<div id="coursesNavBarContainer" class="coursesNavBarContainer">
		<a href="../../courses/" class="noUnderline">
		<div id="courseNavBarBack" class="courseNavBarButton">
		< Back
		</div>
		</a>
		<div id="courseNavBarInfo" class="courseNavBarButton">
			Info
		</div>
		<?php
		if(isset($_GET['s'])) {
			$query_sectionStudents = $login->db_connection->prepare('SELECT COUNT(*) FROM sectionStudents WHERE sectionID = :sectionID AND userID = :userID');
				$query_sectionStudents->bindValue(':sectionID', $_GET['s'], PDO::PARAM_STR);
				$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionStudents->execute();
			$enrolled = $query_sectionStudents->fetchColumn();
			if($enrolled == 1 || $login->getType()=="TEACHER") {
				?>
					<div id="courseNavBarAnnouncements" class="courseNavBarButton">
					Announcements
					</div>
					<div id="courseNavBarAssignments" class="courseNavBarButton">
					Assignments
					</div>
					<div id="courseNavBarGrades" class="courseNavBarButton">
					Grades
					</div>
					<div id="courseNavBarResources" class="courseNavBarButton">
					Resources
					</div>
					<div id="courseNavBarEnrollStatus" class="courseNavBarButtonRight" data-sID="<?php echo $_GET['s']; ?>" data-action="r" style="display:none;">
					Drop Section
					</div>
				<?php
			} else {
				?>
				<div id="courseNavBarEnrollStatus" class="courseNavBarButtonRight" data-sID="<?php echo $_GET['s']; ?>" data-action="a">
					Add Section
				</div>
				<?php
			}
		} else if(isset($_GET['c'])) {
			?>
			<div id="courseNavBarEnrollInfo" class="courseNavBarButtonRight" style="cursor:default;" data-cID="<?php echo $_GET['c']; ?>">
				Select a Section
			</div>
			<?php
		}
	?>

</div>
<div id="mainContentContainerContent" class="mainContentContainerContent" style="margin-top:30px;">
<?php
}
if ($login->databaseConnection()) {
	if(isset($_GET['c'])) {
		// if the user is trying to look up a course
		echo "Loading...";
	} elseif(isset($_GET['s'])) {
		echo "Loading...";
	} else {
		echo "<div id='mainContentContainerContent' class='mainContentContainerContent'>";
		// If the user is a teacher, show them the classes they teach
		if($login->getType() == "TEACHER"){
			// database query, get all of the sections the teacher teaches
			$query_sectionTeachers = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE userID = :userID ORDER BY sectionID ASC');
				$query_sectionTeachers->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionTeachers->execute();

			if($query_sectionTeachers->rowCount() == 0) {
				echo "You do not currently teach any sections";
			}
			$i = 0;
			// get result row as an object, so we can itenerate through the sections
			while($sectionTeachers = $query_sectionTeachers->fetchObject()) {
				// database query, get all the relevant information about the section
				$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
					$query_section->bindValue(':sectionID', $sectionTeachers->sectionID, PDO::PARAM_STR);
					$query_section->execute();
					$section = $query_section->fetchObject();
				// database query, get all of the relevant information about the course
				$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
					$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
					$query_course->execute();
					// get result row (as an object)
					$course = $query_course->fetchObject();

				// query the sectionTeachers to get the teacher of the section
				$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
					$query_sectionTeacher->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
					$query_sectionTeacher->execute();
					$sectionTeacher = $query_sectionTeacher->fetchObject();

				$query_sectionTeacherData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
					$query_sectionTeacherData->bindValue(':userID', $sectionTeacher->userID, PDO::PARAM_STR);
					$query_sectionTeacherData->execute();
					$sectionTeacherData = $query_sectionTeacherData->fetchObject();

				// Display all relevant course information
				// echo "<a href='../courses/?s=". $section->sectionID ."'>". $course->department ." ". $course->number ." - ". ucwords(strtolower($course->title)) ."</a><br />";
		  //   	echo "sectionID: ". $section->sectionID ."</a><br />";
		  //   	echo "courseID: ". $course->courseID ."<br />";
				// echo "Day: ". $section->day ."<br />";
				// echo "Time Start: ". $section->time_start ."<br />";
				// echo "Time End: ". $section->time_end ."<br />";
				// echo "Building: ". $section->building ."<br />";
				// echo "Room: ". $section->room ."<br />";
				// echo "Instructor: ". $section->instructor ."<br />";
				// echo "Term: ". $section->term ."<br />";
				// echo "Year: ". $section->year ."<br />";

				echo "<a href='../courses/?s=". $section->sectionID ."' class='noUnderline'>";
				echo "<div id='coursesCourseContainer' class='coursesCourseContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
				echo "<div id='coursesCourseTitle' class='coursesCourseTitle'>";
				echo $course->department ." ". $course->number ." - ". ucwords(strtolower($course->title));
				echo "</div>";
				echo "<div id='coursesCourseTeacher' class='coursesCourseTeacher'>";
				if($sectionTeacherData->name_first == "") {
					echo "No instructor assigned";
				} else {
					echo "". $sectionTeacherData->name_first ." ". $sectionTeacherData->name_last ."";
				}
				echo "</div>";
				echo "</div>";
				echo "</a>";
			}		
		} elseif($login->getType() == "STUDENT"){
			// database query, get all of the sections the user is enrolled in
			$query_sectionStudents = $login->db_connection->prepare('SELECT * FROM sectionStudents WHERE userID = :userID ORDER BY sectionID ASC');
				$query_sectionStudents->bindValue(':userID', $_SESSION['userID'], PDO::PARAM_STR);
				$query_sectionStudents->execute();
			
			if($query_sectionStudents->rowCount() == 0) {
				echo "You are not enrolled in any courses! Please use the search above to find courses.";
			}
			$i = 0;
			// get result row as an object, so we can itenerate through the sections
			while($sectionStudents = $query_sectionStudents->fetchObject()) {
				// database query, get all the relevant information about the section
				$query_section = $login->db_connection->prepare('SELECT * FROM sections WHERE sectionID = :sectionID');
					$query_section->bindValue(':sectionID', $sectionStudents->sectionID, PDO::PARAM_STR);
					$query_section->execute();
					$section = $query_section->fetchObject();
				// database query, get all of the relevant information about the course
				$query_course = $login->db_connection->prepare('SELECT * FROM courses WHERE courseID = :courseID');
					$query_course->bindValue(':courseID', $section->courseID, PDO::PARAM_STR);
					$query_course->execute();
					// get result row (as an object)
					$course = $query_course->fetchObject();
				// query the sectionTeachers to get the teacher of the section
				$query_sectionTeacher = $login->db_connection->prepare('SELECT * FROM sectionTeachers WHERE sectionID = :sectionID');
					$query_sectionTeacher->bindValue(':sectionID', $section->sectionID, PDO::PARAM_STR);
					$query_sectionTeacher->execute();
					$sectionTeacher = $query_sectionTeacher->fetchObject();

				$query_sectionTeacherData = $login->db_connection->prepare('SELECT name_first, name_last FROM users WHERE userID = :userID');
					$query_sectionTeacherData->bindValue(':userID', $sectionTeacher->userID, PDO::PARAM_STR);
					$query_sectionTeacherData->execute();
					$sectionTeacherData = $query_sectionTeacherData->fetchObject();

				// Display all relevant course information
				// echo "<a href='../courses/?s=". $section->sectionID ."'>". $course->department ." ". $course->number ." - ". $course->title ."</a><br />";
		  //   	echo "sectionID: ". $section->sectionID ."</a><br />";
		  //   	echo "courseID: ". $course->courseID ."<br />";
				// echo "Day: ". $section->day ."<br />";
				// echo "Time Start: ". $section->time_start ."<br />";
				// echo "Time End: ". $section->time_end ."<br />";
				// echo "Building: ". $section->building ."<br />";
				// echo "Room: ". $section->room ."<br />";
				// echo "Instructor: ". $sectionTeacherData->name_first . " ". $sectionTeacherData->name_last ."<br />";
				// echo "Term: ". $section->term ."<br />";
				// echo "Year: ". $section->year ."<br />";
				// echo "<br />";

				echo "<a href='../courses/?s=". $section->sectionID ."' class='noUnderline'>";
				echo "<div id='coursesCourseContainer' class='coursesCourseContainer' ". ((!$i++)? "style='border-top: solid 1px rgb(232,232,232);'" : "") ." >";
				echo "<div id='coursesCourseTitle' class='coursesCourseTitle'>";
				echo $course->department ." ". $course->number ." - ". ucwords(strtolower($course->title));
				echo "</div>";
				echo "<div id='coursesCourseTeacher' class='coursesCourseTeacher'>";
				if($sectionTeacherData->name_first == "") {
					echo "No instructor assigned";
				} else {
					echo "". $sectionTeacherData->name_first ." ". $sectionTeacherData->name_last ."";
				}
				echo "</div>";
				echo "</div>";
				echo "</a>";

			}
		}
	}
} else {
	// There was no database connection
	echo "There was an error with your request. Please try again later.";
}



// Include the footer page
include($_SERVER['DOCUMENT_ROOT'] .'/included/footer.php');
?>