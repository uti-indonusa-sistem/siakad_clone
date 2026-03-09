<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

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
	  
} else if ($aksi=="ubahDirWadir") {
	  $nama_dir  = $data->nama_dir;
	  $nama_wadir  = $data->nama_wadir;
	  
	  $perintah = "update wsia_satuan_pendidikan set nama_dir='$nama_dir', nama_wadir='$nama_wadir' where npsn='".NPSN."' ";
	  try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
		$db		= null;
		    
		$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Ubah Nama";
		echo json_encode($hasil);
	  } catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	  }
}
