<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select * from viewKurikulum where (id_kurikulum_sp='' OR id_kurikulum_sp IS NULL) ";
	  
	  $perintah .= isset($_GET['filter']['nm_kurikulum_sp'])?" and nm_kurikulum_sp like '%".$_GET['filter']['nm_kurikulum_sp']."%'":"";
	  $perintah .= isset($_GET['filter']['id_sms'])?" and id_sms like '%".$_GET['filter']['id_sms']."%'":"";
	  
	  
	  $perintah.=" order by nm_kurikulum_sp asc";
	  
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
				$itemData->statusSync = (empty($itemData->id_kurikulum_sp) ? 0 : 1);
				array_push($dataA,$itemData);
		   	}
			
		    echo json_encode($dataA);
		    $db		= null;
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="sync") {	
	$feeder_token = json_decode($_SESSION['feeder_token']);

	if ($data->id_kurikulum_sp=="") {
		$sync['act']="InsertKurikulum";
		$sync['token']=$feeder_token->data->token;
		$sync['record']=[
			"nama_kurikulum"=>$data->nm_kurikulum_sp,
			"id_prodi"=>$data->id_sms,
            "id_semester"=>$data->id_smt_berlaku,
			"jumlah_sks_lulus"=>$data->jml_sks_lulus, 
			"jumlah_sks_wajib"=>$data->jml_sks_wajib, 
			"jumlah_sks_pilihan"=>$data->jml_sks_pilihan
		];
	} else {
		$sync['act']="UpdateKurikulum";
		$sync['token']=$feeder_token->data->token;
		$sync['key']=[
			'id_kurikulum'=>$data->id_kurikulum_sp
		];
		$sync['record']=[
			"nama_kurikulum"=>$data->nm_kurikulum_sp,
			"id_prodi"=>$data->id_sms, 
            "id_semester"=>$data->id_smt_berlaku,
			"jumlah_sks_lulus"=>$data->jml_sks_lulus, 
			"jumlah_sks_wajib"=>$data->jml_sks_wajib, 
			"jumlah_sks_pilihan"=>$data->jml_sks_pilihan
		];
	}

	$runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_kurikulum_sp=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_kurikulum = $runWS->data->id_kurikulum;
		$qryUpdate = "update wsia_kurikulum set id_kurikulum_sp='$id_kurikulum' where xid_kurikulum_sp='$data->xid_kurikulum_sp' and (id_kurikulum_sp='' OR id_kurikulum_sp IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah data Kurikulum di Feeder"." ".$result_data;
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

	} else if ($runWS->error_code==0 && $data->id_kurikulum_sp!="") {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_kurikulum set xid_kurikulum_sp='$data->xid_kurikulum_sp' where xid_kurikulum_sp='$data->xid_kurikulum_sp'"; // Dummy update since there is no sync_at column
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah data Kurikulum di Feeder"." ".$result_data;
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

//24-12-2023 hanya untuk dev. narik data id_kurikulum
} else if ($aksi=="dev_get_kurikulum") {	
	$feeder_token = json_decode($_SESSION['feeder_token']);
	$offset = $_GET['offset'];
	$db 	= koneksi();

	$perintah = "select * from viewKurikulum where id_kurikulum_sp='' order by nm_kurikulum_sp asc limit 1 offset ".$offset;
	$qry = $db->prepare($perintah); 
	$qry->execute();
	$data = $qry->fetch(PDO::FETCH_OBJ);

	if ($data) {

		$sync['act']="GetDetailKurikulum";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="LOWER(nama_kurikulum) = LOWER('".$data->nm_kurikulum_sp."') and id_prodi='".$data->id_sms_feeder."'";
        $sync['order']="";
        $sync['limit']=1;
        $sync['offset']=0;


		$runWS = json_decode(runWs($sync,'json'));
		$error_desc = $runWS->error_desc;
		$result_data = str_replace(",",", ",json_encode($runWS->data));

		if ($runWS->error_code==0 && $data->id_mk=="") {
			if (count($runWS->data)>0) {
				$id_kurikulum_sp = $runWS->data[0]->id_kurikulum;
				
					$qryUpdate = "update wsia_kurikulum set id_kurikulum_sp='$id_kurikulum_sp' where xid_kurikulum_sp='$data->xid_kurikulum_sp' and (id_kurikulum_sp='' OR id_kurikulum_sp IS NULL)";
					try {
						$db 		= koneksi();
						$eksekusi 	= $db->query($qryUpdate);  
						$db = null;
						$hasil['berhasil']=1;
						$hasil['pesan']="Berhasil Ubah data Kurikulum di SIAKAD: ".$id_mk;
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
				$hasil['pesan']="Tidak ditemukan id_kurikulum_sp di FEEDER. Hubungi IT";
				$hasil['data']=$data;
				$hasil['feeder_result']=$runWS;
				echo json_encode($hasil);

				$offset++;
				echo "
					<script type='text/javascript'>
						setTimeout(function(){
							//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/kurikulum/dev_get_kurikulum/90a5219e942f08c6f3638ec1444aa1a4eb457957/0.3844776390063769?offset=".$offset."';
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
						//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/kurikulum/dev_get_kurikulum/90a5219e942f08c6f3638ec1444aa1a4eb457957/0.3844776390063769?offset=".$offset."';
					}, 1000);
				</script>
			";

		}

	} else {
		echo "Selesai..";
	}
}