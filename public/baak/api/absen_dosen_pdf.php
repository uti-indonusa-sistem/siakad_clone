<?php 
 if (!isset($key)) { exit(); }
 include 'login_auth.php';
 if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

 include "../lib/pdf/mpdf.php";
	 
 $xid_ajar=$id;
 
 try {
	$qryAjarDosen = "select nidn,nm_ptk, gelar_depan,gelar_belakang,sks_subst_tot,wsia_kelas_kuliah.sks_mk,hari,jam,ruang,id_smt,nm_kls,concat(nm_jenj_didik,'-',nm_lemb) as prodi, nm_mk from wsia_ajar_dosen,wsia_dosen_pt,wsia_dosen,wsia_kelas_kuliah,wsia_sms,wsia_jenjang_pendidikan, wsia_mata_kuliah where wsia_kelas_kuliah.xid_kls=wsia_ajar_dosen.id_kls and wsia_sms.xid_sms=wsia_kelas_kuliah.id_sms and wsia_jenjang_pendidikan.id_jenj_didik=wsia_sms.id_jenj_didik and wsia_mata_kuliah.xid_mk=wsia_kelas_kuliah.id_mk and wsia_dosen_pt.xid_reg_ptk=wsia_ajar_dosen.id_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and xid_ajar='$xid_ajar'";
    $db 	= koneksi();
    $qry 	= $db->prepare($qryAjarDosen); 
    $qry->execute();
  
    $dataAjarDosen	= $qry->fetch(PDO::FETCH_OBJ);
    $db		= null;
    //echo json_encode($dataAjarDosen);
    $isi="";
} catch (PDOException $salah) {
   $isi="Ada kesalahan saat pengambilan data ajar dosen<br>".$salah;
}

$mpdf=new mPDF('-s','Legal');  
//$mpdf->SetHTMLHeader("");
//$mpdf->setHTMLFooter('<div style="text-align:right;">{PAGENO} / {nbpg}</div>');
$mpdf->AddPage('P'.'','','','',5,5,5,5,5,5);
$stylesheet = file_get_contents('../lib/pdf/absen.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->SetDisplayMode('fullpage');

if ($isi=="") {
	$th_awal=substr($dataAjarDosen->id_smt,0,4);
	$th_akhir=$th_awal+1;
	if (substr($dataAjarDosen->id_smt,4,1)=="1") {
		$smt="GANJIL";
	} else {
		$smt="GENAP";
	}
	
	$header="
	<table width='100%'>
	  <tr>
	    <td align='left'><H3>POLITEKNIK INDONUSA SURAKARTA</H3></td>
	  </tr>
	  <tr>
	    <td align='left'>
	    	Kampus 1: Jl. KH. Samanhudi No.31, Laweyan Surakarta, Telp: 0271 - 743479<br>
			Kampus 2: Jl. Palem No.8 Cemani, Grogol, Sukoharjo, Telp: 0271 - 7464173
	    </td>
	  </tr>
	  
	  <tr>
	    
	    <td align='center'><BR><h3>JURNAL KULIAH SEMESTER ".$smt." TAHUN AKADEMIK ".$th_awal."/".$th_akhir."</h3> </td>
	    
	  </tr>
	</table>";


	$atas="
	<table width='100%' class='jurnalDosen'>
	  <tr>
	    <td width='180'><strong>Program Studi/ Kelas</strong> </td>
	    <td>: ".$dataAjarDosen->prodi." / ".$dataAjarDosen->nm_kls."</td>
	  </tr>
	  <tr>
	    <td><strong>Mata Kuliah</strong> </td>
	    <td>: ".$dataAjarDosen->nm_mk."</td>
	  </tr>
	  <tr>
	    <td><strong>SKS</strong></td>
	    <td>: ".$dataAjarDosen->sks_mk."</td>
	  </tr>
	  <tr>
	    <td><strong>Pengampu</strong></td>
	    <td>: ".$dataAjarDosen->gelar_depan." ".$dataAjarDosen->nm_ptk.", ".$dataAjarDosen->gelar_belakang."</td>
	  </tr>
	  <tr>
	    <td><strong>Jam/ Ruang</strong> </td>
	    <td>: ".$dataAjarDosen->jam." / ".$dataAjarDosen->ruang."</td>
	  </tr>
	</table>";

	$isiHeader="
	<table width='100%' border='1' class='jurnal'>
	  <tr bgcolor='#CCCCCC'>
	    <td width='1' rowspan='2' align='center' valign='middle'><b>NO</b></td>
	    <td width='80' rowspan='2' align='center' valign='middle'><b>TANGGAL</b></td>
	    <td width='400' rowspan='2' align='center' valign='middle'><b>MATERI</b></td>
	    <td colspan='2' align='center' valign='middle'><b>TANDA TANGAN </b></td>
	  </tr>
	  <tr bgcolor='#CCCCCC'>
	    <td width='70' align='center' valign='middle'><b>DOSEN</b></td>
	    <td width='70' align='center' valign='middle'><b>AKADEMIK</b></td>
	  </tr>";
	$isi="";
	for ($i=1;$i<=16;$i++) {
	$isi.="
	  <tr height='50'>
	    <td align='center' valign='middle'>".$i."</td>
	    <td><br><br><br><br><br></td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>";
	  }
	  
	$isiFooter="
	</table>";

}

$mpdf->WriteHTML($header.$atas.$isiHeader.$isi.$isiFooter);
 
//echo $header.$atas.$isiHeader.$isi.$isiFooter;
$mpdf->Output('ABSEN-DOSEN-'.$dataAjarDosen->nm_ptk.'-'.$dataAjarDosen->nm_mk.'.pdf','D');

?>