<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from siakad_ruang";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$ruang=$data->ruang;

	$qry = "insert into siakad_ruang values('$ruang')";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($qry);  
	    $db = null;
    	$hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil Simpan";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Simpan. Mungkin Nama: ".$ruang." sudah ada";
		echo json_encode($hasil);
	}
	
} else if ($aksi=="ubah") {
	
	$ruang=$data->ruang;
		
	$qry = "update siakad_ruang set ruang='$ruang' where ruang='$ruang'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($qry);  
	    $db = null;
	    if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil ubah";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="ruang tidak dirubah.";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal ubah. Mungkin nama: ".$ruang." sudah ada";
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$ruang=$data->ruang;
	$sql = "delete from siakad_ruang where ruang='$ruang'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    	if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="Kelas tidak bisa dihapus.";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
} else if ($aksi=="pilih") {
	  $perintah = "select * from siakad_ruang order by ruang";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->ruang;
				$pilih[$i]['value']=$itemData->ruang;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} 

?>