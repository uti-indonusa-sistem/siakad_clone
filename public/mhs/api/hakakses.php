<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="cek") {
	  $xid_pd=$_SESSION['xid_pd'];
	  
	  // Always fetch active semester from DB (not session) so it's always up-to-date
	  // even if the student logged in before BAAK activated KRS
	  try {
		    $db 	= koneksi();
		    
		    $qrySmt = $db->query("SELECT id_smt FROM wsia_semester WHERE krs_aktif='1' LIMIT 1");
		    $dataSmt = $qrySmt->fetch(PDO::FETCH_OBJ);
		    
		    if (!$dataSmt) {
		    	// No active KRS semester
		    	file_put_contents('C:/laragon/tmp/krs_debug.log', "[" . date('Y-m-d H:i:s') . "] KRS Check FAILED - No active KRS semester. xid_pd: $xid_pd, session id_smt_aktif: " . ($_SESSION['id_smt_aktif'] ?? 'not set') . "\n", FILE_APPEND);
		    	echo "0";
		    	return;
		    }
		    
		    $id_smt_aktif = $dataSmt->id_smt;
		    
		    // Update session to keep it in sync
		    $_SESSION['id_smt_aktif'] = $id_smt_aktif;
		    
		    $perintah = "select * from wsia_hakakses where xid_pd='$xid_pd' and id_smt='$id_smt_aktif'";
		    file_put_contents('C:/laragon/tmp/krs_debug.log', "[" . date('Y-m-d H:i:s') . "] KRS Check - xid_pd: $xid_pd, id_smt_aktif: $id_smt_aktif (from DB)\n", FILE_APPEND);
		    
		    $eksekusi = $db->query($perintah); 
		    
		    if ($eksekusi->rowCount()>0) {
		  		echo "1";
		    } else {
		  		echo "0";
		   }
		    		    
	  } catch (PDOException $salah) {
		  file_put_contents('C:/laragon/tmp/krs_debug.log', "[" . date('Y-m-d H:i:s') . "] KRS Check ERROR: " . $salah->getMessage() . "\n", FILE_APPEND);
		  echo "0";
	  }
}


