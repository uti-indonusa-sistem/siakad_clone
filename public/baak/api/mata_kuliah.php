<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $ta=$_SESSION['ta'];
	  $perintah = "select * from wsia_mata_kuliah, wsia_sms where wsia_mata_kuliah.id_sms =  wsia_sms.xid_sms";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $dataA=array();
		    foreach ($data as $itemData) {
		    		$itemData->id_sms=$itemData->xid_sms;
				if ($itemData->jns_mk=="A") { $itemData->vjns_mk="Wajib"; }
				else if ($itemData->jns_mk=="B") { $itemData->vjns_mk="Pilihan"; }
				else if ($itemData->jns_mk=="C") { $itemData->vjns_mk="Wajib Peminatan"; }
				else if ($itemData->jns_mk=="D") { $itemData->vjns_mk="Pilihan Peminatan"; }
				else if ($itemData->jns_mk=="S") { $itemData->vjns_mk="Tugas akhir/Skripsi/Tesis/Disertasi"; }
				
				if ($itemData->kel_mk=="A") { $itemData->vkel_mk="MPK-Pengembangan Kepribadian"; }
				else if ($itemData->kel_mk=="B") { $itemData->vkel_mk="MKK-Keilmuan dan Ketrampilan"; }
				else if ($itemData->kel_mk=="C") { $itemData->vkel_mk="MKB-Keahlian Berkarya"; }
				else if ($itemData->kel_mk=="D") { $itemData->vkel_mk="MPB-Perilaku Berkarya"; }
				else if ($itemData->kel_mk=="E") { $itemData->vkel_mk="MBB-Berkehidupan Bermasyarakat"; }
				else if ($itemData->kel_mk=="F") { $itemData->vkel_mk="MKU/MKDU"; }
				else if ($itemData->kel_mk=="G") { $itemData->vkel_mk="MKDK"; }
				else if ($itemData->kel_mk=="H") { $itemData->vkel_mk="MKK"; }
				
			}
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$xid_mk=date("ymdHis").$data->xid_mk;
	$id_sms=$data->id_sms;
	$kode_mk=$data->kode_mk;
	$nm_mk=$data->nm_mk;
	$nm_mk_en=$data->nm_mk_en;
	$jns_mk=$data->jns_mk;
	$kel_mk=$data->kel_mk;
	$sks_mk=$data->sks_mk;
	$sks_tm=$data->sks_tm;
	$sks_prak=$data->sks_prak;
	$sks_prak_lap=$data->sks_prak_lap;
	$sks_sim=$data->sks_sim;
	$a_sap=$data->a_sap;
	$a_silabus=$data->a_silabus;
	$a_bahan_ajar=$data->a_bahan_ajar;
	$acara_prak=$data->acara_prak;
	$a_diktat=$data->a_diktat;
	$qrySMS = "select * from wsia_sms where xid_sms ='$id_sms'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qrySMS);  
		$dataSMS		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$id_jenj_didik=$dataSMS->id_jenj_didik;
		$qryKelasKuliah = "insert into wsia_mata_kuliah (xid_mk,id_sms,id_jenj_didik,kode_mk,nm_mk,nm_mk_en,jns_mk,kel_mk,sks_mk,sks_tm,sks_prak,sks_prak_lap,sks_sim,a_sap,a_silabus,a_bahan_ajar,acara_prak,a_diktat) values('$xid_mk','$id_sms','$id_jenj_didik','$kode_mk','$nm_mk','$nm_mk_en','$jns_mk','$kel_mk','$sks_mk','$sks_tm','$sks_prak','$sks_prak_lap','$sks_sim','$a_sap','$a_silabus','$a_bahan_ajar','$acara_prak','$a_diktat')";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKelasKuliah);  
		    $db = null;
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Simpan";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
			echo json_encode($hasil);
		}
		    
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 $hasil['pesan']="Gagal mengambil data Program Studi. Kesalahan:<br>".$salah->getMessage();
		 echo json_encode($hasil);
	  }
} else if ($aksi=="ubah") {
	$xid_mk=$data->xid_mk;
	$id_sms=$data->id_sms;
	$kode_mk=$data->kode_mk;
	$nm_mk=$data->nm_mk;
	$nm_mk_en=$data->nm_mk_en;
	$jns_mk=$data->jns_mk;
	$kel_mk=$data->kel_mk;
	$sks_mk=$data->sks_mk;
	$sks_tm=$data->sks_tm;
	$sks_prak=$data->sks_prak;
	$sks_prak_lap=$data->sks_prak_lap;
	$sks_sim=$data->sks_sim;
	$a_sap=$data->a_sap;
	$a_silabus=$data->a_silabus;
	$a_bahan_ajar=$data->a_bahan_ajar;
	$acara_prak=$data->acara_prak;
	$a_diktat=$data->a_diktat;
	
	$qrySMS = "select * from wsia_sms where xid_sms ='$id_sms'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qrySMS);  
		$dataSMS		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$id_jenj_didik=$dataSMS->id_jenj_didik;
		
		$qryKelasKuliah = "update wsia_mata_kuliah set id_sms='$id_sms',id_jenj_didik='$id_jenj_didik',kode_mk='$kode_mk',nm_mk='$nm_mk',nm_mk_en='$nm_mk_en',jns_mk='$jns_mk',kel_mk='$kel_mk',sks_mk='$sks_mk',sks_tm='$sks_tm',sks_prak='$sks_prak',sks_prak_lap='$sks_prak_lap',sks_sim='$sks_sim',a_sap='$a_sap',a_silabus='$a_silabus',a_bahan_ajar='$a_bahan_ajar',acara_prak='$acara_prak',a_diktat='$a_diktat' where xid_mk='$xid_mk' and (id_mk='' OR id_mk IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKelasKuliah);  
		    $db = null;
		    if ($eksekusi->rowCount()>0) {
				$hasil['berhasil']=1;
	    		$hasil['pesan']="Berhasil ubah";
			} else {
				$hasil['berhasil']=0;
	    		$hasil['pesan']="Mata kuliah tidak bisa dirubah";
			}
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal ubah. Kesalahan:<br>".$salah->getMessage();
			echo json_encode($hasil);
		}
		    
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 $hasil['pesan']="Gagal mengambil data program studi. Kesalahan:<br>".$salah->getMessage();
		 echo json_encode($hasil);
	  }
	
} else if ($aksi=="hapus") {
	$xid_mk	=$data->xid_mk;
	$sql = "delete from wsia_mata_kuliah where xid_mk='$xid_mk' and (id_mk='' OR id_mk IS NULL)";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    	if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="Mata kuliah tidak bisa dihapus.";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
}
