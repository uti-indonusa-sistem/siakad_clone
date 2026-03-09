<?php
error_reporting(0);

if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from wsia_kurikulum, wsia_sms,wsia_jenjang_pendidikan where wsia_kurikulum.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    $dataA=array();
		    foreach ($data as $itemData) {
				$itemData->vnm_lemb=$itemData->nm_jenj_didik."-".$itemData->nm_lemb;
				$tahun1=substr($itemData->id_smt_berlaku,0,4);
				$tahun2=$tahun1+1;
				$smt=substr($itemData->id_smt_berlaku,4,1);
				if ($smt=="1") {
					$vsmt="Ganjil";
				} else if ($smt=="2") {
					$vsmt="Genap";
				} else if ($smt=="3") {
					$vsmt="Pendek";
				}
				$itemData->vid_smt_berlaku=$tahun1."/".$tahun2." ".$vsmt;
				array_push($dataA,$itemData);
			}
		    echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="pilih") {
	  $id_sms = $id;
	  $perintah = "select * from wsia_kurikulum where id_sms='$id_sms' order by nm_kurikulum_sp desc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->xid_kurikulum_sp;
				$pilih[$i]['value']=$itemData->nm_kurikulum_sp;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="tambah") {
	$xid_kurikulum_sp=date("ymdHis").$data->xid_kurikulum_sp;
	$nm_kurikulum_sp=$data->nm_kurikulum_sp;
	$jml_sks_wajib=$data->jml_sks_wajib;
	$jml_sks_pilihan=$data->jml_sks_pilihan;
	$jml_sks_lulus=$data->jml_sks_lulus;
	$id_sms=$data->id_sms;
	$id_smt_berlaku=$data->id_smt_berlaku;
	
	$qrySMS = "select * from wsia_sms where xid_sms ='$id_sms'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qrySMS);  
		$dataSMS		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$id_jenj_didik=$dataSMS->id_jenj_didik;
		
		$qryKurikulum = "insert into wsia_kurikulum (xid_kurikulum_sp,nm_kurikulum_sp,jml_sem_normal,jml_sks_lulus,jml_sks_wajib,jml_sks_pilihan,id_sms,id_jenj_didik,id_smt_berlaku) values('$xid_kurikulum_sp','$nm_kurikulum_sp','8','$jml_sks_lulus','$jml_sks_wajib','$jml_sks_pilihan','$id_sms','$id_jenj_didik','$id_smt_berlaku')";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKurikulum);  
		    $db = null;
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Simpan";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
			echo json_encode($hasil);
		}
		    
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 $hasil['pesan']="Gagal mengambil data Program Studi. Kesalahan:<br>".$salah->getMessage();
		 echo json_encode($hasil);
	  }
} else if ($aksi=="ubah") {
	$xid_kurikulum_sp=$data->xid_kurikulum_sp;
	$nm_kurikulum_sp=$data->nm_kurikulum_sp;
	$jml_sks_wajib=$data->jml_sks_wajib;
	$jml_sks_pilihan=$data->jml_sks_pilihan;
	$jml_sks_lulus=$data->jml_sks_lulus;
	$id_sms=$data->id_sms;
	$id_smt_berlaku=$data->id_smt_berlaku;
	
	$qrySMS = "select * from wsia_sms where xid_sms ='$id_sms'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qrySMS);  
		$dataSMS		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$id_jenj_didik=$dataSMS->id_jenj_didik;
		
		$qryKurikulum = "update wsia_kurikulum set nm_kurikulum_sp='$nm_kurikulum_sp',jml_sks_lulus='$jml_sks_lulus',jml_sks_wajib='$jml_sks_wajib',jml_sks_pilihan='$jml_sks_pilihan',id_sms='$id_sms',id_jenj_didik='$id_jenj_didik',id_smt_berlaku='$id_smt_berlaku' where xid_kurikulum_sp='$xid_kurikulum_sp' and id_kurikulum_sp=''";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKurikulum);  
		    $db = null;
		    if ($eksekusi->rowCount()>0) {
				$hasil['berhasil']=1;
	    		$hasil['pesan']="Berhasil ubah";
			} else {
				$hasil['berhasil']=0;
	    		$hasil['pesan']="Kurikulum tidak bisa diubah.<br>Mungkin sudah disinkronkan ke Feeder";
			}
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal ubah. Kesalahan:<br>".$salah->getMessage();
			echo json_encode($hasil);
		}
		    
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 $hasil['pesan']="Gagal mengambil data program studi. Kesalahan:<br>".$salah->getMessage();
		 echo json_encode($hasil);
	  }
	
} else if ($aksi=="hapus") {
	$xid_kurikulum_sp	=$data->xid_kurikulum_sp;
	$sql = "delete from wsia_kurikulum where xid_kurikulum_sp='$xid_kurikulum_sp' and id_kurikulum_sp=''";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    	if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="Kurikulum tidak bisa dihapus.<br>Mungkin sudah disinkronkan ke Feeder";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
}

