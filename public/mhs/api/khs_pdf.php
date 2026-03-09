<?php
error_reporting(0);
if (!isset($key)) {
	exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaMHS']) {
	exit();
}

include "../lib/pdf/mpdf.php";

$mpdf = new mPDF('-s', 'Legal');
$mpdf->setHTMLFooter('<div style="text-align:right;">KHS Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
$mpdf->AddPage('P' . '', '', '', '', 5, 5, 5, 5, 5, 5);
$stylesheet = file_get_contents('../lib/pdf/krs.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetWatermarkText('POLINUS COPY');
$mpdf->showWatermarkText = true;
$mpdf->watermarkTextAlpha = 0.1;

if (strlen($id) == 5) {
	$id_smt = $id;
} else {
	$id_smt = $_SESSION['id_smt_aktif'];
}

$xid_reg_pd = $_SESSION['xid_reg_pd'];




$th_awal = substr($id_smt, 0, 4);
$th_akhir = $th_awal + 1;
if (substr($id_smt, 4, 1) == "1") {
	$smt = "GANJIL";
} else {
	$smt = "GENAP";
}
$header = "
	<table width='100%' align='left'>
	 <tr>
	    <td rowspan='3' align='center' width='95' valign='center'><img src='../gambar/logo_pt.jpg' height='90'></td>
	    <td align='left'><h3>POLITEKNIK INDONUSA SURAKARTA</h3></td>
	  </tr>
	  <tr>
	    <td align='left'>
	    	Kampus 1: Jl. KH. Samanhudi No.31, Laweyan Surakarta, Telp: 0271 - 743479<br>
			Kampus 2: Jl. Palem No.8 Cemani, Grogol, Sukoharjo, Telp: 0271 - 7464173
	    </td>
	  </tr>
	  <tr>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td align='center' colspan='2'><h3><u>KARTU HASIL STUDI ONLINE</u></h3> </td>
	  </tr>
	</table>";


$KHS = "Tidak ada data";
$qryMhsKrs = "select id_nilai, wsia_nilai.nilai_tampil as akses, IF(wsia_nilai.nilai_tampil = '3', nilai_angka, '0.00') as nilai_angka, IF(wsia_nilai.nilai_tampil = '3', nilai_huruf, '') as nilai_huruf, IF(wsia_nilai.nilai_tampil = '3', nilai_indeks, '0.00') as nilai_indeks, wsia_nilai.xid_kls as vid_kls, nm_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where  wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' and wsia_kelas_kuliah.id_smt='$id_smt'";
// $qryMhsKrs = "select nilai_tampil,id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,xid_sms,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt<='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='".$xid_reg_pd."' and nilai_tampil = '3'";
// $qryMhsKrs = "select id_nilai, wsia_nilai.nilai_tampil as akses, wsia_nilai.xid_kls as vid_kls, nm_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where  wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' and wsia_kelas_kuliah.id_smt='$id_smt'";
try {
	$db 	= koneksi();
	$qry 	= $db->prepare($qryMhsKrs);
	$qry->execute();
	$dataMhsKrs	= $qry->fetch(PDO::FETCH_OBJ);

	if ($qry->rowCount() == 0) {
		$adaData = 0;
	} else {
		$adaData = 1;
	}

	$id_ptk = $dataMhsKrs->pa;
	$sqlPa = "select * from wsia_dosen where xid_ptk='$id_ptk'";
	$qryPa = $db->prepare($sqlPa);
	$qryPa->execute();
	$dataPa = $qryPa->fetch(PDO::FETCH_OBJ);
	if ($qryPa->rowCount() > 0) {
		$pa = $dataPa->gelar_depan . " " . $dataPa->nm_ptk . ", " . $dataPa->gelar_belakang;
	} else {
		$pa = "-";
	}
} catch (PDOException $salah) {
	exit(json_encode($salah->getMessage()));
}



$atas = "
	<table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
	  <tr>
	    <td>NIM</td>
	    <td>: " . $dataMhsKrs->nipd . " </td>
	    <td>&nbsp;</td>
	    <td>Semester </td>
	    <td>: " . $smt . " </td>
	  </tr>
	  <tr>
	    <td>Nama</td>
	    <td>: " . $dataMhsKrs->nm_pd . " </td>
	    <td>&nbsp;</td>
	    <td>Tahun Akademik</td>
	    <td>: " . $th_awal . "/" . $th_akhir . " </td>
	  </tr>
	  <tr>
	    <td>Program Studi </td>
	    <td>: " . $dataMhsKrs->nm_jenj_didik . "-" . $dataMhsKrs->nm_lemb . " </td>
	    <td>&nbsp;</td>
	    <td>Pembimbing Akademik</td>
	    <td>: " . $pa . " </td>
	  </tr>
	</table>";


$dataQR = "http://document.poltekindonusa.ac.id/view_khs-" . ord(substr($dataMhsKrs->nipd, 0, 1)) . '_' . (substr($dataMhsKrs->nipd, 1, 5) * 666) . "-" . ($id_smt * 666) . ".html";
$qrcode = "<barcode code='" . $dataQR . "' type='QR' class='barcode' size='1.5' error='L' />";

$isiheader = "<center>
		<table width='100%' border='1' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		  <tr>
		    <td rowspan='2' align='center' valign='middle' width='20'>No</td>
		    <td rowspan='2' align='center' valign='middle' width='20'>Kode<br>Mata Kuliah </td>
		    <td rowspan='2' align='center' valign='middle' width='380'>Mata Kuliah </td>    
		    <td rowspan='2' align='center' valign='middle' width='20'>SKS</td>
		    <td colspan='2' align='center' valign='middle' width='100'>NILAI</td>
		    <td rowspan='2' align='center' valign='middle' width='90'>SKS x NILAI</td>    
		  </tr>
		  
		  <tr>
		    <td align='center' valign='middle' width='50'>ANGKA</td>
		    <td align='center' valign='middle' width='50'>HURUF</td>
		  </tr>";

$qryNilai 	= $db->prepare($qryMhsKrs);
$qryNilai->execute();
$dataNilai	= $qryNilai->fetchAll(PDO::FETCH_OBJ);

$isi = "";
$n = 0;
$jsks = 0;
$jnXsks = 0;
$ipsmt = 0;
foreach ($dataNilai as $itemNilai) {
	$n++;
	$jsks = $jsks + $itemNilai->vsks_mk;
	$na = $itemNilai->nilai_indeks * $itemNilai->vsks_mk;
	$jnXsks = $jnXsks + $na;
	if ($jsks > 0) {
		$ipsmt = $jnXsks / $jsks;
	} else {
		$ipsmt = 0;
	}


	$isi .= "
		  <tr height='20'>
		    <td align='center' valign='middle'>" . $n . "</td>
		    <td align='center' valign='middle'>" . $itemNilai->kode_mk . "</td>
		    <td valign='middle'>" . $itemNilai->nm_mk . "</td>    
		    <td align='center' valign='middle'>" . $itemNilai->vsks_mk . "</td>";
	if ($itemNilai->akses == '2') {
		$isi .= "
		    <td align='center' valign='middle'>" . number_format('0', 2) . "</td>
		    <td align='center' valign='middle'></td>
		    <td align='center' valign='middle'>" . number_format('0', 2) . "</td>";
	} else {
		$isi .= "
		    <td align='center' valign='middle'>" . number_format($itemNilai->nilai_indeks, 2) . "</td>
		    <td align='center' valign='middle'>" . $itemNilai->nilai_huruf . "</td>
		    <td align='center' valign='middle'>" . number_format($na, 2) . "</td>";
	}
	$isi .= "
		  </tr>
		";
}

//hitung komulatif
$qryNilaiSebelum = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,xid_sms,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt<='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='" . $xid_reg_pd . "' ";
try {
	$qry 	= $db->prepare($qryNilaiSebelum);
	$qry->execute();
	$dataNilaiSebelum	= $qry->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $salah) {
	exit(json_encode($salah->getMessage()));
}

$jsksK = 0;
$jnXsksK = 0;
$ipK = 0;
foreach ($dataNilaiSebelum as $itemNilaiSebelum) {
	$jsksK = $jsksK + $itemNilaiSebelum->vsks_mk;
	$naK = $itemNilaiSebelum->nilai_indeks * $itemNilaiSebelum->vsks_mk;
	$jnXsksK = $jnXsksK + $naK;
	$ipK = $jnXsksK / $jsksK;
}

$isi .= "
	  <tr>
	    <td colspan='3' align='center'>Total</td>    
	    <td align='center'>" . $jsks . "</td>
	    <td colspan='2'>&nbsp;</td>    
	    <td align='center'>" . number_format($jnXsks, 2) . "</td>   
	  </tr>
	  <tr>
	    <td colspan='7' align='left'>
		<table width='50%' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		     <tr>
			     <td>Index Prestasi Semester</td><td>: " . number_format($ipsmt, 2) . " </td>
				 <td>SKS Semester</td><td>: " . $jsks . "</td>
			 </tr>
			 <tr>
			     <td>Index Prestasi Kumulatif</td><td>: " . number_format($ipK, 2) . " </td>
				 <td>SKS Kumulatif</td><td>: " . $jsksK . "</td>
			 </tr>
		</table>
		</td>          
	  </tr>";

$npsn = NPSN;
$sqlWadir = "select nama_wadir from wsia_satuan_pendidikan where npsn='$npsn' ";
$qryWadir = $db->prepare($sqlWadir);
$qryWadir->execute();
$dataWadir = $qryWadir->fetch(PDO::FETCH_OBJ);
if ($qryWadir->rowCount() > 0) {
	$namaWadir = $dataWadir->nama_wadir;
} else {
	$namaWadir = "-";
}

$bawah = "</table>
		<br>
		<table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		  <tr>
		    <td width='50%' align='left'>
				Keterangan:<br>
				<table width='150' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
					  <tr>
					    <td><strong>Angka</strong></td>
					    <td><strong>Huruf</strong></td>
					  </tr>
					  <tr>
					    <td>0.00 - 0.99  </td>
					    <td align='center'>E</td>
					  </tr>
					  <tr>
					    <td>1.00 - 1.99 </td>
					    <td align='center'>D</td>
					  </tr>
					  <tr>
					    <td>2.00 - 2.99 </td>
					    <td align='center'>C</td>
					  </tr>
					  <tr>
					    <td>3.00 - 3.99 </td>
					    <td align='center'>B</td>
					  </tr>
					  <tr>
					    <td>4.00</td>
					    <td align='center'>A</td>
					  </tr>
				</table>
		</td>
		<td>
		$qrcode
		</td>
	    	<td  width='50%' align='center'>
			Surakarta, " . format_tanggal_bln(date('Y-m-d')) . "<br>
			Wakil Direktur I, <br><br><br><br><br>
			" . $namaWadir . "
		</td>
	  </tr>
	</table> </center>";

if ($adaData) {
	$KHS = $header . $atas . $isiheader . $isi . $bawah;
} else {
	$KHS = $header . "Tidak ada data nilai";
}
$db = null;
$mpdf->WriteHTML($KHS);

$mpdf->Output('KHS_' . $id_smt . '_' . $_SESSION['nipd'] . '.pdf', 'D');
