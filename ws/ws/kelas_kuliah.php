<?php

if ($aksi=="sync") {
	
	  $perintah = "select 
	  	xid_kls,wsia_sms.id_sms,
	  	id_smt,wsia_mata_kuliah.id_mk as vid_mk, 
	  	wsia_kelas_kuliah.id_mk as vid_mk2, 
	  	nm_kls,wsia_kelas_kuliah.sks_mk,
	  	wsia_kelas_kuliah.sks_tm,
	  	wsia_kelas_kuliah.sks_prak,
	  	wsia_kelas_kuliah.sks_prak_lap,
	  	wsia_kelas_kuliah.sks_sim,
	  	wsia_kelas_kuliah.bahasan_case,
	  	wsia_kelas_kuliah.tgl_mulai_koas,
	  	wsia_kelas_kuliah.tgl_selesai_koas,
	  	wsia_kelas_kuliah.id_mou,
	  	wsia_kelas_kuliah.a_selenggara_pditt,
	  	wsia_kelas_kuliah.kuota_pditt,
	  	wsia_kelas_kuliah.a_pengguna_pditt
	  	   from 
	  	wsia_kelas_kuliah,
	  	wsia_mata_kuliah, 
	  	wsia_sms 
	  	   where 
	  	wsia_sms.xid_sms = wsia_kelas_kuliah.id_sms and 
	  	wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and 
	  	wsia_kelas_kuliah.id_smt='$key' and 
	  	id_kls='' and 
	  	wsia_mata_kuliah.id_mk <> '' 
	  	   order by xid_kls asc";
	  	  
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
		   
		    $aData=array();
		    foreach ($data as $itemData) {

		    	$itemData->id_mk=$itemData->vid_mk;	
		    	
		    	$vItemData['xid_kls']=$itemData->xid_kls;
		    	$itemData->tgl_mulai_koas="2020-02-01";
				$itemData->tgl_selesai_koas="2020-08-31";
			
		    	unset($itemData->xid_kls);
		    	unset($itemData->vid_mk);
		    	unset($itemData->vid_mk2);
		    	array_push($aData,$itemData);
		    }
		    
		    		    
		   $insert=proxy()->InsertRecordSet(token(),"kelas_kuliah",json_encode($aData));
		   
		   echo "<pre>";
		   echo json_encode($insert);
		   echo "</pre>";
		   
		   $i=0;		  
		   foreach ($insert['result'] as $itemData) {
			 $error = $itemData['error_code'];
			   
			 if ($error=="0") {
			   
				$hasil['berhasil']=1;
			    $hasil['pesan']="Berhasil Input";
			    $hasil['data']=$data[$i];
			    echo "<pre>";
				print_r($hasil);
				echo "</pre>";

				$xid_kls=$vItemData['xid_kls'];
			    $id_kls=$itemData['id_kls'];
			   
			    $qryKelasKuliah = "update wsia_kelas_kuliah set id_kls='$id_kls' where xid_kls='$xid_kls' and id_kls=''";
			    $eksekusi 	= $db->query($qryKelasKuliah);  
			    if ($eksekusi->rowCount()>0) {
					$hasil['berhasil']=1;
		    		$hasil['pesan']="Berhasil ubah";
			    } else {
					$hasil['berhasil']=0;
		    		$hasil['pesan']="Kelas tidak bisa dirubah.<br>Mungkin sudah disinkronkan ke Feeder";
		    		exit();
			    }
			
			  echo json_encode($hasil);
				 
			 } else {
				$pesan= $itemData['error_desc'];
				$hasil['berhasil']=0;
			    $hasil['pesan']=$itemData;
			    $hasil['data']=$data[$i];
			    	
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log/'.$key.'_kelas_kuliah_error_insert_'.$id.'.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			 }
			 
			 $i++;
			 
		   }
		   
		 
		  
		  $db	= null;
		  
		  echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		  //$id+=1;

		  exit();

		  exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/kelas_kuliah/sync/".$key."/".$id."';
			}, 2000);
		  </script>");

	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  }
	  
} else if ($aksi=="ambilId") {

	$a_kelas=proxy()->GetRecordSet(token(),"kelas_kuliah.raw","id_smt ='$key'","id_sms",800,0);
	$kelas=$a_kelas['result'];
	echo "UPDATE ID_KLS DARI KELAS KULIAH FEEDER<br><pre>";
	print_r($kelas);
	echo "</pre>";

	try {
		$db 	= koneksi_wsia();
		$no=0;
		foreach ($kelas as $itemKelas) {
			$no++;
			$id_kls = $itemKelas['id_kls'];
	        $id_sms = $itemKelas['id_sms'];
	        $id_smt = $itemKelas['id_smt'];
	        $nm_kls = trim($itemKelas['nm_kls']);
	        $id_mk  = $itemKelas['id_mk'];

	        $qryKelasKuliah = "update wsia_kelas_kuliah,wsia_mata_kuliah set id_kls='$id_kls' where wsia_kelas_kuliah.id_sms='$id_sms' and id_smt='$id_smt' and nm_kls='$nm_kls' and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mata_kuliah.id_mk='$id_mk' ";
	        echo $qryKelasKuliah."<hr>";
			$eksekusi 	= $db->query($qryKelasKuliah);  
			   if ($eksekusi->rowCount()>0) {
					$hasil['berhasil']=1;
		    		$hasil['pesan']="Berhasil ubah";
		    		echo $no." - ".json_encode($hasil);
		    		$file = 'log/20171_ubah_id_kls_sukses.txt';
					// Open the file to get existing content
					$current = file_get_contents($file);
					// Append a new person to the file
					$current .= $id_kls.",";
					// Write the contents back to the file
					file_put_contents($file, $current);
			   } else {
					$hasil['berhasil']=0;
		    		$hasil['pesan']="Tidak diubah: ".$id_kls;
		    		echo $no." - ".json_encode($hasil);

		    		$file = 'log/'.$key.'_ubah_id_kls_gagal.txt';
					// Open the file to get existing content
					$current = file_get_contents($file);
					// Append a new person to the file
					$current .= $id_kls.",";
					// Write the contents back to the file
					file_put_contents($file, $current);
			   }

		}

		 $db	= null;
	} catch (PDOException $salah) {
		   exit(json_encode($salah->getMessage() ));
	}

} else if ($aksi=="dump") {

	$proxy = proxy();
	$token = token();
	
	$a_nilai=$proxy->GetRecordSet($token,"kelas_kuliah.raw","id_smt ='$key'","id_sms",100,$id);
	$nilai=$a_nilai['result'];
	echo "TAMPIL KELAS KULIAH <br><pre>";
	print_r($nilai);
	echo "</pre>";

	//tampil kelas dengan mata kuliah
	$jkelas = count($nilai);
	$a_kelas=array();
	for ($i=0;$i<$jkelas;$i++) {
		$id_kls	=$nilai[$i]['id_kls'];
		$id_sms	=$nilai[$i]['id_sms'];
		$sks_mk	=$nilai[$i]['sks_mk'];
		$id_mk	=$nilai[$i]['id_mk'];
		$nm_kls	=$nilai[$i]['nm_kls'];
		
		if ($id_sms=="054de581-83be-4adf-85a4-65f6c28c065d") {
			$nm_progdi="TI";
		} else if ($id_sms=="20ad1060-6e2e-4920-a977-036a79b458ed") {
			$nm_progdi="TK";
		} else if ($id_sms=="35eee452-2a2a-4769-8b14-904934d8df4d") {
			$nm_progdi="MI";
		} else  if ($id_sms=="79833b0d-28f2-4b80-af49-ac2fe74ea997") {
			$nm_progdi="SI";
		} 
		
		$a_matkul=$proxy->GetRecord($token,"mata_kuliah.raw","id_mk='$id_mk' ");
		$matkul=$a_matkul['result'];
		
		$nama_mk	=$matkul['nm_mk'];
		
		$a_kelas[$i]['id_kls']=$id_kls;
		$a_kelas[$i]['nm_kls']=$nm_kls;
		$a_kelas[$i]['id_sms']=$id_sms;
		$a_kelas[$i]['progdi']=$nm_progdi;
		$a_kelas[$i]['matkul']=$nama_mk;
		$a_kelas[$i]['sks']	=$sks_mk;
	}

	//buat file
	$array =$a_kelas;
	$f = fopen('kelas_kuliah_'.$key.'-'.$id.'.csv', 'w');
	$firstLineKeys = false;
	foreach ($array as $line)
	{
		if (empty($firstLineKeys))
		{
			$firstLineKeys = array_keys($line);
			fputcsv($f, $firstLineKeys);
			$firstLineKeys = array_flip($firstLineKeys);
		}
		fputcsv($f, array_merge($firstLineKeys, $line));
	}

	//exit();
	$id+=100;
		 
		  exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/kelas_kuliah/dump/".$key."/".$id."?sopingiwtwg';
			}, 2000);
		  </script>");

} else if ($aksi=="ubah") {
	
	$kelas=array("8a50443a-bcd0-4c07-a76b-c308b60e801e","49e06cc2-aa39-4ecc-9912-0335b18b3fb6","6cae6eb5-adb1-4e33-9e89-0ea1b72bc73a","0b19e8d8-6f8b-48f3-af54-61f2d10d0b9e","67282b9a-f85b-4ce4-9198-540145f6a9d4","f5216543-2959-47d2-b966-40120f179efa","168525b0-e8f4-4507-ab85-271253e971f2","b84d8de9-e67f-4902-90f4-ceac27c90ce7","2dc7aee0-8b47-4775-b627-471ad7f2dc1f","604e2112-776b-4f1e-912f-e8756d179259","677e3b31-7ac5-4feb-b1c1-85e236003869","145b6591-db7f-4f23-bedd-d952354adef0","ceb7225c-0f02-4a59-b41f-ccf1620eee90","e73a9a25-f2a7-4437-b314-366c9eb72c88","5e632368-684b-4a75-970c-e63d59303ee5","7466257c-dd3f-4da5-839a-d0722ce1ad6d","8a369cfe-2e39-4129-abb2-b8304bf446c7","be0622ca-ef43-4cb2-bdc5-f95a66db23ca","d02fb15d-9e8c-440f-bd0d-7f3c432cc025","4f4360be-9c39-4d3c-a444-5407038a2c92","079cd631-77cb-40f3-a8d5-d5b1ccb7146c","8a9e35ab-4f08-45b9-8752-8979c2f84f78","0815ae8f-0694-42bc-8e2c-f234545c1eca","f1efefe5-95cb-4f37-85bb-bf9921735cd9","3113853f-14d5-4dff-bdbb-873dabc4512f","131374b7-0819-4b20-adb2-4d7500454aa4","d015eaac-8f7e-4a5e-a475-0520746dafc8","15fc8021-769e-434c-bd4b-2c9d79b65d5f","fb8f5316-4a56-4467-897b-41634148e23b","9fdcfa63-7310-4497-9220-517bb6f77c27");
	
	$records_kelas=array();
	foreach ($kelas as $itemKelas) {
    	$key=array('id_kls'=>$itemKelas);
		$data=array('tgl_mulai_koas'=> "2017-02-20", 'tgl_selesai_koas'=> "2017-08-26" );
		$records_kelas[]=array('key'=>$key,'data'=>$data);
    }
    
    $update_kelas=proxy()->UpdateRecordSet(token(),"kelas_kuliah",json_encode($records_kelas));	
	echo "HASIL UPDATE KELAS<hr><pre>";
	print_r($update_kelas);
	echo "</pre>";
	
}