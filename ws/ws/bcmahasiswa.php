<?php
$db 	= koneksi_wsia();
//$db 	= koneksi_wsia_off();

if ($aksi=="tambah") {

	$qryWsMahasiswa="select * from view_mahasiswa_tambah_2020 order by nik asc limit 50 OFFSET $id";
		$exeWsMahasiswa=$db->query($qryWsMahasiswa);
		$data	= $exeWsMahasiswa->fetchAll(PDO::FETCH_OBJ);
		if ($data) {
			foreach($data as $row){
				if (!ctype_digit($row->id_wil)) {
					$row->id_wil="999999";
				}
				if (strlen($row->kode_pos)<5) {
					$row->kode_pos="00000";
				}
				if ($row->tgl_lahir_ayah=="0000-00-00") {
					$row->tgl_lahir_ayah="1900-01-01";
				}
				if ($row->tgl_lahir_ibu=="0000-00-00") {
					$row->tgl_lahir_ibu="1900-01-01";
				}
				if ($row->tgl_lahir_wali=="0000-00-00") {
					$row->tgl_lahir_wali="1900-01-01";
				}
				if ($row->nik=="") {
					$row->nik="0";
				}
				if ($row->id_alat_transport=="0") {
					$row->id_alat_transport="99";
				}
				
				if ($row->id_penghasilan_ibu>16) {
					$row->id_penghasilan_ibu=0;
				}
				
				if ($row->id_penghasilan_ayah>16) {
					$row->id_penghasilan_ayah=0;
				}
				
				if ($row->id_penghasilan_wali>16) {
					$row->id_penghasilan_wali=0;
				}
				
				if (!ctype_digit($row->telepon_rumah)) {
					$row->telepon_rumah="";
				}
				
				$row->nik_ayah="";
				$row->nik_ibu="";
				$row->npwp="";
				$row->no_tel_rmh=str_replace(" ","",$row->telepon_rumah);
				$row->no_hp=str_replace(" ","",$row->telepon_seluler);
				$row->no_hp=str_replace("-","",$row->no_hp);
				$row->no_hp=str_replace("+62","0",$row->no_hp);
				$row->email=str_replace(" ","",$row->email);
				$row->email=str_replace(",",".",$row->email);

				if (strlen($row->no_tel_rmh)<9) {
					unset($row->no_tel_rmh);
				}

				if ($row->no_hp=="-") {
					unset($row->no_hp);
				}

				if ($row->email=="-") {
					unset($row->email);
				}
				
				unset($row->xid_pd);
				unset($row->id_pd);
				unset($row->telepon_rumah);
				unset($row->telepon_seluler);
				unset($row->id_sp);
				unset($row->stat_pd);
			}

			/*
			echo "<pre>";
			print_r($data);
			echo "</pre>";
			*/
			
			$proxy = proxy();
			$token = token();
			echo $token;
			$insert_mahasiswa=$proxy->InsertRecordSet($token,"mahasiswa",  json_encode($data) );
			
			/*
			echo "<pre>";
			print_r($insert_mahasiswa);
			echo "</pre>";
			*/

			$i=0;	
		    if (!isset($insert_mahasiswa['result'])) {
				exit("Error InsertRecordSet");
			}  

		    foreach ($insert_mahasiswa['result'] as $itemData) {
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
					
					$file = 'log/'.$key.'_mahasiswa_insert_error_'.$id.'.txt';
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
			 $id+=50;
			 
			exit("<script>
			  	setTimeout(function() {
						window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa/tambah/".$key."/".$id."?sopingiwtwg';
				}, 2000);
			</script>");
			
		} else {
			exit("Habis pada Hal=".$id);
		}
	

} else if ($aksi=="ubah") {

	$proxy = proxy();
	$token = token();
	echo $token."<hr>";

	//$qryWsMahasiswa="select * from view_mahasiswa_update where left(no_pend,4)<>'2020' and isnull(sync_at) order by nim asc limit 50 OFFSET $id";
	$qryWsMahasiswa="select * from view_mahasiswa_update where left(no_pend,4)<>'2020' and (isnull(sync_at) or updated_at>sync_at) order by nim asc limit 50 OFFSET $id";
		$exeWsMahasiswa=$db->query($qryWsMahasiswa);
		$data	= $exeWsMahasiswa->fetchAll(PDO::FETCH_OBJ);
		$jml = count($data);

		if ($data) {

			

			$update = array();

			foreach($data as $row){

				//cek id_pd_feeder
				
				//$dataMahasiswaFeeder=$proxy->GetRecord($token,"mahasiswa_pt.raw","trim(nipd) = '".trim($row->nim)."'");
				$dataMahasiswaFeeder=$proxy->GetRecord($token,"mahasiswa_pt.raw","id_pd = '".trim($row->id_pd_feeder)."'");

				if (!isset($dataMahasiswaFeeder['result'])) {
					exit("Error GetRecord pada ID=".$id." NIM=".$row->nim);
					break;
				} else if ( count($dataMahasiswaFeeder['result'])==0 ) {

					$file = 'log/'.$key.'_nim_tidak_ada_difeeder.txt';
					$current = file_get_contents($file);
					$current .= $row->nim."\n\n";
					// Write the contents back to the file
					file_put_contents($file, $current);

					//exit("Tidak Ada Data GetRecord pada ID=".$id." NIM=".$row->nim);
					//break;
				} else {
					$mahasiswaFeeder = $dataMahasiswaFeeder['result'];
					$id_pd = $mahasiswaFeeder['id_pd'];
					$xid_pd = $row->xid_pd;
					$sync_at = date("Y-m-d H:i:s");
					$updateMahasiswa="update wsia_mahasiswa set id_pd='$id_pd', sync_at='$sync_at' where xid_pd='$xid_pd'";
					$exeUpdateMahasiswa=$db->query($updateMahasiswa);
				
				

					if (!ctype_digit($row->id_wil)) {
						$row->id_wil="999999";
					}
					if (strlen($row->kode_pos)<5) {
						$row->kode_pos="00000";
					}
					
					if ($row->nik=="") {
						$row->nik="0";
					}

					
					
					$row->no_hp=str_replace(" ","",$row->telepon_seluler);
					$row->no_hp=str_replace("-","",$row->no_hp);
					$row->no_hp=str_replace("+62","0",$row->no_hp);

					$row->email=str_replace(" ","",$row->email);
					$row->email=str_replace(",",".",$row->email);

					$update[] = array(
						"key" => array(
							"id_pd" => $id_pd
						),
						"data" => array(
							//"nm_pd" => $row->nm_pd,
							"nik" => $row->nik,
							"jln" => $row->jln,
							"rt" => $row->rt,
							"rw" => $row->rw,
							"nm_dsn" => $row->nm_dsn,
							"ds_kel" => $row->ds_kel,
							"id_wil" => $row->id_wil,
							"kode_pos" => $row->kode_pos,
							"no_hp" => $row->telepon_seluler,
							"email" => $row->email,
						)
					);
				}

			}

			
			
			$update_mahasiswa=$proxy->UpdateRecordSet($token,"mahasiswa",  json_encode($update) );


			$i=0;	
		    if (!isset($update_mahasiswa['result'])) {
				exit("Update InsertRecordSet");
			}  

		    foreach ($update_mahasiswa['result'] as $itemData) {
			 	$error = $itemData['error_code'];
			   
				 if ($error=="0") {
				   
						$hasil['berhasil']=1;
					    $hasil['pesan']="Berhasil Update data Mahasiswa";
					    $hasil['data']=$update[$i];
					    echo "<pre>";
						print_r($hasil);
						echo "</pre>";
					 
				 } else {
					$pesan= $itemData['error_desc'];
					$hasil['berhasil']=0;
				    $hasil['pesan']=$itemData;
				    $hasil['data']=$update[$i];
				    	
					echo "<pre>";
					print_r($hasil);
					echo "</pre>";
					
					$file = 'log/'.$key.'_mahasiswa_update_error_'.$id.'.txt';
					// Open the file to get existing content
					$current = file_get_contents($file);
					// Append a new person to the file
					$current .= json_encode($hasil)."\n\n";
					// Write the contents back to the file
					file_put_contents($file, $current);
					
				 }
			 
			 	 $i++;
			}

			$db=null;

			if ($jml>=50) {
				echo "<h3>Halaman akan merefresh selama 3 detik</h3>";
				
				//$id+=50;
				 
				exit("<script>
				  	setTimeout(function() {
							window.location='http://sync.wsia.udb.ac.id/sopingi/mahasiswa/ubah/".$key."/".$id."';
					}, 3000);
				</script>");
			} else {
				exit("Habis pada Hal=".$id." Jumlah Terakhir=".$jml);
			}
			
		} else {
			exit("Habis pada Hal=".$id);
		}
	

} else if ($aksi=="tampils") {
	
	$proxy = proxy();
	$token = token();
	echo $token;
	$data=$proxy->GetRecordSet($token,"mahasiswa.raw","id_pd = '2416869e-9765-4173-af61-75ec03d4f109'","id_pd",100,$id);
	echo "<pre>";
	print_r($data);
	echo "</pre>";

	//buat file
	$array =$data['result'];
	$f = fopen('mahasiswa_lama_'.$id.'.csv', 'w');
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
	
} else if ($aksi=="tampil") {
	
	$proxy = proxy();
	$token = token();
	echo $token;
	$dataMahasiswaFeeder=$proxy->GetRecord($token,"mahasiswa_pt.raw","trim(nipd) = '180206004'");
	echo "<pre>";
	print_r(count($dataMahasiswaFeeder['result']));
	print_r($dataMahasiswaFeeder);
	echo "</pre>";
	
} else if ($aksi=="struktur") {
	
	$proxy = proxy();
	$token = token();
	echo $token;
	$data=$proxy->GetDictionary($token,"mahasiswa");
	echo "<pre>";
	print_r($data);
	echo "</pre>";
	
} else if ($aksi=="dump") {
	
	$proxy = proxy();
	$token = token();
	echo $token;
	$data=$proxy->GetRecordSet($token,"mahasiswa_pt.raw","mulai=20141","nipd",800,$id);
	echo "<pre>";
	print_r($data);
	echo "</pre>";
	
	$mahasiswa_pt=$result['result'];
	foreach ($mahasiswa_pt as $item) {
		
	}
	
} 

?>