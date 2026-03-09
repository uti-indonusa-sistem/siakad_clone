<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	

	$ta=$_SESSION['ta'];
	$perintah="select * from viewMahasiswaKeluar where updated_keluar_at > sync_keluar_at";

    $perintah .= isset($_GET['filter']['nipd'])?" and nipd like '%".$_GET['filter']['nipd']."%'":"";
    $perintah .= isset($_GET['filter']['nm_pd'])?" and nm_pd like '%".$_GET['filter']['nm_pd']."%'":"";
	$perintah .= isset($_GET['filter']['xid_sms'])?" and xid_sms like '%".$_GET['filter']['xid_sms']."%'":"";
	
	$perintah .= isset($_GET['filter']['ket_keluar'])?" and ket_keluar like '%".$_GET['filter']['id_stat_mhs']."%'":"";
	
	$perintah.="  order by nipd ";

	$perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:'  LIMIT 50';
	$pos = isset($_GET['start'])?$_GET['start']:0;
	$perintah .= ' OFFSET '.$pos;

	try {
		$db 	= koneksi();
		$qry 	= $db->prepare($perintah); 
		$qry->execute();
		$data		= $qry->fetchAll(PDO::FETCH_OBJ);
		$db		= null;

		foreach ($data as $itemData) {
			if ($itemData->sync_keluar_at == "0000-00-00 00:00:00") {
				$itemData->statusSync=0;
			} else {
				$itemData->statusSync=2;
			}
		}

		echo json_encode($data);
		
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage() );
	}

} else if ($aksi=="sync") {	
	$feeder_token = json_decode($_SESSION['feeder_token']);

	$sync=[
		"act"=>"UpdateMahasiswaLulusDO",
		"token" => $feeder_token->data->token,
        "key"=>[
			"id_registrasi_mahasiswa"=>$data->id_reg_pd,
        ],
        "record"=>[
          	'id_jenis_keluar'=>$data->id_jns_keluar,
		    'tanggal_keluar'=>$data->tgl_keluar,
			'id_periode_keluar' =>  $data->smt_yudisium, //7-1-2023 update frontend
		    'keterangan'=>$data->ket,
			'nomor_sk_yudisium'=>$data->sk_yudisium,
			'tanggal_sk_yudisium'=>$data->tgl_sk_yudisium,
			'ipk'=>$data->ipk,
			'nomor_ijazah'=>$data->no_seri_ijazah,
		    'jalur_skripsi'=>$data->jalur_skripsi,
		    'judul_skripsi'=>$data->judul_skripsi,
		    'bulan_awal_bimbingan'=>$data->bln_awal_bimbingan,
		    'bulan_akhir_bimbingan'=>$data->bln_akhir_bimbingan,
			'ipk_transkip'=>$data->ipk,

        ]
    ];
	
	$runWS = json_decode(runWs($sync,'json'));
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0) {
		$sync_at = date("Y-m-d H:i:s");
		$qryUpdate = "update wsia_mahasiswa_pt set sync_keluar_at='$sync_at' where xid_reg_pd='$data->xid_reg_pd'";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryUpdate);  
		    $db = null;
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Syncron Mahasiswa Lulus/Keluar di Feeder"." ".$result_data;
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
	
} 