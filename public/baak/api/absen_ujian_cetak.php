<?php  
ob_start();
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

include "../lib/office/clsMsDocGenerator.php";

$doc = new clsMsDocGenerator('PORTRAIT','LETTER','',0.5,0.5,0.5,0.8);

$id_kls=$id;
try {
	$qryKelas="select nm_jenj_didik,nm_lemb,nm_kls,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,kode_mk, nm_mk from wsia_kelas_kuliah, wsia_sms, wsia_jenjang_pendidikan, wsia_mata_kuliah where wsia_kelas_kuliah.xid_kls='$id_kls' and wsia_mata_kuliah.xid_mk=wsia_kelas_kuliah.id_mk and wsia_sms.xid_sms=wsia_kelas_kuliah.id_sms and wsia_jenjang_pendidikan.id_jenj_didik=wsia_sms.id_jenj_didik";
    $db 	= koneksi();
    $qry 	= $db->prepare($qryKelas); 
    $qry->execute();
    $dataKelas	= $qry->fetch(PDO::FETCH_OBJ);
    
    //ajar dosen
    $qryAjarDosen = "select nidn,nm_ptk, gelar_depan,gelar_belakang from wsia_ajar_dosen,wsia_dosen_pt,wsia_dosen,wsia_kelas_kuliah where wsia_kelas_kuliah.xid_kls=wsia_ajar_dosen.id_kls and wsia_dosen_pt.xid_reg_ptk=wsia_ajar_dosen.id_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_ajar_dosen.id_kls='$id_kls'";
    $qry 	= $db->prepare($qryAjarDosen); 
    $qry->execute();
  
    $dataAjarDosen	= $qry->fetch(PDO::FETCH_OBJ);
    
    
    $db		= null;
    $isi="";
} catch (PDOException $salah) {
   $isi="Ada kesalahan saat pengambilan data kelas";
}

