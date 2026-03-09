<?php

if ($aksi=="struktur") {
	$proxy = proxy();
	$token=token();
	
	$a_dosen=$proxy->GetDictionary($token,"dosen");
	$dosen=$a_dosen['result'];
	echo "Struktur dosen<br><pre>";
	print_r($dosen);
	echo "</pre>";
	
} else if ($aksi=="tampil") {
	$proxy = proxy();
	$token=token();

	$adosen=$proxy->GetRecordSet($token,"dosen.raw","","",500,0);
	$dosen=$adosen['result'];
	echo "dosen<br><pre>";
	print_r($dosen);
	echo "</pre>";

	//buat file
		$array =$dosen;
		$f = fopen('dosen.csv', 'w');
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