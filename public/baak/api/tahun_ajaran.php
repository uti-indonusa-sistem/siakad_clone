<?php
use \Psr\Http\Message\ServerRequestInterface as PenerimaData;

require '../../config/config.php';
require '../vendor/autoload.php';

$appWsia = new Slim\App();
$appWsia->get('/', 'tampil');
$appWsia->run();

function tampil()
{
	$perintah = "select * from wsia_tahun_ajaran where a_periode_aktif='1' ";
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$hasil['berhasil'] = 1;
		$hasil['pesan'] = $data;
		echo json_encode($hasil);

	} catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = $salah->getMessage();
		echo json_encode($hasil);
	}
}
