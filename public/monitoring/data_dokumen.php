<?php
// Start output buffering to prevent any output before PDF generation
ob_start();

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
ini_set('pcre.backtrack_limit', '5000000');
set_time_limit(600);
require_once 'auth_check.php';
require '../../config/config.php';
include "../lib/pdf/mpdf.php"; // Include mPDF class definition once

// Helper function to format date
function format_tanggal($date)
{
  if (!$date)
    return "-";
  $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
  $d = explode('-', $date);
  if (count($d) < 3)
    return $date;
  return $d[2] . ' ' . $months[(int) $d[1] - 1] . ' ' . $d[0];
}
function format_tanggal_bln($date)
{
  return format_tanggal($date);
}

// --- PDF GENERATION FUNCTIONS ---

function generate_krs_pdf_content($db, $xid_reg_pd, $id_smt)
{
  // Logic adapted from public/baak/api/krs_pdf.php

  // Calculate Semester Label
  if ($id_smt != "-") {
    $tahun1 = substr($id_smt, 0, 4);
    $tahun2 = $tahun1 + 1;
    $smt = substr($id_smt, 4, 1);
    $vsmt = ($smt == "1") ? "Ganjil" : (($smt == "2") ? "Genap" : "Pendek");
    $ta = $tahun1 . "/" . $tahun2;
    // $vid_smt = $tahun1 . "/" . $tahun2 . " " . $vsmt;
  } else {
    $vsmt = "";
    $ta = "";
  }

  // Get Student Data
  $qryMhs = "select * from wsia_mahasiswa_pt,wsia_mahasiswa,wsia_sms,wsia_jenjang_pendidikan 
               where wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd 
               and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms 
               and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik 
               and xid_reg_pd=:id";
  $stmt = $db->prepare($qryMhs);
  $stmt->execute(['id' => $xid_reg_pd]);
  $dataMhs = $stmt->fetch(PDO::FETCH_OBJ);

  if (!$dataMhs)
    return "Mahasiswa tidak ditemukan";

  // QR Code
  $dataQR2 = "http://document.poltekindonusa.ac.id/view_krs-" . ord(substr($dataMhs->nipd, 0, 1)) . '_' . (substr($dataMhs->nipd, 1, 5) * 666) . "-" . ($id_smt * 666) . ".html";
  $qrcode = "<barcode code='" . $dataQR2 . "' type='QR' class='barcode' size='1.5' error='L' />";

  // PA Data
  $id_ptk = $dataMhs->pa;
  $sqlPa = "select * from wsia_dosen where xid_ptk=:id_ptk";
  $stmtPa = $db->prepare($sqlPa);
  $stmtPa->execute(['id_ptk' => $id_ptk]);
  $dataPa = $stmtPa->fetch(PDO::FETCH_OBJ);
  $pa = ($stmtPa->rowCount() > 0) ? $dataPa->nm_ptk : "-";

  // Photo
  $foto_path = "../mhs/foto/" . md5($dataMhs->nipd) . ".jpg";
  $foto_mhs = file_exists($foto_path) ? $foto_path : "../gambar/no-foto.jpg";

  // Build HTML
  $header = "<table width='100%' border='0'>
            <tr>
                <td rowspan='5' align='center'><img src='../gambar/logo_pt.jpg' height='110'></td>
                <td align='center'><h3>POLITEKNIK INDONUSA SURAKARTA</h3></td>
                <td rowspan='5' align='left'><img src='" . $foto_mhs . "' height='130'></td>
            </tr>
            <tr>
                <td align='center'>
                    Kampus 1: Jl. KH. Samanhudi No.31, Laweyan Surakarta, Telp: 0271 - 743479<br>
                    Kampus 2: Jl. Palem No.8 Cemani, Grogol, Sukoharjo, Telp: 0271 - 7464173
                </td>
            </tr>  
            <tr><td>&nbsp;</td></tr>";

  $atas = "<table width='100%' border='0'>  
      <tr>
        <td width='70'>No.Daftar</td>
        <td>: " . $dataMhs->xid_reg_pd . " </td>
        <td>&nbsp;</td>
        <td width='150'>Semester</td>
        <td>: " . $vsmt . " </td>
      </tr>
      <tr>
        <td>Nama</td>
        <td>: " . $dataMhs->nm_pd . " </td>
        <td>&nbsp;</td>
        <td>Tahun Akademik</td>
        <td>: " . $ta . " </td>
      </tr>
      <tr>
        <td>NIM</td>
        <td>: " . $dataMhs->nipd . " </td>
        <td>&nbsp;</td>
        <td>Pembimbing Akademik</td>
        <td>: " . $pa . " </td>
      </tr>
    </table>";

  $header .= "<tr>
                <td align='center'>
                    <h3 class='judulKrs'>Kartu Rencana Studi</h3>
                    " . $dataMhs->nm_jenj_didik . " - " . $dataMhs->nm_lemb . "
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
         </table>";

  $isiheader = "<table width='100%' border='1' style='border-collapse:collapse'>
                <tr bgcolor='#CCCCCC'>
                  <th rowspan='2' align='center' width='20'>No</th>
                  <th rowspan='2' align='center' width='90'>Kode<br>Mata Kuliah</th>
                  <th rowspan='2' align='center'>Mata Kuliah</th>
                  <th rowspan='2' align='center' width='20'>Jml<br>SKS</th>
                  <th colspan='3' align='center'>Komposisi SKS</th>
                  <th rowspan='2' align='center'>Dosen Pengajar</th>
                 </tr>
                 <tr bgcolor='#CCCCCC'>
                   <th align='center' width='60'>T</th>
                   <th align='center' width='60'>P</th>
                   <th align='center' width='60'>K</th>
                 </tr>";

  // Get Grades/KRS Items
  // original query joins wsia_nilai, so using it (even it's krs, usually it's in nilai with NULL grade initially)
  $qryNilai = "select id_nilai,wsia_nilai.xid_kls as vid_kls,nm_kls,kode_mk,nm_mk,wsia_kelas_kuliah.sks_mk as vsks_mk,wsia_kelas_kuliah.sks_tm as vsks_tm,wsia_kelas_kuliah.sks_prak as vsks_prak,wsia_kelas_kuliah.sks_prak_lap as vsks_prak_lap,id_smt 
                 from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai 
                 where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms 
                 and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk 
                 and wsia_kelas_kuliah.id_smt=:smt 
                 and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls 
                 and wsia_nilai.xid_reg_pd=:id 
                 order by kode_mk asc";

  $stmtVal = $db->prepare($qryNilai);
  $stmtVal->execute(['smt' => $id_smt, 'id' => $xid_reg_pd]);
  $dataNilai = $stmtVal->fetchAll(PDO::FETCH_OBJ);

  $isi = "";
  $n = 0;
  $jsks = 0;
  $jskst = 0;
  $jsksp = 0;
  $jsksk = 0;

  foreach ($dataNilai as $itemNilai) {
    $n++;
    $jsks += $itemNilai->vsks_mk;
    $jskst += $itemNilai->vsks_tm;
    $jsksp += $itemNilai->vsks_prak;
    $jsksk += $itemNilai->vsks_prak_lap;
    $vid_kls = $itemNilai->vid_kls;

    // Dosen Pengampu
    $sqlPengampu = "select nm_ptk as dosen_pengampu 
                      from wsia_ajar_dosen,wsia_dosen,wsia_dosen_pt 
                      where wsia_ajar_dosen.id_reg_ptk=wsia_dosen_pt.xid_reg_ptk 
                      and wsia_dosen.xid_ptk=wsia_dosen_pt.id_ptk 
                      and wsia_ajar_dosen.id_kls=:kls";
    $stmtPeng = $db->prepare($sqlPengampu);
    $stmtPeng->execute(['kls' => $vid_kls]);
    $dataPengampu = $stmtPeng->fetchAll(PDO::FETCH_OBJ);
    $jPengampu = count($dataPengampu);

    $rowspanPengampu = ($jPengampu > 1) ? " rowspan='" . $jPengampu . "' " : "";

    $isi .= "<tr>
                   <td align='center' valign='top' " . $rowspanPengampu . ">" . $n . "</td>
                   <td align='center' valign='top' " . $rowspanPengampu . ">" . $itemNilai->kode_mk . "</td>
                   <td align='left' valign='top' " . $rowspanPengampu . ">" . $itemNilai->nm_mk . "</td>
                   <td align='center' valign='top' " . $rowspanPengampu . ">" . $itemNilai->vsks_mk . "</td>
                   <td align='center' valign='top' " . $rowspanPengampu . ">" . $itemNilai->vsks_tm . "</td>
                   <td align='center' valign='top' " . $rowspanPengampu . ">" . $itemNilai->vsks_prak . "</td>
                   <td align='center' valign='top' " . $rowspanPengampu . ">" . $itemNilai->vsks_prak_lap . "</td>";

    if ($jPengampu > 0) {
      $iPengampu = 0;
      foreach ($dataPengampu as $itemPengampu) {
        if ($iPengampu == 0) {
          $isi .= "<td>" . $itemPengampu->dosen_pengampu . "</td></tr>";
        } else {
          $isi .= "<tr><td>" . $itemPengampu->dosen_pengampu . "</td></tr>";
        }
        $iPengampu++;
      }
    } else {
      $isi .= "<td>&nbsp;<br></td></tr>";
    }
  }

  // Fill blank rows
  for ($j = $n; $j < 12; $j++) {
    $isi .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
  }

  $isi .= "<tr>
             <td colspan='3' align='right'>Total SKS</td>
             <td align='center'>" . $jsks . "</td>
             <td align='center'>" . $jskst . "</td>
             <td align='center'>" . $jsksp . "</td>
             <td align='center'>" . $jsksk . "</td>
             <td>&nbsp;</td>
          </tr>";

  $bawah = "</table>
            <table width='90%' border='0'>
                <tr>
                  <td width='240' align='center'>Mengetahui,</td>
                  <td rowspan='4' align='center'>$qrcode</td>
                  <td  width='240' align='center'>Surakarta, " . format_tanggal(date('Y-m-d')) . "</td>
                </tr>
                <tr>
                  <td align='center'>Dosen Pembimbing </td>
                  <td></td>
                  <td align='center'>Mahasiswa</td>
                </tr>
                <tr>
                  <td>&nbsp;<br><br></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td align='center'><br><b>" . $pa . "</b></td>
                  <td>&nbsp;</td>
                  <td align='center'> <br><b>" . $dataMhs->nm_pd . "</b></td>
                </tr>
            </table>";

  return $header . $atas . $isiheader . $isi . $bawah;
}

