<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Dosen Login | POLITEKNIK INDONUSA Surakarta</title>
	<script type="text/javascript" src="<?php echo $domain; ?>/lib/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="<?php echo $domain; ?>/lib/backbone/underscore.js"></script>
	<script type="text/javascript" src="<?php echo $domain; ?>/lib/backbone/backbone.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>/lib/webix.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $domain; ?>/lib/wsia.css">
	<link rel="stylesheet" href="<?php echo $domain; ?>/lib/skins/compact.css" type="text/css" media="screen"
		charset="utf-8">
	<script src="<?php echo $domain; ?>/lib/webix.js" type="text/javascript" charset="utf-8"></script>
</head>

<body class="app_wsiamhs" bgcolor="#244531">
	<br>
	<center>
		<img src="<?php echo $domain; ?>/gambar/logo.png" width="300">
	</center>
	<div id="areaLogin"></div>

	<script src="https://accounts.google.com/gsi/client" async defer></script>
	<script type="text/javascript" charset="utf-8">
		// Google Login Handler
		function handleCredentialResponse(response) {
			proses_tampil();
			webix.ajax().post("<?php echo $domain; ?>/dosen/login-google", JSON.stringify({ token: response.credential }), {
				success: function (text, data, xhr) {
					proses_hide();
					var hasil = JSON.parse(text);
					if (hasil.berhasil) {
					webix.storage.session.put('wSiaMhs', { domain: hasil.domain, nidn: hasil.nidn, xid_ptk: hasil.xid_ptk, nm_ptk: hasil.nm_ptk, apiKey: hasil.apiKey, nidnMd5: hasil.nidnMd5, email_poltek: hasil.email_poltek });
					$$("formLogin").disable();
					webix.message(hasil.pesan);
					window.location = '<?php echo $domain; ?>/dosen';
				} else {
						webix.alert({
							title: "Gagal Login Google",
							text: hasil.pesan,
							type: "alert-error"
						})
					}
				},
				error: function (text, data, xhr) {
					proses_hide();
					webix.alert({
						title: "Kesalahan",
						text: "Gagal terkoneksi dengan server..!",
						type: "alert-error"
					})
				}
			});
		}

		webix.ui({
			container: "areaLogin",
			view: "form", id: "formLogin", css: "formLogin", scroll: false, maxWidth: 450, borderless: true,
			elements: [
				{
					view: "fieldset", label: "Login Dosen", body: {
						rows: [
							{ view: "text", id: "user", name: "user", label: "NIDN", labelWidth: 100, placeholder: "Ketik NIDN", required: true, invalidMessage: "NIDN belum diisi" },
							{ view: "text", id: "pass", name: "pass", type: "password", label: "Password", labelWidth: 100, placeholder: "Ketik Password", required: true, invalidMessage: "Password belum diisi" },
							{ template: " ", height: 20, borderless: true },
							{
								margin: 5, cols: [
									{ view: "button", id: "masuk", label: "Masuk", type: "danger", height: 40 }
								]
							},
							{ template: "<div style='text-align:center; margin-top:10px;'>Atau login dengan:</div>", height: 30, borderless: true },
							{
								view: "template",
								height: 50,
								borderless: true,
								template: "<div style='display:flex; justify-content:center; margin-top: 10px;'><div id='g_id_onload' data-client_id='594821951155-gjnu9qb2g2sltb67qvrr6frjmggvc6n5.apps.googleusercontent.com' data-callback='handleCredentialResponse' data-auto_prompt='false'></div><div class='g_id_signin' data-type='standard' data-size='large' data-theme='outline' data-text='sign_in_with' data-shape='rectangular' data-logo_alignment='left'></div></div>"
							}
						]
					}
				}
			]

		});

		//event
		//event
		$$("masuk").attachEvent("onItemClick", function (id, e) {
			if ($$('formLogin').validate()) {
				dataKirim = JSON.stringify($$("formLogin").getValues());
				proses_tampil();
				webix.ajax().post("<?php echo $domain; ?>/dosen/login", dataKirim, {
					success: function (response, data, xhr) {
						proses_hide();
						hasil = JSON.parse(response);
						if (hasil.berhasil) {
							webix.storage.session.put('wSiaMhs', { domain: hasil.domain, nidn: hasil.nidn, xid_ptk: hasil.xid_ptk, nm_ptk: hasil.nm_ptk, apiKey: hasil.apiKey, nidnMd5: hasil.nidnMd5, email_poltek: hasil.email_poltek });
							$$("formLogin").disable();
							webix.message(hasil.pesan);
							window.location = '<?php echo $domain; ?>/dosen';
						} else {
							webix.alert({
								title: "Kesalahan",
								text: hasil.pesan,
								type: "alert-error"
							})
						}
					},
					error: function (response, data, xhr) {
						proses_hide();
						webix.alert({
							title: "Kesalahan",
							text: "Gagal terkoneksi dengan server..!",
							type: "alert-error"
						})
					}
				});
			}
		});

		//adding ProgressBar functionality to layout
		webix.extend($$("formLogin"), webix.ProgressBar);

		function proses_tampil() {
			$$("formLogin").disable();
			$$("formLogin").showProgress({
				type: "icon",
				icon: "cog"
			});
		}

		function proses_hide() {
			$$("formLogin").enable();
			$$("formLogin").hideProgress();
		}

	</script>
</body>

</html>