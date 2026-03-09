<?php
 if (!isset($key)) { exit("Key API Salah"); }
 include 'login_auth.php';
 if ($key!=$_SESSION['wsiaADMIN']) { exit("Akses Ditolak"); }

 include "lib/pdf/mpdf.php";
 
$mpdf=new mPDF('-s','Legal');  
//$mpdf->SetHTMLHeader("");
//$mpdf->setHTMLFooter('<div style="text-align:right;">{PAGENO} / {nbpg}</div>');
$mpdf->AddPage('P'.'','','','',5,5,5,5,5,5);
$stylesheet = file_get_contents('lib/pdf/absen.css');
$mpdf->WriteHTML($stylesheet,1);
$mpdf->SetDisplayMode('fullpage');

/* FUNGSI UNTUK QRCODE */
function kodeQR($nama,$key) {
    return crypt($nama,$key);
}

$id_smt=$_SESSION['ta'];
$no_pend = $data->no_pend;

if (count($no_pend)>0) {
	
	$th_awal=substr($id_smt,0,4);
	$th_akhir=$th_awal+1;
	if (substr($id_smt,4,1)=="1") {
		$smt="GANJIL";
	} else {
		$smt="GENAP";
	}
	
	$header="
	<table width='100%' >
	  <tr>
	    <td rowspan='4' align='center'><img src='gambar/logo_versi2.png' height='80' align='right'></td>
	    <td align='left'>SEKOLAH TINGGI MANAJEMEN INFORMATIKA DAN KOMPUTER </td>
	    <td rowspan='5' align='center'>";

	$header1="</td>
	  </tr>
	  <tr>
	    <td align='left'><h2>STMIK DUTA BANGSA SURAKARTA </h2></td>
	  </tr>
	  <tr>
	    <td align='left'>Jl. Bhayangkara No. 55 Telp. 0271-719552 web: http://www.stmikdb.ac.id </td>
	  </tr>
	<tr>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>

	  ";
	
	$i=0;
	foreach ($no_pend as $itemNo_pend) {
	  $i++;
	  $qryMhsKrs = "select wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,no_pend,nm_jenj_didik,nm_lemb from wsia_nilai,mahasiswa,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and mahasiswa.no_pend=wsia_nilai.xid_reg_pd and trim(wsia_mahasiswa_pt.nipd)=trim(mahasiswa.nim) and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and mahasiswa.no_pend='$itemNo_pend' and wsia_kelas_kuliah.id_smt='$id_smt' group by mahasiswa.no_pend";
	  try {
		    	$db 	= koneksi();
		    	$qry 	= $db->prepare($qryMhsKrs); 
		   	$qry->execute();
		    	$dataMhsKrs	= $qry->fetch(PDO::FETCH_OBJ);
		    	$db		= null;
		    
			$ujian="KARTU HASIL STUDI";

			$header2="
			  <tr>
			    <td>&nbsp;</td>
			    <td align='center'><h3>".$ujian."<br> SEMESTER ".$smt." TAHUN AKADEMIK ".$th_awal."/".$th_akhir."</h3> </td>
			    <td>&nbsp;</td>
			  </tr>
			</table>";  	

			/* KARTU UJIAN QR */
			$keyQR=str_replace("/", "", kodeQR($dataMhsKrs->nipd,md5("KARTU:".$id_smt)) );
			$dataQR="http://siakad.stmikdb.ac.id/".$aksi."/".$dataMhsKrs->nipd."/".$id_smt."~".strrev($keyQR);
			$qrcode="<barcode code='".$dataQR."' type='QR' class='barcode' size='1.5' error='L' />";
				
			$isikiri="
			<table width='100%'>
			      <tr>
			        <td>Progdi</td>
			        <td width='1'>:</td>
			        <td>".$dataMhsKrs->nm_jenj_didik."-".$dataMhsKrs->nm_lemb."</td>
			      </tr>
			      <tr>
			        <td>No. Daftar</td>
			        <td>:</td>
			        <td>".$dataMhsKrs->no_pend."</td>
			      </tr>
			      <tr>
			        <td>Nama</td>
			        <td>:</td>
			        <td>".$dataMhsKrs->nm_pd."</td>
			      </tr>
			      <tr>
			        <td>NIM</td>
			        <td>:</td>
			        <td>".$dataMhsKrs->nipd."</td>
			      </tr>
			      <tr>
			        <td>Password</td>
			        <td>:</td>
			        <td>".$dataMhsKrs->no_pend."</td>
			      </tr>
			      <tr>
			        <td>Status</td>
			        <td>:</td>
			        <td>".$id."</td>
			      </tr>
			      <tr>
			        <td>&nbsp;</td>
			        <td>&nbsp;</td>
			        <td>&nbsp;</td>
			      </tr>
			      <tr>
			        <td align='center' colspan='3'>Wakil Ketua I,<br>
			          <br>
			          ttd<br>
			          <br>
			          Wijiyanto, S.Kom, M.Pd, M.Kom</td>
			      </tr>
			      
			    </table>";
		
			$qryNilai = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='".$dataMhsKrs->no_pend."' order by nm_mk asc";
			try {
			    $db 	= koneksi();
			    $qry 	= $db->prepare($qryNilai); 
			    $qry->execute();
			    $dataNilai	= $qry->fetchAll(PDO::FETCH_OBJ);
			    $db		= null;
			} catch (PDOException $salah) {
			   exit(json_encode($salah->getMessage()));
			}
			
			$isitengah="<table border='0' bordercolor='#000' cellpadding='5' cellspacing='0' class='kartuUjianKanan' >
						<tr class='matkulJudulKartu'>
						<td align='center' colspan='2' class='matkulKartu'>Mata Kuliah</td>
		    				<td align='center' colspan='2'>Tanda Tangan Pengawas</td>
						</tr>
						<tr><td>&nbsp;</td><td class='matkulKartu'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
			$n=0;
			foreach ($dataNilai as $itemNilai) {
				$n++;
				$isitengah.="
				  <tr>
				    <td>".$n."</td>
				    <td class='matkulKartu'>".$itemNilai->nm_mk."</td>";
				  
					if ($n%2!=0) {
						$isitengah.="<td>".$n." ....................</td><td>&nbsp;</td>";
					}   else {
						$isitengah.="<td>&nbsp;</td><td>".$n." ....................</td>";
					}
				       
				$isitengah.="</tr>";
			}

			for ($j=$n+1;$j<=10;$j++) {
				$isitengah.="
				  <tr>
				    <td>&nbsp;</td>
				    <td class='matkulKartu'>&nbsp;</td>";
				  
				if ($j%2!=0) {
					$isitengah.="<td>".$j." ....................</td><td>&nbsp;</td>";
				}   else {
					$isitengah.="<td>&nbsp;</td><td>".$j." ....................</td>";
				}
				       
				$isitengah.="</tr>";
			}
			$isitengah.="</table>";

			$kartu="
			<table width='100%' border='1' cellspacing='0' cellpadding='0' bordercolor='#000000' class='kartuUjian'>
			  <tr>
			    <td align='center' valign='middle'>
				".$isikiri."
			    </td>
			    <td align='center' valign='top' class='isiTengah' width='500px'>
				".$isitengah."
			    </td>
			  </tr>
			</table>
			<center><i><font size='9pt'>Kartu ini harus selalu dibawa saat mengikuti ujian dan ditandatangani oleh pengawas ujian</font></i></center>
			<br>
			<br>
			<br>
			<br>
			";
			
			if ($qry->rowCount()!=$i) {
				if ($i%2==0) $kartu.="<pagebreak>";
			}
			//echo $header.$qrcode.$header1.$header2;  
			//echo $kartu;
	   
			$mpdf->WriteHTML($header.$qrcode.$header1.$header2);
			$mpdf->WriteHTML($kartu);
	    
	   } catch (PDOException $salah) {
	   	 $kartu="Kesalahan saat mengambil KRS";
	   }
   
  }

} 
				
$file="Kartu-UTS-".$id." ".date("d-m-Y H-i-s");
$mpdf->Output($file.'.pdf','i');

?>