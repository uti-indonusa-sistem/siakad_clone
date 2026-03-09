<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	
	  if (isset($_GET['filter'])) {
	  	$data=$_GET['filter'];
	  	$value=$data['value'];
	  } else {
	  	$value="-";
	  }

	  if (isset($_GET['id'])) {
	  	$id=$_GET['id'];
	  } else {
	  	$id="";
	  }
	  
	  $kec = "select * from siakad_wilayah where value like '%$value%' and id like '%$id%' order by id asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->query($kec);		    
		    $dataKec		= $qry->fetchAll(PDO::FETCH_OBJ);
		   	    
		    $wilayah=json_encode($dataKec);
		    echo $wilayah;
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="generate") {
	
	  $wilayah = array();
	  $negara = "select id_wil, trim(nm_wil) as value, id_negara from wilayah where id_level_wil=0";
	  try {
		    $db 	= koneksi();
		    $qryNegara 	= $db->query($negara);		    
		    $dataNegara= $qryNegara->fetchAll(PDO::FETCH_OBJ);

		   	foreach ($dataNegara as $itemNegara) {
		   		
		   		$wilayah[] = array(
		   			"id"=>$itemNegara->id_wil,
		   			"value"=>$itemNegara->value,
		   			"id_negara"=>$itemNegara->id_negara
		   		);

		   		$id_wil_negara = $itemNegara->id_wil;
		   		$provinsi = "select id_wil, trim(nm_wil) as value, id_negara from wilayah where id_level_wil=1 and id_induk_wilayah='$id_wil_negara'";
		   		$qryPropinsi 	= $db->query($provinsi);		    
		    	$dataPropinsi= $qryPropinsi->fetchAll(PDO::FETCH_OBJ);

		    	foreach ($dataPropinsi as $itemPropinsi) {

		    		$wilayah[] = array(
			   			"id"=>$itemPropinsi->id_wil,
			   			"value"=>$itemPropinsi->value." - ".$itemNegara->value,
			   			"id_negara"=>$itemPropinsi->id_negara
			   		);

		    		$id_wil_propinsi = $itemPropinsi->id_wil;
			   		$kotakab = "select id_wil, trim(nm_wil) as value, id_negara from wilayah where id_level_wil=2 and id_induk_wilayah='$id_wil_propinsi'";
			   		$qryKotaKab 	= $db->query($kotakab);		    
			    	$dataKotaKab= $qryKotaKab->fetchAll(PDO::FETCH_OBJ);

			    	foreach ($dataKotaKab as $itemKotaKab) {

			    		$wilayah[] = array(
				   			"id"=>$itemKotaKab->id_wil,
				   			"value"=>$itemKotaKab->value." - ".$itemPropinsi->value." - ".$itemNegara->value,
				   			"id_negara"=>$itemKotaKab->id_negara
				   		);

			    		$id_wil_kotakab = $itemKotaKab->id_wil;
				   		$kec = "select id_wil, trim(nm_wil) as value, id_negara from wilayah where id_level_wil=3 and id_induk_wilayah='$id_wil_kotakab'";
				   		$qryKec 	= $db->query($kec); 
				    	$dataKec= $qryKec->fetchAll(PDO::FETCH_OBJ);

				    	foreach ($dataKec as $itemKec) {

				    		$wilayah[] = array(
					   			"id"=>$itemKec->id_wil,
					   			"value"=>$itemKec->value." - ".$itemKotaKab->value." - ".$itemPropinsi->value." - ".$itemNegara->value,
					   			"id_negara"=>$itemKec->id_negara
					   		);
					   	}
			    	}

		    	}

		   	}
		   
		    //insert ke tabel baru
		    $count=0;
		    foreach ($wilayah as $item) {
		    	$id = $item['id'];
		    	$value = addslashes($item['value']);
		    	$id_negara = $item['id_negara'];
		    	$insert = "insert into siakad_wilayahv2 values('$id','$value','$id_negara')";
				$qryInsert 	= $db->prepare($insert)->execute(); 
				$count++;
		    }

		    echo "selesai = ".$count;
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
} else if ($aksi=="generate2") {
	
	  $wilayah = array();
	  $kosong = "select id_wil, trim(nm_wil) as value, id_negara from wilayah where id_level_wil=3 and id_induk_wilayah=''";
	  try {
		    $db 	= koneksi();
		    $qryKosong 	= $db->query($kosong);		    
		    $dataKosong= $qryKosong->fetchAll(PDO::FETCH_OBJ);

		   	foreach ($dataKosong as $itemKosong) {
		   		
		   		$wilayah[] = array(
		   			"id"=>$itemKosong->id_wil,
		   			"value"=>$itemKosong->value,
		   			"id_negara"=>$itemKosong->id_negara
		   		);

		   	}
		   
		    //insert ke tabel baru
		    $count=0;
		    foreach ($wilayah as $item) {
		    	$id = $item['id'];
		    	$value = addslashes($item['value']);
		    	$id_negara = $item['id_negara'];
		    	$insert = "insert ignore into siakad_wilayahv2 values('$id','$value','$id_negara')";
				$qryInsert 	= $db->prepare($insert)->execute(); 
				$count++;
		    }

		    echo "selesai = ".$count;
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
}