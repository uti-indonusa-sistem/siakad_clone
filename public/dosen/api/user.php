<?php
use Slim\Views\PhpRenderer;

require '../../config/config.php';
require '../vendor/autoload.php';

$appWsia = new Slim\App();

$appWsia->get('/ambil_user', 'userAmbil');
$appWsia->post('/tambah_user', 'userTambah');
$appWsia->run();


function userAmbil()
{
	$sql = "select * FROM wsia_user ORDER BY username";
	try {
		$db = koneksi();
		$eksekusi = $db->query($sql);
		$hasil = $eksekusi->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		echo '{"error":{"text":' . $salah->getMessage() . '}}';
	}
}
function userTambah()
{
	$request = Slim::getInstance()->request();
	$user = json_decode($request->getBody());
	$sql = "INSERT INTO wsia_user (username, passwd, nama, bagian, level) VALUES (:username, :passwd, :nama, :bagian, :level)";
	try {
		$db = koneksi();
		$qry = $db->prepare($sql);
		$qry->bindParam("username", $user->username);
		$qry->bindParam("passwd", $user->passwd);
		$qry->bindParam("nama", $user->nama);
		$qry->bindParam("bagian", $user->bagian);
		$qry->bindParam("level", $user->level);
		$qry->execute();
		$db = null;
		echo json_encode($user);
	} catch (PDOException $salah) {
		echo '{"error":{"text":' . $salah->getMessage() . '}}';
	}
}
