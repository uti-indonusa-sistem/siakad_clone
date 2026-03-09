<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $perintah = "select xid_ptk,id_ptk,nm_ptk,nidn,jk,wsia_agama.id_agama,nm_agama,tmpt_lahir,tgl_lahir,concat(gelar_depan,' ',nm_ptk,', ',gelar_belakang) as nm_ptk_gelar,gelar_depan, gelar_belakang from wsia_dosen,wsia_agama where wsia_dosen.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') and wsia_agama.id_agama=wsia_dosen.id_agama order by nm_ptk";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="pilih") {
	
	  $ta=substr($_SESSION['ta'],0,4);
	  //$perintah = "select xid_ptk,nidn,nm_ptk from wsia_dosen where id_ptk not in(select id_ptk from wsia_dosen_pt where id_thn_ajaran='$ta' and wsia_dosen_pt.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."') )";
	  $perintah = "select xid_ptk,nidn,nm_ptk from wsia_dosen where wsia_dosen.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."')";
	  $perintah .= isset($_GET['filter']['value'])?" and nm_ptk like '%".$_GET['filter']['value']."%'":"";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $pilih=array();
		    foreach ($data as $itemData) {
		    		$itemData->id=$itemData->xid_ptk;
				$itemData->value=$itemData->nidn." - ".$itemData->nm_ptk;
				array_push($pilih,array('id'=>$itemData->xid_ptk,'value'=>$itemData->nidn." - ".$itemData->nm_ptk));
		    }
		    
		    echo json_encode($pilih);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$nm_ptk=clean($data->nm_ptk);
	$nidn=$data->nidn;
	$jk=$data->jk;
	$id_agama=$data->id_agama;
	$tmpt_lahir=clean($data->tmpt_lahir);
	$tgl_lahir=$data->tgl_lahir;
	$gelar_depan=clean($data->gelar_depan);
	$gelar_belakang=clean($data->gelar_belakang);
	$pass 	= sha1(md5(clean($data->passBaru)).$nidn);
	
	$db 	= koneksi();
    	$qry 	= $db->query("select * from wsia_satuan_pendidikan where npsn='".NPSN."' "); 
    	$data		= $qry->fetch(PDO::FETCH_OBJ);
    	$db		= null;
	$id_sp=$data->id_sp;
	$qrydosen = "insert into wsia_dosen (xid_ptk,nm_ptk,nidn,jk,id_agama,tmpt_lahir,tgl_lahir,gelar_depan,gelar_belakang,id_sp,pass) values('$nidn','$nm_ptk','$nidn','$jk','$id_agama','$tmpt_lahir','$tgl_lahir','$gelar_depan','$gelar_belakang','$id_sp','$pass')";
	try {
	    	$db 		= koneksi();
	    	$eksekusi 	= $db->query($qrydosen);  
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
	$xid_ptk=$data->xid_ptk;
	$nm_ptk=$data->nm_ptk;
	$nidn=$data->nidn;
	$jk=$data->jk;
	$id_agama=$data->id_agama;
	$tmpt_lahir=$data->tmpt_lahir;
	$tgl_lahir=$data->tgl_lahir;
	$gelar_depan=$data->gelar_depan;
	$gelar_belakang=$data->gelar_belakang;

	if (clean($data->passBaru)!="") {
		$pass 	= sha1(md5(clean($data->passBaru)).$nidn);
		$ubahPass = ", pass='$pass' ";
	} else {
		$ubahPass = "";
	}
	
	$qrydosen = "update wsia_dosen set nm_ptk='$nm_ptk',nidn='$nidn',jk='$jk',id_agama='$id_agama',tmpt_lahir='$tmpt_lahir',tgl_lahir='$tgl_lahir',gelar_depan='$gelar_depan',gelar_belakang='$gelar_belakang' ".$ubahPass." where xid_ptk='$xid_ptk'";
	try {
	    	$db 		= koneksi();
	    	$eksekusi 	= $db->query($qrydosen);  
	    	$db = null;
    		$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil Ubah";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="hapus") {
	$xid_ptk	=$data->xid_ptk;
	$sql = "delete from wsia_dosen where xid_ptk='$xid_ptk'";
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

