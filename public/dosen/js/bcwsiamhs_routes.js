/* DILARANG AKSES */
var wSiaMhs = webix.storage.session.get('wSiaMhs');

var aksesDitolak = new WebixView({
	config: {
		type: "clean",
		rows: [
			{ template: "", height: 50 },
			{ type: "clean", template: "<center><img src='../gambar/logo_center.png' width='200'></center>" },
			{ template: "", height: 20 },
			{ template: "<h2 align='center'>Maaf, akses KRS ditolak. Silahkan melakukan tagihan pembayaran atau hubungi admin</h2>", height: 70 },
			{ template: "" }
		]
	}
});

var winDitolak = webix.ui({
	view: "window", height: 250, width: 300, head: "Hak Akses Ditolak",
	position: "center",
	body: {
		template: "Maaf, masa pengisian KRS sudah ditutup"
	}
});

//Utama
var formUtama = new WebixView({
	config: {
		type: "clean",
		rows: [
			{ template: "", height: 5 },
			{ type: "clean", height: 160, template: "<center><img src='../gambar/logo_center.png' height='150'></center>" },
			{
				cols: [
					{ template: "" },
					{
						view: "form", id: "formUtama", css: "formLogin", scroll: false, width: 500, borderless: true,
						elements: [
							{
								view: "fieldset", label: "Halaman Dosen", body: {
									template: "<h2 class='info' align='center'>SELAMAT DATANG DI SIAKAD<br>POLITEKNIK INDONUSA SURAKARTA</h2>", height: 80, borderless: true
								}
							}
						]
					},
					{ template: "" }
				]
			},
			{ template: "" }
		]
	}
});


webix.type(webix.ui.list, {
	name: "myUploader",
	scroll: false,
	template: function (f, type) {
		var html = "<div class='overall'><div class='name'>" + f.name + "</div>";
		html += "<div class='remove_file'><span style='color:#AAA' class='cancel_icon'></span></div>";
		html += "<div class='status'>";
		html += "<div class='progress " + f.status + "' style='width:" + (f.status == 'transfer' || f.status == "server" ? f.percent + "%" : "0px") + "'></div>";
		html += "<div class='message " + f.status + "'>" + type.status(f) + "</div>";
		html += "</div>";
		html += "<div class='size'>" + f.sizetext + "</div></div>";
		return html;
	},
	status: function (f) {
		var messages = {
			server: "Berhasil",
			error: "Gagal",
			client: "Siap Upload",
			transfer: f.percent + "%"
		};
		return messages[f.status]

	},
	on_click: {
		"remove_file": function (ev, id) {
			$$(this.config.uploader).files.remove(id);
		}
	},
	autoheight: true,
	borderless: true
});

/* HALAMAN AKUN */
var halamanAkun = new WebixView({
	config: {
		type: "clean",
		rows: [
			{
				template: "Akun Dosen", type: "header"
			},
			{
				type: "clean", borderless: true, cols: [
					{ template: " " },
					{
						view: "form",
						id: "formAkun",
						scroll: false,
						width: 400,
						borderless: true,
						elements: [
							{
								view: "fieldset", label: "Ubah Akun Dosen", body: {
									rows: [
										{ view: "text", id: "pass", name: "pass", type: "password", label: "Password Lama", labelWidth: 180, required: true, invalidMessage: "Password lama belum diisi" },
										{ view: "text", id: "passBaru1", name: "passBaru1", type: "password", label: "Password Baru", labelWidth: 180, required: true, invalidMessage: "Password baru belum diisi" },
										{ view: "text", id: "passBaru", name: "passBaru", type: "password", label: "Ulangi Password Baru", labelWidth: 180, required: true, invalidMessage: "Ulangi Password baru belum diisi" },
										{ view: "text", id: "aksi", name: "aksi", value: "ubahAkun", hidden: true },
										{ template: " ", borderless: true, height: 20 },
										{
											margin: 5, cols: [
												{ template: " ", borderless: true },
												{ view: "button", id: "simpanAkun", label: "Ubah Akun", type: "form" },
												{ template: " ", borderless: true }
											]
										}
									]
								}
							}
						]
					},
					{ template: " " }
				]
			},

			{
				type: "clean", borderless: true, cols: [
					{ template: " " },
					{
						view: "form",
						id: "formFoto",
						scroll: false,
						width: 400,
						borderless: true,
						elements: [
							{
								view: "fieldset", label: "Ubah Foto", body: {
									rows: [
										{
											view: "uploader", id: "foto", name: "foto", value: "Pilih Foto (Hanya: JPG,PNG,GIF)",
											autosend: false, multiple: false, link: "mylist",
											upload: "uploader/dosen/foto/" + wSiaMhs.apiKey + "/" + Math.random(),
											accept: "image/png, image/gif, image/jpg",
											height: 30
										},
										{
											view: "list", id: "mylist", type: "myUploader", borderless: true, height: 30, scroll: false,
										},
										{
											margin: 5, cols: [
												{ template: " ", borderless: true },
												{ view: "button", id: "updateFoto", label: "Upload", type: "form", icon: "upload", height: 40 },
												{ template: " ", borderless: true }
											]
										},
										{ id: 'fotoMhs', template: "<center><img src='foto/" + wSiaMhs.nidnMd5 + ".jpg' height='140'></center>", height: 150, borderless: true },
									]
								}
							}
						]
					},
					{ template: " " }
				]
			},



			{
				template: " ", borderless: true
			}
		]
	} //config
});

/* HALAMAN PENGAJARAN */
var panelKiriPengajaran = {
	id: "panelKiriPengajaran", borderless: false, width: 150, rows: [
		{
			template: "Tahun Akademik",
			type: "header"
		},
		{
			id: "menuPengajaran",
			view: "list",
			select: true,
			scroll: true,
			url: "sopingi/semester/pilih/" + wSiaMhs.apiKey + "/" + Math.random()
		}
	]
};

var dosen_pengajaranRiwayat = {
	id: "dosen_pengajaranRiwayat",
	type: "space",
	rows: [
		{
			view: "toolbar", paddingY: 2,
			cols: [
				{ view: "button", id: "pengajaranRiwayatRefresh", label: "Refresh", type: "iconButton", icon: "refresh", width: 100 },
				{ template: "", borderless: true },
			]
		},
		{
			view: "datatable",
			select: true,
			id: "pengajaranRiwayatDataTable",
			datafetch: 12,
			loadahead: 12,
			navigation: true,
			fixedRowHeight: false,
			rowLineHeight: 25,
			columns: [
				{ id: "index", header: "No", width: 40 },
				{ id: "nm_lemb", header: "Program Studi", width: 220 },
				{ id: "nm_kls", header: "Kelas", width: 60 },
				{ id: "kode_mk", header: "Kode MK", width: 80 },
				{ id: "nm_mk", header: "Nama MK", fillspace: true },
				{ id: "mhs", header: "Mahasiswa", width: 90, template: "<button class='btnNilai btnTransparant'><i class='webix_icon fa-users'></i> Mhs</button>" },
			],
			pager: "pengajaranRiwayatPager",
			hover: "tableHover",
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
				},
				onAfterLoad: function () {

					/*
					webix.delay(function(){
						this.adjustRowHeight(); 
						this.render();
					}, this);
					*/

					this.hideOverlay();
					var jData = this.data.order.length;
					$("#jPengajaran").html(jData);
				},
				onAfterFilter: function () {
					var jData = this.data.order.length;
					$("#jPengajaran").html(jData);
				},
				onLoadError: function (text, data, xhr) {
					var hasil = data.json();
					peringatanStatus(hasil.status_code, hasil.message);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						try {
							obj.index = i + 1;
						} catch (e) {

						}
					});

					var jData = this.data.order.length;
					$("#jPengajaran").html(jData);
				}
			}

		},

		{
			view: "pager",
			id: "pengajaranRiwayatPager",
			template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jPengajaran'>  </span></b> Data",
			size: 12,
			group: 5,
			animate: {
				direction: "left", type: "slide"
			}
		}
	]
}

var pengajaranDetail = new WebixView({
	config: {
		id: "pengajaranDetail",
		type: "clean",
		rows: [
			{
				template: "Pengajaran", type: "header"
			},
			{
				cols: [
					panelKiriPengajaran, dosen_pengajaranRiwayat
				]
			}
		]
	}
});

var abobot;

function cekPersen() {
	var persen_absen = parseFloat($$("persen_absen").getValue()) || 0;
	var persen_tugas = parseFloat($$("persen_tugas").getValue()) || 0;
	var persen_uts = parseFloat($$("persen_uts").getValue()) || 0;
	var persen_uas = parseFloat($$("persen_uas").getValue()) || 0;
	var persen_total = persen_absen + persen_tugas + persen_uts + persen_uas;
	$$("persen_total").setValue(persen_total);
}

