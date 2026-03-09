<?php

if ($aksi=="struktur") {
	$proxy = proxy();
	$token=token();
	
	$a_dosen_pt=$proxy->GetDictionary($token,"dosen_pt");
	$dosen_pt=$a_dosen_pt['result'];
	echo "Struktur dosen_pt<br><pre>";
	print_r($dosen_pt);
	echo "</pre>";
	
} else if ($aksi=="tampil") {
	$proxy = proxy();
	$token=token();

	$a_dosen_pt=$proxy->GetRecordSet($token,"dosen_pt.raw","id_sp='dea50385-fb50-4d4b-b50c-dbc539da3292' ","id_reg_ptk",800,0);
	$dosen_pt=$a_dosen_pt['result'];
	
	//echo "dosen_pt<br><pre>";
	//print_r($a_dosen_pt);
	//echo "</pre>";

	//tampil kelas dengan mata kuliah
	$jdosen_pt = count($dosen_pt);
	$adosen=array();
	for ($i=0;$i<$jdosen_pt;$i++) {
		$id_reg_ptk=$dosen_pt[$i]['id_reg_ptk'];
		$id_sdm=$dosen_pt[$i]['id_sdm'];
		
		$a_dosen=$proxy->GetRecord($token,"dosen.raw","id_sdm='$id_sdm' ");
		$dosen=$a_dosen['result'];
		
		print_r($dosen);

		$nama_dosen	=$dosen['nm_sdm'];
		
		$adosen[$i]['id_reg_ptk']=$id_reg_ptk;
		$adosen[$i]['id_sdm']=$id_sdm;
		$adosen[$i]['nm_sdm']=$nama_dosen;
	}


	//buat file
		$array =$adosen;
		$f = fopen('dosen_pt.csv', 'w');
		$firstLineKeys = false;
		foreach ($array as $line)
		{
			if (empty($firstLineKeys))
			{
				$firstLineKeys = array_keys($line);
				fputcsv($f, $firstLineKeys);
				$firstLineKeys = array_flip($firstLineKeys);
			}
			fputcsv($f, array_merge($firstLineKeys, $line));
		}
		
}	
?>