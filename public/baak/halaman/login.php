<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>BAAK Login | POLITEKNIK INDONUSA Surakarta</title>
		<script type="text/javascript" src="<?php echo $domain;?>/lib/jquery-1.7.1.min.js"></script>
  		<script type="text/javascript" src="<?php echo $domain;?>/lib/backbone/underscore.js"></script>
  		<script type="text/javascript" src="<?php echo $domain;?>/lib/backbone/backbone.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $domain;?>/lib/webix.css">
		<link rel="stylesheet" type="text/css" href="<?php echo $domain;?>/lib/wsia.css">
		<link rel="stylesheet" href="<?php echo $domain;?>/lib/skins/compact.css" type="text/css" media="screen" charset="utf-8">
		<script src="<?php echo $domain;?>/lib/webix.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body class="app_wsia" bgcolor="#244531">
		<br>
		<center>
		<img src="<?php echo $domain;?>/gambar/logo.png" width="300">
		</center>
		
		<div id="areaLogin"></div>
	
		<script type="text/javascript" charset="utf-8">
			webix.ui({
				container:"areaLogin",
				id:"formLogin",
				css:"formLogin",
				view:"form", 
				scroll:false,
				borderless:true,
				width:300,
				elements:[
					{ view:"text", id:"user", name:"user", label:"Username", required:true, labelWidth: 100},
					{ view:"text", id:"pass", name:"pass", type:"password", label:"Password", required:true, labelWidth: 100},
					{ view:"richselect", id:"ta", name:"ta", label:"Thn Akademik", labelWidth: 100, required:true,options:"api/tahun_ajaran_login.php"},
					{ margin:5, cols:[
						{ view:"button",id:"masuk", label:"Masuk" , type:"form" }						
					]}
				]
			});
			
			//event
			$$("masuk").attachEvent("onItemClick", function(id, e){
				if ($$('formLogin').validate()) {
					dataKirim = JSON.stringify($$("formLogin").getValues());
					proses_tampil();
	   				webix.ajax().post("<?php echo $domain;?>/baak/login", dataKirim, {
   					success: function(response, data, xhr){
		   					proses_hide();
		   					hasil=JSON.parse(response);
							if (hasil.berhasil) {
								webix.message(hasil.pesan);
								webix.storage.session.put('wSia', { domain: hasil.domain, ta: hasil.ta, nama: hasil.nama, apiKey:hasil.apiKey });
								$$("formLogin").disable();
								window.location='<?php echo $domain;?>/baak';
							} else {
								webix.alert({
								    title: "Kesalahan",
								    text: hasil.pesan,
								    type:"alert-error"
								})
							}
					},
					error:function(response, data, xhr){
						proses_hide();
		        		webix.alert({
						    title: "Kesalahan",
						    text: "Gagal terkoneksi dengan server..!",
						    type:"alert-error"
						})
	    		  	}
				});
				}
			});
			
			//adding ProgressBar functionality to layout
			webix.extend($$("formLogin"), webix.ProgressBar);
		    	
		    function proses_tampil(){
			        $$("formLogin").disable();
			        $$("formLogin").showProgress({
			        		 type:"icon",
			        		 icon:"cog"
			        });
			}
			
			function proses_hide() {
			        $$("formLogin").enable();
			        $$("formLogin").hideProgress();
			}
			
		</script>
		
	</body>
</html>