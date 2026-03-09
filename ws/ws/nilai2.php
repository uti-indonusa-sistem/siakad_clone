<?php

if ($aksi=="sync") {
	 
   $data='{"id_kls":"dad9f4a2-a26f-476d-bd32-72aa4734c707","id_reg_pd":"e0a756a5-fb25-4825-80e8-ac8edb382f43","nilai_angka":"82.0","nilai_huruf":"A","nilai_indeks":"4.00"}';	    
		    		    
   $insert=proxy()->InsertRecord(token(),"nilai",$data);
   
   //print_r($insert['result']);

   $i=0;		  
   foreach ($insert['result'] as $itemData) {
	 $error = $itemData['error_code'];
	   
	 if ($error=="0") {
	   
		$hasil['berhasil']=1;
	    	$hasil['pesan']="Berhasil KRS";
	    	$hasil['data']=$data[$i];
	    	echo "<pre>";
		print_r($hasil);
		echo "</pre>";
		 
	 } else {
		$pesan= $itemData['error_desc'];
		$hasil['berhasil']=0;
	    	$hasil['pesan']=$itemData;
	    	$hasil['data']=$data[$i];
	    	
		echo "<pre>";
		print_r($hasil);
		echo "</pre>";
		
		$file = 'log_insert_nilai'.$id.'.txt';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= json_encode($hasil)."\n\n";
		// Write the contents back to the file
		file_put_contents($file, $current);
		
	 }
	 
	 $i++;
	 
}

		
} 