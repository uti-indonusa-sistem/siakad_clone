<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $id_smt=$_SESSION['ta'];
	  $perintah = "select id_aktifitas, nipd,nm_pd,id_smt,ips,sks_smt,wsia_kuliah_mahasiswa.ipk as vipk,sks_total,nm_jenj_didik,nm_lemb,ips,sks_smt,wsia_kuliah_mahasiswa.ipk,sks_total, id_stat_mhs from wsia_kuliah_mahasiswa,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_sms,wsia_jenjang_pendidikan  where wsia_kuliah_mahasiswa.xid_reg_pd=wsia_mahasiswa_pt.xid_reg_pd and wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and id_smt='$id_smt' order by nipd asc";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $dataA=array();
		    foreach ($data as $itemData) {
				$itemData->vnm_lemb=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
				
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
				array_push($dataA,$itemData);
			}
		    
		    echo json_encode($dataA);
		    
	  } catch (PDOException $salah) {
	    	  
	  }
	  
} else if ($aksi=="cek") {
	$id_smt = $_SESSION['ta'];
	$qryMhsAktif ="select count( DISTINCT xid_reg_pd) as jml from viewNilai where id_smt='$id_smt'";
	try {
		$db 	= koneksi();
	    $qry 	= $db->prepare($qryMhsAktif); 
	    $qry->execute();
	    $dataMhs	= $qry->fetch(PDO::FETCH_OBJ);
	    $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Cek Jumlah";
		$hasil['jumlah']=$dataMhs->jml;
		echo json_encode($hasil);
	} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
	}

} else if ($aksi=="update") {
	$id_smt = $_SESSION['ta'];

	//15/6/2020

	$qryMhsAktif ="select xid_reg_pd from viewNilai where id_smt='$id_smt' GROUP BY xid_reg_pd ORDER BY xid_reg_pd limit 500 offset $id";
	try {
		$db 	= koneksi();
	    $qry 	= $db->prepare($qryMhsAktif); 
	    $qry->execute();
	    $dataMhs	= $qry->fetchAll(PDO::FETCH_OBJ);
	} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
	}

	
	$count=0;
	foreach ($dataMhs as $itemMhs) {
		$count++;
		$xid_reg_pd=$itemMhs->xid_reg_pd;
		$id_aktifitas = $xid_reg_pd.$id_smt;

		//semester
		$jsks=0;
		$jnXsks=0;
		$ipsmt=0;
		
		//kumulatif
		$jsksK=0;
		$jnXsksK=0;
		$ipK=0;

		//hitung nilai
		$qryNilaiSebelum = "select * from viewNilai where id_smt<='$id_smt' and xid_reg_pd='".$xid_reg_pd."' order by id_smt";
		try {
		    $qry 	= $db->prepare($qryNilaiSebelum); 
		    $qry->execute();
		    $dataNilaiSebelum	= $qry->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $salah) {
		    exit(json_encode($salah->getMessage()));
		}
		
		foreach ($dataNilaiSebelum as $itemNilaiSebelum) {
		   	$jsksK=$jsksK+$itemNilaiSebelum->vsks_mk;
			$naK=$itemNilaiSebelum->nilai_indeks*$itemNilaiSebelum->vsks_mk;	
			$jnXsksK=$jnXsksK+$naK;	
			$ipK=$jnXsksK/$jsksK;
			if ($jsksK>0) {
				$ipK=number_format($jnXsksK/$jsksK,2);
			} 

			if ($itemNilaiSebelum->id_smt ==$id_smt) {
				$jsks=$jsks+$itemNilaiSebelum->vsks_mk;
				$na=$itemNilaiSebelum->nilai_indeks*$itemNilaiSebelum->vsks_mk;	
				$jnXsks=$jnXsks+$na;	
				if ($jsks>0) {
					$ipsmt=number_format($jnXsks/$jsks,2);
				} 
			}

		}

		//$sql = "insert ignore into wsia_kuliah_mahasiswa SELECT concat(xid_reg_pd,id_smt), id_smt, xid_reg_pd,'', '0','0','0','0','A' FROM wsia_nilai, wsia_kelas_kuliah WHERE wsia_nilai.xid_kls = wsia_kelas_kuliah.xid_kls AND wsia_kelas_kuliah.id_smt = '$id_smt' GROUP BY wsia_nilai.xid_reg_pd";

		$updated_at=date("Y-m-d H:i:s");
		$sql = "insert into wsia_kuliah_mahasiswa (id_aktifitas,id_smt,xid_reg_pd,id_reg_pd,ips,sks_smt,ipk,sks_total, id_stat_mhs,updated_at) values
					('$id_aktifitas','$id_smt','$xid_reg_pd','','$ipsmt','$jsks','$ipK','$jsksK','A','$updated_at')
				ON DUPLICATE KEY UPDATE ips = '$ipsmt',sks_smt = '$jsks',ipk = '$ipK',sks_total = '$jsksK', updated_at='$updated_at';
		";

		try {
			
		    $eksekusi 	= $db->query($sql);  
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Update. Kesalahan:<br>".$salah->getMessage();
			exit(json_encode($hasil));
		}
	
	}

	$db = null;
	$hasil['berhasil']=1;
	$hasil['id']=$id;
	$hasil['berikutnya']=$id+500;
	$hasil['jumlah']=$count;
	$hasil['pesan']="Berhasil Update";
	echo json_encode($hasil);
	
} else if ($aksi=="ceknon") {
	$id_smt = $_SESSION['ta'];
	$qryMhsAktif ="select count(xid_reg_pd) as jml from wsia_mahasiswa_pt where id_jns_keluar='' and mulai_smt <= '$id_smt' and xid_reg_pd not in ( select xid_reg_pd from viewNilai where id_smt='$id_smt' group by xid_reg_pd)";
	try {
		$db 	= koneksi();
	    $qry 	= $db->prepare($qryMhsAktif); 
	    $qry->execute();
	    $dataMhs	= $qry->fetch(PDO::FETCH_OBJ);
	    $db = null;
		$hasil['berhasil']=1;
		$hasil['pesan']="Berhasil Cek Jumlah";
		$hasil['jumlah']=$dataMhs->jml;
		echo json_encode($hasil);
	} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
	}

} else if ($aksi=="updatenon") {
	$id_smt = $_SESSION['ta'];

	//15/6/2020

	$qryMhsAktif ="select xid_reg_pd from wsia_mahasiswa_pt where id_jns_keluar='' and mulai_smt <= '$id_smt' and xid_reg_pd not in ( select xid_reg_pd from viewNilai where id_smt='$id_smt' group by xid_reg_pd) ORDER BY xid_reg_pd limit 500 offset $id";
	try {
		$db 	= koneksi();
	    $qry 	= $db->prepare($qryMhsAktif); 
	    $qry->execute();
	    $dataMhs	= $qry->fetchAll(PDO::FETCH_OBJ);
	} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
	}

	
	$count=0;
	foreach ($dataMhs as $itemMhs) {
		$count++;
		$xid_reg_pd=$itemMhs->xid_reg_pd;
		$id_aktifitas = $xid_reg_pd.$id_smt;

		//semester
		$jsks=0;
		$jnXsks=0;
		$ipsmt=0;
		
		//kumulatif
		$jsksK=0;
		$jnXsksK=0;
		$ipK=0;

		//hitung nilai
		$qryNilaiSebelum = "select * from viewNilai where id_smt<'$id_smt' and xid_reg_pd='".$xid_reg_pd."' order by id_smt";
		try {
		    $qry 	= $db->prepare($qryNilaiSebelum); 
		    $qry->execute();
		    $dataNilaiSebelum	= $qry->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $salah) {
		    exit(json_encode($salah->getMessage()));
		}
		
		foreach ($dataNilaiSebelum as $itemNilaiSebelum) {
		   	$jsksK=$jsksK+$itemNilaiSebelum->vsks_mk;
			$naK=$itemNilaiSebelum->nilai_indeks*$itemNilaiSebelum->vsks_mk;	
			$jnXsksK=$jnXsksK+$naK;	
			$ipK=$jnXsksK/$jsksK;
			if ($jsksK>0) {
				$ipK=number_format($jnXsksK/$jsksK,2);
			} 

		}


		$sql = "insert into wsia_kuliah_mahasiswa values
					('$id_aktifitas','$id_smt','$xid_reg_pd','','$ipsmt','$jsks','$ipK','$jsksK','N')
				ON DUPLICATE KEY UPDATE ips = '$ipsmt',sks_smt = '$jsks',ipk = '$ipK',sks_total = '$jsksK';
		";

		try {
			
		    $eksekusi 	= $db->query($sql);  
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Update. Kesalahan:<br>".$salah->getMessage();
			exit(json_encode($hasil));
		}
	
	}

	$db = null;
	$hasil['berhasil']=1;
	$hasil['id']=$id;
	$hasil['berikutnya']=$id+500;
	$hasil['jumlah']=$count;
	$hasil['pesan']="Berhasil Update";
	echo json_encode($hasil);
	
} else if ($aksi=="ubah") {
	$id_smt = $_SESSION['ta'];
	$id_aktifitas=$data->id_aktifitas;
	$id_stat_mhs=$data->id_stat_mhs;

	//15/6/2020

	$qryMhsStatus ="update wsia_kuliah_mahasiswa set id_stat_mhs='$id_stat_mhs' where id_aktifitas='$id_aktifitas'";
	try {
		$db 	= koneksi();
	    $qry 	= $db->prepare($qryMhsStatus); 
	    $qry->execute();
	    $db =null;
	    $hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil ubah status";
		echo json_encode($hasil);
	} catch (PDOException $salah) {
	   exit(json_encode($salah->getMessage()));
	}

}
