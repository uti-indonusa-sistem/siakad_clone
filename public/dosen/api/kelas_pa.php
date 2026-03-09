<?php
if (!isset($key)) {
    exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaDOSEN']) {
    exit();
}

if ($aksi == "semua") {
    $id_smt=$id;
    $xid_ptk    = $_SESSION['xid_ptk'];
    $perintah = "select DISTINCT(kelas) from wsia_mahasiswa_pt where pa = '$xid_ptk'";
    try {
        $db     = koneksi();
        $qry     = $db->prepare($perintah);
        $qry->execute();

        $data        = $qry->fetchAll(PDO::FETCH_OBJ);
        $db        = null;

        $hasil['berhasil'] = 1;
        $jdata = count($data);
        for ($i = 0; $i < $jdata; $i++) {
            $hasil['pesan'][$i]['id'] = $data[$i]->kelas;
            $hasil['pesan'][$i]['value'] = $data[$i]->kelas;
        }
        echo json_encode($hasil['pesan']);
    } catch (PDOException $salah) {
        $hasil['berhasil'] = 0;
        $hasil['pesan'] = $salah->getMessage();
        echo json_encode($hasil);
    }
}
