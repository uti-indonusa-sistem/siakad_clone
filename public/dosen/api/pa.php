<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

if ($aksi=="krs") {
	 
	  $id_smt=$_SESSION['id_smt_aktif'];
	  $xid_reg_pd=$id;
	  
	  $perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt, wsia_nilai.id_kls, asal_data, nilai_huruf from wsia_kelas_kuliah, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";

	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db	= null;
		    
		    $aData=array();
		    $aSpan=array();
		    $index=0;
		    foreach ($data as $itemData) {
		    	
		    	if ( $itemData->asal_data=="1" or $itemData->asal_data=="9") {
		    		$itemData->vasal_data="FEEDER";
		    	} else if ( $itemData->asal_data=="2") {
		    		$itemData->vasal_data="PA";
		    	} else if ( $itemData->asal_data=="3") {
		    		$itemData->vasal_data="Mahasiswa";
		    	} else {
		    		$itemData->vasal_data="BAAK";
		    	}
		    	

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
				
				$vid_kls=$itemData->vid_kls;
			  
				 $sqlPengampu="select xid_ajar,xid_ptk,xid_reg_ptk,concat(gelar_depan,nm_ptk,', ',gelar_belakang) as dosen_pengampu from wsia_ajar_dosen,wsia_dosen,wsia_dosen_pt where wsia_ajar_dosen.id_reg_ptk=wsia_dosen_pt.xid_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_ajar_dosen.id_kls='$vid_kls' ";
				  
				  try {
					 $db 	= koneksi();
					 $qryPengampu 	= $db->prepare($sqlPengampu); 
					 $qryPengampu->execute();
					 $dataPengampu	= $qryPengampu->fetchAll(PDO::FETCH_OBJ);
					 $jPengampu=$qryPengampu->rowCount();
					 $db		= null;
				  } catch (PDOException $salah) {
					 exit(json_encode($salah->getMessage()));
				  }
				
				  if ($jPengampu>0) {
				  	$aItemData = array();
				  	$id_nilai=$itemData->id_nilai;
				  	$vid_kls=$itemData->vid_kls;
				  	$nm_kls=$itemData->nm_kls;
				  	$kode_mk=$itemData->kode_mk;
				  	$nm_mk=$itemData->nm_mk;
				  	$vsks_mk=$itemData->vsks_mk;
				  	$vsks_tm=$itemData->vsks_tm;
				  	$vsks_prak=$itemData->vsks_prak;
				  	$vsks_prak_lap=$itemData->vsks_prak_lap;
				  	$id_smt=$itemData->id_smt;
				  	$vid_smt=$itemData->vid_smt;
				  	$agama=$itemData->agama;
				  	
				  	$iPengampu=0;
				  	$nama_pengampu="";
					foreach ($dataPengampu as $itemPengampu) {
						$aItemData['id_nilai']=$id_nilai;
					  	$aItemData['vid_kls']=$vid_kls;
					  	$aItemData['nm_kls']=$nm_kls;
					  	$aItemData['kode_mk']=$kode_mk;
					  	$aItemData['nm_mk']=$nm_mk;
					  	$aItemData['vsks_mk']=$vsks_mk;
					  	$aItemData['vsks_tm']=$vsks_tm;
					  	$aItemData['vsks_prak']=$vsks_prak;
					  	$aItemData['vsks_prak_lap']=$vsks_prak_lap;
					  	$aItemData['id_smt']=$id_smt;
					  	$aItemData['vid_smt']=$vid_smt;
					  	$aItemData['agama']=$agama;
						$aItemData['dosen_pengampu']=$itemPengampu->dosen_pengampu;
						$aItemData['id']=$index;
						
						//array_push($aData,$aItemData);
						
						$nama_pengampu.=$itemPengampu->dosen_pengampu;
						
						$iPengampu++;
						 if ($jPengampu>1 && $iPengampu<$jPengampu) {
						  	$aSpan[]=array($index,"index",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"nm_kls",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"kode_mk",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"nm_mk",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"vsks_mk",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"vsks_tm",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"vsks_prak",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"vsks_prak_lap",1,$jPengampu,"","");
						  	$aSpan[]=array($index,"vid_smt",1,$jPengampu,"","");
						  	
						  	$nama_pengampu.="<br>";
						  } 
						
						//$index++;
				  	}
				  	
				  	
				  	$itemData->dosen_pengampu=$nama_pengampu;
					$itemData->id=$index;
					array_push($aData,$itemData);
					$index++;
					
				  } else {
					$itemData->dosen_pengampu="-";
					$itemData->id=$index;
					array_push($aData,$itemData);
					$index++;
				  }
			
		   	}
		   
		    echo json_encode(array("data"=>$aData,"spans"=>$aSpan));
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
} else if ($aksi=="kelas_kuliah") {
	$id_smt	= $_SESSION['id_smt_aktif'];
	$nm_kls	= $data->kelas;
	$id_sms	= $data->id_sms;
	  
	$dataSudahKrs=json_decode(json_encode($data->data),true);
	  
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
	  
	
	$perintah = "select xid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,id_smt from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah where wsia_kelas_kuliah.id_sms =  wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and nm_kls='$nm_kls' and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_sms.xid_sms='$id_sms' $filterSudahKrs";
	
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

} else if ($aksi=="tambah_krs") {

	$xid_reg_pd=$id;
	$dataKelas=$data->kelas;
	$gagal=0;
	
	foreach ($dataKelas as $id_kls) {
		$id_nilai=md5($id_kls.$xid_reg_pd);
		//echo $id_nilai."<br>";
		$qryKrs = "insert ignore into wsia_nilai (id_nilai,xid_kls,xid_reg_pd,asal_data) values('$id_nilai','$id_kls','$xid_reg_pd','2')";
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

} else if ($aksi=="hapus_krs") {
	
	$id_nilai =$data->id_nilai;
	$sql = "delete from wsia_nilai where id_nilai='$id_nilai' and id_kls='' and id_reg_pd='' and nilai_huruf=''";
	try {
	    $db 		= koneksi();
	    $eksekusi 	= $db->query($sql);  
	    $db = null;
    	if ($eksekusi->rowCount()>0) {
			$hasil['berhasil']=1;
    		$hasil['pesan']="Berhasil hapus";
		} else {
			$hasil['berhasil']=0;
    		$hasil['pesan']="KRS tidak bisa dihapus";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil']=0;
    		$hasil['pesan']="Gagal Hapus. Kesalahan:<br>".$salah->getMessage();
		echo json_encode($hasil);
	}

} else if ($aksi=="nilai") {

	$xid_reg_pd=$id;
	
	$perintah = "select id_nilai, wsia_nilai.xid_kls as vid_kls, nm_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' order by id_smt desc, nm_mk";
	 
	try {
	    $db 	= koneksi();
	    $qry 	= $db->prepare($perintah); 
	    $qry->execute();
	  
	    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
	    $db	= null;
	    
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
	    	$itemData->sksXindeks = $itemData->vsks_mk*$itemData->nilai_indeks;
	    	array_push($aData,$itemData);
	    }
	    
	    echo json_encode($aData);
	    
	} catch (PDOException $salah) {
	   echo json_encode($salah->getMessage() );
	}

} else if ($aksi=="kuliah_mahasiswa") {

	  $xid_reg_pd=$id;

	  $perintah = "select id_aktifitas, nipd,nm_pd,id_smt,ips,sks_smt,wsia_kuliah_mahasiswa.ipk as vipk,sks_total,nm_jenj_didik,nm_lemb,ips,sks_smt,wsia_kuliah_mahasiswa.ipk,sks_total, id_stat_mhs from wsia_kuliah_mahasiswa,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_sms,wsia_jenjang_pendidikan  where wsia_kuliah_mahasiswa.xid_reg_pd=wsia_mahasiswa_pt.xid_reg_pd and wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kuliah_mahasiswa.xid_reg_pd='$xid_reg_pd' order by id_smt asc";
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

} else if ($aksi=="jurnal") {

	  $xid_reg_pd=$id;
	  $xid_ptk	= $_SESSION['xid_ptk'];

	  $perintah = "select * from siakad_pa_jurnal where xid_ptk='$xid_ptk' and xid_reg_pd='$xid_reg_pd' ";

	  $perintah .= isset($_GET['filter']['tanggal'])?" and tanggal like '%".$_GET['filter']['tanggal']."%'":"";
	  $perintah .= isset($_GET['filter']['konten'])?" and konten like '%".$_GET['filter']['konten']."%'":"";
	  $perintah .= isset($_GET['filter']['oleh'])?" and oleh like '%".$_GET['filter']['oleh']."%'":"";
	  
	  $perintah.=" order by tanggal desc";
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }

} else if ($aksi=="tambah_jurnal") {

	  $xid_reg_pd=$id;
	  $xid_ptk	= $_SESSION['xid_ptk'];
	  $tanggal = $data->tanggal;
	  $konten = $data->konten;

	  $perintah = "insert into siakad_pa_jurnal (xid_ptk, xid_reg_pd, tanggal, konten, oleh) values (?,?,?,?,?)";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute([$xid_ptk, $xid_reg_pd, $tanggal, $konten, 'Dosen PA']);
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil tambah jurnal";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal tambah jurnal " . $salah->getMessage();
		    echo json_encode($hasil);
	  }

} else if ($aksi=="ubah_jurnal") {

	  $id = $data->id;
	  $tanggal = $data->tanggal;
	  $konten = $data->konten;

	  $perintah = "update siakad_pa_jurnal set tanggal=?, konten=? where id=?";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute([$tanggal, $konten, $id]);
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil ubah jurnal";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal ubah jurnal: " . $salah->getMessage();
		    echo json_encode($hasil);
	  }

} else if ($aksi=="hapus_jurnal") {

	  $id = $data->id;

	  $perintah = "delete from siakad_pa_jurnal where id='$id'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil hapus jurnal";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal hapus jurnal. Mungkin ada pesan didalamnya";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="pesan") {

	  $id_jurnal=$id;
	 
	  $perintah = "select * from siakad_pa_chat where id_jurnal='$id_jurnal' order by waktu asc ";

	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data		= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;

		    foreach ($data as $item) {
		    	
		    	if ($item->pesan_pa!="") {
		    		$item->author="dosen";
		    		$item->text=$item->pesan_pa;
		    	} else {
		    		$item->author="mahasiswa";
		    		$item->text=$item->pesan_mhs;
		    	}
		    	$item->waktu = format_tanggal_waktu($item->waktu);
		    	
		    }
		    
		    echo json_encode($data);
		    
	  } catch (PDOException $salah) {
	    	  
	  }

} else if ($aksi=="kirim_pesan") {

	  $id_jurnal = $data->id_jurnal;
	  $pesan_pa = $data->pesan;
	  $waktu = date("Y-m-d H:i:s");

	  $perintah = "insert into siakad_pa_chat values(null,'$id_jurnal','$waktu','$pesan_pa','')";
	  //echo $perintah;
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil kirim pesan";
	    	$hasil['author']="dosen";
	    	$hasil['text']=$pesan_pa;
	    	$hasil['waktu']=format_tanggal_waktu($waktu);
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal kirim pesan";
		    echo json_encode($hasil);
	  }

} else if ($aksi=="hapus_pesan") {

	  $id = $data->id;

	  $perintah = "delete from siakad_pa_chat where id='$id'";
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $db		= null;
		    
		    $hasil['berhasil']=1;
		    $hasil['id']=$id;
	    	$hasil['pesan']="Berhasil hapus pesan";
		    echo json_encode($hasil);
		    
	  } catch (PDOException $salah) {
	    	$hasil['berhasil']=0;
	    	$hasil['pesan']="Gagal hapus pesan";
		    echo json_encode($hasil);
	  }

} 