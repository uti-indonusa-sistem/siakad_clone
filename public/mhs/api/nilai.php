<?php
if (!isset($key)) {
	exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaMHS']) {
	exit();
}

if ($aksi == "tampil") {
	$id_smt = $_SESSION['id_smt_aktif'];
	$xid_reg_pd = $_SESSION['xid_reg_pd'];

	//$perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt, concat(gelar_depan,nm_ptk,gelar_belakang) as dosen_pengampu from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai, wsia_dosen where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and pengampu = id_ptk and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";

	$perintah = "select id_nilai, wsia_nilai.nilai_tampil as akses, wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt from wsia_kelas_kuliah, wsia_mata_kuliah, wsia_nilai where  wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";

	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$aData = array();
		$aSpan = array();
		$index = 0;
		foreach ($data as $itemData) {

			$tahun1 = substr($itemData->id_smt, 0, 4);
			$tahun2 = $tahun1 + 1;
			$smt = substr($itemData->id_smt, 4, 1);
			if ($smt == "1") {
				$vsmt = "Ganjil";
			} else if ($smt == "2") {
				$vsmt = "Genap";
			} else {
				$vsmt = "Pendek";
			}

			$itemData->vid_smt = $tahun1 . "/" . $tahun2 . " " . $vsmt;

			$adaAgama = strpos(strtolower($itemData->nm_mk), "agama");
			$itemData->agama = $adaAgama;

			$vid_kls = $itemData->vid_kls;

			$sqlPengampu = "select xid_ajar,xid_ptk,xid_reg_ptk,concat(gelar_depan,nm_ptk,', ',gelar_belakang) as dosen_pengampu from wsia_ajar_dosen,wsia_dosen,wsia_dosen_pt where wsia_ajar_dosen.id_reg_ptk=wsia_dosen_pt.xid_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_ajar_dosen.id_kls='$vid_kls' ";

			try {
				$db = koneksi();
				$qryPengampu = $db->prepare($sqlPengampu);
				$qryPengampu->execute();
				$dataPengampu = $qryPengampu->fetchAll(PDO::FETCH_OBJ);
				$jPengampu = $qryPengampu->rowCount();
				$db = null;
			} catch (PDOException $salah) {
				exit(json_encode($salah->getMessage()));
			}

			if ($jPengampu > 0) {
				$aItemData = array();
				$id_nilai = $itemData->id_nilai;
				$vid_kls = $itemData->vid_kls;
				$nm_kls = $itemData->nm_kls;
				$kode_mk = $itemData->kode_mk;
				$nm_mk = $itemData->nm_mk;
				$vsks_mk = $itemData->vsks_mk;
				$vsks_tm = $itemData->vsks_tm;
				$vsks_prak = $itemData->vsks_prak;
				$vsks_prak_lap = $itemData->vsks_prak_lap;
				$id_smt = $itemData->id_smt;
				$vid_smt = $itemData->vid_smt;
				$agama = $itemData->agama;

				$iPengampu = 0;
				$nama_pengampu = "";
				foreach ($dataPengampu as $itemPengampu) {
					$aItemData['id_nilai'] = $id_nilai;
					$aItemData['vid_kls'] = $vid_kls;
					$aItemData['nm_kls'] = $nm_kls;
					$aItemData['kode_mk'] = $kode_mk;
					$aItemData['nm_mk'] = $nm_mk;
					$aItemData['vsks_mk'] = $vsks_mk;
					$aItemData['vsks_tm'] = $vsks_tm;
					$aItemData['vsks_prak'] = $vsks_prak;
					$aItemData['vsks_prak_lap'] = $vsks_prak_lap;
					$aItemData['id_smt'] = $id_smt;
					$aItemData['vid_smt'] = $vid_smt;
					$aItemData['agama'] = $agama;
					$aItemData['dosen_pengampu'] = $itemPengampu->dosen_pengampu;
					$aItemData['id'] = $index;

					//array_push($aData,$aItemData);

					$nama_pengampu .= $itemPengampu->dosen_pengampu;

					$iPengampu++;
					if ($jPengampu > 1 && $iPengampu < $jPengampu) {
						$aSpan[] = array($index, "index", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "nm_kls", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "kode_mk", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "nm_mk", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_mk", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_tm", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_prak", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_prak_lap", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vid_smt", 1, $jPengampu, "", "");

						$nama_pengampu .= "<br>";
					}

					//$index++;
				}



				// --- START MODIFICATION: Add Moodle Link Lookup ---
				$cleanKelas = preg_replace('/[^a-z0-9]/i', '', $nm_kls);
				$moodleKey = $kode_mk . '-' . $id_smt . '-' . $cleanKelas;

				// Default link
				$moodleLink = "#";
				$moodleStatus = "Belum Tersedia";

				try {
					$dbCheck = koneksi();
					$sqlMoodle = "SELECT moodle_id FROM moodle_sync_mapping WHERE type='course' AND siakad_id = :key LIMIT 1";
					$stmtMoodle = $dbCheck->prepare($sqlMoodle);
					$stmtMoodle->execute(['key' => $moodleKey]);
					$mMap = $stmtMoodle->fetch(PDO::FETCH_ASSOC);

					if ($mMap) {
						$moodleLink = "https://learning.poltekindonusa.ac.id/course/view.php?id=" . $mMap['moodle_id'];
						$moodleStatus = "Tersedia";
					}
					$dbCheck = null; // Close connection
				} catch (Exception $ex) {
					// Silent fail
				}

				$itemData->learning_link = $moodleLink;
				$itemData->learning_status = $moodleStatus;
				// --- END MODIFICATION ---

				$itemData->dosen_pengampu = $nama_pengampu;
				$itemData->id = $index;
				array_push($aData, $itemData);
				$index++;
			} else {
				$itemData->dosen_pengampu = "-";
				$itemData->id = $index;
				array_push($aData, $itemData);
				$index++;
			}
		}

		echo json_encode(array("data" => $aData, "spans" => $aSpan));
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}

	// UPDATE ANDRE 24012024 
} else if ($aksi == "tampilW") {
	$xid_reg_pd = $_SESSION['xid_reg_pd'];
	if (strlen($id) == 5) {
		$id_smt = $id;
	} else {
		$id_smt = $_SESSION['id_smt_aktif'];
	}

	//$perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt, concat(gelar_depan,nm_ptk,gelar_belakang) as dosen_pengampu from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai, wsia_dosen where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and pengampu = id_ptk and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";

	$perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt from wsia_kelas_kuliah, wsia_mata_kuliah, wsia_nilai where wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";

	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$aData = array();
		$aSpan = array();
		$index = 0;
		foreach ($data as $itemData) {

			$tahun1 = substr($itemData->id_smt, 0, 4);
			$tahun2 = $tahun1 + 1;
			$smt = substr($itemData->id_smt, 4, 1);
			if ($smt == "1") {
				$vsmt = "Ganjil";
			} else if ($smt == "2") {
				$vsmt = "Genap";
			} else {
				$vsmt = "Pendek";
			}

			$itemData->vid_smt = $tahun1 . "/" . $tahun2 . " " . $vsmt;

			$adaAgama = strpos(strtolower($itemData->nm_mk), "agama");
			$itemData->agama = $adaAgama;

			$vid_kls = $itemData->vid_kls;

			$sqlPengampu = "select xid_ajar,xid_ptk,xid_reg_ptk,concat(gelar_depan,nm_ptk,', ',gelar_belakang) as dosen_pengampu from wsia_ajar_dosen,wsia_dosen,wsia_dosen_pt where wsia_ajar_dosen.id_reg_ptk=wsia_dosen_pt.xid_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_ajar_dosen.id_kls='$vid_kls' ";

			try {
				$db = koneksi();
				$qryPengampu = $db->prepare($sqlPengampu);
				$qryPengampu->execute();
				$dataPengampu = $qryPengampu->fetchAll(PDO::FETCH_OBJ);
				$jPengampu = $qryPengampu->rowCount();
				$db = null;
			} catch (PDOException $salah) {
				exit(json_encode($salah->getMessage()));
			}

			if ($jPengampu > 0) {
				$aItemData = array();
				$id_nilai = $itemData->id_nilai;
				$vid_kls = $itemData->vid_kls;
				$nm_kls = $itemData->nm_kls;
				$kode_mk = $itemData->kode_mk;
				$nm_mk = $itemData->nm_mk;
				$vsks_mk = $itemData->vsks_mk;
				$vsks_tm = $itemData->vsks_tm;
				$vsks_prak = $itemData->vsks_prak;
				$vsks_prak_lap = $itemData->vsks_prak_lap;
				$id_smt = $itemData->id_smt;
				$vid_smt = $itemData->vid_smt;
				$agama = $itemData->agama;

				$iPengampu = 0;
				$nama_pengampu = "";
				foreach ($dataPengampu as $itemPengampu) {
					$aItemData['id_nilai'] = $id_nilai;
					$aItemData['vid_kls'] = $vid_kls;
					$aItemData['nm_kls'] = $nm_kls;
					$aItemData['kode_mk'] = $kode_mk;
					$aItemData['nm_mk'] = $nm_mk;
					$aItemData['vsks_mk'] = $vsks_mk;
					$aItemData['vsks_tm'] = $vsks_tm;
					$aItemData['vsks_prak'] = $vsks_prak;
					$aItemData['vsks_prak_lap'] = $vsks_prak_lap;
					$aItemData['id_smt'] = $id_smt;
					$aItemData['vid_smt'] = $vid_smt;
					$aItemData['agama'] = $agama;
					$aItemData['dosen_pengampu'] = $itemPengampu->dosen_pengampu;
					$aItemData['id'] = $index;

					//array_push($aData,$aItemData);

					$nama_pengampu .= $itemPengampu->dosen_pengampu;

					$iPengampu++;
					if ($jPengampu > 1 && $iPengampu < $jPengampu) {
						$aSpan[] = array($index, "index", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "nm_kls", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "kode_mk", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "nm_mk", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_mk", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_tm", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_prak", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vsks_prak_lap", 1, $jPengampu, "", "");
						$aSpan[] = array($index, "vid_smt", 1, $jPengampu, "", "");

						$nama_pengampu .= "<br>";
					}

					//$index++;
				}


				$itemData->dosen_pengampu = $nama_pengampu;
				$itemData->id = $index;
				array_push($aData, $itemData);
				$index++;
			} else {
				$itemData->dosen_pengampu = "-";
				$itemData->id = $index;
				array_push($aData, $itemData);
				$index++;
			}
		}

		echo json_encode(array("data" => $aData, "spans" => $aSpan));
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}
} else if ($aksi == "tampilSpan") {
	$id_smt = $_SESSION['id_smt_aktif'];
	$xid_reg_pd = $_SESSION['xid_reg_pd'];

	$perintah = "select nilai_tampil, id_nilai, wsia_nilai.xid_kls as vid_kls, nm_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' and wsia_kelas_kuliah.id_smt='$id_smt' and nilai_tampil = '3'";
	//  $perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt from wsia_kelas_kuliah, wsia_mata_kuliah, wsia_nilai where  wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";

	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$aSpan = array();
		$index = 0;
		foreach ($data as $itemData) {

			$vid_kls = $itemData->vid_kls;

			$sqlPengampu = "select xid_ajar from wsia_ajar_dosen,wsia_dosen,wsia_dosen_pt where wsia_ajar_dosen.id_reg_ptk=wsia_dosen_pt.xid_reg_ptk and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk and wsia_ajar_dosen.id_kls='$vid_kls' ";

			try {
				$db = koneksi();
				$qryPengampu = $db->prepare($sqlPengampu);
				$qryPengampu->execute();
				$dataPengampu = $qryPengampu->fetchAll(PDO::FETCH_OBJ);
				$jPengampu = $qryPengampu->rowCount();
				$db = null;
			} catch (PDOException $salah) {
				exit(json_encode($salah->getMessage()));
			}

			if ($jPengampu > 1) {
				$aSpan[] = array($index, "index", 1, $jPengampu);
				$aSpan[] = array($index, "nm_kls", 1, $jPengampu);
				$aSpan[] = array($index, "kode_mk", 1, $jPengampu);
				$aSpan[] = array($index, "nm_mk", 1, $jPengampu);
				$aSpan[] = array($index, "vsks_mk", 1, $jPengampu);
				$aSpan[] = array($index, "vsks_tm", 1, $jPengampu);
				$aSpan[] = array($index, "vsks_prak", 1, $jPengampu);
				$aSpan[] = array($index, "vsks_prak_lap", 1, $jPengampu);
				$aSpan[] = array($index, "vid_smt", 1, $jPengampu);
				$index++;
			} else {

				$index++;
			}
		}

		echo json_encode(array($aSpan));
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}
} else if ($aksi == "tambah") {
	$xid_reg_pd = $_SESSION['xid_reg_pd'];
	$dataKelas = $data->kelas;
	$gagal = 0;
	$updated_at = date("Y-m-d H:i:s");
	//print_r($dataKelas);
	foreach ($dataKelas as $id_kls) {
		$id_nilai = md5($id_kls . $xid_reg_pd);
		//echo $id_nilai."<br>";
		$qryKrs = "insert ignore into wsia_nilai (id_nilai,xid_kls,xid_reg_pd,asal_data,updated_at) values('$id_nilai','$id_kls','$xid_reg_pd','9','$updated_at')";
		try {
			$db = koneksi();
			$eksekusi = $db->query($qryKrs);
			$db = null;
		} catch (PDOException $salah) {
			$gagal = 1;
		}
	}

	if ($gagal) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Proses Simpan KRS Tidak Selesai";
	} else {
		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil Simpan";
	}

	echo json_encode($hasil);
} else if ($aksi == "hapus") {
	$id_nilai = $data->id_nilai;
	$sql = "delete from wsia_nilai where id_nilai='$id_nilai' and id_kls='' and id_reg_pd=''";
	try {
		$db = koneksi();
		$eksekusi = $db->query($sql);
		$db = null;
		if ($eksekusi->rowCount() > 0) {
			$hasil['berhasil'] = 1;
			$hasil['pesan'] = "Berhasil hapus";
		} else {
			$hasil['berhasil'] = 0;
			$hasil['pesan'] = "KRS tidak bisa dihapus";
		}
		echo json_encode($hasil);
	} catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Hapus. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}
} else if ($aksi == "tampilKhs") {
	if (strlen($id) == 5) {
		$id_smt = $id;
	} else {
		$id_smt = $_SESSION['id_smt_aktif'];
	}

	$xid_reg_pd = $_SESSION['xid_reg_pd'];

	/*
	 $perintah = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt, concat(gelar_depan,nm_ptk,gelar_belakang) as dosen_pengampu,nilai_angka,nilai_huruf,nilai_indeks from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai, wsia_dosen where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_kelas_kuliah.id_smt='$id_smt' and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls and pengampu = id_ptk and wsia_nilai.xid_reg_pd='$xid_reg_pd' order by kode_mk asc";
	 */
	//17-9-2019
	//  $perintah = "select id_nilai, wsia_nilai.nilai_tampil as akses, wsia_nilai.xid_kls as vid_kls, nm_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt,nilai_angka,nilai_huruf,nilai_indeks from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where  wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' and wsia_kelas_kuliah.id_smt='$id_smt'";

	// 25 01 2023
	$perintah = "select id_nilai, wsia_nilai.nilai_tampil as akses, IF(wsia_nilai.nilai_tampil = '3', nilai_angka, '0.00') as nilai_angka, IF(wsia_nilai.nilai_tampil = '3', nilai_huruf, '') as nilai_huruf, IF(wsia_nilai.nilai_tampil = '3', nilai_indeks, '0.00') as nilai_indeks, wsia_nilai.xid_kls as vid_kls, nm_kls, wsia_nilai.xid_reg_pd as vid_reg_pd, nipd, nm_pd,xid_sms,nm_jenj_didik,nm_lemb,pa,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt from wsia_nilai,wsia_mahasiswa,wsia_mahasiswa_pt,wsia_kelas_kuliah,wsia_mata_kuliah,wsia_sms,wsia_jenjang_pendidikan where  wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk and wsia_mahasiswa_pt.xid_reg_pd='$xid_reg_pd' and wsia_kelas_kuliah.id_smt='$id_smt'";
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$aData = array();
		foreach ($data as $itemData) {
			$itemData->sksXindeks = $itemData->vsks_mk * $itemData->nilai_indeks;
			array_push($aData, $itemData);
		}

		echo json_encode($aData);
	} catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}
}
