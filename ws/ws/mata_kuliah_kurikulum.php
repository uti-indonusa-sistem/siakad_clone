<?php

if ($aksi=="sync") {  
	  	  
	  try {
			$db 	= koneksi_wsia();
			$perintahKurikulum="select * from wsia_kurikulum_view where id_kurikulum_sp<>'-' and id_kurikulum_sp<>'' and id_kurikulum_sp is not null and id_sms<>'' and id_smt_berlaku >= '20191'  LIMIT 1 OFFSET $id";
			

			$qryKurikulum 	= $db->query($perintahKurikulum); 
		    $dataKurikulum	= $qryKurikulum->fetch(PDO::FETCH_OBJ);
			$xid_kurikulum_sp = $dataKurikulum->xid_kurikulum_sp;

			$perintah = "select wsia_kurikulum.id_kurikulum_sp,wsia_mata_kuliah.id_mk,wsia_mata_kuliah_kurikulum.smt,wsia_mata_kuliah_kurikulum.sks_mk,wsia_mata_kuliah_kurikulum.sks_tm,wsia_mata_kuliah_kurikulum.sks_prak,wsia_mata_kuliah_kurikulum.sks_prak_lap,wsia_mata_kuliah_kurikulum.sks_sim,wsia_mata_kuliah_kurikulum.a_wajib from wsia_mata_kuliah_kurikulum,wsia_kurikulum,wsia_mata_kuliah where xid_kurikulum_sp='$xid_kurikulum_sp' and wsia_mata_kuliah_kurikulum.id_kurikulum_sp=xid_kurikulum_sp and wsia_mata_kuliah_kurikulum.id_mk=xid_mk and wsia_mata_kuliah.id_mk<>''";
		    
		    $qry 	= $db->query($perintah); 
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		    if ($qry->rowCount()==0) {
		  		exit("Data Habis. di Halaman = ".$id);
		    }
		   	 
		   
		   echo "<pre>";
		   print_r($data);
		   echo "</pre>";
		   
		   
		   $insert=proxy()->InsertRecordSet(token(),"mata_kuliah_kurikulum",json_encode($data));
		   //echo json_encode($insert);
		  
		   foreach ($insert['result'] as $itemData) {
			 $error = $itemData['error_code'];
			   
			 if ($error=="0") {
			   
				$hasil['berhasil']=1;
			    $hasil['pesan']="Berhasil tambah";
			    echo "<pre>";
				echo json_encode($hasil);
				echo "</pre>";
				 
			 } else {
				$pesan= $itemData['error_desc'];
				$hasil['berhasil']=0;
			    $hasil['pesan']=$pesan;
			    
			    $file = 'log/'.$key.'_mata_kuliah_kurikulum_'.$id.'.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);	

			    echo "<pre>";
				echo json_encode($hasil);
				echo "</pre>";
			}
		  }

		  exit();
		  echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		  $id++;
		  exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/mata_kuliah_kurikulum/sync/1/".$id."';
			}, 2000);
		  </script>");

	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  }
	  
} 