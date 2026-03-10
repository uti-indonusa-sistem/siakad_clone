<?php
error_reporting(0);

if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

if ($aksi=="tampil") {

		$exp = explode("_", $id);
		$id_smt=$exp[0];
		$kelas=$exp[1];
	  	$xid_ptk	= $_SESSION['xid_ptk'];
	  	$perintah = "select * from siakad_pa_aktifitas where id_smt='$id_smt' and id_ptk='$xid_ptk' and kelas='$kelas' order by tanggal asc ";

	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;

		    foreach ($data as $item) {
		    	
		    	$item->tanggal_id = format_tanggal($item->tanggal);
		    	
		    }
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }

} else if ($aksi=="tambah") {

	  $xid_ptk	= $_SESSION['xid_ptk'];
	  $nm_ptk = $_SESSION['nm_ptk'];
	  $id_smt = $data->id_smt;
	  $tanggal = $data->tanggal;
	  $mhs_aktif = $data->mhs_aktif;
	  $mhs_nonaktif = $data->mhs_nonaktif;
	  $mhs_cuti = $data->mhs_cuti;
	  $mhs_keluar = $data->mhs_keluar;
	  $kondisi_mahasiswa = $data->kondisi_mahasiswa;
	  $penanganan_mahasiswa = $data->penanganan_mahasiswa;
	  $kesimpulan = $data->kesimpulan;
	  $kelas = $data->kelas;

	  $perintah = "insert into siakad_pa_aktifitas (id_ptk, nama_dosen, id_smt, kelas, tanggal, mhs_aktif, mhs_nonaktif, mhs_cuti, mhs_keluar, kondisi_mahasiswa, penanganan_mahasiswa, kesimpulan) values (?,?,?,?,?,?,?,?,?,?,?,?)";

	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute([$xid_ptk, $nm_ptk, $id_smt, $kelas, $tanggal, $mhs_aktif, $mhs_nonaktif, $mhs_cuti, $mhs_keluar, $kondisi_mahasiswa, $penanganan_mahasiswa, $kesimpulan]);
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil simpan";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal simpan: " . $salah->getMessage();
		    echo json_encode($hasil);
	  }

} else if ($aksi=="ubah") {

	  $id = $data->id;
	  $tanggal = $data->tanggal;
	  $mhs_aktif = $data->mhs_aktif;
	  $mhs_nonaktif = $data->mhs_nonaktif;
	  $mhs_cuti = $data->mhs_cuti;
	  $mhs_keluar = $data->mhs_keluar;
	  $kondisi_mahasiswa = $data->kondisi_mahasiswa;
	  $penanganan_mahasiswa = $data->penanganan_mahasiswa;
	  $kesimpulan = $data->kesimpulan;

	  $perintah = "update siakad_pa_aktifitas set tanggal=?, mhs_aktif=?, mhs_nonaktif=?, mhs_cuti=?, mhs_keluar=?, kondisi_mahasiswa=?, penanganan_mahasiswa=?, kesimpulan=? where id=?";
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute([$tanggal, $mhs_aktif, $mhs_nonaktif, $mhs_cuti, $mhs_keluar, $kondisi_mahasiswa, $penanganan_mahasiswa, $kesimpulan, $id]);
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil simpan";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal simpan: " . $salah->getMessage();
		    echo json_encode($hasil);
	  }

} else if ($aksi=="hapus") {

	  $id = $data->id;

	  $perintah = "delete from siakad_pa_aktifitas where id='$id'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil hapus";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal hapus";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="aktifitas_mahasiswa") {

	  $exp = explode("_", $id);
	  $id_smt=$exp[0];
	  $kelas=$exp[1];
	  $xid_ptk	= $_SESSION['xid_ptk'];
	  $perintah_mhs = "select * from wsia_mahasiswa, wsia_mahasiswa_pt where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and pa='$xid_ptk' and kelas='$kelas' ";

	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah_mhs); 
		    $qry->execute();
		    $data_mhs = $qry->fetchAll(PDO::FETCH_OBJ);
		    

		    $mhs_aktif=0;
		    $mhs_nonaktif=0;
		    $mhs_cuti=0;
		    $mhs_keluar=0;

		    foreach ($data_mhs as $item_mhs) {
		    	$xid_reg_pd = $item_mhs->xid_reg_pd;
		    	$perintah_aktifitas = "select * from wsia_kuliah_mahasiswa where xid_reg_pd='$xid_reg_pd' and id_smt='$id_smt'";
		    	$qry 	= $db->prepare($perintah_aktifitas); 
			    $qry->execute();
			    $data_aktifitas = $qry->fetch(PDO::FETCH_OBJ);
			    if ($data_aktifitas) {
				    if ($data_aktifitas->id_stat_mhs=="A") {
				    	$mhs_aktif++;
				    } else if ($data_aktifitas->id_stat_mhs=="N") {
				    	$mhs_nonaktif++;
				    } else if ($data_aktifitas->id_stat_mhs=="C") {
				    	$mhs_cuti++;
				    } else if ($data_aktifitas->id_stat_mhs=="K") {
				    	$mhs_keluar++;
				    } else if ($data_aktifitas->id_stat_mhs=="L") {
				    	$mhs_keluar++;
				    } 
			    }
		    }

		    $db		= null;

		    $data = array(
		    	"aksi"=>"tambah",
		    	"id_smt"=>$id_smt,
				"kelas"=>$kelas,
		    	"mhs_aktif"=>$mhs_aktif,
			    "mhs_nonaktif"=>$mhs_nonaktif,
			    "mhs_cuti"=>$mhs_cuti,
			    "mhs_keluar"=>$mhs_keluar
		    );

		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }

} 
