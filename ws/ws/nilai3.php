<?php

if ($aksi=="sync") {

  	$kelas = array(
  	"13f85e4a-36ef-4199-b187-24889eb03d97",
  	"889f8dd5-f102-47c7-b6d0-8311dee94991",
  	"94999d0e-2f92-4df4-b254-ce2c2bc60e07",
  	"9dda36ba-4aa1-4d01-bdd6-2fb1ce0dc2ac",
  	"e3b64f04-ff86-4362-8848-92a4f56d93c0",
  	"e4941f56-b865-44eb-a967-66b2088a46c3",
  	"1481aec1-4f9d-4873-bcd5-768ad6e468c2",
  	"0712161d-fe89-44d6-8db8-7f1f9e55ed33",
  	"cb69c318-4c1d-4c5b-be80-07b4beee722a",
  	"f6d2078e-a62b-4ef4-aa23-73459eb1e67d",
  	"66193fe4-4267-4903-a417-2bb906c12a37",
  	"3e83f5cc-1af3-490e-a054-f0cc37454549"
  	);
  foreach ($kelas as $itemKls) {
	  
	  $perintah = "SELECT
					wsia_kelas_kuliah.id_kls,
					wsia_mahasiswa_pt.id_reg_pd,
					nilai_angka,
					nilai_huruf,
					nilai_indeks
				FROM
					wsia_kelas_kuliah,
					wsia_mahasiswa_pt,
					mahasiswa,
					wsia_nilai
				WHERE
				( wsia_nilai.xid_kls = wsia_kelas_kuliah.xid_kls
				AND wsia_kelas_kuliah.id_kls = '$itemKls')
				AND (wsia_nilai.xid_reg_pd = mahasiswa.no_pend
				AND trim(wsia_mahasiswa_pt.nipd) = trim(mahasiswa.nim)
				) ";
	  	  
	  $perintah .= "  LIMIT 100 ";
	  $perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	  
	  try {
		    $db 	= koneksi_wsia();
		    
		    $qry 	= $db->query($perintah); 
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		   
		    if ($qry->rowCount()==0) {
		  	//exit($key." - ".$id." : Data Habis. di Halaman = ".$id);
		    }
		   	   
		   print_r($data);
		   //exit(); 
		    		    
		   $insert=proxy()->InsertRecordSet(token(),"nilai",json_encode($data));
		   
		   //print_r($insert['result']);

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
				
				$file = 'log_insert_nilai'.$id.'.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			 }
			 
			 $i++;
			 
		}
		  	  
		 
		 
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 

	}

} 