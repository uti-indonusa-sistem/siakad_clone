<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select * from viewMataKuliah where (id_mk='' OR id_mk IS NULL) and id_sms_feeder<>'' ";
	  
	  $perintah .= isset($_GET['filter']['nm_mk'])?" and nm_mk like '%".$_GET['filter']['nm_mk']."%'":"";
	  $perintah .= isset($_GET['filter']['kode_mk'])?" and kode_mk like '%".$_GET['filter']['kode_mk']."%'":"";
	  $perintah .= isset($_GET['filter']['id_sms'])?" and id_sms like '%".$_GET['filter']['id_sms']."%'":"";
	  
	  $perintah.=" order by kode_mk asc";
	  
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
				$itemData->statusSync = (empty($itemData->id_mk) ? 0 : 1);
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

	if ($data->id_mk=="") {
		$sync['act']="InsertMataKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"kode_mata_kuliah"=>$data->kode_mk, 
			"nama_mata_kuliah"=>$data->nm_mk, 
			"id_prodi"=>$data->id_sms_feeder, 
			"id_jenis_mata_kuliah"=>$data->jns_mk, 
			"id_kelompok_mata_kuliah"=>$data->kel_mk, 
			"sks_mata_kuliah"=>$data->sks_mk, 
			"sks_tatap_muka"=>$data->sks_tm,
			"sks_praktek"=>$data->sks_prak, 
			"sks_praktek_lapangan"=>$data->sks_prak_lap,
			"sks_simulasi"=>$data->sks_sim,
			"metode_kuliah"=>$data->metode_pelaksanaan_kuliah, 
			"ada_sap"=>$data->a_sap, 
			"ada_silabus"=>$data->a_silabus, 
			"ada_bahan_ajar"=>$data->a_bahan_ajar, 
			"ada_acara_praktek"=>$data->acara_prak, 
			"ada_diktat"=>$data->a_diktat, 
			"tanggal_mulai_efektif"=>"9999-01-01", 
			"tanggal_akhir_efektif"=>"9999-01-01"
		];
	} else {
		$sync['act']="UpdateMataKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			'id_matkul'=>$data->id_mk
		];
		$sync['record']=[
			"kode_mata_kuliah"=>$data->kode_mk, 
			"nama_mata_kuliah"=>$data->nm_mk, 
			"id_prodi"=>$data->id_sms_feeder, 
			"id_jenis_mata_kuliah"=>$data->jns_mk, 
			"id_kelompok_mata_kuliah"=>$data->kel_mk, 
			"sks_mata_kuliah"=>$data->sks_mk, 
			"sks_tatap_muka"=>$data->sks_tm,
			"sks_praktek"=>$data->sks_prak, 
			"sks_praktek_lapangan"=>$data->sks_prak_lap,
			"sks_simulasi"=>$data->sks_sim,
			"metode_kuliah"=>$data->metode_pelaksanaan_kuliah, 
			"ada_sap"=>$data->a_sap, 
			"ada_silabus"=>$data->a_silabus, 
			"ada_bahan_ajar"=>$data->a_bahan_ajar, 
			"ada_acara_praktek"=>$data->acara_prak, 
			"ada_diktat"=>$data->a_diktat, 
			"tanggal_mulai_efektif"=>"9999-01-01", 
			"tanggal_akhir_efektif"=>"9999-01-01"
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

	if ($runWS->error_code==0 && $data->id_mk=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_mk = $runWS->data->id_matkul;
		$qryUpdate = "update wsia_mata_kuliah set id_mk='$id_mk' where xid_mk='$data->xid_mk'  and (id_mk='' OR id_mk IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah data Mata Kuliah di Feeder"." ".$result_data;
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

	} else if ($runWS->error_code==0 && $data->id_mk!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_mata_kuliah set xid_mk='$data->xid_mk' where xid_mk='$data->xid_mk'"; // Dummy update since there is no sync_at column
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah data Mata Kuliah di Feeder"." ".$result_data;
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
	

} else if ($aksi=="getMataKuliah") {

    if (!isset($_SESSION['feeder_token']) || empty($_SESSION['feeder_token'])) {
		token();
	}
    $feeder_token = json_decode($_SESSION['feeder_token'] ?? '');

	if ($data->id_mk=="") {
		$sync['act']="GetDetailMataKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="LOWER(kode_mata_kuliah) = LOWER('".$data->kode_mk."') and LOWER(nama_mata_kuliah) = LOWER('".$data->nm_mk."') and id_prodi='".$data->id_sms."'";
        $sync['order']="kode_mata_kuliah";
        $sync['limit']=1;
        $sync['offset']=0;
        
	} else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Sudah ada id_mk";
    	$hasil['data']=$data;
    	echo json_encode($hasil);
        exit();
    }

    $runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_mk=="") {
        if (count($runWS->data)>0) {
            $id_mk = $runWS->data[0]->id_matkul;
            $sks_feeder = floatval($runWS->data[0]->sks_mata_kuliah);
            $sks_wsia = floatval($data->sks_mk);
            if ($sks_feeder==$sks_wsia) {

                $sync_at = date("Y-m-d H:i:s");
                $id_mk = $runWS->data[0]->id_matkul;
                $qryUpdate = "update wsia_mata_kuliah set id_mk='$id_mk' where xid_mk='$data->xid_mk' and (id_mk='' OR id_mk IS NULL)";
                try {
                    $db 		= koneksi();
                    $eksekusi 	= $db->query($qryUpdate);  
                    $db = null;
                    $hasil['berhasil']=1;
                    $hasil['pesan']="Berhasil Ubah data Mata Kuliah di SIAKAD: ".$id_mk;
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
                $hasil['pesan']="SKS MK Feeder tidak sama dengan SKS MK SIAKAD. Disarankan membuat kode MK baru";
                $hasil['data']=$data;
                $hasil['feeder_result']=$runWS;
                echo json_encode($hasil);
                exit();
            }

        } else {
            $hasil['berhasil']=0;
            $hasil['pesan']="Tidak ditemukan id_mk di FEEDER. Hubungi IT";
            $hasil['data']=$data;
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

//24-12-2023 hanya untuk dev. narik data kode_mk
} else if ($aksi=="dev_get_mata_kuliah") {	
	$feeder_token = json_decode($_SESSION['feeder_token']);
	$offset = $_GET['offset'];
	$db 	= koneksi();

	$perintah = "select * from viewMataKuliah where id_mk='' order by kode_mk asc limit 1 offset ".$offset;
	$qry = $db->prepare($perintah); 
	$qry->execute();
	$data = $qry->fetch(PDO::FETCH_OBJ);

	if ($data) {

		$sync['act']="GetDetailMataKuliah";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="LOWER(kode_mata_kuliah) = LOWER('".$data->kode_mk."') and LOWER(nama_mata_kuliah) = LOWER('".$data->nm_mk."') and id_prodi='".$data->id_sms_feeder."'";
        $sync['order']="kode_mata_kuliah";
        $sync['limit']=1;
        $sync['offset']=0;


		$runWS = json_decode(runWs($sync,'json'));
		$error_desc = $runWS->error_desc;
		$result_data = str_replace(",",", ",json_encode($runWS->data));

		if ($runWS->error_code==0 && $data->id_mk=="") {
			if (count($runWS->data)>0) {
				$id_mk = $runWS->data[0]->id_matkul;
				$sks_feeder = floatval($runWS->data[0]->sks_mata_kuliah);
				$sks_wsia = floatval($data->sks_mk);
				if ($sks_feeder==$sks_wsia) {
	
					$id_mk = $runWS->data[0]->id_matkul;
					$qryUpdate = "update wsia_mata_kuliah set id_mk='$id_mk' where xid_mk='$data->xid_mk' and (id_mk='' OR id_mk IS NULL)";
					try {
						$db 		= koneksi();
						$eksekusi 	= $db->query($qryUpdate);  
						$db = null;
						$hasil['berhasil']=1;
						$hasil['pesan']="Berhasil Ubah data Mata Kuliah di SIAKAD: ".$id_mk;
						$hasil['data']=$data;
						$hasil['feeder_token']=$_SESSION['feeder_token'];
						$hasil['feeder_result']=$runWS;
						echo json_encode($hasil);
						echo "
							<script type='text/javascript'>
								setTimeout(function(){
									//window.location.reload(1);
								}, 1000);
							</script>
						";
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
					$hasil['pesan']="SKS MK Feeder tidak sama dengan SKS MK SIAKAD. Disarankan membuat kode MK baru";
					$hasil['data']=$data;
					$hasil['feeder_result']=$runWS;
					echo json_encode($hasil);

					$offset++;
					echo "
						<script type='text/javascript'>
							setTimeout(function(){
								//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/mata_kuliah/dev_get_mata_kuliah/90a5219e942f08c6f3638ec1444aa1a4eb457957/0.3844776390063769?offset=".$offset."';
							}, 1000);
						</script>
					";

					
				}
	
			} else {
				$hasil['berhasil']=0;
				$hasil['pesan']="Tidak ditemukan id_mk di FEEDER. Hubungi IT";
				$hasil['data']=$data;
				$hasil['feeder_result']=$runWS;
				echo json_encode($hasil);

				$offset++;
				echo "
					<script type='text/javascript'>
						setTimeout(function(){
							//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/mata_kuliah/dev_get_mata_kuliah/90a5219e942f08c6f3638ec1444aa1a4eb457957/0.3844776390063769?offset=".$offset."';
						}, 1000);
					</script>
				";

				
			}
	
		} else if ($runWS->error_code==100 || $runWS->error_code==106) {

			$data = token();
			echo "<pre>";
			echo $data;
			echo "</pre>";

			echo "
				<script type='text/javascript'>
					setTimeout(function(){
						//window.location.reload(1);
					}, 1000);
				</script>
			";
			
		} else {
			$hasil['berhasil']=0;
			$hasil['pesan']=$error_desc." ".$result_data;
			$hasil['data']=$data;
			$hasil['feeder_token']=$_SESSION['feeder_token'];
			$hasil['feeder_result']=$runWS;
			echo json_encode($hasil);

			$offset++;
			echo "
				<script type='text/javascript'>
					setTimeout(function(){
						//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/mata_kuliah/dev_get_mata_kuliah/90a5219e942f08c6f3638ec1444aa1a4eb457957/0.3844776390063769?offset=".$offset."';
					}, 1000);
				</script>
			";

		}

	} else {
		echo "Selesai..";
	}
}