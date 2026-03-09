<?php
error_reporting(0);
if (!isset($key)) {
	exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaADMIN']) {
	exit();
}


include "../lib/pdf/mpdf.php";
$exp = explode("_", $id);
$id_induk = $exp[0];
$th = "%" . substr($exp[1], 0, 4) . "%";

if ($aksi == "download") {
	try {
		$qryProgdi = "SELECT a.nm_lemb as prodi, xid_sms FROM wsia_sms a WHERE a.kode_prodi = ?";
		$db 	= koneksi();
		$qry 	= $db->prepare($qryProgdi);
		$qry->bindParam('1', $id_induk);
		$qry->execute();

		$d	= $qry->fetch(PDO::FETCH_OBJ);

		$qrySemester = "SELECT * FROM wsia_semester WHERE id_smt = ?";
		$db 	= koneksi();
		$qrx 	= $db->prepare($qrySemester);
		$qrx->bindParam('1', $exp[1]);
		$qrx->execute();

		$semst	= $qrx->fetch(PDO::FETCH_OBJ);

		$db		= null;
		$isi = "";
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}

	function UR_exists($url)
	{
		$headers = get_headers($url);
		return stripos($headers[0], "200 OK") ? true : false;
	}

	$mpdf = new mPDF('-s', 'Legal');
	//$mpdf->SetHTMLHeader("");
	//$mpdf->setHTMLFooter('<div style="text-align:right;">{PAGENO} / {nbpg}</div>');
	$mpdf->AddPage('L' . '', '', '', '', 5, 10, 15, 30, 10, 5);
	$stylesheet = file_get_contents('../lib/pdf/absen.css');
	$mpdf->WriteHTML($stylesheet, 1);
	$mpdf->SetDisplayMode('fullpage');

	if ($isi == "") {
		$header = "
		<table width='92%' align='right'>
		<tr>
			<td align='center'>
				<h3>BUKU INDUK MAHASISWA POLITEKNIK INDONUSA SURAKARTA</h3>
			</td>
		</tr>
		<tr>   
			<td align='center'><h3>PROGRAM STUDI " . strtoupper($d->prodi) . " - TAHUN ANGKATAN " . strtoupper($semst->nm_smt) . "</h3> </td>
		</tr>
		</table>";

		$isiHeader = "
		<table width='100%' border='1' align='left'>
		<tr bgcolor='#CCCCCC'>
			<th width='10' rowspan='2' align='center' valign='middle'><strong>NO</strong></th>
			<th width='100' rowspan='2' align='center' valign='middle'><strong>NIM</strong></th>
			<th width='150' rowspan='2' align='center' valign='middle'><strong>Nama Mahasiswa </strong></th>
			<th width='150' rowspan='2' align='center' valign='middle'><strong>Tempat Tanggal Lahir</strong></th>
			<th width='100' rowspan='2' align='center' valign='middle'><strong>Jenis Kelamin</strong></th>
			<th width='100' rowspan='2' align='center' valign='middle'><strong>Agama</strong></th>
			<th width='100' rowspan='2' align='center' valign='middle'><strong>Nama Ibu Kandung</strong></th>
			<th width='200' rowspan='2' align='center' valign='middle'><strong>Alamat</strong></th>
		</tr>
		<tr style='border: hidden;'><td style='border: hidden;'></td></tr>";
		$isi = "";
		$n = 0;

		$qryMhs = "SELECT a.nipd, b.nm_pd, b.tgl_lahir, b.tmpt_lahir, b.jk, c.nm_agama, b.jln, b.rt, b.rw, b.nm_dsn, b.ds_kel, d.value as wilayah, b.nm_ibu_kandung as ibu FROM wsia_mahasiswa_pt a JOIN wsia_mahasiswa b ON (a.xid_reg_pd = b.xid_pd OR a.id_reg_pd = a.id_pd) JOIN wsia_agama c ON c.id_agama = b.id_agama JOIN siakad_wilayah d ON b.id_wil = d.id WHERE a.mulai_smt LIKE ? AND a.id_sms = ? ORDER BY nipd ASC";
		try {
			$db 	= koneksi();
			$qry 	= $db->prepare($qryMhs);
			$qry->bindParam('1', $th);
			$qry->bindParam('2', $d->xid_sms);
			$qry->execute();

			$dataMhs	= $qry->fetchAll(PDO::FETCH_OBJ);
			$db		= null;

			$aData = array();
			foreach ($dataMhs as $itemData) {
				$n++;
				$nim = $itemData->nipd;
				if ($itemData->jk == 'P') {
					$jenisk = "Perempuan";
				} else {
					$jenisk = "Laki - Laki";
				}

				// if (UR_exists("http://".$_SERVER['HTTP_HOST']."/mhs/foto/".md5($itemData->nipd).".jpg")) {
				// 	$foto_mhs = "http://".$_SERVER['HTTP_HOST']."/mhs/foto/" . md5($itemData->nipd) . ".jpg";
				// 	// $foto_mhs = 'ada';
				// } else {
				// 	// $foto_mhs = 'tidak ada';
				// 	$foto_mhs = "http://".$_SERVER['HTTP_HOST']."/gambar/no-foto.jpg";
				// }

				$alamat = "";
				if ($itemData->jln != " " or $itemData->jln != null or $itemData->jln != "jln" or $itemData->jln != "-") {
					$alamat .= $itemData->jln . ", ";
				} else {
					$alamat .= "";
				}

				if ($itemData->nm_dsn != "-" or $itemData->nm_dsn != " " or $itemData->nm_dsn != null) {
					$alamat .= $itemData->nm_dsn . ", ";
				} else {
					$alamat .= "";
				}

				if ($itemData->ds_kel != "-" or $itemData->ds_kel != " ") {
					$alamat .= $itemData->ds_kel . ", ";
				} else {
					$alamat .= "";
				}

				if ($itemData->rt != "-" or $itemData->rt != " " or $itemData->rt != "0") {
					$alamat .= $itemData->rt . ", ";
				} else {
					$alamat .= "";
				}

				if ($itemData->rw != "-" or $itemData->rw != " " or $itemData->rw != "0") {
					$alamat .= $itemData->rw . ", ";
				} else {
					$alamat .= "";
				}

				$alamat .= "" . $itemData->wilayah;

				$isi .= "
				<tr>
					<td align='center' valign='middle'>" . $n . "</td>
					<td align='center' valign='middle'>" . $nim . "</td>
					<td valign='middle'>" . $itemData->nm_pd . "</td>
					<td valign='middle'>" . $itemData->tmpt_lahir . ", " . date('d-m-Y', strtotime($itemData->tgl_lahir)) . "</td>
					<td valign='middle'>" . $jenisk . "</td>
					<td valign='middle'>" . $itemData->nm_agama . "</td>
					<td valign='middle'>" . $itemData->ibu . "</td>
					<td valign='middle'>" . $alamat . "</td>
				</tr>";
			}
		} catch (PDOException $salah) {
			$isi .= "Kesalahan mengambil data mahasiswa";
		}




		for ($m = $n; $m <= 30; $m++) {
			$n++;
			$isi .= "
			<tr>
				<td align='center' valign='middle'>" . $n . "</td>
				<td align='center' valign='middle'>&nbsp;</td>
				<td valign='middle'>&nbsp;</td>
			</tr>";
		}


		$isiFooter = "
		</table>";
	}

	$mpdf->WriteHTML($header . $isiHeader . $isi . $isiFooter);

	$mpdf->Output('BUKU-INDUK-MAHASISWA-' . strtoupper($d->prodi) . '.pdf', 'D');
	// $mpdf->Output();
}
