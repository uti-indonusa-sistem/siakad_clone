<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

if ($aksi=="tampil") {
	  $id_smt	= $id;	  
	  $xid_ptk	=$_SESSION['xid_ptk'];
	  
	
	$perintah = "select * from viewKelasKuliah where id_smt='$id_smt' and id_ptk ='$xid_ptk' ";
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
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
								
			}
		     echo json_encode($data);
	  } catch (PDOException $salah) {
		   exit( "1.".json_encode($salah->getMessage()));
	  }
	 
} else if ($aksi=="persennilai") {

	$id_kls  	= $id;
	$persen_absen = $data->persen_absen;
	$persen_tugas = $data->persen_tugas;
	$persen_uts = $data->persen_uts;
	$persen_uas = $data->persen_uas;
	
	$sql = "update wsia_kelas_kuliah set persen_absen ='$persen_absen', persen_tugas='$persen_tugas', persen_uts='$persen_uts', persen_uas='$persen_uas' where xid_kls='$id_kls'";
	
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
	    if($eksekusi->rowCount()>0) {
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Ubah Persen Nilai";
	    } else {
			$hasil['berhasil']=1;
	    	$hasil['pesan']="Persen Nilai Tidak Berubah";
	    }
	    echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Ubah. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
}  
