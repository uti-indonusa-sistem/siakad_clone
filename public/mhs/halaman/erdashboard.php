<?php 
	include 'login_auth.php';

	//KONEKSI PJM
	//koneksi UPM dan siakad lama
	$hostname_conf = "117.20.58.122";
	$database_conf = "indonusa";
	$username_conf = "uti-check";
	$password_conf = "JcMnZu2D4mWUPcLr";
	$con = new mysqli($hostname_conf, $username_conf, $password_conf,$database_conf);
	
	if ($con -> connect_errno) {
	   echo "<h1 align='center'>Tidak Dapat Konek Database UPM! Hubungi administrator</h1>";
		exit();
	}

	//CEK ANGKET UPM
	$nim=$_SESSION['nipd'];

	//Query cek angket 
	$qryAngket=$con->query("select * from upm_angket where aktif='1'");
	$adaAngket=$qryAngket->num_rows;

	function encrypt($str) {
	    $kunci = '979a218e0632df2935317f98d47956c7sopingi';
	    $hasil="";
	    for ($i = 0; $i < strlen($str); $i++) {
	        $karakter = substr($str, $i, 1);
	        $kuncikarakter = substr($kunci, ($i % strlen($kunci))-1, 1);
	        $karakter = chr(ord($karakter)+ord($kuncikarakter));
	        $hasil .= $karakter;
	        
	    }
	    return urlencode(base64_encode($hasil));
	}


	function e($e){
	    $e = serialize($e);
	    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
	    $id = pack('H*', substr(sha1(md5("sopingi")).md5("indonusa"),1,64));
	    $mac = hash_hmac('sha256', $e, substr(bin2hex($id), -32));
	    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $id, $e.$mac, MCRYPT_MODE_CBC, $iv);
	    $enc = base64_encode($passcrypt).'|'.base64_encode($iv);
	    return $enc;
	}


	if ($adaAngket>0)  {
		//query setting ANGKET
		$qryKonf=$con->query("select * from upm_konf");
		$dataKonf=$qryKonf->fetch_array(MYSQLI_ASSOC);
		$smt=$dataKonf['smt'];
		$thAk=$dataKonf['th_akademik'];

		if ($smt % 2 ==0) { $vsmt="GENAP"; } else { $vsmt="GANJIL"; }

		//cek mahasiswa
		$nim=$_SESSION['nipd'];
		echo "select * from upm_mhs where nim='$nim' and smt='$smt' and thak='$thAk'";
		$qryMhs=$con->query("select * from upm_mhs where nim='$nim' and smt='$smt' and thak='$thAk'");
		$ada=$qryAngket->num_rows;

		if ($ada==0) {
			
			$qryAmbilMhs=$con->query("select * from mahasiswa where nim='$nim'");
		  	$dataAmbilMhs=$qryAmbilMhs->fetch_array(MYSQLI_ASSOC);
		  	$nimUpm=$dataAmbilMhs['nim'];
		  	$passUpm=$dataAmbilMhs['pass'];
		  	$nimpass=e($passUpm)."&n=".$nimUpm;
			//echo $nimpass;
		  	exit("<script>alert('Anda harus mengisi Angket Evaluasi Pembelajaran terlebih dahulu agar bisa melanjutkan untuk mengisi KRS'); window.location='http://upm.poltekindonusa.ac.id/auth.php?v=".$nimpass."';</script>");
			
		 } 
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Mahasiswa | POLITEKNIK INDONUSA Surakarta</title>
		<script type="text/javascript" src="../lib/jquery-1.7.1.min.js"></script>
  		<script type="text/javascript" src="../lib/backbone/underscore.js"></script>
  		<script type="text/javascript" src="../lib/backbone/backbone.js"></script>
		<link rel="stylesheet" type="text/css" href="../lib/webix.css">
		<link rel="stylesheet" type="text/css" href="../lib/wsia.css">
		<link rel="stylesheet" href="../lib/skins/compact.css" type="text/css" media="screen" charset="utf-8">
		<script src="../lib/webix.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" src="../lib/components/sidebar/sidebar.js"></script>
		<link rel="stylesheet" type="text/css" href="../lib/components/sidebar/sidebar.css">
	</head>
	<body class="app_wsiamhs" bgcolor="#244531">
	</body>
	<script src="js/wsiamhs_routes.js?v=<?= filemtime('js/wsiamhs_routes.js') ?>" charset="utf-8"></script>
	<script src="js/wsiamhs.js?v=<?= filemtime('js/wsiamhs.js') ?>" charset="utf-8"></script>
	<script src="js/wsiamhs_actions.js?v=<?= filemtime('js/wsiamhs_actions.js') ?>" charset="utf-8"></script>
	
</html>