function generate_khs_pdf_content($db, $xid_reg_pd, $id_smt)
{
  // Logic adapted from public/mhs/api/khs_pdf.php

  $th_awal = substr($id_smt, 0, 4);
  $th_akhir = $th_awal + 1;
  $smt = (substr($id_smt, 4, 1) == "1") ? "GANJIL" : "GENAP";

  // Get Student Base Data
  $qryMhsKrs = "select wsia_nilai.xid_reg_pd, nipd, nm_pd, nm_jenj_didik, nm_lemb, pa 
                  from wsia_nilai, wsia_mahasiswa, wsia_mahasiswa_pt, wsia_kelas_kuliah, wsia_mata_kuliah, wsia_sms, wsia_jenjang_pendidikan 
                  where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls 
                  and wsia_mahasiswa_pt.xid_reg_pd=wsia_nilai.xid_reg_pd 
                  and wsia_mahasiswa.xid_pd=wsia_mahasiswa_pt.id_pd 
                  and wsia_mahasiswa_pt.id_sms=wsia_sms.xid_sms 
                  and wsia_sms.id_jenj_didik=wsia_jenjang_pendidikan.id_jenj_didik 
                  and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk 
                  and wsia_mahasiswa_pt.xid_reg_pd=:id 
                  and wsia_kelas_kuliah.id_smt=:smt LIMIT 1";

  $stmt = $db->prepare($qryMhsKrs);
  $stmt->execute(['id' => $xid_reg_pd, 'smt' => $id_smt]);
  $dataMhsKrs = $stmt->fetch(PDO::FETCH_OBJ);

  if (!$dataMhsKrs)
    return "Tidak ada data KHS untuk mahasiswa ini pada semester " . $id_smt;

  $adaData = 1;

  // PA Name
  $id_ptk = $dataMhsKrs->pa;
  $sqlPa = "select * from wsia_dosen where xid_ptk=:id_ptk";
  $stmtPa = $db->prepare($sqlPa);
  $stmtPa->execute(['id_ptk' => $id_ptk]);
  $dataPa = $stmtPa->fetch(PDO::FETCH_OBJ);
  $pa = ($stmtPa->rowCount() > 0) ? $dataPa->nm_ptk : "-";

  // Qr Code
  $dataQR = "http://document.poltekindonusa.ac.id/view_khs-" . ord(substr($dataMhsKrs->nipd, 0, 1)) . '_' . (substr($dataMhsKrs->nipd, 1, 5) * 666) . "-" . ($id_smt * 666) . ".html";
  $qrcode = "<barcode code='" . $dataQR . "' type='QR' class='barcode' size='1.5' error='L' />";

  $header = "
    <table width='100%' align='left'>
     <tr>
        <td rowspan='3' align='center' width='95' valign='center'><img src='../gambar/logo_pt.jpg' height='90'></td>
        <td align='left'><h3>POLITEKNIK INDONUSA SURAKARTA</h3></td>
      </tr>
      <tr>
        <td align='left'>
            Kampus 1: Jl. KH. Samanhudi No.31, Laweyan Surakarta, Telp: 0271 - 743479<br>
            Kampus 2: Jl. Palem No.8 Cemani, Grogol, Sukoharjo, Telp: 0271 - 7464173
        </td>
      </tr>
      <tr><td>&nbsp;</td></tr>
      <tr><td colspan='2'>&nbsp;</td></tr>
      <tr>
        <td align='center' colspan='2'><h3><u>KARTU HASIL STUDI ONLINE</u></h3> </td>
      </tr>
    </table>";

  $atas = "
    <table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt; font-family:Arial; color:#000; padding:5px;'>
      <tr>
        <td>NIM</td> <td>: " . $dataMhsKrs->nipd . " </td> <td>&nbsp;</td>
        <td>Semester </td> <td>: " . $smt . " </td>
      </tr>
      <tr>
        <td>Nama</td> <td>: " . $dataMhsKrs->nm_pd . " </td> <td>&nbsp;</td>
        <td>Tahun Akademik</td> <td>: " . $th_awal . "/" . $th_akhir . " </td>
      </tr>
      <tr>
        <td>Program Studi </td> <td>: " . $dataMhsKrs->nm_jenj_didik . "-" . $dataMhsKrs->nm_lemb . " </td> <td>&nbsp;</td>
        <td>Pembimbing Akademik</td> <td>: " . $pa . " </td>
      </tr>
    </table>";

  $isiheader = "<center>
        <table width='100%' border='1' align='left' style='border-collapse: collapse; font-size:10pt; font-family:Arial; color:#000; padding:5px;'>
          <tr>
            <td rowspan='2' align='center' valign='middle' width='20'>No</td>
            <td rowspan='2' align='center' valign='middle' width='20'>Kode<br>Mata Kuliah </td>
            <td rowspan='2' align='center' valign='middle' width='380'>Mata Kuliah </td>    
            <td rowspan='2' align='center' valign='middle' width='20'>SKS</td>
            <td colspan='2' align='center' valign='middle' width='100'>NILAI</td>
            <td rowspan='2' align='center' valign='middle' width='90'>SKS x NILAI</td>    
          </tr>
          <tr>
            <td align='center' valign='middle' width='50'>ANGKA</td>
            <td align='center' valign='middle' width='50'>HURUF</td>
          </tr>";

  // Re-query for list
  $qryList = "select id_nilai, wsia_nilai.nilai_tampil as akses, IF(wsia_nilai.nilai_tampil = '3', nilai_angka, '0.00') as nilai_angka, IF(wsia_nilai.nilai_tampil = '3', nilai_huruf, '') as nilai_huruf, IF(wsia_nilai.nilai_tampil = '3', nilai_indeks, '0.00') as nilai_indeks, kode_mk, nm_mk, wsia_kelas_kuliah.sks_mk as vsks_mk 
                from wsia_nilai, wsia_kelas_kuliah, wsia_mata_kuliah 
                where wsia_nilai.xid_kls=wsia_kelas_kuliah.xid_kls 
                and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk 
                and wsia_nilai.xid_reg_pd=:id 
                and wsia_kelas_kuliah.id_smt=:smt";

  $stmt = $db->prepare($qryList);
  $stmt->execute(['id' => $xid_reg_pd, 'smt' => $id_smt]);
  $dataNilai = $stmt->fetchAll(PDO::FETCH_OBJ);

  $isi = "";
  $n = 0;
  $jsks = 0;
  $jnXsks = 0;

  foreach ($dataNilai as $itemNilai) {
    $n++;
    $jsks += $itemNilai->vsks_mk;
    $na = $itemNilai->nilai_indeks * $itemNilai->vsks_mk;
    $jnXsks += $na;

    $isi .= "
          <tr height='20'>
            <td align='center' valign='middle'>" . $n . "</td>
            <td align='center' valign='middle'>" . $itemNilai->kode_mk . "</td>
            <td valign='middle'>" . $itemNilai->nm_mk . "</td>    
            <td align='center' valign='middle'>" . $itemNilai->vsks_mk . "</td>";

    if ($itemNilai->akses == '2') {
      $isi .= "<td align='center' valign='middle'>0.00</td><td align='center' valign='middle'></td><td align='center' valign='middle'>0.00</td>";
    } else {
      $isi .= "<td align='center' valign='middle'>" . number_format($itemNilai->nilai_indeks, 2) . "</td>
                      <td align='center' valign='middle'>" . $itemNilai->nilai_huruf . "</td>
                      <td align='center' valign='middle'>" . number_format($na, 2) . "</td>";
    }
    $isi .= "</tr>";
  }

  $ipsmt = ($jsks > 0) ? $jnXsks / $jsks : 0;

  // Cumulative (IPK)
  $qryNilaiSebelum = "select wsia_kelas_kuliah.sks_mk as vsks_mk, nilai_indeks 
                        from wsia_kelas_kuliah, wsia_sms, wsia_mata_kuliah, wsia_nilai 
                        where wsia_kelas_kuliah.id_sms = wsia_sms.xid_sms 
                        and wsia_kelas_kuliah.id_mk=wsia_mata_kuliah.xid_mk 
                        and wsia_kelas_kuliah.id_smt <= :smt 
                        and wsia_kelas_kuliah.xid_kls=wsia_nilai.xid_kls 
                        and wsia_nilai.xid_reg_pd=:id";
  $stmtPre = $db->prepare($qryNilaiSebelum);
  $stmtPre->execute(['id' => $xid_reg_pd, 'smt' => $id_smt]);
  $dataNilaiSebelum = $stmtPre->fetchAll(PDO::FETCH_OBJ);

  $jsksK = 0;
  $jnXsksK = 0;
  foreach ($dataNilaiSebelum as $itemNilaiSebelum) {
    $jsksK += $itemNilaiSebelum->vsks_mk;
    $naK = $itemNilaiSebelum->nilai_indeks * $itemNilaiSebelum->vsks_mk;
    $jnXsksK += $naK;
  }
  $ipK = ($jsksK > 0) ? $jnXsksK / $jsksK : 0;

  $isi .= "
      <tr>
        <td colspan='3' align='center'>Total</td>    
        <td align='center'>" . $jsks . "</td>
        <td colspan='2'>&nbsp;</td>    
        <td align='center'>" . number_format($jnXsks, 2) . "</td>   
      </tr>
      <tr>
        <td colspan='7' align='left'>
        <table width='50%' style='border-collapse: collapse; font-size:10pt; font-family:Arial; color:#000; padding:5px;'>
             <tr>
                 <td>Index Prestasi Semester</td><td>: " . number_format($ipsmt, 2) . " </td>
                 <td>SKS Semester</td><td>: " . $jsks . "</td>
             </tr>
             <tr>
                 <td>Index Prestasi Kumulatif</td><td>: " . number_format($ipK, 2) . " </td>
                 <td>SKS Kumulatif</td><td>: " . $jsksK . "</td>
             </tr>
        </table>
        </td>          
      </tr>";

  // Wadir Name
  $npsn = 'NPSN_PLACEHOLDER';
  // Assuming NPSN constant is defined in config or login_auth. If not, we use query to get any.
  $sqlWadir = "select nama_wadir from wsia_satuan_pendidikan LIMIT 1";
  $stmtWadir = $db->query($sqlWadir);
  $dataWadir = $stmtWadir->fetch(PDO::FETCH_OBJ);
  $namaWadir = $dataWadir ? $dataWadir->nama_wadir : "-";

  $bawah = "</table>
        <br>
        <table width='100%' border='0' align='left' style='border-collapse: collapse; font-size:10pt; font-family:Arial; color:#000; padding:5px;'>
          <tr>
            <td width='50%' align='left'>
                Keterangan:<br>
                <table width='150' style='border-collapse: collapse; font-size:10pt; font-family:Arial; color:#000; padding:5px;'>
                      <tr><td><strong>Angka</strong></td><td><strong>Huruf</strong></td></tr>
                      <tr><td>0.00 - 0.99  </td><td align='center'>E</td></tr>
                      <tr><td>1.00 - 1.99 </td><td align='center'>D</td></tr>
                      <tr><td>2.00 - 2.99 </td><td align='center'>C</td></tr>
                      <tr><td>3.00 - 3.99 </td><td align='center'>B</td></tr>
                      <tr><td>4.00</td><td align='center'>A</td></tr>
                </table>
            </td>
            <td> $qrcode </td>
            <td width='50%' align='center'>
                Surakarta, " . format_tanggal(date('Y-m-d')) . "<br>
                Wakil Direktur I, <br><br><br><br><br>
                " . $namaWadir . "
            </td>
          </tr>
        </table> </center>";

  return $header . $atas . $isiheader . $isi . $bawah;
}

