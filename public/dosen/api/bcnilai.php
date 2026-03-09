<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

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
	  
	 
	  	$perintah = "select id_nilai, wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd, nipd, nm_pd, jk, nm_jenj_didik,nm_lemb, left(mulai_smt,4) as angkatan,nilai_angka,nilai_huruf,nilai_indeks, nilai_absen, nilai_tugas, nilai_uts, nilai_uas, nilai_tampil from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls='$id_kls' and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik order by nipd asc";

	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute();
		  
		    $data	= $qry->fetchAll(PDO::FETCH_OBJ);
		    $db		= null;
		    
		    $aData=array();
		    foreach ($data as $itemData) {
				$itemData->prodi=$itemData->nm_jenj_didik."-".$itemData->nm_lemb;

				if ($itemData->nilai_tampil!=1) {
	                $itemData->nilai_absen = $itemData->nilai_absen==null?"":$itemData->nilai_absen;
	                $itemData->nilai_tugas = $itemData->nilai_tugas==null?"":$itemData->nilai_tugas;
	                $itemData->nilai_uts = $itemData->nilai_uts==null?"":$itemData->nilai_uts;
	                $itemData->nilai_uas = $itemData->nilai_uas==null?"":$itemData->nilai_uas;
	            }
	            if ($itemData->nilai_tampil==0) {
	                $itemData->nilai_status = "Butuh Dinilai";
	            } else if ($itemData->nilai_tampil==1) {
	                $itemData->nilai_status = "BAAK";
	            } else if ($itemData->nilai_tampil==2) {
	                $itemData->nilai_status = "Draft";
	            } else if ($itemData->nilai_tampil==3) {
	                $itemData->nilai_status = "Terkirim";
	            }

				array_push($aData,$itemData);



		     }
		    
		    echo json_encode($aData);
		    
	  } catch (PDOException $salah) {
		   echo json_encode($salah->getMessage() );
	  }
	  
}  else if ($aksi=="ubah") {

	$nilai = json_decode(json_encode($data->nilai),true);

   	$db 	= koneksi();
    $sukses = true;
    foreach ($nilai as $item) {
        
        $id_nilai = $item['id_nilai'];
       	$nilai_absen = $item['nilai_absen']==""?"null":"'".$item['nilai_absen']."'";
        $nilai_tugas = $item['nilai_tugas']==""?"null":"'".$item['nilai_tugas']."'";
        $nilai_uts = $item['nilai_uts']==""?"null":"'".$item['nilai_uts']."'";
        $nilai_uas = $item['nilai_uas']==""?"null":"'".$item['nilai_uas']."'";
        if ($item['nilai_absen']=="" && $item['nilai_tugas']=="" && $item['nilai_uts']=="" && $item['nilai_uas']=="") {
            $nilai_angka = 0;
            $nilai_huruf = "";
            $nilai_indeks = 0;
            $nilai_tampil=0; //Butuh Dinilai
        } else {
            $nilai_angka = $item['nilai_angka'];
            $nilai_huruf = $item['nilai_huruf'];
            $nilai_indeks = $item['nilai_indeks'];
            $nilai_tampil=2; //Draft
        }

        $qryNilai = "update wsia_nilai set nilai_absen=$nilai_absen, nilai_tugas=$nilai_tugas, nilai_uts=$nilai_uts, nilai_uas=$nilai_uas, nilai_angka='$nilai_angka', nilai_huruf='$nilai_huruf', nilai_indeks='$nilai_indeks', nilai_tampil='$nilai_tampil'  where id_nilai='$id_nilai'";
	  
		try {
		    $qry 	= $db->prepare($qryNilai); 
		    $qry->execute();
		} catch (PDOException $salah) {
			$sukses=false;
			echo json_encode($salah);
		}

            
    }

    $db		= null;

    if ($sukses) {
        $hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil simpan nilai";    	
		echo json_encode($hasil);
    } else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Ada nilai yang tidak tersimpan";
		echo json_encode($hasil);
    }

} else if ($aksi=="kirim") {

	$nilai = json_decode(json_encode($data->nilai),true);

   	$db 	= koneksi();
    $sukses = true;
    foreach ($nilai as $item) {
        
        $id_nilai = $item['id_nilai'];
        $updated_at = date("Y-m-d H:i:s");
        $qryNilai = "update wsia_nilai set nilai_tampil='3', updated_at='$updated_at' where id_nilai='$id_nilai'";
	  
		try {
		    $qry 	= $db->prepare($qryNilai); 
		    $qry->execute();
		} catch (PDOException $salah) {
			$sukses=false;
			echo json_encode($salah);
		}

            
    }

    $db		= null;

    if ($sukses) {
        $hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil kirim nilai";
		echo json_encode($hasil);
    } else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Ada nilai yang tidak terkirim";
		echo json_encode($hasil);
    }

}