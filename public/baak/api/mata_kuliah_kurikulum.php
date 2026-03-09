<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $id_kurikulum_sp = $id;
	  $perintah = "select * from wsia_mata_kuliah_kurikulum, wsia_mata_kuliah where id_kurikulum_sp='$id_kurikulum_sp' and wsia_mata_kuliah_kurikulum.id_mk=wsia_mata_kuliah.xid_mk order by nm_mk asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $dataA=array();
		    
		    foreach ($data as $itemData) {
				if ($itemData->a_wajib=="1") {
					$itemData->va_wajib="<span class='webix_icon fa-check'></span>";
				} else {
					$itemData->va_wajib="<span class='webix_icon fa-close'></span>";
				}
				array_push($dataA,$itemData);
			}
		    
		    echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="pilihProdi") {
	  $id_sms = $id;
	  if (isset($_GET['filter'])) {
	  	 $filter1=$_GET['filter'];
	  	 //print_r($filter1['value']);
	  	 $filter2=explode("-",$filter1['value']);
	  	 $lfilter=count($filter2)-1;
	  	 $nm_mk=trim($filter2[$lfilter]);
	  	 //echo $nm_mk;
	  } else {
	  	$nm_mk="";
	  }
	  
	  $perintah = "select * from wsia_mata_kuliah where id_sms='$id_sms' and nm_mk like '%$nm_mk%' order by nm_mk asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->xid_mk;
				$pilih[$i]['value']=$itemData->kode_mk." - ".$itemData->nm_mk;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="tambah") {
	$id_kurikulum_sp=$data->xid_kurikulum_sp;
	$id_mk=$data->id_mk;
	$id_mk_kurikulum=md5($id_kurikulum_sp.$id_mk);
	$smt=$data->smt;
	$a_wajib=$data->a_wajib;
	
	$qryMk = "select * from wsia_mata_kuliah where xid_mk ='$id_mk'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qryMk);  
		$dataMk		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$sks_mk=$dataMk->sks_mk;
		$sks_tm=$dataMk->sks_tm;
		$sks_prak=$dataMk->sks_prak;
		$sks_prak_lap=$dataMk->sks_prak_lap;
		$sks_sim=$dataMk->sks_sim;
		$qryKelasKuliah = "insert into wsia_mata_kuliah_kurikulum (id_mk_kurikulum,id_kurikulum_sp,id_mk,smt,sks_mk,sks_tm,sks_prak,sks_prak_lap,sks_sim,a_wajib) values('$id_mk_kurikulum','$id_kurikulum_sp','$id_mk','$smt','$sks_mk','$sks_tm','$sks_prak','$sks_prak_lap','$sks_sim','$a_wajib')";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKelasKuliah);  
		    $db = null;
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Simpan";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Simpan.<br>Mungkin mata kuliah sudah masuk";
			echo json_encode($hasil);
		}
		    
  	} catch (PDOException $salah) {
	 	$hasil['berhasil']=0;
 		$hasil['pesan']="Gagal mengambil data Mata kuliah. Kesalahan:<br>".$salah->getMessage();
	 	echo json_encode($hasil);
  	}
  	
} else if ($aksi=="ubah") {
	$id_kurikulum_sp=$data->xid_kurikulum_sp;
	$id_mk=$data->id_mk;
	$id_mk_kurikulum=$data->id_mk_kurikulum;
	$id_mk_kurikulum2=$id_kurikulum_sp.$id_mk;
	$smt=$data->smt;
	$a_wajib=$data->a_wajib;
	
	$qryMk = "select * from wsia_mata_kuliah where xid_mk ='$id_mk'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qryMk);  
		$dataMk		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$sks_mk=$dataMk->sks_mk;
		$sks_tm=$dataMk->sks_tm;
		$sks_prak=$dataMk->sks_prak;
		$sks_prak_lap=$dataMk->sks_prak_lap;
		$sks_sim=$dataMk->sks_sim;
		$qryKelasKuliah = "update wsia_mata_kuliah_kurikulum set id_mk_kurikulum='$id_mk_kurikulum2',id_mk='$id_mk',smt='$smt',sks_mk='$sks_mk',sks_tm='$sks_tm',sks_prak='$sks_prak',sks_prak_lap='$sks_prak_lap',sks_sim='$sks_sim',a_wajib='$a_wajib' where id_mk_kurikulum='$id_mk_kurikulum'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKelasKuliah);  
		    $db = null;
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Ubah";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Ubah.<br>Mungkin mata kuliah sudah masuk";
			echo json_encode($hasil);
		}
		    
  	} catch (PDOException $salah) {
	 	$hasil['berhasil']=0;
 		$hasil['pesan']="Gagal mengambil data Mata kuliah. Kesalahan:<br>".$salah->getMessage();
	 	echo json_encode($hasil);
  	}
  	
} else if ($aksi=="hapus") {
	$id_mk_kurikulum=$data->id_mk_kurikulum;
	$sql = "delete from wsia_mata_kuliah_kurikulum where id_mk_kurikulum='$id_mk_kurikulum'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil hapus";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="pilih") {
	  $id_kurikulum_sp = $id;
	  $perintah = "select * from wsia_mata_kuliah_kurikulum, wsia_mata_kuliah where id_kurikulum_sp='$id_kurikulum_sp' and wsia_mata_kuliah_kurikulum.id_mk=wsia_mata_kuliah.xid_mk order by nm_mk asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->xid_mk;
				$pilih[$i]['value']=$itemData->kode_mk." - ".$itemData->nm_mk;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} 