var viewMahasiswaKelas = {
	id: "viewMahasiswaKelas", type: "space", cols: [
		{
			width: 250, rows: [
				{ template: "Kelas Perkuliahan", type: "header" },
				{
					id: "panelKelasKuliah",
					view: "list",
					type: {
						templateStart: "<div class='panelKelasPerkuliahan'>",
						template: "#judul#<br><b>#konten#</b>",
						templateEnd: "</div>"
					}
				},
				{ template: "<span style='background:#ffff00;font-size:13pt;font-weight:bold'>Pengumpulan nilai maksimal 7 hari setelah UAS</span>" },
				{
					view: "toolbar", cols: [
						{ label: "% Nilai", view: "label" },
						{ template: "", borderless: true },
					]
				},
				{
					id: "formPersenNilai",
					view: "form",
					url: "sopingi/persen_nilai/tampil/" + wSiaMhs.apiKey + "/" + Math.random(),
					elements: [
						{ view: "text", label: "Absen (%)", on: { "onChange": () => cekPersen() }, name: "persen_absen", id: "persen_absen", validate: "isNumber", invalidMessage: "Harus numeric", required: true, readonly: true },
						{ view: "text", label: "Tugas (%)", on: { "onChange": () => cekPersen() }, name: "persen_tugas", id: "persen_tugas", validate: "isNumber", invalidMessage: "Harus numeric", required: true, readonly: true },
						{ view: "text", label: "UTS (%)", on: { "onChange": () => cekPersen() }, name: "persen_uts", id: "persen_uts", validate: "isNumber", invalidMessage: "Harus numeric", required: true, readonly: true },
						{ view: "text", label: "UAS (%)", on: { "onChange": () => cekPersen() }, name: "persen_uas", id: "persen_uas", validate: "isNumber", invalidMessage: "Harus numeric", required: true, readonly: true },
						{ view: "text", label: "Total (%)", name: "persen_total", id: "persen_total", validate: "isNumber", readonly: true, invalidMessage: "Harus bernilai 100", required: true, readonly: true },
						{
							cols: [
								{},
								//{view:"button", id:"simpanPersenNilai", value:"Simpan"},
								{}
							]
						}
					],
					elementsConfig: {
						labelPosition: "left",
						labelWidth: 80
					},
					rules: {
						persen_total: function (value) {
							return (value == 100);
						}
					}
				}
			]
		},
		{
			rows: [
				{
					view: "toolbar", paddingY: 2, cols: [
						{ view: "button", id: "refreshMhsKelas", label: "Refresh", type: "iconButton", icon: "refresh", width: 100 },
						{ template: "", borderless: true },
						{ view: "button", label: "Simpan Draft Nilai", id: "simpanKhsKelas", type: "iconButton", icon: "save", width: 140 },
						{ view: "button", label: "Unduh Xls", id: "unduhKhsXls", type: "iconButton", icon: "file-excel-o", width: 120 },
						{ view: "button", label: "Kirim Nilai", id: "kirimKhsKelas", type: "iconButton", icon: "send", width: 120 },
					]
				},
				{
					view: "datatable",
					select: true,
					editable: true,
					id: "dataTableMahasiswaKelas",
					fixedRowHeight: false,
					rowLineHeight: 25,
					columns: [
						{ id: "index", header: "No", width: 40 },
						//{ id:"xid_reg_pd",header:"No Daftar",sort:"string"},
						{ id: "nipd", header: "NIM", sort: "string" },
						{
							id: "nm_pd", header: ["Nama Mahasiswa", { content: "textFilter" }], fillspace: true, sort: "string", template: data => {
								return data.nm_pd + "<br>" + data.telepon_seluler;
							}
						},
						{ id: "jk", header: ["L/P", { content: "selectFilter" }], width: 40, sort: "string" },
						//{ id:"prodi", header:["Program Studi",{content:"selectFilter"}],fillspace:true},
						{ id: "angkatan", header: ["Angkatan", { content: "selectFilter" }], width: 70 },
						{ id: "nilai_absen", header: [{ text: "Nilai", colspan: 7, css: { "text-align": "center" } }, { text: "Kehadiran", css: { "text-align": "center", "background-color": "#ffff00" } }], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 70, editor: "text", numberFormat: "1,111.00" },
						{ id: "nilai_tugas", header: ["", { text: "Tugas", css: { "text-align": "center", "background-color": "#ffff00" } }], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 60, editor: "text", numberFormat: "1,111.00" },
						{ id: "nilai_uts", header: ["", { text: "UTS", css: { "text-align": "center", "background-color": "#ffff00" } }], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 60, editor: "text", numberFormat: "1,111.00" },
						{ id: "nilai_uas", header: ["", { text: "UAS", css: { "text-align": "center", "background-color": "#ffff00" } }], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 60, editor: "text", numberFormat: "1,111.00" },
						{ id: "nilai_angka", header: ["", "Angka"], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 60, numberFormat: "1,111.00" },
						{ id: "nilai_huruf", header: ["", "Huruf"], css: { "text-align": "center" }, width: 60 },
						{ id: "nilai_indeks", header: ["", "Indeks"], css: { "text-align": "right" }, format: webix.i18n.numberFormat, width: 60 },
						{ id: "nilai_status", header: ["Status Nilai", { content: "selectFilter" }], width: 100 },
					],
					//pager:"pagerMahasiswaKelas",
					hover: "tableHover",
					rules: {
						nilai_absen: (value) => { return (value != "" && value != null) },
						nilai_tugas: (value) => { return (value != "" && value != null) },
						nilai_uts: (value) => { return (value != "" && value != null) },
						nilai_uas: (value) => { return (value != "" && value != null) }
					},

					on: {
						onBeforeLoad: function () {
							this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
						},
						onAfterLoad: function () {

							/*
							webix.delay(function(){
								this.adjustRowHeight(); 
								this.render();
							}, this);
							*/

							this.hideOverlay();
							var jMahasiswaKelas = this.data.order.length;
							$("#jMahasiswaKelas").html(jMahasiswaKelas);
							this.validate();
						},
						onAfterFilter: function () {
							var jMahasiswaKelas = this.data.order.length;
							$("#jMahasiswaKelas").html(jMahasiswaKelas);
						},
						"data->onStoreUpdated": function () {
							this.data.each(function (obj, i) {
								obj.index = i + 1;
							});

							var jMahasiswaKelas = this.data.order.length;
							$("#jMahasiswaKelas").html(jMahasiswaKelas);
						},

						onBeforeFilter: function (id, value, config) {
							this.editStop();
						},
						onBeforeEditStart: function (id) {

							var row = this.getItem(id);
							if (!$$("formPersenNilai").validate()) {
								webix.alert({
									title: "Informasi",
									text: "Silahkan update persen Nilai terlebih dahulu",
									type: "alert-warning"
								});
								this.editCancel();
								return false;
							} else if (row.nilai_tampil == 1 || row.nilai_tampil == 3) {
								webix.alert({
									title: "Informasi",
									text: "Nilai mahasiswa: " + row.nm_pd + " tidak diperkenankan diubah, karena status Nilai diisi oleh BAAK atau Sudah TERKIRIM",
									type: "alert-warning"
								});
								this.editCancel();
								return false;
							}

						},
						onBeforeEditStop: function (state, editor) {
							if (!$$("formPersenNilai").validate()) {
								this.editCancel();
								return false;
							}
						},
						onAfterEditStop: function (data, editor, ignoreUpdate) {

							var formNilai = $$("formPersenNilai").getValues();

							var record = this.getItem(editor.row);

							if (parseFloat(record.nilai_absen) > 100 || parseFloat(record.nilai_absen) < 0) {
								record.nilai_absen = "";
							}
							if (parseFloat(record.nilai_tugas) > 100 || parseFloat(record.nilai_tugas) < 0) {
								record.nilai_tugas = "";
							}
							if (parseFloat(record.nilai_uts) > 100 || parseFloat(record.nilai_uts) < 0) {
								record.nilai_uts = "";
							}
							if (parseFloat(record.nilai_uas) > 100 || parseFloat(record.nilai_uas) < 0) {
								record.nilai_uas = "";
							}

							if (record.nilai_absen.toString() == "" && record.nilai_tugas.toString() == "" && record.nilai_uts.toString() == "" && record.nilai_uas.toString() == "") {
								record['nilai_angka'] = 0;
								record['nilai_huruf'] = "";
								record['nilai_indeks'] = 0;
								record['sksXindeks'] = "";
							} else {

								var nilai_absen = (parseFloat(record.nilai_absen) || 0) * (parseFloat(formNilai.persen_absen) / 100);
								var nilai_tugas = (parseFloat(record.nilai_tugas) || 0) * (parseFloat(formNilai.persen_tugas) / 100);
								var nilai_uts = (parseFloat(record.nilai_uts) || 0) * (parseFloat(formNilai.persen_uts) / 100);
								var nilai_uas = (parseFloat(record.nilai_uas) || 0) * (parseFloat(formNilai.persen_uas) / 100);
								var nilai_angka = nilai_absen + nilai_tugas + nilai_uts + nilai_uas;
								//console.log(nilai_angka);

								abobot.forEach(function (item, index) {
									if (nilai_angka >= parseFloat(item.bobot_nilai_min)) {
										record['nilai_angka'] = nilai_angka;
										record['nilai_huruf'] = item.nilai_huruf;
										record['nilai_indeks'] = item.nilai_indeks;
										record['sksXindeks'] = record['vsks_mk'] * record['nilai_indeks'];
									}
								});

							}

							//console.log(record);

							this.updateItem(editor.row, record);

						},
					}, //on

					/* V1
					on:{
						onBeforeLoad:function(){
							this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
						},
						onAfterLoad:function(){
							
							this.hideOverlay();
							var jMahasiswaKelas=this.data.order.length;
							$("#jMahasiswaKelas").html(jMahasiswaKelas);
							this.validate();
						},
						onAfterFilter:function(){
							var jMahasiswaKelas=this.data.order.length;
							$("#jMahasiswaKelas").html(jMahasiswaKelas);
						},
						"data->onStoreUpdated":function(){							 
								this.data.each(function(obj, i){
									obj.index = i+1;
								});
							    
								var jMahasiswaKelas=this.data.order.length;
								$("#jMahasiswaKelas").html(jMahasiswaKelas);
						},
						onBeforeFilter:function(id, value, config) {
							this.editStop();
						},
						onBeforeEditStart: function(id) {
	
							var row= this.getItem(id);
							console.log(row);
							if (!$$("formPersenNilai").validate()) {
								webix.alert({
										title: "Informasi",
										text: "Silahkan update persen Nilai terlebih dahulu",
										type:"alert-warning"
								});
								this.editCancel();
								return false;
							} else if (row.nilai_tampil==1 || row.nilai_tampil==3) {
								webix.alert({
										title: "Informasi",
										text: "Nilai mahasiswa: "+row.nm_pd+" tidak diperkenankan diubah, karena status Nilai diisi oleh BAAK atau Sudah TERKIRIM",
										type:"alert-warning"
								});
								this.editCancel();
								return false;
							}
						},
						onBeforeEditStop:function(state,editor) {
							if (!$$("formPersenNilai").validate()) {
								this.editCancel();
								return false;
							}
						},
	
						onAfterEditStop: function (data, editor, ignoreUpdate) {
							
							var formNilai = $$("formPersenNilai").getValues();
	
							var record = this.getItem(editor.row);
	
							if (parseFloat(record.nilai_absen)>100 || parseFloat(record.nilai_absen)<0) {
								record.nilai_absen="";
							}
							if (parseFloat(record.nilai_tugas)>100 || parseFloat(record.nilai_tugas)<0) {
								record.nilai_tugas="";
							}
							if (parseFloat(record.nilai_uts)>100 || parseFloat(record.nilai_uts)<0) {
								record.nilai_uts="";
							}
							if (parseFloat(record.nilai_uas)>100 || parseFloat(record.nilai_uas)<0) {
								record.nilai_uas="";
							}
	
							if (record.nilai_absen.toString()=="" && record.nilai_tugas.toString()=="" && record.nilai_uts.toString()=="" && record.nilai_uas.toString()=="") {
								record['nilai_angka'] = 0;
								record['nilai_huruf'] = "";
								record['nilai_indeks'] = 0;
								record['sksXindeks'] = "";
							} else {
	
								var nilai_absen = (parseFloat(record.nilai_absen)||0 ) * ( parseFloat(formNilai.persen_absen)/100 );
								var nilai_tugas = (parseFloat(record.nilai_tugas)||0 ) * ( parseFloat(formNilai.persen_tugas)/100 ) ;
								var nilai_uts = (parseFloat(record.nilai_uts)||0 ) * ( parseFloat(formNilai.persen_uts)/100 );
								var nilai_uas = (parseFloat(record.nilai_uas)||0 ) * ( parseFloat(formNilai.persen_uas)/100 );
								var nilai_angka = nilai_absen+nilai_tugas+nilai_uts+nilai_uas;
								//console.log(nilai_angka);
	
								abobot.forEach( function(item,index){
									if (nilai_angka>=parseFloat( item.bobot_nilai_min)) {
										record['nilai_angka'] = nilai_angka;
										record['nilai_huruf'] = item.nilai_huruf;
										record['nilai_indeks'] = item.nilai_indeks;
										record['sksXindeks'] = record['vsks_mk']  *  record['nilai_indeks'];
									}
								});
						    
							}
							//console.log(record);
	
							this.updateItem(editor.row, record);
							
						},
					 }, //on
	
					 */

				}, {
					//view:"pager",
					height: 35,
					id: "pagerMahasiswaKelas",
					template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jMahasiswaKelas'>  </span></b> Mahasiswa &nbsp;&nbsp; KETERANGAN : Nilai Absen, Tugas, UTS dan UAS Wajib Diisi",
					size: 15,
					group: 5,
					animate: {
						direction: "left", type: "slide"
					}
				}]
		}]
};


