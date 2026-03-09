<?php

if ($aksi=="sync") {
	
	$proxy = proxy();
	$token=token();
  	$perintah = "select id_kls_feeder as id_kls,id_reg_pd,angka as nilai_angka,if (huruf='','E',huruf) as nilai_huruf, if(huruf='A',4,if(huruf='B',3,if(huruf='C',2,if(huruf='D',1,if(huruf='E',0,0))))) as nilai_indeks from tmp_krs_feeder limit 100 offset $id";
	  try {
		    $db 	= koneksi_wsia_off();
		    
		    $qry 	= $db->query($perintah); 
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);		    
		   	   
		   	echo "<pre>";
		    //print_r($data);
		    echo "</pre>";
		    		    
		   $insert=$proxy->InsertRecordSet($token,"nilai",json_encode($data));
		   
		   echo "<pre>";
		   print_r($insert['result']);
		   echo "</pre>";
		   $i=0;		  
		   foreach ($insert['result'] as $itemData) {
			 $error = $itemData['error_code'];
			   
			 if ($error=="0") {
			   
				$hasil['berhasil']=1;
			    	$hasil['pesan']="Berhasil KRS";
			    	$hasil['data']=$data[$i];
			    	echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				 
			 } else {
				$pesan= $itemData['error_desc'];
				$hasil['berhasil']=0;
			    	$hasil['pesan']=$itemData;
			    	$hasil['data']=$data[$i];
			    	
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log_insert_nilai_wsia_off.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			 }
			 
			 $i++;
			 
		}
		
		
		 echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		 $id+=100;
		 
		 exit("<script>
		  	setTimeout(function() {
  				window.location='http://localhost/stmikdb_feeder/sopingi/nilai_wsia_off/sync/".$key."/".$id."';
			}, 2000);
		  </script>");
		  	  
		 
		 
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 


} 