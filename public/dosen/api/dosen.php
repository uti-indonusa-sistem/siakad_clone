<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

 if ($aksi=="pilih") {
	
	  $perintah = "select xid_ptk,nidn,nm_ptk from wsia_dosen where wsia_dosen.id_sp= (select id_sp from wsia_satuan_pendidikan where npsn='".NPSN."')";
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
	  
} else if ($aksi=="ubahAkun") {
	$user  		= $_SESSION['nidn'];
	$pass 		= sha1(md5(clean($data->pass)).$user);
	$passBaru 	= sha1(md5(clean($data->passBaru)).$user);
	
	$sql = "update wsia_dosen set pass ='$passBaru' where nidn='$user' and pass='$pass'";
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
		$filename = $destination."/".md5($_SESSION['nidn']).".jpg";
		$hasil = kompresGbr($file["tmp_name"],$filename,250,75);
		if ($hasil) {
			echo json_encode(array('status'=>'server'));
		} else {
			echo json_encode(array('status'=>'error'));
		}
	} else {
		echo json_encode(array('status'=>'error'));
	}
} 
