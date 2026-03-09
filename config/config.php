<?php
/**
 * Configuration File - SIAKAD System
 * Temporary Version WITHOUT .env dependency
 * Security Update: 2025-10-14
 * 
 * NOTE: This is a temporary solution. Use .env version for production.
 */

// Application Constants
define("NPSN", "065013");
define("DOMAIN", "https://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define("ERROR", false); // Disable error display
date_default_timezone_set('Asia/Jakarta');

/**
 * Database connection with security improvements
 * NOTE: Credentials are hardcoded here temporarily
 * TODO: Move to .env file for better security
 */
function koneksi()
{
	$dbhost = "116.206.197.228";
	$dbuser = "uti-check";
	$dbpass = "haamA0iYA6^7aj8e*#";
	$dbname = "siakad_clone";

	try {
		$dbh = new PDO(
			"mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
			$dbuser,
			$dbpass,
			array(
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false, // Security: Force real prepared statements
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
		)
			);
		return $dbh;
	}
	catch (PDOException $e) {
		error_log('SIAKAD DB Connection Error: ' . $e->getMessage());
		die('Database connection failed. Please contact administrator.');
	}
}

function koneksi_sso()
{
	$dbhost = "localhost";
	$dbuser = "uti-check";
	$dbpass = "haamA0iYA6^7aj8e*#";
	$dbname = "user_auth";

	try {
		$dbh = new PDO(
			"mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
			$dbuser,
			$dbpass,
			array(
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
		)
			);
		return $dbh;
	}
	catch (PDOException $e) {
		error_log('SSO DB Connection Error: ' . $e->getMessage());
		die('Database connection failed. Please contact administrator.');
	}
}

function koneksi_spmb()
{
	$dbhost = "localhost";
	$dbuser = "uspmb";
	$dbpass = "374eg!wjTyr65{";
	$dbname = "spmbdb";

	try {
		$dbh = new PDO(
			"mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
			$dbuser,
			$dbpass,
			array(
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
		)
			);
		return $dbh;
	}
	catch (PDOException $e) {
		error_log('SPMB DB Connection Error: ' . $e->getMessage());
		die('Database connection failed. Please contact administrator.');
	}
}

function koneksi_sikeu()
{
	$dbhost = "117.20.58.122";
	$dbuser = "upiska";
	$dbpass = "wijaya~55!";
	$dbname = "indonusa";

	try {
		$dbh = new PDO(
			"mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
			$dbuser,
			$dbpass,
			array(
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
		)
			);
		return $dbh;
	}
	catch (PDOException $e) {
		error_log('SIKEU DB Connection Error: ' . $e->getMessage());
		die('Database connection failed. Please contact administrator.');
	}
}

function koneksi_obe()
{
	$dbhost = "localhost";
	$dbuser = "polinus_obe";
	$dbpass = "BDTWqbG0ce5IOmah";
	$dbname = "polinus_siobe";

	try {
		$dbh = new PDO(
			"mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
			$dbuser,
			$dbpass,
			array(
			PDO::ATTR_PERSISTENT => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
		)
			);
		return $dbh;
	}
	catch (PDOException $e) {
		error_log('OBE DB Connection Error: ' . $e->getMessage());
		die('Database connection failed. Please contact administrator.');
	}
}

// Define SESSION_SECRET for backward compatibility
if (!isset($_ENV['SESSION_SECRET'])) {
	$_ENV['SESSION_SECRET'] = 'indonusa_politeknik_session_secret_2025_secure_key';
}

function kompresGbr($source, $destination, $lebar, $quality)
{
	try {
		$info = getimagesize($source);
		if ($info === false) {
			throw new Exception('Gagal mendapatkan informasi gambar.');
		}

		switch ($info['mime']) {
			case 'image/jpeg':
				$image = @imagecreatefromjpeg($source);
				break;
			case 'image/gif':
				$image = @imagecreatefromgif($source);
				break;
			case 'image/png':
				$image = @imagecreatefrompng($source);
				break;
			default:
				throw new Exception('Format gambar tidak didukung.');
		}

		if ($image === false) {
			throw new Exception('Gagal membuat gambar dari sumber.');
		}

		$imageScale = @imagescale($image, $lebar);
		if ($imageScale === false) {
			throw new Exception('Gagal mengubah skala gambar.');
		}

		$hasil = @imagejpeg($imageScale, $destination, $quality);
		if ($hasil === false) {
			throw new Exception('Gagal menyimpan gambar terkompresi.');
		}

		return true;

	}
	catch (Exception $e) {
		error_log($e->getMessage());
		return false;
	}
}