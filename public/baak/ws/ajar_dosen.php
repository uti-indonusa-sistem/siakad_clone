<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }
$ta = $_SESSION['ta'];

if ($aksi=="tampil") {	  
	  
	  $perintah = "select * from viewAjarDosen where id_smt>20222 and id_smt='$ta' and (id_ajar='' OR id_ajar IS NULL) and id_reg_ptk_feeder<>'' and id_kls_feeder<>'' ";
	  
	  $perintah .= isset($_GET['filter']['mk'])?" and mk like '%".$_GET['filter']['mk']."%'":"";
	  $perintah .= isset($_GET['filter']['nm_kls'])?" and nm_kls like '%".$_GET['filter']['nm_kls']."%'":"";
	  $perintah .= isset($_GET['filter']['xid_sms'])?" and xid_sms like '%".$_GET['filter']['xid_sms']."%'":"";
	  $perintah .= isset($_GET['filter']['nm_ptk'])?" and nm_ptk like '%".$_GET['filter']['nm_ptk']."%'":"";
	  
	  
	  $perintah.=" order by nm_mk, nm_kls asc";
	  
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 50';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		    $dataA=array();
		    foreach ($data as $itemData) {
				$itemData->statusSync = (empty($itemData->id_ajar) ? 0 : 1);
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

	if ($data->id_ajar=="") {
		$sync['act']="InsertDosenPengajarKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"id_registrasi_dosen"=>$data->id_reg_ptk_feeder,
			"id_kelas_kuliah"=>$data->id_kls_feeder,
            "sks_substansi_total"=>$data->sks_subst_tot,
			"rencana_minggu_pertemuan"=>$data->jml_tm_renc, 
			"realisasi_minggu_pertemuan"=>$data->jml_tm_real, 
			"id_jenis_evaluasi"=>$data->id_jns_eval
		];
	} else {
		$sync['act']="UpdateDosenPengajarKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			'id_aktivitas_mengajar'=>$data->id_ajar
		];
		$sync['record']=[
			"id_registrasi_dosen"=>$data->id_reg_ptk_feeder,
			"id_kelas_kuliah"=>$data->id_kls_feeder,
            "sks_substansi_total"=>$data->sks_subst_tot,
			"rencana_minggu_pertemuan"=>$data->jml_tm_renc, 
			"realisasi_minggu_pertemuan"=>$data->jml_tm_real, 
			"id_jenis_evaluasi"=>$data->id_jns_eval
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

	if ($runWS->error_code==0 && $data->id_ajar=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_ajar = $runWS->data->id_aktivitas_mengajar;
		$qryUpdate = "update wsia_ajar_dosen set id_ajar='$id_ajar' where xid_ajar='$data->xid_ajar' and (id_ajar='' OR id_ajar IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah data Ajar Dosen di Feeder"." ".$result_data;
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

	} else if ($runWS->error_code==0 && $data->id_ajar!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_ajar_dosen set xid_ajar='$data->xid_ajar' where xid_ajar='$data->xid_ajar'"; // Dummy update since there is no sync_at column
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah data Ajar Dosen di Feeder"." ".$result_data;
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
	

} else if ($aksi=="getAjarDosen") {

    if (!isset($_SESSION['feeder_token']) || empty($_SESSION['feeder_token'])) {
		token();
	}
    $feeder_token = json_decode($_SESSION['feeder_token'] ?? '');

	if ($data->id_ajar=="") {
		$sync['act']="GetDosenPengajarKelasKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="id_registrasi_dosen = '".$data->id_reg_ptk_feeder."' and id_kelas_kuliah = '".$data->id_kls_feeder."'";
        $sync['order']="nama_kelas_kuliah";
        $sync['limit']=1;
        $sync['offset']=0;
        
	} else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Sudah ada id_ajar";
    	$hasil['data']=$data;
    	echo json_encode($hasil);
        exit();
    }

    $runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_ajar=="") {
        if (count($runWS->data)>0) {

            $id_aktivitas_mengajar = $runWS->data[0]->id_aktivitas_mengajar;
           
            $qryUpdate = "update wsia_ajar_dosen set id_ajar='$id_aktivitas_mengajar' where xid_ajar='$data->xid_ajar' and (id_ajar='' OR id_ajar IS NULL)";
            try {
                $db 		= koneksi();
                $eksekusi 	= $db->query($qryUpdate);  
                $db = null;
                $hasil['berhasil']=1;
                $hasil['pesan']="Berhasil Ubah data ajar dosen di SIAKAD: ".$id_aktivitas_mengajar;
                $hasil['data']=$data;
                $hasil['feeder_token']=$_SESSION['feeder_token'];
                $hasil['feeder_result']=$runWS;
                $hasil['id_aktivitas_mengajar']=$id_aktivitas_mengajar;
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