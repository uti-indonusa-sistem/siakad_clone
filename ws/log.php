<?php
$dir ="log";
/*
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            echo "filename: .".$file."<br />";
        }
        closedir($dh);
    }
}
*/

$no=0;
foreach (glob("log/*.*") as $filename) {
    //echo $filename."<br />";
	$no++;
    $string = file_get_contents($filename);
	$json_a = json_decode($string, true);

	echo("'".$json_a['data']['vid_mk2'])."',";


}

echo "<br>";
echo $no;
?>