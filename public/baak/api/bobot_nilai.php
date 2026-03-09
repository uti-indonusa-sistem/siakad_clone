<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from wsia_bobot_nilai,wsia_sms,wsia_jenjang_pendidikan where wsia_bobot_nilai.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik  order by nm_lemb asc, nilai_indeks desc, bobot_nilai_maks desc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $dataA=array();
		    foreach ($data as $itemData) {
				$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
				array_push($dataA,$itemData);
			}
		    
		    echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
	    	  
	  }
	  
} else if ($aksi=="tambah") {
	$id_sms=$data->id_sms;
	$nilai_huruf=$data->nilai_huruf;
	$nilai_indeks=$data->nilai_indeks;
	$bobot_nilai_min=$data->bobot_nilai_min;
	$bobot_nilai_maks=$data->bobot_nilai_maks;
	
	$kode_bobot_nilai=date("YmdHis");
	
	$sql = "insert into wsia_bobot_nilai values('$kode_bobot_nilai','$id_sms','$nilai_huruf','$bobot_nilai_min','$bobot_nilai_maks','$nilai_indeks','0000-00-00','0000-00-00')";
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
	
} else if ($aksi=="ubah") {
	$kode_bobot_nilai=$data->kode_bobot_nilai;
	$id_sms=$data->id_sms;
	$nilai_huruf=$data->nilai_huruf;
	$nilai_indeks=$data->nilai_indeks;
	$bobot_nilai_min=$data->bobot_nilai_min;
	$bobot_nilai_maks=$data->bobot_nilai_maks;
	
	$sql = "update wsia_bobot_nilai set id_sms = '$id_sms', nilai_huruf='$nilai_huruf', bobot_nilai_min='$bobot_nilai_min', bobot_nilai_maks='$bobot_nilai_maks', nilai_indeks='$nilai_indeks' where kode_bobot_nilai='$kode_bobot_nilai' ";
	try {
		$db 		= koneksi();
	    	$eksekusi 	= $db->query($sql);  
	   	 $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Ubah";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$kode_bobot_nilai=$data->kode_bobot_nilai;
	try {
		$sql 		 = "delete from wsia_bobot_nilai where kode_bobot_nilai='$kode_bobot_nilai'";
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
	
} else if ($aksi=="tampilSms") {
	  $perintah = "select * from wsia_bobot_nilai where id_sms='$id' order by nilai_huruf desc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }
} else if ($aksi=="persennilai") {
	$persen_absen=$data->persen_absen;
	$persen_tugas=$data->persen_tugas;
	$persen_uts=$data->persen_uts;
	$persen_uas=$data->persen_uas;
	$persen_total=$data->persen_total;
	
	$sql = "update wsia_persen_nilai set persen_absen = '$persen_absen', persen_tugas='$persen_tugas', persen_uts='$persen_uts', persen_uas='$persen_uas', persen_total='$persen_total' ";
	try {
		$db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	   	$db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Ubah";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="tampilpersennilai") {
	  $perintah = "select * from wsia_persen_nilai limit 1";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetch(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }
} 
