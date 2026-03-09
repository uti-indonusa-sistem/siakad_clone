<?php

function sanitizeInt($val, $default = null) {
    if ($val === '' || $val === null) return $default;
    return is_numeric($val) ? (int)$val : $default;
}
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

if ($aksi=="tampil") {
	  $ta=$_SESSION['ta'];
	  $perintah = "select * from wsia_kelas_kuliah, wsia_sms,wsia_jenjang_pendidikan, wsia_mata_kuliah where wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$ta' ";
	  
	  $perintah .= isset($_GET['filter']['nm_mk'])?" and nm_mk like '%".$_GET['filter']['nm_mk']."%'":"";
	  $perintah .= isset($_GET['filter']['nm_kls'])?" and nm_kls like '%".$_GET['filter']['nm_kls']."%'":"";
	  
	  if ( isset($_GET['filter']['prodi']) && $_GET['filter']['prodi']!="" ){
	  	$nm_lemb=explode(" - ",$_GET['filter']['prodi']);
	  	$nm_jenj_didik=$nm_lemb[0];
	  	$nm_lemb=$nm_lemb[1];
	  	$perintah .= " and nm_jenj_didik like '%".$nm_jenj_didik."%'";
	  	$perintah .= " and nm_lemb like '%".$nm_lemb."%'";
	  }
	  
	  $perintah.=" order by nm_lemb,nm_kls ";
	  
	  $perintah .= isset($_GET['count'])?' LIMIT '.$_GET['count']:'';
	  $perintah .= isset($_GET['start'])?' OFFSET '.$_GET['start']:'';
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    $aData=array();
		    foreach ($data as $itemData) {
				$itemData->prodi=$itemData->nm_jenj_didik." - ".$itemData->nm_lemb;
				$th_awal=substr($itemData->id_smt,0,4);
				$th_akhir=$th_awal+1;
				if (substr($itemData->id_smt,4,1)==1) {
					$smt="Ganjil";
				} else {
					$smt="Genap";
				}
				$itemData->smt=$th_awal."/".$th_akhir." ".$smt;
				array_push($aData,$itemData);
			}
		    echo json_encode($aData);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="tambah") {
	$xid_kls=date("ymdHis").$data->xid_kls;
	$id_kurikulum_sp=$data->id_kurikulum_sp;
	$id_sms=$data->id_sms;
	$id_smt=$data->id_smt;
	$id_mk=$data->id_mk;
	$nm_kls=strtoupper($data->nm_kls);
	$mbkm = sanitizeInt($data->mbkm, 0);
	
	$qryMk = "select * from wsia_mata_kuliah where xid_mk ='$id_mk'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qryMk);  
		$dataMk		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$sks_mk=$dataMk->sks_mk;
		$sks_tm=$dataMk->sks_tm;
		$sks_prak=$dataMk->sks_prak;
		$sks_prak_lap=$dataMk->sks_prak_lap;
		$sks_sim=$dataMk->sks_sim;
		$qryKelasKuliah = "insert into wsia_kelas_kuliah (xid_kls,id_kurikulum_sp,id_sms,id_smt,id_mk,nm_kls,sks_mk,sks_tm,sks_prak,sks_prak_lap,sks_sim,mbkm) values('$xid_kls','$id_kurikulum_sp', '$id_sms','$id_smt','$id_mk','$nm_kls','$sks_mk','$sks_tm','$sks_prak','$sks_prak_lap','$sks_sim','$mbkm')";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKelasKuliah);  
		    $db = null;
	    	$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil Simpan";
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal Simpan. Kesalahan:<br>".$salah->getMessage();
			echo json_encode($hasil);
		}
		    
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 $hasil['pesan']="Gagal mengambil data Mata kuliah. Kesalahan:<br>".$salah->getMessage();
		 echo json_encode($hasil);
	  }
} else if ($aksi=="ubah") {
	$xid_kls=$data->xid_kls;
	$id_kurikulum_sp=$data->id_kurikulum_sp;
	$id_sms=$data->id_sms;
	$id_smt=$data->id_smt;
	$id_mk=$data->id_mk;
	$nm_kls=strtoupper($data->nm_kls);
	$mbkm=$data->mbkm;
	
	$qryMk = "select * from wsia_mata_kuliah where xid_mk ='$id_mk'";
	try {
		$db 	= koneksi();
		$eksekusi 	= $db->query($qryMk);  
		$dataMk		= $eksekusi->fetch(PDO::FETCH_OBJ);
		$db		= null;
		$sks_mk=$dataMk->sks_mk;
		$sks_tm=$dataMk->sks_tm;
		$sks_prak=$dataMk->sks_prak;
		$sks_prak_lap=$dataMk->sks_prak_lap;
		$sks_sim=$dataMk->sks_sim;
		$qryKelasKuliah = "update wsia_kelas_kuliah set id_kurikulum_sp='$id_kurikulum_sp',id_sms='$id_sms',id_smt='$id_smt',id_mk='$id_mk',nm_kls='$nm_kls',sks_mk='$sks_mk',sks_tm='$sks_tm',sks_prak='$sks_prak',sks_prak_lap='$sks_prak_lap',sks_sim='$sks_sim', mbkm='$mbkm' where xid_kls='$xid_kls' and (id_kls='' OR id_kls IS NULL)";
		try {
		    $db 		= koneksi();
		    $eksekusi 	= $db->query($qryKelasKuliah);  
		    $db = null;
		    if ($eksekusi->rowCount()>0) {
				$hasil['berhasil']=1;
	    		$hasil['pesan']="Berhasil ubah";
			} else {
				$hasil['berhasil']=0;
	    		$hasil['pesan']="Kelas tidak bisa dirubah.<br>Mungkin sudah disinkronkan ke Feeder";
			}
			echo json_encode($hasil);
		} catch (PDOException $salah) {
			$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal ubah. Kesalahan:<br>".$salah->getMessage();
			echo json_encode($hasil);
		}
		    
	  } catch (PDOException $salah) {
		 $hasil['berhasil']=0;
    	 $hasil['pesan']="Gagal mengambil data Mata kuliah. Kesalahan:<br>".$salah->getMessage();
		 echo json_encode($hasil);
	  }
	
} else if ($aksi=="hapus") {
	$xid_kls	=$data->xid_kls;
	$sql = "delete from wsia_kelas_kuliah where xid_kls='$xid_kls' and (id_kls='' OR id_kls IS NULL)";
	
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    	if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="Kelas tidak bisa dihapus.<br>Mungkin sudah disinkronkan ke Feeder";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    	$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}
	
} else if ($aksi=="tampilKRS") {
	$id_smt=$_SESSION['ta'];
	$xid_reg_pd=$id;
	  
	$qryMhs = "select id_sms from wsia_mahasiswa_pt where xid_reg_pd='$xid_reg_pd'";	
	try {
		  $db 	= koneksi();
		  $eksekusi = $db->query($qryMhs);  
		  $dataMhs	= $eksekusi->fetch(PDO::FETCH_OBJ);
		  $db		= null;
	  
		  $id_sms=$dataMhs->id_sms;
		  
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
		  
				  $perintah = "select xid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah where wsia_kelas_kuliah.id_sms =  wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_sms.xid_sms='$id_sms' $filterSudahKrs";
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
								$itemData->ambilKelas=0;
							}
							array_push($aData,$itemData);
						}
					     echo json_encode($aData);
				  } catch (PDOException $salah) {
					   exit( "1.".json_encode($salah->getMessage()));
				  }

     } catch (PDOException $salah) {
	    exit ( "3.".json_encode($salah->getMessage() ));
     }
}
