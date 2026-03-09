<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select * from viewMataKuliahKurikulum where (id_mk_kurikulum_feeder='' OR id_mk_kurikulum_feeder IS NULL) and id_mk<>'' and id_kurikulum_sp<>'' ";
	  
	  $perintah .= isset($_GET['filter']['mk'])?" and mk like '%".$_GET['filter']['mk']."%'":"";
	  $perintah .= isset($_GET['filter']['nm_kurikulum_sp'])?" and nm_kurikulum_sp like '%".$_GET['filter']['nm_kurikulum_sp']."%'":"";
	  $perintah .= isset($_GET['filter']['id_sms'])?" and id_sms like '%".$_GET['filter']['id_sms']."%'":"";
	  
	  
	  $perintah.=" order by nm_kurikulum_sp, mk asc";
	  
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
				$itemData->statusSync=0;
				array_push($dataA,$itemData);
		   	}
			
		    echo json_encode($dataA);
		    $db		= null;
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="sync") {	
	$feeder_token = json_decode($_SESSION['feeder_token']);

	if ($data->id_mk_kurikulum_feeder=="") {
		$sync['act']="InsertMatkulKurikulum";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"id_kurikulum"=>$data->id_kurikulum_sp, 
			"id_matkul"=>$data->id_mk, 
			"semester"=>floatval($data->smt), 
			"sks_mata_kuliah"=>floatval($data->sks_mk), 
			"sks_tatap_muka"=>floatval($data->sks_tm),
			"sks_praktek"=>floatval($data->sks_prak), 
			"sks_praktek_lapangan"=>floatval($data->sks_prak_lap),
			"sks_simulasi"=>floatval($data->sks_sim),
			"apakah_wajib"=>floatval($data->a_wajib)
		];
	} else {
		$hasil['berhasil']=0;
        $hasil['pesan']="Sudah di syncronkan";
        $hasil['data']=$data;
        $hasil['feeder_token']=$_SESSION['feeder_token'];
        echo json_encode($hasil);
        exit();
	}

	$runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_mk_kurikulum_feeder=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_mk_kurikulum_feeder = md5($runWS->data->id_kurikulum.$runWS->data->id_matkul);
		$qryUpdate = "update wsia_mata_kuliah_kurikulum set id_mk_kurikulum_feeder='$id_mk_kurikulum_feeder' where id_mk_kurikulum='$data->id_mk_kurikulum'  and id_mk_kurikulum_feeder=''";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah data Mata Kuliah Kurikulum di Feeder"." ".$result_data;
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
		$hasil['sync']=$sync;
    	echo json_encode($hasil);
	}
	

} else if ($aksi=="getMataKuliahKurikulum") {

    $feeder_token = json_decode($_SESSION['feeder_token']);

	if ($data->id_mk_kurikulum_feeder=="") {
		$sync['act']="GetMatkulKurikulum";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="id_kurikulum = '".$data->id_kurikulum_sp."' and id_matkul = '".$data->id_mk."'";
        $sync['order']="nama_kurikulum";
        $sync['limit']=1;
        $sync['offset']=0;
        
	} else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Sudah ada id_mk_kurikulum_feeder";
    	$hasil['data']=$data;
    	echo json_encode($hasil);
        exit();
    }

    $runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_mk_kurikulum_feeder=="") {
        if (count($runWS->data)>0) {

            $id_kurikulum = $runWS->data[0]->id_kurikulum;
            $id_mk = $runWS->data[0]->id_matkul;
            $id_mk_kurikulum_feeder = md5($id_kurikulum.$id_mk);
            $sync_at = date("Y-m-d H:i:s");
           
            $qryUpdate = "update wsia_mata_kuliah_kurikulum set id_mk_kurikulum_feeder='$id_mk_kurikulum_feeder' where id_mk_kurikulum='$data->id_mk_kurikulum' and id_mk_kurikulum_feeder=''";
            try {
                $db 		= koneksi();
                $eksekusi 	= $db->query($qryUpdate);  
                $db = null;
                $hasil['berhasil']=1;
                $hasil['pesan']="Berhasil Ubah data Mata Kuliah Kurikulum di SIAKAD: ".$id_mk;
                $hasil['data']=$data;
                $hasil['feeder_token']=$_SESSION['feeder_token'];
                $hasil['feeder_result']=$runWS;
                $hasil['id_mk_kurikulum_feeder']=$id_mk_kurikulum_feeder;
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
            $hasil['pesan']="Tidak ditemukan id_kurikulum dan id_matkul di FEEDER. Hubungi IT";
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
	
} else if ($aksi=="validasiMataKuliahKurikulum") {

    $feeder_token = json_decode($_SESSION['feeder_token']);

	$perintah = "select *  from view_mata_kuliah_kurikulum where id_mk_kurikulum_feeder='' and id_kurikulum_sp<>'' and id_kurikulum_sp<>'-' and id_mk='' and isnull(created_at) limit 100";

	$perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';

	try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
	  
		$dataMkKurikulum	= $qry->fetchAll(PDO::FETCH_OBJ);

		if ($qry->rowCount()==0) {
			exit("data habis di offset: ".$id);
		}
		
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage() );
	}

	foreach($dataMkKurikulum as $data) {

		if ($data->id_mk_kurikulum_feeder=="") {
			$sync['act']="GetMatkulKurikulum";
			$sync['token']=$feeder_token->data->token;
			$sync['filter']="id_kurikulum = '".$data->id_kurikulum_sp."' and id_matkul = '".$data->id_mk."'";
			$sync['order']="nama_kurikulum";
			$sync['limit']=1;
			$sync['offset']=0;
			
		} else {
			$hasil['berhasil']=0;
			$hasil['pesan']="Sudah ada id_mk_kurikulum_feeder";
			$hasil['data']=$data;
			echo json_encode($hasil);
			exit();
		}

		$runWS = json_decode(runWs($sync,'json'));
		//$error_desc = error_status($runWS->error_code);
		$error_desc = $runWS->error_desc;
		$result_data = str_replace(",",", ",json_encode($runWS->data));

		if ($runWS->error_code==0 && $data->id_mk_kurikulum_feeder=="") {
			if (count($runWS->data)>0) {

				$id_kurikulum = $runWS->data[0]->id_kurikulum;
				$id_mk = $runWS->data[0]->id_matkul;
				$id_mk_kurikulum_feeder = md5($id_kurikulum.$id_mk);
				$sync_at = date("Y-m-d H:i:s");
			
				$qryUpdate = "update wsia_mata_kuliah_kurikulum set id_mk_kurikulum_feeder='$id_mk_kurikulum_feeder' where id_mk_kurikulum='$data->id_mk_kurikulum' and id_mk_kurikulum_feeder=''";
				try {
					$db 		= koneksi();
					$eksekusi 	= $db->query($qryUpdate);  
					$db = null;
					$hasil['berhasil']=1;
					$hasil['pesan']="Berhasil Ubah data Mata Kuliah Kurikulum di Wsia: ".$id_mk;
					//$hasil['data']=$data;
					// $hasil['feeder_token']=$_SESSION['feeder_token'];
					// $hasil['feeder_result']=$runWS;
					// $hasil['id_mk_kurikulum_feeder']=$id_mk_kurikulum_feeder;
					echo json_encode($hasil);
				} catch (PDOException $salah) {
					$hasil['berhasil']=0;
					$hasil['pesan']=$salah->getMessage()." ".$result_data;
					// $hasil['data']=$data;
					// $hasil['feeder_token']=$_SESSION['feeder_token'];
					// $hasil['feeder_result']=$runWS;
					echo json_encode($hasil);
				}

			} else {

				$sync_at = date("Y-m-d H:i:s");
				$qryUpdate = "update wsia_mata_kuliah_kurikulum set id_mk_kurikulum_feeder='' where id_mk_kurikulum='$data->id_mk_kurikulum' and id_mk_kurikulum_feeder=''"; // Dummy update to mark as checked
				try {
					$db 		= koneksi();
					$eksekusi 	= $db->query($qryUpdate);  
					$db = null;
					$hasil['berhasil']=1;
					$hasil['pesan']="Berhasil Ubah created_at Mata Kuliah Kurikulum di Wsia: ".$data->id_mk_kurikulum;
					//$hasil['data']=$data;
					// $hasil['feeder_token']=$_SESSION['feeder_token'];
					// $hasil['feeder_result']=$runWS;
					// $hasil['id_mk_kurikulum_feeder']=$id_mk_kurikulum_feeder;
					echo json_encode($hasil);
				} catch (PDOException $salah) {
					$hasil['berhasil']=0;
					$hasil['pesan']=$salah->getMessage()." ".$result_data;
					// $hasil['data']=$data;
					// $hasil['feeder_token']=$_SESSION['feeder_token'];
					// $hasil['feeder_result']=$runWS;
					echo json_encode($hasil);
				}

			}

		} else {
			$hasil['berhasil']=0;
			$hasil['pesan']=$error_desc." ".$result_data;
			
			echo json_encode($hasil);
		}
	}

	//exit();
	$id+=100;
	//$id=0;	 
	exit("<script>
	setTimeout(function() {
		window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/mata_kuliah_kurikulum/validasiMataKuliahKurikulum/".$key."/".$id."';
	}, 2000);
	</script>");

}