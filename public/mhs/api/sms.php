<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

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
}

if ($aksi=="pilih") {
	  $perintah = "select id_sms, nm_lemb, nm_jenj_didik from wsia_sms,wsia_jenjang_pendidikan where wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_sms.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') order by nm_jenj_didik, nm_lemb asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->id_sms;
				$pilih[$i]['value']=$itemData->nm_jenj_didik."-".$itemData->nm_lemb;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
}

