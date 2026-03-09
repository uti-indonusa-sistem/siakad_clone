<?php
include 'login_auth.php';

// //KONEKSI PJM
// //koneksi UPM dan siakad lama
$hostname_conf = "117.20.58.122";
$database_conf = "indonusa";
$username_conf = "uti-check";
$password_conf = "JcMnZu2D4mWUPcLr";
$con = new mysqli($hostname_conf, $username_conf, $password_conf, $database_conf);

if ($con->connect_errno) {
	echo "<h1 align='center'>Tidak Dapat Konek Database UPM! Hubungi administrator</h1>";
	exit();
}

$hostname_conf2 = "localhost";
$database_conf2 = "siakaddb";
$username_conf2 = "uti-check";
$password_conf2 = "haamA0iYA6^7aj8e*#";
$con2 = new mysqli($hostname_conf2, $username_conf2, $password_conf2, $database_conf2);

if ($con2->connect_errno) {
	echo "<h1 align='center'>Tidak Dapat Konek Database Siakad! Hubungi administrator</h1>";
	exit();
}

// //CEK ANGKET UPM
$nim = $_SESSION['nipd'];

// //Query cek angket 
$qryAngket = $con->query("select * from upm_angket where aktif='1'");
$adaAngket = $qryAngket->num_rows;

// if ($adaAngket > 0) {
// 	//query setting ANGKET
// 	$qryKonf = $con->query("select * from upm_konf");
// 	$dataKonf = $qryKonf->fetch_array(MYSQLI_ASSOC);
// 	$smt = $dataKonf['smt'];
// 	$thAk = $dataKonf['th_akademik'];

// 	if ($smt % 2 == 0) {
// 		$vsmt = "GENAP";
// 	} else {
// 		$vsmt = "GANJIL";
// 	}

// 	//cek mahasiswa
// 	$nim = $_SESSION['nipd'];
// 	$qryMhs = $con->query("select * from upm_mhs where nim='$nim' and smt='$smt' and thak='$thAk'");
// 	$ada = $qryMhs->num_rows;
// 	if ($ada == 0) {

// 		$qryAmbilMhs = $con2->query("select * from wsia_mahasiswa_pt where nipd='$nim'");
// 		$dataAmbilMhs = $qryAmbilMhs->fetch_array(MYSQLI_ASSOC);
// 		$nimUpm = $dataAmbilMhs['nim'];
// 		$passUpm = $dataAmbilMhs['pass'];
// 		$nimpass = $passUpm . "&n=" . $nim . "&verse=2";
// 		//echo $nimpass;
// 		exit("<script>alert('Anda harus mengisi Angket Evaluasi Pembelajaran terlebih dahulu agar bisa melanjutkan untuk mengisi KRS'); window.location='http://upm.poltekindonusa.ac.id/auth.php?v=" . $nimpass . "';</script>");
// 	}
// }

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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>
	<link rel="stylesheet" type="text/css" href="../lib/components/sidebar/sidebar.css">

	<script src="https://accounts.google.com/gsi/client" async defer></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

		body,
		.webix_view {
			font-family: 'Inter', sans-serif !important;
		}

		/* Premium Layout */
		.card_premium {
			background: #ffffff !important;
			border-radius: 12px !important;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06) !important;
			border: 1px solid #edf2f7 !important;
		}

		.toolbar_premium {
			background: #ffffff !important;
			border-bottom: 1px solid #edf2f7 !important;
		}

		.header_title {
			color: #1a202c !important;
			font-weight: 700 !important;
			font-size: 18px !important;
			letter-spacing: -0.5px;
		}

		.card_title {
			font-weight: 700 !important;
			font-size: 16px !important;
			color: #2d3748 !important;
			margin-bottom: 10px;
		}

		.card_desc {
			color: #718096 !important;
			font-size: 13px !important;
			line-height: 1.5;
		}

		/* Profile Image */
		.profile_image_container {
			width: 140px;
			height: 140px;
			border-radius: 70px;
			overflow: hidden;
			border: 4px solid #f7fafc;
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
			background: #fff;
			margin-bottom: 15px;
		}

		.profile_img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		/* Moodle Card Theme */
		.moodle_card {
			background: linear-gradient(to bottom right, #ffffff, #f9f7ff) !important;
			border-left: 4px solid #7c3aed !important;
		}

		.btn_moodle button {
			background: #7c3aed !important;
			border-radius: 8px !important;
			font-weight: 600 !important;
			box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2) !important;
		}

		.moodle_hint {
			font-size: 11px !important;
			color: #6d28d9 !important;
			font-style: italic;
			opacity: 0.8;
			padding: 5px 0;
		}

		/* Status Badges */
		.status_badge {
			padding: 4px 12px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 700;
			text-align: center;
			text-transform: uppercase;
		}

		.status_badge.aktif {
			background: #e6fffa;
			color: #047481;
			border: 1px solid #b2f5ea;
		}

		.status_badge.nonaktif {
			background: #fff5f5;
			color: #c53030;
			border: 1px solid #fed7d7;
		}

		.status_badge.loading {
			background: #f7fafc;
			color: #a0aec0;
		}

		/* Google Linked Account */
		.linked_account {
			padding: 12px;
			background: #f0fff4;
			border: 1px solid #c6f6d5;
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}

		.email_text {
			color: #22543d;
			font-weight: 600;
			font-size: 13px;
		}

		.unlink_btn {
			color: #c53030;
			font-size: 12px;
			cursor: pointer;
			text-decoration: underline;
		}

		/* Buttons Fix */
		.webix_primary button,
		.btn_action button {
			border-radius: 8px !important;
			font-weight: 600 !important;
		}
	</style>
</head>

<body class="app_wsiamhs" bgcolor="#244531">
</body>
<script src="js/wsiamhs_routes.js?v=<?= filemtime('js/wsiamhs_routes.js') ?>" charset="utf-8"></script>
<script src="js/wsiamhs.js?v=<?= filemtime('js/wsiamhs.js') ?>" charset="utf-8"></script>
<script src="js/wsiamhs_actions.js?v=<?= filemtime('js/wsiamhs_actions.js') ?>" charset="utf-8"></script>

</html>