<?php
error_reporting(E_ALL & ~E_NOTICE);
if (!isset($key)) {
    exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaADMIN']) {
    exit();
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


if ($aksi == "belumnim") {

    try {

        $db     = koneksi();
        $perintah = "select * from viewMahasiswaPt where angkatan='$id' and (nipd='' or kelas='') order by kode_prodi, nm_pd";
        $qry     = $db->prepare($perintah);
        $qry->execute();
        $dataMhs        = $qry->fetchAll(PDO::FETCH_OBJ);


        $perintahDosen = "select * from wsia_dosen order by nm_ptk";
        $qry     = $db->prepare($perintahDosen);
        $qry->execute();
        $dataDosen        = $qry->fetchAll(PDO::FETCH_OBJ);

        $perintahSms = "select * from wsia_sms_view order by kode_prodi";
        $qry     = $db->prepare($perintahSms);
        $qry->execute();
        $dataSms        = $qry->fetchAll(PDO::FETCH_OBJ);

        $db        = null;



        $excelMhs = array(
            ["No Daftar", "Kelas SPMB", "Nama", "JK", "Kode Prodi", "NIM", "ID Jenis Daftar", "Kelas", "ID PA", "Tanggal Masuk", "ID Semester Masuk", "Nama PT Asal", "Nama Prodi Asal", "SKS Diakui Jika Transfer", "Pembiayaan", "Biaya Masuk"]
        );

        foreach ($dataMhs as $item) {
            $mulai_smt = $item->angkatan . "1";
            $tgl_masuk = $item->angkatan . "-09-01";
            $baris = [$item->no_pend, $item->kelas_spmb, $item->nm_pd, $item->jk, $item->kode_prodi, "", $item->jenis_daftar, $item->kelas, "", $tgl_masuk, $mulai_smt, $item->asal_pt, $item->progdi_pt];
            $excelMhs[] = $baris;
        }


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($excelMhs, NULL, 'A1');
        $sheet->setTitle("Mahasiswa Baru");
        for ($i = 65; $i <= 80; $i++) {
            $sheet->getColumnDimension(chr($i))->setAutoSize(true);
        }


        $excelDosen = array(
            ["ID PA", "NIDN", "Nama"]
        );

        foreach ($dataDosen as $item) {
            $baris = [$item->xid_ptk, $item->nidn, $item->nm_ptk];
            $excelDosen[] = $baris;
        }

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->fromArray($excelDosen, NULL, 'A1');
        $sheet2->setTitle('Dosen');
        for ($i = 65; $i <= 68; $i++) {
            $sheet2->getColumnDimension(chr($i))->setAutoSize(true);
        }

        $excelSms = array(
            ["Kode Prodi", "Nama Prodi", "Jenjang"]
        );

        foreach ($dataSms as $item) {
            $baris = [$item->kode_prodi, $item->nm_lemb, $item->nm_jenj_didik];
            $excelSms[] = $baris;
        }

        $sheet3 = $spreadsheet->createSheet();
        $sheet3->fromArray($excelSms, NULL, 'A1');
        $sheet3->setTitle('Program Studi');
        for ($i = 65; $i <= 68; $i++) {
            $sheet3->getColumnDimension(chr($i))->setAutoSize(true);
        }

        $excelJenisDaftar = array(
            ["ID Jenis Daftar", "Nama Jenis Daftar"],
            ["1", "Peserta Didik Baru"],
            ["2", "Pindahan (Belum lulus dari PT asal)"],
            ["13", "RPL Perolehan SKS (Pengalaman Kerja)"],
            ["16", "RPL Transfer SKS (Sudah lulus dari PT asal)"],
        );


        $sheet4 = $spreadsheet->createSheet();
        $sheet4->fromArray($excelJenisDaftar, NULL, 'A1');
        $sheet4->setTitle('Jenis Daftar');
        for ($i = 65; $i <= 67; $i++) {
            $sheet4->getColumnDimension(chr($i))->setAutoSize(true);
        }


        $nama_file = "Mahasiswa Tanpa NIM" . date("d-m-Y");

        // redirect output to client browser
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nama_file . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    } catch (PDOException $salah) {
        echo json_encode($salah->getMessage());
    }
} else if ($aksi == "uploadbelumnim") {

    $file = $_FILES['upload']["tmp_name"];

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($file);

    $worksheet = $spreadsheet->getSheet(0);
    $rows = $worksheet->toArray();

    unset($rows[0]);

    $berhasil = 0;
    $gagal = 0;

    $info_gagal = "";
    try {

        $db     = koneksi();

        foreach ($rows as $item) {

            $no_pend      = trim($item[0] ?? '');
            $nama         = trim(addslashes($item[2] ?? ''));
            $kode_prodi   = trim($item[4] ?? '');
            $nim          = trim($item[5] ?? '');
            $jenis_daftar = trim($item[6] ?? '');
            $kelas        = trim($item[7] ?? '');
            $pa           = trim($item[8] ?? '');
            $tgl_masuk_sp = trim(substr($item[9] ?? '', 0, 10));
            $mulai_smt    = trim($item[10] ?? '');
            $nm_pt_asal   = trim($item[11] ?? '');
            $nm_prodi_asal = trim($item[12] ?? '');
            $sks_diakui = trim($item[13] ?? '');
            $sks_diakui = ($sks_diakui === '' ? 0 : $sks_diakui);
            $id_biaya = trim($item[14] ?? '');
            $nm_biaya = trim($item[15] ?? '');
            $passBaru     = sha1(md5($no_pend) . $nim);


            if ($no_pend != "" && $kode_prodi != "" && $nim != "" && $jenis_daftar != "" && $kelas != "" && $pa != "" && $tgl_masuk_sp != "" && $mulai_smt != "") {


                $qry     = $db->prepare("select * from wsia_sms where kode_prodi='$kode_prodi' ");
                $qry->execute();
                $sms    = $qry->fetch(PDO::FETCH_OBJ);
                $xid_sms = $sms->xid_sms;

                try {


                    $qry = $db->prepare("SELECT xid_sms FROM wsia_sms WHERE kode_prodi = :kode_prodi");
                    $qry->bindParam(':kode_prodi', $kode_prodi);
                    $qry->execute();
                    $sms = $qry->fetch(PDO::FETCH_OBJ);

                    if ($sms) {
                        $xid_sms = $sms->xid_sms;

                        $perintahMahasiswaPt = "
                        UPDATE wsia_mahasiswa_pt 
                        SET id_sms=:id_sms, 
                            nipd=:nim, 
                            tgl_masuk_sp=:tgl_masuk_sp, 
                            id_jns_daftar=:jenis_daftar, 
                            jenis_daftar=:jenis_daftar,  
                            mulai_smt=:mulai_smt, 
                            sks_diakui=:sks_diakui, 
                            nm_pt_asal=:nm_pt_asal, 
                            nm_prodi_asal=:nm_prodi_asal, 
                            kelas=:kelas, 
                            pass=:passBaru, 
                            pa=:pa,
                            id_pembiayaan=:idBiaya,
                            biaya_masuk=:nmBiaya
                        WHERE xid_reg_pd=:no_pend
                    ";

                        $qryUpdate = $db->prepare($perintahMahasiswaPt);
                        $hasilMahasiswaPt = $qryUpdate->execute([
                            ':id_sms'       => $xid_sms,
                            ':nim'          => $nim,
                            ':tgl_masuk_sp' => $tgl_masuk_sp,
                            ':jenis_daftar' => $jenis_daftar,
                            ':mulai_smt'    => $mulai_smt,
                            ':sks_diakui'   => $sks_diakui,
                            ':nm_pt_asal'   => $nm_pt_asal,
                            ':nm_prodi_asal' => $nm_prodi_asal,
                            ':kelas'        => $kelas,
                            ':passBaru'     => $passBaru,
                            ':pa'           => $pa,
                            ':no_pend'      => $no_pend,
                            ':idBiaya'      => $id_biaya,
                            ':nmBiaya'      => $nm_biaya
                        ]);

                        if ($hasilMahasiswaPt) {
                            $berhasil++;
                        } else {
                            $info_gagal .= "<tr><td>{$no_pend} {$nama}</td><td>Gagal Eksekusi</td></tr>";
                            $gagal++;
                        }
                    } else {
                        $info_gagal .= "<tr><td>{$no_pend} {$nama}</td><td>Kode Prodi tidak ditemukan</td></tr>";
                        $gagal++;
                    }
                } catch (PDOException $salah) {
                    $info_gagal .= "<tr><td>{$no_pend} {$nama}</td><td>Error: {$salah->getMessage()}</td></tr>";
                    $gagal++;
                }
            } else {
                $info_gagal .= "<tr><td>" . $no_pend . " " . $nama . "</td><td>Gagal Eksekusi</td></tr>";
                $gagal++;
            }
        }

        $db = null;

        $hasil['status'] = "server";
        $hasil['pesan'] = "Berhasil: " . $berhasil . "<br>Gagal: " . $gagal . "<hr>";
        $hasil['data'] = $rows;
        $hasil['gagal'] = "<table border='1' align='center'>" . $info_gagal . "</table>";
        echo json_encode($hasil);
    } catch (PDOException $salah) {
        $hasil['status'] = "error";
        $hasil['pesan'] = $salah->getMessage();
        $hasil['data'] = [];
    }
}
