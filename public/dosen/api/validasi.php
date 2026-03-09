<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="tampil") {
	  $xid_reg_pd=$_SESSION['xid_reg_pd'];
	  $perintah = "select * from wsia_mahasiswa_pt,wsia_mahasiswa,wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and where xid_reg_pd='$xid_reg_pd'";
	  try {
		    $db 	= koneksi();
		    $eksekusi 	= $db->query($perintah); 
		    $data		= $eksekusi->fetch(PDO::FETCH_OBJ);
		    
		    $data->prodi=substr($data->kode,4,2);
		    $data->jns_daftar=substr($data->kode,6,1);
		    
		    $kode_prodi=$data->kode_prodi;
		  	$qryProdi="select * from wsia_sms where kode_prodi='$kode_prodi'";
		    $eksekusiProdi 	= $db->query($qryProdi);  
			$dataProdi	= $eksekusiProdi->fetch(PDO::FETCH_OBJ);
			if ($eksekusiProdi->rowCount()>0) {
				$data->nm_prodi=$dataProdi->nm_lemb;
			} else {
				$data->nm_prodi="-";
			}
			$data->aksi="ubah";
			$data->prodi_lama=$data->prodi;
			$data->kode_kelas_lama=$data->kode_kelas;
		    $db		= null;
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
}

if ($aksi=="ubah") {
	  $no_pend=$_SESSION['no_pend'];
	  $nama=addslashes(strtoupper($data->nama));
	  $prodi=$data->prodi;
	  if ($prodi=="01") {
	  	$kode_prodi="57201";
	  } elseif ($prodi=="02") {
	  	$kode_prodi="57401";
	  } elseif ($prodi=="03") {
	  	$kode_prodi="55201";
	  } elseif ($prodi=="04") {
	  	$kode_prodi="56401";
	  }
	  $kode_kelas=$data->kode_kelas;
	  $jns_daftar=$data->jns_daftar;
	  $kode=substr($no_pend,0,4).$prodi.$jns_daftar;
	  
	  if ( ($data->kode_kelas!=$data->kode_kelas_lama) ||  ($data->prodi!=$data->prodi_lama)) {
	  	 try {
		 	$db 	= koneksi();
		    $qryKelas="select max(kelas_prodi) as kls_terakhir from mahasiswa where kode_kelas='$kode_kelas' and kode_prodi='$kode_prodi' and left(kode,4)='2016'";
		    $eksekusiKelas 	= $db->query($qryKelas);  
			$dataKelas	= $eksekusiKelas->fetch(PDO::FETCH_OBJ);
			$kelas_prodi_baru=$dataKelas->kls_terakhir;
			$db=null;
			 $perintah = "update mahasiswa set nama='$nama', kode_prodi='$kode_prodi', kode_kelas='$kode_kelas', kelas_prodi='$kelas_prodi_baru', kode='$kode', sudah_validasi='1' where no_pend='$no_pend'";
			
		 } catch (PDOException $salah) {
		   	echo json_encode($salah->getMessage() );
	  	 }
	  } else {
	  	 $perintah = "update mahasiswa set nama='$nama', kode_prodi='$kode_prodi', kode_kelas='$kode_kelas', kode='$kode', sudah_validasi='1' where no_pend='$no_pend'";
	  }
	  
	  
	  try {
		    $db 	= koneksi();
		    $eksekusi 	= $db->query($perintah); 
		    
		    $qryProdi="select * from wsia_sms where kode_prodi='$kode_prodi'";
		    $eksekusiProdi 	= $db->query($qryProdi);  
			$dataProdi	= $eksekusiProdi->fetch(PDO::FETCH_OBJ);
			$id_sms=$dataProdi->xid_sms;
			
			$qrySp="select * from wsia_satuan_pendidikan where npsn='".NPSN."'";
		    $eksekusiSp 	= $db->query($qrySp);  
			$dataSp	= $eksekusiSp->fetch(PDO::FETCH_OBJ);
			$id_sp=$dataSp->id_sp;
			
			if ($jns_daftar=="3") {
				$id_jns_daftar="2";
			} else {
				$id_jns_daftar="1";
			}
		    
		    $tgl_masuk_sp=date("Y-m-d");
		    $mulai_smt=substr($no_pend,0,4)."1";
		    $qryMhsPt = "insert ignore into wsia_mahasiswa_pt (xid_reg_pd,id_sms,id_pd,id_sp,id_jns_daftar,tgl_masuk_sp,a_pernah_tk,mulai_smt) values('$no_pend','$id_sms','$no_pend','$id_sp','$id_jns_daftar','$tgl_masuk_sp','1','$mulai_smt')";
		    $eksekusiMhsPt 	= $db->query($qryMhsPt); 
		    
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Validasi";
	    	$_SESSION['nama']=$nama;
	    	$_SESSION['kode']=$kode;
		    $_SESSION['kode_kelas']=$kode_kelas;
			echo json_encode($hasil);
			
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
}

