<?php

if ($aksi=="tampil") {
	
	$proxy = proxy();
	$token = token();
	//$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa","p.id_smt like '%20171%' and p.ipk <=2.5","ipk",500,$id );
	$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa","p.id_smt like '%20171%'","ipk",500,$id );
	$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
	
	echo "Kuliah_mahasiswa<br><pre>";
	print_r($a_kuliah_mahasiswa);
	echo "</pre>";
	
	//buat file
	$array =$kuliah_mahasiswa;
	$f = fopen('mahasiswa_nilai_ipk_20171_'.$id.'.csv', 'w');
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

	$id+=500;
	 
	exit("<script>
	  	setTimeout(function() {
			window.location='http://localhost/stmikdb_feeder_22-10-2018/sopingi/kuliah_mahasiswa/tampil/".$key."/".$id."';
		},1000);
	</script>");
	
} else if ($aksi=="struktur") {
	
	$proxy = proxy();
	$token = token();
	$a=$proxy->GetDictionary($token,"kuliah_mahasiswa");
	$data=$a['result'];
	echo "Struktur kuliah_mahasiswa<br><pre>";
	print_r($data);
	echo "</pre>";

} else if ($aksi=="tambah") {
	
	$proxy = proxy();
	$token = token();
	
	$mulai_smt='20191'; //ANGKATAN

	$records_kuliah_mahasiswa=array();
	
	$result=$proxy->GetRecordSet($token,"mahasiswa_pt.raw","mulai_smt like '$mulai_smt' and ((id_jns_keluar='') IS NOT FALSE)","nipd",1,$id);

	$mahasiswa_pt=$result['result'];
	
	
	/*
	echo "Mahasiswa PT <br><pre>";
		print_r($mahasiswa_pt);
	echo "</pre>";
	 exit();
	 */
	
		
	$jMhs = count($mahasiswa_pt);
	if ($jMhs==0) {
		exit('SELESAI '.$mulai_smt);
	}
	
	foreach ($mahasiswa_pt as $itemMahasiswa_pt) {
		$id_reg_pd=$itemMahasiswa_pt['id_reg_pd'];
		
		$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',200,0);
		$nilai=$a_nilai['result'];
		
		/*
		echo "Nilai<br><pre>";
		print_r($nilai);
		echo "</pre>";
		*/
		
		$j_nilai=count($nilai);
		
		$nilaiSMT=0;
		$sksSMT=0;
		
		//HITUNG IPS = Nilai.s / SKS.s
		$aktif=0;
		foreach ($nilai as $itemNilai) {
			$id_kls=$itemNilai['id_kls'];
			$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls'");
			$kelas_kuliah=$a_kelas_kuliah['result'];
			
			if ($kelas_kuliah['id_smt']=="$key") {
				$aktif=1;
				$nilaiSMT+=$itemNilai['nilai_indeks'] * $kelas_kuliah['sks_mk']; //nilai mutu Semester
				$sksSMT+=$kelas_kuliah['sks_mk']; // SKS Semester
				
				
				//echo "Kelas Kuliah<br><pre>";
				//print_r($kelas_kuliah);
				//echo "</pre>";
				
			}
			
		}
		
		if ($aktif=="1") {
			$IPS=$nilaiSMT/$sksSMT;
		}
		
		//HITUNG SKS TOTAL = SKS.S+SKS.T
		$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa.raw","id_reg_pd = '$id_reg_pd'","id_smt",100,0 );
		$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
		
		/*
		echo "Kuliah Mhs<br><pre>";
		print_r($a_kuliah_mahasiswa);
		echo "</pre>";
		*/
		
		$sks_sebelumnya=0;
		$nilaiMutu_sebelumnya=0;
		
		foreach ($kuliah_mahasiswa as $itemKuliah_mahasiswa) {
			if ($itemKuliah_mahasiswa['id_smt']<$key and $itemKuliah_mahasiswa['id_stat_mhs']=="A") {
				//echo "SKS Sebelum == ".$itemKuliah_mahasiswa['sks_smt']."<br>";
				$sks_sebelumnya+=$itemKuliah_mahasiswa['sks_smt'];
				$nilaiMutu_sebelumnya+= ($itemKuliah_mahasiswa['sks_smt'] * $itemKuliah_mahasiswa['ips']);
				
			} 
		}
		
		
		if ($aktif==1)  {
			$IPK=($nilaiMutu_sebelumnya+$nilaiSMT) / ($sks_sebelumnya+$sksSMT);
			$sks_total=$sks_sebelumnya+$sksSMT;
			echo "Nilai : ".$nilaiSMT."<br>";
			echo "SKS Semester : ".$sksSMT."<br>";
			echo "SKS Sebelumnya : ".$sks_sebelumnya."<br>";
			echo "SKS Total : ".$sks_total."<br>";
			
			
			/* UNTUK UPDATE */
			/*
			$keyUpdate=array('id_smt'=> $key,'id_reg_pd'=>$id_reg_pd);
			$data_kuliah_mahasiswa=array('ips'=>$IPS,'sks_smt'=>$sksSMT,'sks_total'=>$sks_total);
					
			$records_kuliah_mahasiswa[]=array('key'=>$keyUpdate,'data'=>$data_kuliah_mahasiswa);
			*/
			
			/* UNTUK TAMBAH */
			
			$data_kuliah_mahasiswa=array('id_smt'=> $key,
										 'id_reg_pd'=>$id_reg_pd,
										 'ips'=>$IPS,
										 'sks_smt'=>$sksSMT,
										 'ipk'=>$IPK,
										 'sks_total'=>$sks_total,
										 'id_stat_mhs'=>"A",
										 'biaya_smt'=>2400000
										);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
			
			
	    } else {
			$data_kuliah_mahasiswa=array('id_smt'=> $key,
										 'id_reg_pd'=>$id_reg_pd,
										 'ips'=>0,
										 'sks_smt'=>0,
										 'ipk'=>0,
										 'sks_total'=>0,
										 'id_stat_mhs'=>"N",
										 'biaya_smt'=>2400000
										);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;

			
		}
		
	}

	echo "<pre>";
	print_r($records_kuliah_mahasiswa);
	echo "</pre>";

	$insert_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
	//$insert_kuliah_mahasiswa=$proxy->UpdateRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
	
	/*
	echo "<pre>";
	print_r($insert_kuliah_mahasiswa);
	echo "</pre>";
	*/
			  
   	foreach ($insert_kuliah_mahasiswa['result'] as $itemData) {
		 $error = $itemData['error_code'];
		   
		 if ($error=="0") {
		   
			$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Kuliah Mahasiswa";
	    	$hasil['data']=$itemData;
	    	echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/sukses_log_kuliah_mahasiswa.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);
			 
		 } else {
			$pesan= $itemData['error_desc'];
			$hasil['berhasil']=0;
			$hasil['pesan']="Gagal Kuliah Mahasiswa";
	    	$hasil['data']=$itemData;
		    	
			echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/gagal_log_kuliah_mahasiswa.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);
			
		 }
		 
	} 
	
	echo "<h3>Halaman akan merefresh setelah 1 detik</h3>";
	//exit();
	$id+=1;
	 
	exit("<script>
	  	setTimeout(function() {
			window.location='http://sync.wsia.udb.ac.id/sopingi/kuliah_mahasiswa/tambah/".$key."/".$id."?sopingiwtwg';
		},1000);
	</script>");
	
} else if ($aksi=="tambah2018") {
	
	$proxy = proxy();
	$token = token();
	
	$mulai_smt='20181'; //ANGKATAN

	$records_kuliah_mahasiswa=array();
	
	$result=$proxy->GetRecordSet($token,"mahasiswa_pt.raw","mulai_smt like '$mulai_smt' and ((id_jns_keluar='') IS NOT FALSE)","nipd",1,$id);

	$mahasiswa_pt=$result['result'];
	
	
	/*
	echo "Mahasiswa PT <br><pre>";
		print_r($mahasiswa_pt);
	echo "</pre>";
	 exit();
	 */
	
		
	$jMhs = count($mahasiswa_pt);
	if ($jMhs==0) {
		exit('SELESAI '.$mulai_smt);
	}
	
	foreach ($mahasiswa_pt as $itemMahasiswa_pt) {
		$id_reg_pd=$itemMahasiswa_pt['id_reg_pd'];
		
		$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',200,0);
		$nilai=$a_nilai['result'];
		
		/*
		echo "Nilai<br><pre>";
		print_r($nilai);
		echo "</pre>";
		*/
		
		$j_nilai=count($nilai);
		
		$nilaiSMT=0;
		$sksSMT=0;
		
		//HITUNG IPS = Nilai.s / SKS.s
		$aktif=0;
		foreach ($nilai as $itemNilai) {
			$id_kls=$itemNilai['id_kls'];
			$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls'");
			$kelas_kuliah=$a_kelas_kuliah['result'];
			
			if ($kelas_kuliah['id_smt']=="$key") {
				$aktif=1;
				$nilaiSMT+=$itemNilai['nilai_indeks'] * $kelas_kuliah['sks_mk']; //nilai mutu Semester
				$sksSMT+=$kelas_kuliah['sks_mk']; // SKS Semester
				
				
				//echo "Kelas Kuliah<br><pre>";
				//print_r($kelas_kuliah);
				//echo "</pre>";
				
			}
			
		}
		
		if ($aktif=="1") {
			$IPS=$nilaiSMT/$sksSMT;
		}
		
		//HITUNG SKS TOTAL = SKS.S+SKS.T
		$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa.raw","id_reg_pd = '$id_reg_pd'","id_smt",100,0 );
		$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
		
		/*
		echo "Kuliah Mhs<br><pre>";
		print_r($a_kuliah_mahasiswa);
		echo "</pre>";
		*/
		
		$sks_sebelumnya=0;
		$nilaiMutu_sebelumnya=0;
		
		foreach ($kuliah_mahasiswa as $itemKuliah_mahasiswa) {
			if ($itemKuliah_mahasiswa['id_smt']<$key and $itemKuliah_mahasiswa['id_stat_mhs']=="A") {
				//echo "SKS Sebelum == ".$itemKuliah_mahasiswa['sks_smt']."<br>";
				$sks_sebelumnya+=$itemKuliah_mahasiswa['sks_smt'];
				$nilaiMutu_sebelumnya+= ($itemKuliah_mahasiswa['sks_smt'] * $itemKuliah_mahasiswa['ips']);
				
			} 
		}
		
		
		if ($aktif==1)  {
			$IPK=($nilaiMutu_sebelumnya+$nilaiSMT) / ($sks_sebelumnya+$sksSMT);
			$sks_total=$sks_sebelumnya+$sksSMT;
			echo "Nilai : ".$nilaiSMT."<br>";
			echo "SKS Semester : ".$sksSMT."<br>";
			echo "SKS Sebelumnya : ".$sks_sebelumnya."<br>";
			echo "SKS Total : ".$sks_total."<br>";
			
			
			/* UNTUK UPDATE */
			/*
			$keyUpdate=array('id_smt'=> $key,'id_reg_pd'=>$id_reg_pd);
			$data_kuliah_mahasiswa=array('ips'=>$IPS,'sks_smt'=>$sksSMT,'sks_total'=>$sks_total);
					
			$records_kuliah_mahasiswa[]=array('key'=>$keyUpdate,'data'=>$data_kuliah_mahasiswa);
			*/
			
			/* UNTUK TAMBAH */
			
			$data_kuliah_mahasiswa=array('id_smt'=> $key,
										 'id_reg_pd'=>$id_reg_pd,
										 'ips'=>$IPS,
										 'sks_smt'=>$sksSMT,
										 'ipk'=>$IPK,
										 'sks_total'=>$sks_total,
										 'id_stat_mhs'=>"A",
										 'biaya_smt'=>2400000
										);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
			
			
	    } else {
			$data_kuliah_mahasiswa=array('id_smt'=> $key,
										 'id_reg_pd'=>$id_reg_pd,
										 'ips'=>0,
										 'sks_smt'=>0,
										 'ipk'=>0,
										 'sks_total'=>0,
										 'id_stat_mhs'=>"N",
										 'biaya_smt'=>2400000
										);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;

			
		}
		
	}

	echo "<pre>";
	print_r($records_kuliah_mahasiswa);
	echo "</pre>";

	$insert_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
	//$insert_kuliah_mahasiswa=$proxy->UpdateRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
	
	/*
	echo "<pre>";
	print_r($insert_kuliah_mahasiswa);
	echo "</pre>";
	*/
			  
   	foreach ($insert_kuliah_mahasiswa['result'] as $itemData) {
		 $error = $itemData['error_code'];
		   
		 if ($error=="0") {
		   
			$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Kuliah Mahasiswa";
	    	$hasil['data']=$itemData;
	    	echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/sukses_log_kuliah_mahasiswa.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);
			 
		 } else {
			$pesan= $itemData['error_desc'];
			$hasil['berhasil']=0;
			$hasil['pesan']="Gagal Kuliah Mahasiswa";
	    	$hasil['data']=$itemData;
		    	
			echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/gagal_log_kuliah_mahasiswa.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);
			
		 }
		 
	} 
	
	echo "<h3>Halaman akan merefresh setelah 1 detik</h3>";
	//exit();
	$id+=1;
	 
	exit("<script>
	  	setTimeout(function() {
			window.location='http://sync.wsia.udb.ac.id/sopingi/kuliah_mahasiswa/tambah2018/".$key."/".$id."?sopingiwtwg';
		},1000);
	</script>");
	
} else if ($aksi=="tambah2017") {
	
	$proxy = proxy();
	$token = token();
	
	$mulai_smt='20171'; //ANGKATAN

	$records_kuliah_mahasiswa=array();
	
	$result=$proxy->GetRecordSet($token,"mahasiswa_pt.raw","mulai_smt like '$mulai_smt' and ((id_jns_keluar='') IS NOT FALSE)","nipd",1,$id);

	$mahasiswa_pt=$result['result'];
	
	
	/*
	echo "Mahasiswa PT <br><pre>";
		print_r($mahasiswa_pt);
	echo "</pre>";
	 exit();
	 */
	
		
	$jMhs = count($mahasiswa_pt);
	if ($jMhs==0) {
		exit('SELESAI '.$mulai_smt);
	}
	
	foreach ($mahasiswa_pt as $itemMahasiswa_pt) {
		$id_reg_pd=$itemMahasiswa_pt['id_reg_pd'];
		
		$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',200,0);
		$nilai=$a_nilai['result'];
		
		/*
		echo "Nilai<br><pre>";
		print_r($nilai);
		echo "</pre>";
		*/
		
		$j_nilai=count($nilai);
		
		$nilaiSMT=0;
		$sksSMT=0;
		
		//HITUNG IPS = Nilai.s / SKS.s
		$aktif=0;
		foreach ($nilai as $itemNilai) {
			$id_kls=$itemNilai['id_kls'];
			$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls'");
			$kelas_kuliah=$a_kelas_kuliah['result'];
			
			if ($kelas_kuliah['id_smt']=="$key") {
				$aktif=1;
				$nilaiSMT+=$itemNilai['nilai_indeks'] * $kelas_kuliah['sks_mk']; //nilai mutu Semester
				$sksSMT+=$kelas_kuliah['sks_mk']; // SKS Semester
				
				
				//echo "Kelas Kuliah<br><pre>";
				//print_r($kelas_kuliah);
				//echo "</pre>";
				
			}
			
		}
		
		if ($aktif=="1") {
			$IPS=$nilaiSMT/$sksSMT;
		}
		
		//HITUNG SKS TOTAL = SKS.S+SKS.T
		$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa.raw","id_reg_pd = '$id_reg_pd'","id_smt",100,0 );
		$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
		
		/*
		echo "Kuliah Mhs<br><pre>";
		print_r($a_kuliah_mahasiswa);
		echo "</pre>";
		*/
		
		$sks_sebelumnya=0;
		$nilaiMutu_sebelumnya=0;
		
		foreach ($kuliah_mahasiswa as $itemKuliah_mahasiswa) {
			if ($itemKuliah_mahasiswa['id_smt']<$key and $itemKuliah_mahasiswa['id_stat_mhs']=="A") {
				//echo "SKS Sebelum == ".$itemKuliah_mahasiswa['sks_smt']."<br>";
				$sks_sebelumnya+=$itemKuliah_mahasiswa['sks_smt'];
				$nilaiMutu_sebelumnya+= ($itemKuliah_mahasiswa['sks_smt'] * $itemKuliah_mahasiswa['ips']);
				
			} 
		}
		
		
		if ($aktif==1)  {
			$IPK=($nilaiMutu_sebelumnya+$nilaiSMT) / ($sks_sebelumnya+$sksSMT);
			$sks_total=$sks_sebelumnya+$sksSMT;
			echo "Nilai : ".$nilaiSMT."<br>";
			echo "SKS Semester : ".$sksSMT."<br>";
			echo "SKS Sebelumnya : ".$sks_sebelumnya."<br>";
			echo "SKS Total : ".$sks_total."<br>";
			
			
			/* UNTUK UPDATE */
			/*
			$keyUpdate=array('id_smt'=> $key,'id_reg_pd'=>$id_reg_pd);
			$data_kuliah_mahasiswa=array('ips'=>$IPS,'sks_smt'=>$sksSMT,'sks_total'=>$sks_total);
					
			$records_kuliah_mahasiswa[]=array('key'=>$keyUpdate,'data'=>$data_kuliah_mahasiswa);
			*/
			
			/* UNTUK TAMBAH */
			
			$data_kuliah_mahasiswa=array('id_smt'=> $key,
										 'id_reg_pd'=>$id_reg_pd,
										 'ips'=>$IPS,
										 'sks_smt'=>$sksSMT,
										 'ipk'=>$IPK,
										 'sks_total'=>$sks_total,
										 'id_stat_mhs'=>"A",
										 'biaya_smt'=>2400000
										);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
			
			
	    } else {
			$data_kuliah_mahasiswa=array('id_smt'=> $key,
										 'id_reg_pd'=>$id_reg_pd,
										 'ips'=>0,
										 'sks_smt'=>0,
										 'ipk'=>0,
										 'sks_total'=>0,
										 'id_stat_mhs'=>"N",
										 'biaya_smt'=>2400000
										);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;

			
		}
		
	}

	echo "<pre>";
	print_r($records_kuliah_mahasiswa);
	echo "</pre>";

	$insert_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
	//$insert_kuliah_mahasiswa=$proxy->UpdateRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
	
	/*
	echo "<pre>";
	print_r($insert_kuliah_mahasiswa);
	echo "</pre>";
	*/
			  
   	foreach ($insert_kuliah_mahasiswa['result'] as $itemData) {
		 $error = $itemData['error_code'];
		   
		 if ($error=="0") {
		   
			$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Kuliah Mahasiswa";
	    	$hasil['data']=$itemData;
	    	echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/sukses_log_kuliah_mahasiswa.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);
			 
		 } else {
			$pesan= $itemData['error_desc'];
			$hasil['berhasil']=0;
			$hasil['pesan']="Gagal Kuliah Mahasiswa";
	    	$hasil['data']=$itemData;
		    	
			echo "<pre>";
			print_r($hasil);
			echo "</pre>";
			
			$file = 'log/gagal_log_kuliah_mahasiswa.txt';
			// Open the file to get existing content
			$current = file_get_contents($file);
			// Append a new person to the file
			$current .= json_encode($hasil)."\n\n";
			// Write the contents back to the file
			file_put_contents($file, $current);
			
		 }
		 
	} 
	
	echo "<h3>Halaman akan merefresh setelah 1 detik</h3>";
	//exit();
	$id+=1;
	 
	exit("<script>
	  	setTimeout(function() {
			window.location='http://sync.wsia.udb.ac.id/sopingi/kuliah_mahasiswa/tambah2017/".$key."/".$id."?sopingiwtwg';
		},1000);
	</script>");
	
} else if ($aksi=="tambahLama") {
	
	$proxy = proxy();
	$token = token();
	
	$perintah = "select nim,nama from mahasiswa where no_pend in (select no_pend from krs_susulan)";
	  	  
	$perintah .= "  LIMIT 1 ";
	$perintah .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	  
	try {
	  	
		    $db 	= koneksi_wsia();
		    
		    $qry 	= $db->query($perintah); 
			$data	= $qry->fetchAll(PDO::FETCH_OBJ);
			
			if (!count($data)) {
				exit("Data Habis");
			}
		    
		    foreach ($data as $item) {
		    	
				$result=$proxy->GetRecord($token,"mahasiswa_pt","trim(nipd) like '%".$item->nim."%'");

				$mahasiswa_pt=$result['result'];
				
				
				echo "Mahasiswa<br><pre>";
					//print_r($mahasiswa_pt);
				echo "</pre>";
				
				if (isset($mahasiswa_pt['id_reg_pd'])) {
	
					$id_reg_pd=$mahasiswa_pt['id_reg_pd'];
					
					$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',200,0);
					$nilai=$a_nilai['result'];
					
					/*
					echo "Nilai<br><pre>";
					print_r($nilai);
					echo "</pre>";
					*/
					
					$j_nilai=count($nilai);
					
					$nilaiSMT=0;
					$sksSMT=0;
					
					//HITUNG IPS = Nilai.s / SKS.s
					$aktif=0;
					foreach ($nilai as $itemNilai) {
						$id_kls=$itemNilai['id_kls'];
						$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls'");
						$kelas_kuliah=$a_kelas_kuliah['result'];
						
						if ($kelas_kuliah['id_smt']=="$key") {
							$aktif=1;
							$nilaiSMT+=$itemNilai['nilai_indeks'] * $kelas_kuliah['sks_mk']; //nilai mutu Semester
							$sksSMT+=$kelas_kuliah['sks_mk']; // SKS Semester
							
							/*
							echo "Kelas Kuliah<br><pre>";
							print_r($kelas_kuliah);
							echo "</pre>";
							*/
						}
						
					}
					
					if ($aktif=="1") {
						$IPS=$nilaiSMT/$sksSMT;
					}
					
					//HITUNG SKS TOTAL = SKS.S+SKS.T
					$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa.raw","id_reg_pd = '$id_reg_pd'","id_smt",100,0 );
					$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
					
					/*
					echo "Kuliah Mhs<br><pre>";
					print_r($a_kuliah_mahasiswa);
					echo "</pre>";
					*/
					
					$sks_sebelumnya=0;
					$nilaiMutu_sebelumnya=0;
					$update_records_kuliah_mahasiswa_aktif=array();
					foreach ($kuliah_mahasiswa as $itemKuliah_mahasiswa) {
						if ($itemKuliah_mahasiswa['id_smt']<$key and $itemKuliah_mahasiswa['id_stat_mhs']=="A") {
							//echo "SKS Sebelum == ".$itemKuliah_mahasiswa['sks_smt']."<br>";
							$sks_sebelumnya+=$itemKuliah_mahasiswa['sks_smt'];
							$nilaiMutu_sebelumnya+= ($itemKuliah_mahasiswa['sks_smt'] * $itemKuliah_mahasiswa['ips']);
							
						} 
					}
					
					
					if ($aktif==1)  {
						$IPK=($nilaiMutu_sebelumnya+$nilaiSMT) / ($sks_sebelumnya+$sksSMT);
						$sks_total=$sks_sebelumnya+$sksSMT;
						echo "NIM : ".$item->nim."<br>";
						echo "Nilai : ".$nilaiSMT."<br>";
						echo "SKS Semester : ".$sksSMT."<br>";
						echo "SKS Sebelumnya : ".$sks_sebelumnya."<br>";
						echo "SKS Total : ".$sks_total."<br>";
						
						
						/* UNTUK UPDATE */
						$update_key_aktif=array('id_smt'=>$key,'id_reg_pd'=>$id_reg_pd);
						$update_data_kuliah_mahasiswa_aktif=array('ips'=>$IPS,'sks_smt'=>$sksSMT,'ipk'=>$IPK,'sks_total'=>$sks_total,'id_stat_mhs'=>"A");
								
						$update_records_kuliah_mahasiswa_aktif[]=array('key'=>$update_key_aktif,'data'=>$update_data_kuliah_mahasiswa_aktif);
						
						
						$data_kuliah_mahasiswa=array('id_smt'=> $key,
													 'id_reg_pd'=>$id_reg_pd,
													 'ips'=>$IPS,
													 'sks_smt'=>$sksSMT,
													 'ipk'=>$IPK,
													 'sks_total'=>$sks_total,
													 'id_stat_mhs'=>"A",
													 'biaya_smt'=>2400000
													);
						$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
						
				    } else {
						$data_kuliah_mahasiswa=array('id_smt'=> $key,
													 'id_reg_pd'=>$id_reg_pd,
													 'ips'=>0,
													 'sks_smt'=>0,
													 'ipk'=>0,
													 'sks_total'=>0,
													 'id_stat_mhs'=>"N",
													 'biaya_smt'=>2400000
													);
						$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
					}


				$insert_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	
				
				/*
				echo "<pre>";
				print_r($insert_kuliah_mahasiswa);
				echo "</pre>";
				*/
						  
			   	foreach ($insert_kuliah_mahasiswa['result'] as $itemData) {
					 $error = $itemData['error_code'];
					   
					 if ($error=="0") {
					   
						$hasil['berhasil']=1;
				    	$hasil['pesan']="Berhasil Kuliah Mahasiswa";
				    	$hasil['data']=$itemData;
				    	echo "<pre>";
						print_r($hasil);
						echo "</pre>";
						
						$file = 'log/sukses_log_kuliah_mahasiswa_lama.txt';
						// Open the file to get existing content
						$current = file_get_contents($file);
						// Append a new person to the file
						$current .= json_encode($hasil)."\n\n";
						// Write the contents back to the file
						file_put_contents($file, $current);
						 
					 } else {
						$pesan= $itemData['error_desc'];
						$hasil['berhasil']=0;
						$hasil['pesan']="Gagal Kuliah Mahasiswa";
				    	$hasil['data']=$itemData;
					    	
						echo "<pre>";
						print_r($hasil);
						echo "</pre>";
						
						$file = 'log/gagal_log_kuliah_mahasiswa.txt';
						// Open the file to get existing content
						$current = file_get_contents($file);
						// Append a new person to the file
						$current .= json_encode($hasil)."\n\n";
						// Write the contents back to the file
						file_put_contents($file, $current);
						
						//update
						
						if($aktif==1) {
							$update_kuliah_mahasiswa=$proxy->UpdateRecordSet($token,"kuliah_mahasiswa",json_encode($update_records_kuliah_mahasiswa_aktif));	
				
							echo "<pre>";
							print_r($update_records_kuliah_mahasiswa_aktif);
							print_r($update_kuliah_mahasiswa);
							echo "</pre>";
							
						}
						
					 }
					 
				}
			
		}
		
	
		}	
			
	echo "<h3>Halaman akan merefresh setelah 1 detik</h3>";
	$id+=1;
	 
	exit("<script>
	  	setTimeout(function() {
			window.location='http://sync.wsia.udb.ac.id/sopingi/kuliah_mahasiswa/tambahLama/".$key."/".$id."?sopingiwtwg';
		},1000);
	</script>");
	
	} catch (PDOException $salah) {
		exit("Hal=".$id."<br>".json_encode($salah->getMessage() ));
	}
	
} else if ($aksi=="rekap_kuliah_mahasiswa") {
	
	$proxy = proxy();
	$token = token();
	$a_kuliah_mahasiswa=$proxy->GetRecordSet($token,"kuliah_mahasiswa","p.id_smt like '%$key%'","id_stat_mhs",100,$id );
	$kuliah_mahasiswa=$a_kuliah_mahasiswa['result'];
	
	$jml = count($kuliah_mahasiswa);
	if ($jml>0) {
		
		$dataKuliahMahasiswa=array();
		foreach ($kuliah_mahasiswa as $item_kuliah_mahasiswa) {
			//print_r($item_kuliah_mahasiswa);
			$id_reg_pd = $item_kuliah_mahasiswa['id_reg_pd'];
			$a_mahasiswa_pt=$proxy->GetRecord($token,"mahasiswa_pt","id_reg_pd = '$id_reg_pd'");
			$mahasiswa_pt=$a_mahasiswa_pt['result'];
			//print_r($mahasiswa_pt);
			$id_pd = $mahasiswa_pt['id_pd'];
			$a_mahasiswa=$proxy->GetRecord($token,"mahasiswa","id_pd = '$id_pd'");
			$mahasiswa=$a_mahasiswa['result'];
			//print_r($mahasiswa);
			$dataKuliahMahasiswa[]=array_merge($mahasiswa,$mahasiswa_pt,$item_kuliah_mahasiswa);
		}
		
		//buat file
		$array =$dataKuliahMahasiswa;
		$f = fopen('dataKuliahMahasiswa_'.$key.'_'.$id.'.csv', 'w');
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
		
		echo "<h3>Sudah sampai offset-".$id.". Halaman akan merefresh setelah 2 detik</h3>";
		$id+=100;
		 
		exit("<script>
		  	setTimeout(function() {
				window.location='http://localhost/stmikdb_feeder/sopingi/kuliah_mahasiswa/rekap_kuliah_mahasiswa/".$key."/".$id."';
			},2000);
		</script>");
		
	} else {
		echo "selesai pada data offset-".$id;
	}
	
}  else if ($aksi=="tambahkosong") {
	
	$proxy = proxy();
	$token = token();
	
	$qryWsMahasiswa="SELECT
	wsia_nilai_view.id_nilai,
	wsia_nilai_view.vid_reg_pd,
	wsia_nilai_view.id_smt,
	wsia_mahasiswa_pt.id_reg_pd
	FROM
	wsia_nilai_view
	INNER JOIN wsia_mahasiswa_pt ON wsia_nilai_view.vid_reg_pd = wsia_mahasiswa_pt.xid_reg_pd
	where id_smt='20201' and left(vid_reg_pd,4)<>'2020' group by vid_reg_pd order by vid_reg_pd asc limit 50 OFFSET $id";

	$db 	= koneksi_wsia();
	$exeWsMahasiswa=$db->query($qryWsMahasiswa);
	$data	= $exeWsMahasiswa->fetchAll(PDO::FETCH_OBJ);
	$jml = count($data);

	if ($data) {

		$records_kuliah_mahasiswa=[];

		$update = array();

		foreach($data as $row){

			$data_kuliah_mahasiswa=array('id_smt'=> "20201",
										'id_reg_pd'=>$row->id_reg_pd,
										'ips'=>0,
										'sks_smt'=>0,
										'ipk'=>0,
										'sks_total'=>0,
										'id_stat_mhs'=>"A",
										'biaya_smt'=>2400000
									);
			$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
		}

		//echo "<pre>";
		//print_r($records_kuliah_mahasiswa);
		//echo "</pre>";

		$insert_kuliah_mahasiswa=$proxy->InsertRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	

			
		foreach ($insert_kuliah_mahasiswa['result'] as $itemData) {
			$error = $itemData['error_code'];
			
			if ($error=="0") {
			
				$hasil['berhasil']=1;
				$hasil['pesan']="Berhasil Kuliah Mahasiswa";
				$hasil['data']=$itemData;
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log/sukses_log_kuliah_kosong_mahasiswa.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			} else {
				$pesan= $itemData['error_desc'];
				$hasil['berhasil']=0;
				$hasil['pesan']="Gagal Kuliah Mahasiswa";
				$hasil['data']=$itemData;
					
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log/gagal_log_kuliah_kosong_mahasiswa.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			}
			
		} 

		$db=null;
		if ($jml>=50) {
			echo "<h3>Halaman akan merefresh setelah 1 detik</h3>";
			//exit();
			$id+=50;
			
			exit("<script>
				setTimeout(function() {
					window.location='http://sync.wsia.udb.ac.id/sopingi/kuliah_mahasiswa/tambahkosong/".$key."/".$id."?sopingiwtwg';
				},1000);
			</script>");
		} else {
			exit("Habis pada Hal=".$id." Jumlah Terakhir=".$jml);
		}
			
    } else {
		exit("Habis pada Hal=".$id." Jumlah Terakhir=".$jml);
	}
		
	
	
	
} else if ($aksi=="ubahkosong") {
	
	$proxy = proxy();
	$token = token();
	
	$qryWsMahasiswa="SELECT
	wsia_nilai_view.id_nilai,
	wsia_nilai_view.vid_reg_pd,
	wsia_nilai_view.id_smt,
	wsia_mahasiswa_pt.id_reg_pd
	FROM
	wsia_nilai_view
	INNER JOIN wsia_mahasiswa_pt ON wsia_nilai_view.vid_reg_pd = wsia_mahasiswa_pt.xid_reg_pd
	where id_smt='20201' and left(vid_reg_pd,4)<>'2020' group by vid_reg_pd order by vid_reg_pd asc limit 100 OFFSET $id";

	$db 	= koneksi_wsia();
	$exeWsMahasiswa=$db->query($qryWsMahasiswa);
	$data	= $exeWsMahasiswa->fetchAll(PDO::FETCH_OBJ);
	$jml = count($data);

	if ($data) {

		$records_kuliah_mahasiswa=[];

		$update = array();

		foreach($data as $row){

			
			$key_kuliah_mahasiswa=array('id_smt'=>  $key,'id_reg_pd'=>$row->id_reg_pd);
			$data_kuliah_mahasiswa=array('sks_smt'=>12,'sks_total'=>12);
					
			$records_kuliah_mahasiswa[]=array('key'=>$key_kuliah_mahasiswa,'data'=>$data_kuliah_mahasiswa);
			
		
		}

		//echo "<pre>";
		//print_r($records_kuliah_mahasiswa);
		//echo "</pre>";

		$update_kuliah_mahasiswa=$proxy->UpdateRecordSet($token,"kuliah_mahasiswa",json_encode($records_kuliah_mahasiswa));	

			
		foreach ($update_kuliah_mahasiswa['result'] as $itemData) {
			$error = $itemData['error_code'];
			
			if ($error=="0") {
			
				$hasil['berhasil']=1;
				$hasil['pesan']="Berhasil Ubah Kuliah Mahasiswa";
				$hasil['data']=$itemData;
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log/sukses_log_ubah_kuliah_kosong_mahasiswa.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			} else {
				$pesan= $itemData['error_desc'];
				$hasil['berhasil']=0;
				$hasil['pesan']="Gagal Kuliah Mahasiswa";
				$hasil['data']=$itemData;
					
				echo "<pre>";
				print_r($hasil);
				echo "</pre>";
				
				$file = 'log/gagal_log_ubah_kuliah_kosong_mahasiswa.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				// Append a new person to the file
				$current .= json_encode($hasil)."\n\n";
				// Write the contents back to the file
				file_put_contents($file, $current);
				
			}
			
		} 

		$db=null;
		if ($jml>=100) {
			echo "<h3>Halaman akan merefresh setelah 1 detik</h3>";
			//exit();
			$id+=100;
			
			exit("<script>
				setTimeout(function() {
					window.location='http://sync.wsia.udb.ac.id/sopingi/kuliah_mahasiswa/ubahkosong/".$key."/".$id."?sopingiwtwg';
				},1000);
			</script>");
		} else {
			exit("Habis pada Hal=".$id." Jumlah Terakhir=".$jml);
		}
			
    } else {
		exit("Habis pada Hal=".$id." Jumlah Terakhir=".$jml);
	}
		
	
	
	
} 