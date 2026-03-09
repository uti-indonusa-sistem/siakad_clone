<?php

if ($aksi=="struktur") {
	$proxy = proxy();
	$token=token();
	
	$a_sms=$proxy->GetDictionary($token,"sms");
	$sms=$a_sms['result'];
	echo "Struktur sms<br><pre>";
	print_r($sms);
	echo "</pre>";
	
} else if ($aksi=="tampil") {
	$proxy = proxy();
	$token= token();

	$a_nilai=$proxy->GetRecordSet($token,"sms","id_sp='55cd2bd1-f23e-40bc-a3ac-ff3b14f0f5c7'","kode_prodi",500,0);
	$nilai=$a_nilai['result'];
	echo "Data SMS<br><pre>";
	print_r($nilai);
	echo "</pre>";
	$sms=array();
	$i=0;
	foreach ($nilai as $key => $value) {
		$sms[$i]['id_sms'] = $value['id_sms']; 
		$sms[$i]['nm_lemb'] = $value['nm_lemb'];
		$sms[$i]['id_jenj_didik'] = $value['id_jenj_didik'];
		$i++;
	}

	$array =$sms;
	$f = fopen('sms.csv', 'w');
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