/*====== PEMBIMBING AKADEMIK =======*/

//Tahun Angkatan
var menuBimbinganAkademik = {
	width: 150, borderless: false, rows: [
		{
			template: "Pilih Angkatan",
			type: "header"
		},
		{
			id: "menuBimbinganAkademik",
			view: "list",
			select: true,
			scroll: true,
			url: "sopingi/siakad_angkatan/pilihV1/" + wSiaMhs.apiKey + "/" + Math.random()
		}
	]
};

var riwayatBimbinganAkademik = {
	id: "riwayatBimbinganAkademik",
	type: "space",
	rows: [

		{
			view: "toolbar", paddingY: 2,
			cols: [
				{ view: "button", id: "bimbinganAkademikRiwayatRefresh", label: "Refresh", type: "iconButton", icon: "refresh", width: 100 },
				{ template: "", borderless: true },
				{ view: "button", id: "bimbinganAkademikRiwayatCetak", label: "Presensi", type: "iconButton", icon: "download", width: 100 },
			]
		},
		{
			view: "datatable",
			select: true,
			id: "bimbinganAkademikRiwayatDataTable",
			datafetch: 12,
			loadahead: 12,
			navigation: true,
			fixedRowHeight: false,
			rowLineHeight: 25,
			columns: [
				{ id: "index", header: "No", width: 40 },
				{ id: "nipd", header: ['NIM', { content: "serverFilter" }], width: 80, sort: "server" },
				{ id: "nm_pd", header: ["Nama Mahasiswa", { content: "serverFilter" }], fillspace: true, sort: "server" },
				{ id: "vnm_lemb", header: "Program Studi", width: 250 },
				{ id: "kelas", header: ["Kelas", { content: "serverFilter" }], width: 60 },
				{ id: "vid_jns_daftar", header: "Jenis Daftar", width: 150 },
				{ id: "detail", header: "Detail", width: 100, template: "<button class='btnDetail btnTransparant'><i class='webix_icon fa-users'></i> Detail</button>" },
			],
			pager: "bimbinganAkademikRiwayatPager",
			hover: "tableHover",
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
				},
				onAfterLoad: function () {
					this.hideOverlay();
					var jData = this.data.order.length;
					$("#jBimbinganAkademik").html(jData);
				},
				onAfterFilter: function () {
					var jData = this.data.order.length;
					$("#jBimbinganAkademik").html(jData);
				},
				onLoadError: function (text, data, xhr) {
					var hasil = data.json();
					peringatanStatus(hasil.status_code, hasil.message);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						try {
							obj.index = i + 1;
						} catch (e) {

						}
					});

					var jData = this.data.order.length;
					$("#jBimbinganAkademik").html(jData);
				}
			}

		},

		{
			view: "pager",
			id: "bimbinganAkademikRiwayatPager",
			template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jBimbinganAkademik'>  </span></b> Data",
			size: 12,
			group: 5,
			animate: {
				direction: "left", type: "slide"
			}
		}
	]
};

var viewBimbinganAkademik = new WebixView({
	config: {
		id: "viewBimbinganAkademik",
		type: "clean",
		rows: [
			{
				template: "Bimbingan mahasiswa", type: "header"
			},
			{
				cols: [
					menuBimbinganAkademik, riwayatBimbinganAkademik
				]
			}
		]
	}
});


/*HALAMAN PROFIL MAHASISWA */

var alamatView = {
	view: "scrollview", id: "alamatView", scroll: "y", body: {
		rows: [
			{ view: "text", label: "NIK", name: "nik", id: "nik", required: true, placeholder: "Nomor KTP tanpa tanda baca", invalidMessage: "NIK belum diisi", inputWidth: 300, attributes: { maxlength: 16 } },
			{ view: "text", label: "Negara", name: "kewarganegaraan", id: "kewarganegaraan", required: true, value: "Indonesia", readonly: true, inputWidth: 300 },
			{ view: "text", label: "Jalan", name: "jln", id: "jln", placeholder: "Jalan alamat rumah (Jika ada)", inputWidth: 700 },
			{ view: "text", label: "Dusun", name: "nm_dsn", id: "nm_dsn", placeholder: "Nama dusun (Jika ada)", inputWidth: 350 },
			{ view: "counter", label: "RT", name: "rt", id: "rt", placeholder: "RT (Jika ada)", inputWidth: 200 },
			{ view: "counter", label: "RW", name: "rw", id: "rw", placeholder: "RW (Jika ada)", inputWidth: 200 },
			{ view: "text", label: "Kelurahan", name: "ds_kel", id: "ds_kel", required: true, placeholder: "Nama Kelurahan/ desa", inputWidth: 350, invalidMessage: "Kelurahan belum diisi" },
			{ view: "text", label: "Kode POS", name: "kode_pos", id: "kode_pos", placeholder: "Kode Pos", inputWidth: 250, attributes: { maxlength: 5 } },
			{ view: "combo", label: "Kecamatan", name: "id_wil", id: "id_wil", options: "sopingi/wilayah/tampil/" + wSiaMhs.apiKey + "/" + Math.random(), placeholder: "Ketik kecamatan sampai muncul Kab dan Provinsi", required: true, invalidMessage: "Wilayah belum dipilih", inputWidth: 700 },
			{ view: "richselect", label: "Jenis Tinggal", name: "id_jns_tinggal", id: "id_jns_tinggal", placeholder: "Pilih Jenis Tinggal", required: true, invalidMessage: "Jenis tinggal belum dipilih", options: [{ id: 1, value: "Bersama orang tua" }, { id: 2, value: "Wali" }, { id: 3, value: "Kost" }, { id: 4, value: "Asrama" }, { id: 5, value: "Panti asuhan" }, { id: 99, value: "Lainnya" }], inputWidth: 350 },
			{ view: "text", label: "Telepon", name: "telepon_rumah", id: "telepon_rumah", required: true, placeholder: "Telepon Rumah", inputWidth: 350 },
			{ view: "text", label: "Handphone", name: "telepon_seluler", id: "telepon_seluler", placeholder: "No. Handphone", inputWidth: 350 },
			{ view: "text", label: "Email", name: "email", id: "email", placeholder: "Email", required: true, inputWidth: 400 },
			{
				cols: [
					{ view: "radio", label: "Penerima KPS", name: "a_terima_kps", id: "a_terima_kps", required: true, inputWidth: 250, options: [{ id: "0", value: "Tidak" }, { id: "1", value: "Ya" }], invalidMessage: "KPS belum dipilih" },
					{ template: " ", borderless: true, width: 10 },
					{ view: "text", label: "No. KPS", name: "no_kps", id: "no_kps", placeholder: "No. Kartu Perlindungan Sosial", inputWidth: 300 },
					{ template: "*KPS: Kartu Perlindungan Sosial", borderless: true }
				]
			}
		]
	}
}

