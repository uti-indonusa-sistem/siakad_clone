<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from wsia_satuan_pendidikan where npsn='".NPSN."' ";
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
