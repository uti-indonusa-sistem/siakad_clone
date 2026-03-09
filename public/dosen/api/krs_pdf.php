<?php 
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

include "../lib/pdf/mpdf.php";

$mpdf=new mPDF('-s','Legal');
$mpdf->setHTMLFooter('<div style="text-align:right;">KRS Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
$mpdf->AddPage('P'.'','','','',5,5,5,5,5,5);
$stylesheet = file_get_contents('../lib/pdf/krs.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetWatermarkText('POLINUS');
$mpdf->showWatermarkText = true;
$mpdf->watermarkTextAlpha = 0.1;

function kodeQR($nama,$key) {
    return crypt($nama,$key);
}

$id_smt=$_SESSION['id_smt_aktif'];

if ($id_smt!="-") {
	$tahun1=substr($id_smt,0,4);
	$tahun2=$tahun1+1;
	$smt=substr($id_smt,4,1);
	if ($smt=="1") {
		$vsmt="Ganjil";
	} else if ($smt=="2") {
		$vsmt="Genap";
	} else {
		$vsmt="Pendek";
	}
	$ta=$tahun1."/".$tahun2;
	$vid_smt=$tahun1."/".$tahun2." ".$vsmt;
} else {
	$vsmt="";
	$ta="";
	$vid_smt="";
}

$xid_reg_pd=$_SESSION['xid_reg_pd'];	
$nipd=$_SESSION['nipd'];
$nm_pd=$_SESSION['nm_pd'];

$dataQR=$nipd."#".$nm_pd."#".$vid_smt;
$qrcode="<barcode code='".$dataQR."' type='QR' class='barcode' size='1.5' error='L' />";
			

	$qryMhs = "select * from wsia_mahasiswa_pt,wsia_mahasiswa,wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and xid_reg_pd='$xid_reg_pd'";
	try {
	    $db 	= koneksi();
	    $qry 	= $db->prepare($qryMhs); 
	    $qry->execute();
	    $dataMhs	= $qry->fetch(PDO::FETCH_OBJ);
	    
	    $id_ptk=$dataMhs->pa;
	    $sqlPa="select * from wsia_dosen where id_ptk='$id_ptk'";
	    $qryPa = $db->prepare($sqlPa);
	    $qryPa->execute();
	    $dataPa = $qryPa->fetch(PDO::FETCH_OBJ);
	    if ($qryPa->rowCount()>0) {
		$pa=$dataPa->gelar_depan." ".$dataPa->nm_ptk.", ".$dataPa->gelar_belakang;
	    } else {
		$pa="-";
	   }
	    
	    $db=null;
	} catch (PDOException $salah) {
		exit (json_encode($salah->getMessage()));
	}
		

	//cek foto
	if (file_exists("foto/".md5($dataMhs->nipd).".jpg")) {
		$foto_mhs = "foto/".md5($dataMhs->nipd).".jpg";
	} else {
		$foto_mhs = "../gambar/no-foto.jpg";
	}

	$header="<table width='100%' border='0'>
				<tr>
					<td rowspan='5' align='center'><img src='../gambar/logo_pt.jpg' height='110'></td>
					<td align='center'><h3>POLITEKNIK INDONUSA SURAKARTA</h3></td>
					<td rowspan='5' align='left'><img src='".$foto_mhs."' height='130'></td>
				</tr>
				<tr>
				    <td align='center'>
				    	Kampus 1: Jl. KH. Samanhudi No.31, Laweyan Surakarta, Telp: 0271 - 743479<br>
						Kampus 2: Jl. Palem No.8 Cemani, Grogol, Sukoharjo, Telp: 0271 - 7464173
				    </td>
				</tr>  
				<tr>    
				    <td>&nbsp;</td>
				</tr>";


	$atas="<table width='100%' border='0'>  
	  <tr>
		<td width='70'>No.Daftar</td>
		<td>: ".$dataMhs->xid_reg_pd." </td>
		<td>&nbsp;</td>
		<td width='150'>Semester</td>
		<td>: ".$vsmt." </td>
	  </tr>
	  <tr>
		<td>Nama</td>
		<td>: ".$dataMhs->nm_pd." </td>
		<td>&nbsp;</td>
		<td width='150'>Semester</td>
		<td>: ".$ta." </td>
	  </tr>
	  <tr>
	    <td>NIM</td>
	    <td>: ".$dataMhs->nipd." </td>
	    <td>&nbsp;</td>
	    <td>Pembimbing Akademik</td>
	    <td>: ".$pa." </td>
	  </tr>
	</table>";

	$header.="<tr>
			    <td align='center'>
			    	<h3 class='judulKrs'>Kartu Rencana Studi</h3>
			        ".$dataMhs->nm_jenj_didik." - ".$dataMhs->nm_lemb."
			    </td>
			</tr>
			<tr>    
			    <td>&nbsp;</td>
			</tr>
		 </table>";   

	$isiheader="<table width='100%' border='1'>
				<tr bgcolor='#CCCCCC'>
				  <th rowspan='2' align='center' valign='middle' width='20'>No</th>
				  <th rowspan='2' align='center' valign='middle' width='90'>Kode<br>Mata Kuliah</th>
				  <th rowspan='2' align='center' valign='middle'>Mata Kuliah</th>
				  <th rowspan='2' align='center' valign='middle' width='20'>Jml<br>SKS</th>
				  <th colspan='3' align='center' valign='middle'>Komposisi SKS</th>
				  <th rowspan='2' align='center' valign='middle'>Dosen Pengajar</th>
				 </tr>
				 <tr bgcolor='#CCCCCC'>
				   <th align='center' valign='middle' width='60'>T</th>
				   <th align='center' valign='middle' width='60'>P</th>
				   <th align='center' valign='middle' width='60'>K</th>
				 </tr>";
	  				 
	$isi="";

	$qryNilai = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";
	try {
	    $db 	= koneksi();
	    $qry 	= $db->prepare($qryNilai); 
	    $qry->execute();
	    $dataNilai	= $qry->fetchAll(PDO::FETCH_OBJ);
	    $db		= null;
	} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
	}
		
	$n=0;
	$jsks=0;
	$jskst=0;
	$jsksp=0;
	$jsksk=0;
	foreach ($dataNilai as $itemNilai) {	
		  $n++;
		  $jsks=$jsks+$itemNilai->vsks_mk;
		  $jskst=$jskst+$itemNilai->vsks_tm;
		  $jsksp=$jsksp+$itemNilai->vsks_prak;	
		  $jsksk=$jsksk+$itemNilai->vsks_prak_lap;	
		  $vid_kls=$itemNilai->vid_kls;
		  
		  $sqlPengampu="select xid_ptk,xid_reg_ptk,concat(gelar_depan,nm_ptk,', ',gelar_belakang) as dosen_pengampu from wsia_ajar_dosen,wsia_dosen,wsia_dosen_pt where wsia_ajar_dosen.id_reg_ptk=wsia_dosen_pt.xid_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_ajar_dosen.id_kls='$vid_kls' ";
		  
		  try {
			    $db 	= koneksi();
			    $qryPengampu 	= $db->prepare($sqlPengampu); 
			    $qryPengampu->execute();
			    $dataPengampu	= $qryPengampu->fetchAll(PDO::FETCH_OBJ);
			    $jPengampu=$qryPengampu->rowCount();
			    $db		= null;
		  } catch (PDOException $salah) {
			   exit(json_encode($salah->getMessage()));
		  }
		  
		  $isi.="<tr>
			           <td align='center' valign='top' rowspan='".$jPengampu."'>".$n."</td>
			           <td align='center' valign='top' rowspan='".$jPengampu."'>".$itemNilai->kode_mk."</td>
			           <td align='left' valign='top' rowspan='".$jPengampu."'>".$itemNilai->nm_mk."</td>
			           <td align='center' valign='top' rowspan='".$jPengampu."'>".$itemNilai->vsks_mk."</td>
			           <td align='center' valign='top' rowspan='".$jPengampu."'>".$itemNilai->vsks_tm."</td>
			           <td align='center' valign='top' rowspan='".$jPengampu."'>".$itemNilai->vsks_prak."</td>
			           <td align='center' valign='top' rowspan='".$jPengampu."'>".$itemNilai->vsks_prak_lap."</td>";
		  
		  if ($jPengampu>0) {
		  	$iPengampu=0;
			foreach ($dataPengampu as $itemPengampu) {
				if ($iPengampu==0) {
					 $isi.="<td>".$itemPengampu->dosen_pengampu."</td></tr>";
				} else {
					$isi.="<tr><td>".$itemPengampu->dosen_pengampu."</td></tr>";
				}
				$iPengampu++;
		  	}
		  } else {
			$isi.="<td>-</td></tr>";
		  }
		  
		  
		  
		  
	}

	for ($j=$n;$j<12;$j++) {    
	   $isi.="<tr>
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
	$isi.="<tr>
			     <td colspan='3' align='right'>Total SKS</td>
			     <td align='center'>".$jsks."</td>
			     <td align='center'>".$jskst."</td>
			     <td align='center'>".$jsksp."</td>
			     <td align='center'>".$jsksk."</td>
			     <td>&nbsp;</td>
	     	  </tr>";
	$bawah="</table>
			<table width='90%' border='0'>
				<tr>
				  <td width='240' align='center'>Mengetahui,</td>
				  <td></td>
				  <td  width='240' align='center'>Surakarta, ".format_tanggal(date('Y-m-d'))."</td>
				</tr>
				<tr>
				  <td align='center'>Dosen Pembimbing </td>
				  <td></td>
				  <td align='center'>Mahasiswa</td>
				</tr>
				<tr>
				  <td>&nbsp;<br><br></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td align='center'><br><b>".$pa."</b></td>
				  <td>&nbsp;</td>
				  <td align='center'> <br><b>".$dataMhs->nm_pd."</b></td>
				</tr>
			</table>";
			
	$isiKrs=$atas.$isiheader.$isi.$bawah;

	$mpdf->WriteHTML($header.$isiKrs);

$mpdf->Output('KRS_'.$id_smt.'_'.$dataMhs->nipd.'.pdf','D');

?>