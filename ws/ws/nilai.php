<?php

if ($aksi=="sync") {

	  $kondisi = " AND wsia_nilai.xid_kls in ('2002271015001582772921742',
	  '2002271023181582772921929',
	  '2002271026201582772922027',
	  '2002271029151582772922120',
	  '2002271051491582772922588',
	  '2002271054531582772922728',
	  '2002271100451582772922871',
	  '2002271800101582800255977',
	  '2002271800231582800256049',
	  '2002271800391582800256139',
	  '2002271801071582800256230',
	  '2002271801451582800256326',
	  '2002271801591582800256419',
	  '2002271802121582800256513',
	  '2002271802231582800256608',
	  '2002281323051582870621140',
	  '2002281501241582876716925',
	  '2002281501421582876716989',
	  '2002281502011582876717079',
	  '2002281502291582876717173',
	  '2002281502471582876717265',
	  '2002281503031582876717358',
	  '2002281508151582876717458',
	  '2002281508381582876717553',
	  '2002281508531582876717649',
	  '2002281509111582876717746',
	  '2002281510051582876717844',
	  '2002281510201582876717933',
	  '2002281510361582876718023',
	  '2002281510541582876718114',
	  '2002291158101582952044875',
	  '2002291158401582952044980',
	  '2002291216271582952052318',
	  '2002291216571582952052392',
	  '2004280927231588039999732',
	  '2004280927401588039999792',
	  '2004280935101587997850725',
	  '2006241107541592971626041',
	  '2010071120401602043738164',
	  '2010071132151602043741793',
	  '2010071144481602043745360',
	  '2010071145161602043745439',
	  '2010071410201602043754519',
	  '2010071410411602043754598') ";
	  //$kondisi="";

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
					wsia_nilai.xid_kls = wsia_kelas_kuliah.xid_kls
				AND wsia_nilai.xid_reg_pd = mahasiswa.no_pend
				AND wsia_kelas_kuliah.id_kls<>''
				AND trim(wsia_mahasiswa_pt.nipd) = trim(mahasiswa.nim)
				AND wsia_kelas_kuliah.id_smt = '$key' $kondisi order by wsia_kelas_kuliah.id_kls asc";
				
				
	  	  
	  $perintah .= "  LIMIT 100 ";
	  $perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	  
	  try {
	  	
		    $db 	= koneksi_wsia();
		    
		    $qry 	= $db->query($perintah); 
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    
		    echo "<pre>";
		    //print_r(json_encode($data));
		    echo "</pre>";
		
		   
		    if ($qry->rowCount()==0) {
				  
				  
				 $file = 'log/'.$key.'_nilai_selesai_'.$id.'.txt';
				 $current = file_get_contents($file);
				 $current .= json_encode($hasil)."\n\n";
				 file_put_contents($file, $current);
				 exit($key." - ".$id." : Data Habis. di Halaman = ".$id);
		    }
		   	  
		    		    
		    $insert=proxy()->InsertRecordSet(token(),"nilai",json_encode($data));
		  
		  
		    //print_r(json_encode($insert['result']));
		   
		    $i=0;	
		    if (!isset($insert['result'])) {
				exit("Error InsertRecordSet");
			}  
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
				
				$file = 'log/'.$key.'_nilai_insert_error_'.$id.'.txt';
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
  				window.location='http://sync.wsia.udb.ac.id/sopingi/nilai/sync/".$key."/".$id."?sopingiwtwg';
			}, 2000);
		  </script>");
		 
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 
} else if ($aksi=="syncLama") {
	  $perintah = "select nim from mahasiswa where no_pend in (select no_pend from krs_susulan)";
	  	  
	  $perintah .= "  LIMIT 1 ";
	  $perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	  
	  try {
	  	
		    $db 	= koneksi_wsia_off();
		    
		    $qry 	= $db->query($perintah); 
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    
		    foreach ($data as $item) {
		    	
				$result=proxy()->GetRecord(token(),"mahasiswa_pt","trim(nipd) like '%".$item->nim."%'");

				$mahasiswa_pt=$result['result'];
				
				
				echo "Mahasiswa<br><pre>";
					//print_r($mahasiswa_pt);
				echo "</pre>";
				
				if (isset($mahasiswa_pt['id_reg_pd'])) {
					
					if ($item->nilai=="A") { $indeks=4;}
					else if ($item->nilai=="B") { $indeks=3;}
					else if ($item->nilai=="C") { $indeks=2;}
					else if ($item->nilai=="D") { $indeks=1;}
					else if ($item->nilai=="E") { $indeks=0;}
					
					$nilai["id_kls"]=$item->id_kls;
					$nilai["id_reg_pd"]=$mahasiswa_pt['id_reg_pd'];
					$nilai["nilai_angka"]=$item->angka;
					$nilai["nilai_huruf"]=$item->nilai;
					$nilai["nilai_indeks"]=$indeks; 
					
					echo "<pre>";
			    		print_r(json_encode($nilai));
			    	echo "</pre>";
			    	
			    	$insert=proxy()->InsertRecord(token(),"nilai",json_encode($nilai));
			    	
			    	print_r(json_encode($insert));
				} else {
					exit($id." - Feeder Gak Nyambung...");
				} 
		    	
			}
			
			
		    
		    
		    echo "<pre>";
		    //print_r(json_encode($data));
		    echo "</pre>";
		
		    
		   
		    if ($qry->rowCount()==0) {
		  		exit($key." - ".$id." : Data Habis. di Halaman = ".$id);
		    }
		   	  
		    		    
		    $insert=proxy()->InsertRecordSet(token(),"nilai",json_encode($data));
		   
		    //$insert=proxy()->InsertRecordSet(token(),"nilai",json_encode($jsondata));
		  
		  
		    print_r(json_encode($insert['result']));
		   
		    $i=0;	
		    if (!isset($insert['result'])) {
				exit("Error InsertRecordSet");
			}  
		    foreach ($insert['result'] as $itemData) {
			 $error = $itemData['error_code'];
			   
			 if ($error=="0") {
			   
				$hasil['berhasil']=1;
			    $hasil['pesan']="Berhasil KRS";
			    $hasil['data']=$data[$i];
			    echo "<pre>";
				//print_r($hasil);
				echo "</pre>";
				 
			 } else {
				$pesan= $itemData['error_desc'];
				$hasil['berhasil']=0;
			    $hasil['pesan']=$itemData;
			    $hasil['data']=$data[$i];
			    	
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log/nilai_insert_error_'.$id.'.txt';
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
		 $id+=1;
		 if ($id==988) {
		 	exit($id);
		 }
		 exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/nilai/syncLama/".$key."/".$id."?sopingiwtwg';
			}, 1000);
		  </script>");
		 
		 
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 
} else if ($aksi=="struktur") {
	
	$a=proxy()->GetDictionary(token(),"nilai");
	$data=$a['result'];
	echo "Struktur Nilai<br><pre>";
	print_r(json_encode($data));
	echo "</pre>";
}