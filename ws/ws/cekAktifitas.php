<?php
if ($aksi=="cekAktifitas") {
	
	$proxy = proxy();
	$token = token();
	
	 $db 	= koneksi_wsia_off();
	$qryMahasiswaLulus = "select * from ws_kelulusan where nim='130101131'";
	$qryMahasiswaLulus .= "  LIMIT 1 ";
	$qryMahasiswaLulus .= isset($id)?' OFFSET '.$id:' OFFSET 0';
	$eksekusi 	= $db->query($qryMahasiswaLulus); 
	
	$records_mahasiswa_pt=array();
	$records_kuliah_mahasiswa=array();
	
	foreach ($eksekusi  as $dataWsLulus) {
		
		$nim=trim($dataWsLulus['nim']);
		$id_jns_keluar=$dataWsLulus['id_jns_keluar'];
		$tgl_keluar=$dataWsLulus['tgl_keluar']; 
		$ket=$dataWsLulus['ket']; 
		$jalur_skripsi=$dataWsLulus['jalur_skripsi'];
		$judul_skripsi=$dataWsLulus['judul_skripsi'];
		$bln_awal_bimbingan=$dataWsLulus['bln_awal_bimbingan'];
		$bln_akhir_bimbingan=$dataWsLulus['bln_akhir_bimbingan'];
		$sk_yudisium=$dataWsLulus['sk_yudisium'];
		$tgl_sk_yudisium=$dataWsLulus['tgl_sk_yudisium'];
		$ipk=$dataWsLulus['ipk'];
		$no_seri_ijazah=$dataWsLulus['no_seri_ijazah'];
		$id_smt=$key;
		$id_stat_mhs="A";
		//$ips=$dataWsLulus['ips'];
		//$sks_smt=$dataWsLulus['sks_smt'];
		$sks_total=$dataWsLulus['sks_total'];
		echo $nim."-";
		$a_mahasiswa_pt=$proxy->GetRecord($token,"mahasiswa_pt","nipd like '%$nim%'");
		$mahasiswa_pt=$a_mahasiswa_pt['result'];
		
		//echo "<pre>";
		//print_r($a_mahasiswa_pt);
		//echo "</pre>";
		
		$id_reg_pd=$mahasiswa_pt['id_reg_pd'];

		echo $id_reg_pd."<br>";
		
		$nilai_smt=0;
		$sks_smt=0;
		
		
		//HITUNG IPS = Nilai.s / SKS.s
		$a_nilai=$proxy->GetRecordSet($token,"nilai.raw","id_reg_pd = '$id_reg_pd' ",'id_kls',500,0);
		$nilai=$a_nilai['result'];
		
		//echo "Nilai<br><pre>";
		//print_r($nilai);
		//echo "</pre>";
		
		$j_nilai=count($nilai);
		for($j=0;$j<$j_nilai;$j++) {
			$id_kls=$nilai[$j]['id_kls'];
			$a_kelas_kuliah=$proxy->GetRecord($token,"kelas_kuliah.raw","id_kls = '$id_kls' ");
			$kelas_kuliah=$a_kelas_kuliah['result'];
			
			if ($kelas_kuliah['id_smt']==$id_smt) {
				//echo "Kelas Kuliah ".$j."<br><pre>";
				//	print_r($a_kelas_kuliah);
				//echo "</pre>";
					
				$sks_smt+=$kelas_kuliah['sks_mk'];
				$nilai_smt+=$nilai[$j]['nilai_indeks']*$kelas_kuliah['sks_mk'];
			}
		}
		
		$ips=$nilai_smt/$sks_smt;
		
		
		$key_mahasiswa_pt=array('id_reg_pd'=>$id_reg_pd);
		$data_mahasiswa_pt=array('id_jns_keluar'=>$id_jns_keluar,
				      'tgl_keluar'=>$tgl_keluar,
				      'ket'=>$ket,
				      'jalur_skripsi'=>$jalur_skripsi,
				      'judul_skripsi'=>$judul_skripsi,
				      'bln_awal_bimbingan'=>$bln_awal_bimbingan,
				      'bln_akhir_bimbingan'=>$bln_akhir_bimbingan,
				      'sk_yudisium'=>$sk_yudisium,
				      'tgl_sk_yudisium'=>$tgl_sk_yudisium,
				      'ipk'=>$ipk,
				      'no_seri_ijazah'=>$no_seri_ijazah
		);
		
		
		$data_kuliah_mahasiswa=array('id_smt'=>$id_smt,
				      'id_reg_pd'=>$id_reg_pd,
				      'id_stat_mhs'=>$id_stat_mhs,
				      'ips'=>$ips,	//hitung lagi
				      'ipk'=>$ipk,
				      'sks_smt'=>$sks_smt, //hitung lagi
				      'sks_total'=>$sks_total
		);
		
		$records_mahasiswa_pt[]=array('key'=>$key_mahasiswa_pt,'data'=>$data_mahasiswa_pt);
		$records_kuliah_mahasiswa[]=$data_kuliah_mahasiswa;
	
    
		
	} 
	
	echo "<hr>";
	echo json_encode($records_mahasiswa_pt);
	echo "<hr>";
	echo json_encode($records_kuliah_mahasiswa);


	
	
}

?>