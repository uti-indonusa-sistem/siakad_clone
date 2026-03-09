<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="pilih") {
	  $perintah = "select * from wsia_jenis_pendaftaran where aktif='1' order by id_jns_daftar asc ";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    foreach ($data as $itemData) {
		    	$itemData->id=$itemData->id_jns_daftar;
				$itemData->value=$itemData->nm_jns_daftar;
				array_push($pilih,$itemData);
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} 