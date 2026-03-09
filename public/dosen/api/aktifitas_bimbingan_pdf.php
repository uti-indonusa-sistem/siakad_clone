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
				<td align='center' colspan='2'><h3><u>LAPORAN AKTIFITAS BIMBINGAN</u></h3> </td>
			</tr>
		</table>
			";

$xid_ptk	= $_SESSION['xid_ptk'];

$exp = explode("_", $id);
$id_smt=$exp[0];
$kelas=$exp[1];

$db 	= koneksi();
$qryDosen 	= $db->prepare("SELECT nm_ptk, nidn FROM wsia_dosen WHERE id_ptk =? OR xid_ptk =?"); 
$qryDosen->bindParam('1', $xid_ptk);
$qryDosen->bindParam('2', $xid_ptk);
$qryDosen->execute();
$dataDosen   = $qryDosen->fetch(PDO::FETCH_OBJ);

$qrySmt 	= $db->prepare("SELECT nm_smt FROM wsia_semester WHERE id_smt=?"); 
$qrySmt->bindParam('1', $id_smt);
$qrySmt->execute();
$dataSmt   = $qrySmt->fetch(PDO::FETCH_OBJ);


$atas="
	<table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
	  <tr>
	    <td>NIDN</td>
	    <td>: ".$dataDosen->nidn." </td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>Nama</td>
	    <td>: ".$dataDosen->nm_ptk." </td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>Tahun Akademik</td>
	    <td>: ".$dataSmt->nm_smt." </td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>Kelas</td>
	    <td>: ".$kelas." </td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	</table>";


try{
	$qry 	= $db->prepare("select * from siakad_pa_aktifitas where id_smt=? and id_ptk=? and kelas=? order by tanggal asc"); 
	$qry->bindParam('1', $id_smt);
	$qry->bindParam('2', $xid_ptk);
	$qry->bindParam('3', $kelas);
	$qry->execute();
	$data = $qry->fetchAll(PDO::FETCH_OBJ);
	$c = $qry->rowCount();
} catch (PDOException $salah) {
	exit(json_encode($salah->getMessage()));
}


$isiheader="<center>
		<table width='100%' border='1' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		  <tr>
		    <td rowspan='2' align='center' valign='middle' width='5'>Per</td>
		    <td rowspan='2' align='center' valign='middle' width='20'>Tanggal</td>
		    <td colspan='4' align='center' valign='middle'>Aktifitas Kuliah</td>    
		    <td rowspan='2' align='center' valign='middle' width='30'>Kondisi Mahasiswa</td>
		    <td rowspan='2' align='center' valign='middle' width='30'>Penanganan Khusus</td>
		    <td rowspan='2' align='center' valign='middle' width='30'>Kesimpulan</td>    
		  </tr>
		  <tr>
		    <td align='center' valign='middle' width='10'>Aktif</td>
		    <td align='center' valign='middle' width='10'>Non Aktif</td>
		    <td align='center' valign='middle' width='10'>Cuti</td>
		    <td align='center' valign='middle' width='10'>Keluar/Lulus</td>
		  </tr>";
if($c != 0){
	foreach ($data as $key => $d) {
	
		$isi.="
		  <tr height='20'>
		    <td align='center' valign='middle'>".($key+1)."</td>
		    <td align='center' valign='middle'>".format_tanggal($d->tanggal)."</td>
		    <td align='center' valign='middle'>".$d->mhs_aktif."</td>
		    <td align='center' valign='middle'>".$d->mhs_nonaktif."</td>
		    <td align='center' valign='middle'>".$d->mhs_cuti."</td>
		    <td align='center' valign='middle'>".$d->mhs_keluar."</td>
		    <td align='center' valign='middle'>".$d->kondisi_mahasiswa."</td>
		    <td align='center' valign='middle'>".$d->penanganan_mahasiswa."</td>
		    <td align='center' valign='middle'>".$d->kesimpulan."</td>
		  </tr>
		";
	}
}else{
	$isi="
		  <tr height='20'>
		    <td colspan='9' align='center' valign='middle'>Belum Ada Data</td>
		  </tr>
		";
}

$npsn=NPSN;
$sqlWadir="select nama_wadir from wsia_satuan_pendidikan where npsn='$npsn' ";
$qryWadir = $db->prepare($sqlWadir);
$qryWadir->execute();
$dataWadir = $qryWadir->fetch(PDO::FETCH_OBJ);
if ($qryWadir->rowCount()>0) {
	$namaWadir=$dataWadir->nama_wadir;
} else {
	$namaWadir="-";
}

$bawah="</table>
		<br>
		<table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		  <tr>
		    <td width='50%' align='left'>
				<table width='150' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
					  <tr>
					    <td><strong>&nbsp;</strong></td>
					  </tr>
				</table>
		</td>
	    	<td  width='50%' align='center'>
			Surakarta, ".format_tanggal_bln(date('Y-m-d'))."<br>
			Wakil Direktur I, <br><br><br><br><br>
			".$namaWadir."
		</td>
	  </tr>
	</table> </center>";
	
	
	$bimbingan=$header.$atas.$isiheader.$isi.$bawah;

$mpdf->WriteHTML($bimbingan);

$mpdf->Output('Laporan_Aktifitas_Bimbingan_'.$dataDosen->nidn.'_'.$kelas.'_'.date('d-m-Y').'.pdf', 'D');
?>