<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="tampil") {
	
	  if (isset($_GET['filter'])) {
	  	$data=$_GET['filter'];
	  	$value=$data['value'];
	  } else {
	  	$value="-";
	  }

	  if (isset($_GET['id'])) {
	  	$id=$_GET['id'];
	  } else {
	  	$id="";
	  }
	  
	  $kec = "select * from siakad_wilayah where value like '%$value%' and id like '%$id%' order by id asc limit 0,20";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->query($kec);		    
		    $dataKec		= $qry->fetchAll(PDO::FETCH_OBJ);
		   	    
		    $wilayah=json_encode($dataKec);
		    echo $wilayah;
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
}