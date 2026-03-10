webix.ready(function () {
  var wSiaMhs = webix.storage.session.get("wSiaMhs");

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
        view: "button",
        id: "tombolMenu",
        type: "icon",
        icon: "bars",
        hidden: true,
        width: 35,
        align: "left",
        css: "menuKiri",
        click: function () {
          $$("sideKiri").toggle();
        },
      },
      {
        view: "label",
        label: "<img src='../gambar/logo.png' height='28'>",
        css: "headerAtas",
        width: 200,
        borderless: true,
      },
      {
        css: "kampus",
        view: "template",
        id: "akunMahasiswa",
        data: { nm_ptk: wSiaMhs.nm_ptk, nidn: wSiaMhs.nidn },
        template: "<b>#nm_ptk# - #nidn#</b>",
      },
      {
        css: "kampus",
        view: "button",
        id: "keluarMenu",
        type: "icon",
        icon: "lock",
        width: 80,
        label: "Keluar",
        align: "right",
        type: "form",
        click: function () {
          keluarProses();
        },
      },
    ],
  };

  var menu_kiri = [
    { id: "dashboard", icon: "dashboard", value: "Beranda" },
    { id: "akun", icon: "key", value: "Akun" },
    { id: "pengajaran", icon: "table", value: "Pengajaran" },
    {
      id: "bimbinganakademik",
      icon: "book",
      value: "Bimbingan Akademik",
      data: [
        {
          id: "bimbinganakademikAktifitas",
          icon: "rss",
          value: "Aktifitas Bimbingan",
        },
        {
          id: "bimbinganakademikJurnal",
          icon: "comments",
          value: "Jurnal Bimbingan",
        },
      ],
    },
    { id: "mbkm", icon: "briefcase", value: "MBKM" },
  ];

  var ui_wsiamhs = {
    id: "layout_utama",
    type: "clean",
    rows: [
      menuBarJudul,
      {
        cols: [
          {
            id: "sideKiri",
            view: "sidebar",
            data: menu_kiri,
            width: 180,
            hidden: true,
            on: {
              onAfterSelect: function (id) {
                bukaHalaman(id + ".html");
              },
            },
          },
          {
            id: "halaman",
            template:
              "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
            css: "halaman",
          },
        ],
      },

      {
        template: "SIAKAD | POLITEKNIK INDONUSA Surakarta",
        height: 30,
        css: "footerBawah",
        borderless: true,
        autowidth: true,
        align: "left",
      },
    ],
  };

  //Buka Halaman
  bukaHalaman = function (id) {
    routes.navigate("/" + id, { trigger: true });
  };

  //Layout Utama
  var layout = new WebixView({
    config: ui_wsiamhs,
    el: ".app_wsiamhs",
  }).render();

  //Router
  var routes = new (Backbone.Router.extend({
    routes: {
      "": "index",
      aksesditolak: "ditolak",
      ":hal": "hal",
    },
    index: function () {
      var wSiaMhs = webix.storage.session.get("wSiaMhs");
      if (wSiaMhs === null || wSiaMhs == "") {
        kembaliKeLogin();
      }

      bukaHalaman("utama.html");
    },
    ditolak: function () {
      halaman = layout.root.getChildViews()[1].getChildViews()[1];
      aksesDitolak.el = halaman;
      aksesDitolak.render();

      $$("sideKiri").hide();
      $$("tombolMenu").hide();

      webix
        .ui({
          view: "window",
          height: 130,
          width: 300,
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              { view: "button", type: "icon", icon: "user-times" },
              { view: "label", label: "Hak akses ditolak" },
              { template: "" },
            ],
          },
          position: "center",
          body: {
            template:
              "<h2 align='center'>Maaf, Anda tidak diperkenankan<br>Mengakses SIAKAD</h2>",
          },
        })
        .show();
    },
    hal: function (id) {
      //if (navigator.userAgent!="sopingi.com") {
      //routes.navigate("/aksesditolak", { trigger:true });
      //}

      halaman = layout.root.getChildViews()[1].getChildViews()[1];

      if (id == "utama.html") {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        formUtama.el = halaman;
        formUtama.render();

        $$("sideKiri").show();
        $$("tombolMenu").show();
        $$("sideKiri").unselectAll();
      } else if (id == "akun.html") {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        halamanAkun.el = halaman;
        halamanAkun.render();

        $$("sideKiri").show();
        $$("tombolMenu").show();
        $$("sideKiri").unselectAll();

        $$("simpanAkun").attachEvent("onItemClick", simpanAkun);
        $$("updateFoto").attachEvent("onItemClick", updateFoto);
      } else if (id == "pengajaran.html") {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        pengajaranDetail.el = halaman;
        pengajaranDetail.render();

        $$("sideKiri").show();
        $$("tombolMenu").show();

        $$("menuPengajaran").attachEvent("onAfterSelect", function (id) {
          try {
            $$("pengajaranRiwayatDataTable").clearAll();
            $$("pengajaranRiwayatDataTable").define(
              "url",
              "sopingi/kelas_kuliah/tampil/" + wSiaMhs.apiKey + "/" + id,
            );
            $$("pengajaranRiwayatDataTable").refresh();
          } catch (e) {}
        });

        $$("pengajaranRiwayatRefresh").attachEvent("onItemClick", function () {
          var id = $$("menuPengajaran").getSelectedId();
          $$("pengajaranRiwayatDataTable").clearAll();
          $$("pengajaranRiwayatDataTable").define(
            "url",
            "sopingi/kelas_kuliah/tampil/" + wSiaMhs.apiKey + "/" + id,
          );
          $$("pengajaranRiwayatDataTable").refresh();
        });

        webix.ui({
          view: "window",
          id: "winNilai",
          width: screen.width - 100,
          height: 600,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Mahasiswa Yang Mengisi KRS",
                id: "judulwinNilai",
              },
              {
                view: "button",
                type: "icon",
                icon: "close",
                click: "$$('winNilai').hide();",
              },
            ],
          },
          body: webix.copy(viewMahasiswaKelas),
        });

        $$("pengajaranRiwayatDataTable").on_click.btnNilai = function (e, id) {
          $$("pengajaranRiwayatDataTable").select(id);
          var data = $$("pengajaranRiwayatDataTable").getItem(id);
          var idTa = $$("menuPengajaran").getSelectedId();
          var tahun = idTa.substr(0, 4);
          var smt = idTa.substr(4, 1);
          if (smt % 2 == 0) {
            var semester = tahun + "/" + (parseInt(tahun) + 1) + " Genap";
          } else {
            var semester = tahun + "/" + (parseInt(tahun) + 1) + " Ganjil";
          }

          var dataPanelKelas = [
            { judul: "Program Studi:", konten: data.nm_lemb },
            { judul: "Semester:", konten: semester },
            { judul: "Mata Kuliah:", konten: data.kode_mk + "-" + data.nm_mk },
            { judul: "Nama Kelas:", konten: data.nm_kls },
          ];

          $$("panelKelasKuliah").clearAll();
          $$("panelKelasKuliah").define("data", dataPanelKelas);
          $$("panelKelasKuliah").refresh();

          //FORM PERSEN NILAI
          var formPersenNilai = {
            id_kls: data.id_kls,
            persen_absen: data.persen_absen,
            persen_tugas: data.persen_tugas,
            persen_uts: data.persen_uts,
            persen_uas: data.persen_uas,
            persen_total:
              (parseFloat(data.persen_absen) || 0) +
              (parseFloat(data.persen_tugas) || 0) +
              (parseFloat(data.persen_uts) || 0) +
              (parseFloat(data.persen_uas) || 0),
          };

          //dimatkan - direvisi
          //$$("formPersenNilai").setValues(formPersenNilai);

          webix.ajax().get(
            "sopingi/sms/bobotnilai/" + wSiaMhs.apiKey + "/" + data.id_sms,
            {},
            {
              success: function (text, xml, xhr) {
                proses_hide();
                var bobot = JSON.parse(text);
                abobot = Object.values(bobot);

                var data = $$("pengajaranRiwayatDataTable").getSelectedItem();
                $$("dataTableMahasiswaKelas").clearAll();
                $$("dataTableMahasiswaKelas").load(
                  "sopingi/nilai/tampil/" + wSiaMhs.apiKey + "/" + data.xid_kls,
                );
                $$("viewMahasiswaKelas").show();

                return false;
              },
              error: function (text, data, xhr) {
                proses_hide();
                webix.alert({
                  title: "Gagal Koneksi",
                  text: "Tidak dapat terhubung dengan internet!",
                  type: "alert-error",
                });
              },
            },
          );

          $$("winNilai").show();
        };

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

        $$("refreshMhsKelas").attachEvent("onItemClick", function () {
          var data = $$("pengajaranRiwayatDataTable").getSelectedItem();
          $$("dataTableMahasiswaKelas").clearAll();
          $$("dataTableMahasiswaKelas").load(
            "sopingi/nilai/tampil/" + wSiaMhs.apiKey + "/" + data.xid_kls,
          );
        });

        $$("unduhKhsXls").attachEvent("onItemClick", function () {
          var panel = $$("panelKelasKuliah").serialize();
          webix.toExcel($$("dataTableMahasiswaKelas"), {
            filename: panel[2].konten,
            name: panel[0].konten + " " + panel[3].konten,
            spans: true,
            styles: true,
          });
        });

        $$("simpanKhsKelas").attachEvent("onItemClick", function () {
          var itemNilai = new Array();
          $$("dataTableMahasiswaKelas").eachRow(function (baris) {
            var id_nilai = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).id_nilai;
            var nilai_absen = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_absen;
            var nilai_tugas = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_tugas;
            var nilai_uts = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_uts;
            var nilai_uas = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_uas;
            var nilai_angka = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_angka;
            var nilai_huruf = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_huruf;
            var nilai_indeks = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_indeks;
            var nilai_tampil = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_tampil;

            if (nilai_tampil != 1 && nilai_tampil != 3) {
              itemNilai.push({
                id_nilai: id_nilai,
                nilai_absen: String(nilai_absen || ""),
                nilai_tugas: String(nilai_tugas || ""),
                nilai_uts: String(nilai_uts || ""),
                nilai_uas: String(nilai_uas || ""),
                nilai_angka: String(nilai_angka || 0),
                nilai_huruf: String(nilai_huruf || ""),
                nilai_indeks: String(nilai_indeks || 0),
              });
            }
          });

          var nilai = JSON.stringify({ nilai: itemNilai, aksi: "ubah" });

          var data = $$("pengajaranRiwayatDataTable").getSelectedItem();
          webix
            .ajax()
            .post(
              "sopingi/nilai/ubah/" + wSiaMhs.apiKey + "/" + data.xid_kls,
              nilai,
              {
                success: function (response, data, xhr) {
                  proses_hide();
                  var hasil = JSON.parse(response);
                  if (hasil.berhasil) {
                    webix.message(hasil.pesan);
                    var data = $$(
                      "pengajaranRiwayatDataTable",
                    ).getSelectedItem();
                    $$("dataTableMahasiswaKelas").clearAll();
                    $$("dataTableMahasiswaKelas").load(
                      "sopingi/nilai/tampil/" +
                        wSiaMhs.apiKey +
                        "/" +
                        data.xid_kls,
                    );
                  } else {
                    peringatan("Kesalahan!", hasil.pesan);
                  }
                },
                error: function (text, data, xhr) {
                  proses_hide();
                  webix.alert({
                    title: "Gagal Koneksi",
                    text: "Tidak dapat terhubung dengan server/jaringan!",
                    type: "alert-error",
                  });
                },
              },
            );
        }); //simpanKhsKelas

        $$("kirimKhsKelas").attachEvent("onItemClick", function () {
          var itemNilai = new Array();
          var draft = true;
          var lengkap = true;
          $$("dataTableMahasiswaKelas").eachRow(function (baris) {
            var id_nilai = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).id_nilai;
            var nilai_absen = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_absen;
            var nilai_tugas = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_tugas;
            var nilai_uts = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_uts;
            var nilai_uas = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_uas;
            var nilai_tampil = $$("dataTableMahasiswaKelas").getItem(
              baris,
            ).nilai_tampil;

            if (nilai_tampil == 0) {
              draft = false;
            }

            if (
              nilai_absen == "" ||
              nilai_tugas == "" ||
              nilai_uts == "" ||
              nilai_uas == ""
            ) {
              lengkap = false;
            } else if (nilai_tampil == 2) {
              itemNilai.push({
                id_nilai: id_nilai,
              });
            }
          });

          if (!lengkap || !draft) {
            webix.alert({
              title: "Informasi",
              text: "Harap mengisi semua Nilai Mahasiswa<br>Nilai Absen, Tugas, UTS dan UAS<br> dan Simpan Draft Nilai terlebih dahulu",
              type: "alert-error",
            });
            return;
          }

          webix.confirm({
            title: "Konfirmasi",
            ok: "Kirim",
            cancel: "Batal",
            text: "Yakin mengirim Nilai ke BAAK ?<br>PASTIKAN SUDAH MENGISI NILAI SEMUA MAHASISWA<br>Setelah mengirim nilai, maka nilai tidak bisa dirubah",
            callback: function (result) {
              if (result) {
                var nilai = JSON.stringify({ nilai: itemNilai, aksi: "kirim" });

                var data = $$("pengajaranRiwayatDataTable").getSelectedItem();
                webix
                  .ajax()
                  .post(
                    "sopingi/nilai/kirim/" +
                      wSiaMhs.apiKey +
                      "/" +
                      data.xid_kls,
                    nilai,
                    {
                      success: function (response, data, xhr) {
                        proses_hide();
                        var hasil = JSON.parse(response);
                        if (hasil.berhasil) {
                          webix.message(hasil.pesan);
                          var data = $$(
                            "pengajaranRiwayatDataTable",
                          ).getSelectedItem();
                          $$("dataTableMahasiswaKelas").clearAll();
                          $$("dataTableMahasiswaKelas").load(
                            "sopingi/nilai/tampil/" +
                              wSiaMhs.apiKey +
                              "/" +
                              data.xid_kls,
                          );
                        } else {
                          peringatan("Kesalahan!", hasil.pesan);
                        }
                      },
                      error: function (text, data, xhr) {
                        proses_hide();
                        webix.alert({
                          title: "Gagal Koneksi",
                          text: "Tidak dapat terhubung dengan server/jaringan!",
                          type: "alert-error",
                        });
                      },
                    },
                  );
              }
            },
          });
        }); //kirimKhsKelas
      } else if (id == "mbkm.html") {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        open("#");
      } else if (id == "bimbinganakademikJurnal.html") {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        if (viewBimbinganAkademik) viewBimbinganAkademik.destroy();
        viewBimbinganAkademik.el = halaman;
        viewBimbinganAkademik.render();

        $$("sideKiri").show();
        $$("tombolMenu").show();

        $$("menuBimbinganAkademik").attachEvent("onAfterSelect", function (id) {
          try {
            $$("bimbinganAkademikRiwayatDataTable").clearAll();
            $$("bimbinganAkademikRiwayatDataTable").define(
              "url",
              "sopingi/mahasiswa/tampil/" + wSiaMhs.apiKey + "/" + id,
            );
            $$("bimbinganAkademikRiwayatDataTable").refresh();
          } catch (e) {}
        });

        $$("bimbinganAkademikRiwayatRefresh").attachEvent(
          "onItemClick",
          function () {
            var id = $$("menuBimbinganAkademik").getSelectedId();
            if (id) {
              $$("bimbinganAkademikRiwayatDataTable").clearAll();
              $$("bimbinganAkademikRiwayatDataTable").define(
                "url",
                "sopingi/mahasiswa/tampil/" + wSiaMhs.apiKey + "/" + id,
              );
              $$("bimbinganAkademikRiwayatDataTable").refresh();
            }
          },
        );

        $$("bimbinganAkademikRiwayatCetak").attachEvent(
          "onItemClick",
          function () {
            var id = $$("menuBimbinganAkademik").getSelectedId();
            if (id) {
              window.open(
                wSiaMhs.domain +
                  "/dosen/sopingi/presensi_pdf/download/" +
                  wSiaMhs.apiKey +
                  "/" +
                  id,
                "_blank",
              );
            } else {
              webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
            }
          },
        );

        webix.ui({
          view: "window",
          id: "mahasiswaWin",
          move: true,
          width: screen.width - 100,
          height: 600,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Detail Mahasiswa",
                id: "mahasiswaJudulWin",
              },
              {
                view: "button",
                type: "icon",
                icon: "close",
                click: "$$('mahasiswaWin').hide();",
              },
            ],
          },
          body: webix.copy(masterMahasiswaDetail),
        });

        $$("bimbinganAkademikRiwayatDataTable").on_click.btnDetail = function (
          e,
          id,
        ) {
          $$("bimbinganAkademikRiwayatDataTable").select(id);
          var data = $$("bimbinganAkademikRiwayatDataTable").getItem(id);
          //console.log(data);
          $$("menuMahasiswa").select("biodata_mahasiswa");
          $$("formMahasiswaDetail").setValues(data);
          $$("mahasiswaJudulWin").setValue(
            "Detail Mahasiswa: " +
              data.nipd +
              " - " +
              data.nm_pd +
              " ( " +
              data.kelas +
              " )" +
              " - " +
              data.vid_jns_daftar,
          );
          $$("mahasiswaWin").show();
          $$("kontenMahasiswaDetail").setValue("viewMahasiswaDetail");
          $$("viewMahasiswaDetail").show();
        };

        //Panel Kiri Mahasiswa
        $$("menuMahasiswa").attachEvent("onItemClick", function (id) {
          if (id == "biodata_mahasiswa") {
            $$("viewMahasiswaDetail").show();
          } else if (id == "krs_mahasiswa") {
            $$("viewMahasiswaKRS").show();
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();
            $$("dataTableKrs").clearAll();
            $$("dataTableKrs").load(
              "sopingi/pa/krs/" + wSiaMhs.apiKey + "/" + dataMhs.xid_reg_pd,
            );
          } else if (id == "khs_mahasiswa") {
            $$("viewMahasiswaKHS").show();
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();
            $$("dataTableKhs").clearAll();
            $$("dataTableKhs").load(
              "sopingi/pa/nilai/" + wSiaMhs.apiKey + "/" + dataMhs.xid_reg_pd,
            );
          } else if (id == "aktifitas_mahasiswa") {
            $$("viewMahasiswaAktifitas").show();
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();
            $$("dataTableAktifitas").clearAll();
            $$("dataTableAktifitas").load(
              "sopingi/pa/kuliah_mahasiswa/" +
                wSiaMhs.apiKey +
                "/" +
                dataMhs.xid_reg_pd,
            );
          } else if (id == "jurnal_bimbingan") {
            $$("viewJurnalBimbingan").show();
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();
            $$("jurnalDataTable").clearAll();
            $$("jurnalDataTable").load(
              "sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + dataMhs.xid_reg_pd,
            );
          }
        });

        //KRS

        webix.ui({
          view: "window",
          id: "winKelasKuliah",
          width: 750,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Kelas Mata Kuliah",
                id: "judulWinKelasKuliah",
              },
              {
                view: "button",
                type: "icon",
                icon: "close",
                click: "$$('winKelasKuliah').hide();",
              },
            ],
          },
          body: webix.copy(formKRS),
        });

        $$("refreshKRS").attachEvent("onItemClick", function (id, e) {
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          $$("dataTableKrs").clearAll();
          $$("dataTableKrs").load(
            "sopingi/pa/krs/" + wSiaMhs.apiKey + "/" + dataMhs.xid_reg_pd,
          );
        });

        $$("tambahKRS").attachEvent("onItemClick", function () {
          var data = [];
          var jSKS = 0;
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          var kelas = dataMhs.kelas;
          var id_sms = dataMhs.id_sms;

          $$("dataTableKrs").data.each(function (dataKrs) {
            data.push(dataKrs.vid_kls);
            jSKS = jSKS + parseInt(dataKrs.vsks_mk);
          });

          if (jSKS < 24) {
            var dataSudahKrs = new Object();
            dataSudahKrs.aksi = "kelas_kuliah";
            dataSudahKrs.data = data;
            dataSudahKrs.kelas = kelas;
            dataSudahKrs.id_sms = id_sms;
            var dataKirim = JSON.stringify(dataSudahKrs);
            webix
              .ajax()
              .post(
                "sopingi/pa/kelas_kuliah/" +
                  wSiaMhs.apiKey +
                  "/" +
                  dataMhs.xid_reg_pd,
                dataKirim,
                function (text, xml, xhr) {
                  var dataBelumKrs = JSON.parse(text);
                  $$("dataTableKelasPerkuliahan").clearAll();
                  $$("dataTableKelasPerkuliahan").define("data", dataBelumKrs);
                  $$("dataTableKelasPerkuliahan").refresh();
                  $$("winKelasKuliah").show();
                  $$("judulWinKelasKuliah").setValue(
                    "Tambah Kelas Mata Kuliah",
                  );
                },
              );
          } else {
            webix.alert({
              title: "Informasi",
              text: "Tidak dapat menambah SKS lagi..<br>(Maksimal 24 SKS)",
              type: "alert-error",
            });
          }
        });

        $$("simpanKRS").attachEvent("onItemClick", function () {
          if ($$("formKRS").validate()) {
            var dataKrs = [];
            var jSKSbaru = 0;
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();

            $$("dataTableKelasPerkuliahan").data.each(function (dataKelas) {
              if (dataKelas.ambilKelas) {
                dataKrs.push(dataKelas.xid_kls);
                jSKSbaru += parseInt(dataKelas.vsks_mk);
              }
            });

            var jSKS = 0;
            $$("dataTableKrs").data.each(function (dataSudahKrs) {
              jSKS += parseInt(dataSudahKrs.vsks_mk);
            });

            var totalSKS = jSKS + jSKSbaru;

            if (totalSKS <= 24) {
              $$("formKRS").setValues(
                { kelas: dataKrs, aksi: "tambah_krs" },
                true,
              );
              var dataKirim = JSON.stringify($$("formKRS").getValues());

              webix
                .ajax()
                .post(
                  "sopingi/pa/tambah_krs/" +
                    wSiaMhs.apiKey +
                    "/" +
                    dataMhs.xid_reg_pd,
                  dataKirim,
                  function (response, data, xhr) {
                    var hasil = JSON.parse(response);
                    if (hasil.berhasil) {
                      webix.message(hasil.pesan);
                      var dataMhs = $$(
                        "bimbinganAkademikRiwayatDataTable",
                      ).getSelectedItem();
                      $$("dataTableKrs").clearAll();
                      $$("dataTableKrs").load(
                        "sopingi/pa/krs/" +
                          wSiaMhs.apiKey +
                          "/" +
                          dataMhs.xid_reg_pd,
                      );
                      $$("formKRS").refresh();
                      $$("winKelasKuliah").hide();
                    } else {
                      webix.alert({
                        title: "Gagal Simpan",
                        text: hasil.pesan,
                        type: "alert-error",
                      });
                    }
                  },
                );
            } else {
              webix.alert({
                title: "Informasi",
                text: "Jumlah SKS melebihi 24 SKS. Silahkan cek kembali mata kuliah yang diambil",
                type: "alert-error",
              });
            }
          } else {
            webix.alert({
              title: "Kesalahan",
              text: "Form tidak valid",
              type: "alert-error",
            });
          }
        });

        $$("hapusKRS").attachEvent("onItemClick", function () {
          if ($$("dataTableKrs").getSelectedId() != null) {
            var data = $$("dataTableKrs").getSelectedItem();

            if (data.nilai_huruf != "") {
              webix.alert({
                title: "Informasi",
                text:
                  "Mata kuliah sudah mendapatkan nilai: " +
                  data.nilai_huruf +
                  "<br>Tidak diperbolehkan dihapus",
                type: "alert-warning",
              });
            } else if (data.id_kls != "") {
              webix.alert({
                title: "Informasi",
                text: "Kelas Perkuliahan ini tidak dapat dihapus",
                type: "alert-warning",
              });
            } else {
              var dataMhs = $$(
                "bimbinganAkademikRiwayatDataTable",
              ).getSelectedItem();
              webix.confirm({
                title: "Konfirmasi",
                ok: "Ya",
                cancel: "Tidak",
                text: "Yakin akan menghapus data yang dipilih ?",
                callback: function (jwb) {
                  if (jwb) {
                    data.aksi = "hapus_krs";
                    var dataKirim = JSON.stringify(data);
                    webix
                      .ajax()
                      .post(
                        "sopingi/pa/hapus_krs/" +
                          wSiaMhs.apiKey +
                          "/" +
                          dataMhs.xid_reg_pd,
                        dataKirim,
                        function (response, data, xhr) {
                          var hasil = JSON.parse(response);
                          if (hasil.berhasil) {
                            webix.message(hasil.pesan);
                            var dataMhs = $$(
                              "bimbinganAkademikRiwayatDataTable",
                            ).getSelectedItem();
                            $$("dataTableKrs").clearAll();
                            $$("dataTableKrs").load(
                              "sopingi/pa/krs/" +
                                wSiaMhs.apiKey +
                                "/" +
                                dataMhs.xid_reg_pd,
                            );
                          } else {
                            webix.alert({
                              title: "Gagal Hapus",
                              text: hasil.pesan,
                              type: "alert-error",
                            });
                          }
                        },
                      );
                  }
                },
              });
            }
          } else {
            webix.alert({
              title: "Informasi",
              text: "Belum ada data yang dipilih",
              type: "alert-warning",
            });
          }
        });

        $$("refreshKhs").attachEvent("onItemClick", function (id, e) {
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          $$("dataTableKhs").clearAll();
          $$("dataTableKhs").load(
            "sopingi/pa/nilai/" + wSiaMhs.apiKey + "/" + dataMhs.xid_reg_pd,
          );
        });

        $$("excelKhs").attachEvent("onItemClick", function (id, e) {
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          webix.toExcel($$("dataTableKhs"), {
            filename: "Histori_Nilai_" + dataMhs.nipd,
            name: dataMhs.nipd + "-" + dataMhs.nm_pd,
            spans: true,
            styles: true,
            footer: true,
          });
        });

        $$("refreshAktifitas").attachEvent("onItemClick", function (id, e) {
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          $$("dataTableAktifitas").clearAll();
          $$("dataTableAktifitas").load(
            "sopingi/pa/kuliah_mahasiswa/" +
              wSiaMhs.apiKey +
              "/" +
              dataMhs.xid_reg_pd,
          );
        });

        $$("excelAktifitas").attachEvent("onItemClick", function (id, e) {
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          webix.toExcel($$("dataTableAktifitas"), {
            filename: "Aktifitas_Kuliah_" + dataMhs.nipd,
            name: dataMhs.nipd + "-" + dataMhs.nm_pd,
            spans: true,
            styles: true,
            footer: true,
          });
        });

        //Jurnal Bimbingan
        webix.ui({
          view: "window",
          id: "winJurnal",
          width: 750,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Jurnal Bimbingan Akademik",
                id: "judulWinJurnal",
              },
              {
                view: "button",
                type: "icon",
                icon: "close",
                click: "$$('winJurnal').hide();",
              },
            ],
          },
          body: webix.copy(formJurnal),
        });

        $$("refreshJurnal").attachEvent("onItemClick", function (id, e) {
          var dataMhs = $$(
            "bimbinganAkademikRiwayatDataTable",
          ).getSelectedItem();
          $$("jurnalDataTable").clearAll();
          $$("jurnalDataTable").load(
            "sopingi/pa/jurnal/" + wSiaMhs.apiKey + "/" + dataMhs.xid_reg_pd,
          );
        });

        $$("tambahJurnal").attachEvent("onItemClick", function () {
          $$("winJurnal").show();
          $$("formJurnal").clear();
          $$("formJurnal").setValues({ aksi: "tambah_jurnal" });
          $$("judulWinJurnal").setValue("Tambah Jurnal Bimbingan Akademik");
        });

        $$("ubahJurnal").attachEvent("onItemClick", function () {
          if ($$("jurnalDataTable").getSelectedId() != null) {
            var data = $$("jurnalDataTable").getSelectedItem();
            data.aksi = "ubah_jurnal";
            $$("winJurnal").show();
            $$("formJurnal").setValues(data);
            $$("judulWinJurnal").setValue("Ubah Jurnal Bimbingan Akademik");
          } else {
            webix.alert({
              title: "Informasi",
              text: "Belum ada data yang dipilih",
              type: "alert-warning",
            });
          }
        });

        $$("simpanJurnal").attachEvent("onItemClick", function () {
          if ($$("formJurnal").validate()) {
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();

            var dataJurnal = $$("formJurnal").getValues();
            var dataKirim = JSON.stringify($$("formJurnal").getValues());
            $$("formJurnal").disable();
            webix
              .ajax()
              .post(
                "sopingi/pa/jurnal/" +
                  wSiaMhs.apiKey +
                  "/" +
                  dataMhs.xid_reg_pd,
                dataKirim,
                function (response, data, xhr) {
                  $$("formJurnal").enable();
                  var hasil = JSON.parse(response);
                  if (hasil.berhasil) {
                    webix.message(hasil.pesan);
                    var dataMhs = $$(
                      "bimbinganAkademikRiwayatDataTable",
                    ).getSelectedItem();
                    $$("jurnalDataTable").clearAll();
                    $$("jurnalDataTable").load(
                      "sopingi/pa/jurnal/" +
                        wSiaMhs.apiKey +
                        "/" +
                        dataMhs.xid_reg_pd,
                    );
                    $$("formJurnal").clear();
                    $$("winJurnal").hide();
                  } else {
                    webix.alert({
                      title: "Gagal Simpan",
                      text: hasil.pesan,
                      type: "alert-error",
                    });
                  }
                },
              );
          } else {
            webix.alert({
              title: "Kesalahan",
              text: "Form tidak valid",
              type: "alert-error",
            });
          }
        });

        $$("hapusJurnal").attachEvent("onItemClick", function () {
          if ($$("jurnalDataTable").getSelectedId() != null) {
            var data = $$("jurnalDataTable").getSelectedItem();
            var dataMhs = $$(
              "bimbinganAkademikRiwayatDataTable",
            ).getSelectedItem();
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  data.aksi = "hapus_jurnal";
                  var dataKirim = JSON.stringify(data);
                  webix
                    .ajax()
                    .post(
                      "sopingi/pa/hapus_jurnal/" +
                        wSiaMhs.apiKey +
                        "/" +
                        dataMhs.xid_reg_pd,
                      dataKirim,
                      function (response, data, xhr) {
                        var hasil = JSON.parse(response);
                        if (hasil.berhasil) {
                          webix.message(hasil.pesan);
                          var dataMhs = $$(
                            "bimbinganAkademikRiwayatDataTable",
                          ).getSelectedItem();
                          $$("jurnalDataTable").clearAll();
                          $$("jurnalDataTable").load(
                            "sopingi/pa/jurnal/" +
                              wSiaMhs.apiKey +
                              "/" +
                              dataMhs.xid_reg_pd,
                          );
                        } else {
                          webix.alert({
                            title: "Gagal Hapus",
                            text: hasil.pesan,
                            type: "alert-error",
                          });
                        }
                      },
                    );
                }
              },
            });
          } else {
            webix.alert({
              title: "Informasi",
              text: "Belum ada data yang dipilih",
              type: "alert-warning",
            });
          }
        });

        //chat
        webix.ui({
          view: "window",
          id: "winPesan",
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Pesan Bimbingan Akademik",
                id: "judulWinPesan",
              },
              {
                view: "button",
                type: "icon",
                icon: "trash",
                hidden: true,
                id: "hapusPesan",
                click: function () {
                  var data = $$("pesanList").getSelectedItem();
                  webix.confirm({
                    title: "Konfirmasi",
                    ok: "Ya",
                    cancel: "Tidak",
                    text: "Yakin akan menghapus pesan yang dipilih ?",
                    callback: function (jwb) {
                      if (jwb) {
                        $$("pesanList").unselectAll();
                        $$("hapusPesan").hide();
                        data.aksi = "hapus_pesan";
                        var dataKirim = JSON.stringify(data);
                        webix
                          .ajax()
                          .post(
                            "sopingi/pa/hapus_pesan/" +
                              wSiaMhs.apiKey +
                              "/" +
                              Math.random(),
                            dataKirim,
                            function (response, data, xhr) {
                              var hasil = JSON.parse(response);
                              if (hasil.berhasil) {
                                webix.message(hasil.pesan);
                                $$("pesanList").remove(hasil.id);
                              } else {
                                webix.alert({
                                  title: "Gagal Hapus",
                                  text: hasil.pesan,
                                  type: "alert-error",
                                });
                              }
                            },
                          );
                      }
                    },
                  });
                },
              },
              {
                width: 20,
                template: "",
              },
              {
                view: "button",
                type: "icon",
                icon: "close",
                click: "$$('winPesan').hide();",
              },
            ],
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
                  height: "auto",
                },

                template: function (item) {
                  var css = item.author == "dosen" ? "1" : "2";
                  return (
                    "<div class='pesan msg" +
                    css +
                    "'><span class='waktu'>" +
                    item.author +
                    " | " +
                    item.waktu +
                    "</span>" +
                    item.text +
                    "</div>"
                  );
                },
              },
              {
                height: 60,
                cols: [
                  { view: "textarea", id: "pesan", required: true },
                  {
                    view: "text",
                    id: "id_jurnal",
                    required: true,
                    hidden: true,
                  },
                  {
                    width: 60,
                    rows: [
                      {
                        view: "button",
                        label: "Kirim",
                        height: 60,
                        click: function () {
                          $$("pesanList").unselectAll();
                          $$("hapusPesan").hide();
                          var text = $$("pesan").getValue();
                          var id = $$("id_jurnal").getValue();
                          text = text.replace(/\n/g, "<br/>");
                          if (text && id) {
                            var data = {
                              aksi: "kirim_pesan",
                              id_jurnal: id,
                              pesan: text,
                            };
                            var dataKirim = JSON.stringify(data);
                            webix
                              .ajax()
                              .post(
                                "sopingi/pa/kirim_pesan/" +
                                  wSiaMhs.apiKey +
                                  "/" +
                                  Math.random(),
                                dataKirim,
                                function (response, data, xhr) {
                                  $$("pesan").setValue("");
                                  var hasil = JSON.parse(response);
                                  if (hasil.berhasil) {
                                    webix.message(hasil.pesan);
                                    $$("pesanList").add({
                                      text: hasil.text,
                                      waktu: hasil.waktu,
                                      author: hasil.author,
                                    });
                                    $$("pesanList").showItem(
                                      $$("pesanList").getLastId(),
                                    );
                                  } else {
                                    webix.alert({
                                      title: "Gagal Kirim",
                                      text: hasil.pesan,
                                      type: "alert-error",
                                    });
                                  }
                                },
                              );
                          }
                        },
                      },
                      {},
                    ],
                  },
                ],
              },
            ],
          },
        });

        $$("jurnalDataTable").on_click.btnChat = function (e, id) {
          $$("jurnalDataTable").select(id);
          var data = $$("jurnalDataTable").getItem(id);
          $$("id_jurnal").setValue(data.id);
          $$("pesanList").clearAll();
          $$("pesanList").load(
            "sopingi/pa/pesan/" + wSiaMhs.apiKey + "/" + data.id,
          );
          $$("winPesan").show();
        };

        $$("pesanList").attachEvent("onAfterLoad", function () {
          $$("pesanList").showItem($$("pesanList").getLastId());
        });

        $$("pesanList").attachEvent("onItemClick", function (id, e, node) {
          $$("hapusPesan").show();
        });
      } else if (id == "bimbinganakademikAktifitas.html") {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        if (typeof pembimbingDetail !== "undefined" && pembimbingDetail) {
          pembimbingDetail.destroy();
        }
        var panelKiriPembimbing = {
          id: "panelKiriPembimbing",
          borderless: false,
          width: 150,
          rows: [
            {
              template: "Tahun Akademik",
              type: "header",
            },
            {
              id: "menuPembimbing",
              view: "list",
              select: true,
              scroll: true,
              url:
                "sopingi/semester/pilih/" +
                wSiaMhs.apiKey +
                "/" +
                Math.random(),
            },
          ],
        };

        var dosen_pembimbingRiwayat = {
          id: "dosen_pembimbingRiwayat",
          type: "space",
          rows: [
            {
              view: "toolbar",
              paddingY: 2,
              cols: [
                {
                  view: "richselect",
                  type: "iconButton",
                  select: true,
                  id: "selectBimbinganKelas",
                  label: "Pilih Kelas",
                  width: 200,
                  options: "sopingi/kelas_pa/semua/" + wSiaMhs.apiKey + "/1",
                },
                {
                  view: "button",
                  id: "selectBimbinganTampil",
                  label: "Search",
                  type: "iconButton",
                  icon: "search",
                  width: 100,
                },
                {
                  view: "button",
                  id: "pembimbingRiwayatRefresh",
                  label: "Refresh",
                  type: "iconButton",
                  icon: "refresh",
                  width: 100,
                },
                { template: "", borderless: true },
                {
                  view: "button",
                  id: "pembimbingRiwayatCetak",
                  label: "Cetak",
                  type: "iconButton",
                  icon: "file-pdf-o",
                  width: 100,
                },
                {
                  view: "button",
                  id: "pembimbingRiwayatTambah",
                  label: "Tambah",
                  type: "iconButton",
                  icon: "plus",
                  width: 100,
                },
              ],
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
                  id: "mhs_aktif",
                  header: [{ text: "Aktifitas Kuliah", colspan: 4 }, "Aktif"],
                  width: 75,
                },
                { id: "mhs_nonaktif", header: ["", "Non Aktif"], width: 75 },
                { id: "mhs_cuti", header: ["", "Cuti"], width: 75 },
                { id: "mhs_keluar", header: ["", "Keluar/Lulus"], width: 90 },
                { id: "kesimpulan", header: "Keterangan", fillspace: true },
                {
                  id: "detail",
                  header: "Detail",
                  width: 100,
                  template:
                    "<button class='btnDetail btnTransparant'><i class='webix_icon fa-info'></i> Detail</button>",
                },
                {
                  id: "hapus",
                  header: "Aksi",
                  width: 100,
                  template:
                    "<button class='btnHapus btnTransparant'><i class='webix_icon fa-trash'></i> Hapus</button>",
                },
              ],
              pager: "pembimbingRiwayatPager",
              hover: "tableHover",
              on: {
                onBeforeLoad: function () {
                  this.showOverlay(
                    "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                  );
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
                    } catch (e) {}
                  });

                  var jData = this.data.order.length;
                  $("#jPengajaran").html(jData);
                },
              },
            },

            {
              view: "pager",
              id: "pembimbingRiwayatPager",
              template:
                "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jPengajaran'>  </span></b> Data",
              size: 12,
              group: 5,
              animate: {
                direction: "left",
                type: "slide",
              },
            },
          ],
        };

        if (typeof pembimbingDetail !== "undefined" && pembimbingDetail) {
          pembimbingDetail.destroy();
        }

        pembimbingDetail = new WebixView({
          config: {
            id: "pembimbingDetail",
            type: "clean",
            rows: [
              {
                template: "Aktifitas Bimbingan",
                type: "header",
              },
              { cols: [panelKiriPembimbing, dosen_pembimbingRiwayat] },
            ],
          },
        });

        pembimbingDetail.el = halaman;
        pembimbingDetail.render();

        $$("sideKiri").show();
        $$("tombolMenu").show();

        var formPembimbing = {
          view: "form",
          id: "formPembimbing",
          borderless: true,
          elements: [
            {
              view: "datepicker",
              label: "Tanggal",
              name: "tanggal",
              required: true,
              invalidMessage: "Belum dipilih",
              inputWidth: 180,
              format: "%d-%m-%Y",
              stringResult: true,
            },
            {
              view: "fieldset",
              id: "fieldAktifitas",
              label:
                "Aktifitas Kuliah Mahasiswa (SAAT MODE TAMBAH: otomatis mengambil data kuliah mahasiswa di BAAK)",
              body: {
                cols: [
                  {
                    view: "text",
                    label: "Aktif",
                    name: "mhs_aktif",
                    required: true,
                    attributes: { type: "number" },
                  },
                  {
                    view: "text",
                    label: "Non Aktif",
                    name: "mhs_nonaktif",
                    required: true,
                    attributes: { type: "number" },
                  },
                  {
                    view: "text",
                    label: "Cuti",
                    name: "mhs_cuti",
                    required: true,
                    attributes: { type: "number" },
                  },
                  {
                    view: "text",
                    label: "Keluar/Lulus",
                    name: "mhs_keluar",
                    required: true,
                    attributes: { type: "number" },
                  },
                ],
              },
            },
            {
              view: "textarea",
              label: "Kondisi mahasiswa",
              name: "kondisi_mahasiswa",
              required: true,
              placeholder: "",
              invalidMessage: "Belum diisi",
              height: 100,
            },
            {
              view: "textarea",
              label: "Mahasiswa butuh penanganan khusus",
              name: "penanganan_mahasiswa",
              required: true,
              placeholder: "",
              invalidMessage: "Belum diisi",
              height: 100,
            },
            {
              view: "textarea",
              label: "Hasil dan kesimpulan bimbingan",
              name: "kesimpulan",
              required: true,
              placeholder: "",
              invalidMessage: "Belum diisi",
              height: 100,
            },
            {
              cols: [
                { template: " ", borderless: true },
                {
                  view: "button",
                  id: "simpanPembimbing",
                  label: "Simpan",
                  type: "form",
                  width: 120,
                  borderless: true,
                },
                { template: " ", borderless: true },
              ],
            },
          ],
          elementsConfig: {
            labelPosition: "top",
          },
        };

        if ($$("winPembimbing")) {
          $$("winPembimbing").destructor();
        }

        webix.ui({
          view: "window",
          id: "winPembimbing",
          width: 750,
          height: 600,
          position: "center",
          top: 30,
          modal: true,
          move: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Aktifitas Bimbingan",
                id: "judulWinPembimbing",
              },
              {
                view: "button",
                type: "icon",
                icon: "close",
                click: "$$('winPembimbing').hide();",
              },
            ],
          },
          body: {
            view: "scrollview",
            scroll: "y",
            body: formPembimbing,
          },
        });

        var reloadBimbinganDataTable = function () {
          var kelas = $$("selectBimbinganKelas").getValue();
          var th = $$("menuPembimbing").getSelectedId();
          if (th && kelas) {
            $$("pembimbingRiwayatDataTable").clearAll();
            $$("pembimbingRiwayatDataTable").load(
              "sopingi/pa_aktifitas/tampil/" +
                wSiaMhs.apiKey +
                "/" +
                th +
                "_" +
                kelas,
            );
          }
        };

        $$("pembimbingRiwayatRefresh").attachEvent(
          "onItemClick",
          reloadBimbinganDataTable,
        );

        $$("menuPembimbing").attachEvent(
          "onAfterSelect",
          reloadBimbinganDataTable,
        );

        $$("selectBimbinganKelas").attachEvent(
          "onChange",
          reloadBimbinganDataTable,
        );

        $$("selectBimbinganTampil").attachEvent("onItemClick", function () {
          console.log("[DEBUG] Search Clicked");
          var kelas = $$("selectBimbinganKelas").getValue();
          var th = $$("menuPembimbing").getSelectedId();
          console.log("[DEBUG] Kelas:", kelas, "Tahun:", th);

          if (th === "") {
            webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
          } else if (kelas === "") {
            webix.alert("Silahkan pilih kelas terlebih dahulu");
          } else {
            var url =
              "sopingi/pa_aktifitas/tampil/" +
              wSiaMhs.apiKey +
              "/" +
              th +
              "_" +
              kelas;
            console.log("[DEBUG] Loading URL: " + url);
            $$("pembimbingRiwayatDataTable").clearAll();
            $$("pembimbingRiwayatDataTable").load(url);
          }
        });

        $$("pembimbingRiwayatCetak").attachEvent("onItemClick", function () {
          var kelas = $$("selectBimbinganKelas").getValue();
          var th = $$("menuPembimbing").getSelectedId();
          if (th === "") {
            webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
          } else if (kelas === "") {
            webix.alert("Silahkan pilih kelas terlebih dahulu");
          } else {
            window.open(
              wSiaMhs.domain +
                "/dosen/sopingi/aktifitas_bimbingan_pdf/download/" +
                wSiaMhs.apiKey +
                "/" +
                th +
                "_" +
                kelas,
              "_blank",
            );
          }
        });

        $$("pembimbingRiwayatTambah").attachEvent("onItemClick", function () {
          var kelas = $$("selectBimbinganKelas").getValue();
          var th = $$("menuPembimbing").getSelectedId();
          if (th === "") {
            webix.alert("Silahkan pilih tahun akademik terlebih dahulu");
          } else if (kelas === "") {
            webix.alert("Silahkan pilih kelas terlebih dahulu");
          } else {
            $$("fieldAktifitas").define({
              label:
                "Aktifitas Kuliah Mahasiswa (SAAT MODE TAMBAH: otomatis mengambil data kuliah mahasiswa di BAAK)",
            });

            $$("winPembimbing").show();
            $$("formPembimbing").clear();
            $$("formPembimbing").load(
              "sopingi/pa_aktifitas/aktifitas_mahasiswa/" +
                wSiaMhs.apiKey +
                "/" +
                th +
                "_" +
                kelas,
            );
            $$("judulWinPembimbing").setValue("Tambah Aktifitas Bimbingan");
          }
        });

        $$("simpanPembimbing").attachEvent("onItemClick", simpanPembimbing);

        $$("pembimbingRiwayatDataTable").on_click.btnDetail = function (e, id) {
          $$("pembimbingRiwayatDataTable").select(id);
          var data = $$("pembimbingRiwayatDataTable").getItem(id);
          data.aksi = "ubah";
          $$("fieldAktifitas").define({ label: "Aktifitas Kuliah Mahasiswa" });

          $$("formPembimbing").setValues(data);
          $$("judulWinPembimbing").setValue("Ubah Aktifitas Bimbingan");
          $$("winPembimbing").show();
        };

        $$("pembimbingRiwayatDataTable").on_click.btnHapus = function (e, id) {
          $$("pembimbingRiwayatDataTable").select(id);
          var data = $$("pembimbingRiwayatDataTable").getItem(id);
          data.aksi = "hapus";

          webix.confirm({
            title: "Konfirmasi",
            ok: "Ya",
            cancel: "Tidak",
            text: "Yakin akan menghapus data yang dipilih ?",
            callback: function (jwb) {
              if (jwb) {
                var dataKirim = JSON.stringify(data);
                webix
                  .ajax()
                  .post(
                    "sopingi/pa_aktifitas/hapus/" +
                      wSiaMhs.apiKey +
                      "/" +
                      Math.random(),
                    dataKirim,
                    function (response, data, xhr) {
                      var hasil = JSON.parse(response);
                      if (hasil.berhasil) {
                        webix.message(hasil.pesan);
                        var kelas = $$("selectBimbinganKelas").getValue();
                        var th = $$("menuPembimbing").getSelectedId();
                        $$("pembimbingRiwayatDataTable").clearAll();
                        $$("pembimbingRiwayatDataTable").load(
                          "sopingi/pa_aktifitas/tampil/" +
                            wSiaMhs.apiKey +
                            "/" +
                            th +
                            "_" +
                            kelas,
                        );
                      } else {
                        webix.alert({
                          title: "Gagal Hapus",
                          text: hasil.pesan,
                          type: "alert-error",
                        });
                      }
                    },
                  );
              }
            },
          });
        };
      } else {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
        if (wSiaMhs === null || wSiaMhs == "") {
          kembaliKeLogin();
        }

        formUtama.el = halaman;
        formUtama.render();

        $$("sideKiri").show();
        $$("tombolMenu").show();
      }
    },
  }))();

  Backbone.history.start();

  //adding ProgressBar functionality to layout
  webix.extend($$("layout_utama"), webix.ProgressBar);

  function proses_tampil() {
    $$("layout_utama").disable();
    $$("layout_utama").showProgress({
      type: "icon",
      icon: "cog",
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
    var style = document.createElement("style");
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

    webix
      .ui({
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
										Email <strong>@poltekindonusa.ac.id</strong> Bapak/Ibu belum tersambung ke akun SIAKAD ini. 
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
                },
              },
            },
          ],
        },
      })
      .show();
  }

  // Cek notifikasi email poltek
  if (wSiaMhs && (!wSiaMhs.email_poltek || wSiaMhs.email_poltek == "")) {
    // Tampilkan sedikit delay agar dashboard termuat dulu
    setTimeout(tampilkanNotifEmailPoltek, 1500);
  }
});
