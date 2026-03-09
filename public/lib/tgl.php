<?php
function format_tanggal($tgl) {
	$vtanggal=strtotime($tgl." 00:00:00");
	//echo $vtanggal;
	
	$ftahun=date("Y",$vtanggal);
	$fbulan=date("n",$vtanggal);
	$ftgl=date("d",$vtanggal);
	$array_bulan = array(1=>"Januari","Februari","Maret", "April", "Mei","Juni","Juli","Agustus","September","Oktober", "November","Desember");
	$bulan = $array_bulan[$fbulan];

	$ftanggal=$ftgl." ".$bulan." ".$ftahun;
	return $ftanggal;
}

function format_tanggal2($tgl) {
	$vtanggal=strtotime($tgl." 00:00:00");
	//echo $vtanggal;
	
	$ftahun=date("Y",$vtanggal);
	$fbulan=date("n",$vtanggal);
	$ftgl=date("d",$vtanggal);
	if ($fbulan<10) {
		$bulan="0".$fbulan;
	} else {
		$bulan=$fbulan;
	}
	$ftanggal=$ftgl."-".$bulan."-".$ftahun;
	return $ftanggal;
}

function format_tanggal_waktu($tgl) {
	$vtanggal=strtotime($tgl);
	//echo $vtanggal;
	
	$ftahun=date("Y",$vtanggal);
	$fbulan=date("n",$vtanggal);
	$ftgl=date("d",$vtanggal);
	$fwaktu=date("H:i:s",$vtanggal);
	if ($fbulan<10) {
		$bulan="0".$fbulan;
	} else {
		$bulan=$fbulan;
	}
	$ftanggal=$ftgl."-".$bulan."-".$ftahun." ".$fwaktu;
	return $ftanggal;
}

function format_tanggal_bln($tgl) {
	$vtanggal=strtotime($tgl." 00:00:00");
	//echo $vtanggal;
	
	$ftahun=date("Y",$vtanggal);
	$fbulan=date("n",$vtanggal);
	$ftgl=date("d",$vtanggal);
	$array_bulan = array(1=>"Januari","Februari","Maret", "April", "Mei","Juni","Juli","Agustus","September","Oktober", "November","Desember");
	$bulan = $array_bulan[$fbulan];

	$ftanggal=$ftgl." ".$bulan." ".$ftahun;
	return $ftanggal;
}

function format_hari($tgl) {
	
	$tanggal = strtotime($tgl);
	$array_hari = array(1=>"Senin","Selasa","Rabu","Kamis","Jum&rsquo;at","Sabtu","Minggu");
	$hari = $array_hari[date("N",$tanggal)];
	return $hari;
}
//Array Hari
$array_hari = array(1=>"Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu");
$hari = $array_hari[date("N")];
//Format Tanggal
$tanggal = date ("j");
//Array Bulan
$array_bulan = array(1=>"Januari","Februari","Maret", "April", "Mei","Juni","Juli","Agustus","September","Oktober", "November","Desember");
$bulan = $array_bulan[date("n")];
//Format Tahun
$tahun = date("Y");
//Menampilkan tanggal
$tanggal =$tanggal ." ". $bulan ." ". $tahun;