-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Jan 30, 2020 at 09:09 PM
-- Server version: 5.7.29
-- PHP Version: 7.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `codesort_docker`
--
CREATE DATABASE IF NOT EXISTS `codesort_docker` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `codesort_docker`;

-- --------------------------------------------------------

--
-- Table structure for table `codes`
--

DROP TABLE IF EXISTS `codes`;
CREATE TABLE `codes` (
    `code_id` int(6) UNSIGNED NOT NULL,
    `code_fl` int(2) UNSIGNED NOT NULL DEFAULT '0',
    `code_cat` int(6) UNSIGNED NOT NULL DEFAULT '0',
    `code_size` int(2) UNSIGNED NOT NULL DEFAULT '0',
    `code_image` varchar(100) NOT NULL DEFAULT '',
    `code_donor` int(6) UNSIGNED NOT NULL DEFAULT '0',
    `code_approved` enum('y','n') NOT NULL DEFAULT 'y'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codes`
--

INSERT INTO `codes` (`code_id`, `code_fl`, `code_cat`, `code_size`, `code_image`, `code_donor`, `code_approved`) VALUES
    (1, 0, 1, 1, 'Yoda-icon.png', 1, 'y');

-- --------------------------------------------------------

--
-- Table structure for table `codes_cat`
--

DROP TABLE IF EXISTS `codes_cat`;
CREATE TABLE `codes_cat` (
    `cat_id` int(6) UNSIGNED NOT NULL,
    `cat_fl` int(6) UNSIGNED NOT NULL DEFAULT '0',
    `cat_name` varchar(50) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codes_cat`
--

INSERT INTO `codes_cat` (`cat_id`, `cat_fl`, `cat_name`) VALUES
    (1, 0, 'Wholeeee'),
    (2, 1, 'Subj1');

-- --------------------------------------------------------

--
-- Table structure for table `codes_donors`
--

DROP TABLE IF EXISTS `codes_donors`;
CREATE TABLE `codes_donors` (
    `donor_id` int(6) UNSIGNED NOT NULL,
    `donor_name` varchar(20) NOT NULL DEFAULT '',
    `donor_url` varchar(100) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codes_donors`
--

INSERT INTO `codes_donors` (`donor_id`, `donor_name`, `donor_url`) VALUES
    (1, 'DonorFromSystem', 'http://donorsurl.cooooooom');

-- --------------------------------------------------------

--
-- Table structure for table `codes_options`
--

DROP TABLE IF EXISTS `codes_options`;
CREATE TABLE `codes_options` (
    `optkey` varchar(30) NOT NULL,
    `optvalue` varchar(255) NOT NULL,
    `optdesc` varchar(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codes_options`
--

INSERT INTO `codes_options` (`optkey`, `optvalue`, `optdesc`) VALUES
    ('admin_email', 'test@adminemail.com', 'Your email address, so you can be notified of new donated codes.'),
    ('collective_name', 'Collective Name', 'The name of your site collective.'),
    ('do_upload', 'y', 'Use CodeSort to upload images directly? y or n'),
    ('images_folder', '/app/samples/images/', 'The full server path to your CodeSort images directory. INCLUDE the trailing slash. Ex: /home/username/public_html/codesort/images/'),
    ('images_url', 'http://localhost:8075/samples/images', 'The URL to your CodeSort images directory. NO trailing slash. Ex: http://example.com/codesort/images'),
    ('install_folder', '/app/codesort2', 'The full server path to your CodeSort directory. NO trailing slash. Ex: /home/username/public_html/codesort'),
    ('install_url', 'http://localhost:8075/codesort2', 'The URL to your CodeSort directory. NO trailing slash. Ex: http://example.com/codesort'),
    ('num_per_page', '20', 'Number of items displayed per page for pagination.'),
    ('sort_order', 'DESC', 'Display order of codes. DESC for newest first; ASC for oldest first.'),
    ('use_captcha', 'y', 'Use CAPTCHA on donation form? y or n'),
    ('use_cat', 'y', 'Use categories? y or n');

-- --------------------------------------------------------

--
-- Table structure for table `codes_sizes`
--

DROP TABLE IF EXISTS `codes_sizes`;
CREATE TABLE `codes_sizes` (
    `size_id` int(2) UNSIGNED NOT NULL,
    `size_order` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
    `size_size` varchar(20) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `codes_sizes`
--

INSERT INTO `codes_sizes` (`size_id`, `size_order`, `size_size`) VALUES
    (1, 0, 'Test Size 1');

-- --------------------------------------------------------
--
-- Indexes for dumped tables
--

--
-- Indexes for table `codes`
--
ALTER TABLE `codes`
    ADD PRIMARY KEY (`code_id`),
    ADD KEY `code_fl` (`code_fl`,`code_size`,`code_cat`),
    ADD KEY `code_approved` (`code_approved`);

--
-- Indexes for table `codes_cat`
--
ALTER TABLE `codes_cat`
    ADD PRIMARY KEY (`cat_id`),
    ADD KEY `cat_fl` (`cat_fl`);

--
-- Indexes for table `codes_donors`
--
ALTER TABLE `codes_donors`
    ADD PRIMARY KEY (`donor_id`);

--
-- Indexes for table `codes_options`
--
ALTER TABLE `codes_options`
    ADD PRIMARY KEY (`optkey`);

--
-- Indexes for table `codes_sizes`
--
ALTER TABLE `codes_sizes`
    ADD PRIMARY KEY (`size_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `codes`
--
ALTER TABLE `codes`
    MODIFY `code_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `codes_cat`
--
ALTER TABLE `codes_cat`
    MODIFY `cat_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `codes_donors`
--
ALTER TABLE `codes_donors`
    MODIFY `donor_id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `codes_sizes`
--
ALTER TABLE `codes_sizes`
    MODIFY `size_id` int(2) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;
