<?php
error_reporting(0);
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

include "../lib/pdf/mpdf.php";

$mpdf=new mPDF('-s','Legal');
$mpdf->setHTMLFooter('<div style="text-align:right;">Transkip Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
$mpdf->AddPage('P'.'','','','',5,5,5,5,5,5);
$stylesheet = file_get_contents('../lib/pdf/transkip.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetWatermarkText('POLINUS COPY');
$mpdf->showWatermarkText = true;
$mpdf->watermarkTextAlpha = 0.1;


$xid_reg_pd = $_SESSION['xid_reg_pd'];

$header="
	<table width='100%' align='left'>
	 <tr>
	    <td rowspan='2' align='center' width='95' valign='center'><img src='../gambar/logo_pt.jpg' height='90'></td>
	    <td align='left' valign='bottom'><h3>POLITEKNIK INDONUSA SURAKARTA</h3></td>
	  </tr>
	  <tr>
	    <td align='left' valign='top'>
	    	Kampus 1: Jl. KH. Samanhudi No.31, Laweyan Surakarta, Telp: 0271 - 743479<br>
			Kampus 2: Jl. Palem No.8 Cemani, Grogol, Sukoharjo, Telp: 0271 - 7464173
	    </td>
	  </tr>
	  <tr>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td align='center' colspan='2'><h3><u>TRANSKIP NILAI ONLINE</u></h3> </td>
	  </tr>
	</table>";


$Transkip="Tidak ada data";
// $qryMhsKrs = "select id_smt,wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,tmpt_lahir,tgl_lahir,tgl_keluar,xid_sms,nm_jenj_didik,nm_lemb,nm_lemb_en,pa,kode_mk,nm_mk,nm_mk_en,wsia_kelas_kuliah.sks_mk as vsks_mk,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' order by wsia_kelas_kuliah.id_smt,kode_mk asc";
$qryMhsKrs = "select id_smt,wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,tmpt_lahir,tgl_lahir,tgl_keluar,xid_sms,nm_jenj_didik,nm_lemb,nm_lemb_en,pa,kode_mk,nm_mk,nm_mk_en,wsia_kelas_kuliah.sks_mk as vsks_mk,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' and wsia_nilai.nilai_tampil = '3' order by wsia_kelas_kuliah.id_smt,kode_mk asc";
 try {
	    	$db 	= koneksi();
	    	$qry 	= $db->prepare($qryMhsKrs); 
	   	$qry->execute();
	    	$dataMhsKrs	= $qry->fetch(PDO::FETCH_OBJ);
	    	if ($qry->rowCount()==0) {
			$adaData=0;
		} else {
			$adaData=1;
		}
	    	
	    	$id_ptk=$dataMhsKrs->pa;
	    	$sqlPa="select * from wsia_dosen where id_ptk='$id_ptk'";
	    	$qryPa = $db->prepare($sqlPa);
	    	$qryPa->execute();
	    	$dataPa = $qryPa->fetch(PDO::FETCH_OBJ);
	    	if ($qryPa->rowCount()>0) {
			$pa=$dataPa->gelar_depan." ".$dataPa->nm_ptk.", ".$dataPa->gelar_belakang;
		} else {
			$pa="-";
		}
} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
}	    	

$atas="
	<table border='0' align='left'>
	  <tr>
		<td align='right' width='390'>Program Studi</td>
		<td width='1'>:</td>
		<td align='left' width='390'>".ucwords(strtolower($dataMhsKrs->nm_lemb))."</td>
	  </tr>
	  <tr>
		<td align='right'><i>Study Program</i></td>
		<td width='1'>:</td>
		<td align='left'><i>".ucwords(strtolower($dataMhsKrs->nm_lemb_en))."</i></td>
	  </tr>
	  <tr>
		<td align='right'>Jenjang Pendidikan/ <i>Level of Education</i></td>
		<td width='1'>:</td>
		<td align='left'>".$dataMhsKrs->nm_jenj_didik."</td>
	  </tr>
	</table>
	<br>
	<table border='0' align='left'>
	 <tr>
	    <td width='130'>Nama Mahasiswa<br><i>Name of Student</i></td>
	    <td width='450'>: ".$dataMhsKrs->nm_pd." </td>
	    <td width='130'>Tanggal Lulus<br><i>Date of Graduation</i></td>
	    <td width='150'>: ".(($dataMhsKrs->tgl_keluar=="0000-00-00")?"Belum Lulus":format_tanggal_bln($dataMhsKrs->tgl_keluar))." </td>
	  </tr>
	  <tr>
	    <td>Tempat Tanggal Lahir<br><i>Place and date of birth</i></td>
	    <td>: ".ucwords(strtolower($dataMhsKrs->tmpt_lahir)).", ".(($dataMhsKrs->tgl_lahir=="0000-00-00")?"Belum Diisi":format_tanggal_bln($dataMhsKrs->tgl_lahir))." </td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>Nomor Mahasiswa<br><i>Identification Number</i></td>
	    <td>: ".$dataMhsKrs->nipd." </td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	</table>";

	$isiheader="<center>
		<table width='100%' repeat-header='1' class='transkip'>
		<thead>
		  <tr class='judul'>
		    <th align='center' valign='middle' width='15'>NO<br><i>No</i></th>
		    <th align='center' valign='middle' width='15'>KODE<br><i>Code</i></th>
		    <th align='center' valign='middle' width='500'>MATA KULIAH<br><i>Course</i></th>    
		    <th align='center' valign='middle' width='15'>SKS<br><i>Credit</i></th>
		    <th align='center' valign='middle' width='15'>NILAI<br><i>Grade</i></th>
		  </tr>
		  </thead>
		  <tbody>";
		  
	
	$qry 	= $db->prepare($qryMhsKrs); 
	$qry->execute();
	$dataNilai	= $qry->fetchAll(PDO::FETCH_OBJ);
	
	$isi="";
	$n=0;	
	$jsks=0;
	$jnXsks=0;
	$ipK=0;
	$id_smt="";
	foreach ($dataNilai as $itemNilai) {
		$n++;
	    	$jsks=$jsks+$itemNilai->vsks_mk;
		$na=$itemNilai->nilai_indeks*$itemNilai->vsks_mk;	
		$jnXsks=$jnXsks+$na;
		if ($jsks>0) {
			$ipK=$jnXsks/$jsks;
		} else {
			$ipK=0;
		}
		
		if ($id_smt!=$itemNilai->id_smt) {
			$cssSmt=" class='batasSmt' ";
			$id_smt=$itemNilai->id_smt;
		} else {
			$cssSmt="";
		}
		
		$isi.="
		  <tr height='20' $cssSmt>
		    <td align='center' valign='top'>".$n."</td>
		    <td align='center' valign='top'>".$itemNilai->kode_mk."</td>
		    <td valign='top'>".ucwords(strtolower($itemNilai->nm_mk))."<br><i>".ucwords(strtolower($itemNilai->nm_mk_en))."</i></td>    
		    <td align='center' valign='top'>".$itemNilai->vsks_mk."</td>
		    <td align='center' valign='top'>".$itemNilai->nilai_huruf."</td>
		  </tr>
		";
	}
	
	$isi.="
	  <tr class='footer'>
		    <td colspan='3' align='right'>Total SKS</td>    
		    <td align='center'>".$jsks."</td>
		    <td>&nbsp;</td>    
	  </tr>
	  <tr class='footer'>
		    <td colspan='3' align='right'>Index Prestasi Kumulatif</td>    
		    <td align='center'>".number_format($ipK,2)."</td>
		    <td>&nbsp;</td>    
	  </tr>";
	
	$bawah=" </tbody></table>
		<br>
		</center>
		<p>Tanggal Diunduh: ".format_tanggal_waktu(date('Y-m-d H:i:s'))."</p>
		<p>Transkip ini semata-mata hanya digunakan untuk kepentingan melihat nilai secara keseluruhan.<br>Transkip Nilai yang resmi berasal dari BAAK</p>
		";
		
	
if ($adaData) {
	$Transkip=$header.$atas.$isiheader.$isi.$bawah;
} else {
	$Transkip=$header."Tidak ada data nilai";
}

$db=null;
$mpdf->WriteHTML($Transkip);
	
$mpdf->Output('TRANSKIP_'.$_SESSION['nipd'].'.pdf','D');

?>
