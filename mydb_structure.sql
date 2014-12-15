-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 14, 2014 at 10:17 PM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE IF NOT EXISTS `announcements` (
  `announcementID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `type` enum('GRADE','ASSIGNMENT','RESOURCE','ANNOUNCEMENT') NOT NULL,
  `typeID` int(10) unsigned DEFAULT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `isRead` tinyint(1) NOT NULL DEFAULT '0',
  `title` text,
  `comment` text,
  PRIMARY KEY (`announcementID`),
  KEY `sectionID` (`sectionID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE IF NOT EXISTS `assignments` (
  `assignmentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `category` enum('TEST','LAB','QUIZ','MINIQUIZ','REPORT','ESSAY','HOMEWORK','PARTICIPATION','MIDTERM','FINAL','OTHER') NOT NULL,
  `maxScore` int(6) DEFAULT NULL,
  `creatorID` int(10) unsigned NOT NULL COMMENT 'The creator',
  `curveType` enum('ADD_PERCENT','ADD_CONSTANT','REDUCE_MAX') DEFAULT NULL,
  `curveParam` tinyint(4) DEFAULT NULL,
  `due_time` int(10) unsigned NOT NULL,
  `show_letter` tinyint(1) NOT NULL,
  `comment` text,
  `latePolicyID` int(10) unsigned NOT NULL,
  `fileID` int(10) unsigned DEFAULT NULL,
  `gradeVisible` tinyint(1) NOT NULL DEFAULT '0',
  `submittable` tinyint(1) NOT NULL,
  PRIMARY KEY (`assignmentID`,`creatorID`,`latePolicyID`),
  KEY `fk_assignments_users1_idx` (`creatorID`),
  KEY `fk_assignments_latePolicies1_idx` (`latePolicyID`),
  KEY `fileID` (`fileID`),
  KEY `fileID_2` (`fileID`),
  KEY `assignmentID` (`assignmentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE IF NOT EXISTS `courses` (
  `courseID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school` varchar(64) NOT NULL,
  `department` char(3) NOT NULL,
  `number` smallint(3) unsigned NOT NULL,
  `type` enum('MAIN_COURSE','LAB','RECITATION','WORKSHOP') NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `credits` decimal(2,1) unsigned NOT NULL,
  `requirements` text,
  `clusters` text COMMENT 'This should be removed and expanded into a whole tables, but for now we''re not dealing with clusters and just leaving them as text.',
  `prerequisites` text,
  `cross_listed` text,
  PRIMARY KEY (`courseID`),
  UNIQUE KEY `courseID_UNIQUE` (`courseID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2264 ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `fileID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(10) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `upload_time` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fileID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE IF NOT EXISTS `grades` (
  `gradeID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `real_score` text NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `assignmentID` int(10) unsigned NOT NULL,
  `graderID` int(10) unsigned NOT NULL,
  `effective_score` text,
  `comment` text,
  PRIMARY KEY (`gradeID`,`sectionID`,`userID`,`assignmentID`,`graderID`),
  KEY `fk_grades_sections1_idx` (`sectionID`),
  KEY `fk_grades_users1_idx` (`userID`),
  KEY `fk_grades_assignments1_idx` (`assignmentID`),
  KEY `fk_grades_users2_idx` (`graderID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `latePolicies`
--

CREATE TABLE IF NOT EXISTS `latePolicies` (
  `latePolicyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `rate` smallint(5) unsigned DEFAULT NULL,
  `period` enum('DAY','HOUR','NONE') NOT NULL,
  `is_percent` tinyint(1) DEFAULT NULL,
  `creatorID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`latePolicyID`,`creatorID`),
  KEY `fk_latePolicies_users1_idx` (`creatorID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `letterScales`
--

CREATE TABLE IF NOT EXISTS `letterScales` (
  `letterScaleID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `a` decimal(2,0) unsigned DEFAULT NULL,
  `am` decimal(2,0) unsigned DEFAULT NULL,
  `bp` decimal(2,0) unsigned DEFAULT NULL,
  `b` decimal(2,0) unsigned DEFAULT NULL,
  `bm` decimal(2,0) unsigned DEFAULT NULL,
  `cp` decimal(2,0) unsigned DEFAULT NULL,
  `c` decimal(2,0) unsigned DEFAULT NULL,
  `cm` decimal(2,0) unsigned DEFAULT NULL,
  `dp` decimal(2,0) unsigned DEFAULT NULL,
  `d` decimal(2,0) unsigned NOT NULL,
  `dm` decimal(2,0) unsigned DEFAULT NULL,
  PRIMARY KEY (`letterScaleID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionAssignments`
--

CREATE TABLE IF NOT EXISTS `sectionAssignments` (
  `sectionAssignmentsID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sectionID` int(10) unsigned NOT NULL,
  `assignmentID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sectionAssignmentsID`,`sectionID`,`assignmentID`),
  KEY `fk_sectionAssignments_sections1_idx` (`sectionID`),
  KEY `fk_sectionAssignments_assignments1_idx` (`assignmentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionGrades`
--

CREATE TABLE IF NOT EXISTS `sectionGrades` (
  `sectionGradeID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sectionID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `grade` text,
  `finalized` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sectionGradeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionGradingPolicies`
--

CREATE TABLE IF NOT EXISTS `sectionGradingPolicies` (
  `sectionGradingPolicyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `letterScaleID` int(10) unsigned NOT NULL,
  `isWeighted` tinyint(1) unsigned NOT NULL,
  `quiz_weight` decimal(2,0) unsigned DEFAULT NULL,
  `test_weight` decimal(2,0) unsigned DEFAULT NULL,
  `homework_weight` decimal(2,0) unsigned DEFAULT NULL,
  `participation_weight` decimal(2,0) unsigned DEFAULT NULL,
  `miniquiz_weight` decimal(2,0) unsigned DEFAULT NULL,
  `report_weight` decimal(2,0) unsigned DEFAULT NULL,
  `lab_weight` decimal(2,0) unsigned DEFAULT NULL,
  `essay_weight` decimal(2,0) unsigned DEFAULT NULL,
  `midterm_weight` decimal(2,0) unsigned DEFAULT NULL,
  `final_weight` decimal(2,0) unsigned DEFAULT NULL,
  `other_weight` decimal(2,0) unsigned DEFAULT NULL,
  PRIMARY KEY (`sectionGradingPolicyID`,`letterScaleID`),
  KEY `fk_sectionGradingPolicies_letterScales1_idx` (`letterScaleID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2267 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionResources`
--

CREATE TABLE IF NOT EXISTS `sectionResources` (
  `resourceID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submit_time` int(10) unsigned NOT NULL,
  `comment` text,
  `name` text NOT NULL,
  `fileID` int(10) unsigned DEFAULT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`resourceID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `sectionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CRN` int(10) unsigned NOT NULL,
  `day` varchar(7) NOT NULL,
  `time_start` int(4) unsigned NOT NULL,
  `time_end` int(4) unsigned NOT NULL,
  `building` varchar(64) NOT NULL,
  `room` varchar(64) NOT NULL,
  `enroll` int(10) unsigned DEFAULT NULL,
  `enrollCap` int(10) unsigned DEFAULT NULL,
  `info` text,
  `term` enum('SUMMER','WINTER','FALL','SPRING') NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `status` enum('OPEN','CLOSED') NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `courseID` int(10) unsigned NOT NULL,
  `sectionGradingPolicyID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sectionID`,`courseID`,`sectionGradingPolicyID`),
  UNIQUE KEY `sectionID_2` (`sectionID`),
  KEY `fk_sections_courses1_idx` (`courseID`),
  KEY `fk_sections_sectionGradingPolicies1_idx` (`sectionGradingPolicyID`),
  KEY `sectionID` (`sectionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2242 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionStudents`
--

CREATE TABLE IF NOT EXISTS `sectionStudents` (
  `sectionStudentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  `is_pass_fail` tinyint(1) NOT NULL,
  `is_satisfactory_fail` tinyint(1) NOT NULL,
  `is_no_credit` tinyint(1) NOT NULL,
  PRIMARY KEY (`sectionStudentID`,`userID`,`sectionID`),
  KEY `fk_sectionStudents_users1_idx` (`userID`),
  KEY `fk_sectionStudents_sections1_idx` (`sectionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=80 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionTAs`
--

CREATE TABLE IF NOT EXISTS `sectionTAs` (
  `sectionTAID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sectionTAID`,`userID`,`sectionID`),
  KEY `fk_sectionTAs_users1_idx` (`userID`),
  KEY `fk_sectionTAs_sections1_idx` (`sectionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionTeachers`
--

CREATE TABLE IF NOT EXISTS `sectionTeachers` (
  `sectionTeacherID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sectionTeacherID`,`userID`,`sectionID`),
  KEY `fk_sectionTeachers_users1_idx` (`userID`),
  KEY `fk_sectionTeachers_sections1_idx` (`sectionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE IF NOT EXISTS `submissions` (
  `submissionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submit_time` int(10) unsigned NOT NULL,
  `comment` text,
  `assignmentID` int(10) unsigned NOT NULL,
  `fileID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`submissionID`,`assignmentID`,`fileID`,`userID`),
  KEY `fk_submissions_assignments1_idx` (`assignmentID`),
  KEY `fk_submissions_files1_idx` (`fileID`),
  KEY `fk_submissions_users1_idx` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('STUDENT','TEACHER','ADMIN') NOT NULL,
  `username` varchar(64) NOT NULL,
  `password_hash` char(255) NOT NULL,
  `email` varchar(64) NOT NULL,
  `activation_hash` char(40) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL COMMENT 'NULL = is banned',
  `password_reset_hash` char(40) DEFAULT NULL,
  `password_reset_time` int(10) unsigned DEFAULT NULL,
  `rememberme_token` char(64) DEFAULT NULL,
  `failed_logins` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Ban them after 255 failed logins',
  `last_failed_login_time` int(10) unsigned DEFAULT NULL,
  `registration_ip` varchar(39) DEFAULT NULL COMMENT 'Might remove this',
  `name_first` varchar(64) NOT NULL,
  `name_last` varchar(64) NOT NULL,
  `name_suffix` varchar(10) NOT NULL,
  `expected_graduation` smallint(5) unsigned DEFAULT NULL,
  `birth_year` smallint(6) NOT NULL,
  `birth_day` tinyint(2) DEFAULT NULL,
  `birth_month` tinyint(2) unsigned DEFAULT NULL,
  `registration_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_sections` FOREIGN KEY (`sectionID`) REFERENCES `sections` (`sectionID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_announcements_users` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assignments_files` FOREIGN KEY (`fileID`) REFERENCES `files` (`fileID`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_assignments_latePolicies1` FOREIGN KEY (`latePolicyID`) REFERENCES `latePolicies` (`latePolicyID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_assignments_users1` FOREIGN KEY (`creatorID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_assignments1` FOREIGN KEY (`assignmentID`) REFERENCES `assignments` (`assignmentID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_grades_sections1` FOREIGN KEY (`sectionID`) REFERENCES `sections` (`sectionID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_grades_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_grades_users2` FOREIGN KEY (`graderID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `latePolicies`
--
ALTER TABLE `latePolicies`
  ADD CONSTRAINT `fk_latePolicies_users1` FOREIGN KEY (`creatorID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sectionAssignments`
--
ALTER TABLE `sectionAssignments`
  ADD CONSTRAINT `fk_sectionAssignments_assignments1` FOREIGN KEY (`assignmentID`) REFERENCES `assignments` (`assignmentID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_sectionAssignments_sections1` FOREIGN KEY (`sectionID`) REFERENCES `sections` (`sectionID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sectionGradingPolicies`
--
ALTER TABLE `sectionGradingPolicies`
  ADD CONSTRAINT `fk_sectionGradingPolicies_letterScales1` FOREIGN KEY (`letterScaleID`) REFERENCES `letterScales` (`letterScaleID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_courses1` FOREIGN KEY (`courseID`) REFERENCES `courses` (`courseID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_sections_sectionGradingPolicies1` FOREIGN KEY (`sectionGradingPolicyID`) REFERENCES `sectionGradingPolicies` (`sectionGradingPolicyID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sectionStudents`
--
ALTER TABLE `sectionStudents`
  ADD CONSTRAINT `fk_sectionStudents_sections1` FOREIGN KEY (`sectionID`) REFERENCES `sections` (`sectionID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_sectionStudents_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sectionTAs`
--
ALTER TABLE `sectionTAs`
  ADD CONSTRAINT `fk_sectionTAs_sections1` FOREIGN KEY (`sectionID`) REFERENCES `sections` (`sectionID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_sectionTAs_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sectionTeachers`
--
ALTER TABLE `sectionTeachers`
  ADD CONSTRAINT `fk_sectionTeachers_sections1` FOREIGN KEY (`sectionID`) REFERENCES `sections` (`sectionID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_sectionTeachers_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `fk_submissions_assignments1` FOREIGN KEY (`assignmentID`) REFERENCES `assignments` (`assignmentID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_submissions_files1` FOREIGN KEY (`fileID`) REFERENCES `files` (`fileID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_submissions_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
