<?php

if ($aksi=="struktur") {
	$proxy = proxy();
	$token=token();
	
	$a_satuanpendidikan=$proxy->GetDictionary($token,"satuan_pendidikan");
	$satuanpendidikan=$a_satuanpendidikan['result'];
	echo "Struktur satuan_pendidikan<br><pre>";
	print_r($satuanpendidikan);
	echo "</pre>";
	
} else if ($aksi=="tampil") {
	$proxy = proxy();
	$token=token();

	$a_nilai=$proxy->GetRecord($token,"satuan_pendidikan","npsn= '061047' ");
	$nilai=$a_nilai['result'];
	echo "Data Satuan Pendidikan<br><pre>";
	print_r($nilai);
	echo "</pre>";
		
		
}	
?>