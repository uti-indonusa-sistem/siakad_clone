<?php

function koneksi_sikeuv2() {
    $dbhost="localhost";
    $dbuser="ukeukeu";
    $dbpass="^Rtr251Gtf_hGt";
    $dbname="sikeudb";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array( PDO::ATTR_PERSISTENT => false ));  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}