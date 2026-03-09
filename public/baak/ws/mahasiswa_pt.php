<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select * from viewMahasiswaPt where (id_reg_pd='' OR id_reg_pd IS NULL) and id_pd<>'' ";
	  
	  $perintah .= isset($_GET['filter']['nm_pd'])?" and nm_pd like '%".$_GET['filter']['nm_pd']."%'":"";
	  $perintah .= isset($_GET['filter']['nipd'])?" and nipd like '%".$_GET['filter']['nipd']."%'":"";
	  $perintah .= isset($_GET['filter']['kelas'])?" and kelas like '%".$_GET['filter']['kelas']."%'":"";
	  $perintah .= isset($_GET['filter']['xid_sms'])?" and  xid_sms like '%".$_GET['filter']['xid_sms']."%'":"";
	  
	  if ( isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb']!="" ){
	  	$nm_lemb=explode(" - ",$_GET['filter']['vnm_lemb']);
	  	$nm_jenj_didik=$nm_lemb[0];
	  	$nm_lemb=$nm_lemb[1];
	  	$perintah .= " and nm_jenj_didik like '%".$nm_jenj_didik."%'";
	  	$perintah .= " and nm_lemb like '%".$nm_lemb."%'";
	  }
	  
	  
	  $perintah.=" order by nipd asc";
	  
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
				$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
				$itemData->statusSync = (empty($itemData->id_reg_pd) ? 0 : 1);
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

	$sync['act']="InsertRiwayatPendidikanMahasiswa";
	$sync['token']=$feeder_token->data->token;
	$sync['record']=[
		"id_mahasiswa"=>$data->id_pd, 
		"nim"=>$data->nipd, 
		"id_jenis_daftar"=>$data->id_jns_daftar, 
		"id_jalur_daftar"=>12, //Seleksi Mandiri PTS
		"id_periode_masuk"=>$data->mulai_smt, 
		"tanggal_daftar"=>$data->tgl_masuk_sp,  //07-01-2023 update juga di frontend
		"id_perguruan_tinggi"=>$data->id_sp,
		"id_prodi"=>$data->id_sms, 
		"id_pembiayaan"=>$data->id_pembiayaan, //07-01-2023 update juga di frontend
		"biaya_masuk"=>$data->biaya_masuk, //07-01-2023 update juga di frontend
	];
	
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

	if ($runWS->error_code==0 && $data->id_reg_pd=="") {
		$sync_at = date("Y-m-d H:i:s");
		$id_reg_pd = $runWS->data->id_registrasi_mahasiswa;
		$xid_reg_pd = $data->xid_reg_pd;
		$qryUpdate = "update wsia_mahasiswa_pt set id_reg_pd='$id_reg_pd' where xid_reg_pd='$xid_reg_pd' and (id_reg_pd='' OR id_reg_pd IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Tambah riwayat pendidikan Mahasiswa di Feeder"." ".$result_data;
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

//24-12-2023 hanya untuk dev. narik data id_reg_pd, id_pd, status, lulus
} else if ($aksi=="dev_get_mahasiswa") {	
	$feeder_token = json_decode($_SESSION['feeder_token']);
	$offset = $_GET['offset'];
	$db 	= koneksi();

	$perintah = "select * from viewMahasiswaPt where id_sms='44d491de-d521-48e3-a86b-586e6f00c639' order by nipd asc limit 1 offset ".$offset;
	$qry = $db->prepare($perintah); 
	$qry->execute();
	$data = $qry->fetch(PDO::FETCH_OBJ);

	if ($data) {

		$sync['act']="GetListMahasiswa";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="nim='".$data->nipd."' and id_prodi='".$data->id_sms."'";
		$sync['limit']="1";


		$runWS = json_decode(runWs($sync,'json'));
		//$error_desc = error_status($runWS->error_code);
		$error_desc = $runWS->error_desc;
		$result_data = str_replace(",",", ",json_encode($runWS->data));

		if ($runWS->error_code==0 && $runWS->jumlah==1) {
			$tgl_sekarang = date("Y-m-d H:i:s");
			$data_feeder = $runWS->data[0];
			$id_reg_pd = $data_feeder->id_registrasi_mahasiswa;
			
			if ($data_feeder->tanggal_keluar) {
				$id_jns_keluar = $data_feeder->id_status_mahasiswa;
				$atgl_keluar = explode("-",$data_feeder->tanggal_keluar);
				$tgl_keluar = $atgl_keluar[2]."-".$atgl_keluar[1]."-".$atgl_keluar[0];
				$qryUpdateMahasiswaPt = "update wsia_mahasiswa_pt set id_reg_pd='$id_reg_pd', id_jns_keluar='$id_jns_keluar', tgl_keluar='$tgl_keluar', sync_keluar_at='$tgl_sekarang' where xid_reg_pd='$data->xid_reg_pd'";
			} else {
				$qryUpdateMahasiswaPt = "update wsia_mahasiswa_pt set id_reg_pd='$id_reg_pd', id_jns_keluar='', tgl_keluar='0000-00-00', sync_keluar_at='0000-00-00 00:00:00' where xid_reg_pd='$data->xid_reg_pd'";
			}
			
			$qry = $db->prepare($qryUpdateMahasiswaPt); 
			$qry->execute();

			/*
			$id_pd = $data_feeder->id_mahasiswa;
			$qryUpdateMahasiswa = "update wsia_mahasiswa set id_pd='$id_pd',  sync_at='$tgl_sekarang' where xid_pd='$data->xid_pd' and id_pd=''";
			$qry = $db->prepare($qryUpdateMahasiswa); 
			$qry->execute();
			*/

			$hasil['berhasil']=1;
			$hasil['pesan']="Berhasil";
			$hasil['data']=$data;
			$hasil['feeder_result']=$runWS;
			$hasil['qry_mahasiswa_pt']=$qryUpdateMahasiswaPt;
			$hasil['qry_mahasiswa']=$qryUpdateMahasiswa;
			echo "<pre>";
			echo json_encode($hasil);
			echo "</pre>";

			/*
			echo "
				<script type='text/javascript'>
					setTimeout(function(){
						window.location.reload(1);
					}, 1000);
				</script>
			";
			*/

			$offset++;
			echo "
				<script type='text/javascript'>
					setTimeout(function(){
						//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/mahasiswa_pt/dev_get_mahasiswa/57d798712e0005ec5556bb3f2d5a70fa4522d9a3/0.3844776390063769?offset=".$offset."';
					}, 2000);
				</script>
			";

		} else if ($runWS->error_code==100) {

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
			echo "<pre>";
			echo json_encode($hasil);
			echo "</pre>";
			$offset++;
			echo "
				<script type='text/javascript'>
					setTimeout(function(){
						//window.location='https://siakadv2.poltekindonusa.ac.id/baak/sopingi-feeder/mahasiswa_pt/dev_get_mahasiswa/57d798712e0005ec5556bb3f2d5a70fa4522d9a3/0.3844776390063769?offset=".$offset."';
					}, 2000);
				</script>
			";
		}

	} else {
		echo "Selesai...";
	}

	$db = null;


}