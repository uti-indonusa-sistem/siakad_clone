<?php

include 'login_auth.php';

function stringXML($data) {
	$xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
	array_to_xml($data,$xml);
	return $xml->asXML();
}

function array_to_xml($data,$xml_data){
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$subnode = $xml_data->addChild($key);
			array_to_xml($value,$subnode);
		} else {
			$xml_data->addChild("$key",$value);
		}
	}
}

function proxy() {
	$url="http://117.20.58.123:8100/ws/live2.php";
	$client = new nusoap_client($url,true);
   	$proxy = $client->getProxy();
   	return $proxy;
}

function runWs($data,$type="json") {
 	$url="http://117.20.58.123:8100/ws/live2.php";
 	$ch = curl_init();
 	
 	curl_setopt($ch,CURLOPT_POST,1);
 	
 	$headers = array();
 	if ($type=="xml") {
		$headers[]='Content-Type: application/xml';
	} else {
		$headers[]='Content-Type: application/json';
	}
	
	curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
	
	if ($data) {
		if ($type=="xml") {
			$data = stringXml($data);
		} else {
			$data = json_encode($data);
		}
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
	}
	
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $result;
 	
}

function token() {
	$data['act']="GetToken";
	$data['username']="sulistiyo@poltekindonusa.ac.id";
	// $data['password']="P45sw0rd!@#";
	$data['password']="P45sw0rd!@#";
	$token = runWs($data,'json');
	$_SESSION['feeder_token'] = $token;
	return $token;
}