var ortuView = {
	view: "scrollview", id: "ortuView", scroll: "y", body: {
		rows: [
			{ template: "Profil Ayah", type: "section" },
			{ view: "text", label: "Nama Ayah", name: "nm_ayah", id: "nm_ayah", required: true, placeholder: "Nama Ayah kandung", invalidMessage: "Nama Ayah belum diisi", inputWidth: 500 },
			{ view: "datepicker", label: "Tanggal Lahir", name: "tgl_lahir_ayah", id: "tgl_lahir_ayah", format: "%d-%m-%Y", required: true, placeholder: "Tanggal lahir", invalidMessage: "Tanggal lahir ayah belum diisi", inputWidth: 250, stringResult: true },
			{ view: "richselect", label: "Pendidikan", name: "id_jenjang_pendidikan_ayah", id: "id_jenjang_pendidikan_ayah", placeholder: "Pilih Pendidikan", required: true, invalidMessage: "Pendidikan ayah belum dipilih", options: [{ id: 0, value: "Tidak sekolah" }, { id: 1, value: "PAUD" }, { id: 2, value: "TK / sederajat" }, { id: 3, value: "Putus SD" }, { id: 4, value: "SD / sederajat" }, { id: 5, value: "SMP / sederajat" }, { id: 6, value: "SMA / sederajat" }, { id: 7, value: "Paket A" }, { id: 8, value: "Paket B" }, { id: 9, value: "Paket C" }, { id: 20, value: "D1" }, { id: 21, value: "D2" }, { id: 22, value: "D3" }, { id: 23, value: "D4" }, { id: 25, value: "Profesi" }, { id: 30, value: "S1" }, { id: 32, value: "Sp-1" }, { id: 35, value: "S2" }, { id: 37, value: "Sp-2" }, { id: 40, value: "S3" }, { id: 90, value: "Non formal" }, { id: 91, value: "Informal" }, { id: 99, value: "Lainnya" }], inputWidth: 280 },
			{ view: "richselect", label: "Pekerjaan", name: "id_pekerjaan_ayah", id: "id_pekerjaan_ayah", placeholder: "Pilih Pekerjaan", required: true, invalidMessage: "Pekerjaan ayah belum dipilih", options: [{ id: 1, value: "Tidak bekerja" }, { id: 2, value: "Nelayan" }, { id: 3, value: "Petani" }, { id: 4, value: "Peternak" }, { id: 5, value: "PNS/TNI/Polri" }, { id: 6, value: "Karyawan Swasta" }, { id: 7, value: "Pedagang Kecil" }, { id: 8, value: "Pedagang Besar" }, { id: 9, value: "Wiraswasta" }, { id: 10, value: "Wirausaha" }, { id: 11, value: "Buruh" }, { id: 12, value: "Pensiunan" }, { id: 99, value: "Lainnya" }, { id: 98, value: "Sudah Meninggal" }], inputWidth: 280 },
			{ view: "richselect", label: "Penghasilan", name: "id_penghasilan_ayah", id: "id_penghasilan_ayah", placeholder: "Pilih Penghasilan", required: true, invalidMessage: "Penghasilan ayah belum dipilih", options: [{ id: 0, value: "Tidak ada" }, { id: 11, value: "Kurang dari Rp. 500,000" }, { id: 12, value: "Rp. 500,000 - Rp. 999,999" }, { id: 13, value: "Rp. 1,000,000 - Rp. 1,999,999" }, { id: 14, value: "Rp. 2,000,000 - Rp. 4,999,999" }, { id: 15, value: "Rp. 5,000,000 - Rp. 20,000,000" }, { id: 16, value: "Lebih dari Rp. 20,000,000" }], inputWidth: 350 },

			{ template: "Profil Ibu", type: "section" },
			{ view: "text", label: "Nama Ibu", name: "vnm_ibu_kandung", id: "vnm_ibu_kandung", readonly: true, placeholder: "Nama ibu kandung", inputWidth: 500 },
			{ view: "datepicker", label: "Tanggal Lahir", name: "tgl_lahir_ibu", id: "tgl_lahir_ibu", format: "%d-%m-%Y", required: true, placeholder: "Tanggal lahir", invalidMessage: "Tanggal lahir ibu belum diisi", inputWidth: 250, stringResult: true },
			{ view: "richselect", label: "Pendidikan", name: "id_jenjang_pendidikan_ibu", id: "id_jenjang_pendidikan_ibu", placeholder: "Pilih Pendidikan", required: true, invalidMessage: "Pendidikan ibu belum dipilih", options: [{ id: 0, value: "Tidak sekolah" }, { id: 1, value: "PAUD" }, { id: 2, value: "TK / sederajat" }, { id: 3, value: "Putus SD" }, { id: 4, value: "SD / sederajat" }, { id: 5, value: "SMP / sederajat" }, { id: 6, value: "SMA / sederajat" }, { id: 7, value: "Paket A" }, { id: 8, value: "Paket B" }, { id: 9, value: "Paket C" }, { id: 20, value: "D1" }, { id: 21, value: "D2" }, { id: 22, value: "D3" }, { id: 23, value: "D4" }, { id: 25, value: "Profesi" }, { id: 30, value: "S1" }, { id: 32, value: "Sp-1" }, { id: 35, value: "S2" }, { id: 37, value: "Sp-2" }, { id: 40, value: "S3" }, { id: 90, value: "Non formal" }, { id: 91, value: "Informal" }, { id: 99, value: "Lainnya" }], inputWidth: 280 },
			{ view: "richselect", label: "Pekerjaan", name: "id_pekerjaan_ibu", id: "id_pekerjaan_ibu", placeholder: "Pilih Pekerjaan", required: true, invalidMessage: "Pekerjaan ibu belum dipilih", options: [{ id: 1, value: "Tidak bekerja" }, { id: 2, value: "Nelayan" }, { id: 3, value: "Petani" }, { id: 4, value: "Peternak" }, { id: 5, value: "PNS/TNI/Polri" }, { id: 6, value: "Karyawan Swasta" }, { id: 7, value: "Pedagang Kecil" }, { id: 8, value: "Pedagang Besar" }, { id: 9, value: "Wiraswasta" }, { id: 10, value: "Wirausaha" }, { id: 11, value: "Buruh" }, { id: 12, value: "Pensiunan" }, { id: 99, value: "Lainnya" }, { id: 98, value: "Sudah Meninggal" }], inputWidth: 280 },
			{ view: "richselect", label: "Penghasilan", name: "id_penghasilan_ibu", id: "id_penghasilan_ibu", placeholder: "Pilih Penghasilan", required: true, invalidMessage: "Penghasilan ibu belum dipilih", options: [{ id: 0, value: "Tidak ada" }, { id: 11, value: "Kurang dari Rp. 500,000" }, { id: 12, value: "Rp. 500,000 - Rp. 999,999" }, { id: 13, value: "Rp. 1,000,000 - Rp. 1,999,999" }, { id: 14, value: "Rp. 2,000,000 - Rp. 4,999,999" }, { id: 15, value: "Rp. 5,000,000 - Rp. 20,000,000" }, { id: 16, value: "Lebih dari Rp. 20,000,000" }], inputWidth: 350 }
		]
	}
}

var waliView = {
	view: "scrollview", id: "waliView", scroll: "y", body: {
		rows: [
			{ template: "Profil Wali", type: "section" },
			{ view: "text", label: "Nama Wali", name: "nm_wali", id: "nm_wali", placeholder: "Nama wali", invalidMessage: "Nama wali belum diisi", inputWidth: 500 },
			{ view: "datepicker", label: "Tanggal Lahir", name: "tgl_lahir_wali", id: "tgl_lahir_wali", format: "%d-%m-%Y", placeholder: "Tanggal lahir", invalidMessage: "Tanggal lahir wali belum diisi", inputWidth: 250, stringResult: true },
			{ view: "richselect", label: "Pendidikan", name: "id_jenjang_pendidikan_wali", id: "id_jenjang_pendidikan_wali", placeholder: "Pilih Pendidikan", invalidMessage: "Pendidikan wali belum dipilih", options: [{ id: 0, value: "Tidak sekolah" }, { id: 1, value: "PAUD" }, { id: 2, value: "TK / sederajat" }, { id: 3, value: "Putus SD" }, { id: 4, value: "SD / sederajat" }, { id: 5, value: "SMP / sederajat" }, { id: 6, value: "SMA / sederajat" }, { id: 7, value: "Paket A" }, { id: 8, value: "Paket B" }, { id: 9, value: "Paket C" }, { id: 20, value: "D1" }, { id: 21, value: "D2" }, { id: 22, value: "D3" }, { id: 23, value: "D4" }, { id: 25, value: "Profesi" }, { id: 30, value: "S1" }, { id: 32, value: "Sp-1" }, { id: 35, value: "S2" }, { id: 37, value: "Sp-2" }, { id: 40, value: "S3" }, { id: 90, value: "Non formal" }, { id: 91, value: "Informal" }, { id: 99, value: "Lainnya" }], inputWidth: 280 },
			{ view: "richselect", label: "Pekerjaan", name: "id_pekerjaan_wali", id: "id_pekerjaan_wali", placeholder: "Pilih Pekerjaan", invalidMessage: "Pekerjaan wali belum dipilih", options: [{ id: 1, value: "Tidak bekerja" }, { id: 2, value: "Nelayan" }, { id: 3, value: "Petani" }, { id: 4, value: "Peternak" }, { id: 5, value: "PNS/TNI/Polri" }, { id: 6, value: "Karyawan Swasta" }, { id: 7, value: "Pedagang Kecil" }, { id: 8, value: "Pedagang Besar" }, { id: 9, value: "Wiraswasta" }, { id: 10, value: "Wirausaha" }, { id: 11, value: "Buruh" }, { id: 12, value: "Pensiunan" }, { id: 99, value: "Lainnya" }, { id: 98, value: "Sudah Meninggal" }], inputWidth: 280 },
			{ view: "richselect", label: "Penghasilan", name: "id_penghasilan_wali", id: "id_penghasilan_wali", placeholder: "Pilih Penghasilan", invalidMessage: "Penghasilan wali belum dipilih", options: [{ id: 0, value: "Tidak ada" }, { id: 11, value: "Kurang dari Rp. 500,000" }, { id: 12, value: "Rp. 500,000 - Rp. 999,999" }, { id: 13, value: "Rp. 1,000,000 - Rp. 1,999,999" }, { id: 14, value: "Rp. 2,000,000 - Rp. 4,999,999" }, { id: 15, value: "Rp. 5,000,000 - Rp. 20,000,000" }, { id: 16, value: "Lebih dari Rp. 20,000,000" }], inputWidth: 350 }
		]
	}
}

