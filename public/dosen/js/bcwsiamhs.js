webix.ready(function(){
	var wSiaMhs = webix.storage.session.get('wSiaMhs');
	
	var menuBarJudul = {
		view:"toolbar",
		id:"toolbar",
		paddingY:0,
		paddingX:0,
		css:"headerAtas",
		height:35,
		type:"clean",
		elements:[
			{ view: "button",id:"tombolMenu", type: "icon", icon: "bars", hidden:true,
				width: 35, align: "left",css:"menuKiri", click: function(){
					$$("sideKiri").toggle()
				}
			},
			{ view: "label", label: "<img src='../gambar/logo.png' height='28'>", css:"headerAtas", width:200, borderless:true },
			{ css:"kampus",view:"template", id:"akunMahasiswa", data:{nm_ptk:wSiaMhs.nm_ptk,nidn:wSiaMhs.nidn} , template:"<b>#nm_ptk# - #nidn#</b>"},
			{ css:"kampus", view:"button", id: "keluarMenu", type:"icon", icon: "lock", width: 80, label:"Keluar", align:"right", type:"form",
				click: function(){
					keluarProses();
				}
			}
		]
	};
	
	var menu_kiri = [
		{id: "dashboard", icon: "dashboard", value:"Beranda"},
		{id: "akun", icon: "key", value: "Akun"},		
		{id: "pengajaran", icon: "table", value:"Pengajaran"},
		{id: "bimbinganakademik", icon: "book", value:"Bimbingan Akademik", data:[
            { id:"bimbinganakademikAktifitas",icon: "rss", value:"Aktifitas Bimbingan"},
            { id:"bimbinganakademikJurnal",icon: "comments", value:"Jurnal Bimbingan"},
        ]}		
	];
	
	var ui_wsiamhs ={
		id:"layout_utama",
		type:"clean", rows:[
			menuBarJudul,
			{ cols:[
					{ id:"sideKiri", view:"sidebar", data: menu_kiri,width:180, hidden:true, on:{
						onAfterSelect: function(id){
							bukaHalaman(id+".html");
						}
					  }
					},
					{
					  id:"halaman", template: "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>", css:"halaman"
					}
			]},
			
			{template:"SIAKAD | POLITEKNIK INDONUSA Surakarta", height:30, css:"footerBawah", borderless:true,autowidth:true,  align:"left"}
		]
	};
	
	//Buka Halaman
	bukaHalaman= function(id) {
		routes.navigate("/"+id, { trigger:true });
	}
	
	//Layout Utama
	var layout=new WebixView({
			config: ui_wsiamhs,
			el: ".app_wsiamhs"
	}).render();
	
	//Router
	var routes = new (Backbone.Router.extend({
		routes:{
			"":"index", 
			"aksesditolak":"ditolak",
			":hal":"hal"
		},
		index:function(){
			var wSiaMhs = webix.storage.session.get('wSiaMhs');
			if (wSiaMhs===null ||  wSiaMhs=="") {
				kembaliKeLogin();
			} 
			
			
			bukaHalaman("utama.html");

		},
		ditolak:function(){
			halaman = layout.root.getChildViews()[1].getChildViews()[1];
			aksesDitolak.el = halaman
			aksesDitolak.render();
			
			$$('sideKiri').hide();
			$$('tombolMenu').hide();
			
			webix.ui({
				view:"window",height:130, width:300, modal:true,
				head: { view:"toolbar", margin:-4, cols:[
						{ view:"icon", icon:"user-times"},
						{ view:"label", label: "Hak akses ditolak" },
						{ template:"" }
					  ]},
			        position:"center",
				body:{
					template:"<h2 align='center'>Maaf, Anda tidak diperkenankan<br>Mengakses SIAKAD</h2>"
				}
			}).show();
		},
		hal:function(id){
			//if (navigator.userAgent!="sopingi.com") {
				//routes.navigate("/aksesditolak", { trigger:true });
			//}  
			
			halaman = layout.root.getChildViews()[1].getChildViews()[1];
			
			if (id=="utama.html") {
				
				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs===null ||  wSiaMhs=="") {
					kembaliKeLogin();
				} 
				
				formUtama.el = halaman
				formUtama.render();
				
				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();
				
			} else if (id=="akun.html") {
				
				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs===null ||  wSiaMhs=="") {
					kembaliKeLogin();
				} 
				
				halamanAkun.el = halaman
				halamanAkun.render();
				
				$$('sideKiri').show();
				$$('tombolMenu').show();
				$$('sideKiri').unselectAll();
				
				$$("simpanAkun").attachEvent("onItemClick", simpanAkun);
				$$("updateFoto").attachEvent("onItemClick", updateFoto);
				
			} else if (id=="pengajaran.html") {
				
				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs===null ||  wSiaMhs=="") {
					kembaliKeLogin();
				} 
				
				pengajaranDetail.el = halaman
				pengajaranDetail.render();
				
				$$('sideKiri').show();
				$$('tombolMenu').show();
				
				$$("menuPengajaran").attachEvent("onAfterSelect", function (id) {
					try {
						$$("pengajaranRiwayatDataTable").clearAll();
						$$("pengajaranRiwayatDataTable").define("url","sopingi/kelas_kuliah/tampil/"+wSiaMhs.apiKey+"/"+id );
						$$("pengajaranRiwayatDataTable").refresh();
					} catch(e) {

					}
				});

				$$("pengajaranRiwayatRefresh").attachEvent("onItemClick", function(){
					var id = $$("menuPengajaran").getSelectedId(); 
					$$("pengajaranRiwayatDataTable").clearAll();
					$$("pengajaranRiwayatDataTable").define("url","sopingi/kelas_kuliah/tampil/"+wSiaMhs.apiKey+"/"+id );
					$$("pengajaranRiwayatDataTable").refresh();
				});

				webix.ui({
		            view:"window",
		            id:"winNilai",
		            width:screen.width-100,
				    height:600,
		            position:"center",
		            modal:true,
		            head:{ view:"toolbar", margin:-4, cols:[
							{ view:"label", label: "Mahasiswa Yang Mengisi KRS", id:"judulwinNilai" },
							{  view:"icon", icon:"close", click:"$$('winNilai').hide();"}
					]},
		            body:webix.copy(viewMahasiswaKelas)
		        });

				$$("pengajaranRiwayatDataTable").on_click.btnNilai=function(e, id){
			
					$$("pengajaranRiwayatDataTable").select(id);
					var data= $$("pengajaranRiwayatDataTable").getItem(id);
					var idTa = $$("menuPengajaran").getSelectedId();
					var tahun = idTa.substr(0,4);
					var smt = idTa.substr(4,1);
					if (smt%2==0) {
						var semester = tahun+"/"+(parseInt(tahun)+1)+" Genap";
					} else {
						var semester = tahun+"/"+(parseInt(tahun)+1)+" Ganjil";
					}

					var dataPanelKelas=[
						{"judul":"Program Studi:","konten":data.nm_lemb},
						{"judul":"Semester:","konten":semester},
						{"judul":"Mata Kuliah:","konten":data.kode_mk+"-"+data.nm_mk},
						{"judul":"Nama Kelas:","konten":data.nm_kls}
					];

					$$("panelKelasKuliah").clearAll();
					$$("panelKelasKuliah").define("data",dataPanelKelas);
					$$("panelKelasKuliah").refresh();

					//FORM PERSEN NILAI
					var formPersenNilai = {
						id_kls : data.id_kls,
						persen_absen: data.persen_absen,
						persen_tugas: data.persen_tugas,
						persen_uts: data.persen_uts,
						persen_uas: data.persen_uas,
						persen_total: (parseFloat(data.persen_absen)||0) + (parseFloat(data.persen_tugas)||0) + (parseFloat(data.persen_uts)||0) + (parseFloat(data.persen_uas)||0),
					};

					//dimatkan - direvisi
					//$$("formPersenNilai").setValues(formPersenNilai);
					
					
					webix.ajax().get("sopingi/sms/bobotnilai/"+wSiaMhs.apiKey+"/"+data.id_sms,{},{
						success: function(text, xml, xhr){
							proses_hide();
							var bobot=JSON.parse(text);
							abobot = Object.values(bobot);					

							var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
							$$("dataTableMahasiswaKelas").clearAll();
							$$("dataTableMahasiswaKelas").load("sopingi/nilai/tampil/"+wSiaMhs.apiKey+"/"+data.xid_kls);
							$$("viewMahasiswaKelas").show();
							  
							return false;
						},
						error:function(text, data, xhr){
							proses_hide();
				        	webix.alert({
								    title: "Gagal Koneksi",
								    text: "Tidak dapat terhubung dengan internet!",
								    type:"alert-error"
							});
						}    
						    
					});


					$$("winNilai").show();
				}	
				
				/*dimatikan - direvisi
				$$('simpanPersenNilai').attachEvent("onItemClick", function(){
					if ($$("formPersenNilai").validate()) {
						var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
						var dataKirim = $$("formPersenNilai").getValues();				
						dataKirim.aksi="persennilai";
						dataKirim = JSON.stringify(dataKirim);
						proses_tampil();
						webix.ajax().post("sopingi/kelas_kuliah/aksi/"+wSiaMhs.apiKey+"/"+data.xid_kls,dataKirim,{
							success: function(text, xml, xhr){
								proses_hide();
								var hasil = xml.json();
								if (hasil.berhasil) {
									webix.message(hasil.pesan);
								} else {
									webix.alert({
									    title: "Gagal Simpan",
									    text: "Gagal simpan persen nilai",
									    type:"alert-error"
									});
								}
								
							},
							error:function(text, data, xhr){
								proses_hide();
					        	webix.alert({
									    title: "Gagal Koneksi",
									    text: "Tidak dapat terhubung dengan internet!",
									    type:"alert-error"
								});
							}    
							    
						});
					} else {
						webix.alert({
							    title: "Kesalahan",
							    text: "Persen Nilai Tidak Valid",
							    type:"alert-error"
						});
					}
				});
				*/

				$$("refreshMhsKelas").attachEvent("onItemClick",function(){
					var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
					$$("dataTableMahasiswaKelas").clearAll();
					$$("dataTableMahasiswaKelas").load("sopingi/nilai/tampil/"+wSiaMhs.apiKey+"/"+data.xid_kls);
				});

				$$("unduhKhsXls").attachEvent("onItemClick", function(){
					var panel =  $$("panelKelasKuliah").serialize();					
					webix.toExcel($$("dataTableMahasiswaKelas"),{
					    filename: panel[2].konten,
					    name: panel[0].konten+" "+panel[3].konten,
					    spans:true,
					    styles:true
					});
				});

				$$("simpanKhsKelas").attachEvent("onItemClick",function(){			
					var itemNilai = new Array();
					$$("dataTableMahasiswaKelas").eachRow( function (baris) {
						var id_nilai = $$("dataTableMahasiswaKelas").getItem(baris).id_nilai;
						var nilai_absen = $$("dataTableMahasiswaKelas").getItem(baris).nilai_absen;
						var nilai_tugas = $$("dataTableMahasiswaKelas").getItem(baris).nilai_tugas;
						var nilai_uts = $$("dataTableMahasiswaKelas").getItem(baris).nilai_uts;
						var nilai_uas = $$("dataTableMahasiswaKelas").getItem(baris).nilai_uas;
						var nilai_angka = $$("dataTableMahasiswaKelas").getItem(baris).nilai_angka;
						var nilai_huruf = $$("dataTableMahasiswaKelas").getItem(baris).nilai_huruf;
						var nilai_indeks = $$("dataTableMahasiswaKelas").getItem(baris).nilai_indeks;
						var nilai_tampil = $$("dataTableMahasiswaKelas").getItem(baris).nilai_tampil;

						if (nilai_tampil!=1 && nilai_tampil!=3) {
							itemNilai.push({
								'id_nilai':id_nilai,
								'nilai_absen':nilai_absen.toString(),
								'nilai_tugas':nilai_tugas.toString(),
								'nilai_uts':nilai_uts.toString(),
								'nilai_uas':nilai_uas.toString(),
								'nilai_angka':nilai_angka.toString(),
								'nilai_huruf':nilai_huruf.toString(),
								'nilai_indeks':nilai_indeks.toString()
							});
						}

					});
					
					var nilai = JSON.stringify({"nilai":itemNilai,"aksi":"ubah"});

					var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
					webix.ajax().post("sopingi/nilai/ubah/"+wSiaMhs.apiKey+"/"+data.xid_kls, nilai,{
						success: function(response, data, xhr){
							proses_hide() ;
							var hasil=JSON.parse(response);
							if (hasil.berhasil) {
								webix.message(hasil.pesan);					
								var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
								$$("dataTableMahasiswaKelas").clearAll();
								$$("dataTableMahasiswaKelas").load("sopingi/nilai/tampil/"+wSiaMhs.apiKey+"/"+data.xid_kls);				
							} else {
								peringatan("Kesalahan!",hasil.pesan);
							}
						},
						error:function(text, data, xhr){
							proses_hide();
					        	webix.alert({
								    title: "Gagal Koneksi",
								    text: "Tidak dapat terhubung dengan server/jaringan!",
								    type:"alert-error"
								})
							}
					});
					 		
				}); //simpanKhsKelas

				$$("kirimKhsKelas").attachEvent("onItemClick",function(){	

					var itemNilai = new Array();
					var draft = true;
					var lengkap = true;
					$$("dataTableMahasiswaKelas").eachRow( function (baris) {
						var id_nilai = $$("dataTableMahasiswaKelas").getItem(baris).id_nilai;
						var nilai_absen = $$("dataTableMahasiswaKelas").getItem(baris).nilai_absen;
						var nilai_tugas = $$("dataTableMahasiswaKelas").getItem(baris).nilai_tugas;
						var nilai_uts = $$("dataTableMahasiswaKelas").getItem(baris).nilai_uts;
						var nilai_uas = $$("dataTableMahasiswaKelas").getItem(baris).nilai_uas;
						var nilai_tampil = $$("dataTableMahasiswaKelas").getItem(baris).nilai_tampil;

						if (nilai_tampil==0) {
							draft=false;
						}

						if (nilai_absen=="" || nilai_tugas=="" || nilai_uts=="" || nilai_uas=="") {
							lengkap = false;
						} else if (nilai_tampil==2) {
							itemNilai.push({
								'id_nilai':id_nilai
							});
						}

						
						
					});

					if (!lengkap || !draft) {
						webix.alert({
						    title: "Informasi",
						    text: "Harap mengisi semua Nilai Mahasiswa<br>Nilai Absen, Tugas, UTS dan UAS<br> dan Simpan Draft Nilai terlebih dahulu",
						    type:"alert-error"
						});
						return;
					}

					webix.confirm({
					    title:"Konfirmasi",
					    ok:"Kirim", 
					    cancel:"Batal",
					    text:"Yakin mengirim Nilai ke BAAK ?<br>PASTIKAN SUDAH MENGISI NILAI SEMUA MAHASISWA<br>Setelah mengirim nilai, maka nilai tidak bisa dirubah",
					    callback: function(result){
							   if (result) {
									
									var nilai = JSON.stringify({"nilai":itemNilai,"aksi":"kirim"});

									var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
									webix.ajax().post("sopingi/nilai/kirim/"+wSiaMhs.apiKey+"/"+data.xid_kls, nilai,{
										success: function(response, data, xhr){
											proses_hide() ;
											var hasil=JSON.parse(response);
											if (hasil.berhasil) {
												webix.message(hasil.pesan);					
												var data= $$("pengajaranRiwayatDataTable").getSelectedItem();
												$$("dataTableMahasiswaKelas").clearAll();
												$$("dataTableMahasiswaKelas").load("sopingi/nilai/tampil/"+wSiaMhs.apiKey+"/"+data.xid_kls);				
											} else {
												peringatan("Kesalahan!",hasil.pesan);
											}
										},
										error:function(text, data, xhr){
											proses_hide();
									        	webix.alert({
												    title: "Gagal Koneksi",
												    text: "Tidak dapat terhubung dengan server/jaringan!",
												    type:"alert-error"
												})
											}
									});
							   }
						}
					});		
					
					 		
				});//kirimKhsKelas

			} else if (id=="bimbinganakademikJurnal.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs===null ||  wSiaMhs=="") {
					kembaliKeLogin();
				} 

				viewBimbinganAkademik.el = halaman;
				viewBimbinganAkademik.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();

				$$("menuBimbinganAkademik").attachEvent("onAfterSelect", function (id) {
					try {
						$$("bimbinganAkademikRiwayatDataTable").clearAll();
						$$("bimbinganAkademikRiwayatDataTable").define("url","sopingi/mahasiswa/tampil/"+wSiaMhs.apiKey+"/"+id );
						$$("bimbinganAkademikRiwayatDataTable").refresh();
					} catch(e) {

					}
				});

				$$("bimbinganAkademikRiwayatRefresh").attachEvent("onItemClick", function(){
					var id = $$("menuBimbinganAkademik").getSelectedId(); 
					if (id) {
						$$("bimbinganAkademikRiwayatDataTable").clearAll();
						$$("bimbinganAkademikRiwayatDataTable").define("url","sopingi/mahasiswa/tampil/"+wSiaMhs.apiKey+"/"+id );
						$$("bimbinganAkademikRiwayatDataTable").refresh();
					} 
				});

				$$("bimbinganAkademikRiwayatCetak").attachEvent("onItemClick", function(){
					var id = $$("menuBimbinganAkademik").getSelectedId(); 
					if (id) {
						window.open(wSiaMhs.domain+"/dosen/sopingi/presensi_pdf/download/"+wSiaMhs.apiKey+"/"+id,"_blank");
					} else {
						webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
					}
				});

				webix.ui({
				    view:"window",
				    id:"mahasiswaWin",
				    move:true,
				    width:screen.width-100,
				    height:600,
		            position:"center",				    
				    modal:true,
				    head:{
						view:"toolbar", margin:-4, cols:[
							{view:"label", label: "Detail Mahasiswa", id:"mahasiswaJudulWin" },
							{ view:"icon", icon:"close", click:"$$('mahasiswaWin').hide();"}
						]
					},
				    body:webix.copy(masterMahasiswaDetail)
				});

				$$("bimbinganAkademikRiwayatDataTable").on_click.btnDetail=function(e, id){

					$$("bimbinganAkademikRiwayatDataTable").select(id);
					var data= $$("bimbinganAkademikRiwayatDataTable").getItem(id);
					//console.log(data);
					$$("menuMahasiswa").select("biodata_mahasiswa");
					$$("formMahasiswaDetail").setValues(data);
					$$("mahasiswaJudulWin").setValue("Detail Mahasiswa: " + data.nipd + " - " + data.nm_pd+ " ( " + data.kelas + " )" + " - " + data.vid_jns_daftar);
					$$("mahasiswaWin").show();
					$$("kontenMahasiswaDetail").setValue("viewMahasiswaDetail");
					$$("viewMahasiswaDetail").show();
				}

				//Panel Kiri Mahasiswa
				$$("menuMahasiswa").attachEvent("onItemClick", function(id){
					if (id=="biodata_mahasiswa"){
						$$("viewMahasiswaDetail").show();
					} else if (id=="krs_mahasiswa"){
						$$("viewMahasiswaKRS").show();
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
						$$("dataTableKrs").clearAll();
						$$("dataTableKrs").load("sopingi/pa/krs/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
					} else if (id=="khs_mahasiswa"){
						$$("viewMahasiswaKHS").show();
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
					    $$("dataTableKhs").clearAll();
					    $$("dataTableKhs").load("sopingi/pa/nilai/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
						
					} else if (id=="aktifitas_mahasiswa"){
						$$("viewMahasiswaAktifitas").show();
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
						$$("dataTableAktifitas").clearAll();
						$$("dataTableAktifitas").load("sopingi/pa/kuliah_mahasiswa/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
					} else if (id=="jurnal_bimbingan"){
						$$("viewJurnalBimbingan").show();
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
						$$("jurnalDataTable").clearAll();
						$$("jurnalDataTable").load("sopingi/pa/jurnal/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
					}
						
				});

				//KRS

				webix.ui({
				    view:"window",
				    id:"winKelasKuliah",
				    width:750,
				    position:"center",
				    modal:true,
				    head:{
					view:"toolbar", margin:-4, cols:[
						{
							view:"label", label: "Tambah Kelas Mata Kuliah", id:"judulWinKelasKuliah"
						},
						{ 
							view:"icon", icon:"close",
							click:"$$('winKelasKuliah').hide();"}
						]
					},
				    body:webix.copy(formKRS)
				});

				$$("refreshKRS").attachEvent("onItemClick", function(id, e){
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
					$$("dataTableKrs").clearAll();
					$$("dataTableKrs").load("sopingi/pa/krs/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
				});

				$$("tambahKRS").attachEvent("onItemClick", function(){			
					var data=[];
					var jSKS=0;
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
					var kelas = dataMhs.kelas;
					var id_sms = dataMhs.id_sms;

					$$("dataTableKrs").data.each(function(dataKrs){
						 data.push(dataKrs.vid_kls);
						 jSKS=jSKS+parseInt(dataKrs.vsks_mk);
					});
					
					if (jSKS<24) {
						var dataSudahKrs= new Object();
						dataSudahKrs.aksi="kelas_kuliah";
						dataSudahKrs.data=data;
						dataSudahKrs.kelas=kelas;
						dataSudahKrs.id_sms=id_sms;
						var dataKirim = JSON.stringify(dataSudahKrs);
						webix.ajax().post("sopingi/pa/kelas_kuliah/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd,dataKirim,
							function(text, xml, xhr){
						    	var dataBelumKrs=JSON.parse(text);
						    	$$("dataTableKelasPerkuliahan").clearAll();
						    	$$("dataTableKelasPerkuliahan").define("data",dataBelumKrs);
						    	$$("dataTableKelasPerkuliahan").refresh();
						    	$$("winKelasKuliah").show();
								$$("judulWinKelasKuliah").setValue("Tambah Kelas Mata Kuliah");
							}
						);
					} else {
						webix.alert({
							    title: "Informasi",
							    text: "Tidak dapat menambah SKS lagi..<br>(Maksimal 24 SKS)",
							    type:"alert-error"
						})
					}	
					
				});

				$$("simpanKRS").attachEvent("onItemClick", function () {
					if ($$('formKRS').validate()) {
						
						var dataKrs=[];
						var jSKSbaru=0;
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();

						$$("dataTableKelasPerkuliahan").data.each(function(dataKelas){
							if (dataKelas.ambilKelas) {
								dataKrs.push(dataKelas.xid_kls);
								jSKSbaru+=parseInt(dataKelas.vsks_mk);
							}
						});
						
						var jSKS=0;
						$$("dataTableKrs").data.each(function(dataSudahKrs){
							 jSKS+=parseInt(dataSudahKrs.vsks_mk);
						});
						
						var totalSKS=jSKS+jSKSbaru;
						
						if (totalSKS<=24) {
							
							$$("formKRS").setValues({ kelas:dataKrs, aksi:"tambah_krs" },true);
							var dataKirim = JSON.stringify($$("formKRS").getValues());
							
							webix.ajax().post("sopingi/pa/tambah_krs/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd, dataKirim,
								function(response, data, xhr){
									
									var hasil=JSON.parse(response);
									if (hasil.berhasil) {
										webix.message(hasil.pesan);					
										var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
										$$("dataTableKrs").clearAll();
										$$("dataTableKrs").load("sopingi/pa/krs/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
										$$("formKRS").refresh(); 
										$$('winKelasKuliah').hide();
									} else {
										webix.alert({
										    title: "Gagal Simpan",
										    text: hasil.pesan,
										    type:"alert-error"
										});
									}
								
								}
							);
							
					     } else {
							webix.alert({
							    title: "Informasi",
							    text: "Jumlah SKS melebihi 24 SKS. Silahkan cek kembali mata kuliah yang diambil",
							    type:"alert-error"
							})
						 }		
						 		
					} else {
						
						webix.alert({
						    title: "Kesalahan",
						    text: "Form tidak valid",
						    type:"alert-error"
						});
					}

				});
				
				$$("hapusKRS").attachEvent("onItemClick", function(){
					if ($$("dataTableKrs").getSelectedId()!=null) {
						var data= $$("dataTableKrs").getSelectedItem();
						
						if (data.nilai_huruf!="") {
							webix.alert({
							    title: "Informasi",
							    text: "Mata kuliah sudah mendapatkan nilai: "+data.nilai_huruf+"<br>Tidak diperbolehkan dihapus",
							    type:"alert-warning"
							});

						} else if (data.id_kls!="") {
							webix.alert({
							    title: "Informasi",
							    text: "Kelas Perkuliahan ini tidak dapat dihapus",
							    type:"alert-warning"
							});

						} else {
							var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
							webix.confirm({
						        title:"Konfirmasi",
						        ok:"Ya", 
						        cancel:"Tidak",
						        text:"Yakin akan menghapus data yang dipilih ?",
						        callback:function(jwb){
									if (jwb) {
										data.aksi="hapus_krs";
										var dataKirim = JSON.stringify(data);
										webix.ajax().post("sopingi/pa/hapus_krs/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd, dataKirim,
											function(response, data, xhr){
												
												var hasil=JSON.parse(response);
												if (hasil.berhasil) {
													webix.message(hasil.pesan);
													var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
													$$("dataTableKrs").clearAll();
													$$("dataTableKrs").load("sopingi/pa/krs/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
												} else {
													webix.alert({
													    title: "Gagal Hapus",
													    text: hasil.pesan,
													    type:"alert-error"
													});
												}

											}
										);
									} 
								}
							});

						}

					} else {
						
						webix.alert({
						    title: "Informasi",
						    text: "Belum ada data yang dipilih",
						    type:"alert-warning"
						});
					}
					
				});

				$$("refreshKhs").attachEvent("onItemClick", function(id, e){
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
					$$("dataTableKhs").clearAll();
					$$("dataTableKhs").load("sopingi/pa/nilai/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
				});

				$$("excelKhs").attachEvent("onItemClick", function(id, e){
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();				
					webix.toExcel($$("dataTableKhs"),{
					    filename: "Histori_Nilai_"+dataMhs.nipd,
					    name: dataMhs.nipd+"-"+dataMhs.nm_pd,
					    spans:true,
					    styles:true,
					    footer:true
					});
				});

				$$("refreshAktifitas").attachEvent("onItemClick", function(id, e){
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
					$$("dataTableAktifitas").clearAll();
					$$("dataTableAktifitas").load("sopingi/pa/kuliah_mahasiswa/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
				});

				$$("excelAktifitas").attachEvent("onItemClick", function(id, e){
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();				
					webix.toExcel($$("dataTableAktifitas"),{
					    filename: "Aktifitas_Kuliah_"+dataMhs.nipd,
					    name: dataMhs.nipd+"-"+dataMhs.nm_pd,
					    spans:true,
					    styles:true,
					    footer:true
					});
				});

				//Jurnal Bimbingan
				webix.ui({
				    view:"window",
				    id:"winJurnal",
				    width:750,
				    position:"center",
				    modal:true,
				    head:{
						view:"toolbar", margin:-4, cols:[
							{
								view:"label", label: "Tambah Jurnal Bimbingan Akademik", id:"judulWinJurnal"
							},
							{ 
								view:"icon", icon:"close",
								click:"$$('winJurnal').hide();"
							}
						]
					},
				    body:webix.copy(formJurnal)
				});

				$$("refreshJurnal").attachEvent("onItemClick", function(id, e){
					var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
					$$("jurnalDataTable").clearAll();
					$$("jurnalDataTable").load("sopingi/pa/jurnal/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
				});

				$$("tambahJurnal").attachEvent("onItemClick", function(){			
					
					$$("winJurnal").show();
					$$("formJurnal").clear();
					$$("formJurnal").setValues({"aksi":"tambah_jurnal"});
					$$("judulWinJurnal").setValue("Tambah Jurnal Bimbingan Akademik");
					
				});

				$$("ubahJurnal").attachEvent("onItemClick", function(){			
					if ($$("jurnalDataTable").getSelectedId()!=null) {
						var data= $$("jurnalDataTable").getSelectedItem();
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

				$$("simpanJurnal").attachEvent("onItemClick", function () {
					if ($$('formJurnal').validate()) {
						
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();

						var dataJurnal = $$("formJurnal").getValues();
						var dataKirim = JSON.stringify($$("formJurnal").getValues());
						$$('formJurnal').disable();
						webix.ajax().post("sopingi/pa/jurnal/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd, dataKirim,
							function(response, data, xhr){
								$$('formJurnal').enable();
								var hasil=JSON.parse(response);
								if (hasil.berhasil) {
									webix.message(hasil.pesan);					
									var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
									$$("jurnalDataTable").clearAll();
									$$("jurnalDataTable").load("sopingi/pa/jurnal/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
									$$("formJurnal").clear(); 
									$$('winJurnal').hide();
								} else {
									webix.alert({
									    title: "Gagal Simpan",
									    text: hasil.pesan,
									    type:"alert-error"
									});
								}
							
							}
						);
						 		
					} else {
						webix.alert({
						    title: "Kesalahan",
						    text: "Form tidak valid",
						    type:"alert-error"
						});
					}

				});
				
				$$("hapusJurnal").attachEvent("onItemClick", function(){
					if ($$("jurnalDataTable").getSelectedId()!=null) {
						var data= $$("jurnalDataTable").getSelectedItem();
						var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
						webix.confirm({
					        title:"Konfirmasi",
					        ok:"Ya", 
					        cancel:"Tidak",
					        text:"Yakin akan menghapus data yang dipilih ?",
					        callback:function(jwb){
								if (jwb) {
									data.aksi="hapus_jurnal";
									var dataKirim = JSON.stringify(data);
									webix.ajax().post("sopingi/pa/hapus_jurnal/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd, dataKirim,
										function(response, data, xhr){
											
											var hasil=JSON.parse(response);
											if (hasil.berhasil) {
												webix.message(hasil.pesan);
												var dataMhs= $$("bimbinganAkademikRiwayatDataTable").getSelectedItem();
												$$("jurnalDataTable").clearAll();
												$$("jurnalDataTable").load("sopingi/pa/jurnal/"+wSiaMhs.apiKey+"/"+dataMhs.xid_reg_pd);
											} else {
												webix.alert({
												    title: "Gagal Hapus",
												    text: hasil.pesan,
												    type:"alert-error"
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
						    type:"alert-warning"
						});
					}
					
				});

				//chat
				webix.ui({
				  view: "window",
				  id:"winPesan",
				  head: {
				  	view:"toolbar", margin:-4, cols:[
						{
							view:"label", label: "Pesan Bimbingan Akademik", id:"judulWinPesan"
						},
						{ 
							view:"icon", icon:"trash", hidden:true, id:"hapusPesan",
							click:function(){
								var data= $$("pesanList").getSelectedItem();
								webix.confirm({
							        title:"Konfirmasi",
							        ok:"Ya", 
							        cancel:"Tidak",
							        text:"Yakin akan menghapus pesan yang dipilih ?",
							        callback:function(jwb){
										if (jwb) {
											$$('pesanList').unselectAll();
											$$("hapusPesan").hide();
											data.aksi="hapus_pesan";
											var dataKirim = JSON.stringify(data);
											webix.ajax().post("sopingi/pa/hapus_pesan/"+wSiaMhs.apiKey+"/"+Math.random(), dataKirim,
												function(response, data, xhr){
													var hasil=JSON.parse(response);
													if (hasil.berhasil) {
														webix.message(hasil.pesan);
														$$("pesanList").remove(hasil.id); 
													} else {
														webix.alert({
														    title: "Gagal Hapus",
														    text: hasil.pesan,
														    type:"alert-error"
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
							width:20, template:""
						},
						{ 
							view:"icon", icon:"close",
							click:"$$('winPesan').hide();"
						}
					]
				  },
				  position: "center",
				  width: 500,
				  height: 500,
				  modal:true,
				  body: {
				  	
				      borderless: true,
				      rows:[
				       
				        {
				          view: "list",
				          id: "pesanList",
				          css: "char-list",
				          select:true,
				          type: {
				          	height: "auto"
				          },
				          
				          template: function(item){
				            var css = item.author== "dosen"? "1":"2";
				            return "<div class='pesan msg"+css+"'><span class='waktu'>"+item.author+" | "+item.waktu+"</span>"+item.text+"</div>";
				          }
				        },
				        {
				          height:60,
				          cols:[
				            {view: "textarea", id: "pesan", required:true},
				            {view: "text", id: "id_jurnal", required:true, hidden:true},
				            {width: 60, rows:[
				              {
				                view: "button", label: "Kirim", height:60, click: function(){
				                	$$('pesanList').unselectAll();
				                	$$("hapusPesan").hide();
				                	var text = $$("pesan").getValue();
				                	var id = $$("id_jurnal").getValue();
				                    text = text.replace(/\n/g,"<br/>");
				                    if(text && id) {
				                      var data = {"aksi":"kirim_pesan","id_jurnal":id,"pesan":text};
				                      var dataKirim = JSON.stringify(data);
				                      webix.ajax().post("sopingi/pa/kirim_pesan/"+wSiaMhs.apiKey+"/"+Math.random(), dataKirim,
										function(response, data, xhr){
											$$("pesan").setValue("");
											var hasil=JSON.parse(response);
											if (hasil.berhasil) {
												webix.message(hasil.pesan);
												$$("pesanList").add({text: hasil.text, waktu: hasil.waktu, author: hasil.author});
												$$('pesanList').showItem($$('pesanList').getLastId())
											} else {
												webix.alert({
												    title: "Gagal Kirim",
												    text: hasil.pesan,
												    type:"alert-error"
												});
											}

										}
									);

				                    }
				                }
				              },
				              {}
				            ]}
				            
				          ]
				        }  
				    ]
				  }
				});

				$$("jurnalDataTable").on_click.btnChat=function(e, id){
					$$("jurnalDataTable").select(id);
					var data= $$("jurnalDataTable").getItem(id);
					$$('id_jurnal').setValue(data.id);	
					$$("pesanList").clearAll();
					$$('pesanList').load("sopingi/pa/pesan/"+wSiaMhs.apiKey+"/"+data.id);
					$$('winPesan').show();

				};

				$$("pesanList").attachEvent("onAfterLoad", function(){
				   $$('pesanList').showItem($$('pesanList').getLastId())
				});

				$$("pesanList").attachEvent("onItemClick", function(id, e, node){
				    $$("hapusPesan").show();
				});


			} else if (id=="bimbinganakademikAktifitas.html") {

				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs===null ||  wSiaMhs=="") {
					kembaliKeLogin();
				} 


				pembimbingDetail.el = halaman
				pembimbingDetail.render();

				$$('sideKiri').show();
				$$('tombolMenu').show();

				webix.ui({
				    view:"window",
				    id:"winPembimbing",
				    width:750,
				    position:"center",
				    modal:true,
				    head:{
						view:"toolbar", margin:-4, cols:[
							{
								view:"label", label: "Tambah Aktifitas Bimbingan", id:"judulWinPembimbing"
							},
							{ 
								view:"icon", icon:"close",
								click:"$$('winPembimbing').hide();"
							}
						]
					},
				    body:webix.copy(formPembimbing)
				});

				$$("menuPembimbing").attachEvent("onAfterSelect", function (id) {
					try {
						var id = $$("menuPembimbing").getSelectedId(); 
						$$("pembimbingRiwayatDataTable").clearAll();
						$$("pembimbingRiwayatDataTable").load("sopingi/pa_aktifitas/tampil/"+wSiaMhs.apiKey+"/"+id);
					} catch(e) {

					}
				});

				$$("pembimbingRiwayatRefresh").attachEvent("onItemClick", function(){
					var id = $$("menuPembimbing").getSelectedId(); 
					if (id) {
						$$("pembimbingRiwayatDataTable").clearAll();
						$$("pembimbingRiwayatDataTable").load("sopingi/pa_aktifitas/tampil/"+wSiaMhs.apiKey+"/"+id);
					}
				});

				$$("pembimbingRiwayatCetak").attachEvent("onItemClick", function(){
					var id = $$("menuPembimbing").getSelectedId(); 
					if (id) {
						window.open(wSiaMhs.domain+"/dosen/sopingi/aktifitas_bimbingan_pdf/download/"+wSiaMhs.apiKey+"/"+id,"_blank");
					} else {
						webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
					}
				});

				$$("pembimbingRiwayatTambah").attachEvent("onItemClick", function(){
					var id = $$("menuPembimbing").getSelectedId(); 
					if (id) {		
						$$("fieldAktifitas").define({label:"Aktifitas Kuliah Mahasiswa (SAAT MODE TAMBAH: otomatis mengambil data kuliah mahasiswa di BAAK)"});
						
						$$("winPembimbing").show();
						$$("formPembimbing").clear();
						$$("formPembimbing").load("sopingi/pa_aktifitas/aktifitas_mahasiswa/"+wSiaMhs.apiKey+"/"+id);
						$$("judulWinPembimbing").setValue("Tambah Aktifitas Bimbingan");
					} else {
						webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
					}
				});
				
				$$("simpanPembimbing").attachEvent("onItemClick", simpanPembimbing);

				$$("pembimbingRiwayatDataTable").on_click.btnDetail=function(e, id){
					$$("pembimbingRiwayatDataTable").select(id);
					var data= $$("pembimbingRiwayatDataTable").getItem(id);
					data.aksi="ubah";
					$$("fieldAktifitas").define({label:"Aktifitas Kuliah Mahasiswa"});
					
					$$("formPembimbing").setValues(data);
					$$("judulWinPembimbing").setValue("Ubah Aktifitas Bimbingan");
					$$("winPembimbing").show();
				};

				$$("pembimbingRiwayatDataTable").on_click.btnHapus=function(e, id){
					$$("pembimbingRiwayatDataTable").select(id);
					var data= $$("pembimbingRiwayatDataTable").getItem(id);
					data.aksi="hapus";
					
					webix.confirm({
				        title:"Konfirmasi",
				        ok:"Ya", 
				        cancel:"Tidak",
				        text:"Yakin akan menghapus data yang dipilih ?",
				        callback:function(jwb){
							if (jwb) {
								var dataKirim = JSON.stringify(data);
								webix.ajax().post("sopingi/pa_aktifitas/hapus/"+wSiaMhs.apiKey+"/"+Math.random(), dataKirim,
									function(response, data, xhr){
										var hasil=JSON.parse(response);
										if (hasil.berhasil) {
											webix.message(hasil.pesan);
											var id = $$("menuPembimbing").getSelectedId(); 
											$$("pembimbingRiwayatDataTable").clearAll();
											$$("pembimbingRiwayatDataTable").load("sopingi/pa_aktifitas/tampil/"+wSiaMhs.apiKey+"/"+id);
										} else {
											webix.alert({
											    title: "Gagal Hapus",
											    text: hasil.pesan,
											    type:"alert-error"
											});
										}

									}
								);
							} 
						}
					});

				};
				

			} else {
				var wSiaMhs = webix.storage.session.get('wSiaMhs');
				if (wSiaMhs===null ||  wSiaMhs=="") {
					kembaliKeLogin();
				} 
				
				formUtama.el = halaman;
				formUtama.render();
				
				$$('sideKiri').show();
				$$('tombolMenu').show();
			}
			
		}
	}));
	
	Backbone.history.start();
	
	//adding ProgressBar functionality to layout
	webix.extend($$("layout_utama"), webix.ProgressBar);

	function proses_tampil(){
        $$("layout_utama").disable();
        $$("layout_utama").showProgress({
    		 type:"icon",
    		 icon:"cog"
        });
	}

	function proses_hide() {
	        $$("layout_utama").enable();
	        $$("layout_utama").hideProgress();
	}

	function keDashboard(){
		routes.navigate("/", { trigger:true });
	}

});