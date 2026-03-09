<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select * from view_sudah_du where tahun_angkatan='$id' and impor_siakad=0";
	  
	  $perintah .= isset($_GET['filter']['no_pend'])?" and IFNULL(no_pend,'') like '%".$_GET['filter']['no_pend']."%'":"";
	  $perintah .= isset($_GET['filter']['nama'])?" and IFNULL(nama,'') like '%".$_GET['filter']['nama']."%'":"";
	  $perintah .= isset($_GET['filter']['nama_prodi'])?" and IFNULL(nama_prodi,'') like '%".$_GET['filter']['nama_prodi']."%'":"";
	  $perintah .= isset($_GET['filter']['jenis_daftar'])?" and IFNULL(jenis_daftar,'') like '%".$_GET['filter']['jenis_daftar']."%'":"";
	  $perintah .= isset($_GET['filter']['kelas'])?" and  IFNULL(kelas,'')  like '%".$_GET['filter']['kelas']."%'":"";
	
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 50';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
	  //echo $perintah;
	  try {
		    $db 	= koneksi_spmb();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
			echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }

} 