var kebutuhanView = {
	view: "scrollview", id: "kebutuhanView", scroll: "y", body: {
		rows: [
			{ template: "Mahasiswa", type: "section" },
			{
				cols: [
					{
						rows: [
							{ view: "checkbox", id: "mhs_a_kk_a", name: "mhs_a_kk_a", label: "A - Tuna netra", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_b", name: "mhs_a_kk_b", label: "B - Tuna rungu", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_c", name: "mhs_a_kk_c", label: "C - Tuna grahita ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_c1", name: "mhs_a_kk_c1", label: "C1 - Tuna grahita ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_d", name: "mhs_a_kk_d", label: "D - Tuna daksa ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_d1", name: "mhs_a_kk_d1", label: "D1 - Tuna daksa sedang", labelWidth: 150, labelAlign: "right" }
						]
					},
					{
						rows: [
							{ view: "checkbox", id: "mhs_a_kk_e", name: "mhs_a_kk_e", label: "E - Tuna laras", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_f", name: "mhs_a_kk_f", label: "F - Tuna wicara", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_h", name: "mhs_a_kk_h", label: "H - Hiperaktif", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_i", name: "mhs_a_kk_i", label: "I - Cerdas Istimewa", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_j", name: "mhs_a_kk_j", label: "J - Bakat Istimewa", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_k", name: "mhs_a_kk_k", label: "K - Kesulitan Belajar", labelWidth: 150, labelAlign: "right" }
						]
					},
					{
						rows: [
							{ view: "checkbox", id: "mhs_a_kk_n", name: "mhs_a_kk_n", label: "N - Narkoba", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_o", name: "mhs_a_kk_o", label: "O - Indigo", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_p", name: "mhs_a_kk_p", label: "P - Down Syndrome", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "mhs_a_kk_q", name: "mhs_a_kk_q", label: "Q - Autis", labelWidth: 150, labelAlign: "right" },
							{ template: " ", borderless: true }
						]
					},
					{ template: " ", borderless: true }
				]
			},
			{ template: "Ayah", type: "section" },
			{
				cols: [
					{
						rows: [
							{ view: "checkbox", id: "ayah_a_kk_a", name: "ayah_a_kk_a", label: "A - Tuna netra", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_b", name: "ayah_a_kk_b", label: "B - Tuna rungu", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_c", name: "ayah_a_kk_c", label: "C - Tuna grahita ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_c1", name: "ayah_a_kk_c1", label: "C1 - Tuna grahita ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_d", name: "ayah_a_kk_d", label: "D - Tuna daksa ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_d1", name: "ayah_a_kk_d1", label: "D1 - Tuna daksa sedang", labelWidth: 150, labelAlign: "right" }
						]
					},
					{
						rows: [
							{ view: "checkbox", id: "ayah_a_kk_e", name: "ayah_a_kk_e", label: "E - Tuna laras", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_f", name: "ayah_a_kk_f", label: "F - Tuna wicara", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_h", name: "ayah_a_kk_h", label: "H - Hiperaktif", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_i", name: "ayah_a_kk_i", label: "I - Cerdas Istimewa", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_j", name: "ayah_a_kk_j", label: "J - Bakat Istimewa", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_k", name: "ayah_a_kk_k", label: "K - Kesulitan Belajar", labelWidth: 150, labelAlign: "right" }
						]
					},
					{
						rows: [
							{ view: "checkbox", id: "ayah_a_kk_n", name: "ayah_a_kk_n", label: "N - Narkoba", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_o", name: "ayah_a_kk_o", label: "O - Indigo", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_p", name: "ayah_a_kk_p", label: "P - Down Syndrome", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ayah_a_kk_q", name: "ayah_a_kk_q", label: "Q - Autis", labelWidth: 150, labelAlign: "right" },
							{ template: " ", borderless: true }
						]
					},
					{ template: " ", borderless: true }
				]
			},
			{ template: "Ibu", type: "section" },
			{
				cols: [
					{
						rows: [
							{ view: "checkbox", id: "ibu_a_kk_a", name: "ibu_a_kk_a", label: "A - Tuna netra", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_b", name: "ibu_a_kk_b", label: "B - Tuna rungu", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_c", name: "ibu_a_kk_c", label: "C - Tuna grahita ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_c1", name: "ibu_a_kk_c1", label: "C1 - Tuna grahita ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_d", name: "ibu_a_kk_d", label: "D - Tuna daksa ringan", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_d1", name: "ibu_a_kk_d1", label: "D1 - Tuna daksa sedang", labelWidth: 150, labelAlign: "right" }
						]
					},
					{
						rows: [
							{ view: "checkbox", id: "ibu_a_kk_e", name: "ibu_a_kk_e", label: "E - Tuna laras", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_f", name: "ibu_a_kk_f", label: "F - Tuna wicara", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_h", name: "ibu_a_kk_h", label: "H - Hiperaktif", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_i", name: "ibu_a_kk_i", label: "I - Cerdas Istimewa", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_j", name: "ibu_a_kk_j", label: "J - Bakat Istimewa", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_k", name: "ibu_a_kk_k", label: "K - Kesulitan Belajar", labelWidth: 150, labelAlign: "right" }
						]
					},
					{
						rows: [
							{ view: "checkbox", id: "ibu_a_kk_n", name: "ibu_a_kk_n", label: "N - Narkoba", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_o", name: "ibu_a_kk_o", label: "O - Indigo", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_p", name: "ibu_a_kk_p", label: "P - Down Syndrome", labelWidth: 150, labelAlign: "right" },
							{ view: "checkbox", id: "ibu_a_kk_q", name: "ibu_a_kk_q", label: "Q - Autis", labelWidth: 150, labelAlign: "right" },
							{ template: " ", borderless: true }
						]
					},
					{ template: " ", borderless: true }
				]
			}

		]
	}
}

var viewMahasiswaDetail = {
	id: "viewMahasiswaDetail", type: "space", rows: [
		{
			view: "toolbar", paddingY: 2,
			cols: [
				{ view: "label", template: "Biodata Mahasiswa", borderless: true }
			]
		},
		{
			view: "form",
			id: "formMahasiswaDetail",
			borderless: true,
			elements: [
				{
					cols: [{
						rows: [
							{ view: "text", label: "No.Daftar", name: "no_pend", id: "no_pend", required: true, placeholder: "No. Daftar (SPMB)", invalidMessage: "No.Daftar belum diisi", inputWidth: 450, labelWidth: 150, readonly: true },
							{ view: "text", label: "Nama", name: "nm_pd", id: "nm_pd", required: true, placeholder: "Nama Lengkap Sesuai Ijazah", invalidMessage: "Nama belum diisi", inputWidth: 400, labelWidth: 150 },
							{ view: "text", label: "Tempat Lahir", name: "tmpt_lahir", id: "tmpt_lahir", required: true, placeholder: "Tempat lahir", invalidMessage: "Tempat lahir belum diisi", inputWidth: 300, labelWidth: 150 },
							{ view: "datepicker", label: "Tanggal Lahir", name: "tgl_lahir", id: "tgl_lahir", format: "%d-%m-%Y", required: true, editable: true, placeholder: "dd-mm-yyyy", invalidMessage: "Tanggal lahir belum diisi", inputWidth: 300, labelWidth: 150, stringResult: true },
							{ view: "radio", label: "Jenis Kelamin", name: "jk", id: "jk", required: true, options: [{ id: "L", value: "Laki-laki" }, { id: "P", value: "Perempuan" }], invalidMessage: "Jenis kelamin belum dipilih", inputWidth: 350, labelWidth: 150 },
							{ view: "richselect", label: "Agama", name: "id_agama", id: "id_agama", placeholder: "Pilih Agama", required: true, invalidMessage: "Agama belum dipilih", options: [{ id: 1, value: "Islam" }, { id: 2, value: "Kristen" }, { id: 3, value: "Katholik" }, { id: 4, value: "Hindu" }, { id: 5, value: "Budha" }, { id: 6, value: "Konghucu" }, { id: 99, value: "Lainnya" }], inputWidth: 280, labelWidth: 150 },
							{ view: "text", label: "Nama Ibu Kandung", name: "nm_ibu_kandung", id: "nm_ibu_kandung", required: true, placeholder: "Nama Ibu kandung sesuai KTP", invalidMessage: "Nama ibu kandung belum diisi", inputWidth: 400, labelWidth: 150 }
						]
					},
					{ template: " ", borderless: true, width: 50 },
					{
						css: 'dataAkademik', rows: [
							{ view: "text", label: "No. Induk Mahasiswa", name: "nipd", id: "nipd", placeholder: "NIM", invalidMessage: "No.Daftar belum diisi", inputWidth: 350, labelWidth: 150, readonly: true },
							{ view: "richselect", label: "Program Studi", name: "id_sms", id: "id_sms", placeholder: "Pilih Program Studi", invalidMessage: "Program studi belum dipilih", options: "sopingi/sms/pilih/" + wSiaMhs.apiKey + "/" + Math.random(), value: "", inputWidth: 450, labelWidth: 150, readonly: true },
							{ view: "richselect", label: "Mulai Masuk", name: "mulai_smt", id: "mulai_smt", placeholder: "Pilih Semester", options: "sopingi/semester/pilihSemua/" + wSiaMhs.apiKey + "/" + Math.random(), invalidMessage: "Semester belum dipilih", inputWidth: 350, labelWidth: 150, readonly: true },
							{ view: "richselect", label: "Jenis Daftar", name: "id_jns_daftar", id: "id_jns_daftar", placeholder: "Pilih Jenis Daftar", invalidMessage: "Jenis Daftar belum dipilih", options: [{ id: 1, value: "Mahasiswa Baru" }, { id: 2, value: "Pindahan/Transfer" }], inputWidth: 350, labelWidth: 150, readonly: true },
							{ view: "richselect", label: "Kelas", name: "kelas", id: "kelas", placeholder: "Pilih nama kelas", invalidMessage: "Nama kelas belum dipilih", options: "sopingi/siakad_kelas/pilih/" + wSiaMhs.apiKey + "/" + Math.random(), inputWidth: 450, labelWidth: 150, readonly: true },
							{ view: "combo", label: "Pembimbing Akademik", name: "pa", id: "pa", placeholder: "Pilih Dosen", invalidMessage: "Pembimbing Akademik belum dipilih", options: "sopingi/dosen/pilih/" + wSiaMhs.apiKey + "/" + Math.random(), inputWidth: 450, labelWidth: 150, readonly: true }

						]
					},

					]
				},
				{
					view: "tabbar", id: 'tabbar', value: 'alamatView', multiview: true, options: [
						{ value: 'Alamat', id: 'alamatView' },
						{ value: 'Orang Tua', id: 'ortuView' },
						{ value: 'Wali', id: 'waliView' },
						{ value: 'Kebutuhan Khusus', id: 'kebutuhanView' }
					]
				},
				{ cells: [alamatView, ortuView, waliView, kebutuhanView] },

				{ view: "text", name: "xid_pd", id: "xid_pd", hidden: true },
				{ view: "text", name: "xid_reg_pd", id: "xid_reg_pd", hidden: true },
				{ view: "text", name: "aksi", id: "aksi", value: "simpan", required: true, hidden: true },
			],
			on: {
				onValidationError: function (key, obj) {
					webix.message({ type: "error", text: key });
				}
			},
			elementsConfig: {
				labelPosition: "left",
				labelWidth: 100
			}
		}
	]
};

/* HALAMAN KRS */
var halamanKrs = new WebixView({
	config: {
		type: "space", borderless: true,
		rows: [
			{
				view: "toolbar", borderless: true,
				cols: [
					{ template: "KRS Mahasiswa", css: "headerBg", borderless: true },
					{ view: "button", label: "Tambah", id: "tambahKRS", type: "iconButton", icon: "plus", width: 100 },
					{ view: "button", id: "hapusKRS", label: "Hapus", type: "iconButton", icon: "remove", width: 100 },
					{ view: "button", label: "Refresh", id: "refreshKRS", type: "iconButton", icon: "refresh", width: 100 },
					{ view: "button", label: "Download KRS PDF", id: "unduhKRS", type: "iconButton", icon: "file-pdf-o", width: 150 },
				],
			},
			{
				view: "datatable",
				select: true,
				footer: true,
				id: "dataTableKrs",
				fixedRowHeight: false,
				columns: [
					{ id: "index", header: "No", width: 40, footer: { text: "Jumlah SKS:", colspan: 4, css: { 'text-align': 'right', 'font-weight': 'bold' } } },
					{ id: "nm_kls", header: "Kelas", adjust: "data" },
					{ id: "kode_mk", header: "Kode MK", adjust: "data", sort: "string" },
					{ id: "nm_mk", header: "Nama Mata Kuliah", fillspace: true, sort: "string" },
					{ id: "vsks_mk", header: "Jml SKS", width: 40, sort: "string", footer: { id: "jSKS", content: "summColumn", css: { 'text-align': 'center', 'font-weight': 'bold' } } },
					{ id: "vsks_tm", header: [{ text: "Komposisi SKS", colspan: 3, css: { "text-align": "center" } }, "T"], width: 40, footer: { id: "jSKSt", content: "summColumn", css: { 'text-align': 'center', 'font-weight': 'bold' } } },
					{ id: "vsks_prak", header: ["", "P"], width: 40, footer: { id: "jSKSp", content: "summColumn", css: { 'text-align': 'center', 'font-weight': 'bold' } } },
					{ id: "vsks_prak_lap", header: ["", "K"], width: 40, footer: { id: "jSKSk", content: "summColumn", css: { 'text-align': 'center', 'font-weight': 'bold' } } },
					{ id: "vid_smt", header: "Semester", adjust: "data", sort: "string" },
					{ id: "dosen_pengampu", header: "Dosen Pengajar", adjust: "data", sort: "string" },
				],
				pager: "pagerKrs",
				url: "sopingi/nilai/tampil/" + wSiaMhs.apiKey + "/" + Math.random(),
				on: {
					onBeforeLoad: function () {
						this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
						$$('tambahKRS').disable();
						$$('hapusKRS').disable();
						$$('refreshKRS').disable();
					},
					onAfterLoad: function () {

						webix.delay(function () {
							this.adjustRowHeight("dosen_pengampu", true);
							this.render();
						}, this);

						this.hideOverlay();
						jKrs = this.data.order.length;
						$("#jKrs").html(jKrs);

						jSKS = 0;
						this.eachRow(function (id) {
							var item = this.getItem(id);
							jSKS += parseInt(item.vsks_mk);
						});

						$("#jSKS").html(jSKS);

						$$('tambahKRS').enable();
						$$('hapusKRS').enable();
						$$('refreshKRS').enable();

					},
					onAfterFilter: function () {
						jKrs = this.data.order.length;
						$("#jKrs").html(jKrs);
					},
					"data->onStoreUpdated": function () {
						this.data.each(function (obj, i) {
							obj.index = i + 1;
						});

						jKrs = this.data.order.length;
						$("#jKrs").html(jKrs);
					}
				}
			},
			{
				view: "pager",
				id: "pagerKrs",
				css: "pager",
				template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKrs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS",
				size: 12,
				group: 5,
				animate: {
					direction: "left", type: "slide"
				}
			},
			{ template: "Bagi yang ingin menambah mata kuliah untuk <b>Perbaikan Nilai</b>, silahkan konsultasi <b>Terlebih Dahulu</b> dengan Pembimbing Akademik masing-masing, sebelum mengisi KRS Online", height: 60 }]
	} //config
});

function checkbox_krs(obj, common, value) {
	if (value) {
		return "<div class='webix_table_checkbox webix_icon fa-check checked'> diambil</div>";
	} else {
		return "<div class='webix_table_checkbox webix_icon fa-close notchecked'> tidak diambil</div>";
	}
};

var formKRS = {
	rows: [
		{
			view: "scrollview", id: "scrollKRS", scroll: "y", height: 500, width: 800,
			body: {
				view: "form",
				id: "formKRS",
				borderless: true,
				elements: [
					{ template: "<ul class='info_krs'><li>Pastikan mata kuliah yang diambil sudah TERCENTANG</li><li>Khusus mata kuliah Pendidikan Agama, silahkan pilih salah satu</li></ul>", height: 60, borderless: true },
					{
						view: "datatable", label: "Mata Kuliah", id: "dataTableKelasPerkuliahan", autoheight: true, checkboxRefresh: true,
						columns: [
							{ id: "index", header: "No", width: 30 },
							{ id: "nm_kls", header: "Kelas", width: 50 },
							{ id: "kode_mk", header: "Kode MK", sort: "string", width: 60 },
							{ id: "nm_mk", header: "Nama Mata Kuliah", fillspace: true, sort: "string" },
							{ id: "vsks_mk", header: "SKS", sort: "string", width: 40 },
							{ id: "vid_smt", header: "Semester", width: 120 },
							{ id: "ambilKelas", header: "Ambil (Klik Centang)", template: checkbox_krs, width: 150 }
						],
						//url:"sopingi/kelas_kuliah/tampil/"+wSiaMhs.apiKey+"/"+Math.random(),
						on: {
							onBeforeLoad: function () {
								this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
								$$('simpanKRS').disable();
							},
							onAfterLoad: function () {
								this.hideOverlay();
								$$('simpanKRS').enable();
							},
							"data->onStoreUpdated": function () {
								this.data.each(function (obj, i) {
									obj.index = i + 1;
								})
							}
						}
					},
					{ view: "text", name: "aksi", id: "aksi", required: true, hidden: true, value: "tambah" },
				],
				elementsConfig: {
					labelPosition: "top",
				}
			}
		}, {
			cols: [
				{ template: " ", borderless: true },
				{ view: "icon", icon: "hand-o-right" },
				{ view: "button", id: "simpanKRS", label: "Tambahkan Kelas Mata Kuliah", type: "form", width: 200, borderless: true },
				{ template: " ", borderless: true }
			]
		}
	]
};

/* HALAMAN KHS */
var halamanKhs = new WebixView({
	config: {
		id: "viewMahasiswaKHS", type: "space", borderless: true,
		rows: [
			{
				view: "toolbar", borderless: true,
				cols: [
					{ template: "KHS Mahasiswa", css: "headerBg", borderless: true, width: 100 },
					{ view: "richselect", label: "", name: "khs_id_smt", id: "khs_id_smt", placeholder: "Pilih semester", options: "sopingi/semester/pilih/" + wSiaMhs.apiKey + "/" + Math.random(), borderless: true, width: 220 },
					{ view: "button", label: "KHS PDF", id: "khsPDF", type: "iconButton", icon: "file-pdf-o", width: 100 },
					{},
					{ view: "button", label: "Transkip PDF", id: "transkipPDF", type: "iconButton", icon: "file-pdf-o", width: 120 }
				]
			},
			{
				view: "datatable",
				select: true,
				footer: true,
				id: "dataTableKhs",
				columns: [
					{ id: "index", header: "No", width: 40, footer: { text: "Jumlah SKS:", colspan: 4, css: { 'text-align': 'right', 'font-weight': 'bold' } } },
					{ id: "nm_kls", header: "Kelas", adjust: "data" },
					{ id: "kode_mk", header: "Kode MK", adjust: "data", sort: "string" },
					{ id: "nm_mk", header: "Nama Mata Kuliah", fillspace: true, sort: "string" },
					{ id: "vsks_mk", header: "Jml SKS", css: { "text-align": "center" }, width: 70, format: webix.i18n.numberFormat, footer: { id: "jSKS", content: "summColumn", css: { 'text-align': 'center', 'font-weight': 'bold' } } },
					{ id: "nilai_angka", header: [{ text: "Nilai", colspan: 3, css: { "text-align": "center" } }, "Angka"], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 60, footer: { text: "&sum; SKS*N.Indeks:", colspan: 3, css: { 'text-align': 'right', 'font-weight': 'bold' } }, editor: "text" },
					{ id: "nilai_huruf", header: ["", "Huruf"], css: { "text-align": "center" }, width: 60 },
					{ id: "nilai_indeks", header: ["", "Indeks"], css: { "text-align": "right" }, format: webix.i18n.numberFormat, width: 60 },
					{ id: "sksXindeks", header: "SKS*N.Indeks", css: { "text-align": "right" }, format: webix.i18n.numberFormat, width: 90, footer: { content: "summColumn", css: { 'text-align': 'right', 'font-weight': 'bold' } } }
				],
				pager: "pagerKhs",
				url: "sopingi/nilai/tampilKhs/" + wSiaMhs.apiKey + "/" + Math.random(),
				on: {
					onBeforeLoad: function () {
						this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
						$$('khs_id_smt').disable();
						$$('khsPDF').disable();
						$$('transkipPDF').disable();
					},
					onAfterLoad: function () {
						this.hideOverlay();
						jKhs = this.data.order.length;
						$("#jKhs").html(jKhs);

						jSKS = 0;
						jSKSxIndex = 0;
						this.eachRow(function (id) {
							var item = this.getItem(id);
							jSKS += parseInt(item.vsks_mk);
							jSKSxIndex += parseInt(item.sksXindeks);
						});

						if (jSKSxIndex.toString() != "NaN") {
							ips = jSKSxIndex / jSKS;
						} else {
							ips = 0;
						}

						$("#IPS").html(ips);
						$("#jSKS").html(jSKS);

						$$('khs_id_smt').enable();
						$$('khsPDF').enable();
						$$('transkipPDF').enable();

					},
					onAfterFilter: function () {
						jKhs = this.data.order.length;
						$("#jKhs").html(jKhs);
					},
					"data->onStoreUpdated": function () {
						this.data.each(function (obj, i) {
							obj.index = i + 1;
						});

						jKhs = this.data.order.length;
						$("#jKhs").html(jKhs);
					}
				}
			},
			{
				view: "pager",
				id: "pagerKhs",
				css: "pager",
				size: 12,
				group: 5,
				template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKhs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS, Indeks Prestasi Semester = <b><span id='IPS'>0</span></b>"
			}
		]
	}
});


//Mahasiswa KRS
var checkbox_krs = function (obj, common, value) {
	if (value) {
		return "<div class='webix_table_checkbox webix_icon fa-check checked'> diambil</div>";
	} else {
		return "<div class='webix_table_checkbox webix_icon fa-close notchecked'> tidak diambil</div>";
	}
};

var formKRS = {
	rows: [
		{
			view: "scrollview", id: "scrollKRS", scroll: "y", height: 500, width: 800,
			body: {
				view: "form",
				id: "formKRS",
				borderless: true,
				elements: [
					{
						template: "<ul class='info_krs'><li>Pastikan mata kuliah yang diambil sudah TERCENTANG</li><li>Khusus mata kuliah Pendidikan Agama, silahkan pilih salah satu</li></ul>", height: 60, borderless: true
					},
					{
						view: "datatable",
						label: "Mata Kuliah",
						id: "dataTableKelasPerkuliahan",
						checkboxRefresh: true,
						hover: "table_hover",
						columns: [
							{ id: "index", header: "No", width: 30 },
							{ id: "nm_kls", header: "Kelas", width: 50 },
							{ id: "kode_mk", header: "Kode MK", sort: "string", width: 60 },
							{ id: "nm_mk", header: ["Nama Mata Kuliah", { content: "textFilter" }], fillspace: true, sort: "string" },
							{ id: "vsks_mk", header: "SKS", sort: "string", width: 40 },
							{ id: "vid_smt", header: "Semester", width: 150 },
							{ id: "ambilKelas", header: "Ambil (Klik Centang)", template: checkbox_krs, width: 160 }
						],

						on: {
							"data->onStoreUpdated": function () {
								this.data.each(function (obj, i) {
									obj.index = i + 1;
								})
							}
						}
					}
				],
				elementsConfig: {
					labelPosition: "top",
				}
			}

		},
		{
			type: "clean",
			cols: [
				{ template: "", borderless: true },
				{ view: "button", id: "simpanKRS", label: "Tambahkan Kelas Mata Kuliah", type: "form", width: 300, borderless: true },
				{ template: "", borderless: true }
			]
		}
	]
};


var viewMahasiswaKRS = {
	id: "viewMahasiswaKRS", type: "space", borderless: true,
	rows: [
		{
			view: "toolbar", borderless: true,
			cols: [
				{ view: "label", id: "judulKrs", template: "KRS Mahasiswa pada Semester Aktif saat ini", borderless: true },

				{ view: "button", label: "Tambah", id: "tambahKRS", type: "iconButton", icon: "plus", width: 100, },
				{ view: "button", id: "hapusKRS", label: "Hapus", type: "iconButton", icon: "trash", width: 100 },
				{ view: "button", label: "Refresh", id: "refreshKRS", type: "iconButton", icon: "refresh", width: 100 },
				//{ view:"button", label:"Download KRS PDF", id:"unduhKRS", type:"iconButton", icon:"file-pdf-o", width:150 },
			],
		},
		{
			view: "datatable",
			select: true,
			footer: true,
			id: "dataTableKrs",
			scheme: {
				$change: function (item) {
					if (item.asal_data <= 2) {
						item.$css = "highlight";
					}
				}
			},

			columns: [
				{ id: "index", header: "No", width: 40, footer: { text: "Jumlah SKS:", colspan: 5, css: { 'text-align': 'right', 'font-weight': 'bold' } } },
				{ id: "vid_smt", header: "Semester", width: 130, sort: "string" },
				{ id: "nm_kls", header: "Kelas", width: 50 },
				{ id: "kode_mk", header: "Kode MK", width: 80, sort: "string" },
				{ id: "nm_mk", header: "Nama Mata Kuliah", fillspace: true, sort: "string" },
				{ id: "vsks_mk", header: "SKS", width: 40, sort: "string", footer: { id: "jSKS", content: "summColumn", css: { 'text-align': 'right', 'font-weight': 'bold' } } },
				{ id: "vasal_data", header: "Diisi oleh", fillspace: true, sort: "string" },
				//{ id:"nilai_indeks", header:"Index",fillspace:true, sort:"numeric"},

			],
			pager: "pagerKrs",
			//url:"sopingi/nilai/tampil/"+wSimpeg.apiKey+"/"+Math.random(),
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
					//$$('refreshKRS').disable();
				},
				onAfterLoad: function () {
					this.hideOverlay();
					var jKrs = this.data.order.length;
					$("#jKrs").html(jKrs);

					var jSKS = 0;
					this.eachRow(function (id) {
						var item = this.getItem(id);
						jSKS += parseInt(item.vsks_mk);
					});

					$("#jSKS").html(jSKS);

					//$$('refreshKRS').enable();

				},
				onAfterFilter: function () {
					var jKrs = this.data.order.length;
					$("#jKrs").html(jKrs);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						obj.index = i + 1;
					});

					var jKrs = this.data.order.length;
					$("#jKrs").html(jKrs);
				}
			}
		},
		{
			view: "pager",
			id: "pagerKrs",
			css: "pager",
			template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKrs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS",
			size: 15,
			group: 5,
			animate: {
				direction: "left", type: "slide"
			}
		}
	]
};

var nilai_style = function (value, config) {
	if (value == "" || value == "T") {
		return { "background": "#ff0000" };
	}
}

//MAHASISWA KHS
var abobot; //bobot Nilai
var record;
var nilaiBaru;
var viewMahasiswaKHS = {
	id: "viewMahasiswaKHS", type: "space", borderless: true,
	rows: [
		{
			view: "toolbar", borderless: true,
			cols: [
				{ view: "label", template: "History Nilai Mahasiswa", borderless: true },
				{},
				{ view: "button", label: "Refresh", id: "refreshKhs", type: "iconButton", icon: "refresh", width: 100 },
				{ view: "button", label: "Unduh Excel", id: "excelKhs", type: "iconButton", icon: "file-excel-o", width: 120 },
			],
		},
		{
			view: "datatable",
			select: true,
			footer: true,
			editable: true,
			id: "dataTableKhs",
			columns: [
				{ id: "index", header: "No", width: 40, footer: { text: "Jumlah SKS:", colspan: 5, css: { 'text-align': 'right', 'font-weight': 'bold' } } },
				{ id: "vid_smt", header: "Semester", adjust: "data", sort: "string" },
				{ id: "nm_kls", header: "Kelas", adjust: "data" },
				{ id: "kode_mk", header: "Kode MK", adjust: "data", sort: "string" },
				{ id: "nm_mk", header: "Nama Mata Kuliah", fillspace: true, sort: "string" },
				{ id: "vsks_mk", header: "Jml SKS", css: { "text-align": "center" }, width: 70, format: webix.i18n.numberFormat, footer: { id: "jSKS", content: "summColumn", css: { 'text-align': 'center', 'font-weight': 'bold' } } },
				{ id: "nilai_angka", header: [{ text: "Nilai", colspan: 3, css: { "text-align": "center" } }, "Angka"], css: { "text-align": "center" }, format: webix.i18n.numberFormat, width: 60, footer: { text: "&sum; SKS*N.Indeks:", colspan: 3, css: { 'text-align': 'right', 'font-weight': 'bold' } }, },
				{ id: "nilai_huruf", header: ["", "Huruf"], css: { "text-align": "center" }, cssFormat: nilai_style, width: 60 },
				{ id: "nilai_indeks", header: ["", "Indeks"], css: { "text-align": "right" }, format: webix.i18n.numberFormat, width: 60 },
				{ id: "sksXindeks", header: "SKS*N.Indeks", css: { "text-align": "right" }, format: webix.i18n.numberFormat, width: 90, footer: { content: "summColumn", css: { 'text-align': 'right', 'font-weight': 'bold' } } }
			],
			pager: "pagerKhs",
			//url:"sopingi/nilai/tampilKHS/"+wSimpeg.apiKey+"/"+Math.random(),
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");

				},
				onAfterLoad: function () {
					this.hideOverlay();
					var jKhs = this.data.order.length;
					$("#jKhs").html(jKhs);

					var jSKS = 0;
					var jSKSxIndex = 0;
					this.eachRow(function (id) {
						var item = this.getItem(id);
						jSKS += parseInt(item.vsks_mk);
						jSKSxIndex += parseInt(item.sksXindeks);
					});

					var ips = jSKSxIndex / jSKS;
					ips = ips.toFixed(2);
					$("#IPS").html(ips);
					$("#jSKS").html(jSKS);


				},
				onAfterFilter: function () {
					var jKhs = this.data.order.length;
					$("#jKhs").html(jKhs);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						obj.index = i + 1;
					});

					var jKhs = this.data.order.length;
					$("#jKhs").html(jKhs);
				}
			}
		},
		{
			cols: [
				{
					view: "pager",
					id: "pagerKhs",
					css: "pager",
					template: "{common.prev()} {common.pages()} {common.next()}",
					size: 15,
					group: 5,
					animate: {
						direction: "left", type: "slide"
					}
				},
				{
					view: "template", template: "Nilai Huruf Merah Artinya: Nilai Tunda atau Belum Diisi"
				},
				{
					view: "template", template: "Index Prestasi Komulatif: <b><span id='IPS'></span></b>"
				}
			]
		}
	]
}; //khs

