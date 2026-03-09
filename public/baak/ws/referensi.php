<?php
if (!isset($key)) { exit(); }
include 'feeder_auth.php';
if ($key!=$_SESSION['wsiaADMIN']) { exit(); }

$feeder_token = json_decode($_SESSION['feeder_token']);

if ($aksi=="jenjangpendidikan") {	
	$sync['act']="GetJenjangPendidikan";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=50;
	$sync['offset']=0;
} else if ($aksi=="alattransportasi") {	
	$sync['act']="GetAlatTransportasi";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=50;
	$sync['offset']=0;
} else if ($aksi=="penghasilan") {	
	$sync['act']="GetPenghasilan";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=50;
	$sync['offset']=0;
} else if ($aksi=="biodatamahasiswa") {	
	$sync['act']="GetBiodataMahasiswa";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=1;
	$sync['offset']=0;
} else if ($aksi=="prodi") {	
	$sync['act']="GetProdi";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=10;
	$sync['offset']=0;
} else if ($aksi=="jalurmasuk") {	
	$sync['act']="GetJalurMasuk";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=10;
	$sync['offset']=0;
} else if ($aksi=="jenisdaftar") {	
	$sync['act']="GetJenisPendaftaran";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=10;
	$sync['offset']=0;
} else if ($aksi=="profilpt") {	
	$sync['act']="GetProfilPT";
	$sync['token']=$feeder_token->data->token;	
	$sync['filter']="";
	$sync['limit']=10;
	$sync['offset']=0;
}  


$runWs = json_decode(runWs($sync,'json'));
echo "<pre>";
print_r ($runWs);
echo "</pre>";

?>