function error_status($kode) {
	$kode_status = [
	    0 => 'Berhasil',
	    11 => 'Content-Type yang diperbolehkan: application/json dan application/xml',
	    12 => 'username/password salah',
	    13 => 'Fungsi yang dipanggil tidak tersedia',
	    14 => 'Under Construction',
	    15 => 'Ada kesalahan pada JSON yang dikirim',
	    16 => 'Ada kesalahan pada XML yang dikirim',
	    100 => 'Invalid Token. Token tidak ada atau token sudah expired.',
	    101 => 'Web Service dalam posisi Developer Mode. Jika ingin mengarahkan ke Live silakan diubah melalui Aplikasi Feeder',
	    102 => 'Tabel tidak tersedia',
	    103 => 'ERROR SQL',
	    104 => 'Web Service sudah expired. Silakan lakukan update web service atau hubungi http://sigap.pddikti.ristekdikti.go.id/',
	    105 => 'Tidak ada data yang berubah. Tidak semua field boleh diubah (lihat di feeder)',
	    106 => 'Web Service hanya bisa diakses dengan akun Admin PT',
	    107 => 'Checksum tidak valid, Data telah di modifikasi diluar aplikasi feeder atau webservice.',
	    108 => 'Parameter yang dikirim tidak valid',
	    109 => 'Field record yang dikirim tidak ada dalam tabel',
	    111 => 'Tidak ada data yang bisa diubah',
	    112 => 'Tidak ada data yang bisa dihapus',
	    113 => 'Data yang diubah lebih dari satu',
	    114 => 'Data yang dihapus lebih dari satu',
	    115 => 'Tidak bisa menambah/mengubah data. id_perguruan_tinggi atau id_prodi di luar satuan pendidikan pengguna web service',
	    116 => 'Tidak bisa menambah/mengubah data. Periode data di luar periode aktif',
	    117 => 'Data sudah dihapus',
	    118 => 'Tidak bisa menambah/mengubah data. id_registrasi_mahasiswa mahasiswa di luar satuan pendidikan pengguna web service',
	    119 => 'Data yang akan ditambahkan sudah ada',
	    120 => 'Tidak bisa mengubah data primary key',
	    121 => 'Error database. Periksa kembali parameter yang dikirim (nama kolom, filter, order atau parameter lainnya)',
	    200 => 'Mahasiswa dengan nama, tempat, tanggal lahir dan ibu kandung yang sama sudah ada',
	    201 => 'Nama mahasiswa tidak boleh kosong',
	    202 => 'Tanggal lahir tidak boleh kosong atau format tanggal tidak sesuai (YYYY-MM-DD)',
	    203 => 'Tidak ada data mahasiswa yang bisa diubah',
	    204 => 'Data mahasiswa yang diubah lebih dari satu',
	    205 => 'Tidak ada data mahasiswa yang bisa dihapus',
	    206 => 'Nama ibu tidak boleh kosong',
	    207 => 'Tempat lahir tidak boleh kosong',
	    210 => 'Mahasiswa dengan nama dan tanggal lahir ini tidak ada',
	    211 => 'Mahasiswa ini sudah terdaftar',
	    212 => 'id_mahasiswa tidak boleh kosong',
	    213 => 'nim (NIM/NRP) tidak boleh kosong',
	    214 => 'Mahasiswa dengan id_registrasi_mahasiswa atau nim ini tidak ada',
	    215 => 'Mahasiswa tidak bisa dihapus karena sudah terdaftar di Program Studi. Silakan menghapus data yang mengacu mahasiswa ini terlebih dahulu.',
	    216 => 'Data sudah disinkronisasi, perubahan nim mahasiswa tidak bisa dilakukan. Silakan mengubahnya di Forlap',
	    217 => 'Data sudah disinkronisasi, perubahan (nama, tempat dan tgl lahir serta nama ibu kandung) mahasiswa tidak bisa dilakukan. Silakan mengubahnya di Forlap',
	    218 => 'Mahasiswa tidak bisa dihapus karena sudah diacu di data lain (mis: kuliah, nilai, ekuivalensi atau dosen pembimbing). Silakan menghapus data yang mengacu mahasiswa ini terlebih dahulu.',
	    219 => 'nim (NIM/NRP) tidak boleh sama dalam satu Program Studi',
	    220 => 'Perguruan Tinggi asal dan Program Studi asal harus sudah terdaftar pada forlap',
	    221 => 'Data mahasiswa di luar periode aktif, tidak bisa mengubah data',
	    222 => 'Mahasiswa lulus tidak bisa dihapus karena sudah diacu di data lain (mis: dosen pembimbing). Silakan menghapus data yang mengacu mahasiswa ini terlebih dahulu.',
	    223 => 'Program Studi Asal tidak terdapat pada Perguruan Tinggi Asal',
	    224 => 'Mahasiswa tidak bisa dihapus karena sudah diacu data prestasi. Silakan menghapus data yang mengacu mahasiswa ini terlebih dahulu.',
	    300 => 'Penambahan dosen tidak diizinkan',
	    301 => 'Penghapusan dosen tidak diizinkan',
	    302 => 'Mengubah data dosen tidak diizinkan',
	    303 => 'Tidak ada data dosen yang bisa diubah',
	    304 => 'Data dosen yang diubah lebih dari satu',
	    305 => 'Tidak ada data dosen yang bisa dihapus',
	    310 => 'Dosen dengan nama dan tanggal lahir ini tidak ada',
	    312 => 'id_dosen tidak boleh kosong',
	    400 => 'Mata kuliah dengan nama dan kode_mata_kuliah ini sudah ada',
	    401 => 'Nama Mata kuliah tidak boleh kosong',
	    402 => 'Kode Mata kuliah tidak boleh kosong',
	    403 => 'Tidak ada data mata kuliah yang bisa diubah',
	    404 => 'Data mata kuliah yang diubah lebih dari satu',
	    405 => 'Kode Mata Kuliah, Prodi dan Jenjang tidak boleh kosong',
	    406 => 'Tidak ada data mata kuliah yang akan dihapus',
	    410 => 'Mata kuliah dengan kode dan nama ini tidak ada',
	    411 => 'Matakuliah tidak bisa dihapus karena sudah diacu di data matakuliah kurikulum. Silakan menghapus data yang mengacu matakuliah ini terlebih dahulu.',
	    412 => 'Matakuliah tidak bisa dihapus karena sudah diacu di data kelas perkuliahan. Silakan menghapus data yang mengacu matakuliah ini terlebih dahulu.',
	    413 => 'Matakuliah tidak bisa dihapus karena sudah diacu di data transfer mahasiswa. Silakan menghapus data yang mengacu matakuliah ini terlebih dahulu.',
	    500 => 'Kurikulum dengan nama, id_prodi dan id_jenjang_pendidikan ini sudah ada',
	    501 => 'Nama kurikulum, id_prodi dan id_jenjang_pendidikan tidak boleh kosong',
	    502 => 'id_prodi kurikulum tidak boleh kosong',
	    503 => 'id_jenjang_pendidikan kurikulum tidak boleh kosong',
	    504 => 'Tidak ada data kurikulum yang bisa diubah',
	    505 => 'Data kurikulum yang diubah lebih dari satu',
	    506 => 'Tidak ada kurikulum yang akan dihapus',
	    510 => 'Kurikulum dengan nama, id_prodi dan id_jenjang_pendidikan ini tidak ada',
	    511 => 'Kurikulum tidak bisa dihapus karena sudah diacu di data matakuliah kurikulum. Silakan menghapus data yang mengacu Kurikulum ini terlebih dahulu.',
	    600 => 'Kurikulum dengan nama, id_prodi dan id_semester_berlaku ini sudah ada',
	    601 => 'Nama Mata kuliah tidak boleh kosong',
	    602 => 'Kode Mata kuliah tidak boleh kosong',
	    603 => 'Semeter matakuliah kurikulum tidak boleh kosong',
	    604 => 'SKS matakuliah kurikulum tidak boleh kosong',
	    605 => 'Wajib/Tidak matakuliah kurikulum tidak boleh kosong',
	    606 => 'Nama dan Kode matakuliah ini tidak ada',
	    607 => 'Tidak ada data mata kuliah kurikulum yang bisa diubah',
	    608 => 'Mata kuliah kurikulum yang diubah lebih dari satu',
	    609 => 'Data matakuliah kurikulum tidak ada',
	    610 => 'Kurikulum dengan nama, id_prodi dan id_semester_berlaku ini tidak ada',
	    630 => 'Data mata kuliah kurikulum ini sudah ada',
	    631 => 'Data mata kuliah kurikulum ini tidak ada',
	    632 => 'Tidak ada data yang bisa diubah',
	    633 => 'Data yang diubah lebih dari satu',
	    634 => 'id_kurikulum, dan id_matkul tidak boleh kosong',
	    635 => 'Edit tidak di izinkan melalui webservice',
	    636 => 'Tidak ada data yang akan dihapus',
	    700 => 'Data kelas ini sudah ada',
	    701 => 'Nama kelas, id_matkul, id_prodi dan id_semester tidak boleh kosong',
	    702 => 'Tidak ada data kelas kuliah yang bisa diubah',
	    703 => 'Data kelas kuliah yang diubah lebih dari satu',
	    704 => 'id_kelas_kuliah tidak boleh kosong',
	    705 => 'Data kelas ini tidak ada',
	    706 => 'Tidak ada data yang akan dihapus',
	    707 => 'Mata kuliah yang dimasukkan harus sudah ada di Kurikulum',
	    711 => 'Kelas tidak bisa dihapus karena sudah diacu di data KRS Mahasiswa. Silakan menghapus data yang mengacu Kelas ini terlebih dahulu.',
	    712 => 'Kelas tidak bisa dihapus karena sudah diacu di data Aktifitas Mengajar Dosen. Silakan menghapus data yang mengacu Kelas ini terlebih dahulu.',
	    730 => 'Data aktivitas perkuliahan ini sudah ada',
	    731 => 'Data aktivitas perkuliahan ini tidak ada',
	    732 => 'Tidak ada data yang bisa diubah',
	    733 => 'Data yang diubah lebih dari satu',
	    734 => 'id_semester, id_registrasi_mahasiswa, id_stat_mhs tidak boleh kosong',
	    735 => 'Edit tidak di izinkan melalui webservice',
	    736 => 'Tidak ada data yang akan dihapus',
	    737 => 'Data aktivitas perkuliahan hanya di perbolehkan untuk status Aktif (A), Non Aktif (N), Cuti (C) dan sedang Double Degree (G)',
	    738 => 'Data sks semester tidak sesuai dengan jumlah sks KRS yang di tempuh mahasiswa',
	    800 => 'Data nilai dari id_kelas_kuliah dan id_registrasi_mahasiswa ini sudah ada',
	    801 => 'id_kelas_kuliah dan id_registrasi_mahasiswa tidak boleh kosong',
	    802 => 'id_registrasi_mahasiswa tidak boleh kosong',
	    803 => 'Tidak ada data nilai yang bisa diubah',
	    804 => 'Data nilai yang akan diubah lebih dari satu',
	    805 => 'Delete nilai tidak diizinkan',
	    806 => 'Mahasiswa ini sudah mengambil matakuliah ini di semester ini',
	    810 => 'Nilai transfer dari id_matkul dan id_registrasi_mahasiswa ini sudah ada',
	    811 => 'id_matkul dan id_registrasi_mahasiswa tidak boleh kosong',
	    812 => 'id_registrasi_mahasiswa tidak boleh kosong',
	    813 => 'Tidak ada nilai transfer yang bisa diubah',
	    814 => 'Nilai transfer yang akan diubah lebih dari satu',
	    815 => 'Delete nilai transfer tidak diizinkan',
	    820 => 'Dosen pembimbing untuk id_dosen dan id_registrasi_mahasiswa ini sudah ada',
	    821 => 'id_dosen dan id_registrasi_mahasiswa tidak boleh kosong',
	    900 => 'Data substansi dari nama substansi dan id_prodi ini sudah ada',
	    901 => 'Nama substansi tidak boleh kosong',
	    902 => 'id_prodi tidak boleh kosong',
	    903 => 'Tidak ada data substansi yang bisa diubah',
	    904 => 'Data substansi yang diubah lebih dari satu',
	    905 => 'id_jenis_substansi tidak boleh kosong',
	    906 => 'Tidak ada data substansi yang akan dihapus',
	    907 => 'Nama substansi ini sudah ada',
	    910 => 'Data substansi ini tidak ada',
	    911 => 'Substansi tidak bisa dihapus karena sudah diacu di data aktivitas mengajar dosen. Silakan menghapus data yang mengacu substansi ini terlebih dahulu.',
	    920 => 'Dosen mengajar dari id_registrasi_dosen dan id_kelas_kuliah ini sudah ada',
	    921 => 'id_registrasi_dosen tidak boleh kosong',
	    922 => 'id_kelas_kuliah tidak boleh kosong',
	    923 => 'Data dosen yang diubah lebih dari satu',
	    930 => 'Data dosen mengajar ini tidak ada',
	    931 => 'Data dosen mengajar ini belum ada penugasannya di tahun ajaran kelas',
	    940 => 'Skala nilai dari id_prodi dan nilai huruf ini sudah ada',
	    941 => 'id_prodi tidak boleh kosong',
	    942 => 'Nilai huruf tidak boleh kosong',
	    943 => 'Data skala nilai yang diubah lebih dari satu',
	    950 => 'Data skala nilai ini tidak ada',
	    960 => 'Kapasitas mahasiswa dari id_prodi dan id_semester ini sudah ada',
	    961 => 'id_prodi tidak boleh kosong',
	    962 => 'id_semester tidak boleh kosong',
	    963 => 'id_registrasi_mahasiswa tidak boleh kosong',
	    964 => 'id_perguruan_tinggi tidak boleh kosong',
	    965 => 'id_kelas_kuliah tidak boleh kosong',
	    966 => 'id_kurikulum tidak boleh kosong',
	    971 => 'Aktivitas Mahasiswa tidak bisa dihapus karena sudah diacu di data Dosen Penguji, Dosen Pembimbing dan Anggota Aktivitas. Silakan menghapus data yang mengacu aktivitas ini terlebih dahulu.',
	    991 => 'Data tidak bisa dihapus karena termasuk data invalid dari validator nasional. Silakan memperbaiki data validasi tersebut terlebih dahulu.',
	    980 => 'id_registrasi_mahasiswa tidak ditemukan',
	    981 => 'id_aktivitas tidak ditemukan',
	    982 => 'id_dosen tidak ditemukan'
	  ];

	if (array_key_exists($kode, $kode_status)) {
		return $kode_status[$kode];
	} else {
		return "";
	}
}

?>