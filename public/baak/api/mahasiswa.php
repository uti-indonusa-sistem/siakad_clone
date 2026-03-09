<?php
error_reporting(0);
if (!isset($key)) {
	exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaADMIN']) {
	exit();
}

if ($aksi == "tampil") {

	$perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik ";

	$perintah .= isset($_GET['filter']['nm_pd']) ? " and nm_pd like '%" . $_GET['filter']['nm_pd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['nipd']) ? " and nipd like '%" . $_GET['filter']['nipd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['jk']) ? " and jk like '%" . $_GET['filter']['jk'] . "%'" : "";
	$perintah .= isset($_GET['filter']['tgl_lahir']) ? " and tgl_lahir like '%" . $_GET['filter']['tgl_lahir'] . "%'" : "";
	$perintah .= isset($_GET['filter']['kelas']) ? " and kelas like '%" . $_GET['filter']['kelas'] . "%'" : "";

	if (isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb'] != "") {
		$nm_lemb = explode(" - ", $_GET['filter']['vnm_lemb']);
		$nm_jenj_didik = $nm_lemb[0];
		$nm_lemb = $nm_lemb[1];
		$perintah .= " and nm_jenj_didik like '%" . $nm_jenj_didik . "%'";
		$perintah .= " and nm_lemb like '%" . $nm_lemb . "%'";
	}

	if (isset($_GET['filter']['vid_jns_daftar'])) {
		if ($_GET['filter']['vid_jns_daftar'] == "Mahasiswa Baru") {
			$perintah .= " and id_jns_daftar = '1'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "Pindahan/Transfer") {
			$perintah .= " and id_jns_daftar = '2'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "RPL Perolehan SKS") {
			$perintah .= " and id_jns_daftar = '13'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "RPL Transfer SKS / Karyawan") {
			$perintah .= " and id_jns_daftar = '16'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "Mahasiswa K2") {
			$perintah .= " and id_jns_daftar = '17'";
		}
	}

	$perintah .= " order by mulai_smt desc, nipd desc";

	$perintah .= isset($_GET['count']) ? ' LIMIT ' . $_GET['count'] : ' LIMIT 20';
	$perintah .= isset($_GET['start']) ? ' OFFSET ' . $_GET['start'] : ' OFFSET 0';

	//echo $perintah;
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);

		$dataA = array();
		foreach ($data as $itemData) {
			$id_jns_daftar = $itemData->id_jns_daftar;
			$vid_jns_daftar = "Lainnya";
			if ($id_jns_daftar == "1") {
				$vid_jns_daftar = "Mahasiswa Baru";
			}
			else if ($id_jns_daftar == "2") {
				$vid_jns_daftar = "Pindahan/Transfer";
			}
			else if ($id_jns_daftar == "13") {
				$vid_jns_daftar = "RPL Perolehan SKS";
			}
			else if ($id_jns_daftar == "16") {
				$vid_jns_daftar = "RPL Transfer SKS / Karyawan";
			}
			else if ($id_jns_daftar == "17") {
				$vid_jns_daftar = "Mahasiswa K2";
			}
			$itemData->vid_jns_daftar = $vid_jns_daftar;
			$itemData->no_pend = $itemData->xid_pd;
			$itemData->id_sms = $itemData->xid_sms;
			$itemData->vnm_lemb = $itemData->nm_jenj_didik . " - " . $itemData->nm_lemb;
			$itemData->vnm_ibu_kandung = $itemData->nm_ibu_kandung;

			$id_kk = $itemData->id_kk;
			$qryKKmhs = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk'";
			$eksekusiMhs = $db->query($qryKKmhs);
			$dataKKmhs = $eksekusiMhs->fetch(PDO::FETCH_OBJ);
			if ($dataKKmhs) {
				$aKKmhs = get_object_vars($dataKKmhs);
				foreach ($aKKmhs as $key => $nilai) {
					$keyMhs = "mhs_" . $key;
					$itemData->$keyMhs = $nilai;
				}
			}

			$id_kk_ayah = $itemData->id_kebutuhan_khusus_ayah;
			$qryKKayah = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ayah'";
			$eksekusiAyah = $db->query($qryKKayah);
			$dataKKayah = $eksekusiAyah->fetch(PDO::FETCH_OBJ);
			if ($dataKKayah) {
				$aKKayah = get_object_vars($dataKKayah);
				foreach ($aKKayah as $key => $nilai) {
					$keyAyah = "ayah_" . $key;
					$itemData->$keyAyah = $nilai;
				}
			}

			$id_kk_ibu = $itemData->id_kebutuhan_khusus_ibu;
			$qryKKibu = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ibu'";
			$eksekusiIbu = $db->query($qryKKibu);
			$dataKKibu = $eksekusiIbu->fetch(PDO::FETCH_OBJ);
			if ($dataKKibu) {
				$aKKibu = get_object_vars($dataKKibu);
				foreach ($aKKibu as $key => $nilai) {
					$keyIbu = "ibu_" . $key;
					$itemData->$keyIbu = $nilai;
				}
			}

			array_push($dataA, $itemData);
		}

		echo json_encode($dataA);
		$db = null;
	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}
}
else if ($aksi == "tambah") {

	$no_pend = clean($data->no_pend);
	$nm_pd = clean($data->nm_pd);
	$tmpt_lahir = clean($data->tmpt_lahir);
	$tgl_lahir = clean($data->tgl_lahir);
	$tgl = substr($tgl_lahir, 0, 2);
	$bln = substr($tgl_lahir, 3, 2);
	$thn = substr($tgl_lahir, 6, 4);
	$vtgl_lahir = $thn . "/" . $bln . "/" . $tgl;

	$jk = clean($data->jk);
	$id_agama = clean($data->id_agama);
	$nik = clean($data->nik);
	//$negara		= clean($data->negara);
	$negara = "ID";
	$jln = clean($data->jln);
	$nm_dsn = clean($data->nm_dsn);
	$rt = clean($data->rt);
	$rw = clean($data->rw);
	$ds_kel = clean($data->ds_kel);
	$kode_pos = clean($data->kode_pos);
	$id_wil = clean($data->id_wil);
	$id_jns_tinggal = clean($data->id_jns_tinggal);
	$telepon_rumah = clean($data->telepon_rumah);
	$telepon_seluler = clean($data->telepon_seluler);
	$email = clean($data->email);
	$a_terima_kps = clean($data->a_terima_kps);
	$no_kps = $data->no_kps;
	$nm_ayah = clean($data->nm_ayah);
	$tgl_lahir_ayah = clean($data->tgl_lahir_ayah);
	$tgl = substr($tgl_lahir_ayah, 0, 2);
	$bln = substr($tgl_lahir_ayah, 3, 2);
	$thn = substr($tgl_lahir_ayah, 6, 4);
	$vtgl_lahir_ayah = $thn . "/" . $bln . "/" . $tgl;

	$id_jenjang_pendidikan_ayah = clean($data->id_jenjang_pendidikan_ayah);
	$id_pekerjaan_ayah = clean($data->id_pekerjaan_ayah);
	$id_penghasilan_ayah = clean($data->id_penghasilan_ayah);
	$nm_ibu_kandung = clean($data->nm_ibu_kandung);
	$tgl_lahir_ibu = clean($data->tgl_lahir_ibu);
	$tgl = substr($tgl_lahir_ibu, 0, 2);
	$bln = substr($tgl_lahir_ibu, 3, 2);
	$thn = substr($tgl_lahir_ibu, 6, 4);
	$vtgl_lahir_ibu = $thn . "/" . $bln . "/" . $tgl;

	$id_jenjang_pendidikan_ibu = clean($data->id_jenjang_pendidikan_ibu);
	$id_pekerjaan_ibu = clean($data->id_pekerjaan_ibu);
	$id_penghasilan_ibu = clean($data->id_penghasilan_ibu);
	$nm_wali = clean($data->nm_wali);
	$tgl_lahir_wali = clean($data->tgl_lahir_wali);
	$tgl = substr($tgl_lahir_wali, 0, 2);
	$bln = substr($tgl_lahir_wali, 3, 2);
	$thn = substr($tgl_lahir_wali, 6, 4);
	$vtgl_lahir_wali = $thn . "/" . $bln . "/" . $tgl;

	$id_jenjang_pendidikan_wali = clean($data->id_jenjang_pendidikan_wali);
	$id_pekerjaan_wali = clean($data->id_pekerjaan_wali);
	$id_penghasilan_wali = clean($data->id_penghasilan_wali);

	$mhs_a_kk_a = $data->mhs_a_kk_a;
	$mhs_a_kk_b = $data->mhs_a_kk_b;
	$mhs_a_kk_c = $data->mhs_a_kk_c;
	$mhs_a_kk_c1 = $data->mhs_a_kk_c1;
	$mhs_a_kk_d = $data->mhs_a_kk_d;
	$mhs_a_kk_d1 = $data->mhs_a_kk_d1;
	$mhs_a_kk_e = $data->mhs_a_kk_e;
	$mhs_a_kk_f = $data->mhs_a_kk_f;
	$mhs_a_kk_h = $data->mhs_a_kk_h;
	$mhs_a_kk_i = $data->mhs_a_kk_i;
	$mhs_a_kk_j = $data->mhs_a_kk_j;
	$mhs_a_kk_k = $data->mhs_a_kk_k;
	$mhs_a_kk_n = $data->mhs_a_kk_n;
	$mhs_a_kk_o = $data->mhs_a_kk_o;
	$mhs_a_kk_p = $data->mhs_a_kk_p;
	$mhs_a_kk_q = $data->mhs_a_kk_q;

	$ayah_a_kk_a = $data->ayah_a_kk_a;
	$ayah_a_kk_b = $data->ayah_a_kk_b;
	$ayah_a_kk_c = $data->ayah_a_kk_c;
	$ayah_a_kk_c1 = $data->ayah_a_kk_c1;
	$ayah_a_kk_d = $data->ayah_a_kk_d;
	$ayah_a_kk_d1 = $data->ayah_a_kk_d1;
	$ayah_a_kk_e = $data->ayah_a_kk_e;
	$ayah_a_kk_f = $data->ayah_a_kk_f;
	$ayah_a_kk_h = $data->ayah_a_kk_h;
	$ayah_a_kk_i = $data->ayah_a_kk_i;
	$ayah_a_kk_j = $data->ayah_a_kk_j;
	$ayah_a_kk_k = $data->ayah_a_kk_k;
	$ayah_a_kk_n = $data->ayah_a_kk_n;
	$ayah_a_kk_o = $data->ayah_a_kk_o;
	$ayah_a_kk_p = $data->ayah_a_kk_p;
	$ayah_a_kk_q = $data->ayah_a_kk_q;

	$ibu_a_kk_a = $data->ibu_a_kk_a;
	$ibu_a_kk_b = $data->ibu_a_kk_b;
	$ibu_a_kk_c = $data->ibu_a_kk_c;
	$ibu_a_kk_c1 = $data->ibu_a_kk_c1;
	$ibu_a_kk_d = $data->ibu_a_kk_d;
	$ibu_a_kk_d1 = $data->ibu_a_kk_d1;
	$ibu_a_kk_e = $data->ibu_a_kk_e;
	$ibu_a_kk_f = $data->ibu_a_kk_f;
	$ibu_a_kk_h = $data->ibu_a_kk_h;
	$ibu_a_kk_i = $data->ibu_a_kk_i;
	$ibu_a_kk_j = $data->ibu_a_kk_j;
	$ibu_a_kk_k = $data->ibu_a_kk_k;
	$ibu_a_kk_n = $data->ibu_a_kk_n;
	$ibu_a_kk_o = $data->ibu_a_kk_o;
	$ibu_a_kk_p = $data->ibu_a_kk_p;
	$ibu_a_kk_q = $data->ibu_a_kk_q;

	$qryKKmhs = "select * from wsia_kebutuhan_khusus where a_kk_a='$mhs_a_kk_a' and a_kk_b='$mhs_a_kk_b' and a_kk_c='$mhs_a_kk_c' and a_kk_c1='$mhs_a_kk_c1' and a_kk_d='$mhs_a_kk_d' and a_kk_d1='$mhs_a_kk_d1' and a_kk_e='$mhs_a_kk_e' and a_kk_f='$mhs_a_kk_f' and a_kk_h='$mhs_a_kk_h' and a_kk_i='$mhs_a_kk_i' and a_kk_j='$mhs_a_kk_j' and a_kk_k='$mhs_a_kk_k' and a_kk_n='$mhs_a_kk_n' and a_kk_o='$mhs_a_kk_o' and a_kk_p='$mhs_a_kk_p' and a_kk_q='$mhs_a_kk_q'";

	$qryKKayah = "select * from wsia_kebutuhan_khusus where a_kk_a='$ayah_a_kk_a' and a_kk_b='$ayah_a_kk_b' and a_kk_c='$ayah_a_kk_c' and a_kk_c1='$ayah_a_kk_c1' and a_kk_d='$ayah_a_kk_d' and a_kk_d1='$ayah_a_kk_d1' and a_kk_e='$ayah_a_kk_e' and a_kk_f='$ayah_a_kk_f' and a_kk_h='$ayah_a_kk_h' and a_kk_i='$ayah_a_kk_i' and a_kk_j='$ayah_a_kk_j' and a_kk_k='$ayah_a_kk_k' and a_kk_n='$ayah_a_kk_n' and a_kk_o='$ayah_a_kk_o' and a_kk_p='$ayah_a_kk_p' and a_kk_q='$ayah_a_kk_q'";

	$qryKKibu = "select * from wsia_kebutuhan_khusus where a_kk_a='$ibu_a_kk_a' and a_kk_b='$ibu_a_kk_b' and a_kk_c='$ibu_a_kk_c' and a_kk_c1='$ibu_a_kk_c1' and a_kk_d='$ibu_a_kk_d' and a_kk_d1='$ibu_a_kk_d1' and a_kk_e='$ibu_a_kk_e' and a_kk_f='$ibu_a_kk_f' and a_kk_h='$ibu_a_kk_h' and a_kk_i='$ibu_a_kk_i' and a_kk_j='$ibu_a_kk_j' and a_kk_k='$ibu_a_kk_k' and a_kk_n='$ibu_a_kk_n' and a_kk_o='$ibu_a_kk_o' and a_kk_p='$ibu_a_kk_p' and a_kk_q='$ibu_a_kk_q'";

	$qrySP = "select * from wsia_satuan_pendidikan where npsn='" . NPSN . "' ";

	try {
		$db = koneksi();
		$eksekusiMhs = $db->query($qryKKmhs);
		$dataKKmhs = $eksekusiMhs->fetch(PDO::FETCH_OBJ);
		$id_kk = $dataKKmhs->id_kk;

		$eksekusiAyah = $db->query($qryKKayah);
		$dataKKayah = $eksekusiAyah->fetch(PDO::FETCH_OBJ);
		$id_kk_ayah = $dataKKayah->id_kk;

		$eksekusiIbu = $db->query($qryKKibu);
		$dataKKibu = $eksekusiIbu->fetch(PDO::FETCH_OBJ);
		$id_kk_ibu = $dataKKibu->id_kk;

		$eksekusiSP = $db->query($qrySP);
		$dataSP = $eksekusiSP->fetch(PDO::FETCH_OBJ);
		$id_sp = $dataSP->id_sp;

		$updated_at = date("Y-m-d H:i:s");


		$qryMhs = "insert into wsia_mahasiswa values ('$no_pend','','$nm_pd','$jk','0','$nik','$tmpt_lahir','$tgl_lahir','$id_agama','$id_kk','$id_sp','$jln','$rt','$rw','$nm_dsn','$ds_kel','$id_wil','$kode_pos','$id_jns_tinggal','0','$telepon_rumah','$telepon_seluler','$email','$a_terima_kps','$no_kps','A','$nm_ayah','$tgl_lahir_ayah','$id_jenjang_pendidikan_ayah','$id_pekerjaan_ayah','$id_penghasilan_ayah','$id_kk_ayah','$nm_ibu_kandung','$tgl_lahir_ibu','$id_jenjang_pendidikan_ibu','$id_penghasilan_ibu','$id_pekerjaan_ibu','$id_kk_ibu','$nm_wali','$tgl_lahir_wali','$id_jenjang_pendidikan_wali','$id_pekerjaan_wali','$id_penghasilan_wali','ID','$updated_at',null,'') ";
		try {

			$db->beginTransaction();
			$eksekusi = $db->query($qryMhs);

			if ($eksekusi) {
				$nipd = clean($data->nipd);
				$id_sms = clean($data->id_sms);
				$mulai_smt = clean($data->mulai_smt);
				$id_jns_daftar = clean($data->id_jns_daftar);
				$kelas = clean($data->kelas);
				$pa = clean($data->pa);
				$pass = sha1(md5(clean($no_pend)) . $nipd);
				$tgl_masuk_sp = clean($data->tgl_masuk_sp);
				$id_pembiayaan = empty($data->id_pembiayaan) ? 0 : clean($data->id_pembiayaan);
				$biaya_masuk = empty($data->biaya_masuk) ? 0 : clean($data->biaya_masuk);

				$qryMahasiswaPT = "insert into wsia_mahasiswa_pt (xid_reg_pd,id_pd,id_sms,nipd,mulai_smt,id_jns_daftar,kelas,pass,pa,tgl_masuk_sp,id_pembiayaan,biaya_masuk) values('$no_pend','$no_pend','$id_sms','$nipd','$mulai_smt','$id_jns_daftar','$kelas','$pass','$pa','$tgl_masuk_sp','$id_pembiayaan','$biaya_masuk') ";

				$eksekusiPt = $db->query($qryMahasiswaPT);

				if ($eksekusiPt) {
					$db->commit();
					$hasil['berhasil'] = 1;
					$hasil['pesan'] = "Berhasil Simpan";
				}
				else {
					$db->rollBack();
					$hasil['berhasil'] = 0;
					$hasil['pesan'] = "Gagal Simpan Mahasiswa PT";
				}

				echo json_encode($hasil);
			}
			else {
				$db->rollBack();
				$hasil['berhasil'] = 0;
				$hasil['pesan'] = "Gagal Simpan Mahasiswa";
				echo json_encode($hasil);
			}

			$db = null;

		}
		catch (PDOException $salah) {
			$db->rollBack();
			$hasil['berhasil'] = 0;
			$hasil['pesan'] = "Gagal Simpan. Kesalahan:<br>" . $salah->getMessage();
			echo json_encode($hasil);
		}

	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal mengambil data Kebutuhan Khusus. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}


}
else if ($aksi == "ubah") {
	$xid_pd = clean($data->xid_pd);
	$no_pend = clean($data->no_pend);
	$nm_pd = clean($data->nm_pd);
	$tmpt_lahir = clean($data->tmpt_lahir);
	$tgl_lahir = clean($data->tgl_lahir);
	$tgl = substr($tgl_lahir, 0, 2);
	$bln = substr($tgl_lahir, 3, 2);
	$thn = substr($tgl_lahir, 6, 4);
	$vtgl_lahir = $thn . "/" . $bln . "/" . $tgl;

	$jk = clean($data->jk);
	$id_agama = clean($data->id_agama);
	$no_kk = clean($data->no_kk);
	$nik = clean($data->nik);
	//$negara		= clean($data->negara);
	$negara = "ID";
	$jln = clean($data->jln);
	$nm_dsn = clean($data->nm_dsn);
	$rt = clean($data->rt);
	$rw = clean($data->rw);
	$ds_kel = clean($data->ds_kel);
	$kode_pos = clean($data->kode_pos);
	$id_wil = clean($data->id_wil);
	$id_jns_tinggal = clean($data->id_jns_tinggal);
	$telepon_rumah = clean($data->telepon_rumah);
	$telepon_seluler = clean($data->telepon_seluler);
	$email = clean($data->email);
	$a_terima_kps = clean($data->a_terima_kps);
	$no_kps = $data->no_kps;
	$nm_ayah = clean($data->nm_ayah);
	$tgl_lahir_ayah = clean($data->tgl_lahir_ayah);
	$tgl = substr($tgl_lahir_ayah, 0, 2);
	$bln = substr($tgl_lahir_ayah, 3, 2);
	$thn = substr($tgl_lahir_ayah, 6, 4);
	$vtgl_lahir_ayah = $thn . "/" . $bln . "/" . $tgl;

	$id_jenjang_pendidikan_ayah = clean($data->id_jenjang_pendidikan_ayah);
	$id_pekerjaan_ayah = clean($data->id_pekerjaan_ayah);
	$id_penghasilan_ayah = clean($data->id_penghasilan_ayah);
	$nm_ibu_kandung = clean($data->nm_ibu_kandung);
	$tgl_lahir_ibu = clean($data->tgl_lahir_ibu);
	$tgl = substr($tgl_lahir_ibu, 0, 2);
	$bln = substr($tgl_lahir_ibu, 3, 2);
	$thn = substr($tgl_lahir_ibu, 6, 4);
	$vtgl_lahir_ibu = $thn . "/" . $bln . "/" . $tgl;

	$id_jenjang_pendidikan_ibu = clean($data->id_jenjang_pendidikan_ibu);
	$id_pekerjaan_ibu = clean($data->id_pekerjaan_ibu);
	$id_penghasilan_ibu = clean($data->id_penghasilan_ibu);
	$nm_wali = clean($data->nm_wali);
	$tgl_lahir_wali = clean($data->tgl_lahir_wali);
	$tgl = substr($tgl_lahir_wali, 0, 2);
	$bln = substr($tgl_lahir_wali, 3, 2);
	$thn = substr($tgl_lahir_wali, 6, 4);
	$vtgl_lahir_wali = $thn . "/" . $bln . "/" . $tgl;

	$id_jenjang_pendidikan_wali = clean($data->id_jenjang_pendidikan_wali);
	$id_pekerjaan_wali = clean($data->id_pekerjaan_wali);
	$id_penghasilan_wali = clean($data->id_penghasilan_wali);

	$mhs_a_kk_a = $data->mhs_a_kk_a;
	$mhs_a_kk_b = $data->mhs_a_kk_b;
	$mhs_a_kk_c = $data->mhs_a_kk_c;
	$mhs_a_kk_c1 = $data->mhs_a_kk_c1;
	$mhs_a_kk_d = $data->mhs_a_kk_d;
	$mhs_a_kk_d1 = $data->mhs_a_kk_d1;
	$mhs_a_kk_e = $data->mhs_a_kk_e;
	$mhs_a_kk_f = $data->mhs_a_kk_f;
	$mhs_a_kk_h = $data->mhs_a_kk_h;
	$mhs_a_kk_i = $data->mhs_a_kk_i;
	$mhs_a_kk_j = $data->mhs_a_kk_j;
	$mhs_a_kk_k = $data->mhs_a_kk_k;
	$mhs_a_kk_n = $data->mhs_a_kk_n;
	$mhs_a_kk_o = $data->mhs_a_kk_o;
	$mhs_a_kk_p = $data->mhs_a_kk_p;
	$mhs_a_kk_q = $data->mhs_a_kk_q;

	$ayah_a_kk_a = $data->ayah_a_kk_a;
	$ayah_a_kk_b = $data->ayah_a_kk_b;
	$ayah_a_kk_c = $data->ayah_a_kk_c;
	$ayah_a_kk_c1 = $data->ayah_a_kk_c1;
	$ayah_a_kk_d = $data->ayah_a_kk_d;
	$ayah_a_kk_d1 = $data->ayah_a_kk_d1;
	$ayah_a_kk_e = $data->ayah_a_kk_e;
	$ayah_a_kk_f = $data->ayah_a_kk_f;
	$ayah_a_kk_h = $data->ayah_a_kk_h;
	$ayah_a_kk_i = $data->ayah_a_kk_i;
	$ayah_a_kk_j = $data->ayah_a_kk_j;
	$ayah_a_kk_k = $data->ayah_a_kk_k;
	$ayah_a_kk_n = $data->ayah_a_kk_n;
	$ayah_a_kk_o = $data->ayah_a_kk_o;
	$ayah_a_kk_p = $data->ayah_a_kk_p;
	$ayah_a_kk_q = $data->ayah_a_kk_q;

	$ibu_a_kk_a = $data->ibu_a_kk_a;
	$ibu_a_kk_b = $data->ibu_a_kk_b;
	$ibu_a_kk_c = $data->ibu_a_kk_c;
	$ibu_a_kk_c1 = $data->ibu_a_kk_c1;
	$ibu_a_kk_d = $data->ibu_a_kk_d;
	$ibu_a_kk_d1 = $data->ibu_a_kk_d1;
	$ibu_a_kk_e = $data->ibu_a_kk_e;
	$ibu_a_kk_f = $data->ibu_a_kk_f;
	$ibu_a_kk_h = $data->ibu_a_kk_h;
	$ibu_a_kk_i = $data->ibu_a_kk_i;
	$ibu_a_kk_j = $data->ibu_a_kk_j;
	$ibu_a_kk_k = $data->ibu_a_kk_k;
	$ibu_a_kk_n = $data->ibu_a_kk_n;
	$ibu_a_kk_o = $data->ibu_a_kk_o;
	$ibu_a_kk_p = $data->ibu_a_kk_p;
	$ibu_a_kk_q = $data->ibu_a_kk_q;

	// Update 1 10 2024
	if ($data->nisn == '0' || empty($data->nisn)) {
		$nisn = '0000000000';
	}
	else {
		$nisn = $data->nisn;
	}

	$qryKKmhs = "select * from wsia_kebutuhan_khusus where a_kk_a='$mhs_a_kk_a' and a_kk_b='$mhs_a_kk_b' and a_kk_c='$mhs_a_kk_c' and a_kk_c1='$mhs_a_kk_c1' and a_kk_d='$mhs_a_kk_d' and a_kk_d1='$mhs_a_kk_d1' and a_kk_e='$mhs_a_kk_e' and a_kk_f='$mhs_a_kk_f' and a_kk_h='$mhs_a_kk_h' and a_kk_i='$mhs_a_kk_i' and a_kk_j='$mhs_a_kk_j' and a_kk_k='$mhs_a_kk_k' and a_kk_n='$mhs_a_kk_n' and a_kk_o='$mhs_a_kk_o' and a_kk_p='$mhs_a_kk_p' and a_kk_q='$mhs_a_kk_q'";

	$qryKKayah = "select * from wsia_kebutuhan_khusus where a_kk_a='$ayah_a_kk_a' and a_kk_b='$ayah_a_kk_b' and a_kk_c='$ayah_a_kk_c' and a_kk_c1='$ayah_a_kk_c1' and a_kk_d='$ayah_a_kk_d' and a_kk_d1='$ayah_a_kk_d1' and a_kk_e='$ayah_a_kk_e' and a_kk_f='$ayah_a_kk_f' and a_kk_h='$ayah_a_kk_h' and a_kk_i='$ayah_a_kk_i' and a_kk_j='$ayah_a_kk_j' and a_kk_k='$ayah_a_kk_k' and a_kk_n='$ayah_a_kk_n' and a_kk_o='$ayah_a_kk_o' and a_kk_p='$ayah_a_kk_p' and a_kk_q='$ayah_a_kk_q'";

	$qryKKibu = "select * from wsia_kebutuhan_khusus where a_kk_a='$ibu_a_kk_a' and a_kk_b='$ibu_a_kk_b' and a_kk_c='$ibu_a_kk_c' and a_kk_c1='$ibu_a_kk_c1' and a_kk_d='$ibu_a_kk_d' and a_kk_d1='$ibu_a_kk_d1' and a_kk_e='$ibu_a_kk_e' and a_kk_f='$ibu_a_kk_f' and a_kk_h='$ibu_a_kk_h' and a_kk_i='$ibu_a_kk_i' and a_kk_j='$ibu_a_kk_j' and a_kk_k='$ibu_a_kk_k' and a_kk_n='$ibu_a_kk_n' and a_kk_o='$ibu_a_kk_o' and a_kk_p='$ibu_a_kk_p' and a_kk_q='$ibu_a_kk_q'";

	$qrySP = "select * from wsia_satuan_pendidikan where npsn='" . NPSN . "' ";

	try {
		$db = koneksi();
		$eksekusiMhs = $db->query($qryKKmhs);
		$dataKKmhs = $eksekusiMhs->fetch(PDO::FETCH_OBJ);
		$id_kk = $dataKKmhs->id_kk;

		$eksekusiAyah = $db->query($qryKKayah);
		$dataKKayah = $eksekusiAyah->fetch(PDO::FETCH_OBJ);
		$id_kk_ayah = $dataKKayah->id_kk;

		$eksekusiIbu = $db->query($qryKKibu);
		$dataKKibu = $eksekusiIbu->fetch(PDO::FETCH_OBJ);
		$id_kk_ibu = $dataKKibu->id_kk;

		$eksekusiSP = $db->query($qrySP);
		$dataSP = $eksekusiSP->fetch(PDO::FETCH_OBJ);
		$id_sp = $dataSP->id_sp;
		$updated_at = date("Y-m-d H:i:s");

		$db = null;

		$qryMhs = "update wsia_mahasiswa set nisn='$nisn', nm_pd='$nm_pd',jk='$jk',nik='$nik',tmpt_lahir='$tmpt_lahir',tgl_lahir='$tgl_lahir',id_agama='$id_agama',id_kk='$id_kk',id_sp='$id_sp',jln='$jln',rt='$rt',rw='$rw',nm_dsn='$nm_dsn',ds_kel='$ds_kel',id_wil='$id_wil',kode_pos='$kode_pos',id_jns_tinggal='$id_jns_tinggal',telepon_rumah='$telepon_rumah',telepon_seluler='$telepon_seluler',email='$email',a_terima_kps='$a_terima_kps',no_kps='$no_kps',nm_ayah='$nm_ayah',tgl_lahir_ayah='$tgl_lahir_ayah',id_jenjang_pendidikan_ayah='$id_jenjang_pendidikan_ayah',id_pekerjaan_ayah='$id_pekerjaan_ayah',id_penghasilan_ayah='$id_penghasilan_ayah',id_kebutuhan_khusus_ayah='$id_kk_ayah',nm_ibu_kandung='$nm_ibu_kandung',tgl_lahir_ibu='$tgl_lahir_ibu',id_jenjang_pendidikan_ibu='$id_jenjang_pendidikan_ibu',id_penghasilan_ibu='$id_penghasilan_ibu',id_pekerjaan_ibu='$id_pekerjaan_ibu',id_kebutuhan_khusus_ibu='$id_kk_ibu',nm_wali='$nm_wali',tgl_lahir_wali='$tgl_lahir_wali',id_jenjang_pendidikan_wali='$id_jenjang_pendidikan_wali',id_pekerjaan_wali='$id_pekerjaan_wali',id_penghasilan_wali='$id_penghasilan_wali', updated_at='$updated_at', no_kk='$no_kk' where xid_pd='$xid_pd'";
		try {
			$db = koneksi();
			$eksekusi = $db->query($qryMhs);

			if ($eksekusi) {
				$xid_reg_pd = clean($data->xid_reg_pd);
				$nipd = clean($data->nipd);
				$id_sms = clean($data->id_sms);
				$mulai_smt = clean($data->mulai_smt);
				$id_jns_daftar = clean($data->id_jns_daftar);
				$kelas = clean($data->kelas);
				$pa = clean($data->pa);
				//$pass 			= sha1(md5(clean($no_pend)).$nipd);
				$tgl_masuk_sp = clean($data->tgl_masuk_sp);
				$id_pembiayaan = empty($data->id_pembiayaan) ? 0 : clean($data->id_pembiayaan);
				$biaya_masuk = empty($data->biaya_masuk) ? 0 : clean($data->biaya_masuk);

				$qryMahasiswaPT = "update wsia_mahasiswa_pt set nipd='$nipd', id_sms='$id_sms',mulai_smt='$mulai_smt',id_jns_daftar='$id_jns_daftar',kelas='$kelas',pa='$pa', tgl_masuk_sp='$tgl_masuk_sp', id_pembiayaan='$id_pembiayaan', biaya_masuk='$biaya_masuk' where xid_reg_pd='$xid_reg_pd'";
				$db->query($qryMahasiswaPT);

				$hasil['berhasil'] = 1;
				$hasil['pesan'] = "Berhasil Ubah";

				$db = null;
				echo json_encode($hasil);
			}
			else {
				$hasil['berhasil'] = 0;
				$hasil['pesan'] = "Gagal Ubah Mahasiswa";
				echo json_encode($hasil);
			}

		}
		catch (PDOException $salah) {
			$hasil['berhasil'] = 0;
			$hasil['pesan'] = "Gagal Ubah. Kesalahan:<br>" . $salah->getMessage();
			echo json_encode($hasil);
		}

	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal mengambil data Kebutuhan Khusus. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}


}
else if ($aksi == "hapus") {
	$xid_pd = clean($data->xid_pd);
	$xid_reg_pd = clean($data->xid_reg_pd);

	try {
		$db = koneksi();
		$qryMhs = "delete from wsia_mahasiswa  where xid_pd='$xid_pd'";
		$eksekusi = $db->query($qryMhs);
		$qryMhsPT = "delete from wsia_mahasiswa_pt  where xid_reg_pd='$xid_reg_pd'";
		$eksekusi = $db->query($qryMhsPT);
		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil Hapus";
		$db = null;
		echo json_encode($hasil);

	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Hapus. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}


}
else if ($aksi == "keluar") {

	$perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan,wsia_jenis_keluar where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_mahasiswa_pt.id_jns_keluar=wsia_jenis_keluar.id_jns_keluar ";

	$perintah .= isset($_GET['filter']['nm_pd']) ? " and nm_pd like '%" . $_GET['filter']['nm_pd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['nipd']) ? " and nipd like '%" . $_GET['filter']['nipd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['jk']) ? " and jk like '%" . $_GET['filter']['jk'] . "%'" : "";
	$perintah .= isset($_GET['filter']['tgl_keluar']) ? " and tgl_keluar like '%" . $_GET['filter']['tgl_keluar'] . "%'" : "";
	$perintah .= isset($_GET['filter']['angkatan']) ? " and mulai_smt like '%" . $_GET['filter']['angkatan'] . "%'" : "";
	$perintah .= isset($_GET['filter']['ket_keluar']) ? " and ket_keluar like '%" . $_GET['filter']['ket_keluar'] . "%'" : "";

	if (isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb'] != "") {
		$nm_lemb = explode(" - ", $_GET['filter']['vnm_lemb']);
		$nm_jenj_didik = $nm_lemb[0];
		$nm_lemb = $nm_lemb[1];
		$perintah .= " and nm_jenj_didik like '%" . $nm_jenj_didik . "%'";
		$perintah .= " and nm_lemb like '%" . $nm_lemb . "%'";
	}

	//$perintah.=" order by nim ";

	$perintah .= isset($_GET['count']) ? ' LIMIT ' . $_GET['count'] : ' LIMIT 20';
	$perintah .= isset($_GET['start']) ? ' OFFSET ' . $_GET['start'] : ' OFFSET 0';

	//echo $perintah;
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);

		$dataA = array();
		foreach ($data as $itemData) {
			$id_jns_daftar = $itemData->id_jns_daftar;
			$vid_jns_daftar = "Lainnya";
			if ($id_jns_daftar == "1") {
				$vid_jns_daftar = "Mahasiswa Baru";
			}
			else if ($id_jns_daftar == "2") {
				$vid_jns_daftar = "Pindahan/Transfer";
			}
			else if ($id_jns_daftar == "13") {
				$vid_jns_daftar = "RPL Perolehan SKS";
			}
			else if ($id_jns_daftar == "16") {
				$vid_jns_daftar = "RPL Transfer SKS / Karyawan";
			}
			else if ($id_jns_daftar == "17") {
				$vid_jns_daftar = "Mahasiswa K2";
			}
			$itemData->vid_jns_daftar = $vid_jns_daftar;
			$itemData->no_pend = $itemData->xid_pd;
			$itemData->angkatan = substr($itemData->mulai_smt, 0, 4);
			$itemData->vnm_lemb = $itemData->nm_jenj_didik . " - " . $itemData->nm_lemb;

			$xid_reg_pd = $itemData->xid_reg_pd;
			$perintahAkm = "select * from wsia_kuliah_mahasiswa where xid_reg_pd='$xid_reg_pd' order by id_smt desc limit 1";
			$qryAkm = $db->prepare($perintahAkm);
			$qryAkm->execute();
			$dataAkm = $qryAkm->fetch(PDO::FETCH_OBJ);

			if ($dataAkm) {
				$itemData->id_smt = $dataAkm->id_smt;
				$itemData->ips = $dataAkm->ips;
				$itemData->sks_smt = $dataAkm->sks_smt;
				$itemData->ipk = $dataAkm->ipk;
				$itemData->sks_total = $dataAkm->sks_total;
			} else {
				$itemData->id_smt = "-";
				$itemData->ips = "0.00";
				$itemData->sks_smt = 0;
				$itemData->ipk = "0.00";
				$itemData->sks_total = 0;
			}

			array_push($dataA, $itemData);
		}

		$db = null;
		echo json_encode($dataA);

	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}


}
else if ($aksi == "pilih") {

	if (isset($_GET['filter'])) {
		$cari = $_GET['filter']['value'];
	}
	else {
		$cari = "";
	}

	$filter = " (nm_pd like '%$cari%' or nipd like '%$cari%') ";


	$perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and " . $filter . " limit 0,10";

	//echo $perintah;
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$pilih = array();
		foreach ($data as $itemData) {
			$itemData->id = $itemData->xid_reg_pd;
			$vnm_lemb = $itemData->nm_jenj_didik . " - " . $itemData->nm_lemb;
			$itemData->value = $itemData->nipd . " - " . $itemData->nm_pd . " (" . $vnm_lemb . ")";
			array_push($pilih, $itemData);
		}

		echo json_encode($pilih);

	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}


}
else if ($aksi == "pilihUbah") {
	$xid_reg_pd = $id;
	$perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar<>'' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and xid_reg_pd='$xid_reg_pd'";

	//echo $perintah;
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		$db = null;

		$pilih = array();
		foreach ($data as $itemData) {
			$itemData->id = $itemData->xid_reg_pd;
			$vnm_lemb = $itemData->nm_jenj_didik . " - " . $itemData->nm_lemb;
			$itemData->value = $itemData->nipd . " - " . $itemData->nm_pd . " (" . $vnm_lemb . ")";
			array_push($pilih, $itemData);
		}

		echo json_encode($pilih);

	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}


}
else if ($aksi == "ubahJenisKeluar") {

	$xid_reg_pd = clean($data->xid_reg_pd);
	$id_jns_keluar = clean($data->id_jns_keluar);
	$id_periode_keluar = clean($data->id_periode_keluar);
	$tgl_keluar = clean($data->tgl_keluar);
	$sk_yudisium = clean($data->sk_yudisium);
	$tgl_sk_yudisium = clean($data->tgl_sk_yudisium);
	$no_seri_ijazah = clean($data->no_seri_ijazah);
	$judul_skripsi = clean($data->judul_skripsi);
	$updated_at = date("Y-m-d H:i:s");

	try {
		$db = koneksi();

		$qryMahasiswaPT = "update wsia_mahasiswa_pt set id_jns_keluar='$id_jns_keluar', id_periode_keluar='$id_periode_keluar', tgl_keluar='$tgl_keluar', sk_yudisium='$sk_yudisium', tgl_sk_yudisium='$tgl_sk_yudisium', no_seri_ijazah='$no_seri_ijazah', judul_skripsi='$judul_skripsi', updated_keluar_at='$updated_at' where xid_reg_pd='$xid_reg_pd'";
		$db->query($qryMahasiswaPT);

		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil Simpan Mahasiswa Lulus/Keluar";

		$db = null;
		echo json_encode($hasil);

	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Simpan. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}


}
else if ($aksi == "hapusJenisKeluar") {

	$xid_reg_pd = clean($data->xid_reg_pd);

	try {
		$db = koneksi();

		$qryMahasiswaPT = "update wsia_mahasiswa_pt set id_jns_keluar='', id_periode_keluar=null, tgl_keluar='', sk_yudisium='', tgl_sk_yudisium='', no_seri_ijazah='', judul_skripsi='', updated_keluar_at='0000-00-00 00:00:00' where xid_reg_pd='$xid_reg_pd'";
		$db->query($qryMahasiswaPT);

		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil Hapus Mahasiswa Lulus/Keluar";

		$db = null;
		echo json_encode($hasil);

	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Hapus. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}


}
else if ($aksi == "tampilHakAkses") {
	$id_smt = $_SESSION['ta'];
	$perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan,wsia_hakakses where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_mahasiswa.xid_pd=wsia_hakakses.xid_pd and wsia_hakakses.id_smt='$id_smt' ";

	$perintah .= isset($_GET['filter']['nm_pd']) ? " and nm_pd like '%" . $_GET['filter']['nm_pd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['nipd']) ? " and nipd like '%" . $_GET['filter']['nipd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['jk']) ? " and jk like '%" . $_GET['filter']['jk'] . "%'" : "";
	$perintah .= isset($_GET['filter']['kelas']) ? " and kelas like '%" . $_GET['filter']['kelas'] . "%'" : "";

	if (isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb'] != "") {
		$nm_lemb = explode(" - ", $_GET['filter']['vnm_lemb']);
		$nm_jenj_didik = $nm_lemb[0];
		$nm_lemb = $nm_lemb[1];
		$perintah .= " and nm_jenj_didik like '%" . $nm_jenj_didik . "%'";
		$perintah .= " and nm_lemb like '%" . $nm_lemb . "%'";
	}

	if (isset($_GET['filter']['vid_jns_daftar'])) {
		if ($_GET['filter']['vid_jns_daftar'] == "Mahasiswa Baru") {
			$perintah .= " and id_jns_daftar = '1'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "Pindahan/Transfer") {
			$perintah .= " and id_jns_daftar = '2'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "RPL Perolehan SKS") {
			$perintah .= " and id_jns_daftar = '13'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "RPL Transfer SKS / Karyawan") {
			$perintah .= " and id_jns_daftar = '16'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "Mahasiswa K2") {
			$perintah .= " and id_jns_daftar = '17'";
		}

	}

	//$perintah.=" order by nim ";

	$perintah .= isset($_GET['count']) ? ' LIMIT ' . $_GET['count'] : ' LIMIT 20';
	$perintah .= isset($_GET['start']) ? ' OFFSET ' . $_GET['start'] : ' OFFSET 0';

	//echo $perintah;
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);

		$dataA = array();
		foreach ($data as $itemData) {
			$id_jns_daftar = $itemData->id_jns_daftar;
			$vid_jns_daftar = "Lainnya";
			if ($id_jns_daftar == "1") {
				$vid_jns_daftar = "Mahasiswa Baru";
			}
			else if ($id_jns_daftar == "2") {
				$vid_jns_daftar = "Pindahan/Transfer";
			}
			else if ($id_jns_daftar == "13") {
				$vid_jns_daftar = "RPL Perolehan SKS";
			}
			else if ($id_jns_daftar == "16") {
				$vid_jns_daftar = "RPL Transfer SKS / Karyawan";
			}
			else if ($id_jns_daftar == "17") {
				$vid_jns_daftar = "Mahasiswa K2";
			}
			$itemData->vid_jns_daftar = $vid_jns_daftar;
			$itemData->no_pend = $itemData->xid_pd;
			$itemData->vnm_lemb = $itemData->nm_jenj_didik . " - " . $itemData->nm_lemb;
			$itemData->vnm_ibu_kandung = $itemData->nm_ibu_kandung;

			$id_kk = $itemData->id_kk;
			$qryKKmhs = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk'";
			$eksekusiMhs = $db->query($qryKKmhs);
			$dataKKmhs = $eksekusiMhs->fetch(PDO::FETCH_OBJ);
			 if ($dataKKmhs) {
				$aKKmhs = get_object_vars($dataKKmhs);
				 foreach ($aKKmhs as $key => $nilai) {
					$keyMhs = "mhs_" . $key;
					$itemData->$keyMhs = $nilai;
				}
			}

			$id_kk_ayah = $itemData->id_kebutuhan_khusus_ayah;
			$qryKKayah = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ayah'";
			$eksekusiAyah = $db->query($qryKKayah);
			$dataKKayah = $eksekusiAyah->fetch(PDO::FETCH_OBJ);
			 if ($dataKKayah) {
				$aKKayah = get_object_vars($dataKKayah);
				 foreach ($aKKayah as $key => $nilai) {
					$keyAyah = "ayah_" . $key;
					$itemData->$keyAyah = $nilai;
				}
			}

			$id_kk_ibu = $itemData->id_kebutuhan_khusus_ibu;
			$qryKKibu = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ibu'";
			$eksekusiIbu = $db->query($qryKKibu);
			$dataKKibu = $eksekusiIbu->fetch(PDO::FETCH_OBJ);
			 if ($dataKKibu) {
				$aKKibu = get_object_vars($dataKKibu);
				 foreach ($aKKibu as $key => $nilai) {
					$keyIbu = "ibu_" . $key;
					$itemData->$keyIbu = $nilai;
				}
			}

			array_push($dataA, $itemData);
		}

		echo json_encode($dataA);
		$db = null;
	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}


}
else if ($aksi == "tampilBelumHakAkses") {
	$id_smt = $_SESSION['ta'];
	$perintah = "select * from wsia_mahasiswa, wsia_mahasiswa_pt, wsia_sms,wsia_jenjang_pendidikan where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd and id_jns_keluar='' and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik and wsia_mahasiswa_pt.kelas != '' and wsia_mahasiswa.xid_pd not in (select xid_pd from wsia_hakakses where wsia_hakakses.id_smt='$id_smt') ";

	$perintah .= isset($_GET['filter']['nm_pd']) ? " and nm_pd like '%" . $_GET['filter']['nm_pd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['nipd']) ? " and nipd like '%" . $_GET['filter']['nipd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['jk']) ? " and jk like '%" . $_GET['filter']['jk'] . "%'" : "";
	$perintah .= isset($_GET['filter']['kelas']) ? " and kelas like '%" . $_GET['filter']['kelas'] . "%'" : "";

	if (isset($_GET['filter']['vnm_lemb']) && $_GET['filter']['vnm_lemb'] != "") {
		$nm_lemb = explode(" - ", $_GET['filter']['vnm_lemb']);
		$nm_jenj_didik = $nm_lemb[0];
		$nm_lemb = $nm_lemb[1];
		$perintah .= " and nm_jenj_didik like '%" . $nm_jenj_didik . "%'";
		$perintah .= " and nm_lemb like '%" . $nm_lemb . "%'";
	}

	if (isset($_GET['filter']['vid_jns_daftar'])) {
		if ($_GET['filter']['vid_jns_daftar'] == "Mahasiswa Baru") {
			$perintah .= " and id_jns_daftar = '1'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "Pindahan/Transfer") {
			$perintah .= " and id_jns_daftar = '2'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "RPL Perolehan SKS") {
			$perintah .= " and id_jns_daftar = '13'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "RPL Transfer SKS / Karyawan") {
			$perintah .= " and id_jns_daftar = '16'";
		}
		else if ($_GET['filter']['vid_jns_daftar'] == "Mahasiswa K2") {
			$perintah .= " and id_jns_daftar = '17'";
		}

	}

	//$perintah.=" order by nim ";

	$perintah .= isset($_GET['count']) ? ' LIMIT ' . $_GET['count'] : ' LIMIT 20';
	$perintah .= isset($_GET['start']) ? ' OFFSET ' . $_GET['start'] : ' OFFSET 0';

	//echo $perintah;
	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);

		$dataA = array();
		foreach ($data as $itemData) {
			$id_jns_daftar = $itemData->id_jns_daftar;
			$vid_jns_daftar = "Lainnya";
			if ($id_jns_daftar == "1") {
				$vid_jns_daftar = "Mahasiswa Baru";
			}
			else if ($id_jns_daftar == "2") {
				$vid_jns_daftar = "Pindahan/Transfer";
			}
			else if ($id_jns_daftar == "13") {
				$vid_jns_daftar = "RPL Perolehan SKS";
			}
			else if ($id_jns_daftar == "16") {
				$vid_jns_daftar = "RPL Transfer SKS / Karyawan";
			}
			else if ($id_jns_daftar == "17") {
				$vid_jns_daftar = "Mahasiswa K2";
			}
			$itemData->vid_jns_daftar = $vid_jns_daftar;
			$itemData->no_pend = $itemData->xid_pd;
			$itemData->vnm_lemb = $itemData->nm_jenj_didik . " - " . $itemData->nm_lemb;
			$itemData->vnm_ibu_kandung = $itemData->nm_ibu_kandung;

			$id_kk = $itemData->id_kk;
			$qryKKmhs = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk'";
			$eksekusiMhs = $db->query($qryKKmhs);
			$dataKKmhs = $eksekusiMhs->fetch(PDO::FETCH_OBJ);
			 if ($dataKKmhs) {
				$aKKmhs = get_object_vars($dataKKmhs);
				 foreach ($aKKmhs as $key => $nilai) {
					$keyMhs = "mhs_" . $key;
					$itemData->$keyMhs = $nilai;
				}
			}

			$id_kk_ayah = $itemData->id_kebutuhan_khusus_ayah;
			$qryKKayah = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ayah'";
			$eksekusiAyah = $db->query($qryKKayah);
			$dataKKayah = $eksekusiAyah->fetch(PDO::FETCH_OBJ);
			 if ($dataKKayah) {
				$aKKayah = get_object_vars($dataKKayah);
				 foreach ($aKKayah as $key => $nilai) {
					$keyAyah = "ayah_" . $key;
					$itemData->$keyAyah = $nilai;
				}
			}

			$id_kk_ibu = $itemData->id_kebutuhan_khusus_ibu;
			$qryKKibu = "select * from wsia_kebutuhan_khusus where id_kk='$id_kk_ibu'";
			$eksekusiIbu = $db->query($qryKKibu);
			$dataKKibu = $eksekusiIbu->fetch(PDO::FETCH_OBJ);
			 if ($dataKKibu) {
				$aKKibu = get_object_vars($dataKKibu);
				 foreach ($aKKibu as $key => $nilai) {
					$keyIbu = "ibu_" . $key;
					$itemData->$keyIbu = $nilai;
				}
			}

			array_push($dataA, $itemData);
		}

		echo json_encode($dataA);
		$db = null;
	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}


}
else if ($aksi == "mahasiswaHapusAkses") {
	$xid_pd = clean($data->xid_pd);
	$id_smt = $_SESSION['ta'];
	try {
		$db = koneksi();
		$qry = "delete from wsia_hakakses  where xid_pd='$xid_pd' and id_smt='$id_smt' ";
		$eksekusi = $db->query($qry);
		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil Hapus";
		$db = null;
		echo json_encode($hasil);

	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Hapus. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}


}
else if ($aksi == "mahasiswaTambahAkses") {
	$id_smt = $_SESSION['ta'];
	$xid_pd = $data->xid_pd;
	$db = koneksi();

	foreach ($xid_pd as $itemXid_pd) {
		$id_akses = $itemXid_pd . $id_smt;
		$sql = "insert ignore into wsia_hakakses values('$id_akses','$id_smt','$itemXid_pd') ";
		try {
			$eksekusi = $db->query($sql);
		}
		catch (PDOException $salah) {
			$hasil['berhasil'] = 0;
			$hasil['pesan'] = "Beberapa data tidak tersimpan. Kesalahan:<br>" . $salah->getMessage();
			exit(json_encode($hasil));
		}
	}

	$hasil['berhasil'] = 1;
	$hasil['pesan'] = "Berhasil Menambahkan hak akses";
	echo json_encode($hasil);

	$db = null;


}
else if ($aksi == "pass") {
	$nipd = $data->nipd;
	$passBaru = sha1(md5(clean($data->passBaru)) . $nipd);

	$sql = "update wsia_mahasiswa_pt set pass ='$passBaru' where nipd='$nipd'";
	try {
		$db = koneksi();
		$eksekusi = $db->query($sql);
		$db = null;
		if ($eksekusi->rowCount() > 0) {
			$hasil['berhasil'] = 1;
			$hasil['pesan'] = "Berhasil Ubah Password";
		}
		else {
			$hasil['berhasil'] = 0;
			$hasil['pesan'] = "Tidak ada yang dirubah";
		}
		echo json_encode($hasil);
	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Ubah. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}
}
else if ($aksi == "foto") {
	$destination = realpath('../mhs/foto');
	$filename = $destination . "/" . md5($id) . ".jpg";
	if (file_exists($filename)) {
		echo json_encode(
			array(
			'foto' => "<center><img src='../mhs/foto/" . md5($id) . ".jpg' height='140'></center>"
		)
		);
	}
	else {
		echo json_encode(array('foto' => "Belum Upload Foto"));
	}
}
else if ($aksi == "kk") {
	$destination = realpath('../mhs/kk');
	$filename = $destination . "/" . md5($id) . ".pdf";
	if (file_exists($filename)) {
		echo json_encode(
			array(
			'link' => "<a href='sopingi/mahasiswa/tampilkk/" . $key . "/" . $id . "' target='_blank'>Unduh KK</a>"
		)
		);
	}
	else {
		echo json_encode(array('link' => "Belum Upload KK"));
	}
}
else if ($aksi == "tampilkk") {
	$destination = realpath('../mhs/kk');
	$filename = $destination . "/" . md5($id) . ".pdf";
	if (file_exists($filename)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="KK_' . $id . '.pdf"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		readfile($filename);
		exit;
	}
	else {
		echo "Maaf file KK tidak tersedia";
	}
}
else if ($aksi == "import") {

	$db = koneksi();
	$dbDaftar = koneksi_spmb();
	$du = $data->du;

	foreach ($du as $item) {

		$no_pend = $item->no_pend;
		$sqlDaftar = "select * FROM sipenmaru_daftar where no_pend='$no_pend'";
		$eksekusiDaftar = $dbDaftar->query($sqlDaftar);
		$hasilDaftar = $eksekusiDaftar->fetch(PDO::FETCH_OBJ);

		//INSERT DATA
		$no_pend = clean($hasilDaftar->no_pend);
		$nm_pd = clean($hasilDaftar->nama);
		$tmpt_lahir = clean($hasilDaftar->tempat_lahir);
		$vtgl_lahir = clean($hasilDaftar->tgl_lahir);
		$jk = clean($hasilDaftar->jk);
		$id_agama = clean($hasilDaftar->agama);
		$nik = clean($hasilDaftar->no_identitas);
		//$negara		= clean($hasilDaftar->negara);
		$jln = "";
		$nm_dsn = "";
		$rt = "";
		$rw = "";
		$ds_kel = "";
		$kode_pos = clean($hasilDaftar->kode_pos);
		$id_wil = clean($hasilDaftar->wilayah);

		$telepon_rumah = clean($hasilDaftar->telp_ortu);
		$telepon_seluler = clean($hasilDaftar->telepon);
		$email = clean($hasilDaftar->email);
		$a_terima_kps = "";
		$no_kps = "";
		$nm_ayah = clean($hasilDaftar->nama_ortu);
		$vtgl_lahir_ayah = "";

		$id_jenjang_pendidikan_ayah = "";
		$id_pekerjaan_ayah = "";
		$id_penghasilan_ayah = "";
		$nm_ibu_kandung = "";
		$vtgl_lahir_ibu = "";

		$id_jenjang_pendidikan_ibu = "";
		$id_pekerjaan_ibu = "";
		$id_penghasilan_ibu = "";
		$nm_wali = "";
		$vtgl_lahir_wali = "";

		$id_jenjang_pendidikan_wali = "";
		$id_pekerjaan_wali = "";
		$id_penghasilan_wali = "";

		$id_kk = "";

		$id_kk_ayah = "";

		$id_kk_ibu = "";

		$tgL_update = date("Y-m-d H:i:s");

		$qryMhs = "insert ignore into wsia_mahasiswa values ('$no_pend','','$nm_pd','$jk','0','$nik','$tmpt_lahir','$tgl_lahir','$id_agama','$id_kk','$id_sp','$jln','$rt','$rw','$nm_dsn','$ds_kel','$id_wil','$kode_pos','$id_jns_tinggal','0','$telepon_rumah','$telepon_seluler','$email','$a_terima_kps','$no_kps','A','$nm_ayah','$tgl_lahir_ayah','$id_jenjang_pendidikan_ayah','$id_pekerjaan_ayah','$id_penghasilan_ayah','$id_kk_ayah','$nm_ibu_kandung','$tgl_lahir_ibu','$id_jenjang_pendidikan_ibu','$id_penghasilan_ibu','$id_pekerjaan_ibu','$id_kk_ibu','$nm_wali','$tgl_lahir_wali','$id_jenjang_pendidikan_wali','$id_pekerjaan_wali','$id_penghasilan_wali','ID','$tgL_update',null,'') ";

		$cek_no_pend = "";
		try {

			$eksekusi = $db->query($qryMhs);

			if ($eksekusi) {

				$kode_prodi = $item->kode_progdi;
				$sqlSms = "select * FROM wsia_sms where kode_prodi='$kode_prodi'";
				$eksekusiSms = $db->query($sqlSms);
				$hasilSms = $eksekusiSms->fetch(PDO::FETCH_OBJ);

				$nipd = null;
				$tgl_masuk_sp = date("Y-m-d");
				$id_sms = clean($item->xid_sms);
				$mulai_smt = $item->tahun_angkatan . "1";
				$id_jns_daftar = clean($item->jenis_daftar);
				$kelas = "";
				$pa = "";
				$pass = "Belum Ada";

				$qryMahasiswaPT = "insert ignore into wsia_mahasiswa_pt (xid_reg_pd,id_pd,id_sms,nipd,tgl_masuk_sp,mulai_smt,id_jns_daftar,kelas,pass,pa, no_pend, jenis_daftar, kelas_spmb) values('$no_pend','$no_pend','$id_sms',null,'$tgl_masuk_sp','$mulai_smt','$id_jns_daftar','$kelas','$pass','$pa','$no_pend','$id_jns_daftar','$item->kelas') ";
				$db->query($qryMahasiswaPT);

				$dbDaftar->query("update sipenmaru_daftar set impor_siakad='1' where no_pend='" . $item->no_pend . "'");

				$cek_no_pend .= $item->no_pend . ",";
				$baru++;
			}
			else {
				$sudah_ada++;
			}

		}
		catch (PDOException $salah) {
			$error++;
			$error_ket .= $salah->getMessage() . "<br>";
		}

	}

	$hasil['berhasil'] = 1;
	$hasil['pesan'] = "Berhasil: " . $baru . "<br>Error: " . $error;
	$hasil['error'] = $error_ket;
	$hasil['cek_no_pend'] = $cek_no_pend;
	echo json_encode($hasil);


}
else if ($aksi == "baru") {

	$perintah = "select * from viewMahasiswaPt where angkatan='$id' and (nipd='' or kelas='')";

	$perintah .= isset($_GET['filter']['no_pend']) ? " and IFNULL(no_pend,'') like '%" . $_GET['filter']['no_pend'] . "%'" : "";
	$perintah .= isset($_GET['filter']['nm_pd']) ? " and IFNULL(nm_pd,'') like '%" . $_GET['filter']['nm_pd'] . "%'" : "";
	$perintah .= isset($_GET['filter']['id_sms']) ? " and IFNULL(id_sms,'') like '%" . $_GET['filter']['id_sms'] . "%'" : "";
	$perintah .= isset($_GET['filter']['jenis_daftar']) ? " and IFNULL(jenis_daftar,'') like '%" . $_GET['filter']['jenis_daftar'] . "%'" : "";
	$perintah .= isset($_GET['filter']['kelas']) ? " and  IFNULL(kelas,'')  like '%" . $_GET['filter']['kelas'] . "%'" : "";

	try {
		$db = koneksi();
		$qry = $db->prepare($perintah);
		$qry->execute();

		$data = $qry->fetchAll(PDO::FETCH_OBJ);
		foreach ($data as $itemData) {
			$id_jns_daftar = $itemData->jenis_daftar;
			if ($id_jns_daftar == "1") {
				$vid_jns_daftar = "Mahasiswa Baru";
			}
			else if ($id_jns_daftar == "2") {
				$vid_jns_daftar = "Pindahan/Transfer";
			}
			else if ($id_jns_daftar == "13") {
				$vid_jns_daftar = "RPL Perolehan SKS";
			}
			else if ($id_jns_daftar == "16") {
				$vid_jns_daftar = "RPL Transfer SKS / Karyawan";
			}
			else if ($id_jns_daftar == "17") {
				$vid_jns_daftar = "Mahasiswa K2";
			}
			$itemData->vid_jns_daftar = $vid_jns_daftar;
		}
		$db = null;

		echo json_encode($data);

	}
	catch (PDOException $salah) {
		echo json_encode($salah->getMessage());
	}
}
else if ($aksi == "bulkUbahPA") {
	$pa = clean($data->pa);
	$ids = $data->ids; // Array of xid_reg_pd

	if (empty($ids)) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Tidak ada mahasiswa yang dipilih";
		echo json_encode($hasil);
		exit;
	}

	try {
		$db = koneksi();
		// Sanitize IDs
		$clean_ids = array();
		foreach ($ids as $id_item) {
			$clean_ids[] = clean($id_item);
		}
		$id_list = implode("','", $clean_ids);

		$sql = "UPDATE wsia_mahasiswa_pt SET pa = '$pa' WHERE xid_reg_pd IN ('$id_list')";
		$db->query($sql);

		$hasil['berhasil'] = 1;
		$hasil['pesan'] = "Berhasil update PA untuk " . count($ids) . " mahasiswa";
		$db = null;
		echo json_encode($hasil);
	}
	catch (PDOException $salah) {
		$hasil['berhasil'] = 0;
		$hasil['pesan'] = "Gagal Update Bulk. Kesalahan:<br>" . $salah->getMessage();
		echo json_encode($hasil);
	}
}
