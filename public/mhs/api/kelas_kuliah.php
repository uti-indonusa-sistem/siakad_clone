<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaMHS']) { exit(); }

if ($aksi=="tampil") {
	  $id_smt	= $_SESSION['id_smt_aktif'];
	  $nm_kls	= $_SESSION['kelas'];
	  $id_sms	= $_SESSION['id_sms'];
	  
	  $dataSudahKrs=json_decode($data->data,true);
	  
	  if (count($dataSudahKrs)>0) {
		  $sudahKrs="(";
		  foreach ($dataSudahKrs as $itemData) {
		  	$sudahKrs.="'".$itemData."',";
		  }
		  $sudahKrs=trim($sudahKrs,",");
		  $sudahKrs.=")";
		  
		  $filterSudahKrs=" and xid_kls not in ".$sudahKrs;
	  } else {
	  	  $filterSudahKrs="";
	  }
	  
	
	$perintah = "select xid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah where wsia_kelas_kuliah.id_sms =  wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and nm_kls like '%$nm_kls%' and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_sms.xid_sms='$id_sms' $filterSudahKrs";
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    $aData=array();
		    foreach ($data as $itemData) {
		    	$tahun1=substr($itemData->id_smt,0,4);
		    	$tahun2=$tahun1+1;
		    	$smt=substr($itemData->id_smt,4,1);
		    	if ($smt=="1") {
					$vsmt="Ganjil";
				} else if ($smt=="2") {
					$vsmt="Genap";
				} else {
					$vsmt="Pendek";
				}
				
				$itemData->vid_smt=$tahun1."/".$tahun2." ".$vsmt;
				
				$adaAgama=strpos(strtolower($itemData->nm_mk), "agama");
				$itemData->agama=$adaAgama;
				if ($adaAgama) {
					$itemData->ambilKelas=0;
				} else {
					$itemData->ambilKelas=1;
				}
				array_push($aData,$itemData);
			}
		     echo json_encode($aData);
	  } catch (PDOException $salah) {
		   exit( "1.".json_encode($salah->getMessage()));
	  }
	 
}