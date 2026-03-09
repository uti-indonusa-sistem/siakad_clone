<?php
 if (!isset($key)) { exit("Key API Salah"); }
 include 'login_auth.php';
 if ($key!=$_SESSION['wsiaADMIN']) { exit("Akses Ditolak"); }

 ob_start();
 require_once('../lib/office/clsMsDocGenerator.php');
 $doc = new clsMsDocGenerator('PORTRAIT','LETTER','',0.5,1.02,0.5,0.5);

$id_smt=$_SESSION['ta'];
$xid_reg_pd = $data->xid_reg_pd;

if (count($xid_reg_pd)>0) {
	
	$th_awal=substr($id_smt,0,4);
	$th_akhir=$th_awal+1;
	if (substr($id_smt,4,1)=="1") {
		$smt="GANJIL";
	} else {
		$smt="GENAP";
	}
	$header="
		<table width='100%' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
		  <tr>
		    <td align='left'><h2>POLITEKNIK INDONUSA SURAKARTA</h2></td>
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
		    <td align='center'><h3><u>KARTU HASIL STUDI</u></h3> </td>
		  </tr>
		</table>";
	
	$i=0;
	$KHS="Tidak ada data";
	foreach ($xid_reg_pd as $itemReg_pd) {
	  	$i++;
		  $qryMhsKrs = "select wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_mahasiswa_pt.xid_reg_pd='$itemReg_pd' and wsia_kelas_kuliah.id_smt='$id_smt' group by  wsia_mahasiswa_pt.xid_reg_pd";
		  try {
			    	$db 	= koneksi();
			    	$qry 	= $db->prepare($qryMhsKrs); 
			   	$qry->execute();
			    	$dataMhsKrs	= $qry->fetch(PDO::FETCH_OBJ);
			    	
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
			<table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
			  <tr>
			    <td>NIM</td>
			    <td>: ".$dataMhsKrs->nipd." </td>
			    <td>&nbsp;</td>
			    <td>Semester </td>
			    <td>: ".$smt." </td>
			  </tr>
			  <tr>
			    <td>Nama</td>
			    <td>: ".$dataMhsKrs->nm_pd." </td>
			    <td>&nbsp;</td>
			    <td>Tahun Akademik</td>
			    <td>: ".$th_awal."/".$th_akhir." </td>
			  </tr>
			  <tr>
			    <td>Program Studi </td>
			    <td>: ".$dataMhsKrs->nm_jenj_didik."-".$dataMhsKrs->nm_lemb." </td>
			    <td>&nbsp;</td>
			    <td>Pembimbing Akademik</td>
			    <td>: ".$pa." </td>
			  </tr>
			</table>";

			$isiheader="<center>
				<table width='97%' border='0' align='left' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
				  <tr>
				    <td rowspan='2' align='center' valign='middle' width='20' style='border: 0.5px #000000 solid;'>NO</td>
				    <td rowspan='2' align='center' valign='middle' width='20' style='border: 0.5px #000000 solid;'>Kode Mata Kuliah </td>
				    <td rowspan='2' align='center' valign='middle' width='380' style='border: 0.5px #000000 solid;'>Mata Kuliah </td>    
				    <td rowspan='2' align='center' valign='middle' width='20' style='border: 0.5px #000000 solid;'>SKS</td>
				    <td colspan='2' align='center' valign='middle' width='100' style='border: 0.5px #000000 solid;'>NILAI</td>
				    <td rowspan='2' align='center' valign='middle' width='90' style='border: 0.5px #000000 solid;'>SKS x NILAI</td>    
				  </tr>
				  
				  <tr>
				    <td align='center' valign='middle' width='50' style='border: 0.5px #000000 solid;'>ANGKA</td>
				    <td align='center' valign='middle' width='50' style='border: 0.5px #000000 solid;'>HURUF</td>
				  </tr>";
 

			$qryNilai = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='".$dataMhsKrs->vid_reg_pd."' ";
			try {
			    $qry 	= $db->prepare($qryNilai); 
			    $qry->execute();
			    $dataNilai	= $qry->fetchAll(PDO::FETCH_OBJ);
			} catch (PDOException $salah) {
			   exit(json_encode($salah->getMessage()));
			}
			
			$isi="";
			$n=0;	
			$jsks=0;
			$jnXsks=0;
			$ipsmt=0;
			foreach ($dataNilai as $itemNilai) {
				$n++;
			    	$jsks=$jsks+$itemNilai->vsks_mk;
				$na=$itemNilai->nilai_indeks*$itemNilai->vsks_mk;	
				$jnXsks=$jnXsks+$na;	
				if ($jsks>0) {
					$ipsmt=$jnXsks/$jsks;
				} else {
					
				}
				
				$isi.="
				  <tr height='20'>
				    <td align='center' valign='middle' style='border-left:0.5px #000000 solid;'>".$n."</td>
				    <td align='center' valign='middle' style='border-left:0.5px #000000 solid;'>".$itemNilai->kode_mk."</td>
				    <td valign='middle' style='border-left:0.5px #000000 solid;'>".$itemNilai->nm_mk."</td>    
				    <td align='center' valign='middle' style='border-left:0.5px #000000 solid;'>".$itemNilai->vsks_mk."</td>
				    <td align='center' valign='middle' style='border-left:0.5px #000000 solid;'>".number_format($itemNilai->nilai_indeks,2)."</td>
				    <td align='center' valign='middle' style='border-left:0.5px #000000 solid;'>".$itemNilai->nilai_huruf."</td>
				    <td align='center' valign='middle' style='border-left:0.5px #000000 solid; border-right:0.5px #000000 solid;'>".number_format($na,2)."</td>
				  </tr>
				";
			}

			//hitung komulatif
			$qryNilaiSebelum = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,xid_sms,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt<='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='".$dataMhsKrs->vid_reg_pd."' ";
			try {
			    $qry 	= $db->prepare($qryNilaiSebelum); 
			    $qry->execute();
			    $dataNilaiSebelum	= $qry->fetchAll(PDO::FETCH_OBJ);
			} catch (PDOException $salah) {
			    exit(json_encode($salah->getMessage()));
			}
			
			$jsksK=0;
			$jnXsksK=0;
			$ipK=0;
			foreach ($dataNilaiSebelum as $itemNilaiSebelum) {
			   	$jsksK=$jsksK+$itemNilaiSebelum->vsks_mk;
				$naK=$itemNilaiSebelum->nilai_indeks*$itemNilaiSebelum->vsks_mk;	
				$jnXsksK=$jnXsksK+$naK;	
				$ipK=$jnXsksK/$jsksK;
				if ($jsksK>0) {
					$ipK=$jnXsksK/$jsksK;
				} else {
					//$ipK=0;
				}
			}

			/*
			 $isi.="
			  <tr>
			    <td colspan='3' align='center' style='border: 0.5px #000000 solid;'>Total</td>    
			    <td align='center' style='border: 0.5px #000000 solid;'>".$jsks."</td>
			    <td colspan='2' style='border: 0.5px #000000 solid;'>&nbsp;</td>    
			    <td align='center' style='border: 0.5px #000000 solid;'>".number_format($jnXsks,2)."</td>   
			  </tr>
			  <tr>
			    <td colspan='7' align='left' style='border: 0.5px #000000 solid;'>
				<table width='50%' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
				     <tr>
					     <td>Index Prestasi Semester</td><td>: ".number_format($ipsmt,2)." </td>
						 <td>SKS Semester</td><td>: ".$jsks."</td>
					 </tr>
				</table>
				</td>          
			  </tr>";
			  */
			 
			  $isi.="
			  <tr>
			    <td colspan='3' align='center' style='border: 0.5px #000000 solid;'>Total</td>    
			    <td align='center' style='border: 0.5px #000000 solid;'>".$jsks."</td>
			    <td colspan='2' style='border: 0.5px #000000 solid;'>&nbsp;</td>    
			    <td align='center' style='border: 0.5px #000000 solid;'>".number_format($jnXsks,2)."</td>   
			  </tr>
			  <tr>
			    <td colspan='7' align='left' style='border: 0.5px #000000 solid;'>
				<table width='50%' style='border-collapse: collapse; font-size:10pt;  font-family:Arial; color:#000; padding:5px;'>
				     <tr>
					     <td>Index Prestasi Semester</td><td>: ".number_format($ipsmt,2)." </td>
						 <td>SKS Semester</td><td>: ".$jsks."</td>
					 </tr>
					 <tr>
					     <td>Index Prestasi Kumulatif</td><td>: ".number_format($ipK,2)." </td>
						 <td>SKS Kumulatif</td><td>: ".$jsksK."</td>
					 </tr>
				</table>
				</td>          
			  </tr>";
			
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
			    	<td  width='50%' align='center'>
					Surakarta, ".format_tanggal_bln(date('Y-m-d'))."<br>
					Wakil Direktur I, <br><br><br><br><br>
					".$namaWadir."
				</td>
			  </tr>
			</table> </center>";


		$KHS=$header.$atas.$isiheader.$isi.$bawah;
		
		$doc->newPage();
		$doc->addParagraph($KHS);
	} // Foreach No_Pend
	
	$file="KHS-POLINUS-".$id."-".date("dmYHis").".doc";
	$doc->output($file);

}

?>