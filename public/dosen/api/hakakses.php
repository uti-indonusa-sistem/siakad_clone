<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="cek") {
	  $xid_pd=$_SESSION['xid_pd'];
	  $id_smt_aktif=$_SESSION['id_smt_aktif'];
	  $perintah = "select * from wsia_hakakses where xid_pd='$xid_pd' and id_smt='$id_smt_aktif' ";
	  try {
		    $db 	= koneksi();
		    $eksekusi 	= $db->query($perintah); 
		    
		    if ($eksekusi->rowCount()>0) {
		  	echo "1";
		    } else {
		  	echo "0";
		   }
		    		    
	  } catch (PDOException $salah) {
		  echo "0";
	  }
}


