<?php
$db 	= koneksi_wsia();

$proxy = proxy();
$token = token();

if ($aksi=="struktur") {


	$a=$proxy->GetDictionary($token,"mahasiswa_pt");
	$data=$a['result'];
	echo "Struktur mahasiswa_pt<br><pre>";
	print_r($data);
	echo "</pre>";

	

} else if ($aksi=="tambah") {
	$records_mahasiswa_pt=array();
	$records_WsMahasiswa=array();
	$qryWsMahasiswa="select * from view_mahasiswa_pt where left(xid_pd,4)='2020' and nipd<>'' and nipd<>'190207044' and id_sms<>'17' and id_jns_daftar=2 and xid_pd in (select xid_pd from view_mahasiswa_tambah_2020) limit 50 OFFSET $id";
	$exeWsMahasiswa=$db->query($qryWsMahasiswa);
	$data	= $exeWsMahasiswa->fetchAll(PDO::FETCH_OBJ);

	if ($data) {
		$ke=0;
		foreach($data as $row){
			$ke++;
			$records_WsMahasiswa['nipd']=$row->nipd;
			
			$nama=trim(str_replace("'","''",$row->nm_pd));
			$tmp_lahir=$row->tmpt_lahir;
			$tgl_lahir=$row->tgl_lahir;
			$nm_ibu_kandung=trim(str_replace("'","''",$row->nm_ibu_kandung));

			//update fikom
			/*
			if ($row->id_sms=="054de581-83be-4adf-85a4-65f6c28c065d") { //TI
				$row->id_sms="0e9ef2bb-0e36-4d37-8e04-4cfdbdc7eb78";
			} else if ($row->id_sms=="20ad1060-6e2e-4920-a977-036a79b458ed") { //TK
				$row->id_sms="a33b0a14-8a87-440f-a3fe-00adc3a48fa8";
			} else if ($row->id_sms=="35eee452-2a2a-4769-8b14-904934d8df4d") { //MI
				$row->id_sms="06b800c5-df5f-48c3-96cd-45a9cefadb0c";
			} else if ($row->id_sms=="79833b0d-28f2-4b80-af49-ac2fe74ea997") { //SI
				$row->id_sms="56dacace-c0a7-437d-893d-5e83262deac3";
			}
			

			if ($row->id_sms=="7ac1336d-5b24-48ee-ba9c-f7d507ac9d1c") { //RMIK
				$row->id_sms="4e5ffce3-7be2-4d05-b999-f39f9a8b3a6f";
			} else if ($row->id_sms=="93705ef5-48f4-4252-a939-0f7d96e1b06b") { //BIDAN
				$row->id_sms="a1eda96a-bb19-4e47-8894-9ba1811fd95b";
			} 
			*/
			
			echo $row->nipd." - ".$nama." - ".$tmp_lahir." - ".$tgl_lahir." - ".$nm_ibu_kandung."<hr>";
			
			$a_mahasiswa=$proxy->GetRecord($token,"mahasiswa.raw","nm_pd like '%$nama%' and tmpt_lahir='$tmp_lahir' and tgl_lahir='$tgl_lahir' and nm_ibu_kandung='$nm_ibu_kandung'");
			$mahasiswa=$a_mahasiswa['result'];
			/*
			echo "Mahasiswa ".$ke."<br><pre>";
			print_r($mahasiswa);
			echo "</pre>";
			*/

			$ada = count($mahasiswa);

			if ($ada) {

				$records_WsMahasiswa['id_pd']=$mahasiswa['id_pd'];
				$records_WsMahasiswa['id_sp']="55cd2bd1-f23e-40bc-a3ac-ff3b14f0f5c7";
				$records_WsMahasiswa['id_sms']=$row->id_sms;
				$records_WsMahasiswa['tgl_masuk_sp']=$row->tgl_masuk_sp;
				$records_WsMahasiswa['a_pernah_paud']=$row->a_pernah_paud;
				$records_WsMahasiswa['a_pernah_tk']=$row->a_pernah_tk;
				$records_WsMahasiswa['mulai_smt']=$row->mulai_smt;
				
				//$records_WsMahasiswa['id_jns_daftar']=$row->id_jns_daftar;
				$records_WsMahasiswa['id_jns_daftar']=1;
				$records_WsMahasiswa['nm_pt_asal']=$row->nm_pt_asal;
				$records_WsMahasiswa['nm_prodi_asal']=$row->nm_prodi_asal;
				$records_WsMahasiswa['biaya_masuk_kuliah']=0;
				
				array_push($records_mahasiswa_pt, $records_WsMahasiswa);
			}

		}

		echo json_encode($records_mahasiswa_pt);	

	
		$insert_mahasiswa_pt=$proxy->InsertRecordSet($token,"mahasiswa_pt",  json_encode($records_mahasiswa_pt) );
		echo "<pre>";
		print_r($insert_mahasiswa_pt);
		echo "</pre>";	
		$i=0;	
		    if (!isset($insert_mahasiswa_pt['result'])) {
				exit("Error InsertRecordSet");
			}  

		    foreach ($insert_mahasiswa_pt['result'] as $itemData) {
			 	$error = $itemData['error_code'];
			   
				 if ($error=="0") {
				   
						$hasil['berhasil']=1;
					    $hasil['pesan']="Berhasil Insert data Mahasiswa";
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
					
					$file = 'log/'.$key.'_mahasiswa_pt_insert_error_'.$id.'.txt';
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
			 //$id+=50;
			 //exit();
			
			exit("<script>
			  	setTimeout(function() {
						window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa_pt/tambah/".$key."/".$id."?sopingiwtwg';
				}, 2000);
			</script>");
		
	
	} else {
		exit("Habis pada Hal=".$id);
	}

} else if ($aksi=="update_id_reg_pd") {

	  try {

		   $db 	= koneksi_wsia();
		    
		   $get=proxy()->GetRecordSet(token(),"mahasiswa_pt.raw","mulai_smt='$key'",'id_reg_pd',500,$id);
		   //echo json_encode($get);
		   $result=$get['result'];
		   $jData=count($result);
		   if ($jData==0) {
		   		exit("Data Habis. di Halaman = ".$id);
		   }
		   $no=$id;
		   foreach ( $result as $itemResult) {
		   	  $no++;
			   $nipd=trim($itemResult['nipd']);
			   $id_reg_pd=$itemResult['id_reg_pd'];
			   $id_sms=$itemResult['id_sms'];
			   			  
			   $qryMahasiswaPt = "update wsia_mahasiswa_pt, wsia_sms set id_reg_pd='$id_reg_pd' where wsia_sms.xid_sms = wsia_mahasiswa_pt.id_sms and wsia_sms.id_sms='$id_sms' and  trim(replace(nipd,'.',''))='$nipd' ";
			   $eksekusi 	= $db->query($qryMahasiswaPt);  
			   if ($eksekusi->rowCount()>0) {
				echo $no.". Berhasil Ubah NIM=".$nipd." ID_REG_PD=".$id_reg_pd."<br>";

				$hasil = $no.". Berhasil Ubah. NIM=".$nipd." ID_REG_PD=".$id_reg_pd;

				$file = 'log/'.$key.'_id_reg_pd_sukses_update_'.$id.'.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);

			   } else {
				echo $no.". Tidak DiUbah. NIM=".$nipd." ID_REG_PD=".$id_reg_pd."<br>";

				$hasil = $no.". Tidak DiUbah. NIM=".$nipd." ID_REG_PD=".$id_reg_pd;

				$file = 'log/'.$key.'_id_reg_pd_error_update_'.$id.'.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);

			   }
			
		  }
		  
		  $db	= null;
		  
		  echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		  $id+=500;
		  //exit();
		  exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa_pt/update_id_reg_pd/".$key."/".$id."?sopingiwtwg';
			}, 2000);
		  </script>");
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 
	  
} else if ($aksi=="cek_id_reg_pd") {

	  try {

		   $db 	= koneksi_wsia();
		    
		   $get=proxy()->GetRecordSet(token(),"mahasiswa_pt.raw","mulai_smt='$key'",'id_reg_pd',500,$id);
		   //echo json_encode($get);
		   $result=$get['result'];
		   $jData=count($result);
		   if ($jData==0) {
		   		exit("Data Habis. di Halaman = ".$id);
		   }
		   $no=$id;
		   foreach ( $result as $itemResult) {
		   	  $no++;
			   $nipd=trim($itemResult['nipd']);
			   $id_reg_pd=$itemResult['id_reg_pd'];
			   $id_sms=$itemResult['id_sms'];
			   			  
			   $qryMahasiswaPt = "select * from wsia_mahasiswa_pt, wsia_sms where id_reg_pd='$id_reg_pd' and wsia_sms.xid_sms = wsia_mahasiswa_pt.id_sms and wsia_sms.id_sms='$id_sms' and  trim(replace(nipd,'.',''))='$nipd' ";
			   $eksekusi 	= $db->query($qryMahasiswaPt);  
			   if ($eksekusi->rowCount()>0) {
					echo $no.". Data Ditemukan. NIM=".$nipd." ID_REG_PD=".$id_reg_pd."<br>";

					$hasil = $no.". Data Ditemukan. NIM=".$nipd." ID_REG_PD=".$id_reg_pd;

					$file = 'log/'.$key.'_id_reg_pd_ditemukan.txt';
					// Open the file to get existing content
					$current = file_get_contents($file);
					// Append a new person to the file
					$current .= json_encode($hasil)."\n\n";
					// Write the contents back to the file
					file_put_contents($file, $current);

			   } else {
					echo $no.". Tidak Ditemukan. NIM=".$nipd." ID_REG_PD=".$id_reg_pd."<br>";

					$hasil = $no.",".$nipd.",".$id_reg_pd.",".$id_sms;

					$file = 'log/'.$key.'_id_reg_pd_tidakada.txt';
					// Open the file to get existing content
					$current = file_get_contents($file);
					// Append a new person to the file
					$current .= json_encode($hasil)."\n\n";
					// Write the contents back to the file
					file_put_contents($file, $current);
			   }
			
		  }
		  
		  $db	= null;
		  
		  echo "<h3>Halaman akan merefresh selama 2 detik</h3>";
		  $id+=500;
		  //exit();
		  exit("<script>
		  	setTimeout(function() {
  				window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa_pt/cek_id_reg_pd/".$key."/".$id."';
			}, 2000);
		  </script>");
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 
	  
} else if ($aksi=="update_id_reg_pd_1") {
	  try {

		   $db 	= koneksi_wsia();
		    
		   $get=proxy()->GetRecordSet(token(),"mahasiswa_pt.raw","nipd='160103197'",'id_reg_pd',1,$id);
		   //echo json_encode($get);
		   $result=$get['result'];
		   $jData=count($result);
		   $no=$id;
		   foreach ( $result as $itemResult) {
		   	  $no++;
			   $nipd=trim($itemResult['nipd']);
			   $id_reg_pd=$itemResult['id_reg_pd'];
			   			  
			   $qryMahasiswaPt = "update wsia_mahasiswa_pt set id_reg_pd='$id_reg_pd' where trim(nipd)='$nipd'";
			   $eksekusi 	= $db->query($qryMahasiswaPt);  
			   if ($eksekusi->rowCount()>0) {
				echo $no.". Berhasil Ubah NIM=".$nipd." ID_REG_PD=".$id_reg_pd."<br>";
			   } else {
				echo $no.". Tidak DiUbah. NIM=".$nipd." ID_REG_PD=".$id_reg_pd."<br>";
			   }
			
		  }
		  
		  $db	= null;
		  
		  echo "<h3>selesai</h3>";
		 
		  exit();
		  
	  } catch (PDOException $salah) {
		   exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	  } 
	  
} else if ($aksi=="tampil") {
	
	$proxy = proxy();
	$token = token();
	$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"mahasiswa_pt","nipd like '%110101072%'","nipd",1000,$id );
	$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
	
	echo "Kuliah_mahasiswa<br><pre>";
	print_r($a_kuliah_mahasiswa);
	echo "</pre>";
	
	//buat file
	$array =$kuliah_mahasiswa;
	$f = fopen('mahasiswa_pt_lama_'.$id.'.csv', 'w');
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
	
} else if ($aksi=="lulus") {
	
	$proxy = proxy();
	$token = token();
	
	 $db 	= koneksi_wsia_off();
	//$qryMahasiswaLulus = "select * from ws_kelulusan";
	 $qryMahasiswaLulus = "select * from ws_kelulusan";
	$qryMahasiswaLulus .= "  LIMIT 1 ";
	$qryMahasiswaLulus .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	$eksekusi 	= $db->query($qryMahasiswaLulus); 
	
	$records_mahasiswa_pt=array();
	$records_kuliah_mahasiswa=array();
	
	foreach ($eksekusi  as $dataWsLulus) {
		
		$nim=trim($dataWsLulus['nim']);
		$id_jns_keluar=$dataWsLulus['id_jns_keluar'];
		$tgl_keluar=$dataWsLulus['tgl_keluar']; 
		$ket=$dataWsLulus['ket']; 
		$jalur_skripsi=$dataWsLulus['jalur_skripsi'];
		$judul_skripsi=$dataWsLulus['judul_skripsi'];
		$bln_awal_bimbingan=$dataWsLulus['bln_awal_bimbingan'];
		$bln_akhir_bimbingan=$dataWsLulus['bln_akhir_bimbingan'];
		$sk_yudisium=$dataWsLulus['sk_yudisium'];
		$tgl_sk_yudisium=$dataWsLulus['tgl_sk_yudisium'];
		$ipk=$dataWsLulus['ipk'];
		$no_seri_ijazah=$dataWsLulus['no_seri_ijazah'];
		$id_smt=$key;
		$id_stat_mhs="A";
		//$ips=$dataWsLulus['ips'];
		//$sks_smt=$dataWsLulus['sks_smt'];
		$sks_total=$dataWsLulus['sks_total'];
		echo $nim."-";
		$a_mahasiswa_pt=$proxy->GetRecord($token,"mahasiswa_pt","nipd like '%$nim%'");
		$mahasiswa_pt=$a_mahasiswa_pt['result'];
		
		//echo "<pre>";
		//print_r($a_mahasiswa_pt);
		//echo "</pre>";
		
		$id_reg_pd=$mahasiswa_pt['id_reg_pd'];

		echo $id_reg_pd."<br>";
		
		$nilai_smt=0;
		$sks_smt=0;
		
		
		//HITUNG IPS = Nilai.s / SKS.s
		$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',500,0);
		$nilai=$a_nilai['result'];
		
		//echo "Nilai<br><pre>";
		//print_r($nilai);
		//echo "</pre>";
		
		$j_nilai=count($nilai);
		for($j=0;$j<$j_nilai;$j++) {
			$id_kls=$nilai[$j]['id_kls'];
			$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls' ");
			$kelas_kuliah=$a_kelas_kuliah['result'];
			
			if ($kelas_kuliah['id_smt']==$id_smt) {
				//echo "Kelas Kuliah ".$j."<br><pre>";
				//	print_r($a_kelas_kuliah);
				//echo "</pre>";
					
				$sks_smt+=$kelas_kuliah['sks_mk'];
				$nilai_smt+=$nilai[$j]['nilai_indeks']*$kelas_kuliah['sks_mk'];
			}
		}
		
		$ips=$nilai_smt/$sks_smt;
		
		
		$key_mahasiswa_pt=array('id_reg_pd'=>$id_reg_pd);
		$data_mahasiswa_pt=array('id_jns_keluar'=>$id_jns_keluar,
				      'tgl_keluar'=>$tgl_keluar,
				      'ket'=>$ket,
				      'jalur_skripsi'=>$jalur_skripsi,
				      'judul_skripsi'=>$judul_skripsi,
				      'bln_awal_bimbingan'=>$bln_awal_bimbingan,
				      'bln_akhir_bimbingan'=>$bln_akhir_bimbingan,
				      'sk_yudisium'=>$sk_yudisium,
				      'tgl_sk_yudisium'=>$tgl_sk_yudisium,
				      'ipk'=>$ipk,
				      'no_seri_ijazah'=>$no_seri_ijazah
		);
		
		
		$data_kuliah_mahasiswa=array('id_smt'=>$id_smt,
				      'id_reg_pd'=>$id_reg_pd,
				      'id_stat_mhs'=>$id_stat_mhs,
				      'ips'=>$ips,	//hitung lagi
				      'ipk'=>$ipk,
				      'sks_smt'=>$sks_smt, //hitung lagi
				      'sks_total'=>$sks_total
		);
		
		$records_mahasiswa_pt[]=array('key'=>$key_mahasiswa_pt,'data'=>$data_mahasiswa_pt);
		$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
	
    
		
	} 
	
	echo "<hr>";
	echo json_encode($records_mahasiswa_pt);
	echo "<hr>";
	echo json_encode($records_kuliah_mahasiswa);


	
	$update_mahasiswa_pt=$proxy->UpdateRecordSet($token,"mahasiswa_pt",json_encode($records_mahasiswa_pt));	
	echo "<pre>";
	print_r($update_mahasiswa_pt);
	echo "</pre>";

	$update_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));
	echo "<pre>";
	print_r($update_kuliah_mahasiswa);
	echo "</pre>";		
	
	$berhasilLulus=$update_mahasiswa_pt['result'][0]['error_code'];
	$berhasilAktifitas=$update_kuliah_mahasiswa['result'][0]['error_code'];

	if ($berhasilLulus==0 && $berhasilAktifitas==0) {
		$id+=1;
			 
		exit("<script>
	  	setTimeout(function() {
				window.location='http://localhost/stmikdb_feeder/sopingi/mahasiswa_pt/lulus/".$key."/".$id."';
		}, 2000);
	  </script>");
	
	} else {
		exit ("Kesalahan");
	}


} else if ($aksi=="lulus20172") {
	
	$proxy = proxy();
	$token = token();
	
	$db 	= koneksi_wsia_off();
	$qryMahasiswaLulus = "select * from civil";
	$qryMahasiswaLulus .= "  LIMIT 1 ";
	$qryMahasiswaLulus .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	$eksekusi 	= $db->query($qryMahasiswaLulus); 
	
	$records_mahasiswa_pt=array();
	$records_kuliah_mahasiswa=array();
	$records_kuliah_mahasiswa_tambah=array();
	
	foreach ($eksekusi  as $dataWsLulus) {
		
		$nim=trim($dataWsLulus['nim']);
		$id_jns_keluar=1;
		$tgl_keluar=trim($dataWsLulus['tanggal_keluar']); 
		$ket=$dataWsLulus['keterangan']; 
		$jalur_skripsi=1;
		$judul_skripsi=$dataWsLulus['judul_skripsi'];
		$bln_awal_bimbingan=trim($dataWsLulus['bulan_awal_bimbingan']);
		$bln_akhir_bimbingan=trim($dataWsLulus['bulan_akhir_bimbingan']);
		$sk_yudisium=$dataWsLulus['sk_yudisium'];
		$tgl_sk_yudisium=trim($dataWsLulus['tgl_sk_yudisium']);
		$ipk=$dataWsLulus['ipk'];
		$no_seri_ijazah=$dataWsLulus['no_seri_ijazah'];
		$id_smt=$key;
		$id_stat_mhs="A";
		//$ips=$dataWsLulus['ips'];
		//$sks_smt=$dataWsLulus['sks_smt'];
		$sks_total=$dataWsLulus['sks_total'];
		echo $nim."-";
		$a_mahasiswa_pt=$proxy->GetRecord($token,"mahasiswa_pt.raw","nipd like '%$nim%'");
		$mahasiswa_pt=$a_mahasiswa_pt['result'];
		
		//echo "<pre>";
		//print_r($a_mahasiswa_pt);
		//echo "</pre>";
		
		$id_reg_pd=$mahasiswa_pt['id_reg_pd'];

		echo $id_reg_pd."<br>";
		
		$nilai_smt=0;
		$sks_smt=0;
		
		
		//HITUNG IPS = Nilai.s / SKS.s
		$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',500,0);
		$nilai=$a_nilai['result'];
		
		//echo "Nilai<br><pre>";
		//print_r($nilai);
		//echo "</pre>";
		
		$j_nilai=count($nilai);
		for($j=0;$j<$j_nilai;$j++) {
			$id_kls=$nilai[$j]['id_kls'];
			$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls' ");
			$kelas_kuliah=$a_kelas_kuliah['result'];
			
			if ($kelas_kuliah['id_smt']==$id_smt) {
				//echo "Kelas Kuliah ".$j."<br><pre>";
				//	print_r($a_kelas_kuliah);
				//echo "</pre>";
					
				$sks_smt+=$kelas_kuliah['sks_mk'];
				$nilai_smt+=$nilai[$j]['nilai_indeks']*$kelas_kuliah['sks_mk'];
			}
		}
		
		$ips=$nilai_smt/$sks_smt;
		
		
		$key_mahasiswa_pt=array('id_reg_pd'=>$id_reg_pd);
		$data_mahasiswa_pt=array('id_jns_keluar'=>$id_jns_keluar,
				      'tgl_keluar'=>$tgl_keluar,
				      'ket'=>$ket,
				      'jalur_skripsi'=>$jalur_skripsi,
				      'judul_skripsi'=>$judul_skripsi,
				      'bln_awal_bimbingan'=>$bln_awal_bimbingan,
				      'bln_akhir_bimbingan'=>$bln_akhir_bimbingan,
				      'sk_yudisium'=>$sk_yudisium,
				      'tgl_sk_yudisium'=>$tgl_sk_yudisium,
				      'ipk'=>$ipk,
				      'no_seri_ijazah'=>$no_seri_ijazah
		);
		
		
		$key_kuliah_mahasiswa=array('id_smt'=>$id_smt,'id_reg_pd'=>$id_reg_pd);
		$data_kuliah_mahasiswa=array(
				      'id_stat_mhs'=>$id_stat_mhs,
				      'ips'=>$ips,	//hitung lagi
				      'ipk'=>$ipk,
				      'sks_smt'=>$sks_smt, //hitung lagi
				      'sks_total'=>$sks_total
		);

		$data_kuliah_mahasiswa_tambah=array(
					  'id_smt'=>$id_smt,
					  'id_reg_pd'=>$id_reg_pd,
				      'id_stat_mhs'=>$id_stat_mhs,
				      'ips'=>$ips,	//hitung lagi
				      'ipk'=>$ipk,
				      'sks_smt'=>$sks_smt, //hitung lagi
				      'sks_total'=>$sks_total
		);
		
		print_r(array('key'=>$key_mahasiswa_pt,'data'=>$data_mahasiswa_pt));

		$records_mahasiswa_pt[]=array('key'=>$key_mahasiswa_pt,'data'=>$data_mahasiswa_pt);
		$records_kuliah_mahasiswa[]=array('key'=>$key_kuliah_mahasiswa,'data'=>$data_kuliah_mahasiswa);
		$records_kuliah_mahasiswa_tambah[]=$data_kuliah_mahasiswa_tambah;
	
    
		
	} 
	
	echo "<hr>";
	print_r( json_encode($records_mahasiswa_pt) );
	echo "<hr>";
	print_r( json_encode($records_kuliah_mahasiswa) );


	
	$update_mahasiswa_pt=$proxy->UpdateRecordSet($token,"mahasiswa_pt",json_encode($records_mahasiswa_pt));	
	echo "<pre>";
	print_r($update_mahasiswa_pt);
	echo "</pre>";

	$update_kuliah_mahasiswa=$proxy->UpdateRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));
	echo "<pre>";
	print_r($update_kuliah_mahasiswa);
	echo "</pre>";		
	
	$berhasilLulus=$update_mahasiswa_pt['result'][0]['error_code'];
	$berhasilAktifitas=$update_kuliah_mahasiswa['result'][0]['error_code'];

	if ($berhasilAktifitas!=0) {

		$update_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa_tambah));
		echo "<pre>";
		print_r($update_kuliah_mahasiswa);
		echo "</pre>";	

		$id+=1;
			 
		exit("<script>
	  	setTimeout(function() {
				window.location='http://localhost/stmikdb_feeder_22-10-2018/sopingi/mahasiswa_pt/lulus20172/".$key."/".$id."';
		}, 2000);
	  </script>");

	} else if ($berhasilLulus==0 && $berhasilAktifitas==0) {
		$id+=1;
		//exit();	 
		exit("<script>
	  	setTimeout(function() {
				window.location='http://localhost/stmikdb_feeder_22-10-2018/sopingi/mahasiswa_pt/lulus20172/".$key."/".$id."';
		}, 2000);
	  </script>");
	
	} else {
		exit ("Kesalahan");
	}


} else if ($aksi=="updatecivil") {
	
	$proxy = proxy();
	$token = token();
	
	$db 	= koneksi_wsia_off();
	$qryCivil = "select * from civil";
	//$qryCivil .= "  LIMIT 310 ";
	//$qryCivil .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	$eksekusi 	= $db->query($qryCivil); 
	$data	= $eksekusi->fetchAll(PDO::FETCH_OBJ);
	
	//print_r($data);
	$records_mahasiswa_pt= array();

	foreach ($data  as $item) {
		
		$nim=trim($item->nim);
		$id_jns_keluar=1;

		$tanggal_keluar = strtotime(trim($item->tanggal_keluar));
		$tgl_keluar= date('Y-m-d',$tanggal_keluar);

		$ket = trim($item->keterangan);
		$sk_yudisium = trim($item->sk_yudisium);

		$tgl_sk_yudisium = strtotime(trim($item->tgl_sk_yudisium));
		$tgl_sk_yudisium = date('Y-m-d',$tgl_sk_yudisium);

		$ipk = trim($item->ipk);		
		$no_seri_ijazah = trim($item->no_seri_ijazah);
		$jalur_skripsi = 1;
		$judul_skripsi = trim($item->judul_skripsi);

		$bulan_awal_bimbingan = strtotime(trim($item->bulan_awal_bimbingan));
		$bln_awal_bimbingan = date('Y-m-d',$bulan_awal_bimbingan);

		$bulan_akhir_bimbingan = strtotime(trim($item->bulan_akhir_bimbingan));
		$bln_akhir_bimbingan = date('Y-m-d',$bulan_akhir_bimbingan);

		$a_mahasiswa_pt=$proxy->GetRecord($token,"mahasiswa_pt","nipd like '%$nim%'");
		$mahasiswa_pt=$a_mahasiswa_pt['result'];
		
		//echo "<pre>";
		//print_r($a_mahasiswa_pt);
		//echo "</pre>";
		
		$id_reg_pd=$mahasiswa_pt['id_reg_pd'];

		$key_mahasiswa_pt=array('id_reg_pd'=>$id_reg_pd);
		$data_mahasiswa_pt=array(
					  'id_jns_keluar'=>$id_jns_keluar,
				      'tgl_keluar'=>$tgl_keluar,
				      'ket'=>$ket,
				      'sk_yudisium'=>$sk_yudisium,
				      'tgl_sk_yudisium'=>$tgl_sk_yudisium,
				      'ipk'=>$ipk,				      
				      'no_seri_ijazah'=>$no_seri_ijazah,
				      'jalur_skripsi'=>$jalur_skripsi,
				      'judul_skripsi'=>$judul_skripsi,
				      'bln_awal_bimbingan'=>$bln_awal_bimbingan,
				      'bln_akhir_bimbingan'=>$bln_akhir_bimbingan,
		);
		

		print_r($data_mahasiswa_pt);

		$records_mahasiswa_pt[]=array('key'=>$key_mahasiswa_pt,'data'=>$data_mahasiswa_pt);
		
	} 
	
	echo "<hr>";
	print_r(json_encode($records_mahasiswa_pt));
	//exit();
	
	$update_mahasiswa_pt=$proxy->UpdateRecordSet($token,"mahasiswa_pt",json_encode($records_mahasiswa_pt));	
	echo "<pre>";
	print_r($update_mahasiswa_pt);
	echo "</pre>";
	
	$berhasilLulus=$update_mahasiswa_pt['result'][0]['error_code'];
	
	if ($berhasilLulus==0) {

		$file = 'log/'.$key.'_mahasiswa_pt_civil_SUKSES_'.$id.'.txt';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= json_encode($update_mahasiswa_pt)."\n\n";
		// Write the contents back to the file
		file_put_contents($file, $current);

		$id+=100;
		exit(); 
		exit("<script>
	  	setTimeout(function() {
				window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa_pt/updatecivil/".$key."/".$id."';
		}, 2000);
	  </script>");
	
	} else {

		$file = 'log/'.$key.'_mahasiswa_pt_civil_GAGAL_'.$id.'.txt';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= json_encode($update_mahasiswa_pt)."\n\n";
		// Write the contents back to the file
		file_put_contents($file, $current);

		exit ("Kesalahan");
	}
	
} else if ($aksi=="updatecivilfikes") {
	
	$proxy = proxy();
	$token = token();
	
	$db 	= koneksi_wsia();
	$qryCivil = "select * from civil2";
	$qryCivil .= "  LIMIT 500 ";
	$qryCivil .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	$eksekusi 	= $db->query($qryCivil); 
	$data	= $eksekusi->fetchAll(PDO::FETCH_OBJ);
	
	//print_r($data);
	$records_mahasiswa_pt= array();

	foreach ($data  as $item) {
		
		$nim=trim(str_replace(".", "", $item->nim));
		if (trim($item->program_studi)=="D3 Kebidanan") {
			$id_sms="a1eda96a-bb19-4e47-8894-9ba1811fd95b";
		} else {
			$id_sms="4e5ffce3-7be2-4d05-b999-f39f9a8b3a6f";
		}

		$id_jns_keluar=1;

		$tanggal_keluar = strtotime(trim($item->tanggal_keluar));
		$tgl_keluar= date('Y-m-d',$tanggal_keluar);

		$ket = trim($item->keterangan);
		$sk_yudisium = trim($item->sk_yudisium);

		$tgl_sk_yudisium = strtotime(trim($item->tgl_sk_yudisium));
		$tgl_sk_yudisium = date('Y-m-d',$tgl_sk_yudisium);

		$ipk = trim($item->ipk);		
		$no_seri_ijazah = trim($item->no_seri_ijazah);
		$jalur_skripsi = 1;
		$judul_skripsi = trim($item->judul_skripsi);

		$bulan_awal_bimbingan = strtotime(trim($item->bulan_awal_bimbingan));
		$bln_awal_bimbingan = date('Y-m-d',$bulan_awal_bimbingan);

		$bulan_akhir_bimbingan = strtotime(trim($item->bulan_akhir_bimbingan));
		$bln_akhir_bimbingan = date('Y-m-d',$bulan_akhir_bimbingan);

		$a_mahasiswa_pt=$proxy->GetRecord($token,"mahasiswa_pt.raw","nipd like '%$nim%' and id_sms='$id_sms'");
		$mahasiswa_pt=$a_mahasiswa_pt['result'];
		
		/*
		echo "<pre>";
		print_r($a_mahasiswa_pt);
		echo "</pre>";
		*/
		
		$id_reg_pd=$mahasiswa_pt['id_reg_pd'];

		$key_mahasiswa_pt=array('id_reg_pd'=>$id_reg_pd);
		$data_mahasiswa_pt=array(
					  'id_jns_keluar'=>$id_jns_keluar,
				      'tgl_keluar'=>$tgl_keluar,
				      'ket'=>$ket,
				      'sk_yudisium'=>$sk_yudisium,
				      'tgl_sk_yudisium'=>$tgl_sk_yudisium,
				      'ipk'=>$ipk,				      
				      'no_seri_ijazah'=>$no_seri_ijazah,
				      'jalur_skripsi'=>$jalur_skripsi,
				      'judul_skripsi'=>$judul_skripsi,
				      'bln_awal_bimbingan'=>$bln_awal_bimbingan,
					  'bln_akhir_bimbingan'=>$bln_akhir_bimbingan,
					  'smt_yudisium'=>'20192'
		);
		

		//print_r($data_mahasiswa_pt);

		$records_mahasiswa_pt[]=array('key'=>$key_mahasiswa_pt,'data'=>$data_mahasiswa_pt);

		
		
	} 
	
	echo "<hr>";
	print_r(json_encode($records_mahasiswa_pt));
	//exit();
	
	$update_mahasiswa_pt=$proxy->UpdateRecordSet($token,"mahasiswa_pt",json_encode($records_mahasiswa_pt));	
	echo "<pre>";
	print_r($update_mahasiswa_pt);
	echo "</pre>";
	
	$berhasilLulus=$update_mahasiswa_pt['result'][0]['error_code'];
	
	if ($berhasilLulus==0) {

		$file = 'log/'.$key.'_mahasiswa_pt_civil_SUKSES_'.$id.'.txt';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= json_encode($update_mahasiswa_pt)."\n\n";
		// Write the contents back to the file
		file_put_contents($file, $current);

		$id+=100;
		exit(); 
		exit("<script>
	  	setTimeout(function() {
				window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa_pt/updatecivilfikes/".$key."/".$id."';
		}, 2000);
	  </script>");
	
	} else {

		$file = 'log/'.$key.'_mahasiswa_pt_civil_GAGAL_'.$id.'.txt';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= json_encode($update_mahasiswa_pt)."\n\n";
		// Write the contents back to the file
		file_put_contents($file, $current);

		exit ("Kesalahan");
	}
	
}