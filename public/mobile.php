<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Hak Akses Ditolak | SIAKAD Permata Indonesia</title>
		<script type="text/javascript" src="lib/jquery-1.7.1.min.js"></script>
		<link rel="stylesheet" type="text/css" href="lib/webix.css">
		<link rel="stylesheet" type="text/css" href="lib/wsia.css">
		<link rel="stylesheet" href="lib/skins/compact.css" type="text/css" media="screen" charset="utf-8">
		<script src="lib/webix.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body class="app_wsiamhs" bgcolor="#500758">
		<br>
		<center>
		<img src="gambar/logo.png" width="200">
		</center>
		<div id="areaLogin"></div>
	
		<script type="text/javascript" charset="utf-8">
			webix.ui({
				container:"areaLogin",
				view:"form",  id:"formLogin", css:"formLogin", scroll:false, height:220, borderless:true,
						elements:[
							{ view:"fieldset", label:"Hak akses diTolak", body:{
								 rows:[
									{ template:"<b>Maaf!. untuk menjaga kesalahan input, Halaman SIAKAD tidak diperkenankan diakses melalui perangkat Mobile.<br>Silahkan gunakan komputer Desktop (Laptop/PC).<br>Terima kasih</b>"},
								]}
							}
						]
				
			});
		</script>
	</body>
</html>