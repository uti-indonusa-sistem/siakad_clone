<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampilMurni") {	  
	  $id_smt=$_SESSION['ta'];
	  
	  $perintah = "select wsia_mahasiswa_pt.xid_reg_pd,xid_pd, nm_pd, nipd,jk,kelas,id_jns_daftar,mulai_smt,nm_jenj_didik,nm_lemb from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan, wsia_kuliah_mahasiswa where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and id_jns_daftar='1' and wsia_kuliah_mahasiswa.id_smt='$id_smt' and wsia_kuliah_mahasiswa.xid_reg_pd=wsia_mahasiswa_pt.xid_reg_pd";
	  
	 if ( isset($_GET['filter']['kelas']) ) {
	 	$perintah.=" and kelas like '%".$_GET['filter']['kelas']."%' ";
	 }
	  
	  $perintah .= isset($_GET['filter']['vnm_lemb'])?" and nm_lemb like '%".$_GET['filter']['vnm_lemb']."%'":"";
	  
	  $perintah.=" GROUP BY wsia_kuliah_mahasiswa.xid_reg_pd ";
	  
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 20';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
	  //echo $perintah;
	  try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
	   	$dataMhs	= $qry->fetchAll(PDO::FETCH_OBJ);
		//print_r($dataNilai);
		$dataA=array();
		
		foreach ($dataMhs as $itemData) {
			$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
			$itemData->angkatan=substr($itemData->mulai_smt,0,4);
			array_push($dataA,$itemData);
		}
		    
		$db		= null;
		echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
}  else if ($aksi=="tampilTransfer") {	  
	  $id_smt=$_SESSION['ta'];
	  
	   $perintah = "select wsia_mahasiswa_pt.xid_reg_pd,xid_pd, nm_pd, nipd,jk,kelas,id_jns_daftar,mulai_smt,nm_jenj_didik,nm_lemb from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan, wsia_kuliah_mahasiswa where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and id_jns_daftar>'1' and wsia_kuliah_mahasiswa.id_smt='$id_smt' and wsia_kuliah_mahasiswa.xid_reg_pd=wsia_mahasiswa_pt.xid_reg_pd";
	  
	 if ( isset($_GET['filter']['kelas']) ) {
	 	$perintah.=" and kelas like '%".$_GET['filter']['kelas']."%' ";
	 }
	  
	  $perintah .= isset($_GET['filter']['vnm_lemb'])?" and nm_lemb like '%".$_GET['filter']['vnm_lemb']."%'":"";
	  
	  $perintah.=" GROUP BY wsia_kuliah_mahasiswa.xid_reg_pd ";
	  
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 20';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
	  //echo $perintah;
	  try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
	   	$dataMhs	= $qry->fetchAll(PDO::FETCH_OBJ);
		//print_r($dataNilai);
		$dataA=array();
		
		foreach ($dataMhs as $itemData) {
			$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
			$itemData->angkatan=substr($itemData->mulai_smt,0,4);
			array_push($dataA,$itemData);
		}
		    
		$db		= null;
		echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  	  
}  