function generate_kartu_ujian_pdf_content($db, $xid_reg_pd, $id_smt, $tipe_ujian)
{
  // Logic adapted from public/mhs/api/kartu_ujian_pdf.php
  // Modified for mPDF compatibility (Tables instead of Flex/Grid)

  $tipe_ujian = strtoupper($tipe_ujian);
  $jenis_ujian_text = ($tipe_ujian == 'UTS') ? 'TENGAH SEMESTER' : 'AKHIR SEMESTER';
  $smt_text = "";
  $tahun_akademik_text = "";
  if (strlen($id_smt) >= 5) {
    $smt_kode = substr($id_smt, 4, 1);
    $smt_text = ($smt_kode == "1") ? "GANJIL" : (($smt_kode == "2") ? "GENAP" : "PENDEK");

    $th_awal = substr($id_smt, 0, 4);
    $th_akhir = $th_awal + 1;
    $tahun_akademik_text = $th_awal . "/" . $th_akhir;
  }

  $keterangan_id = $jenis_ujian_text . " " . $smt_text;

  // Fetch Student Data
  $qryMhs = "SELECT wmp.*, wm.*, ws.nm_lemb, ws.kode_prodi, wj.nm_jenj_didik 
               FROM wsia_mahasiswa_pt wmp
               JOIN wsia_mahasiswa wm ON wmp.id_pd = wm.xid_pd
               JOIN wsia_sms ws ON wmp.id_sms = ws.xid_sms
               JOIN wsia_jenjang_pendidikan wj ON ws.id_jenj_didik = wj.id_jenj_didik
               WHERE wmp.xid_reg_pd = :xid_reg_pd";
  $stmt = $db->prepare($qryMhs);
  $stmt->execute(['xid_reg_pd' => $xid_reg_pd]);
  $dataMhs = $stmt->fetch(PDO::FETCH_OBJ);

  if (!$dataMhs)
    return "Data mahasiswa tidak ditemukan";

  // Fetch Course Data
  $qryKrs = "SELECT wn.id_nilai, wkk.xid_kls, wkk.nm_kls, wmk.kode_mk, wmk.nm_mk, 
                      wkk.sks_mk, (
                        SELECT wd.nm_ptk
                        FROM wsia_ajar_dosen wad
                        JOIN wsia_dosen_pt wdp ON wad.id_reg_ptk = wdp.xid_reg_ptk
                        JOIN wsia_dosen wd ON wdp.id_ptk = wd.xid_ptk
                        WHERE wad.id_kls = wkk.xid_kls LIMIT 1
                      ) as nama_dosen
               FROM wsia_nilai wn
               JOIN wsia_kelas_kuliah wkk ON wn.xid_kls = wkk.xid_kls
               JOIN wsia_mata_kuliah wmk ON wkk.id_mk = wmk.xid_mk
               WHERE wn.xid_reg_pd = :xid_reg_pd AND wkk.id_smt = :id_smt
               ORDER BY wmk.nm_mk ASC";
  $stmtKrs = $db->prepare($qryKrs);
  $stmtKrs->execute(['xid_reg_pd' => $xid_reg_pd, 'id_smt' => $id_smt]);
  $dataKrs = $stmtKrs->fetchAll(PDO::FETCH_OBJ);

  // Validation Token logic (simplified for batch)
  $token = hash('sha256', $dataMhs->nipd . $id_smt . $tipe_ujian . 'POLTEK_INDONUSA_KARTU_UJIAN_2025');

  // QR Code URL
  $validation_url = "https://siakadv2.poltekindonusa.ac.id/validasi-kartu-ujian.html?" .
    "nim=" . urlencode($dataMhs->nipd) .
    "&angkatan=" . urlencode(substr($dataMhs->mulai_smt, 0, 4)) .
    "&kode_prodi=" . urlencode($dataMhs->kode_prodi) .
    "&semester=" . urlencode($id_smt) .
    "&tipe=" . urlencode($tipe_ujian) .
    "&token=" . urlencode($token);

  $qrcode = "<barcode code='" . $validation_url . "' type='QR' class='barcode' size='0.8' error='L' />";

  $tgl_cetak = date('d-m-Y');

  // Layout with Table (mPDF compatible)
  $html = "
    <style>
        .card-container { width: 100%; border: 1px solid #333; font-family: 'Times New Roman', serif; margin-bottom: 2mm; height: 85mm; overflow: hidden; padding: 0; }
        .info-table { width: 100%; font-size: 9pt; line-height: 1.1; }
        .matkul-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
        .matkul-table th, .matkul-table td { border: 1px solid #000; padding: 1px 3px; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .kop-img { height: 55px; }
        .title-text { font-size: 10pt; line-height: 1; }
        .subtitle-text { font-size: 8pt; line-height: 1; }
    </style>
    <div class='card-container'>
        <table width='100%' cellpadding='5' border='0' style='border-collapse: collapse;'>
            <tr>
                <td width='40%' valign='top' style='border-right: 1px solid #333; padding: 5px 10px;'>
                    <div class='text-center'>
                        <img src='../gambar/logo_pt.jpg' class='kop-img'><br>
                        <span class='text-bold title-text'>KARTU UJIAN</span><br>
                        <span class='text-bold subtitle-text'>$keterangan_id</span><br>
                        <span class='text-bold subtitle-text'>TAHUN AKADEMIK $tahun_akademik_text</span>
                    </div>
                    <br>
                    <table class='info-table'>
                        <tr><td width='60'>NAMA</td><td width='10'>:</td><td>" . strtoupper(htmlspecialchars($dataMhs->nm_pd)) . "</td></tr>
                        <tr><td>NIM</td><td>:</td><td>" . htmlspecialchars($dataMhs->nipd) . "</td></tr>
                        <tr><td>PRODI</td><td>:</td><td>" . strtoupper(htmlspecialchars($dataMhs->nm_lemb)) . "</td></tr>
                    </table>
                    <table width='100%' style='margin-top: 3px;'>
                        <tr>
                            <td width='55%' align='left'>
                                $qrcode<br>
                                <span style='font-size: 5pt;'>Scan validasi</span>
                            </td>
                            <td width='45%' align='center' style='font-size: 8.5pt; vertical-align: bottom;'>
                              <div style='display: inline-block; text-align: center;'>
                                  Surakarta, $tgl_cetak<br>
                                  Wakil Direktur I
                                  <br><br><br><br>
                                  <strong>Edy Susena, M.Kom</strong>
                              </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width='60%' valign='top' style='padding: 5px 10px;'>
                    <div class='text-center text-bold' style='margin-bottom: 3px; font-size: 9pt;'>MATA KULIAH</div>
                    <table class='matkul-table'>
                        <thead>
                            <tr>
                                <th width='25'>No</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th width='35'>Paraf</th>
                            </tr>
                        </thead>
                        <tbody>";

  $no = 1;
  if (!empty($dataKrs)) {
    foreach ($dataKrs as $row) {
      $nama_dosen = !empty($row->nama_dosen) ? $row->nama_dosen : '-';
      if (strlen($nama_dosen) > 30)
        $nama_dosen = substr($nama_dosen, 0, 30) . '...';
      
      $nm_mk = htmlspecialchars($row->nm_mk);
      $nama_dosen = htmlspecialchars($nama_dosen);

      $html .= "
                            <tr>
                                <td class='text-center'>$no</td>
                                <td>" . $nm_mk . "</td>
                                <td>$nama_dosen</td>
                                <td></td>
                            </tr>";
      $no++;
    }
  }

  // Filler rows to keep height consistent (reduced for 3-per-page)
  for ($i = $no; $i <= 8; $i++) {
    $html .= "<tr><td class='text-center'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
  }

  $html .= "
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>";

  return $html;
}


// --- MAIN LOGIC ---

$action = $_GET['action'] ?? '';
$db = koneksi();

if ($action === 'options') {
  // Return filters similar to data_sebaran.php
  $sqlProdi = "SELECT xid_sms as id, nm_lemb as text FROM wsia_sms WHERE nm_lemb IS NOT NULL ORDER BY nm_lemb";
  $prodi = $db->query($sqlProdi)->fetchAll(PDO::FETCH_ASSOC);

  $sqlSmt = "SELECT DISTINCT id_smt FROM wsia_kelas_kuliah ORDER BY id_smt DESC LIMIT 10";
  $smtRaw = $db->query($sqlSmt)->fetchAll(PDO::FETCH_COLUMN);
  $semester = [];
  foreach ($smtRaw as $sid) {
    $tahun = substr($sid, 0, 4);
    $smtCode = substr($sid, 4, 1);
    $smtName = ($smtCode == '1') ? 'Ganjil' : (($smtCode == '2') ? 'Genap' : 'Pendek');
    $semester[] = ['id' => $sid, 'text' => "$tahun/" . ($tahun + 1) . " " . $smtName];
  }

  // Angkatan: dynamic based on current year
  $currentYear = date('Y');
  $angkatan = [];
  for ($i = 0; $i < 8; $i++) {
    $y = $currentYear - $i;
    $angkatan[] = ['id' => $y, 'text' => $y];
  }

  echo json_encode(['prodi' => $prodi, 'semester' => $semester, 'angkatan' => $angkatan]);
  exit;

} elseif ($action === 'get_kelas') {
  $sem = $_GET['semester'];
  $prodi = $_GET['prodi'];
  $angkatan = $_GET['angkatan'] ?? null;

  $sql = "SELECT DISTINCT k.nm_kls as id, k.nm_kls as text 
            FROM wsia_kelas_kuliah k
            JOIN wsia_nilai n ON k.xid_kls = n.xid_kls
            JOIN wsia_mahasiswa_pt pt ON n.xid_reg_pd = pt.xid_reg_pd
            WHERE k.id_smt = :sem AND k.id_sms = :prodi";

  $params = ['sem' => $sem, 'prodi' => $prodi];
  if ($angkatan) {
    $sql .= " AND LEFT(pt.mulai_smt, 4) = :angkatan";
    $params['angkatan'] = $angkatan;
  }

  $sql .= " ORDER BY k.nm_kls";
  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;

} elseif ($action === 'list_students') {
  $sem = $_GET['semester'];
  $prodi = $_GET['prodi'];
  $angkatan = $_GET['angkatan'];
  $kelas = $_GET['kelas'] ?? null;

  // Filter by angkatan (mulai_smt) and prodi
  $sql = "SELECT DISTINCT pt.xid_reg_pd, pt.nipd, mhs.nm_pd, sms.nm_lemb as prodi, LEFT(pt.mulai_smt,4) as angkatan
            FROM wsia_mahasiswa_pt pt
            JOIN wsia_mahasiswa mhs ON pt.id_pd = mhs.xid_pd
            JOIN wsia_sms sms ON pt.id_sms = sms.xid_sms
            JOIN wsia_nilai n ON pt.xid_reg_pd = n.xid_reg_pd
            JOIN wsia_kelas_kuliah k ON n.xid_kls = k.xid_kls
            WHERE pt.id_sms = :prodi 
            AND LEFT(pt.mulai_smt, 4) = :angkatan
            AND k.id_smt = :sem";

  $params = ['prodi' => $prodi, 'angkatan' => $angkatan, 'sem' => $sem];

  if ($kelas) {
    $sql .= " AND k.nm_kls = :kelas";
    $params['kelas'] = $kelas;
  }

  $sql .= " ORDER BY pt.nipd";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($data);
  exit;

} elseif ($action === 'download_single') {
  $type = $_GET['type']; // krs, khs, uts, uas
  $id = $_GET['id'];
  $sem = $_GET['semester'];

  // Generate PDF
  if ($type === 'uts' || $type === 'uas') {
    $mpdf = new mPDF('-s', 'A4', '', '', 7, 7, 7, 7, 5, 5);
  } else {
    $mpdf = new mPDF('-s', 'Legal', '', '', 10, 10, 10, 10, 5, 5);
  }
  $mpdf->showWatermarkText = false;

  if ($type === 'krs' || $type === 'khs') {
    $stylesheet = file_get_contents('../lib/pdf/krs.css');
    $mpdf->WriteHTML($stylesheet, 1);
  }

  $html = "";
  if ($type === 'krs') {
    $mpdf->setHTMLFooter('<div style="text-align:right;">KRS Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
    $html = generate_krs_pdf_content($db, $id, $sem);
    $filename = "KRS_{$sem}_{$id}.pdf";
  } elseif ($type === 'khs') {
    $mpdf->setHTMLFooter('<div style="text-align:right;">KHS Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
    $html = generate_khs_pdf_content($db, $id, $sem);
    $filename = "KHS_{$sem}_{$id}.pdf";
  } elseif ($type === 'uts' || $type === 'uas') {
    $html = generate_kartu_ujian_pdf_content($db, $id, $sem, $type);
    $filename = "Kartu_Ujian_" . strtoupper($type) . "_{$sem}_{$id}.pdf";
  }

  $mpdf->WriteHTML($html);

  // Clean all output buffers before PDF output
  while (ob_get_level()) {
    ob_end_clean();
  }

  $mpdf->Output($filename, 'D'); // Download
  exit;

} elseif ($action === 'download_zip') {
  // Expect POST with ids json
  $type = $_POST['type'];
  $sem = $_POST['semester'];
  $ids = json_decode($_POST['ids'], true);

  // Debug logging (temporary)
  error_log("Data dokumen - Type: " . $type . ", Semester: " . $sem);
  error_log("IDs received: " . print_r($ids, true));
  error_log("Total IDs: " . count($ids));

  if (!$ids || count($ids) == 0)
    exit("No Data");

  // SPECIAL CASE FOR UTS/UAS: Generate single PDF with 3 cards per page
  if ($type === 'uts' || $type === 'uas') {
    $mpdf = new mPDF('-s', 'A4', '', '', 7, 7, 7, 7, 5, 5);
    $mpdf->showWatermarkText = false;

    $count = 0;
    $totalCards = count($ids);
    error_log("Starting card generation for " . $totalCards . " students");

    foreach ($ids as $id) {
      try {
          $count++;
          // error_log("Generating card for ID: " . $id . " (card #" . $count . " of " . $totalCards . ")");
    
          $cardHtml = generate_kartu_ujian_pdf_content($db, $id, $sem, $type);
          $mpdf->WriteHTML($cardHtml);
    
          // Add page break after every 3 cards, but not after the last card
          if ($count % 3 == 0 && $count < $totalCards) {
            $mpdf->AddPage();
            // error_log("Added page break after card #" . $count);
          } else if ($count < $totalCards) {
            // Add spacer between cards on the same page (not after the last card on a page)
            $mpdf->WriteHTML("<div style='height: 2mm;'></div>");
          }
      } catch (Throwable $e) {
          error_log("Error generating card for ID $id: " . $e->getMessage());
      }
    }
    error_log("Finished generating " . $count . " cards total");

    // Clean all output buffers before PDF output
    while (ob_get_level()) {
      ob_end_clean();
    }

    $mpdf->Output("Batch_Kartu_Ujian_" . strtoupper($type) . "_{$sem}.pdf", 'D');
    exit;
  }

  // DEFAULT CASE: ZIP for KRS/KHS
  $zip = new ZipArchive();
  $tmp_file = tempnam(sys_get_temp_dir(), 'zip_doc_');
  $zip->open($tmp_file, ZipArchive::CREATE);

  $stylesheet = "";
  if ($type === 'krs' || $type === 'khs') {
    $stylesheet = file_get_contents('../lib/pdf/krs.css');
  }

  $cnt = 0;
  foreach ($ids as $id) {
    $mpdf = new mPDF('-s', 'Legal');
    if ($stylesheet)
      $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->SetWatermarkText('POLINUS');
    $mpdf->showWatermarkText = false;
    $mpdf->watermarkTextAlpha = 0.1;

    $html = "";
    $filenameInfo = "";

    // Fetch NIM for filename
    $stmtNim = $db->prepare("SELECT nipd, nm_pd FROM viewMahasiswaPt WHERE no_pend = :id OR xid_reg_pd = :id2 LIMIT 1");
    $stmtNim->execute(['id' => $id, 'id2' => $id]);
    $mhs = $stmtNim->fetch(PDO::FETCH_ASSOC);
    $nim = $mhs ? $mhs['nipd'] : $id;
    $nama = $mhs ? preg_replace('/[^a-zA-Z0-9]/', '_', $mhs['nm_pd']) : 'Mhs';

    if ($type === 'krs') {
      $mpdf->setHTMLFooter('<div style="text-align:right;">KRS Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
      $html = generate_krs_pdf_content($db, $id, $sem);
      $filenameInfo = "KRS_{$nim}_{$nama}.pdf";
    } elseif ($type === 'khs') {
      $mpdf->setHTMLFooter('<div style="text-align:right;">KHS Online | Politeknik Indonusa Surakarta [ {PAGENO} / {nbpg} ]</div>');
      $html = generate_khs_pdf_content($db, $id, $sem);
      $filenameInfo = "KHS_{$nim}_{$nama}.pdf";
    }

    $mpdf->WriteHTML($html);
    $pdfContent = $mpdf->Output('', 'S'); // String output

    $zip->addFromString($filenameInfo, $pdfContent);
    $cnt++;

    // Memory management
    unset($mpdf);
  }

  $zip->close();

  // Clean all output buffers before file output
  while (ob_get_level()) {
    ob_end_clean();
  }

  header('Content-Type: application/zip');
  header('Content-disposition: attachment; filename=Dokumen_' . strtoupper($type) . '_Batch_' . $sem . '.zip');
  header('Content-Length: ' . filesize($tmp_file));
  readfile($tmp_file);
  unlink($tmp_file);
  exit;
}
?>