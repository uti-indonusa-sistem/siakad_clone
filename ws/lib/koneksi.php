<?php
  // koneksi ke database  
   function koneksi_wsia() {
    	$dbhost="localhost";
    	$dbuser="usiakad";
    	$dbpass="%Lr#g?I+UR)Q";
    	$dbname="siakaddb";
    	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array( PDO::ATTR_PERSISTENT => true ));    	
    	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	return $dbh;
   }
?>