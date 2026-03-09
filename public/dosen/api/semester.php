<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

if ($aksi=="pilih") {
	  $mulai_smt=$_SESSION['mulai_smt'] ?? '';
	  $perintah = "select * from wsia_semester where id_smt>='$mulai_smt' and id_smt<=(select id_smt from wsia_semester where krs_aktif='1' ) order by id_smt desc limit 20";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $hasil['berhasil']=1;
		    $jdata= count($data);
		    for ($i=0;$i<$jdata;$i++) {
		    		$hasil['pesan'][$i]['id']=$data[$i]->id_smt;
		  		$hasil['pesan'][$i]['value']=$data[$i]->nm_smt;
		    		if ($data[$i]->krs_aktif=="1") {
					//$hasil['pesan'][$i]['default ']=true;
				}
		    }
		    echo json_encode($hasil['pesan']);
		    
	  } catch (PDOException $salah) {
	    	   $hasil['berhasil']=0;
		   $hasil['pesan']=$salah->getMessage() ;
		   echo json_encode($hasil);
	  }
	  
} else if ($aksi=="pilihSemua") {
	
	  $perintah = "select * from wsia_semester order by id_smt desc  limit 0,20";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $i=0;
		    $pilih=array();
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->id_smt;
		    	$pilih[$i]['value']=$itemData->nm_smt;
		    	$i++;	
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage());
	  }
	  
}