//Mahasiswa Aktifitas
var viewMahasiswaAktifitas = {
	id: "viewMahasiswaAktifitas", type: "space", borderless: true,
	rows: [
		{
			view: "toolbar", borderless: true,
			cols: [
				{ view: "label", template: "Aktifitas Mahasiswa", borderless: true },
				{},
				{ view: "button", label: "Refresh", id: "refreshAktifitas", type: "iconButton", icon: "refresh", width: 100 },
				{ view: "button", label: "Unduh Excel", id: "excelAktifitas", type: "iconButton", icon: "file-excel-o", width: 120 },
			],
		},
		{
			view: "datatable",
			select: true,
			id: "dataTableAktifitas",
			columns: [
				{ id: "index", header: "No", width: 40 },
				{ id: "nipd", header: "NIM", width: 80 },
				{ id: "nm_pd", header: "Nama Mahasiswa", fillspace: true },
				{ id: "id_smt", header: "Semester", width: 80 },
				{ id: "id_stat_mhs", header: "Status", width: 70, sort: "string" },
				{ id: "ips", header: "IPS", width: 70, sort: "string", css: { 'text-align': 'right' } },
				{ id: "sks_smt", header: "SKS Smt", width: 70, sort: "string", css: { 'text-align': 'right' } },
				{ id: "ipk", header: "IPK", width: 70, sort: "string", css: { 'text-align': 'right' } },
				{ id: "sks_total", header: "SKS Total", width: 70, sort: "string", css: { 'text-align': 'right' } },
			],
			pager: "pagerAktifitas",
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
				},
				onAfterLoad: function () {
					this.hideOverlay();
					var jAktifitas = this.data.order.length;
					$("#jAktifitas").html(jAktifitas);
				},
				onAfterFilter: function () {
					var jAktifitas = this.data.order.length;
					$("#jAktifitas").html(jAktifitas);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						try {
							obj.index = i + 1;
						} catch (err) {

						}
					});

					var jAktifitas = this.data.order.length;
					$("#jAktifitas").html(jAktifitas);
				}
			}
		},
		{
			view: "pager",
			id: "pagerAktifitas",
			css: "pager",
			template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jAktifitas'>0</span></b> Data",
			size: 15,
			group: 5,
			animate: {
				direction: "left", type: "slide"
			}
		},
		{
			template: "Jika SKS dan IPK terakhir tidak sesuai dengan Histori Nilai. Mungkin BAAK belum update aktifitas Mahasiswa", height: 60
		}
	]
};

