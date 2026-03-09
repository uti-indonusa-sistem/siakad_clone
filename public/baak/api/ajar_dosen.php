<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $ta=$_SESSION['ta'];
	  $id_kls=$id;
	  $perintah = "SELECT wsia_dosen_pt.xid_reg_ptk, xid_ajar, wsia_dosen.id_ptk AS vid_ptk, wsia_dosen_pt.id_reg_ptk AS vid_reg_ptk, nidn, nm_ptk, sks_subst_tot, jml_tm_renc, jml_tm_real, wsia_jenis_evaluasi.id_jns_eval AS vid_jns_eval, nm_jns_eval, hari, jam, ruang, kode_gabung 
	  FROM wsia_ajar_dosen 
	  INNER JOIN wsia_dosen_pt ON wsia_dosen_pt.xid_reg_ptk = wsia_ajar_dosen.id_reg_ptk 
	  INNER JOIN wsia_dosen ON wsia_dosen.xid_ptk = wsia_dosen_pt.id_ptk 
	  LEFT JOIN wsia_jenis_evaluasi ON wsia_jenis_evaluasi.id_jns_eval = wsia_ajar_dosen.id_jns_eval
	  WHERE wsia_ajar_dosen.id_kls = '$id_kls'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$xid_ajar=$data->xid_ajar;
	if (empty($xid_ajar)) { $xid_ajar = date("ymdHis").rand(100,999); }
	$xid_reg_ptk=$data->xid_reg_ptk;
	$xid_klsAjarDosen=$data->xid_klsAjarDosen;
	$sks_subst_tot=$data->sks_subst_tot;
	$jml_tm_renc=$data->jml_tm_renc;
	$jml_tm_real=$data->jml_tm_real;
	$hari=$data->hari;
	$jam=clean($data->jam);
	$ruang=clean($data->ruang);
	$kode_gabung=clean($data->kode_gabung);
	
	$qryAjarDosen = "insert into wsia_ajar_dosen (xid_ajar,id_reg_ptk,id_kls,sks_subst_tot,jml_tm_renc,jml_tm_real,id_jns_eval,hari,jam,ruang,kode_gabung) values('$xid_ajar','$xid_reg_ptk','$xid_klsAjarDosen','$sks_subst_tot','$jml_tm_renc','$jml_tm_real','1','$hari','$jam','$ruang','$kode_gabung')";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($qryAjarDosen);  
	    $db = null;
    	$hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil Simpan";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	 
} else if ($aksi=="ubah") {
	$xid_ajar=$data->xid_ajar;
	$xid_reg_ptk=$data->xid_reg_ptk;
	$xid_klsAjarDosen=$data->xid_klsAjarDosen;
	$sks_subst_tot=$data->sks_subst_tot;
	$jml_tm_renc=$data->jml_tm_renc;
	$jml_tm_real=$data->jml_tm_real;
	$hari=$data->hari;
	$jam=clean($data->jam);
	$ruang=clean($data->ruang);
	$kode_gabung=clean($data->kode_gabung);
	
	$qryAjarDosen = "update wsia_ajar_dosen set id_reg_ptk='$xid_reg_ptk',id_kls='$xid_klsAjarDosen',sks_subst_tot='$sks_subst_tot',jml_tm_renc='$jml_tm_renc',jml_tm_real='$jml_tm_real', hari='$hari',jam='$jam',ruang='$ruang', kode_gabung='$kode_gabung' where xid_ajar='$xid_ajar'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($qryAjarDosen);  
	    $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Ubah";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$xid_ajar=$data->xid_ajar;
	$sql = "delete from wsia_ajar_dosen where xid_ajar='$xid_ajar'";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil hapus";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
}
