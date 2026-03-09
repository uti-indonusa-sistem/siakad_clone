<?php
  ini_set('memory_limit','-1');
  ini_set('max_execution_time',-1);
  set_time_limit(0);
  
  function proxy() {
	//$url="http://117.20.58.123:8082/ws/sandbox.php?wsdl";
	$url="http://117.20.58.123:8082/ws/live.php?wsdl";
  	$client = new nusoap_client($url,true);
	$proxy = $client->getProxy();
	return $proxy;
  }
  
  function token() {
  	$username="sulistiyo@poltekindonusa.ac.id";
  	$password="P45sw0rd123";
	$result=proxy()->GetToken($username,$password);
	return $result;
  }
 
?>