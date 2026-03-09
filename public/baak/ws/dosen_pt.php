<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }
$ta = $_SESSION['ta'];

$id_thn_ajaran=substr($ta,0,4);

if ($aksi=="tampil") {	 

    $perintah = "select * from viewDosenPt where id_reg_ptk='' and id_ptk<>'' and id_sms<>'' and id_thn_ajaran='$id_thn_ajaran' ";
	  
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

} else if ($aksi=="getPenugasanDosen") {

    $feeder_token = json_decode($_SESSION['feeder_token']);

	if ($data->id_reg_ptk=="") {
		$sync['act']="GetListPenugasanDosen";
		$sync['token']=$feeder_token->data->token;
		$sync['filter']="nidn = '".$data->nidn."' and id_prodi = '".$data->id_sms."' and id_tahun_ajaran = '".$data->id_thn_ajaran."'";
        $sync['order']="nidn";
        $sync['limit']=1;
        $sync['offset']=0;
        
	} else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Sudah ada id_reg_ptk";
    	$hasil['data']=$data;
    	echo json_encode($hasil);
        exit();
    }

    $runWS = json_decode(runWs($sync,'json'));
	//$error_desc = error_status($runWS->error_code);
	$error_desc = $runWS->error_desc;
	$result_data = str_replace(",",", ",json_encode($runWS->data));

	if ($runWS->error_code==0 && $data->id_reg_ptk=="") {
        if (count($runWS->data)>0) {
            $id_reg_ptk = $runWS->data[0]->id_registrasi_dosen;
            $sync_at = date("Y-m-d H:i:s");
            $qryUpdate = "update wsia_dosen_pt set id_reg_ptk='$id_reg_ptk', sync_at='$sync_at' where id_ptk='$data->xid_ptk' and id_sms='".$data->xid_sms."' and id_reg_ptk='' and id_thn_ajaran = '".$data->id_thn_ajaran."'";
            try {
                $db 		= koneksi();
                $eksekusi 	= $db->query($qryUpdate);  
                $db = null;
                $hasil['berhasil']=1;
                $hasil['pesan']="Berhasil Ubah id_reg_ptk Penugasan Dosen di SIAKAD: ".$id_reg_ptk;
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
            $hasil['pesan']="Tidak ditemukan id_reg_ptk di FEEDER TA: ".$data->id_thn_ajaran." dengan NIDN:".$data->nidn;
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