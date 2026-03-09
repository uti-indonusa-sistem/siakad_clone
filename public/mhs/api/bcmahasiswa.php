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
	$id_agama	= clean($data->id_agama);
	$no_kk			= clean($data->no_kk);
	$nik			= clean($data->nik);
	//$negara		= clean($data->negara);
	$negara		= "ID";
	$jln			= clean($data->jln);
	$nm_dsn		= clean($data->nm_dsn);
	$rt			= clean($data->rt);
	$rw			= clean($data->rw);
	$ds_kel		= clean($data->ds_kel);
	$kode_pos	= clean($data->kode_pos);
	$id_wil		= clean($data->id_wil);
	$id_jns_tinggal	= clean($data->id_jns_tinggal);
	$telepon_rumah= clean($data->telepon_rumah);
	$telepon_seluler= clean($data->telepon_seluler);
	$email		= clean($data->email);
	$a_terima_kps	= clean($data->a_terima_kps);
	$no_kps		= $data->no_kps;
	$nm_ayah	= clean($data->nm_ayah);
	$tgl_lahir_ayah	= clean($data->tgl_lahir_ayah);
	 		$tgl	= substr($tgl_lahir_ayah,0,2);
			$bln	= substr($tgl_lahir_ayah,3,2);
			 $thn	= substr($tgl_lahir_ayah,6,4);
	$vtgl_lahir_ayah= $thn."/".$bln."/".$tgl;

	$id_jenjang_pendidikan_ayah	= clean($data->id_jenjang_pendidikan_ayah);
	$id_pekerjaan_ayah			= clean($data->id_pekerjaan_ayah);
	$id_penghasilan_ayah		= clean($data->id_penghasilan_ayah);
	$nm_ibu_kandung				= clean($data->nm_ibu_kandung);
	$tgl_lahir_ibu					= clean($data->tgl_lahir_ibu);
	 		   $tgl	= substr($tgl_lahir_ibu,0,2);
			   $bln	= substr($tgl_lahir_ibu,3,2);
			   $thn	= substr($tgl_lahir_ibu,6,4);
	$vtgl_lahir_ibu	= $thn."/".$bln."/".$tgl;

	$id_jenjang_pendidikan_ibu	= clean($data->id_jenjang_pendidikan_ibu);
	$id_pekerjaan_ibu			= clean($data->id_pekerjaan_ibu);
	$id_penghasilan_ibu			= clean($data->id_penghasilan_ibu);
	$nm_wali					= clean($data->nm_wali);
	$tgl_lahir_wali				= clean($data->tgl_lahir_wali);
	 		   $tgl	= substr($tgl_lahir_wali,0,2);
			   $bln	= substr($tgl_lahir_wali,3,2);
			   $thn	= substr($tgl_lahir_wali,6,4);
	$vtgl_lahir_wali	= $thn."/".$bln."/".$tgl;

	$id_jenjang_pendidikan_wali	= clean($data->id_jenjang_pendidikan_wali);
	$id_pekerjaan_wali			= clean($data->id_pekerjaan_wali);
	$id_penghasilan_wali		= clean($data->id_penghasilan_wali);

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
		$db 	= koneksi();
		$eksekusiMhs 	= $db->query($qryKKmhs);  
		$dataKKmhs	= $eksekusiMhs->fetch(PDO::FETCH_OBJ);
		$id_kk=$dataKKmhs->id_kk;
		
		$eksekusiAyah 	= $db->query($qryKKayah);  
		$dataKKayah	= $eksekusiAyah->fetch(PDO::FETCH_OBJ);
		$id_kk_ayah=$dataKKayah->id_kk;
		
		$eksekusiIbu	= $db->query($qryKKibu);  
		$dataKKibu	= $eksekusiIbu->fetch(PDO::FETCH_OBJ);
		$id_kk_ibu=$dataKKibu->id_kk;
		
		$updated_at = date("Y-m-d H:i:s");

		$qryMhs = "update wsia_mahasiswa set nm_pd='$nm_pd',jk='$jk',nik='$nik',tmpt_lahir='$tmpt_lahir',tgl_lahir='$tgl_lahir',id_agama='$id_agama',id_kk='$id_kk',jln='$jln',rt='$rt',rw='$rw',nm_dsn='$nm_dsn',ds_kel='$ds_kel',id_wil='$id_wil',kode_pos='$kode_pos',id_jns_tinggal='$id_jns_tinggal',telepon_rumah='$telepon_rumah',telepon_seluler='$telepon_seluler',email='$email',a_terima_kps='$a_terima_kps',no_kps='$no_kps',nm_ayah='$nm_ayah',tgl_lahir_ayah='$tgl_lahir_ayah',id_jenjang_pendidikan_ayah='$id_jenjang_pendidikan_ayah',id_pekerjaan_ayah='$id_pekerjaan_ayah',id_penghasilan_ayah='$id_penghasilan_ayah',id_kebutuhan_khusus_ayah='$id_kk_ayah',nm_ibu_kandung='$nm_ibu_kandung',tgl_lahir_ibu='$tgl_lahir_ibu',id_jenjang_pendidikan_ibu='$id_jenjang_pendidikan_ibu',id_penghasilan_ibu='$id_penghasilan_ibu',id_pekerjaan_ibu='$id_pekerjaan_ibu',id_kebutuhan_khusus_ibu='$id_kk_ibu',nm_wali='$nm_wali',tgl_lahir_wali='$tgl_lahir_wali',id_jenjang_pendidikan_wali='$id_jenjang_pendidikan_wali',id_pekerjaan_wali='$id_pekerjaan_wali',id_penghasilan_wali='$id_penghasilan_wali', updated_at='$updated_at', no_kk='$no_kk' where xid_pd='$xid_pd'";
		
		$eksekusiMhs	= $db->query($qryMhs);  
		
		$hasil['berhasil']=1;
    	 	 $hasil['pesan']="Berhasil Ubah Biodata";
		 echo json_encode($hasil);
		
		$db		= null;
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 	 $hasil['pesan']="Gagal Ubah Biodata";
		 echo json_encode($hasil);
	  }
	  
} else if ($aksi=="ubahAkun") {
	$user  		= $_SESSION['nipd'];
	$pass 		= sha1(md5(clean($data->pass)).$user);
	$passBaru 	= sha1(md5(clean($data->passBaru)).$user);
	
	$sql = "update wsia_mahasiswa_pt set pass ='$passBaru' where nipd='$user' and pass='$pass'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
	    if($eksekusi->rowCount()>0) {
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Ubah Password";
	    } else {
		$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Ubah. Password lama tidak sesuai";
	    }
	    echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
}  else if ($aksi=="foto") {
	$file = $_FILES['upload'];
	$fileTypes = array('png','jpg','gif','PNG','JPG','GIF');
	$fileParts = pathinfo($file["name"]);
	if (in_array($fileParts['extension'],$fileTypes)) {
		$destination = realpath('./foto');
		$filename = $destination."/".md5($_SESSION['nipd']).".jpg";
		$hasil = kompresGbr($file["tmp_name"],$filename,250,75);
		if ($hasil) {
			echo json_encode(array('status'=>'server'));
		} else {
			echo json_encode(array('status'=>'error'));
		}
	} else {
		echo json_encode(array('status'=>'error'));
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
