<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	
	  $perintah = "select * from wsia_semester order by id_thn_ajaran desc, smt desc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $ahasil=array();
		    foreach ($data as $itemData) {
			if ($itemData->krs_aktif==1) {
				$itemData->statusAktif="<button class='btnSemesterSudahAktif'><i class='webix_icon fa-check'></i> Aktif</button>";
			} else {
				$itemData->statusAktif="<button class='btnSemesterAktif'><i class='webix_icon fa-close'></i> Tidak Aktif</button>";
			}
			
			array_push($ahasil,$itemData);
			
		    }
		    
		    echo json_encode($ahasil);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage());
	  }
	  
} else if ($aksi=="tambah") {
	$id_thn_ajaran=$data->id_thn_ajaran;
	$smt=$data->smt;
	$id_smt = $id_thn_ajaran.$smt;
	
	if ($smt=="1") {
		$vsmt="Ganjil";
	} else if ($smt=="2") {
		$vsmt="Genap";
	} else if ($smt=="3") {
		$vsmt="Pendek";
	}
	
	$nm_smt = $id_thn_ajaran."/".($id_thn_ajaran+1)." ".$vsmt;
	$waktu_update=date("Y-m-d H:i:s");
	$sql = "insert into wsia_semester values('$id_smt','$id_thn_ajaran','$nm_smt','$smt','0','0000-00-00','0000-00-00','0')";
	try {
		$db 		= koneksi();
	    	$eksekusi 	= $db->query($sql);  
	    	$db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Simpan";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$id_smt=$data->id_smt;
	try {
		$sql 		 = "delete from wsia_semester where id_smt='$id_smt' ";
	       $db 		 = koneksi();
	       $eksekusi 	 = $db->query($sql);  
	       $db 		 = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Hapus";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="statusAktif") {
	$id_smt=$data->id_smt;
	
	try {
		$db 		= koneksi();
		$sql = "update wsia_semester set krs_aktif='1' where id_smt='$id_smt' ";
	    	$eksekusi 	= $db->query($sql);  
	    	$sql2 = "update wsia_semester set krs_aktif='0' where id_smt<>'$id_smt'";
	   	$eksekusi 	= $db->query($sql2);  
	    	$db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Mengaktifkan Semester";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Mengaktifkan Semester. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="aktif") {
	
	  $perintah = "select * from wsia_semester where a_periode_aktif='1' ";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage());
	  }
	  
} else if ($aksi=="berlaku") {
	
	  $ta=$_SESSION['ta'];
	  $perintah = "select * from wsia_semester where id_smt='$ta'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage());
	  }
	  
} else if ($aksi=="pilih") {
	
	  $ta=$_SESSION['ta'];
	  $perintah = "select * from wsia_semester where id_smt='$ta'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih[0]['id']=$data[0]->id_smt;
		    $pilih[0]['value']=$data[0]->nm_smt;
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage());
	  }
	  
} else if ($aksi=="pilihSemua") {
	
	  $ta=$_SESSION['ta'];
	  $perintah = "select * from wsia_semester order by id_smt desc  limit 0,20";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $i=0;
		    $pilih=array();
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->id_smt;
		    	$pilih[$i]['value']=$itemData->nm_smt;
		    	$i++;	
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage());
	  }
	  
} else if ($aksi=="gantiPeriode") {
	$_SESSION['ta']=$data->id_smt;
	$hasil['berhasil']=1;
	$hasil['pesan']="Berhasil Ganti Periode Akademik";
	echo json_encode($hasil);

}