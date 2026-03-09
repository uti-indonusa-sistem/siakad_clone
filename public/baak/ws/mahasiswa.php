<?php
error_reporting(0);
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select *, wsia_mahasiswa.id_pd as id_mahasiswa from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and (id_jns_keluar='' OR id_jns_keluar IS NULL) and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and (wsia_mahasiswa.updated_at is null OR wsia_mahasiswa.updated_at>if(isnull(wsia_mahasiswa.sync_at),'0000-00-00',wsia_mahasiswa.sync_at)) ";
	  
	  $perintah .= isset($_GET['filter']['nm_pd'])?" and nm_pd like '%".$_GET['filter']['nm_pd']."%'":"";
	  $perintah .= isset($_GET['filter']['nipd'])?" and nipd like '%".$_GET['filter']['nipd']."%'":"";
	  $perintah .= isset($_GET['filter']['kelas'])?" and kelas like '%".$_GET['filter']['kelas']."%'":"";
	  $perintah .= isset($_GET['filter']['id_sms'])?" and  wsia_mahasiswa_pt.id_sms like '%".$_GET['filter']['id_sms']."%'":"";
	  
	  if ( isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb']!="" ){
	  	$nm_lemb=explode(" - ",$_GET['filter']['vnm_lemb']);
	  	$nm_jenj_didik=$nm_lemb[0];
	  	$nm_lemb=$nm_lemb[1];
	  	$perintah .= " and nm_jenj_didik like '%".$nm_jenj_didik."%'";
	  	$perintah .= " and nm_lemb like '%".$nm_lemb."%'";
	  }
	  
	  
	  $perintah.=" order by updated_at, nipd asc";
	  
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 50';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		    $dataA=array();
		    foreach ($data as $itemData) {
				$itemData->no_pend=$itemData->xid_pd;
				$itemData->id_sms=$itemData->xid_sms;
				$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
				$itemData->statusSync = (empty($itemData->sync_at) || $itemData->sync_at == '0000-00-00 00:00:00' ? 0 : 1);
				array_push($dataA,$itemData);
		   }
			
		    echo json_encode($dataA);
		     $db		= null;
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="sync") {	
	if (!isset($_SESSION['feeder_token']) || empty($_SESSION['feeder_token'])) {
		token();
	}
	$feeder_token = json_decode($_SESSION['feeder_token'] ?? '');


	if ($data->id_alat_transport=="0") {
		$data->id_alat_transport="99";
	}
	
	if ($data->id_penghasilan_ibu>16) {
		$data->id_penghasilan_ibu=0;
	}
	
	if ($data->id_penghasilan_ayah>16) {
		$data->id_penghasilan_ayah=0;
	}
	
	if ($data->id_penghasilan_wali>16) {
		$data->id_penghasilan_wali=0;
	}
	
	if (isset($data->telepon_rumah) && !ctype_digit($data->telepon_rumah)) {
		$data->telepon_rumah="";
	}
	if (isset($data->no_hp) && !ctype_digit($data->no_hp)) {
		$data->no_hp="";
	}

	if ($data->id_jenjang_pendidikan_ayah==25) {
		$data->id_jenjang_pendidikan_ayah=31;
	}

	if ($data->id_jenjang_pendidikan_ibu==25) {
		$data->id_jenjang_pendidikan_ibu=31;
	}

	if ($data->id_jenjang_pendidikan_wali==25) {
		$data->id_jenjang_pendidikan_wali=31;
	}

	if ($data->id_pekerjaan_ayah==0) {
		$data->id_pekerjaan_ayah=null;
	}

	if ($data->id_pekerjaan_ibu==0) {
		$data->id_pekerjaan_ibu=null;
	}

	if ($data->id_pekerjaan_wali==0) {
		$data->id_pekerjaan_wali=null;
	}

	if ($data->id_mahasiswa=="") {
		$sync['act']="InsertBiodataMahasiswa";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"nama_mahasiswa"=>$data->nm_pd, 
			"jenis_kelamin"=>$data->jk, 
			"jalan"=>$data->jln, 
			"rt"=>$data->rt, 
			"rw"=>$data->rw, 
			"dusun"=>$data->nm_dsn, 
			"kelurahan"=>$data->ds_kel,
			"kode_pos"=>$data->kode_pos, 
			"nisn"=>$data->nisn, 
			"nik"=>$data->nik,
			"tempat_lahir"=>$data->tmpt_lahir,
			"tanggal_lahir"=>$data->tgl_lahir, 
			"nama_ayah"=>$data->nm_ayah, 
			"tanggal_lahir_ayah"=>$data->tgl_lahir_ayah, 
			//"nik_ayah"=>"", 
			"id_pendidikan_ayah"=>$data->id_jenjang_pendidikan_ayah, 
			"id_pekerjaan_ayah"=>$data->id_pekerjaan_ayah, 
			"id_penghasilan_ayah"=>$data->id_penghasilan_ayah, 
			"id_kebutuhan_khusus_ayah"=>$data->id_kebutuhan_khusus_ayah, 
			"nama_ibu_kandung"=>$data->nm_ibu_kandung, 
			"tanggal_lahir_ibu"=>$data->tgl_lahir_ibu, 
			//"nik_ibu", 
			"id_pendidikan_ibu"=>$data->id_jenjang_pendidikan_ibu, 
			"id_pekerjaan_ibu"=>$data->id_pekerjaan_ibu, 
			"id_penghasilan_ibu"=>$data->id_penghasilan_ibu, 
			"id_kebutuhan_khusus_ibu"=>$data->id_kebutuhan_khusus_ibu, 
			"nama_wali"=>$data->nm_wali, 
			"tanggal_lahir_wali"=>$data->tgl_lahir_wali, 
			"id_pendidikan_wali"=>$data->id_jenjang_pendidikan_wali, 
			"id_pekerjaan_wali"=>($data->id_pekerjaan_wali>0)?$data->id_pekerjaan_wali:null,
			"id_penghasilan_wali"=>$data->id_penghasilan_wali, 
			"id_kebutuhan_khusus_mahasiswa"=>$data->id_kk,
			"telepon"=>$data->telepon_rumah, 
			"handphone"=>$data->telepon_seluler, 
			"email"=>$data->email, 
			"penerima_kps"=>$data->a_terima_kps,
			"nomor_kps"=>$data->no_kps, 
			//"npwp", 
			"id_wilayah"=>$data->id_wil,
			"id_jenis_tinggal"=>$data->id_jns_tinggal, 
			"id_agama"=>$data->id_agama,
			"id_alat_transportasi"=>$data->id_alat_transport, 
			"kewarganegaraan"=>$data->kewarganegaraan
		];
	} else {
		$sync['act']="UpdateBiodataMahasiswa";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			'id_mahasiswa'=>$data->id_mahasiswa
		];
		$sync['record']=[
			//"nama_mahasiswa"=>$data->nm_pd, 
			"jenis_kelamin"=>$data->jk, 
			"jalan"=>$data->jln, 
			"rt"=>$data->rt, 
			"rw"=>$data->rw, 
			"dusun"=>$data->nm_dsn, 
			"kelurahan"=>$data->ds_kel,
			"kode_pos"=>$data->kode_pos, 
			//"nisn"=>$data->nisn, 
			"nik"=>$data->nik,
			//"tempat_lahir"=>$data->tmpt_lahir,
			//"tanggal_lahir"=>$data->tgl_lahir, 
			"nama_ayah"=>$data->nm_ayah, 
			"tanggal_lahir_ayah"=>$data->tgl_lahir_ayah, 
			//"nik_ayah"=>"", 
			"id_pendidikan_ayah"=>$data->id_jenjang_pendidikan_ayah, 
			"id_pekerjaan_ayah"=>$data->id_pekerjaan_ayah, 
			"id_penghasilan_ayah"=>$data->id_penghasilan_ayah, 
			"id_kebutuhan_khusus_ayah"=>$data->id_kebutuhan_khusus_ayah, 
			//"nama_ibu_kandung"=>$data->nm_ibu_kandung, 
			"tanggal_lahir_ibu"=>$data->tgl_lahir_ibu, 
			//"nik_ibu", 
			"id_pendidikan_ibu"=>$data->id_jenjang_pendidikan_ibu, 
			"id_pekerjaan_ibu"=>$data->id_pekerjaan_ibu, 
			"id_penghasilan_ibu"=>$data->id_penghasilan_ibu, 
			"id_kebutuhan_khusus_ibu"=>$data->id_kebutuhan_khusus_ibu, 
			"nama_wali"=>$data->nm_wali, 
			"tanggal_lahir_wali"=>$data->tgl_lahir_wali, 
			"id_pendidikan_wali"=>$data->id_jenjang_pendidikan_wali, 
			"id_pekerjaan_wali"=>$data->id_pekerjaan_wali, 
			"id_penghasilan_wali"=>$data->id_penghasilan_wali, 
			"id_kebutuhan_khusus_mahasiswa"=>$data->id_kk,
			"telepon"=>$data->telepon_rumah, 
			"handphone"=>$data->telepon_seluler, 
			"email"=>$data->email, 
			"penerima_kps"=>$data->a_terima_kps,
			"nomor_kps"=>$data->no_kps, 
			//"npwp", 
			"id_wilayah"=>$data->id_wil,
			"id_jenis_tinggal"=>$data->id_jns_tinggal, 
			"id_agama"=>$data->id_agama,
			"id_alat_transportasi"=>$data->id_alat_transport, 
			"kewarganegaraan"=>$data->kewarganegaraan
		];
	}

	$token_str = $feeder_token->data->token ?? '';
	$sync['token'] = $token_str;

	$runWS = json_decode(runWs($sync,'json'));

	// Auto-refresh token if expired
	if ($runWS && $runWS->error_code == 100) {
		$feeder_token = json_decode(token());
		$token_str = $feeder_token->data->token ?? '';
		$sync['token'] = $token_str;
		$runWS = json_decode(runWs($sync,'json'));
	}

	if (!$runWS) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal menghubungi Feeder atau format response tidak valid.";
		echo json_encode($hasil);
		exit();
	}

	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_mahasiswa=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_mahasiswa = $runWS->data->id_mahasiswa;
		$qryUpdate = "update wsia_mahasiswa set id_pd='$id_mahasiswa', sync_at='$sync_at' where xid_pd='$data->xid_pd'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah data Mahasiswa di Feeder"." ".$result_data;
    		$hasil['data']=$data;
    		$hasil['feeder_token']=$_SESSION['feeder_token'];
    		$hasil['feeder_result']=$runWS;
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']=$salah->getMessage()." ".$result_data;
	    	$hasil['data']=$data;
	    	$hasil['feeder_token']=$_SESSION['feeder_token'];
    		$hasil['feeder_result']=$runWS;
	    	echo json_encode($hasil);
		}

	} else if ($runWS->error_code==0 && $data->id_mahasiswa!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_mahasiswa set sync_at='$sync_at' where xid_pd='$data->xid_pd'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah data Mahasiswa di Feeder"." ".$result_data;
    		$hasil['data']=$data;
    		$hasil['feeder_token']=$_SESSION['feeder_token'];
    		$hasil['feeder_result']=$runWS;
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']=$salah->getMessage()." ".$result_data;
	    	$hasil['data']=$data;
	    	$hasil['feeder_token']=$_SESSION['feeder_token'];
    		$hasil['feeder_result']=$runWS;
	    	echo json_encode($hasil);
		}
	} else {
		$hasil['berhasil']=0;
    	$hasil['pesan']=$error_desc." ".$result_data;
    	$hasil['data']=$data;
    	$hasil['feeder_token']=$_SESSION['feeder_token'];
    	$hasil['feeder_result']=$runWS;
    	echo json_encode($hasil);
	}
	return $hasil;
}
