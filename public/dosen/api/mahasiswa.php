<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $xid_ptk=$_SESSION['xid_ptk'];	  

	  $perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and left(mulai_smt,4)='$id' and pa='$xid_ptk' ";
	  
	  $perintah .= isset($_GET['filter']['nm_pd'])?" and nm_pd like '%".$_GET['filter']['nm_pd']."%'":"";
	  $perintah .= isset($_GET['filter']['nipd'])?" and nipd like '%".$_GET['filter']['nipd']."%'":"";
	  $perintah .= isset($_GET['filter']['jk'])?" and jk like '%".$_GET['filter']['jk']."%'":"";
	  $perintah .= isset($_GET['filter']['tgl_lahir'])?" and tgl_lahir like '%".$_GET['filter']['tgl_lahir']."%'":"";
	  $perintah .= isset($_GET['filter']['kelas'])?" and kelas like '%".$_GET['filter']['kelas']."%'":"";
	  
	  if ( isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb']!="" ){
	  	$nm_lemb=explode(" - ",$_GET['filter']['vnm_lemb']);
	  	$nm_jenj_didik=$nm_lemb[0];
	  	$nm_lemb=$nm_lemb[1];
	  	$perintah .= " and nm_jenj_didik like '%".$nm_jenj_didik."%'";
	  	$perintah .= " and nm_lemb like '%".$nm_lemb."%'";
	  }
	  
	  if ( isset($_GET['filter']['vid_jns_daftar']) ){
	  	if ($_GET['filter']['vid_jns_daftar']=="Mahasiswa Baru") {
			$perintah .= " and id_jns_daftar = '1'";
		} else if ($_GET['filter']['vid_jns_daftar']=="Pindahan/Transfer") {
			$perintah .= " and id_jns_daftar = '2'";
		} 
	  	
	  }
	  
	  $perintah.=" order by mulai_smt desc, nipd desc";
	  
	  // Untuk merubah data tampil lebih dari 20
	  //$perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 20';
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 40';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		    $dataA=array();
		    foreach ($data as $itemData) {
				$id_jns_daftar=$itemData->id_jns_daftar;
				if ($id_jns_daftar=="1") {
					$vid_jns_daftar="Mahasiswa Baru";
				} else if ($id_jns_daftar=="2") {
					$vid_jns_daftar="Pindahan/Transfer";
				} 
				$itemData->vid_jns_daftar=$vid_jns_daftar;
				$itemData->no_pend=$itemData->xid_pd;
				$itemData->id_sms=$itemData->xid_sms;
				$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
				$itemData->vnm_ibu_kandung=$itemData->nm_ibu_kandung;
				
				$id_kk=$itemData->id_kk;
			    	$qryKKmhs = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk'";
			    	$eksekusiMhs = $db->query($qryKKmhs);
			    	$dataKKmhs	= $eksekusiMhs->fetch(PDO::FETCH_OBJ);
			    	$aKKmhs = get_object_vars($dataKKmhs);
			    	foreach ($aKKmhs as $key=> $nilai) {
			    		$keyMhs="mhs_".$key;
					$itemData->$keyMhs=$nilai;
				}
			    	
			    	$id_kk_ayah=$itemData->id_kebutuhan_khusus_ayah;
			    	$qryKKayah = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ayah'";
			    	$eksekusiAyah = $db->query($qryKKayah);
			    	$dataKKayah	= $eksekusiAyah->fetch(PDO::FETCH_OBJ);
			    	$aKKayah = get_object_vars($dataKKayah);
			    	foreach ($aKKayah as $key=> $nilai) {
			    		$keyAyah="ayah_".$key;
					$itemData->$keyAyah=$nilai;
				}
			    	
			    	$id_kk_ibu=$itemData->id_kebutuhan_khusus_ibu;
			    	$qryKKibu = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ibu'";
			    	$eksekusiIbu = $db->query($qryKKibu);
			    	$dataKKibu	= $eksekusiIbu->fetch(PDO::FETCH_OBJ);
			    	$aKKibu = get_object_vars($dataKKibu);
			    	foreach ($aKKibu as $key=> $nilai) {
			    		$keyIbu="ibu_".$key;
					$itemData->$keyIbu=$nilai;
				}
				
				array_push($dataA,$itemData);
		   }
			
		    echo json_encode($dataA);
		     $db		= null;
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} 