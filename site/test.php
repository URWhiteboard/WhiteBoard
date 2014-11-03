<?php
define("DB_HOST", "localhost");
define("DB_NAME", "mydb");
define("DB_NAME_OLD", "login");
define("DB_USER", "root");
define("DB_PASS", "root");
// define("DB_NAME_MYDB", "mydb");

$db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME_OLD . ';charset=utf8', DB_USER, DB_PASS);

$query_section = $db_connection->prepare('SELECT * FROM sections');
$query_section->execute();
// get result row (as an object)

while($section = $query_section->fetchObject()) {
	
	$subject = explode(" ", $section->subject);

	$db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);

	$query_new_user_insert = $db_connection->prepare('INSERT INTO sectionGradingPolicies (letterScaleID, isWeighted) VALUES(:letterScaleID, :isWeighted)') or die(mysqli_error($db_connection_insert));
		$query_new_user_insert->bindValue(':letterScaleID', 1, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':isWeighted', 0, PDO::PARAM_STR);
		$query_new_user_insert->execute();
		$sectionGradingPolicyID = $db_connection->lastInsertId();

	$query_new_user_insert = $db_connection->prepare('INSERT INTO courses (school, department, number, type, title, description, credits, requirements, clusters, prerequisites, cross_listed) VALUES(:school, :department, :number, :type, :title, :description, :credits, :requirements, :clusters, :prerequisites, :cross_listed)') or die(mysqli_error($db_connection_insert));
		$query_new_user_insert->bindValue(':school', $section->school, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':department', $subject[0], PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':number', $subject[1], PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':type', MAIN_COURSE, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':title', $section->courseTitle, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':description', $section->description, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':credits', $section->credits, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':requirements', $section->requirements, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':clusters', $section->clusters, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':prerequisites', $section->prerequisites, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':cross_listed', $section->crossListed, PDO::PARAM_STR);
		$query_new_user_insert->execute();
		$courseID = $db_connection->lastInsertId();

	$query_new_user_insert = $db_connection->prepare('INSERT INTO sections (CRN, day, time_start, time_end, building, room, enroll, enrollCap, info, term, year, status, url, courseID, sectionGradingPolicyID) VALUES(:CRN, :day, :time_start, :time_end, :building, :room, :enroll, :enrollCap, :info, :term, :year, :status, :url, :courseID, :sectionGradingPolicyID)') or die(mysqli_error($db_connection_insert));
		$query_new_user_insert->bindValue(':CRN', $section->CRN, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':day', $section->day, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':time_start', $section->timeStart, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':time_end', $section->timeEnd, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':building', $section->building, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':room', $section->room, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':enroll', $section->sectionEnroll, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':enrollCap', $section->sectionCap, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':info', $section->courseInfo, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':term', SPRING, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':year', 2015, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':status', OPEN, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':url', $section->url, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':courseID', $courseID, PDO::PARAM_STR);
		$query_new_user_insert->bindValue(':sectionGradingPolicyID', $sectionGradingPolicyID, PDO::PARAM_STR);
		$query_new_user_insert->execute();

}
?>