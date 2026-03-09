<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	

	$ta=$_SESSION['ta'];
	$perintah="select * from viewSyncKuliahMahasiswa where id_smt='$ta' and (updated_at > sync_at OR sync_at = '0000-00-00 00:00:00' OR sync_at IS NULL)";

    $perintah .= isset($_GET['filter']['nipd'])?" and nipd like '%".$_GET['filter']['nipd']."%'":"";
    $perintah .= isset($_GET['filter']['nm_pd'])?" and nm_pd like '%".$_GET['filter']['nm_pd']."%'":"";
	$perintah .= isset($_GET['filter']['xid_sms'])?" and xid_sms like '%".$_GET['filter']['xid_sms']."%'":"";
	
	$perintah .= isset($_GET['filter']['id_stat_mhs'])?" and id_stat_mhs like '%".$_GET['filter']['id_stat_mhs']."%'":"";
	$perintah .= isset($_GET['filter']['ips'])?" and ips like '%".$_GET['filter']['ips']."%'":"";
	$perintah .= isset($_GET['filter']['ipk'])?" and ipk like '%".$_GET['filter']['ipk']."%'":"";
	$perintah .= isset($_GET['filter']['sks_smt'])?" and sks_smt like '%".$_GET['filter']['sks_smt']."%'":"";
	$perintah .= isset($_GET['filter']['sks_total'])?" and sks_smt like '%".$_GET['filter']['sks_total']."%'":"";
	
	$perintah.="  order by nipd ";

	$perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:'  LIMIT 500';
	$pos = isset($_GET['start'])?$_GET['start']:0;
	$perintah .= ' OFFSET '.$pos;

	try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
		$data		= $qry->fetchAll(PDO::FETCH_OBJ);


		foreach ($data as $itemData) {
			if ($itemData->sync_at == "0000-00-00 00:00:00" || is_null($itemData->sync_at)) {
				$itemData->statusSync=0;
			} else if ($itemData->sync_at < $itemData->updated_at) {
				$itemData->statusSync=2;
			} else {
				$itemData->statusSync=1;
			}
		}

		$db		= null;

		echo json_encode($data);
		
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage() );
	}

} else if ($aksi=="sync") {	
	if (!isset($_SESSION['feeder_token']) || empty($_SESSION['feeder_token'])) {
		token();
	}
	$feeder_token = json_decode($_SESSION['feeder_token'] ?? '');

	if ($data->sync_at=="0000-00-00 00:00:00" || $data->sync_at=="") {
		$sync['act']="InsertPerkuliahanMahasiswa";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"id_registrasi_mahasiswa"=>$data->id_reg_pd_feeder, 
			"id_semester"=>$data->id_smt, 
			"id_status_mahasiswa"=>$data->id_stat_mhs, 
			"ips"=>$data->ips, 
			"ipk"=>$data->ipk, 
			"sks_semester"=>$data->sks_smt, 
			"total_sks"=>$data->sks_total,
			"biaya_kuliah_smt"=>$data->biaya_smt, //7-1-2024 update frontend
			"id_pembiayaan"=>$data->id_pembiayaan, //7-1-2024 update frontend
		];
	} else {
		$sync['act']="UpdatePerkuliahanMahasiswa";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			"id_registrasi_mahasiswa"=>$data->id_reg_pd_feeder, 
			"id_semester"=>$data->id_smt
		];
		$sync['record']=[
			"id_status_mahasiswa"=>$data->id_stat_mhs, 
			"ips"=>$data->ips, 
			"ipk"=>$data->ipk, 
			"sks_semester"=>$data->sks_smt, 
			"total_sks"=>$data->sks_total,
			"biaya_kuliah_smt"=>$data->biaya_smt, //7-1-2024 update frontend
			"id_pembiayaan"=>$data->id_pembiayaan, //7-1-2024 update frontend
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

	if ($runWS->error_code==0) {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_kuliah_mahasiswa set sync_at='$sync_at' where id_aktifitas='$data->id_aktifitas'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Syncron Kuliah Mahasiswa di Feeder"." ".$result_data;
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
	} else if ($runWS->error_code==730) {

		$sync['act']="UpdatePerkuliahanMahasiswa";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			"id_registrasi_mahasiswa"=>$data->id_reg_pd_feeder, 
			"id_semester"=>$data->id_smt
		];
		$sync['record']=[
			"id_status_mahasiswa"=>$data->id_stat_mhs, 
			"ips"=>$data->ips, 
			"ipk"=>$data->ipk, 
			"sks_semester"=>$data->sks_smt, 
			"total_sks"=>$data->sks_total,
			"biaya_kuliah_smt"=>$data->biaya_smt, //7-1-2024 update frontend
			"id_pembiayaan"=>$data->id_pembiayaan, //7-1-2024 update frontend
		];

		$runWS = json_decode(runWs($sync,'json'));
		//$error_desc = error_status($runWS->error_code);
		$error_desc = $runWS->error_desc;
		$result_data = str_replace(",",", ",json_encode($runWS->data));

		if ($runWS->error_code==0) {
			$sync_at = date("Y-m-d H:i:s");
			$qryUpdate = "update wsia_kuliah_mahasiswa set sync_at='$sync_at' where id_aktifitas='$data->id_aktifitas'";
			try {
				$db 		= koneksi();
				$eksekusi 	= $db->query($qryUpdate);  
				$db = null;
				$hasil['berhasil']=1;
				$hasil['pesan']="Berhasil Syncron Kuliah Mahasiswa di Feeder"." ".$result_data;
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

		
	} else {
		$hasil['berhasil']=0;
    	$hasil['pesan']=$error_desc." ".$result_data;
    	$hasil['data']=$data;
    	$hasil['feeder_token']=$_SESSION['feeder_token'];
    	$hasil['feeder_result']=$runWS;
    	echo json_encode($hasil);
	}
	

} 