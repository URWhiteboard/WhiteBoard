-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Dec 07, 2014 at 05:39 PM
-- Server version: 5.5.38
-- PHP Version: 5.5.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
`assignmentID` int(10) unsigned NOT NULL,
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
  `gradeVisible` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
`courseID` int(10) unsigned NOT NULL,
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
  `cross_listed` text
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2264 ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
`fileID` int(10) unsigned NOT NULL,
  `extension` varchar(10) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `upload_time` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=80 ;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
`gradeID` int(10) unsigned NOT NULL,
  `real_score` smallint(5) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `assignmentID` int(10) unsigned NOT NULL,
  `graderID` int(10) unsigned NOT NULL,
  `effective_score` smallint(5) unsigned DEFAULT NULL,
  `comment` text
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `latePolicies`
--

CREATE TABLE `latePolicies` (
`latePolicyID` int(10) unsigned NOT NULL,
  `title` text NOT NULL,
  `rate` smallint(5) unsigned DEFAULT NULL,
  `period` enum('DAY','HOUR','NONE') NOT NULL,
  `is_percent` tinyint(1) DEFAULT NULL,
  `creatorID` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `letterScales`
--

CREATE TABLE `letterScales` (
`letterScaleID` int(10) unsigned NOT NULL,
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
  `dm` decimal(2,0) unsigned DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionAssignments`
--

CREATE TABLE `sectionAssignments` (
`sectionAssignmentsID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  `assignmentID` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionGradingPolicies`
--

CREATE TABLE `sectionGradingPolicies` (
`sectionGradingPolicyID` int(10) unsigned NOT NULL,
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
  `other_weight` decimal(2,0) unsigned DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2267 ;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
`sectionID` int(10) unsigned NOT NULL,
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
  `sectionGradingPolicyID` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2242 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionStudents`
--

CREATE TABLE `sectionStudents` (
`sectionStudentID` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL,
  `is_pass_fail` tinyint(1) NOT NULL,
  `is_satisfactory_fail` tinyint(1) NOT NULL,
  `is_no_credit` tinyint(1) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionTAs`
--

CREATE TABLE `sectionTAs` (
`sectionTAID` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sectionTeachers`
--

CREATE TABLE `sectionTeachers` (
`sectionTeacherID` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `sectionID` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
`submissionID` int(10) unsigned NOT NULL,
  `submit_time` int(10) unsigned NOT NULL,
  `comment` text,
  `assignmentID` int(10) unsigned NOT NULL,
  `fileID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
`userID` int(10) unsigned NOT NULL,
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
  `registration_time` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
 ADD PRIMARY KEY (`assignmentID`,`creatorID`,`latePolicyID`), ADD KEY `fk_assignments_users1_idx` (`creatorID`), ADD KEY `fk_assignments_latePolicies1_idx` (`latePolicyID`), ADD KEY `fileID` (`fileID`), ADD KEY `fileID_2` (`fileID`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
 ADD PRIMARY KEY (`courseID`), ADD UNIQUE KEY `courseID_UNIQUE` (`courseID`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
 ADD PRIMARY KEY (`fileID`), ADD KEY `userID` (`userID`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
 ADD PRIMARY KEY (`gradeID`,`sectionID`,`userID`,`assignmentID`,`graderID`), ADD KEY `fk_grades_sections1_idx` (`sectionID`), ADD KEY `fk_grades_users1_idx` (`userID`), ADD KEY `fk_grades_assignments1_idx` (`assignmentID`), ADD KEY `fk_grades_users2_idx` (`graderID`);

--
-- Indexes for table `latePolicies`
--
ALTER TABLE `latePolicies`
 ADD PRIMARY KEY (`latePolicyID`,`creatorID`), ADD KEY `fk_latePolicies_users1_idx` (`creatorID`);

--
-- Indexes for table `letterScales`
--
ALTER TABLE `letterScales`
 ADD PRIMARY KEY (`letterScaleID`);

--
-- Indexes for table `sectionAssignments`
--
ALTER TABLE `sectionAssignments`
 ADD PRIMARY KEY (`sectionAssignmentsID`,`sectionID`,`assignmentID`), ADD KEY `fk_sectionAssignments_sections1_idx` (`sectionID`), ADD KEY `fk_sectionAssignments_assignments1_idx` (`assignmentID`);

--
-- Indexes for table `sectionGradingPolicies`
--
ALTER TABLE `sectionGradingPolicies`
 ADD PRIMARY KEY (`sectionGradingPolicyID`,`letterScaleID`), ADD KEY `fk_sectionGradingPolicies_letterScales1_idx` (`letterScaleID`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
 ADD PRIMARY KEY (`sectionID`,`courseID`,`sectionGradingPolicyID`), ADD KEY `fk_sections_courses1_idx` (`courseID`), ADD KEY `fk_sections_sectionGradingPolicies1_idx` (`sectionGradingPolicyID`);

--
-- Indexes for table `sectionStudents`
--
ALTER TABLE `sectionStudents`
 ADD PRIMARY KEY (`sectionStudentID`,`userID`,`sectionID`), ADD KEY `fk_sectionStudents_users1_idx` (`userID`), ADD KEY `fk_sectionStudents_sections1_idx` (`sectionID`);

--
-- Indexes for table `sectionTAs`
--
ALTER TABLE `sectionTAs`
 ADD PRIMARY KEY (`sectionTAID`,`userID`,`sectionID`), ADD KEY `fk_sectionTAs_users1_idx` (`userID`), ADD KEY `fk_sectionTAs_sections1_idx` (`sectionID`);

--
-- Indexes for table `sectionTeachers`
--
ALTER TABLE `sectionTeachers`
 ADD PRIMARY KEY (`sectionTeacherID`,`userID`,`sectionID`), ADD KEY `fk_sectionTeachers_users1_idx` (`userID`), ADD KEY `fk_sectionTeachers_sections1_idx` (`sectionID`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
 ADD PRIMARY KEY (`submissionID`,`assignmentID`,`fileID`,`userID`), ADD KEY `fk_submissions_assignments1_idx` (`assignmentID`), ADD KEY `fk_submissions_files1_idx` (`fileID`), ADD KEY `fk_submissions_users1_idx` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
MODIFY `assignmentID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
MODIFY `courseID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2264;
--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
MODIFY `fileID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=80;
--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
MODIFY `gradeID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `latePolicies`
--
ALTER TABLE `latePolicies`
MODIFY `latePolicyID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `letterScales`
--
ALTER TABLE `letterScales`
MODIFY `letterScaleID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `sectionAssignments`
--
ALTER TABLE `sectionAssignments`
MODIFY `sectionAssignmentsID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `sectionGradingPolicies`
--
ALTER TABLE `sectionGradingPolicies`
MODIFY `sectionGradingPolicyID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2267;
--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
MODIFY `sectionID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2242;
--
-- AUTO_INCREMENT for table `sectionStudents`
--
ALTER TABLE `sectionStudents`
MODIFY `sectionStudentID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=56;
--
-- AUTO_INCREMENT for table `sectionTAs`
--
ALTER TABLE `sectionTAs`
MODIFY `sectionTAID` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sectionTeachers`
--
ALTER TABLE `sectionTeachers`
MODIFY `sectionTeacherID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
MODIFY `submissionID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- Constraints for dumped tables
--

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
