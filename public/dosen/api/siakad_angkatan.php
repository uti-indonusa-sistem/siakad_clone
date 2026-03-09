<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select * from siakad_angkatan";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="pilihV1") {

	  /*
	  $perintah = "select * from siakad_angkatan order by angkatan desc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    $i=0;
		    foreach ($data as $itemData) {
				$pilih[$i]['id']=$itemData->angkatan;
				$pilih[$i]['value']="Angkatan: ".$itemData->angkatan;
				$i++;
			}
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  */

	  	$pilih=array();
	    $tahun=date("Y")+1;
	    $tahun2=$tahun-10;
	    $i=0;
	    for ($th=$tahun;$th>=$tahun2;$th--) {
			$pilih[$i]['id']=$th;
			$pilih[$i]['value']="Angkatan: ".$th;
			$i++;
		}
	    
	    echo json_encode($pilih);

} else if ($aksi=="pilih") {
	  
	    $pilih=array();
	    $tahun=date("Y")+1;
	    $tahun2=$tahun-10;
	    $i=0;
	    for ($th=$tahun;$th>=$tahun2;$th--) {
			$pilih[$i]['id']=$th;
			$pilih[$i]['value']="Angkatan: ".$th;
			$i++;
		}
	    
	    echo json_encode($pilih);
		    
} 

?>