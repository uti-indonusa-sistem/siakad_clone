<?php
use \Psr\Http\Message\ServerRequestInterface as PenerimaData;

require '../../../config/config.php';
require '../../vendor/autoload.php';

$appWsia = new Slim\App();
$appWsia->get('/', 'tampil');
$appWsia->run();

function tampil()
{
	$perintah = "select * from wsia_semester order by id_smt desc";
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$hasil['berhasil'] = 1;
		$jdata = count($data);
		for ($i = 0; $i < $jdata; $i++) {
			$hasil['pesan'][$i]['id'] = $data[$i]->id_smt;
			$hasil['pesan'][$i]['value'] = $data[$i]->nm_smt;
		}
		echo json_encode($hasil['pesan']);

	} catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = $salah->getMessage();
		echo json_encode($hasil);
	}
}
