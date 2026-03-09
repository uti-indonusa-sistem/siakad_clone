<?php 
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

include "../lib/pdf/mpdf.php";
 
$id_kls=$id;
try {
	$qryKelas="select nm_jenj_didik,nm_lemb,nm_kls,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,kode_mk, nm_mk from wsia_kelas_kuliah, wsia_sms, wsia_jenjang_pendidikan, wsia_mata_kuliah where wsia_kelas_kuliah.xid_kls='$id_kls' and wsia_mata_kuliah.xid_mk=wsia_kelas_kuliah.id_mk and wsia_sms.xid_sms=wsia_kelas_kuliah.id_sms and wsia_jenjang_pendidikan.id_jenj_didik=wsia_sms.id_jenj_didik";
    $db 	= koneksi();
    $qry 	= $db->prepare($qryKelas); 
    $qry->execute();
  
    $dataKelas	= $qry->fetch(PDO::FETCH_OBJ);
    $db		= null;
    $isi="";
} catch (PDOException $salah) {
   $isi="Ada kesalahan saat pengambilan data kelas";
}


$mpdf=new mPDF('-s','Legal');  
//$mpdf->SetHTMLHeader("");
//$mpdf->setHTMLFooter('<div style="text-align:right;">{PAGENO} / {nbpg}</div>');
$mpdf->AddPage('L'.'','','','',5,10,15,30,10,5);
$stylesheet = file_get_contents('../lib/pdf/absen.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->SetDisplayMode('fullpage');

if ($isi=="") {
	$th_awal=substr($dataKelas->id_smt,0,4);
	$th_akhir=$th_awal+1;
	if (substr($dataKelas->id_smt,4,1)=="1") {
		$smt="GANJIL";
	} else {
		$smt="GENAP";
	}
	$header="
	<table width='92%' align='right'>
	  <tr><td align='center'>
	  <h3>PRESENSI MAHASISWA POLITEKNIK INDONUSA SURAKARTA</h3>
	  </td></tr>
	  <tr>   
	    <td align='center'><h3>SEMESTER ".$smt." TAHUN AKADEMIK ".$th_awal."/".$th_akhir."</h3> </td>
	  </tr>
	</table>";


	$atas="<br>
	<table width='100%' align='left'>
	  <tr>
	    <td width='180'><strong>Program Studi</strong> </td>
	    <td>: ".$dataKelas->nm_jenj_didik." - ".$dataKelas->nm_lemb."</td>
	  </tr>
	  <tr>
	    <td><strong>Mata Kuliah</strong> </td>
	    <td>: ".$dataKelas->kode_mk." - ".$dataKelas->nm_mk."</td>
	  </tr>
	  <tr>
	    <td><strong>SKS</strong></td>
	    <td>: ".$dataKelas->vsks_mk."</td>
	  </tr>
	  <tr>
	    <td><strong>Tahun Akademik</strong></td>
	    <td>: ".$th_awal."/".$th_akhir." ".$smt."</td>
	  </tr>
	  <tr>
	    <td><strong>Kelas</strong> </td>
	    <td>: ".$dataKelas->nm_kls."</td>
	  </tr>
	</table>";

	$isiHeader="
	<table width='100%' border='1' align='left' repeat_header='1'>
	 <thead>
	  <tr bgcolor='#CCCCCC'>
	    <th width='10' rowspan='2' align='center' valign='middle'><strong>NO</strong></th>
	    <th width='100' rowspan='2' align='center' valign='middle'><strong>NIM</strong></th>
	    <th width='250' rowspan='2' align='center' valign='middle'><strong>Nama Mahasiswa </strong></th>
	    <th colspan='16' align='center' valign='middle'><strong>Tanggal &amp; TTD Mahasiswa </strong></th>
	  </tr>
	  <tr bgcolor='#CCCCCC'>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	    <th>&nbsp;</th>
	  </tr>
	</thead>";
	$isi=""; 
	$n=0; 
	
	$qryMhsKrs = "select wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd, jk, nm_jenj_didik,nm_lemb, left(mulai_smt,4) as angkatan from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls='$id_kls' and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($qryMhsKrs); 
		    $qry->execute();
		  
		    $dataMhsKrs	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $aData=array();
		    foreach ($dataMhsKrs as $itemData) {
				$n++;
				$nim=$itemData->nipd;
				
				$isi.="
				  <tr>
				    <td align='center' valign='middle'>".$n."</td>
				    <td align='center' valign='middle'>".$nim."</td>
				    <td valign='middle'>".$itemData->nm_pd."</td>
				    <td><br><br><br></td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				  </tr>";
				
			}
		    
	  } catch (PDOException $salah) {
		   $isi.="Kesalahan mengambil data mahasiswa";
	  }
	
	
	 

	for ($m=$n+1;$m<=30;$m++) { 
	$n++;
	$isi.="
	  <tr>
	    <td align='center' valign='middle'>".$n."</td>
	    <td align='center' valign='middle'>&nbsp;</td>
	    <td valign='middle'>&nbsp;</td>
	    <td><br><br><br></td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>";
	  }

	  
	$isiFooter="
	</table>";
}

$mpdf->WriteHTML($header.$atas.$isiHeader.$isi.$isiFooter);
 
				
$mpdf->Output('ABSEN-MAHASISWA-'.$dataKelas->nm_lemb.'-'.$dataKelas->nm_mk.'.pdf','D');

?>