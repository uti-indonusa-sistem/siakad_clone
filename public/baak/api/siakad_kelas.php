<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from siakad_kelas order by id_nm_kls desc";
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
	$angkatan=$data->angkatan;
	$abc=$data->abc;
	//$urutan=$data->urutan;
	//$id_nm_kls=substr($angkatan,2,2).$abc.$urutan;

	$urutan="";
	$id_nm_kls=substr($angkatan,2,2).$abc;
	
	$qry = "insert into siakad_kelas values('$id_nm_kls','$angkatan','$abc','$urutan')";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($qry);  
	    $db = null;
    	$hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil Simpan";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Simpan. Mungkin kelas: ".$id_nm_kls." sudah ada";
		echo json_encode($hasil);
	}
	
} else if ($aksi=="ubah") {
	$id_nm_kls=$data->id_nm_kls;
	$angkatan=$data->angkatan;
	$abc=$data->abc;
	//$urutan=$data->urutan;
	//$id_nm_klsBaru=substr($angkatan,2,2).$abc.$urutan;

	$urutan="";
	$id_nm_klsBaru=substr($angkatan,2,2).$abc;
	
	
	$qry = "update siakad_kelas set id_nm_kls='$id_nm_klsBaru',angkatan='$angkatan',abc='$abc',urutan='$urutan' where id_nm_kls='$id_nm_kls'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($qry);  
	    $db = null;
	    if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil ubah";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="Kelas tidak bisa dirubah.";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal ubah. Mungkin kelas: ".$id_nm_klsBaru." sudah ada";
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$id_nm_kls=$data->id_nm_kls;
	$sql = "delete from siakad_kelas where id_nm_kls='$id_nm_kls'";
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
	  $perintah = "select * from siakad_kelas order by id_nm_kls desc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->id_nm_kls;
				$pilih[$i]['value']=$itemData->id_nm_kls." ( Angkatan: ".$itemData->angkatan.", Kelas: ".$itemData->abc.$itemData->urutan." )";
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} 
