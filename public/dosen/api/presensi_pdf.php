<?php 

if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

include "../lib/pdf/mpdf.php";
$mpdf=new mPDF('-s','Legal');
$mpdf->setHTMLFooter('<div style="text-align:right;">Aktifitas Bimbingan | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
$mpdf->AddPage('P'.'','','','',5,5,5,5,5,5);
$stylesheet = file_get_contents('../lib/pdf/krs.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetWatermarkText('POLINUS');
$mpdf->showWatermarkText = true;
$mpdf->watermarkTextAlpha = 0.1;

$header="<table width='100%' border='0'>
			<tr>
				<td rowspan='5' align='center'><img src='../gambar/logo_pt.jpg' height='110'></td>
				<td align='center'><h3>POLITEKNIK INDONUSA SURAKARTA</h3></td>
				<td rowspan='5' align='left'></td>
			</tr>
			<tr>
				<td align='center'>
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
				<td align='center' colspan='2'><h3><u>PRESENSI BIMBINGAN</u></h3> </td>
			</tr>
		</table>
			";

$xid_ptk	= $_SESSION['xid_ptk'];


try{
	$db 	= koneksi();
	$qryDosen 	= $db->prepare("SELECT nm_ptk, nidn FROM wsia_dosen WHERE id_ptk =? OR xid_ptk =?"); 
	$qryDosen->bindParam('1', $xid_ptk);
	$qryDosen->bindParam('2', $xid_ptk);
	$qryDosen->execute();
	$dataDosen   = $qryDosen->fetch(PDO::FETCH_OBJ);

	$qry 	= $db->prepare("select nipd, nm_pd from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and left(mulai_smt,4)=? and pa=?"); 
	$qry->bindParam('1', $id);
	$qry->bindParam('2', $xid_ptk);
	$qry->execute();
	$data = $qry->fetchAll(PDO::FETCH_OBJ);
	$c = $qry->rowCount();
} catch (PDOException $salah) {
	exit(json_encode($salah->getMessage()));
}


$isiheader="<center>
		<table width='100%' border='1' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		  <tr>
		    <td align='center' valign='middle' width='5'>No</td>
		    <td align='center' valign='middle' width='20'>NIM</td>
		    <td align='center' valign='middle'>Nama</td>    
		    <td align='center' valign='middle'></td>
		    <td align='center' valign='middle'></td>
		    <td align='center' valign='middle'></td>    
		    <td align='center' valign='middle'></td>
		  </tr>
		  ";
if($c != 0){
	foreach ($data as $key => $d) {
	
		$isi.="
		  <tr height='20'>
		    <td align='center' valign='middle'>".($key+1)."</td>
		    <td align='center' valign='middle'>".$d->nipd."</td>
		    <td align='center' valign='middle'>".$d->nm_pd."</td>
		    <td align='center' valign='middle'></td>
		    <td align='center' valign='middle'></td>
		    <td align='center' valign='middle'></td>
		    <td align='center' valign='middle'></td>
		  </tr>
		";
	}
}else{
	$isi="
		  <tr height='20'>
		    <td align='center' valign='middle'>Belum Ada Data</td>
		  </tr>
		";
}
$bawah = "
		</table> </center>
		";
	
	$bimbingan=$header.$isiheader.$isi.$bawah;

$mpdf->WriteHTML($bimbingan);
$mpdf->Output('Presensi_Bimbingan_'.$dataDosen->nidn.'_'.date('d-m-Y').'.pdf', 'D');
?>