var viewJurnalBimbingan = {
	id: "viewJurnalBimbingan",
	type: "space",
	rows: [

		{
			view: "toolbar", paddingY: 2,
			cols: [
				{ view: "label", template: "Jurnal Bimbingan Akademik", borderless: true },
				{ view: "button", label: "Tambah", id: "tambahJurnal", type: "iconButton", icon: "plus", width: 100, },
				{ view: "button", label: "Ubah", id: "ubahJurnal", type: "iconButton", icon: "pencil", width: 100, },
				{ view: "button", label: "Hapus", id: "hapusJurnal", type: "iconButton", icon: "trash", width: 100 },
				{ view: "button", label: "Refresh", id: "refreshJurnal", type: "iconButton", icon: "refresh", width: 100 },
			]
		},
		{
			view: "datatable",
			select: true,
			id: "jurnalDataTable",
			navigation: true,
			fixedRowHeight: false,
			rowLineHeight: 25,
			columns: [
				{ id: "index", header: "No", width: 40 },
				{ id: "tanggal", header: ['Tanggal', { content: "serverFilter" }], width: 150, sort: "server", format: webix.Date.dateToStr("%d-%m-%Y %H:%i:%s") },
				{ id: "konten", header: ["Jurnal Bimbingan", { content: "serverFilter" }], fillspace: true, sort: "server" },
				{ id: "oleh", header: ["dibuat Oleh", { content: "serverSelectFilter" }], width: 100, sort: "server" },
				{ id: "detail", header: "Pesan", width: 100, template: "<button class='btnChat btnTransparant'><i class='webix_icon fa-send'></i> Pesan</button>" },
			],
			pager: "jurnalPager",
			hover: "tableHover",
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
				},
				onAfterLoad: function () {
					this.hideOverlay();
					var jData = this.data.order.length;
					$("#jJurnal").html(jData);
				},
				onAfterFilter: function () {
					var jData = this.data.order.length;
					$("#jJurnal").html(jData);
				},
				onLoadError: function (text, data, xhr) {
					var hasil = data.json();
					peringatanStatus(hasil.status_code, hasil.message);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						try {
							obj.index = i + 1;
						} catch (e) {

						}
					});

					var jData = this.data.order.length;
					$("#jJurnal").html(jData);
				}
			}

		},

		{
			view: "pager",
			id: "jurnalPager",
			template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jJurnal'>  </span></b> Data",
			size: 12,
			group: 5,
			animate: {
				direction: "left", type: "slide"
			}
		}
	]
};

