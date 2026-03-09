<?php

if ($aksi=="sync") {
	  $perintah = "select * from wsia_kurikulum_view where (id_kurikulum_sp='' or ISNULL(id_kurikulum_sp)) and id_sms<>'' order by xid_kurikulum_sp";
	  	  
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
		    	$vItemData['xid_kurikulum_sp']=$itemData->xid_kurikulum_sp;
		    	$itemData->id_smt=$itemData->id_smt_berlaku;    
		    	unset($itemData->xid_kurikulum_sp);	
		    	unset($itemData->id_smt_berlaku);	
		    	$vItemData['data']=$itemData;
		    }
		    
		   //echo json_encode($vItemData['data']);
		    		    
		   $insert=proxy()->InsertRecord(token(),"kurikulum",json_encode($vItemData['data']));
		   echo json_encode($insert);
		  
		   $xid_kurikulum_sp=$vItemData['xid_kurikulum_sp'];
		   
		   $error = $insert['result']['error_code'];
		   
		   if ($error=="0") {
		   
			   $id_kurikulum_sp=$insert['result']['id_kurikulum_sp'];
			   
			   $qryKurikulum = "update wsia_kurikulum set id_kurikulum_sp='$id_kurikulum_sp' where xid_kurikulum_sp='$xid_kurikulum_sp' and (id_kurikulum_sp='' or ISNULL(id_kurikulum_sp)) ";
			   $eksekusi 	= $db->query($qryKurikulum);  
			   if ($eksekusi->rowCount()>0) {
				$hasil['berhasil']=1;
		    		$hasil['pesan']="Berhasil Tambah";
			   } else {
				$hasil['berhasil']=0;
		    		$hasil['pesan']="Kurikulum tidak bisa dirubah.<br>Mungkin sudah disinkronkan ke Feeder";
			   }
			
			  echo json_encode($hasil);
			  
			  $db	= null;
			  
			  echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
			  //$id++;
			  exit("<script>
			  	setTimeout(function() {
	  				window.location='http://sync.wsia.udb.ac.id/sopingi/kurikulum/sync/1/".$id."';
				}, 2000);
			  </script>");
		} else {
			$pesan= $insert['result']['error_desc'];
			$hasil['berhasil']=0;
		    $hasil['pesan']=$insert['result'];
		    $hasil['data']=$data[$i];
		    	
			echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/'.$key.'_kurikulum_insert_error_'.$id.'.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);

			exit("<br>Kesalahan Syncron: ".$pesan);
		}
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  }
	  
} 