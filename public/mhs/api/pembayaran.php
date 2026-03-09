<?php
if (!isset($key)) {
    exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaMHS']) {
    exit();
}

require '../../config/koneksi_sikeu.php';

function http_request($url)
{
    // persiapkan curl
    $ch = curl_init();
    // set url 
    curl_setopt($ch, CURLOPT_URL, $url);
    // set user agent    
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // ssl verification (if needed)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // $output contains the output string 
    $output = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
    } else {
        $error_msg = "";
    }
    // tutup curl 
    curl_close($ch);

    $hasil = array('berhasil' => 0, 'pesan' => null, 'data' => []);

    // mengembalikan hasil curl
    if ($error_msg == "") {
        if (empty($output)) {
            $hasil['berhasil'] = 0;
            $hasil['pesan'] = "Server sikeu mengembalikan response kosong.";
            $hasil['data'] = [];
            return $hasil;
        }

        $dataTagihan = json_decode($output, TRUE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $hasil['berhasil'] = 0;
            $hasil['pesan'] = "Gagal parsing JSON dari server sikeu: " . json_last_error_msg();
            $hasil['data'] = [];
            $hasil['raw_output'] = substr($output, 0, 500); // Debugging
            return $hasil;
        }

        // If sikeu API already returns in SIAKAD format
        if (isset($dataTagihan['berhasil'])) {
            return $dataTagihan;
        }

        // Standard sikeu API response format
        if (isset($dataTagihan['status']) && $dataTagihan['status'] == true) {
            $hasil['berhasil'] = 1;
            $hasil['pesan'] = $dataTagihan['message'] ?? 'Data tagihan berhasil diambil';
            $hasil['data'] = $dataTagihan['data'] ?? [];
        } else {
            $hasil['berhasil'] = 0;
            $hasil['pesan'] = $dataTagihan['message'] ?? 'Tagihan tidak ditemukan atau belum lunas.';
            $hasil['data'] = [];
        }
    } else {
        $hasil['berhasil'] = 0;
        $hasil['pesan'] = "Gagal terhubung ke server sikeu. " . $error_msg;
        $hasil['data'] = [];
    }

    return $hasil;
}

if ($aksi == "cek_tagihan") {
    // Use xid_reg_pd as fallback if no_pend is not set in session
    $no_pend = $_SESSION['no_pend'] ?? $_SESSION['xid_reg_pd'] ?? '';
    $mulai_smt = $_SESSION['mulai_smt'] ?? '';
    $angkatan = !empty($mulai_smt) ? substr($mulai_smt, 0, 4) : '';
    $kode_prodi = $_SESSION['kode_prodi'] ?? '';
    $jenis_daftar = $_SESSION['jenis_daftar'] ?? '';

    if (empty($no_pend)) {
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Data pendaftaran (no_pend/xid_reg_pd) tidak ditemukan di session. Silakan login kembali.',
            'data' => []
        ]);
        exit();
    }

    // Attempt to hit localhost first (as per original code). 
    // If it returns {berhasil:0, pesan:null}, it might be because of missing parameters or bridge issue.
    $url = "http://localhost:3000/pembayaran/" . $no_pend . "/" . $angkatan . "/" . $kode_prodi . "/" . $jenis_daftar;
    $tagihan = http_request($url);
    
    // Debugging info if it fails
    if ($tagihan['berhasil'] == 0 && empty($tagihan['pesan'])) {
        $tagihan['pesan'] = "Sistem gagal memverifikasi tagihan. Pastikan data pendaftaran Anda lengkap.";
        $tagihan['debug_url'] = $url;
    }

    echo json_encode($tagihan);
}
