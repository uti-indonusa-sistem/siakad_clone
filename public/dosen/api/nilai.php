<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

// Include security functions
require_once __DIR__ . '/../../lib/security.php';


if ($aksi=="tampil") {
	 
	 $id_kls = Security::sanitizeString($id);
	  
	  // SECURED: Use prepared statement
	  $qryKelas = "SELECT nm_kls FROM wsia_kelas_kuliah WHERE xid_kls = :id_kls LIMIT 1";
	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($qryKelas); 
		    $qry->execute([':id_kls' => $id_kls]);
		  
		    $data	= $qry->fetch(PDO::FETCH_OBJ);
		    $db		= null;
		    $nm_kls = $data ? $data->nm_kls : 'Kelas Tidak Ditemukan';
		    		    
	  } catch (PDOException $salah) {
		   Security::logSecurityEvent("Error getting kelas info: " . $salah->getMessage(), 'ERROR');
		   echo json_encode(['error' => 'Gagal mengambil data kelas']);
	  }
	  
	 
	  	// SECURED: Use prepared statement for main query
	  	$perintah = "SELECT id_nilai, wsia_nilai.xid_kls, wsia_nilai.xid_reg_pd, nipd, nm_pd, jk, nm_jenj_didik, nm_lemb, LEFT(mulai_smt,4) as angkatan, nilai_angka, nilai_huruf, nilai_indeks, nilai_absen, nilai_tugas, nilai_uts, nilai_uas, nilai_tampil 
	  	             FROM wsia_nilai, wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms, wsia_jenjang_pendidikan 
	  	             WHERE wsia_nilai.xid_kls = :id_kls 
	  	             AND wsia_mahasiswa_pt.xid_reg_pd = wsia_nilai.xid_reg_pd 
	  	             AND wsia_mahasiswa.xid_pd = wsia_mahasiswa_pt.id_pd 
	  	             AND wsia_mahasiswa_pt.id_sms = wsia_sms.xid_sms 
	  	             AND wsia_sms.id_jenj_didik = wsia_jenjang_pendidikan.id_jenj_didik 
	  	             ORDER BY nipd ASC";

	  
	  try {
		    $db 	= koneksi();
		    $qry 	= $db->prepare($perintah); 
		    $qry->execute([':id_kls' => $id_kls]);
		  
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

				$itemData->nilai_angka = $itemData->nilai_angka==null?0:$itemData->nilai_angka;
				$itemData->nilai_huruf = $itemData->nilai_huruf==null?"":$itemData->nilai_huruf;
				$itemData->nilai_indeks = $itemData->nilai_indeks==null?0:$itemData->nilai_indeks;
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
		   Security::logSecurityEvent("Error getting nilai data: " . $salah->getMessage(), 'ERROR');
		   echo json_encode(['error' => 'Gagal mengambil data nilai']);
	  }
	  
}  else if ($aksi=="ubah") {

	// Convert data to array format for compatibility
	$nilai = json_decode(json_encode($data->nilai), true);

   	$db = koneksi();
    $sukses = true;
    $errors = [];

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
            $nilai_tampil=2; //Draft (tetap draft meskipun kosong)
        } else {
            $nilai_angka = $item['nilai_angka'];
            $nilai_huruf = $item['nilai_huruf'];
            $nilai_indeks = $item['nilai_indeks'];
            $nilai_tampil=2; //Draft
        }

        // SECURED: Use prepared statement
        $qryNilai = "UPDATE wsia_nilai SET 
            nilai_absen = :nilai_absen, 
            nilai_tugas = :nilai_tugas, 
            nilai_uts = :nilai_uts, 
            nilai_uas = :nilai_uas, 
            nilai_angka = :nilai_angka, 
            nilai_huruf = :nilai_huruf, 
            nilai_indeks = :nilai_indeks, 
            nilai_tampil = :nilai_tampil,
            updated_at = NOW()
            WHERE id_nilai = :id_nilai";
	  
		try {
		    $qry = $db->prepare($qryNilai);
		    $qry->execute([
		        ':nilai_absen' => $nilai_absen === 'null' ? null : str_replace("'", "", $nilai_absen),
		        ':nilai_tugas' => $nilai_tugas === 'null' ? null : str_replace("'", "", $nilai_tugas),
		        ':nilai_uts' => $nilai_uts === 'null' ? null : str_replace("'", "", $nilai_uts),
		        ':nilai_uas' => $nilai_uas === 'null' ? null : str_replace("'", "", $nilai_uas),
		        ':nilai_angka' => $nilai_angka,
		        ':nilai_huruf' => $nilai_huruf,
		        ':nilai_indeks' => $nilai_indeks,
		        ':nilai_tampil' => $nilai_tampil,
		        ':id_nilai' => $id_nilai
		    ]);
		} catch (PDOException $salah) {
			$sukses=false;
			$errors[] = "Error updating nilai ID $id_nilai: " . $salah->getMessage();
		}
    }

    $db = null;

    if ($sukses) {
        $hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil simpan draft nilai";    	
		echo json_encode($hasil);
    } else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Ada nilai yang tidak tersimpan: " . implode(', ', $errors);
		echo json_encode($hasil);
    }

} else if ($aksi=="kirim") {

	// Convert data to array format for compatibility
	$nilai = json_decode(json_encode($data->nilai), true);

   	$db = koneksi();
    $sukses = true;
    $errors = [];

    foreach ($nilai as $item) {
        
        $id_nilai = $item['id_nilai'];
        $updated_at = date("Y-m-d H:i:s");
        
        // SECURED: Use prepared statement
        $qryNilai = "UPDATE wsia_nilai SET 
            nilai_tampil = '3', 
            updated_at = :updated_at 
            WHERE id_nilai = :id_nilai 
            AND nilai_tampil = '2'"; // Only allow sending draft values
	  
		try {
		    $qry = $db->prepare($qryNilai);
		    $qry->execute([
		        ':updated_at' => $updated_at,
		        ':id_nilai' => $id_nilai
		    ]);
		    
		    if ($qry->rowCount() === 0) {
		        $errors[] = "Gagal kirim nilai ID: $id_nilai (bukan draft atau tidak ditemukan)";
		        $sukses = false;
		    }
		} catch (PDOException $salah) {
			$sukses=false;
			$errors[] = "Error sending nilai ID $id_nilai: " . $salah->getMessage();
		}
    }

    $db = null;

    if ($sukses) {
        $hasil['berhasil']=1;
    	$hasil['pesan']="Berhasil kirim nilai ke BAAK";
		echo json_encode($hasil);
    } else {
        $hasil['berhasil']=0;
    	$hasil['pesan']="Ada nilai yang tidak terkirim: " . implode(', ', $errors);
		echo json_encode($hasil);
    }

}