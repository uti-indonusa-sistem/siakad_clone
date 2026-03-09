<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

 if ($aksi=="ubahAkun") {
 	
 	$pass 		= crypt(md5(clean($data->pass)),"$1$"."uPolinus");
	$apass		= explode("$",$pass);
	$vpass		= sha1($apass[3]);
	$epass		= md5($vpass);
 	
	$passBaru 	= crypt(md5(clean($data->passBaru)),"$1$"."uPolinus");
	$apassBaru	= explode("$",$passBaru);
	$vpassBaru	= sha1($apassBaru[3]);
	$epassBaru	= md5($vpassBaru);
	
	$sql = "update wsia_user set password ='$vpassBaru' where username='uPolinus' and md5(password)='$epass'";
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
	
}  

?>