var formJurnal = {
	view: "form",
	id: "formJurnal",
	borderless: true,
	elements: [
		{ view: "datepicker", label: "Tanggal dan Waktu", name: "tanggal", timepicker: true, required: true, invalidMessage: "Belum dipilih", inputWidth: 230, format: "%d-%m-%Y %H:%i:%s", stringResult: true },
		{ view: "textarea", label: "Konten Jurnal", name: "konten", required: true, placeholder: "Isi konten bimbingan", invalidMessage: "Konten belum diisi", height: 100 },
		{
			cols: [
				{ template: " ", borderless: true },
				{ view: "button", id: "simpanJurnal", label: "Simpan", type: "form", width: 120, borderless: true },
				{ template: " ", borderless: true }
			]
		}
	],
	elementsConfig: {
		labelPosition: "top",
	}
};

var menu_mahasiswa = [
	{ id: "biodata_mahasiswa", icon: "users", value: "Biodata Mahasiswa" },
	{ id: "krs_mahasiswa", icon: "book", value: "KRS Mahasiswa" },
	{ id: "khs_mahasiswa", icon: "book", value: "Histori Nilai" },
	{ id: "aktifitas_mahasiswa", icon: "table", value: "Aktifitas Perkuliahan" },
	{ id: "jurnal_bimbingan", icon: "table", value: "Jurnal Bimbingan" },
];

var masterMahasiswaDetail = {
	id: "masterMahasiswaDetail", type: "space", cols: [
		{
			id: "panelKiriMahasiswaDetail", header: "Akses Data", width: 180,
			body: { id: "menuMahasiswa", view: "list", select: true, scroll: false, data: menu_mahasiswa }
		},
		{
			rows: [
				{
					id: "kontenMahasiswaDetail", cells: [viewMahasiswaDetail, viewMahasiswaKRS, viewMahasiswaKHS, viewMahasiswaAktifitas, viewJurnalBimbingan]
				}
			]
		}
	]
};


/* HALAMAN AKTIFITAS BIMBINGAN */
var panelKiriPembimbing = {
	id: "panelKiriPembimbing", borderless: false, width: 150, rows: [
		{
			template: "Tahun Akademik",
			type: "header"
		},
		{
			id: "menuPembimbing",
			view: "list",
			select: true,
			scroll: true,
			url: "sopingi/semester/pilih/" + wSiaMhs.apiKey + "/" + Math.random()
		}
	]
};

var dosen_pembimbingRiwayat = {
	id: "dosen_pembimbingRiwayat",
	type: "space",
	rows: [
		{
			view: "toolbar", paddingY: 2,
			cols: [
				{ view: "button", id: "pembimbingRiwayatRefresh", label: "Refresh", type: "iconButton", icon: "refresh", width: 100 },
				{ template: "", borderless: true },
				{ view: "button", id: "pembimbingRiwayatCetak", label: "Cetak", type: "iconButton", icon: "file-pdf-o", width: 100 },
				{ view: "button", id: "pembimbingRiwayatTambah", label: "Tambah", type: "iconButton", icon: "plus", width: 100 },
			]
		},
		{
			view: "datatable",
			select: true,
			id: "pembimbingRiwayatDataTable",
			datafetch: 12,
			loadahead: 12,
			navigation: true,
			fixedRowHeight: false,
			rowLineHeight: 25,
			columns: [
				{ id: "index", header: "Per", width: 50 },
				{ id: "tanggal_id", header: "Tanggal", width: 150 },
				{
					id: "mhs_aktif", header: [
						{ text: "Aktifitas Kuliah", colspan: 4 },
						"Aktif"
					], width: 75
				},
				{ id: "mhs_nonaktif", header: ["", "Non Aktif"], width: 75 },
				{ id: "mhs_cuti", header: ["", "Cuti"], width: 75 },
				{ id: "mhs_keluar", header: ["", "Keluar/Lulus"], width: 90 },
				{ id: "kesimpulan", header: "Keterangan", fillspace: true },
				{ id: "detail", header: "Detail", width: 100, template: "<button class='btnDetail btnTransparant'><i class='webix_icon fa-info'></i> Detail</button>" },
				{ id: "hapus", header: "Aksi", width: 100, template: "<button class='btnHapus btnTransparant'><i class='webix_icon fa-trash'></i> Hapus</button>" },
			],
			pager: "pembimbingRiwayatPager",
			hover: "tableHover",
			on: {
				onBeforeLoad: function () {
					this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
				},
				onAfterLoad: function () {

					this.hideOverlay();
					var jData = this.data.order.length;
					$("#jPengajaran").html(jData);
				},
				onAfterFilter: function () {
					var jData = this.data.order.length;
					$("#jPengajaran").html(jData);
				},
				onLoadError: function (text, data, xhr) {
					var hasil = data.json();
					peringatanStatus(hasil.status_code, hasil.message);
				},
				"data->onStoreUpdated": function () {
					this.data.each(function (obj, i) {
						try {
							obj.index = i + 1;
						} catch (e) {

						}
					});

					var jData = this.data.order.length;
					$("#jPengajaran").html(jData);
				}
			}

		},

		{
			view: "pager",
			id: "pembimbingRiwayatPager",
			template: "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jPengajaran'>  </span></b> Data",
			size: 12,
			group: 5,
			animate: {
				direction: "left", type: "slide"
			}
		}
	]
}

var pembimbingDetail = new WebixView({
	config: {
		id: "pembimbingDetail",
		type: "clean",
		rows: [
			{
				template: "Aktifitas Bimbingan", type: "header"
			},
			{
				cols: [
					panelKiriPembimbing, dosen_pembimbingRiwayat
				]
			}
		]
	}
});

var formPembimbing = {
	view: "form",
	id: "formPembimbing",
	borderless: true,
	elements: [
		{ view: "datepicker", label: "Tanggal", name: "tanggal", required: true, invalidMessage: "Belum dipilih", inputWidth: 180, format: "%d-%m-%Y", stringResult: true },
		{
			view: "fieldset", id: "fieldAktifitas", label: "Aktifitas Kuliah Mahasiswa (SAAT MODE TAMBAH: otomatis mengambil data kuliah mahasiswa di BAAK)", body: {
				cols: [
					{ view: "text", label: "Aktif", name: "mhs_aktif", required: true, attributes: { type: "number" } },
					{ view: "text", label: "Non Aktif", name: "mhs_nonaktif", required: true, attributes: { type: "number" } },
					{ view: "text", label: "Cuti", name: "mhs_cuti", required: true, attributes: { type: "number" } },
					{ view: "text", label: "Keluar/Lulus", name: "mhs_keluar", required: true, attributes: { type: "number" } },
				]
			}
		},
		{ view: "richtext", label: "Kondisi mahasiswa", name: "kondisi_mahasiswa", required: true, placeholder: "", invalidMessage: "Belum diisi", height: 200 },
		{ view: "richtext", label: "Mahasiswa butuh penanganan khusus", name: "penanganan_mahasiswa", required: true, placeholder: "", invalidMessage: "Belum diisi", height: 200 },
		{ view: "richtext", label: "Hasil dan kesimpulan bimbingan", name: "kesimpulan", required: true, placeholder: "", invalidMessage: "Belum diisi", height: 200 },
		{
			cols: [
				{ template: " ", borderless: true },
				{ view: "button", id: "simpanPembimbing", label: "Simpan", type: "form", width: 120, borderless: true },
				{ template: " ", borderless: true },
			]
		}
	],
	elementsConfig: {
		labelPosition: "top",
	}
};
