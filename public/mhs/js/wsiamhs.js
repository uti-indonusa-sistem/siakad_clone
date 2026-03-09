webix.ready(function () {
	var wSiaMhs = webix.storage.session.get('wSiaMhs');

	var menuBarJudul = {
		view: "toolbar",
		id: "toolbar",
		paddingY: 0,
		paddingX: 0,
		css: "headerAtas",
		height: 35,
		type: "clean",
		elements: [
			{
				view: "button", id: "tombolMenu", type: "icon", icon: "bars", hidden: true,
				width: 35, align: "left", css: "menuKiri", click: function () {
					$$("sideKiri").toggle()
				}
			},
			{ view: "label", label: "<img src='../gambar/logo.png' height='28'>", css: "headerAtas", width: 200, borderless: true },
			{ css: "kampus", view: "template", id: "akunMahasiswa", url: "sopingi/profil/tampil/" + wSiaMhs.apiKey + "/" + Math.random(), template: "<b>#nm_pd# - #nipd#</b>  ( #nm_jenj_didik# - #nm_lemb#  )" },
			{
				css: "kampus", view: "button", id: "keluarMenu", type: "icon", icon: "lock", width: 80, label: "Keluar", align: "right", type: "form",
				click: function () {
					keluarProses();
				}
			}
		]
	};

	var menu_kiri = [
		{ id: "dashboard", icon: "dashboard", value: "Beranda" },
		{ id: "akun", icon: "key", value: "Akun" },
		{ id: "biodata", icon: "user", value: "Biodata" },
		{ id: "krs", icon: "table", value: "Kartu Rencana Studi" },
		// UPDATE ANDRE 24012024
		{ id: "krs-lama", icon: "file", value: "KRS Lama" },
		{ id: "khs", icon: "book", value: "Kartu Hasil Studi" },
		{ id: "kartu-ujian", icon: "file-text-o", value: "Kartu Ujian" },
		{ id: "bimbingan", icon: "table", value: "Bimbingan Akademik" },
		{ id: "ktm", icon: "book", value: "Unduh KTM" },
		{ id: "view-ktm", icon: "eye", value: "Lihat KTM" },
		{ id: "unduh-rps", icon: "download", value: "Unduh RPS" },
		{ id: "mbkm", icon: "briefcase", value: "MBKM" },
		//{id: "unduh-kp", icon: "download", value:"Unduh KP"},
	];

	var ui_wsiamhs = {
		id: "layout_utama",
		type: "clean", rows: [
			menuBarJudul,
			{
				cols: [
					{
						id: "sideKiri", view: "sidebar", data: menu_kiri, width: 180, hidden: true, on: {
							onAfterSelect: function (id) {
								bukaHalaman(id + ".html");
							}
						}
					},
					{
						id: "halaman", template: "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>", css: "halaman"
					}
				]
			},

			{ template: "SIAKAD | POLITEKNIK INDONUSA Surakarta", height: 30, css: "footerBawah", borderless: true, autowidth: true, align: "left" }
		]
	};

	//Buka Halaman
	bukaHalaman = function (id) {
		routes.navigate("/" + id, { trigger: true });
	}

	//Layout Utama
	var layout = new WebixView({
		config: ui_wsiamhs,
		el: ".app_wsiamhs"
	}).render();

	//Router
	var routes = new (Backbone.Router.extend({
		routes: {
			"": "index",
			"aksesditolak": "ditolak",
			":hal": "hal"
		},
		index: function () {
			var wSiaMhs = webix.storage.session.get('wSiaMhs');
			if (wSiaMhs === null || wSiaMhs == "") {
				kembaliKeLogin();
			}


			bukaHalaman("utama.html");

		},
		ditolak: function () {
			halaman = layout.root.getChildViews()[1].getChildViews()[1];
			aksesDitolak.el = halaman
			aksesDitolak.render();

			$$('sideKiri').hide();
			$$('tombolMenu').hide();

			webix.ui({
				view: "window", height: 130, width: 300, modal: true,
				head: {
					view: "toolbar", margin: -4, cols: [
						{ view: "icon", icon: "user-times" },
						{ view: "label", label: "Hak akses ditolak" },
						{ template: "" }
					]
				},
				position: "center",
				body: {
					template: "<h2 align='center'>Maaf, Anda tidak diperkenankan<br>Mengakses SIAKAD</h2>"
				}
			}).show();
		},
		hal: function (id) {
			//if (navigator.userAgent!="sopingi.com") {
			//routes.navigate("/aksesditolak", { trigger:true });
			//}  

			halaman = layout.root.getChildViews()[1].getChildViews()[1];

			if (id == "ktm.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				// Use new Siakad KTM API
				open("sopingi/ktm_pdf/cetak/" + wSiaMhs.apiKey);

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				keDashboard();

			} else if (id == "view-ktm.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				// Use new Siakad KTM validation page
				open("https://siakadv2.poltekindonusa.ac.id/validasi-ktm.html?nim=" + CryptoJS.MD5(wSiaMhs.nipd).toString(), "_blank", "location=no");
				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				keDashboard();

			} else if (id == "mbkm.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				open("#");

			} else if (id == "unduh-rps.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				//window.open("http://document.poltekindonusa.ac.id/view_ktm-"+wSiaMhs.nipd+".html", "Preview KTM", "location=no");
				//open("http://document.poltekindonusa.ac.id/view_ktm-"+wSiaMhs.nipd+".html", "_blank", "location=no");
				if (wSiaMhs.nipd.substring(0, 1) == "F") {
					open("https://drive.google.com/drive/folders/1YrDawbsaIr7G0dbaSP2rOxBLH7s9ji6i?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "E") {
					open("https://drive.google.com/drive/folders/1TibPN0xSZzGwIEFCODfpm95cwFY_8qWa?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "A") {
					open("https://drive.google.com/drive/folders/1mcretQm1l3Jwims7gFl1dsWanEEL438R?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "B") {
					open("https://drive.google.com/drive/folders/1_mRM6ay04SChtMEVBoCWr0zdyy1AW2k5?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "C") {
					open("https://drive.google.com/drive/folders/1q2y6e9_ORxzCSgHHPSaURHXJU--jlgb5?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "D") {
					open("https://drive.google.com/drive/folders/1BHE1RM-7FTZvMDhzLmKEV7hIWIMKq3GI?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "G") {
					open("https://drive.google.com/drive/folders/1agzwRBcYvjNtanPMT9nIuhaJADFYtFGK?sourceid=chrome&ie=UTF-8", "_blank", "location=no");
				}

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				keDashboard();

			} else if (id == "unduh-kp.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				//window.open("http://document.poltekindonusa.ac.id/view_ktm-"+wSiaMhs.nipd+".html", "Preview KTM", "location=no");
				//open("http://document.poltekindonusa.ac.id/view_ktm-"+wSiaMhs.nipd+".html", "_blank", "location=no");
				if (wSiaMhs.nipd.substring(0, 1) == "F") {
					open("https://drive.google.com/drive/folders/17Fp35l5fJc9uecS8_lxj6ut5GHsc0cuJ?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "E") {
					open("https://drive.google.com/drive/folders/1R8Ht7iVSI9iBohYCzkdubSspAktvz0nS?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "A") {
					open("https://drive.google.com/drive/folders/1JgqgOpTY9rIrOBya7bwvYyiiofoMlm_x?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "B") {
					open("https://drive.google.com/drive/folders/1EQP0ZBJzk2PCpi8JvRmqRxy5s6c7wqmW?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "C") {
					open("https://drive.google.com/drive/folders/14dd_urmOQxJMTH84ErAXmXdooti_zt_d?usp=sharing", "_blank", "location=no");
				} else if (wSiaMhs.nipd.substring(0, 1) == "D") {
					open("https://drive.google.com/drive/folders/1hLzWVOZv4cGczpdYp7kVYUWn1hk6d-XY?usp=sharing", "_blank", "location=no");
				}

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				keDashboard();

			} else if (id == "utama.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				formUtama.el = halaman
				formUtama.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

			} else if (id == "akun.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				halamanAkun.el = halaman
				halamanAkun.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				$$("simpanAkun").attachEvent("onItemClick", simpanAkun);
				$$("updateFoto").attachEvent("onItemClick", updateFoto);
				// Update Moodle Account Event
				$$("btnUpdateMoodle").attachEvent("onItemClick", updateMoodleAccount);

				// Load initial status
				loadMoodleStatus();

			} else if (id == "biodata.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				viewMahasiswaDetail.el = halaman
				viewMahasiswaDetail.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				//Mahasiswa Biodata
				$$("nm_ibu_kandung").attachEvent("onChange", function (baru, lama) {
					$$('vnm_ibu_kandung').setValue(baru);
				});

				//wilayah
				$$("formMahasiswaDetail").attachEvent("onAfterLoad", function () {
					var data = $$("formMahasiswaDetail").getValues();
					$$("id_wil").getList().load("sopingi/wilayah/tampil/" + wSiaMhs.apiKey + "/" + Math.random() + "?id=" + data.id_wil);
				});

				$$("simpanMahasiswa").attachEvent("onItemClick", simpanMahasiswa);

			} else if (id == "aksesKrsDitolak.html") {
				aksesDitolak.el = halaman
				aksesDitolak.render();
				$$('sideKiri').show();
				$$('tombolMenu').show();

			} else if (id == "krs.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				$$('sideKiri').show();
				$$('tombolMenu').show();
				webix.message("Cek status pembayaran.. Harap tunggu");

				// 1. Cek tagihan pembayaran dahulu (Gatekeeper Utama)
				webix.ajax().get("sopingi/pembayaran/cek_tagihan/" + wSiaMhs.apiKey + "/" + Math.random(), {}, {
					success: function (text, xml, xhr) {
						var hasilTagihan = JSON.parse(text);

						if (hasilTagihan.berhasil == 0) {
							webix.alert({
								title: "Gagal Memuat KRS",
								text: hasilTagihan.pesan || "Terjadi kesalahan saat mengecek status pembayaran. Silakan hubungi bagian administrasi.",
								type: "alert-error"
							});
						} else {
							var dataTagihan = hasilTagihan.data;
							var lunas = 1;
							dataTagihan.forEach(item => {
								if (item.kekurangan > 0) {
									lunas = 0;
								}
							});

							if (lunas == 0) {
								halamanTagihanKrs.el = halaman;
								halamanTagihanKrs.render();
								$$("dataTableTagihanKrs").clearAll();
								$$("dataTableTagihanKrs").parse(dataTagihan);
							} else {
								// 2. Jika Lunas, baru cek hak akses BAAK
								webix.message("Cek Hak akses.. Harap tunggu");
								webix.ajax().get("sopingi/hakakses/cek/" + wSiaMhs.apiKey + "/" + Math.random(), {}, {
									success: function (text, xml, xhr) {
										if (text == "1") {
											halamanKrs.el = halaman
											halamanKrs.render();

											webix.ui({
												view: "window",
												id: "winKelasKuliah",
												width: 750,
												position: "center",
												modal: true,
												head: {
													view: "toolbar", margin: -4, cols: [
														{ view: "label", label: "Tambah Kelas Mata Kuliah", id: "judulWinKelasKuliah" },
														{ view: "icon", icon: "close", click: "$$('winKelasKuliah').hide();" }
													]
												},
												body: webix.copy(formKRS)
											});

											$$("tambahKRS").attachEvent("onItemClick", function () {
												var data = [];
												var jSKS = 0;
												$$("dataTableKrs").data.each(function (dataKrs) {
													data.push(dataKrs.vid_kls);
													jSKS = jSKS + parseInt(dataKrs.vsks_mk);
												});

												if (jSKS < 24) {
													var dataSudahKrs = new Object();
													dataSudahKrs.aksi = "tampil";
													dataSudahKrs.data = JSON.stringify(data);
													proses_tampil();
													dataKirim = JSON.stringify(dataSudahKrs);
													webix.ajax().post("sopingi/kelas_kuliah/tampil/" + wSiaMhs.apiKey + "/" + Math.random(), dataKirim, {
														success: function (text, xml, xhr) {
															proses_hide();
															dataBelumKrs = JSON.parse(text);
															$$("dataTableKelasPerkuliahan").clearAll();
															$$("dataTableKelasPerkuliahan").define("data", dataBelumKrs);
															$$("dataTableKelasPerkuliahan").refresh();
															$$("winKelasKuliah").show();
															$$("judulWinKelasKuliah").setValue("Tambah Kelas Mata Kuliah");
															$$("formKRS").setValues({
																aksi: "tambah"
															});
														},
														error: function (text, data, xhr) {
															proses_hide();
															webix.alert({
																title: "Gagal Koneksi",
																text: "Tidak dapat terhubung dengan server/jaringan!",
																type: "alert-error"
															})
														}
													});
												} else {
													webix.alert({
														title: "Informasi",
														text: "Tidak dapat menambah SKS lagi..<br>(Maksimal 24 SKS)",
														type: "alert-error"
													})
												}
											}); //tambahKRS

											$$("simpanKRS").attachEvent("onItemClick", simpanKRS);

											$$("hapusKRS").attachEvent("onItemClick", function () {
												if ($$("dataTableKrs").getSelectedId() != null) {
													data = $$("dataTableKrs").getSelectedItem();
													data.aksi = "hapus";
													webix.confirm({
														title: "Konfirmasi",
														ok: "Ya",
														cancel: "Tidak",
														text: "Yakin akan menghapus data yang dipilih ?",
														callback: function (jwb) {
															if (jwb) {
																hapusKRS(data);
															}
														}
													});
												} else {
													peringatan("Informasi", "Belum ada data yang dipilih");
												}
											}); //hapusKRS

											$$("refreshKRS").attachEvent("onItemClick", function () {
												$$("dataTableKrs").clearAll();
												$$("dataTableKrs").load("sopingi/nilai/tampil/" + wSiaMhs.apiKey + "/" + Math.random());
											}); //refreshKRS

											$$("unduhKRS").attachEvent("onItemClick", unduhKRS);
										} else {
											bukaHalaman("aksesKrsDitolak.html");
										}
									},
									error: function (text, data, xhr) {
										webix.alert({
											title: "Gagal Koneksi",
											text: "Tidak dapat terhubung dengan internet!",
											type: "alert-error"
										})
									}
								}); //cek hak akses
							}
						}
					},
					error: function (text, data, xhr) {
						webix.alert({
							title: "Gagal Koneksi",
							text: "Tidak dapat terhubung dengan internet!",
							type: "alert-error"
						});
					}
				}); //cek tagihan pembayaran
				// UPDATE ANDRE 24012024
			} else if (id == "krs-lama.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				halamanKrsLama.el = halaman
				halamanKrsLama.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();

				$$('krsPdf').attachEvent("onItemClick", krsPdf);

				$$("krs_id_smt").attachEvent("onChange", function (baru, lama) {
					$$("dataTableKrsLama").clearAll();
					$$("dataTableKrsLama").load("sopingi/nilai/tampilW/" + wSiaMhs.apiKey + "/" + baru);
					console.log(baru);
				});

			} else if (id == "khs.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				halamanKhs.el = halaman
				halamanKhs.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();

				$$('khsPDF').attachEvent("onItemClick", khsPDF);
				$$('transkipPDF').attachEvent("onItemClick", transkipPDF);

				$$("khs_id_smt").attachEvent("onChange", function (baru, lama) {
					$$("dataTableKhs").clearAll();
					$$("dataTableKhs").load("sopingi/nilai/tampilKhs/" + wSiaMhs.apiKey + "/" + baru);
				});

			} else if (id == "kartu-ujian.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				halamanKartuUjian.el = halaman;
				halamanKartuUjian.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();

				// Event handler untuk cetak kartu UTS
				$$('cetakUTS').attachEvent("onItemClick", function () {
					var id_smt = $$('ujian_id_smt').getValue();
					cetakKartuUTS(id_smt);
				});

				// Event handler untuk cetak kartu UAS
				$$('cetakUAS').attachEvent("onItemClick", function () {
					var id_smt = $$('ujian_id_smt').getValue();
					cetakKartuUAS(id_smt);
				});

			} else if (id == "bimbingan.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				halamanBimbingan.el = halaman
				halamanBimbingan.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();

				$$("jurnalDataTable").clearAll();
				$$("jurnalDataTable").load("sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + Math.random());

				//Jurnal Bimbingan
				webix.ui({
					view: "window",
					id: "winJurnal",
					width: 750,
					position: "center",
					modal: true,
					head: {
						view: "toolbar", margin: -4, cols: [
							{
								view: "label", label: "Tambah Jurnal Bimbingan Akademik", id: "judulWinJurnal"
							},
							{
								view: "icon", icon: "close",
								click: "$$('winJurnal').hide();"
							}
						]
					},
					body: webix.copy(formJurnal)
				});

				$$("refreshJurnal").attachEvent("onItemClick", function (id, e) {
					$$("jurnalDataTable").clearAll();
					$$("jurnalDataTable").load("sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + Math.random());
				});

				$$("tambahJurnal").attachEvent("onItemClick", function () {

					$$("winJurnal").show();
					$$("formJurnal").clear();
					$$("formJurnal").setValues({ "aksi": "tambah_jurnal" });
					$$("judulWinJurnal").setValue("Tambah Jurnal Bimbingan Akademik");

				});

				/*

				$$("ubahJurnal").attachEvent("onItemClick", function(){			
					if ($$("jurnalDataTable").getSelectedId()!=null) {
						var data= $$("jurnalDataTable").getSelectedItem();
						if (data.oleh=="Dosen PA") {
							webix.alert("Anda tidak diijinkan mengubah jurnal yang diisi oleh dosen");
							return;
						}
						data.aksi = "ubah_jurnal";
						$$("winJurnal").show();
						$$("formJurnal").setValues(data);
						$$("judulWinJurnal").setValue("Ubah Jurnal Bimbingan Akademik");
					} else {
						
						webix.alert({
							title: "Informasi",
							text: "Belum ada data yang dipilih",
							type:"alert-warning"
						});
					}
				});
				*/


				$$("simpanJurnal").attachEvent("onItemClick", function () {
					if ($$('formJurnal').validate()) {

						var dataJurnal = $$("formJurnal").getValues();
						var dataKirim = JSON.stringify($$("formJurnal").getValues());
						$$('formJurnal').disable();
						webix.ajax().post("sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + Math.random, dataKirim,
							function (response, data, xhr) {
								$$('formJurnal').enable();
								var hasil = JSON.parse(response);
								if (hasil.berhasil) {
									webix.message(hasil.pesan);
									$$("jurnalDataTable").clearAll();
									$$("jurnalDataTable").load("sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + Math.random());
									$$("formJurnal").clear();
									$$('winJurnal').hide();
								} else {
									webix.alert({
										title: "Gagal Simpan",
										text: hasil.pesan,
										type: "alert-error"
									});
								}

							}
						);

					} else {
						webix.alert({
							title: "Kesalahan",
							text: "Form tidak valid",
							type: "alert-error"
						});
					}

				});

				$$("hapusJurnal").attachEvent("onItemClick", function () {
					if ($$("jurnalDataTable").getSelectedId() != null) {
						var data = $$("jurnalDataTable").getSelectedItem();
						if (data.oleh == "Dosen PA") {
							webix.alert("Anda tidak diijinkan menghapus jurnal yang diisi oleh dosen");
							return;
						}
						webix.confirm({
							title: "Konfirmasi",
							ok: "Ya",
							cancel: "Tidak",
							text: "Yakin akan menghapus data yang dipilih ?",
							callback: function (jwb) {
								if (jwb) {
									data.aksi = "hapus_jurnal";
									var dataKirim = JSON.stringify(data);
									webix.ajax().post("sopingi/pa/hapus_jurnal/" + wSiaMhs.apiKey + "/" + Math.random(), dataKirim,
										function (response, data, xhr) {

											var hasil = JSON.parse(response);
											if (hasil.berhasil) {
												webix.message(hasil.pesan);
												$$("jurnalDataTable").clearAll();
												$$("jurnalDataTable").load("sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + Math.random());
											} else {
												webix.alert({
													title: "Gagal Hapus",
													text: hasil.pesan,
													type: "alert-error"
												});
											}

										}
									);
								}
							}
						});

					} else {

						webix.alert({
							title: "Informasi",
							text: "Belum ada data yang dipilih",
							type: "alert-warning"
						});
					}

				});

				//chat
				webix.ui({
					view: "window",
					id: "winPesan",
					head: {
						view: "toolbar", margin: -4, cols: [
							{
								view: "label", label: "Pesan Bimbingan Akademik", id: "judulWinPesan"
							},
							{
								view: "icon", icon: "trash", hidden: true, id: "hapusPesan",
								click: function () {
									var data = $$("pesanList").getSelectedItem();
									webix.confirm({
										title: "Konfirmasi",
										ok: "Ya",
										cancel: "Tidak",
										text: "Yakin akan menghapus pesan yang dipilih ?",
										callback: function (jwb) {
											if (jwb) {
												$$('pesanList').unselectAll();
												$$("hapusPesan").hide();
												data.aksi = "hapus_pesan";
												var dataKirim = JSON.stringify(data);
												webix.ajax().post("sopingi/pa/hapus_pesan/" + wSiaMhs.apiKey + "/" + Math.random(), dataKirim,
													function (response, data, xhr) {
														var hasil = JSON.parse(response);
														if (hasil.berhasil) {
															webix.message(hasil.pesan);
															$$("pesanList").remove(hasil.id);
														} else {
															webix.alert({
																title: "Gagal Hapus",
																text: hasil.pesan,
																type: "alert-error"
															});
														}

													}
												);
											}
										}
									});
								}
							},
							{
								width: 20, template: ""
							},
							{
								view: "icon", icon: "close",
								click: "$$('winPesan').hide();"
							}
						]
					},
					position: "center",
					width: 500,
					height: 500,
					modal: true,
					body: {

						borderless: true,
						rows: [

							{
								view: "list",
								id: "pesanList",
								css: "char-list",
								select: true,
								type: {
									height: "auto"
								},

								template: function (item) {
									var css = item.author == "mahasiswa" ? "1" : "2";
									return "<div class='pesan msg" + css + "'><span class='waktu'>" + item.author + " | " + item.waktu + "</span>" + item.text + "</div>";
								}
							},
							{
								height: 60,
								cols: [
									{ view: "textarea", id: "pesan", required: true },
									{ view: "text", id: "id_jurnal", required: true, hidden: true },
									{
										width: 60, rows: [
											{
												view: "button", label: "Kirim", height: 60, click: function () {
													$$('pesanList').unselectAll();
													$$("hapusPesan").hide();
													var text = $$("pesan").getValue();
													var id = $$("id_jurnal").getValue();
													text = text.replace(/\n/g, "<br/>");
													if (text && id) {
														var data = { "aksi": "kirim_pesan", "id_jurnal": id, "pesan": text };
														var dataKirim = JSON.stringify(data);
														webix.ajax().post("sopingi/pa/kirim_pesan/" + wSiaMhs.apiKey + "/" + Math.random(), dataKirim,
															function (response, data, xhr) {
																$$("pesan").setValue("");
																var hasil = JSON.parse(response);
																if (hasil.berhasil) {
																	webix.message(hasil.pesan);
																	$$("pesanList").add({ text: hasil.text, waktu: hasil.waktu, author: hasil.author });
																	$$('pesanList').showItem($$('pesanList').getLastId())
																} else {
																	webix.alert({
																		title: "Gagal Kirim",
																		text: hasil.pesan,
																		type: "alert-error"
																	});
																}

															}
														);

													}
												}
											},
											{}
										]
									}

								]
							}
						]
					}
				});

				$$("jurnalDataTable").on_click.btnChat = function (e, id) {
					$$("jurnalDataTable").select(id);
					var data = $$("jurnalDataTable").getItem(id);
					$$('id_jurnal').setValue(data.id);
					$$("pesanList").clearAll();
					$$('pesanList').load("sopingi/pa/pesan/" + wSiaMhs.apiKey + "/" + data.id);
					$$('winPesan').show();

				};

				$$("pesanList").attachEvent("onAfterLoad", function () {
					$$('pesanList').showItem($$('pesanList').getLastId())
				});

				$$("pesanList").attachEvent("onItemClick", function (id, e, node) {
					var data = $$("pesanList").getItem(id);
					if (data.author == "mahasiswa") {
						$$("hapusPesan").show();
					}
				});

			} else {
				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs === null || wSiaMhs == "") {
					kembaliKeLogin();
				}

				formUtama.el = halaman
				formUtama.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();
			}

		}
	}));

	Backbone.history.start();

	//adding ProgressBar functionality to layout
	webix.extend($$("layout_utama"), webix.ProgressBar);

	function proses_tampil() {
		$$("layout_utama").disable();
		$$("layout_utama").showProgress({
			type: "icon",
			icon: "cog"
		});
	}

	function proses_hide() {
		$$("layout_utama").enable();
		$$("layout_utama").hideProgress();
	}

	function keDashboard() {
		routes.navigate("/", { trigger: true });
	}

	// Fungsi tampilkan notifikasi email poltek di Dashboard
	function tampilkanNotifEmailPoltek() {
		// CSS Kustom untuk Modal (Premium Design)
		var style = document.createElement('style');
		style.innerHTML = `
			.notif-window .webix_win_content { border-radius: 20px !important; overflow: hidden !important; }
			.notif-window .webix_win_body { padding: 0 !important; }
			.notif-premium-card { font-family: 'Inter', 'Segoe UI', sans-serif; overflow: hidden; background: #fff; }
			.notif-premium-header { 
				background: linear-gradient(135deg, #1a6b3a 0%, #2e9e5b 100%); 
				padding: 30px 25px 20px; text-align: center; color: #fff; 
			}
			.notif-premium-icon { font-size: 50px; margin-bottom: 15px; display: block; }
			.notif-premium-header h2 { margin: 0; font-size: 20px; font-weight: 700; }
			.notif-premium-header p { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }
			.notif-premium-body { padding: 25px; }
			.notif-premium-badge { 
				display: inline-block; background: #e8f5ee; color: #1a6b3a; 
				border-radius: 20px; padding: 4px 14px; font-size: 11px; font-weight: 700; margin-bottom: 15px; 
			}
			.notif-premium-text { color: #444; font-size: 15px; line-height: 1.6; margin-bottom: 20px; }
			.notif-premium-benefit { 
				background: #f0faf4; border-left: 4px solid #2e9e5b; border-radius: 8px; 
				padding: 15px; margin-bottom: 25px; font-size: 13px; color: #2d6a4f; 
			}
			.notif-premium-benefit strong { display: block; margin-bottom: 8px; }
			.notif-premium-benefit ul { margin: 0; padding-left: 20px; }
			.notif-premium-actions { display: flex; flex-direction: column; gap: 10px; }
			.btn-notif-main { 
				background: linear-gradient(135deg, #1a6b3a 0%, #2e9e5b 100%); color: #fff !important; 
				border: none; border-radius: 12px; padding: 14px; font-size: 15px; font-weight: 700; 
				cursor: pointer; text-align: center; text-decoration: none; box-shadow: 0 4px 15px rgba(46,158,91,0.3);
			}
			.btn-notif-sub { 
				background: #fff; color: #888 !important; border: 1.5px solid #eee; 
				border-radius: 12px; padding: 12px; font-size: 13px; cursor: pointer; 
				text-align: center; text-decoration: none; 
			}
		`;
		document.head.appendChild(style);

		webix.ui({
			view: "window",
			id: "winNotifEmail",
			width: 420,
			position: "center",
			modal: true,
			css: "notif-window",
			head: false,
			body: {
				rows: [
					{
						view: "template",
						height: 520,
						borderless: true,
						template: `
							<div class="notif-premium-card">
								<div class="notif-premium-header">
									<span class="notif-premium-icon">📧</span>
									<h2>Hei! Ada yang Perlu Kamu Tahu 👋</h2>
									<p>Satu langkah kecil, manfaatnya besar lho!</p>
								</div>
								<div class="notif-premium-body">
									<div class="notif-premium-badge">✨ TIPS AKUN SIAKAD</div>
									<div class="notif-premium-text">
										Email <strong>@poltekindonusa.ac.id</strong> kamu belum tersambung ke akun SIAKAD ini. 
										Yuk sambungkan sekarang — cuma butuh 1 menit!
									</div>
									<div class="notif-premium-benefit">
										<strong>🚀 Keuntungan menghubungkan email:</strong>
										<ul>
											<li>Login SIAKAD tanpa perlu ingat password</li>
											<li>Cukup klik "Login dengan Google" — selesai!</li>
											<li>Lebih aman & praktis setiap hari</li>
										</ul>
									</div>
									<div class="notif-premium-actions">
										<a href="javascript:void(0)" id="btnGOSetting" class="btn-notif-main">🚀 Oke Lanjut Setting</a>
										<a href="javascript:void(0)" id="btnGONanti" class="btn-notif-sub">Oke saya setting nanti</a>
									</div>
								</div>
							</div>
						`,
						on: {
							onAfterRender: function () {
								var btnGo = document.getElementById("btnGOSetting");
								if (btnGo) {
									btnGo.onclick = function () {
										$$("winNotifEmail").close();
										bukaHalaman("akun.html");
									};
								}
								var btnLater = document.getElementById("btnGONanti");
								if (btnLater) {
									btnLater.onclick = function () {
										$$("winNotifEmail").close();
									};
								}
							}
						}
					}
				]
			}
		}).show();
	}

	// Cek notifikasi email poltek
	if (wSiaMhs && (!wSiaMhs.email_poltek || wSiaMhs.email_poltek == '')) {
		// Tampilkan sedikit delay agar dashboard termuat dulu
		setTimeout(tampilkanNotifEmailPoltek, 1500);
	}

});