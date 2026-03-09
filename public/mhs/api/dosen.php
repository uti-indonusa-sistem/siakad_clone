<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

 if ($aksi=="pilih") {
	
	  $perintah = "select xid_ptk,nidn,nm_ptk from wsia_dosen where wsia_dosen.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."')";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    foreach ($data as $itemData) {
		    		$itemData->id=$itemData->xid_ptk;
				$itemData->value=$itemData->nidn." - ".$itemData->nm_ptk;
				array_push($pilih,array('id'=>$itemData->xid_ptk,'value'=>$itemData->nidn." - ".$itemData->nm_ptk));
		    }
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} 