if ($isi=="") {
	$th_awal=substr($dataKelas->id_smt,0,4);
	$th_akhir=$th_awal+1;
	if (substr($dataKelas->id_smt,4,1)=="1") {
		$smt="GANJIL";
	} else {
		$smt="GENAP";
	}
 
 	if ($aksi=="uts") {
	   $vujian="UJIAN TENGAH SEMESTER"; 
       $kolom="";
       $baris="";
       $file="ABSEN-UTS_".str_replace(" ","-",$dataKelas->nm_lemb."_".$dataKelas->nm_mk)."(".$dataKelas->id_smt.")";
	} elseif ($aksi=="uas") {
	   $vujian="UJIAN AKHIR SEMESTER"; 
       $kolom="<td style='font-size:8pt;' align='center' rowspan='2' width='35'>Absensi</td>
              <td style='font-size:8pt;' align='center' rowspan='2' width='35'>Tugas</td>
              <td style='font-size:8pt;' align='center' rowspan='2' width='35'>UTS</td>
              <td style='font-size:8pt;' align='center' rowspan='2' width='35'>UAS</td>";
      $baris="<td></td><td></td><td></td><td></td>";
      $file="ABSEN-UAS_".str_replace(" ","-",$dataKelas->nm_lemb."_".$dataKelas->nm_mk)."(".$dataKelas->id_smt.")";
	} else {
		exit();
	}

	$header="
	<table width='730' style='font-size:11pt; font-family:Arial; color:#000;'>
	  <tr><td align='center'>
	  <h4>PRESENSI ".$vujian."<br>
	      SEMESTER ".$smt." TAHUN AKADEMIK ".$th_awal."/".$th_akhir." <br>
	      PROGRAM STUDI ".strtoupper($dataKelas->nm_jenj_didik)." - ".strtoupper($dataKelas->nm_lemb)."<br>
	      POLITEKNIK INDONUSA SURAKARTA</h4> </td>
	  </tr>
	</table>";


	$atas="<br>
	<table width='730' style='font-size:10pt;  font-family:Arial; color:#000;'>
	  <tr>
	    <td width='100'>Kode</td>
	    <td width='350'>: ".$dataKelas->kode_mk."</td>
		<td></td>
		<td width='100'>Hari/Tanggal</td>
	    <td>: ..................................</td>
	  </tr>
	  <tr>
	    <td>Mata Kuliah</td>
	    <td>: ".$dataKelas->nm_mk."</td>
		<td></td>
		<td>Ruang</td>
	    <td>: ..................................</td>
	  </tr>
	  <tr>
	    <td>SKS</td>
	    <td>: ".$dataKelas->vsks_mk."</td>
		<td></td>
		<td>Waktu</td>
	    <td>: ............................. WIB</td>
	  </tr>
	  <tr>
	    <td>Nama Dosen</td>
	    <td>: ".$dataAjarDosen->gelar_depan." ".$dataAjarDosen->nm_ptk.", ".$dataAjarDosen->gelar_belakang."</td>
		<td></td>
		<td>Kelas</td>
	    <td>: ".$dataKelas->nm_kls."</td>
	  </tr>
	</table>";

	$isiHeader="
	<table width='730' border='1' bordercolor='#000' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000;'>
	 <tr>
	    <td width='25' rowspan='2'  align='center' valign='middle'>NO</td>
	    <td width='80' rowspan='2' align='center' valign='middle'>NIM</td>
	    <td width='230' rowspan='2'  align='center' valign='middle'>NAMA MAHASISWA</td>
	    ".$kolom."
	    <td colspan='2' align='center' valign='middle'>Nilai</td>
	    <td colspan='2' rowspan='2' align='center' valign='middle'>TANDA TANGAN MAHASISWA</td>
	  </tr>
	  <tr>
	    <td align='middle' width='50'>Angka</td>
	    <td align='middle' width='50'>Huruf</td>
	  </tr>
	  ";
	
	$qryMhsKrs = "select wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd, jk, nm_jenj_didik,nm_lemb, left(mulai_smt,4) as angkatan from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls='$id_kls' and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik order by nipd asc;";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($qryMhsKrs); 
		    $qry->execute();
		  
		    $dataMhsKrs	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $n=0; 
		    $aData=array();
		    foreach ($dataMhsKrs as $itemData) {
				$n++;
				
				/* JIKA BELUM ADA NIM UTK MHS BARU
				if (substr($itemData->vid_reg_pd,0,4)=="2016") {
					$nim=$itemData->vid_reg_pd;
				} else {
					$nim=$itemData->nipd;
				}
				*/
				$nim=$itemData->nipd;
				$isi.="<tr height='20'>
				    <td align='center' valign='middle'>".$n."</td>
				    <td align='center' valign='middle'>".$itemData->nipd."</td>
				    <td valign='middle'>".$itemData->nm_pd."</td>
				    ".$baris."
				    <td align='middle'></td>
				    <td align='middle'></td> ";
				 
				 if ($n%2==1){
				 	$isi.="<td>".$n."<br><br></td>  <td><br><br></td>  </tr>";
				 } else {
				 	$isi.="<td><br><br></td>  <td>".$n."<br><br></td>  </tr>"; 
				 }
				
			}
		    
	  } catch (PDOException $salah) {
		   $isi.="Kesalahan mengambil data mahasiswa";
	  }
	
	$isiFooter="</table>";
	
	$ttd="<table width='730'>
	  <tr>
	    <td>
	 	<table align='right' style='font-size:10pt;  font-family:Arial; color:#000;'>
	 	  <tr>
	 		<td align='center'><br><br>
	         Surakarta, ....... ........................ .............<br>
			 Dosen Pengampu,<br><br><br><br>
			 ".$dataAjarDosen->gelar_depan." ".$dataAjarDosen->nm_ptk.", ".$dataAjarDosen->gelar_belakang."
	        </td>
	      </tr>
	    </table>
	    </td>
	  </tr></table>";

	
	$absen=$header.$atas.$isiHeader.$isi.$isiFooter.$ttd;
	//echo $absen;
	
	$doc->addParagraph($absen);
	$doc->output($file.".doc");

} else {
	exit("Tidak ada data");
}

?>