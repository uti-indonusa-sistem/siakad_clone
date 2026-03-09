<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }
$ta = $_SESSION['ta'];

if ($aksi=="tampil") {	 

      $perintah = "select * from wsia_dosen where id_ptk='' ";
	  
	  $perintah .= isset($_GET['filter']['nidn'])?" and nidn like '%".$_GET['filter']['nidn']."%'":"";
	  $perintah .= isset($_GET['filter']['nm_ptk'])?" and nm_ptk like '%".$_GET['filter']['nm_ptk']."%'":"";	  
      $perintah .= isset($_GET['filter']['jk'])?" and jk like '%".$_GET['filter']['jk']."%'":"";
	  
	  $perintah.=" order by nm_ptk asc";
	  
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:' LIMIT 50';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:' OFFSET 0';
	  
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

} else if ($aksi=="getDosen") {

    $feeder_token = json_decode($_SESSION['feeder_token']);

	if ($data->id_ptk_feeder=="") {
		$sync['act']="DetailBiodataDosen";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="nidn = '".$data->nidn."'";
        $sync['order']="nidn";
        $sync['limit']=1;
        $sync['offset']=0;
        
	} else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Sudah ada id_ptk";
    	$hasil['data']=$data;
    	echo json_encode($hasil);
        exit();
    }

    $runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_ptk_feeder=="") {
        if (count($runWS->data)>0) {
            $id_ptk_feeder = $runWS->data[0]->id_dosen;
            $sync_at = date("Y-m-d H:i:s");
            $qryUpdate = "update wsia_dosen set id_ptk='$id_ptk_feeder', sync_at='$sync_at' where nidn='$data->nidn' and id_ptk=''";
            try {
                $db 		= koneksi();
                $eksekusi 	= $db->query($qryUpdate);  
                $db = null;
                $hasil['berhasil']=1;
                $hasil['pesan']="Berhasil Ubah id_ptk Dosen di SIAKAD: ".$id_ptk_feeder;
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
            $hasil['pesan']="Tidak ditemukan ID_PTK di FEEDER. Mungkin belum dapat NIDN. Jika sudah ada NIDN silahkan sampaikan NIDN ke IT";
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

} 