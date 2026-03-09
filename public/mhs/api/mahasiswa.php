<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="tampil") {
	  $xid_reg_pd=$_SESSION['xid_reg_pd'];
	  $perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' ";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		    $data	= $qry->fetch(PDO::FETCH_OBJ);
		    
		    if ($qry->rowCount()) {
		    	$data->aksi="simpan";
		    	$data->no_pend=$data->xid_pd;
		    	$data->vnm_ibu_kandung=$data->nm_ibu_kandung;
		    	
		    	$id_kk=$data->id_kk;
		    	$qryKKmhs = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk'";
		    	$eksekusiMhs = $db->query($qryKKmhs);
		    	$dataKKmhs	= $eksekusiMhs->fetch(PDO::FETCH_OBJ);
		    	$aKKmhs = get_object_vars($dataKKmhs);
		    	foreach ($aKKmhs as $key=> $nilai) {
		    		$keyMhs="mhs_".$key;
				$data->$keyMhs=$nilai;
			}
		    	
		    	$id_kk_ayah=$data->id_kebutuhan_khusus_ayah;
		    	$qryKKayah = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ayah'";
		    	$eksekusiAyah = $db->query($qryKKayah);
		    	$dataKKayah	= $eksekusiAyah->fetch(PDO::FETCH_OBJ);
		    	$aKKayah = get_object_vars($dataKKayah);
		    	foreach ($aKKayah as $key=> $nilai) {
		    		$keyAyah="ayah_".$key;
				$data->$keyAyah=$nilai;
			}
		    	
		    	$id_kk_ibu=$data->id_kebutuhan_khusus_ibu;
		    	$qryKKibu = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ibu'";
		    	$eksekusiIbu = $db->query($qryKKibu);
		    	$dataKKibu	= $eksekusiIbu->fetch(PDO::FETCH_OBJ);
		    	$aKKibu = get_object_vars($dataKKibu);
		    	foreach ($aKKibu as $key=> $nilai) {
		    		$keyIbu="ibu_".$key;
				$data->$keyIbu=$nilai;
			}
		    	
			echo json_encode($data);
			
		} else {
			$hasil['nm_pd']=$_SESSION['nm_pd'];
			$hasil['kewarganegaraan']="ID";
			$hasil['aksi']="simpan";
			echo json_encode($hasil);
		}
			
		$db		= null;
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="simpan") {

	$xid_pd	= clean($data->xid_pd);
	$no_pend	= clean($data->no_pend);
	$nm_pd		= clean($data->nm_pd);
	$tmpt_lahir	= clean($data->tmpt_lahir);
	$tgl_lahir		= clean($data->tgl_lahir);
	 		$tgl	= substr($tgl_lahir,0,2);
			$bln	= substr($tgl_lahir,3,2);
			$thn	= substr($tgl_lahir,6,4);
	$vtgl_lahir		= $thn."/".$bln."/".$tgl;

	$jk 			= clean($data->jk);
	
	// Handle numeric ID fields - keep as is, will be converted to NULL in query if empty
	$id_agama	= isset($data->id_agama) ? trim($data->id_agama) : '';
	$no_kk			= clean($data->no_kk);
	$nik			= clean($data->nik);
	//$negara		= clean($data->negara);
	$negara		= "ID";
	$jln			= clean($data->jln);
	$nm_dsn		= clean($data->nm_dsn);
	
	// Handle numeric fields - keep as is, will be converted to NULL in query if empty
	$rt			= isset($data->rt) ? trim($data->rt) : '';
	$rw			= isset($data->rw) ? trim($data->rw) : '';
	
	$ds_kel		= clean($data->ds_kel);
	$kode_pos	= clean($data->kode_pos);
	$id_wil		= clean($data->id_wil);
	
	// Handle numeric ID fields
	$id_jns_tinggal	= isset($data->id_jns_tinggal) ? trim($data->id_jns_tinggal) : '';
	
	$telepon_rumah= clean($data->telepon_rumah);
	$telepon_seluler= clean($data->telepon_seluler);
	$email		= clean($data->email);
	
	// Handle a_terima_kps - can be empty
	$a_terima_kps	= isset($data->a_terima_kps) ? trim($data->a_terima_kps) : '';
	$no_kps		= isset($data->no_kps) ? trim($data->no_kps) : '';
	$nm_ayah	= clean($data->nm_ayah);
	$tgl_lahir_ayah	= clean($data->tgl_lahir_ayah);
	 		$tgl	= substr($tgl_lahir_ayah,0,2);
			$bln	= substr($tgl_lahir_ayah,3,2);
			 $thn	= substr($tgl_lahir_ayah,6,4);
	$vtgl_lahir_ayah= $thn."/".$bln."/".$tgl;

	// Handle parent education/occupation/income IDs
	$id_jenjang_pendidikan_ayah	= isset($data->id_jenjang_pendidikan_ayah) ? trim($data->id_jenjang_pendidikan_ayah) : '';
	$id_pekerjaan_ayah	= isset($data->id_pekerjaan_ayah) ? trim($data->id_pekerjaan_ayah) : '';
	$id_penghasilan_ayah = isset($data->id_penghasilan_ayah) ? trim($data->id_penghasilan_ayah) : '';
	$nm_ibu_kandung				= clean($data->nm_ibu_kandung);
	$tgl_lahir_ibu					= clean($data->tgl_lahir_ibu);
	 		   $tgl	= substr($tgl_lahir_ibu,0,2);
			   $bln	= substr($tgl_lahir_ibu,3,2);
			   $thn	= substr($tgl_lahir_ibu,6,4);
	$vtgl_lahir_ibu	= $thn."/".$bln."/".$tgl;

	$id_jenjang_pendidikan_ibu	= isset($data->id_jenjang_pendidikan_ibu) ? trim($data->id_jenjang_pendidikan_ibu) : '';
	$id_pekerjaan_ibu = isset($data->id_pekerjaan_ibu) ? trim($data->id_pekerjaan_ibu) : '';
	$id_penghasilan_ibu = isset($data->id_penghasilan_ibu) ? trim($data->id_penghasilan_ibu) : '';
	$nm_wali					= clean($data->nm_wali);
	$tgl_lahir_wali				= clean($data->tgl_lahir_wali);
	 		   $tgl	= substr($tgl_lahir_wali,0,2);
			   $bln	= substr($tgl_lahir_wali,3,2);
			   $thn	= substr($tgl_lahir_wali,6,4);
	$vtgl_lahir_wali	= $thn."/".$bln."/".$tgl;

	$id_jenjang_pendidikan_wali	= isset($data->id_jenjang_pendidikan_wali) ? trim($data->id_jenjang_pendidikan_wali) : '';
	$id_pekerjaan_wali = isset($data->id_pekerjaan_wali) ? trim($data->id_pekerjaan_wali) : '';
	$id_penghasilan_wali = isset($data->id_penghasilan_wali) ? trim($data->id_penghasilan_wali) : '';

	$mhs_a_kk_a	= $data->mhs_a_kk_a;
	$mhs_a_kk_b	= $data->mhs_a_kk_b;
	$mhs_a_kk_c	= $data->mhs_a_kk_c;
	$mhs_a_kk_c1= $data->mhs_a_kk_c1;
	$mhs_a_kk_d	= $data->mhs_a_kk_d;
	$mhs_a_kk_d1= $data->mhs_a_kk_d1;
	$mhs_a_kk_e	= $data->mhs_a_kk_e;
	$mhs_a_kk_f	= $data->mhs_a_kk_f;
	$mhs_a_kk_h	= $data->mhs_a_kk_h;
	$mhs_a_kk_i	= $data->mhs_a_kk_i;
	$mhs_a_kk_j	= $data->mhs_a_kk_j;
	$mhs_a_kk_k	= $data->mhs_a_kk_k;
	$mhs_a_kk_n	= $data->mhs_a_kk_n;
	$mhs_a_kk_o	= $data->mhs_a_kk_o;
	$mhs_a_kk_p	= $data->mhs_a_kk_p;
	$mhs_a_kk_q	= $data->mhs_a_kk_q;

	$ayah_a_kk_a	= $data->ayah_a_kk_a;
	$ayah_a_kk_b	= $data->ayah_a_kk_b;
	$ayah_a_kk_c	= $data->ayah_a_kk_c;
	$ayah_a_kk_c1	= $data->ayah_a_kk_c1;
	$ayah_a_kk_d	= $data->ayah_a_kk_d;
	$ayah_a_kk_d1	= $data->ayah_a_kk_d1;
	$ayah_a_kk_e	= $data->ayah_a_kk_e;
	$ayah_a_kk_f	= $data->ayah_a_kk_f;
	$ayah_a_kk_h	= $data->ayah_a_kk_h;
	$ayah_a_kk_i	= $data->ayah_a_kk_i;
	$ayah_a_kk_j	= $data->ayah_a_kk_j;
	$ayah_a_kk_k	= $data->ayah_a_kk_k;
	$ayah_a_kk_n	= $data->ayah_a_kk_n;
	$ayah_a_kk_o	= $data->ayah_a_kk_o;
	$ayah_a_kk_p	= $data->ayah_a_kk_p;
	$ayah_a_kk_q	= $data->ayah_a_kk_q;

	$ibu_a_kk_a		= $data->ibu_a_kk_a;
	$ibu_a_kk_b		= $data->ibu_a_kk_b;
	$ibu_a_kk_c		= $data->ibu_a_kk_c;
	$ibu_a_kk_c1	= $data->ibu_a_kk_c1;
	$ibu_a_kk_d		= $data->ibu_a_kk_d;
	$ibu_a_kk_d1	= $data->ibu_a_kk_d1;
	$ibu_a_kk_e		= $data->ibu_a_kk_e;
	$ibu_a_kk_f		= $data->ibu_a_kk_f;
	$ibu_a_kk_h		= $data->ibu_a_kk_h;
	$ibu_a_kk_i		= $data->ibu_a_kk_i;
	$ibu_a_kk_j		= $data->ibu_a_kk_j;
	$ibu_a_kk_k		= $data->ibu_a_kk_k;
	$ibu_a_kk_n		= $data->ibu_a_kk_n;
	$ibu_a_kk_o		= $data->ibu_a_kk_o;
	$ibu_a_kk_p		= $data->ibu_a_kk_p;
	$ibu_a_kk_q		= $data->ibu_a_kk_q;
	
	$qryKKmhs = "select * from wsia_kebutuhan_khusus where a_kk_a='$mhs_a_kk_a' and a_kk_b='$mhs_a_kk_b' and a_kk_c='$mhs_a_kk_c' and a_kk_c1='$mhs_a_kk_c1' and a_kk_d='$mhs_a_kk_d' and a_kk_d1='$mhs_a_kk_d1' and a_kk_e='$mhs_a_kk_e' and a_kk_f='$mhs_a_kk_f' and a_kk_h='$mhs_a_kk_h' and a_kk_i='$mhs_a_kk_i' and a_kk_j='$mhs_a_kk_j' and a_kk_k='$mhs_a_kk_k' and a_kk_n='$mhs_a_kk_n' and a_kk_o='$mhs_a_kk_o' and a_kk_p='$mhs_a_kk_p' and a_kk_q='$mhs_a_kk_q'";
	
	$qryKKayah = "select * from wsia_kebutuhan_khusus where a_kk_a='$ayah_a_kk_a' and a_kk_b='$ayah_a_kk_b' and a_kk_c='$ayah_a_kk_c' and a_kk_c1='$ayah_a_kk_c1' and a_kk_d='$ayah_a_kk_d' and a_kk_d1='$ayah_a_kk_d1' and a_kk_e='$ayah_a_kk_e' and a_kk_f='$ayah_a_kk_f' and a_kk_h='$ayah_a_kk_h' and a_kk_i='$ayah_a_kk_i' and a_kk_j='$ayah_a_kk_j' and a_kk_k='$ayah_a_kk_k' and a_kk_n='$ayah_a_kk_n' and a_kk_o='$ayah_a_kk_o' and a_kk_p='$ayah_a_kk_p' and a_kk_q='$ayah_a_kk_q'";
	
	$qryKKibu = "select * from wsia_kebutuhan_khusus where a_kk_a='$ibu_a_kk_a' and a_kk_b='$ibu_a_kk_b' and a_kk_c='$ibu_a_kk_c' and a_kk_c1='$ibu_a_kk_c1' and a_kk_d='$ibu_a_kk_d' and a_kk_d1='$ibu_a_kk_d1' and a_kk_e='$ibu_a_kk_e' and a_kk_f='$ibu_a_kk_f' and a_kk_h='$ibu_a_kk_h' and a_kk_i='$ibu_a_kk_i' and a_kk_j='$ibu_a_kk_j' and a_kk_k='$ibu_a_kk_k' and a_kk_n='$ibu_a_kk_n' and a_kk_o='$ibu_a_kk_o' and a_kk_p='$ibu_a_kk_p' and a_kk_q='$ibu_a_kk_q'";
		
	
	try {
		$db = koneksi();
		
		// Validate required fields
		if (empty($xid_pd)) {
			throw new Exception('ID Mahasiswa tidak ditemukan. Silakan login ulang.');
		}
		
		if (empty($nm_pd)) {
			throw new Exception('Nama lengkap harus diisi');
		}
		
		if (empty($nik) || strlen($nik) != 16) {
			throw new Exception('NIK harus 16 digit');
		}
		
		if (empty($tgl_lahir)) {
			throw new Exception('Tanggal lahir harus diisi');
		}
		
		// Get kebutuhan khusus IDs
		$eksekusiMhs = $db->query($qryKKmhs);
		if (!$eksekusiMhs) {
			throw new Exception('Error query kebutuhan khusus mahasiswa');
		}
		$dataKKmhs = $eksekusiMhs->fetch(PDO::FETCH_OBJ);
		$id_kk = $dataKKmhs ? $dataKKmhs->id_kk : 0;
		
		$eksekusiAyah = $db->query($qryKKayah);
		if (!$eksekusiAyah) {
			throw new Exception('Error query kebutuhan khusus ayah');
		}
		$dataKKayah = $eksekusiAyah->fetch(PDO::FETCH_OBJ);
		$id_kk_ayah = $dataKKayah ? $dataKKayah->id_kk : 0;
		
		$eksekusiIbu = $db->query($qryKKibu);
		if (!$eksekusiIbu) {
			throw new Exception('Error query kebutuhan khusus ibu');
		}
		$dataKKibu = $eksekusiIbu->fetch(PDO::FETCH_OBJ);
		$id_kk_ibu = $dataKKibu ? $dataKKibu->id_kk : 0;
		
		$updated_at = date("Y-m-d H:i:s");

		// Function to safely convert to NULL or valid number
		function safeNumericConvert($value, $fieldName = '') {
			// If empty or '0', return NULL
			if (empty($value) || $value === '0' || $value === 0) {
				return 'NULL';
			}
			
			// Check if it's a valid number
			if (is_numeric($value)) {
				$numValue = intval($value);
				// Check range for typical DB integer types (TINYINT: 0-255, SMALLINT: 0-65535)
				if ($numValue >= 0 && $numValue <= 65535) {
					return "'" . $numValue . "'";
				} else {
					error_log("Value out of range for $fieldName: $numValue");
					return 'NULL';
				}
			}
			
			// If not numeric, return NULL
			error_log("Non-numeric value for $fieldName: $value");
			return 'NULL';
		}
		
		// Ensure numeric fields have proper values (NULL or valid number)
		$rt_converted = safeNumericConvert($rt, 'rt');
		$rw_converted = safeNumericConvert($rw, 'rw');
		$id_agama_converted = safeNumericConvert($id_agama, 'id_agama');
		$id_jns_tinggal_converted = safeNumericConvert($id_jns_tinggal, 'id_jns_tinggal');
		$id_jenjang_pendidikan_ayah_converted = safeNumericConvert($id_jenjang_pendidikan_ayah, 'id_jenjang_pendidikan_ayah');
		$id_pekerjaan_ayah_converted = safeNumericConvert($id_pekerjaan_ayah, 'id_pekerjaan_ayah');
		$id_penghasilan_ayah_converted = safeNumericConvert($id_penghasilan_ayah, 'id_penghasilan_ayah');
		$id_jenjang_pendidikan_ibu_converted = safeNumericConvert($id_jenjang_pendidikan_ibu, 'id_jenjang_pendidikan_ibu');
		$id_pekerjaan_ibu_converted = safeNumericConvert($id_pekerjaan_ibu, 'id_pekerjaan_ibu');
		$id_penghasilan_ibu_converted = safeNumericConvert($id_penghasilan_ibu, 'id_penghasilan_ibu');
		$id_jenjang_pendidikan_wali_converted = safeNumericConvert($id_jenjang_pendidikan_wali, 'id_jenjang_pendidikan_wali');
		$id_pekerjaan_wali_converted = safeNumericConvert($id_pekerjaan_wali, 'id_pekerjaan_wali');
		$id_penghasilan_wali_converted = safeNumericConvert($id_penghasilan_wali, 'id_penghasilan_wali');
		
		// Handle a_terima_kps (can be empty or 0/1)
		$a_terima_kps_converted = safeNumericConvert($a_terima_kps, 'a_terima_kps');
		$no_kps_converted = (empty($no_kps)) ? 'NULL' : "'" . $no_kps . "'";

		$qryMhs = "update wsia_mahasiswa set nm_pd='$nm_pd',jk='$jk',nik='$nik',tmpt_lahir='$tmpt_lahir',tgl_lahir='$tgl_lahir',id_agama=$id_agama_converted,id_kk='$id_kk',jln='$jln',rt=$rt_converted,rw=$rw_converted,nm_dsn='$nm_dsn',ds_kel='$ds_kel',id_wil='$id_wil',kode_pos='$kode_pos',id_jns_tinggal=$id_jns_tinggal_converted,telepon_rumah='$telepon_rumah',telepon_seluler='$telepon_seluler',email='$email',a_terima_kps=$a_terima_kps_converted,no_kps=$no_kps_converted,nm_ayah='$nm_ayah',tgl_lahir_ayah='$tgl_lahir_ayah',id_jenjang_pendidikan_ayah=$id_jenjang_pendidikan_ayah_converted,id_pekerjaan_ayah=$id_pekerjaan_ayah_converted,id_penghasilan_ayah=$id_penghasilan_ayah_converted,id_kebutuhan_khusus_ayah='$id_kk_ayah',nm_ibu_kandung='$nm_ibu_kandung',tgl_lahir_ibu='$tgl_lahir_ibu',id_jenjang_pendidikan_ibu=$id_jenjang_pendidikan_ibu_converted,id_penghasilan_ibu=$id_penghasilan_ibu_converted,id_pekerjaan_ibu=$id_pekerjaan_ibu_converted,id_kebutuhan_khusus_ibu='$id_kk_ibu',nm_wali='$nm_wali',tgl_lahir_wali='$tgl_lahir_wali',id_jenjang_pendidikan_wali=$id_jenjang_pendidikan_wali_converted,id_pekerjaan_wali=$id_pekerjaan_wali_converted,id_penghasilan_wali=$id_penghasilan_wali_converted, updated_at='$updated_at', no_kk='$no_kk' where xid_pd='$xid_pd'";
		
		error_log("Update query with NULL handling: " . substr($qryMhs, 0, 300) . "...");
		
		$eksekusiMhs = $db->query($qryMhs);
		
		if (!$eksekusiMhs) {
			$errorInfo = $db->errorInfo();
			error_log("Update biodata error: " . print_r($errorInfo, true));
			throw new Exception('Gagal mengeksekusi query update: ' . ($errorInfo[2] ?? 'Unknown error'));
		}
		
		if ($eksekusiMhs->rowCount() == 0) {
			error_log("Update biodata - No rows affected for xid_pd: {$xid_pd}");
			// Tidak throw error karena mungkin data tidak berubah
		}
		
		// Log successful update
		error_log("Biodata updated successfully for xid_pd: {$xid_pd}");
		
		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil ubah biodata";
		echo json_encode($hasil);
		
		$db = null;
		
	} catch (Exception $e) {
		error_log("Update biodata exception: " . $e->getMessage());
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal ubah biodata: " . $e->getMessage();
		 echo json_encode($hasil);
		
	  } catch (PDOException $salah) {
		error_log("Update biodata PDO error: " . $salah->getMessage());
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal ubah biodata. Error database: " . $salah->getMessage();
		 echo json_encode($hasil);
	  }
	  
} else if ($aksi=="ubahAkun") {
	try {
		// Validate input
		if (empty($data->pass) || empty($data->passBaru)) {
			throw new Exception('Password lama dan password baru harus diisi');
		}
		
		if (strlen($data->passBaru) < 6) {
			throw new Exception('Password baru minimal 6 karakter');
		}
		
		$user = $_SESSION['nipd'];
		$passwordLama = $data->pass;
		$passwordBaru = $data->passBaru;
		
		$db = koneksi();
		$db2 = koneksi_sso();
		
		// Get current password hash - SECURED with prepared statement
		$stmt = $db->prepare("SELECT pass FROM wsia_mahasiswa_pt WHERE nipd = :nipd LIMIT 1");
		$stmt->execute([':nipd' => $user]);
		$userData = $stmt->fetch(PDO::FETCH_OBJ);
		
		if (!$userData) {
			throw new Exception('Data mahasiswa tidak ditemukan');
		}
		
		$currentHash = $userData->pass;
		$isPasswordValid = false;
		
		// Debug log (akan dihapus setelah testing)
		error_log("Password Change Debug - NIM: {$user}");
		error_log("Current hash in DB: " . substr($currentHash, 0, 20) . "...");
		error_log("Hash length: " . strlen($currentHash));
		
		// Verify old password - support multiple formats
		
		// 1. Try new format first (Argon2id / Bcrypt)
		if (password_verify($passwordLama, $currentHash)) {
			$isPasswordValid = true;
			error_log("Password verified with modern format (Argon2id/Bcrypt)");
		} 
		// 2. Try legacy format: sha1(md5(pass) + nim)
		else {
			$legacyHash1 = sha1(md5($passwordLama) . $user);
			error_log("Trying legacy format 1: sha1(md5(pass) + nim)");
			
			if (hash_equals($currentHash, $legacyHash1)) {
				$isPasswordValid = true;
				error_log("Password verified with legacy format 1");
			}
		}
		
		// 3. Try alternative format: md5(pass)
		if (!$isPasswordValid) {
			$legacyHash2 = md5($passwordLama);
			error_log("Trying legacy format 2: md5(pass)");
			
			if (hash_equals($currentHash, $legacyHash2)) {
				$isPasswordValid = true;
				error_log("Password verified with legacy format 2");
			}
		}
		
		// 4. Try alternative format: sha1(pass)
		if (!$isPasswordValid) {
			$legacyHash3 = sha1($passwordLama);
			error_log("Trying legacy format 3: sha1(pass)");
			
			if (hash_equals($currentHash, $legacyHash3)) {
				$isPasswordValid = true;
				error_log("Password verified with legacy format 3");
			}
		}
		
		// 5. Try format: md5(sha1(pass))
		if (!$isPasswordValid) {
			$legacyHash4 = md5(sha1($passwordLama));
			error_log("Trying legacy format 4: md5(sha1(pass))");
			
			if (hash_equals($currentHash, $legacyHash4)) {
				$isPasswordValid = true;
				error_log("Password verified with legacy format 4");
			}
		}
		
		// 6. Try format: sha1(md5(pass)) - without nim
		if (!$isPasswordValid) {
			$legacyHash5 = sha1(md5($passwordLama));
			error_log("Trying legacy format 5: sha1(md5(pass))");
			
			if (hash_equals($currentHash, $legacyHash5)) {
				$isPasswordValid = true;
				error_log("Password verified with legacy format 5");
			}
		}
		
		if (!$isPasswordValid) {
			error_log("All password verification attempts failed");
			$hasil['berhasil'] = 0;
			$hasil['pesan'] = 'Password lama tidak sesuai. Pastikan Anda memasukkan password yang benar.';
			echo json_encode($hasil);
			return;
		}
		
		// Generate new password hash using modern Argon2id
		require_once __DIR__ . '/../../lib/security.php';
		$newPasswordHash = Security::hashPassword($passwordBaru);
		
		// Update password in main database - SECURED
		$updateStmt = $db->prepare("UPDATE wsia_mahasiswa_pt SET pass = :pass WHERE nipd = :nipd");
		$updateResult = $updateStmt->execute([
			':pass' => $newPasswordHash,
			':nipd' => $user
		]);
		
		if ($updateResult && $updateStmt->rowCount() > 0) {
			// Sync with SSO database
			try {
				$ssoHash = Security::hashPassword($passwordBaru);
				$updateSSO = $db2->prepare("UPDATE users SET password = ?, updated_at = ? WHERE username = ?");
				$updateSSO->execute([
					$ssoHash,
					date('Y-m-d H:i:s'),
					$user
				]);
				
				Security::logSecurityEvent("Password changed successfully for mahasiswa: {$user}", 'INFO');
			} catch (PDOException $e) {
				// Log SSO error but don't fail the main operation
				Security::logSecurityEvent("SSO password sync error for {$user}: " . $e->getMessage(), 'WARNING');
			}
			
			$hasil['berhasil'] = 1;
			$hasil['pesan'] = 'Berhasil ubah password. Silakan login kembali dengan password baru.';
	    } else {
			throw new Exception('Gagal mengubah password');
	    }
		
		$db = null;
		$db2 = null;
	    echo json_encode($hasil);
		
	} catch (Exception $e) {
		Security::logSecurityEvent("Password change error for mahasiswa: " . $e->getMessage(), 'ERROR');
		
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = 'Gagal ubah password: ' . $e->getMessage();
		echo json_encode($hasil);
	}
	
}  else if ($aksi=="foto") {
	$debugLog = [];
	$debugLog[] = "Request start for NIM " . ($_SESSION['nipd'] ?? 'unknown');
	error_log("DEBUG FOTO: Request start for NIM " . ($_SESSION['nipd'] ?? 'unknown'));
	try {
		// Validate file upload
		if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
			$debugLog[] = "Files array: " . print_r($_FILES, true);
			error_log("DEBUG FOTO: Files array: " . print_r($_FILES, true));
			throw new Exception('No file uploaded or upload error: ' . ($_FILES['upload']['error'] ?? 'unknown'));
		}
		
		$file = $_FILES['upload'];
		
		// Validate file type using Security helper
		require_once __DIR__ . '/../../lib/security.php';
		$validation = Security::validateFileUpload(
			$file,
			['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
			2097152 // 2MB max
		);
		
		if (!$validation['valid']) {
			throw new Exception($validation['error']);
		}
		
		// Additional check: verify actual file type (not just extension)
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $finfo->file($file['tmp_name']);
		$allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
		
		if (!in_array($mimeType, $allowedMimes)) {
			throw new Exception('Tipe file tidak valid. Hanya JPG, PNG, GIF yang diperbolehkan.');
		}
		
		// Get destination directory
		$destination = realpath(__DIR__ . '/../foto');
		$debugLog[] = "Destination resolved: " . ($destination ?: 'FALSE');
		error_log("DEBUG FOTO: Destination resolved: " . ($destination ?: 'FALSE'));
		
		if (!$destination) {
			// Directory doesn't exist, try to create
			$destination = __DIR__ . '/../foto';
			if (!file_exists($destination)) {
				if (!mkdir($destination, 0755, true)) {
					$debugLog[] = "Failed to create directory";
					throw new Exception('Gagal membuat direktori upload');
				}
				$debugLog[] = "Directory created: $destination";
			}
			$destination = realpath($destination);
		}
		
		// Check if directory is writable
		if (!is_writable($destination)) {
			// Try to fix permissions automatically
			$debugLog[] = "Attempting to fix permissions for $destination";
			@chmod($destination, 0755);
			
			if (!is_writable($destination)) {
				@chmod($destination, 0777);
			}

			if (!is_writable($destination)) {
				$debugLog[] = "Directory not writable: $destination";
				error_log("DEBUG FOTO: Directory not writable: $destination");
				throw new Exception('Direktori upload tidak dapat ditulis. Hubungi administrator.');
			}
			$debugLog[] = "Permissions fixed automatically";
		}
		
		// Generate filename
		$filename = $destination . "/" . md5($_SESSION['nipd']) . ".jpg";
		$debugLog[] = "Target filename: $filename";
		
		// Delete old file if exists (with @ to suppress warnings)
		if (file_exists($filename)) {
			@unlink($filename);
		}
		
		// Compress and save image
		$debugLog[] = "Compressing $file[tmp_name] to $filename";
		error_log("DEBUG FOTO: Compressing $file[tmp_name] to $filename");
		$hasil = kompresGbr($file["tmp_name"], $filename, 250, 75);
		
		if ($hasil) {
			// Log successful upload
			$debugLog[] = "Upload successful";
			error_log("Photo uploaded successfully for NIM: " . $_SESSION['nipd']);
			
			echo json_encode([
				'status' => 'server', // Webix expects 'server' for success
				'success' => true,
				'message' => 'Foto berhasil diupload',
				'filename' => md5($_SESSION['nipd']) . ".jpg",
				'timestamp' => time(), // For cache busting
				'debug' => $debugLog
			]);
		} else {
			$debugLog[] = "Compression failed";
            error_log("Compression failed for NIM: " . $_SESSION['nipd']);
			throw new Exception('Gagal mengkompress dan menyimpan gambar. Pastikan format gambar didukung.');
		}
		
	} catch (Exception $e) {
		$debugLog[] = "Exception: " . $e->getMessage();
		error_log("Photo upload error for NIM " . $_SESSION['nipd'] . ": " . $e->getMessage());
		
		echo json_encode([
			'status' => 'error',
			'message' => $e->getMessage(),
			'detail' => 'Pastikan folder foto dapat ditulis dan file yang diupload adalah gambar valid',
			'debug' => $debugLog
		]);
	}
} else if ($aksi=="kk") {
	$file = $_FILES['upload'];
	$fileTypes = array('pdf');
	$fileParts = pathinfo($file["name"]);
	if (in_array($fileParts['extension'],$fileTypes)) {
		$destination = realpath('./kk');
		$filename = $destination."/".md5($_SESSION['nipd']).".pdf";
		$hasil = move_uploaded_file($file["tmp_name"], $filename);
		if ($hasil) {
			echo json_encode(array('status'=>'server'));
		} else {
			echo json_encode(array('status'=>'error'));
		}
	} else {
		echo json_encode(array('status'=>'error'));
	}
} else if ($aksi=="cekkk") {
	$destination = realpath('./kk');
	$filename = $destination."/".md5($_SESSION['nipd']).".pdf";
	if (file_exists($filename)) {
	  	echo json_encode(
	  		array(
	  			'link'=>"<a href='sopingi/mahasiswa/tampilkk/".$key."/".rand()."' target='_blank'>Unduh KK</a>"
	  		)
	  	);
	} else {
		echo json_encode(array('link'=>"Belum Upload KK"));
	}
} else if ($aksi=="tampilkk") {
	$destination = realpath('./kk');
	$filename = $destination."/".md5($_SESSION['nipd']).".pdf";
	if (file_exists($filename)) {
	  	header('Content-Description: File Transfer');
	    header('Content-Type: application/octet-stream');
	    header('Content-Disposition: attachment; filename="KK_'.$_SESSION['nipd'].'.pdf"');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($filename));
	    readfile($filename);
	    exit;
	} else {
		echo "Maaf file KK tidak tersedia";
	}
} 
