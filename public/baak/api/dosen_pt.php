<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	$ta=substr($_SESSION['ta'],0,4);
	$perintah = "select xid_reg_ptk,wsia_dosen.xid_ptk,nm_ptk,nidn,jk,id_thn_ajaran,wsia_dosen_pt.id_sms,concat(nm_jenj_didik,'-',nm_lemb) as prodi,no_srt_tgs,tgl_srt_tgs,a_sp_homebase from wsia_dosen, wsia_dosen_pt,wsia_sms,wsia_jenjang_pendidikan where wsia_dosen_pt.id_thn_ajaran='$ta' and wsia_dosen_pt.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_sms.xid_sms=wsia_dosen_pt.id_sms and wsia_jenjang_pendidikan.id_jenj_didik=wsia_sms.id_jenj_didik order by nm_ptk";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $dataA=array();
		    foreach ($data as $itemData) {
				if ($itemData->a_sp_homebase=="1") {
					$itemData->homebase="<span class='webix_icon fa-check'></span>";
				} else {
					$itemData->homebase="<span class='webix_icon fa-close'></span>";
				}
				
				$itemData->thn_ajaran=$itemData->id_thn_ajaran."/".($itemData->id_thn_ajaran+1);
				
				array_push($dataA,$itemData);
			}
		    
		    echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="pilih") {
	
	  $ta=substr($_SESSION['ta'],0,4);
	  $perintah = "select xid_reg_ptk,nidn,nm_ptk,concat(nm_jenj_didik,'-',nm_lemb) as prodi from wsia_dosen,wsia_dosen_pt,wsia_sms,wsia_jenjang_pendidikan where wsia_dosen_pt.id_ptk=wsia_dosen.xid_ptk and wsia_dosen_pt.id_thn_ajaran='$ta' and wsia_dosen_pt.id_sms='$id' and wsia_dosen_pt.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') and wsia_sms.xid_sms=wsia_dosen_pt.id_sms and wsia_jenjang_pendidikan.id_jenj_didik=wsia_sms.id_jenj_didik";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    foreach ($data as $itemData) {
		    	$itemData->id=$itemData->xid_reg_ptk;
				$itemData->value=$itemData->nidn." - ".$itemData->nm_ptk." - ".$itemData->prodi;
				array_push($pilih,$itemData);
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$xid_reg_ptk=$data->xid_reg_ptk;
	$xid_ptk=$data->xid_ptk;
	$id_thn_ajaran=substr($_SESSION['ta'],0,4);
	$id_sms=$data->id_sms;
	$no_srt_tgs=$data->no_srt_tgs;
	$tgl_srt_tgs=$data->tgl_srt_tgs;
	$a_sp_homebase=$data->a_sp_homebase;
	try {
		$qrySP = "select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."'";
	    $db 	= koneksi();
	    $qrySP 	= $db->prepare($qrySP); 
	    $qrySP->execute();
	    $dataSP	= $qrySP->fetch(PDO::FETCH_OBJ);
	    $db		= null;
	    $id_sp=$dataSP->id_sp;
	    $qryPenugasan = "insert into wsia_dosen_pt (xid_reg_ptk,id_ptk,id_sp,id_thn_ajaran,id_sms,no_srt_tgs,tgl_srt_tgs,tmt_srt_tgs,a_sp_homebase) values('$xid_reg_ptk','$xid_ptk','$id_sp','$id_thn_ajaran','$id_sms','$no_srt_tgs','$tgl_srt_tgs','$tgl_srt_tgs','$a_sp_homebase')";
		
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryPenugasan);  
		    $db = null;
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Simpan";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Simpan.<br>Mungkin penugasan sudah masuk";
			echo json_encode($hasil);
		}
		    
	 } catch (PDOException $salah) {
		$hasil['berhasil']=0;
 		$hasil['pesan']="Gagal mengambil data satuan pendidikan. Kesalahan:<br>".$salah->getMessage();
	 	echo json_encode($hasil);
	 }
	
} else if ($aksi=="ubah") {
	$xid_reg_ptk=$data->xid_reg_ptk;
	$xid_ptk=$data->xid_ptk;
	$id_thn_ajaran=substr($_SESSION['ta'],0,4);
	$id_sms=$data->id_sms;
	$no_srt_tgs=$data->no_srt_tgs;
	$tgl_srt_tgs=$data->tgl_srt_tgs;
	$a_sp_homebase=$data->a_sp_homebase;
	try {
		$qrySP = "select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."'";
	    $db 	= koneksi();
	    $qrySP 	= $db->prepare($qrySP); 
	    $qrySP->execute();
	    $dataSP	= $qrySP->fetch(PDO::FETCH_OBJ);
	    $db		= null;
	    $id_sp=$dataSP->id_sp;
	    $qryPenugasan = "update wsia_dosen_pt set xid_ptk='$xid_ptk',id_sp='$id_sp',id_thn_ajaran='$id_thn_ajaran',id_sms='$id_sms',no_srt_tgs='$no_srt_tgs',tgl_srt_tgs='$tgl_srt_tgs',tmt_srt_tgs='$tgl_srt_tgs',a_sp_homebase='$a_sp_homebase' where xid_reg_ptk='$xid_reg_ptk'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryPenugasan);  
		    $db = null;
			$hasil['berhasil']=1;
			$hasil['pesan']="Berhasil Ubah";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal ubah.<br>Mungkin penugasan sudah masuk";
			echo json_encode($hasil);
		}
		    
	 } catch (PDOException $salah) {
		$hasil['berhasil']=0;
 		$hasil['pesan']="Gagal mengambil data satuan pendidikan. Kesalahan:<br>".$salah->getMessage();
	 	echo json_encode($hasil);
	 }
	
} else if ($aksi=="hapus") {
	$xid_reg_ptk=$data->xid_reg_ptk;
	$sql = "delete from wsia_dosen_pt where xid_reg_ptk='$xid_reg_ptk'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Hapus";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} 

