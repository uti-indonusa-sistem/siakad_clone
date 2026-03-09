<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $id_kls=$id;
	  
	  $qryKelas = "select nm_kls from wsia_kelas_kuliah where xid_kls='$id_kls'";
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($qryKelas); 
		    $qry->execute();
		  
		    $data	= $qry->fetch(PDO::FETCH_OBJ);
		    $db		= null;
		    $nm_kls=$data->nm_kls;
		    		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
	 
	  	$perintah = "select id_nilai, wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd, nipd, nm_pd, jk, nm_jenj_didik,nm_lemb, left(mulai_smt,4) as angkatan,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls='$id_kls' and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik;";

	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $aData=array();
		    foreach ($data as $itemData) {
				$itemData->prodi=$itemData->nm_jenj_didik."-".$itemData->nm_lemb;
				array_push($aData,$itemData);
		     }
		    
		    echo json_encode($aData);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tampilKRS") {
	  $id_smt=$_SESSION['ta'];
	  $xid_reg_pd=$id;
	
	  $perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd'";
	 
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
				
				array_push($aData,$itemData);
			}
		    
		    echo json_encode($aData);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
    
} else if ($aksi=="tampilKHS") {
	  $id_smt=$_SESSION['ta'];
	  $xid_reg_pd=$id;
	
	  $perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd'";
			 
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
				$itemData->sksXindeks=$itemData->vsks_mk*$itemData->nilai_indeks;
				array_push($aData,$itemData);
			}
		    
		    echo json_encode($aData);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
    
} else if($aksi=="tambah") {
		  
	try {
	  	$xid_reg_pd=$id;
		$dataKelas=$data->kelas;
		$gagal=0;
		$updated_at = date("Y-m-d H:i:s");
		//print_r($dataKelas);
		foreach ($dataKelas as $id_kls) {
			$id_nilai=md5($id_kls.$xid_reg_pd);
			//echo $id_nilai."<br>";
			$qryKrs = "insert ignore into wsia_nilai (id_nilai,xid_kls,xid_reg_pd,asal_data,updated_at) values('$id_nilai','$id_kls','$xid_reg_pd','9','$updated_at')";
			try {
			    $db 		= koneksi();
			    $eksekusi 	= $db->query($qryKrs);  
			    $db = null;
		    	
			} catch (PDOException $salah) {
				$gagal=1;
			}
		}
		
		if ($gagal) {
			$hasil['berhasil']=0;
		    $hasil['pesan']="Proses Simpan KRS Tidak Selesai";
		} else {
			$hasil['berhasil']=1;
		    $hasil['pesan']="Berhasil Simpan";
		}
		
		echo json_encode($hasil);
		
	} catch (PDOException $salah) {
	    exit ( "3.".json_encode($salah->getMessage() ));
    } 
    
}  else if ($aksi=="hapus") {
	$id_nilai =$data->id_nilai;
	$sql = "delete from wsia_nilai where id_nilai='$id_nilai' and id_kls='' and id_reg_pd=''";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    	if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="KRS tidak bisa dihapus.<br>Mungkin sudah disinkronkan ke Feeder";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if($aksi=="ubahNilai") {
	
	try {
		$dataNilai=$data->nilai;
		$gagal=0;
		$updated_at = date("Y-m-d H:i:s");
		foreach ($dataNilai as $nilai) {
			$id_nilai=$nilai->id_nilai;
			$nilai_angka=$nilai->nilai_angka;
			$nilai_huruf=$nilai->nilai_huruf;
			$nilai_indeks=$nilai->nilai_indeks;
			
			$qryNilai = "update wsia_nilai set nilai_angka='$nilai_angka', nilai_huruf='$nilai_huruf', nilai_indeks='$nilai_indeks', updated_at='$updated_at' where id_nilai='$id_nilai'";
			try {
			    $db 		= koneksi();
			    $eksekusi 	= $db->query($qryNilai);  
			    $db = null;
		    	
			} catch (PDOException $salah) {
				$gagal=1;
			}
		}
		
		if ($gagal) {
		    $hasil['berhasil']=0;
		    $hasil['pesan']="Proses Simpan Nilai Tidak Selesai";
		} else {
			$hasil['berhasil']=1;
		    $hasil['pesan']="Berhasil Simpan";
		}
		
		echo json_encode($hasil);
		
	} catch (PDOException $salah) {
	    exit ( "4.".json_encode($salah->getMessage() ));
        } 
    
}  else if ($aksi=="tampilTranskip") {
	  $xid_reg_pd=$id;
	
	  $perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd'";
			 
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
				$itemData->sksXindeks=$itemData->vsks_mk*$itemData->nilai_indeks;
				array_push($aData,$itemData);
			}
		    
		    echo json_encode($aData);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
    
} 

