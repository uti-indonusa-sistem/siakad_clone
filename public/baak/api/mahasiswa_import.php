<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="import") {

	try {

		$db 		= koneksi();
		$dbSpmb 	= koneksi_spmb();
		$dbSikeu 	= koneksi_sikeu();

		

		$qrySP 		= "select * from wsia_satuan_pendidikan where npsn='".NPSN."' ";
		$eksekusiSP	= $db->query($qrySP);  
		$dataSP		= $eksekusiSP->fetch(PDO::FETCH_OBJ);
		$id_sp 		= $dataSP->id_sp;
		
		$sqlSpmb = "select * FROM sipenmaru_daftar,sipenmaru_angkatan where left(sipenmaru_daftar.no_pend,4)=sipenmaru_angkatan.tahun and sipenmaru_angkatan.aktif='1' and validasi_pembayaran='2' order by right(no_pend,5) desc";
	    $eksekusiSpmb 	= $dbSpmb->query($sqlSpmb);  
	    $hasilSpmb 	= $eksekusiSpmb->fetchAll(PDO::FETCH_OBJ);
	    
	    $error=0;
	    $error_ket="";
	    $baru=0;
	    $sudah_ada=0;

	    if (count($hasilSpmb)==0) {
	    	$hasil['berhasil']=1;
			$hasil['pesan']="Belum ada data daftar ulang pada periode SPMB yang aktif";
			echo json_encode($hasil);

			$db=null;
			$dbSikeu =null;
			$dbSpmb =null;  
			exit();
	    }

	    foreach ($hasilSpmb as $item) {
	    	
	    	$no_pend 		= $item->no_pend;
	    	$sqlSikeu 		= "select * FROM mahasiswa where no_pend='$no_pend'";
		    $eksekusiSikeu 	= $dbSikeu->query($sqlSikeu);  
		    $hasilSikeu 	= $eksekusiSikeu->fetch(PDO::FETCH_OBJ);
		    
	    	
		    //INSERT DATA
			$no_pend	= clean($item->no_pend);
			$nm_pd		= clean($item->nama);
			$tmpt_lahir	= clean($item->tempat_lahir);
			$vtgl_lahir	= clean($item->tgl_lahir);
			$jk 		= clean($item->jk);
			$id_agama	= clean($item->agama);
			$nik		= clean($item->no_identitas);
			$negara		= "ID";
			$jln		= "";
			$nm_dsn		= "";
			$rt			= "";
			$rw			= "";
			$ds_kel		= "";
			$kode_pos	= clean($item->kode_pos);
			$id_wil		= clean($item->wilayah);
			
			$telepon_rumah= clean($item->telp_ortu);
			$telepon_seluler= clean($item->telepon);
			$email		= clean($item->email);
			$a_terima_kps	= "";
			$no_kps		= "";
			$nm_ayah	= clean($item->nama_ortu);
			$vtgl_lahir_ayah= "";

			$id_jenjang_pendidikan_ayah	= "";
			$id_pekerjaan_ayah		= "";
			$id_penghasilan_ayah	= "";
			$nm_ibu_kandung			= "";
			$vtgl_lahir_ibu	= "";

			$id_jenjang_pendidikan_ibu	= "";
			$id_pekerjaan_ibu			= "";
			$id_penghasilan_ibu			= "";
			$nm_wali					= "";
			$vtgl_lahir_wali	= "";

			$id_jenjang_pendidikan_wali	= "";
			$id_pekerjaan_wali			= "";
			$id_penghasilan_wali		= "";		
			
			$id_kk="";
		
			$id_kk_ayah="";
		
			$id_kk_ibu="";
			
			$tgL_update = date("Y-m-d H:i:s");

			$qryMhs = "insert ignore into wsia_mahasiswa values ('$no_pend','','$nm_pd','$jk','0','$nik','$tmpt_lahir','$tgl_lahir','$id_agama','$id_kk','$id_sp','$jln','$rt','$rw','$nm_dsn','$ds_kel','$id_wil','$kode_pos','$id_jns_tinggal','0','$telepon_rumah','$telepon_seluler','$email','$a_terima_kps','$no_kps','A','$nm_ayah','$tgl_lahir_ayah','$id_jenjang_pendidikan_ayah','$id_pekerjaan_ayah','$id_penghasilan_ayah','$id_kk_ayah','$nm_ibu_kandung','$tgl_lahir_ibu','$id_jenjang_pendidikan_ibu','$id_penghasilan_ibu','$id_pekerjaan_ibu','$id_kk_ibu','$nm_wali','$tgl_lahir_wali','$id_jenjang_pendidikan_wali','$id_pekerjaan_wali','$id_penghasilan_wali','ID','$tgL_update',null,'') ";

			$cek_no_pend="";
			try {
			    
			    $eksekusi 	= $db->query($qryMhs);  
		    	
			    if ($eksekusi) {

		    		$kode_prodi = $item->progdi;
			    	$sqlSms 		= "select * FROM wsia_sms where kode_prodi='$kode_prodi'";
				    $eksekusiSms 	= $db->query($sqlSms);  
				    $hasilSms 	= $eksekusiSms->fetch(PDO::FETCH_OBJ);

		    		$nipd			= clean($hasilSikeu->nim);
		    		$tgl_masuk_sp	= date("Y-m-d");
		    		$id_sms			= clean($hasilSms->xid_sms);
		    		$mulai_smt		= substr($item->no_pend,0,4)."1";
		    		$id_jns_daftar	= clean($item->jenis_daftar);
		    		$kelas			= "";
		    		$pa				= "";
		    		$pass 			= sha1(md5(clean($no_pend)).$nipd);

					$qryMahasiswaPT="insert ignore into wsia_mahasiswa_pt (xid_reg_pd,id_pd,id_sms,nipd,tgl_masuk_sp,mulai_smt,id_jns_daftar,kelas,pass,pa) values('$no_pend','$no_pend','$id_sms','$nipd','$tgl_masuk_sp','$mulai_smt','$id_jns_daftar','$kelas','$pass','$pa') ";
			    		$db->query($qryMahasiswaPT);  
			    	
			    	$cek_no_pend.=$item->no_pend.",";
			    	$baru++;
				} else {
					$sudah_ada++;
				}
		    	
			} catch (PDOException $salah) {
				$error++;
		    	$error_ket.=$salah->getMessage()."<br>";
			}
		
		}	 

		$hasil['berhasil']=1;
		$hasil['pesan']="Baru: ".$baru."<br>Sudah ada: ".$sudah_ada."<br>Error: ".$error;
		$hasil['error'] = $error_ket;
		$hasil['cek_no_pend'] = $cek_no_pend;
		//$hasil['daftar_ulang'] = $hasilSpmb;
		echo json_encode($hasil);

		$db=null;
		$dbSikeu =null;
		$dbSpmb =null;  

  } catch (PDOException $salah) {
	 $hasil['berhasil']=0;
	 $hasil['pesan']="Gagal Import. Kesalahan:<br>".$salah->getMessage();
	 echo json_encode($hasil);
  }
	
	  
} 