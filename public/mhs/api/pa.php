<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="jurnal") {

	  $xid_reg_pd=$_SESSION['xid_reg_pd'];
	  $xid_ptk	= $_SESSION['xid_ptk'];

	  $perintah = "select * from siakad_pa_jurnal where xid_ptk='$xid_ptk' and xid_reg_pd='$xid_reg_pd' ";

	  $perintah .= isset($_GET['filter']['tanggal'])?" and tanggal like '%".$_GET['filter']['tanggal']."%'":"";
	  $perintah .= isset($_GET['filter']['konten'])?" and konten like '%".$_GET['filter']['konten']."%'":"";
	  $perintah .= isset($_GET['filter']['oleh'])?" and oleh like '%".$_GET['filter']['oleh']."%'":"";
	  
	  $perintah.=" order by tanggal desc";
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }

} else if ($aksi=="tambah_jurnal") {

	  $xid_reg_pd=$_SESSION['xid_reg_pd'];
	  $xid_ptk	= $_SESSION['xid_ptk'];
	  $tanggal = date("Y-m-d H:i:s");
	  $konten = $data->konten;

	  $perintah = "insert into siakad_pa_jurnal values(null,'$xid_ptk','$xid_reg_pd','$tanggal','$konten','Mahasiswa')";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil tambah jurnal";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal tambah jurnal";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="ubah_jurnal") {

	  $id = $data->id;
	  $tanggal = $data->tanggal;
	  $konten = $data->konten;

	  $perintah = "update siakad_pa_jurnal set tanggal='$tanggal', konten='$konten' where id='$id' and oleh='Mahasiswa'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil ubah jurnal";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal ubah jurnal";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="hapus_jurnal") {

	  $id = $data->id;

	  $perintah = "delete from siakad_pa_jurnal where id='$id' and oleh='Mahasiswa'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil hapus jurnal";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal hapus jurnal. Mungkin ada pesan didalamnya atau tidak boleh dihapus";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="pesan") {

	  $id_jurnal=$id;
	 
	  $perintah = "select * from siakad_pa_chat where id_jurnal='$id_jurnal' order by waktu asc ";

	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;

		    foreach ($data as $item) {
		    	
		    	if ($item->pesan_pa!="") {
		    		$item->author="dosen";
		    		$item->text=$item->pesan_pa;
		    	} else {
		    		$item->author="mahasiswa";
		    		$item->text=$item->pesan_mhs;
		    	}
		    	$item->waktu = format_tanggal_waktu($item->waktu);
		    	
		    }
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }

} else if ($aksi=="kirim_pesan") {

	  $id_jurnal = $data->id_jurnal;
	  $pesan_mhs = $data->pesan;
	  $waktu = date("Y-m-d H:i:s");

	  $perintah = "insert into siakad_pa_chat values(null,'$id_jurnal','$waktu','','$pesan_mhs')";
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil kirim pesan";
	    	$hasil['author']="mahasiswa";
	    	$hasil['text']=$pesan_mhs;
	    	$hasil['waktu']=format_tanggal_waktu($waktu);
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal kirim pesan";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="hapus_pesan") {

	  $id = $data->id;

	  $perintah = "delete from siakad_pa_chat where id='$id' and pesan_pa=''";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
		    $hasil['id']=$id;
	    	$hasil['pesan']="Berhasil hapus pesan";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal hapus pesan";
		    echo json_encode($hasil);
	  }

} 