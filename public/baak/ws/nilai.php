<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }
$ta = $_SESSION['ta'];

if ($aksi=="tampil") {

	$perintah = "
	SELECT
		*
	FROM
		viewNilaiSync
	WHERE
		id_smt = '$ta'
		AND id_kls_feeder<>''
		AND id_reg_pd<>''
	    AND mbkm=0
		AND (updated_at <> '0000-00-00 00:00:00' OR updated_at IS NULL)
		AND ( updated_at > sync_at OR sync_at='0000-00-00 00:00:00' OR sync_at IS NULL )";

	$perintah .= isset($_GET['filter']['kelas'])?" and kelas like '%".$_GET['filter']['kelas']."%'":"";
	$perintah .= isset($_GET['filter']['mahasiswa'])?" and mahasiswa like '%".$_GET['filter']['mahasiswa']."%'":"";
	$perintah .= isset($_GET['filter']['id_sms'])?" and id_sms like '%".$_GET['filter']['id_sms']."%'":"";
	
	$perintah.=" order by vid_kls, nipd";
	
	$perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:'  LIMIT 500';
	$perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	
	try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
		
		$data	= $qry->fetchAll(PDO::FETCH_OBJ);
		$db		= null;
		
		foreach ($data as $itemData) {
			
			if ($itemData->sync_at=='0000-00-00 00:00:00' || is_null($itemData->sync_at)) {
				$itemData->statusSync=0;
			} else if ($itemData->sync_at < $itemData->updated_at) {
				$itemData->statusSync=2;
			} else {
				$itemData->statusSync=1;
			}
		}
		echo json_encode($data);
		
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage() );
	}

} else if ($aksi=="sync") {	
	if (!isset($_SESSION['feeder_token']) || empty($_SESSION['feeder_token'])) {
		token();
	}
	$feeder_token = json_decode($_SESSION['feeder_token'] ?? '');

	if ($data->sync_at=="0000-00-00 00:00:00" || $data->sync_at==null) {
		/*
		$sync=[
			"id_kls"=>$data->id_kls_feeder,
			"id_reg_pd"=>$data->id_reg_pd_feeder,
            "nilai_angka"=>$data->nilai_angka,
			"nilai_huruf"=>$data->nilai_huruf, 
			"nilai_indeks"=>$data->nilai_indeks
		];
        $wsRecord=proxy()->InsertRecord($feeder_token->data->token,"nilai",json_encode($sync));
		*/

		$sync['act']="InsertPesertaKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"id_kelas_kuliah" =>$data->id_kls_feeder,
			"id_registrasi_mahasiswa"=>$data->id_reg_pd,
            "nilai_angka"=>$data->nilai_angka,
			"nilai_huruf"=>$data->nilai_huruf, 
			"nilai_indeks"=>$data->nilai_indeks
		];

		$sync_nilai['act']="UpdateNilaiPerkuliahanKelas";
		$sync_nilai['token']=$feeder_token->data->token;
		$sync_nilai['key']=[
			"id_kelas_kuliah" =>$data->id_kls_feeder,
			"id_registrasi_mahasiswa"=>$data->id_reg_pd,
		];

		$sync_nilai['record']=[
			"nilai_angka"=>$data->nilai_angka,
			"nilai_huruf"=>$data->nilai_huruf, 
			"nilai_indeks"=>$data->nilai_indeks
		];

	} else {

		/*
        $sync=[
            "key"=>[
                "id_kls"=>$data->id_kls_feeder,
			    "id_reg_pd"=>$data->id_reg_pd_feeder,
            ],
            "data"=>[
                "nilai_angka"=>$data->nilai_angka,
                "nilai_huruf"=>$data->nilai_huruf, 
                "nilai_indeks"=>$data->nilai_indeks
            ]
        ];
		$wsRecord=proxy()->UpdateRecord($feeder_token->data->token,"nilai",json_encode($sync));
		*/

		$sync['act']="UpdateNilaiPerkuliahanKelas";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			"id_kelas_kuliah" =>$data->id_kls_feeder,
			"id_registrasi_mahasiswa"=>$data->id_reg_pd,
		];
		$sync['record']=[
			"nilai_angka"=>$data->nilai_angka,
			"nilai_huruf"=>$data->nilai_huruf, 
			"nilai_indeks"=>$data->nilai_indeks
		];

	}

    // $runWSJson = (object) $wsRecord;
	// $runWS = (object) $runWSJson->result;

	$token_str = $feeder_token->data->token ?? '';
	$sync['token'] = $token_str;
	if (isset($sync_nilai)) {
		$sync_nilai['token'] = $token_str;
	}

	$runWS = json_decode(runWs($sync,'json'));

	// Auto-refresh token result if expired
	if ($runWS && $runWS->error_code == 100) {
		$feeder_token = json_decode(token());
		$token_str = $feeder_token->data->token ?? '';
		$sync['token'] = $token_str;
		if (isset($sync_nilai)) {
			$sync_nilai['token'] = $token_str;
		}
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
   
	if (($runWS->error_code==0 || $runWS->error_code==119) && ($data->sync_at=="0000-00-00 00:00:00" || $data->sync_at==null)) {
		
		$runWS_nilai = json_decode(runWs($sync_nilai,'json'));
		if ($runWS_nilai && $runWS_nilai->error_code == 100) {
			$feeder_token = json_decode(token());
			$sync_nilai['token'] = $feeder_token->data->token ?? '';
			$runWS_nilai = json_decode(runWs($sync_nilai,'json'));
		}
		$error_desc_nilai = $runWS_nilai->error_desc ?? '';
		$result_data_nilai = str_replace(",",", ",json_encode($runWS_nilai->data ?? ''));
		$sync_at = date("Y-m-d H:i:s");
		$tanggal_sync = date_create($sync_at);
		date_add($tanggal_sync,date_interval_create_from_date_string('1 seconds'));
		
		if ($runWS_nilai->error_code==0) {

			$qryUpdate = "update wsia_nilai set sync_at='$sync_at' where id_nilai='$data->id_nilai' and (sync_at='0000-00-00 00:00:00' OR sync_at IS NULL)";
			try {
				$db 		= koneksi();
				$eksekusi 	= $db->query($qryUpdate);  
				$db = null;
				$hasil['berhasil']=1;
				$hasil['pesan']=($runWS->error_code==119 ? "KRS sudah ada di Feeder, " : "Berhasil Tambah data KRS dipadu, ")."Update Nilai Berhasil. ".$result_data_nilai;
				$hasil['data']=$data;
				$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
				$hasil['feeder_result']=$runWS;
				echo json_encode($hasil);
			} catch (PDOException $salah) {
				$hasil['berhasil']=0;
				$hasil['pesan']=$salah->getMessage()." ".$result_data;
				$hasil['data']=$data;
				$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
				$hasil['feeder_result']=$runWS;
				echo json_encode($hasil);
			}
		
		} else {
			$qryUpdate = "update wsia_nilai set sync_at='$sync_at', updated_at='$tanggal_sync' where id_nilai='$data->id_nilai'";
			try {
				$db 		= koneksi();
				$eksekusi 	= $db->query($qryUpdate);  
				$db = null;
				$hasil['berhasil']=3;
				$hasil['pesan']=($runWS->error_code==119 ? "KRS sudah ada di Feeder, " : "KRS Berhasil, ")."tapi Gagal Update Nilai. ".$error_desc_nilai;
				$hasil['data']=$data;
				$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
				$hasil['feeder_result']=$runWS;
				$hasil['feeder_result_nilai']=$runWS_nilai;
				echo json_encode($hasil);
			} catch (PDOException $salah) {
				$hasil['berhasil']=0;
				$hasil['pesan']=$salah->getMessage()." ".$result_data;
				$hasil['data']=$data;
				$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
				$hasil['feeder_result']=$runWS;
				$hasil['feeder_result_nilai']=$runWS_nilai;
				echo json_encode($hasil);
			}
		}
		

	} else if (($runWS->error_code==0 || $runWS->error_code==800 || $runWS->error_code==119) && $data->id_nilai!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_nilai set sync_at='$sync_at' where id_nilai='$data->id_nilai'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah data Nilai di Feeder"." ".$result_data;
    		$hasil['data']=$data;
    		$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
    		$hasil['feeder_result']=$runWS;
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']=$salah->getMessage()." ".$result_data;
	    	$hasil['data']=$data;
	    	$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
    		$hasil['feeder_result']=$runWS;
	    	echo json_encode($hasil);
		}
	} else if ($runWS->error_code==800 && $data->id_nilai!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_nilai set sync_at='$sync_at' where id_nilai='$data->id_nilai'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil menandai sudah ada Nilai di Feeder"." ".$result_data;
    		$hasil['data']=$data;
    		$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
    		$hasil['feeder_result']=$runWS;
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']=$salah->getMessage()." ".$result_data;
	    	$hasil['data']=$data;
	    	$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
    		$hasil['feeder_result']=$runWS;
	    	echo json_encode($hasil);
		}
	} else {
		$hasil['berhasil']=0;
    	$hasil['pesan']=$error_desc." ".$result_data;
    	$hasil['data']=$data;
    	$hasil['feeder_token']=$_SESSION['feeder_token'] ?? null;
    	$hasil['feeder_result']=$runWS;
    	echo json_encode($hasil);
	}
	return $hasil;

} 