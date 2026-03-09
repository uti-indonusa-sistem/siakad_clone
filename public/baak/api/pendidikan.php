<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {	  
	  
	  $perintah = "select *, wsia_satuan_pendidikan.nm_lemb as sp_nm_lemb, concat(nm_jenj_didik,'-',wsia_sms.nm_lemb) as sms_nm_lemb from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan,wsia_satuan_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_mahasiswa_pt.id_sp=wsia_satuan_pendidikan.id_sp and xid_pd='$id' order by nipd ";
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    $dataA=array();
		    foreach ($data as $itemData) {
				$id_jns_daftar=$itemData->id_jns_daftar;
				if ($id_jns_daftar=="1") {
					$vid_jns_daftar="Peserta didik baru";
				} else if ($id_jns_daftar=="2") {
					$vid_jns_daftar="Pindahan";
				} 
				$itemData->vid_jns_daftar=$vid_jns_daftar;
				
				if ($itemData->mulai_smt>0) {
					$tahun1=substr($itemData->mulai_smt,0,4);
				    	$tahun2=$tahun1+1;
				    	$smt=substr($itemData->mulai_smt,4,1);
				    	if ($smt=="1") {
						$vsmt="Ganjil";
					} else if ($smt=="2") {
						$vsmt="Genap";
					} else {
						$vsmt="Pendek";
					}
					$itemData->nm_smt=$tahun1."/".$tahun2." ".$vsmt;
				} else {
					$itemData->nm_smt="Kosong";
				}
				
					
				
				
				array_push($dataA,$itemData);
		  }
			
		  echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
}  else if ($aksi=="tambah") {
	
} else if ($aksi=="ubah") {
	
} else if ($aksi=="hapus") {
	
}

