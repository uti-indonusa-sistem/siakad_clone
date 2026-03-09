<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }
$ta = $_SESSION['ta'];

if ($aksi=="tampil") {	  
	  
	  //mulai 20231 saja
	  $perintah = "select * from viewKelasKuliah where id_smt>20222 and id_smt='$ta' and (id_kls='' OR id_kls IS NULL) and id_sms_feeder<>'' and id_mk_feeder<>'' and mbkm=0";
	  
	  $perintah .= isset($_GET['filter']['nm_mk'])?" and nm_mk like '%".$_GET['filter']['nm_mk']."%'":"";
	  $perintah .= isset($_GET['filter']['nm_kls'])?" and nm_kls like '%".$_GET['filter']['nm_kls']."%'":"";
	  $perintah .= isset($_GET['filter']['xid_sms'])?" and xid_sms like '%".$_GET['filter']['xid_sms']."%'":"";
	  
	  
	  $perintah.=" order by nm_mk, nm_kls asc";
	  
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
				$itemData->statusSync = (empty($itemData->id_kls) ? 0 : 1);
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

	if ($data->id_kls=="") {
		$sync['act']="InsertKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"id_prodi"=>$data->id_sms_feeder, 
			"id_semester"=>$data->id_smt, 
			"id_matkul"=>$data->id_mk_feeder, 
			"nama_kelas_kuliah"=>$data->nm_kls, 
			"bahasan"=>$data->bahasan_case ?? "", 
			"tanggal_mulai_efektif"=>$data->tgl_mulai_koas ?? "", 
            "tanggal_akhir_efektif"=>$data->tgl_selesai_koas ?? ""
		];
	} else {
		$sync['act']="UpdateKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			'id_kelas_kuliah'=>$data->id_kls
		];
		$sync['record']=[
			"id_prodi"=>$data->id_sms_feeder, 
			"id_semester"=>$data->id_smt, 
			"id_matkul"=>$data->id_mk_feeder, 
			"nama_kelas_kuliah"=>$data->nm_kls, 
			"bahasan"=>$data->bahasan_case ?? "", 
			"tanggal_mulai_efektif"=>$data->tgl_mulai_koas ?? "", 
            "tanggal_akhir_efektif"=>$data->tgl_selesai_koas ?? ""
		];
	}

	$token_str = $feeder_token->data->token ?? '';
	$sync['token'] = $token_str;

	$runWS = json_decode(runWs($sync,'json'));

	// Auto-refresh token result if expired
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

	if ($runWS->error_code==0 && $data->id_kls=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_kls = $runWS->data->id_kelas_kuliah;
		$qryUpdate = "update wsia_kelas_kuliah set id_kls='$id_kls' where xid_kls='$data->xid_kls'  and (id_kls='' OR id_kls IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah data Kelas Kuliah di Feeder"." ".$result_data;
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

	} else if ($runWS->error_code==0 && $data->id_kls!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_kelas_kuliah set xid_kls='$data->xid_kls' where xid_kls='$data->xid_kls'"; // Dummy update since there is no sync_at column
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah data Kelas Kuliah di Feeder"." ".$result_data;
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
	} else if (($runWS->error_code==15 || $runWS->error_code==700) && $data->id_kls=="") {
		// Jika gagal karena sudah ada, coba ambil ID-nya
		$syncGet['act']="GetDetailKelasKuliah";
		$syncGet['token']=$feeder_token->data->token;
		$syncGet['filter']="id_prodi = '".$data->id_sms_feeder."' and id_matkul = '".$data->id_mk_feeder."' and nama_kelas_kuliah = '".$data->nm_kls."' and id_semester = '".$data->id_smt."'";
        $syncGet['order']="nama_kelas_kuliah";
        $syncGet['limit']=1;
        $syncGet['offset']=0;
		$runWSGet = json_decode(runWs($syncGet,'json'));

		if ($runWSGet->error_code==0 && count($runWSGet->data)>0) {
			$id_kls = $runWSGet->data[0]->id_kelas_kuliah;
			$sync_at = date("Y-m-d H:i:s");
			$qryUpdate = "update wsia_kelas_kuliah set id_kls='$id_kls' where xid_kls='$data->xid_kls'";
			try {
				$db 		= koneksi();
				$eksekusi 	= $db->query($qryUpdate);  
				$db = null;
				$hasil['berhasil']=1;
				$hasil['pesan']="Berhasil ambil ID Kelas yang sudah ada di Feeder: ".$id_kls;
				$hasil['data']=$data;
				$hasil['feeder_token']=$_SESSION['feeder_token'];
				$hasil['feeder_result']=$runWSGet;
				echo json_encode($hasil);
			} catch (PDOException $salah) {
				$hasil['berhasil']=0;
				$hasil['pesan']=$salah->getMessage();
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
	

} else if ($aksi=="getKelasPerkuliahan") {

    if (!isset($_SESSION['feeder_token']) || empty($_SESSION['feeder_token'])) {
		token();
	}
    $feeder_token = json_decode($_SESSION['feeder_token'] ?? '');

	if ($data->id_kls=="") {
		$sync['act']="GetDetailKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="id_prodi = '".$data->id_sms_feeder."' and id_matkul = '".$data->id_mk_feeder."' and nama_kelas_kuliah = '".$data->nm_kls."' and id_semester = '".$data->id_smt."'";
        $sync['order']="nama_kelas_kuliah";
        $sync['limit']=1;
        $sync['offset']=0;
        
	} else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Sudah ada id_kls";
    	$hasil['data']=$data;
    	echo json_encode($hasil);
        exit();
    }

    $runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_kls=="") {
        if (count($runWS->data)>0) {

            $id_kelas_kuliah = $runWS->data[0]->id_kelas_kuliah;
           
            $qryUpdate = "update wsia_kelas_kuliah set id_kls='$id_kelas_kuliah' where xid_kls='$data->xid_kls' and (id_kls='' OR id_kls IS NULL)";
            try {
                $db 		= koneksi();
                $eksekusi 	= $db->query($qryUpdate);  
                $db = null;
                $hasil['berhasil']=1;
                $hasil['pesan']="Berhasil Ubah data Kelas Perkuliahan di SIAKAD: ".$id_kelas_kuliah;
                $hasil['data']=$data;
                $hasil['feeder_token']=$_SESSION['feeder_token'];
                $hasil['feeder_result']=$runWS;
                $hasil['id_kelas_kuliah']=$id_kelas_kuliah;
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
            $hasil['pesan']="Tidak ditemukan id_kls di FEEDER. Hubungi IT";
            $hasil['data']=$data;
            $hasil['sync']=$sync;
            $hasil['feeder_result']=$runWS;
            echo json_encode($hasil);
            exit();
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