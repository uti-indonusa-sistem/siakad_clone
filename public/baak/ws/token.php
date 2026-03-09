<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="generate") {	
	$data = token();
	echo $data;
} 

?>