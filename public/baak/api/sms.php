<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from wsia_sms,wsia_jenjang_pendidikan where wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_sms.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') ";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="pilih") {
	  $perintah = "select xid_sms, nm_lemb, nm_jenj_didik from wsia_sms,wsia_jenjang_pendidikan where wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_sms.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') order by nm_jenj_didik, nm_lemb asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->xid_sms;
				$pilih[$i]['value']=$itemData->nm_jenj_didik."-".$itemData->nm_lemb;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$kode_prodi=$data->kode_prodi;
	$nm_lemb=$data->nm_lemb;
	$nm_lemb_en=$data->nm_lemb_en;
	$id_jenj_didik=$data->id_jenj_didik;
	$id_sms=$data->id_sms;
	
	$qrySms = "insert into wsia_sms (xid_sms,id_sms,nm_lemb,nm_lemb_en,kode_prodi,id_jenj_didik,id_sp) values('$kode_prodi','$id_sms','$nm_lemb','$nm_lemb_en','$kode_prodi','$id_jenj_didik','1')";
	try {
	    	$db 		= koneksi();
	    	$eksekusi 	= $db->query($qrySms);  
	    	$db = null;
    		$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Simpan";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
		    
} else if ($aksi=="ubah") {
	$xid_sms=$data->xid_sms;
	$kode_prodi=$data->kode_prodi;
	$nm_lemb=$data->nm_lemb;
	$nm_lemb_en=$data->nm_lemb_en;
	$id_jenj_didik=$data->id_jenj_didik;
	$id_sms=$data->id_sms;
	
	$qrySms = "update wsia_sms set nm_lemb='$nm_lemb',nm_lemb_en='$nm_lemb_en',id_jenj_didik='$id_jenj_didik', id_sms='$id_sms' where xid_sms='$xid_sms'";
	try {
	    	$db 		= koneksi();
	    	$eksekusi 	= $db->query($qrySms);  
	    	$db = null;
    		$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$xid_sms	=$data->xid_sms;
	$sql = "delete from wsia_sms where xid_sms='$xid_sms' and id_sms=''";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    		if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    			$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    			$hasil['pesan']="Program studi tidak diperbolehkan dihapus";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
}

