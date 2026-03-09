<?php

if ($aksi=="struktur") {
	$proxy = proxy();
	$token=token();
	
	$a_sms=$proxy->GetDictionary($token,"mata_kuliah");
	$sms=$a_sms['result'];
	echo "Struktur Mata Kuliah<br><pre>";
	print_r($sms);
	echo "</pre>";
	
} else if ($aksi=="sync") {

	/*
	  $kondisi = " and xid_mk in ('8bd6aa05-53e2-4c40-b526-99e99214dd02',
'c75d56b7-d425-414d-8d1a-e6c856b9f730',
'221523d1-0fb3-4942-aa94-30e605caecc1',
'f2092ff1-8c96-4ded-813e-5debfd6ce607',
'059a543c-3f5b-41c7-a4d4-0604b8872eb3',
'5ef853db-c1d7-45d7-af58-473814513047',
'b03837dd-7e34-455d-aded-3f3d17814270',
'94428dfa-19d8-4c8e-9f5b-210956f1d572',
'd5261a0c-f607-4524-af32-fbfee04996a1',
'ce2e2bd3-261a-4914-b9cc-352266f8bec9',
'b2dd6d4b-517e-444c-ad42-d20938e8655c',
'f3ed8dcf-4b78-4c9a-8cc3-184f6eabc2d2',
'7530ec66-2cd6-4469-b46a-30b54456697f',
'679b9842-7b0b-4343-9416-096b357cf124') ";
	

	  $perintah = "select xid_mk, wsia_sms.id_sms, wsia_mata_kuliah.id_jenj_didik, kode_mk, nm_mk, jns_mk, kel_mk, sks_mk, sks_tm, sks_prak, sks_prak_lap, sks_sim, metode_pelaksanaan_kuliah, a_sap, a_silabus, a_bahan_ajar, acara_prak, a_diktat, tgl_mulai_efektif, tgl_akhir_efektif from wsia_mata_kuliah, wsia_sms where wsia_sms.xid_sms = wsia_mata_kuliah.id_sms and id_mk='' $kondisi order by xid_mk asc";

	  */

	  $perintah = "SELECT
			xid_mk,
			wsia_sms.id_sms,
			wsia_mata_kuliah.id_jenj_didik,
			kode_mk,
			nm_mk,
			jns_mk,
			kel_mk,
			wsia_mata_kuliah.sks_mk,
			wsia_mata_kuliah.sks_tm,
			wsia_mata_kuliah.sks_prak,
			wsia_mata_kuliah.sks_prak_lap,
			wsia_mata_kuliah.sks_sim,
			metode_pelaksanaan_kuliah,
			a_sap,
			a_silabus,
			a_bahan_ajar,
			acara_prak,
			a_diktat,
			tgl_mulai_efektif,
			tgl_akhir_efektif
		FROM
			wsia_kelas_kuliah,
			wsia_mata_kuliah,
			wsia_sms
		WHERE
			wsia_sms.xid_sms = wsia_kelas_kuliah.id_sms
		AND wsia_kelas_kuliah.id_mk = wsia_mata_kuliah.xid_mk
		AND wsia_kelas_kuliah.id_smt = '20192'
		AND id_kls = ''
		AND wsia_mata_kuliah.id_mk = ''
		ORDER BY
			xid_kls ASC";
	  	  
	  $perintah .= "  LIMIT 1 ";
	  $perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	  
	  try {
		    $db 	= koneksi_wsia();
		    
		    $qry 	= $db->query($perintah); 
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		    if ($qry->rowCount()==0) {
		  	exit("Data Habis. di Halaman = ".$id);
		    }
		   	    
		   echo json_encode($data);
		   
		    foreach ($data as $itemData) {
		    	$vItemData['xid_mk']=$itemData->xid_mk;
		    	unset($itemData->xid_mk);		    
		    	$itemData->tgl_mulai_efektif="9999-01-01";
				$itemData->tgl_akhir_efektif="9999-01-01";
		    	$vItemData['data']=$itemData;
		    }
		    
		   //echo json_encode($vItemData['data']);
		    		    
		   $insert=proxy()->InsertRecord(token(),"mata_kuliah",json_encode($vItemData['data']));
		   echo json_encode($insert);
		  
		   
		   $xid_mk=$vItemData['xid_mk'];
		   $id_mk=$insert['result']['id_mk'];
		   
		   $qryMataKuliah = "update wsia_mata_kuliah set id_mk='$id_mk' where xid_mk='$xid_mk' and id_mk=''";
		   $eksekusi 	= $db->query($qryMataKuliah);  
		   if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
	    		$hasil['pesan']="Berhasil ubah";
		   } else {
			$hasil['berhasil']=0;
	    		$hasil['pesan']="Mata kuliah tidak bisa dirubah.<br>Mungkin sudah disinkronkan ke Feeder";
		   }
		
		  echo json_encode($hasil);
		  
		  $db	= null;
		  
		  echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		  
		  exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/mata_kuliah/sync/1/".$id."';
			}, 2000);
		  </script>");
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  }
	  
}  else if ($aksi=="ambilId") {
	
	/*
	$perintah = "select * from wsia_mata_kuliah where xid_mk IN (
		'1802171401241518850186976',
		'1802171623231518853286021',
		'1802171620251518853284606',
		'1802171622041518853285313',
		'1802171623231518853286021',
		'1802171622041518853285313',
		'1802171620251518853284606',
		'1802171401241518850186976',
		'1802171622041518853285313',
		'1802171623231518853286021',
		'1802171401241518850186976',
		'1802171620251518853284606',
		'1802171622041518853285313',
		'1802171401241518850186976',
		'1802171623231518853286021',
		'1802171620251518853284606',
		'1802171623231518853286021',
		'1802171620251518853284606',
		'1802171622041518853285313',
		'1802171401241518850186976',
		'1802171401241518850186976',
		'1802171623231518853286021',
		'1802171622041518853285313',
		'1802171620251518853284606',
		'1802171401241518850186976',
		'1802171623231518853286021',
		'1802171620251518853284606',
		'1802171622041518853285313',
		'1802190950411519006577466',
		'1802190954011519006579816',
		'1802190950061519006576743',
		'1802191006511519006584743',
		'1802190950411519006577466',
		'1802190954011519006579816',
		'1802191006511519006584743',
		'1802190950061519006576743',
		'1802190954011519006579816',
		'1802190950411519006577466',
		'1802191006511519006584743',
		'1802190950061519006576743',
		'1802190950061519006576743',
		'1802191006511519006584743',
		'1802190950411519006577466',
		'1802190954011519006579816',
		'1802190950061519006576743',
		'1802190950411519006577466',
		'1802191006511519006584743',
		'1802190954011519006579816',
		'1802191006511519006584743',
		'1802190950061519006576743',
		'1802190950411519006577466',
		'1802190954011519006579816',
		'1802190926211519006564870',
		'1802190931581519006565582',
		'1802190932521519006566296',
		'1802190925081519006564160',
		'1802191006511519006584743',
		'1802190933401519006567009',
		'1802190936091519006569154',
		'1802190950411519006577466',
		'1802190934181519006567723',
		'1802190954011519006579816',
		'1802190935101519006568438',
		'1802190931581519006565582',
		'1802190935101519006568438',
		'1802190950061519006576743',
		'1802190932521519006566296',
		'1802190925081519006564160',
		'1802190934181519006567723',
		'1802190936091519006569154',
		'1802190933401519006567009',
		'1802190926211519006564870',
		'1802171622041518853285313',
		'1802171623231518853286021',
		'1802171401241518850186976',
		'1802171620251518853284606',
		'1802171029581518837366398',
		'1802171056551518839088046',
		'1802171048411518839085940',
		'1802171056021518839087343',
		'1802171057461518839088750',
		'1802171049411518839086641'
	) ";

	*/

	$perintah="SELECT
			wsia_mata_kuliah.xid_mk,
			wsia_mata_kuliah.kode_mk,
			wsia_mata_kuliah.id_sms
		FROM
			wsia_kelas_kuliah,
			wsia_mata_kuliah,
			wsia_sms
		WHERE
			wsia_sms.xid_sms = wsia_kelas_kuliah.id_sms
		AND wsia_kelas_kuliah.id_mk = wsia_mata_kuliah.xid_mk
		AND wsia_kelas_kuliah.id_smt = '20191'
		AND id_kls = ''
		AND wsia_mata_kuliah.id_mk = ''
		ORDER BY
			xid_kls ASC";
	$perintah .= "  LIMIT 10 ";
	$perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';

	try {
		$db 	= koneksi_wsia();
		    
		$qry 	= $db->query($perintah); 
		$data	= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		if ($qry->rowCount()==0) {
		  	exit("Data Tidak Ada");
		}
		   	    
		echo json_encode($data);
		   
		foreach ($data as $itemData) {
		   
		   $xid_mk = $itemData->xid_mk;
		   $kode_mk = $itemData->kode_mk;
		   $id_sms = $itemData->id_sms;
		    		    
		   $a_matakuliah=proxy()->GetRecord(token(),"mata_kuliah.raw","kode_mk ='$kode_mk'");
		   $matakuliah=$a_matakuliah['result'];
		   $id_mk = $matakuliah['id_mk'];

		   $qryMataKuliah = "update wsia_mata_kuliah set id_mk='$id_mk' where xid_mk='$xid_mk' and id_mk=''";
		   $eksekusi 	= $db->query($qryMataKuliah);  
		   if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
	    	$hasil['pesan']=$xid_mk." : Berhasil ubah";
		   } else {
			$hasil['berhasil']=0;
	    	$hasil['pesan']=$xid_mk." : Mata kuliah tidak bisa dirubah.<br>Mungkin sudah disinkronkan ke Feeder";
		   }
			
		  echo json_encode($hasil);
		}

		$db	= null;
		
		$id+=10;
	   	echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		  
	  	exit("<script>
	  		setTimeout(function() {
				window.location='http://sync.wsia.udb.ac.id/sopingi/mata_kuliah/ambilId/1/".$id."';
			}, 2000);
	  	</script>");
		  
	  } catch (PDOException $salah) {
		   exit(json_encode($salah->getMessage() ));
	  }

} else if ($aksi=="updateId") {
	
	$db 	= koneksi_wsia();
	$proxy = proxy();
	$token= token();

	$offset= isset($id)? $id:0;

	$a_nilai=$proxy->GetRecordSet($token,"mata_kuliah.raw","","id_mk",100,$offset);
	$nilai=$a_nilai['result'];
	//echo "Data Mata Kuliah<br><pre>";
	//print_r($nilai);

	if (count($nilai)==0) {
		exit("Data Habis. di Halaman = ".$id);
	}

	foreach ($nilai as $item) {
		$id_mk = $item['id_mk'];
		$id_sms = $item['id_sms'];
		$kode_mk = trim($item['kode_mk']);
		$nm_mk = trim($item['nm_mk']);

		$perintah_sms = "select * from wsia_sms where id_sms='$id_sms'";
		$qry_sms 	= $db->query($perintah_sms); 
		$data_sms	= $qry_sms->fetch(PDO::FETCH_OBJ);
		$xid_sms = $data_sms->xid_sms;


		$qryMataKuliah = "update wsia_mata_kuliah set id_mk='$id_mk' where id_sms='$xid_sms' and trim(kode_mk)='$kode_mk' and trim(nm_mk)='$nm_mk' ";

		   $eksekusi 	= $db->query($qryMataKuliah);  
		   if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
	    	$hasil['pesan']=$id_mk." : Berhasil ubah";
		   } else {
			$hasil['berhasil']=0;
			$hasil['id_mk']=$id_mk;
	    	$hasil['pesan']="Mata kuliah tidak bisa dirubah";

	    	$file = 'log/'.$key.'_mata_kuliah_'.$id.'.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);

		   }
			
	}

	$id+=100;
	echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
  	exit("<script>
  		setTimeout(function() {
			window.location='http://localhost/udb_feeder/sopingi/mata_kuliah/updateId/1/".$id."';
		}, 2000);
  	</script>");

} else if ($aksi=="tambah") {
	$proxy = proxy();
	$token= token();

	$baru = Array(
		"id_sms"=>"56dacace-c0a7-437d-893d-5e83262deac3",
		"id_jenj_didik"=> 30,
		"kode_mk"=>"TES",
		"nm_mk"=>"TES",
		"jns_mk"=>"B",
		"kel_mk"=>"A",
		"sks_mk"=>2.86,
		"sks_tm"=>1.12,
		"sks_prak"=>0.2,
		"sks_prak_lap"=>0.5,
		"sks_sim"=>1.00,
		"metode_pelaksanaan_kuliah"=>1,
		"a_sap"=>1,
		"a_silabus"=>1,
		"a_bahan_ajar"=>1,
		"acara_prak"=>1,
		"a_diktat"=>1,
		"tgl_mulai_efektif"=>"2019-11-14",
		"tgl_akhir_efektif"=>"2019-11-15"
	);

	$insert=proxy()->InsertRecord(token(),"mata_kuliah",json_encode($baru));
	echo json_encode($insert);

 }  elseif ($aksi=="tampil") {

 	$proxy = proxy();
	$token= token();

 	$a_nilai=$proxy->GetRecordSet($token,"mata_kuliah.raw","id_mk='7187dbbe-50e3-4633-9e02-4614df183c84'","id_mk",100,0);
	$nilai=$a_nilai['result'];
	print_r($nilai);
 }

//7187dbbe-50e3-4633-9e02-4614df183c84