webix.ready(function () {
  var menuBarJudul = {
    view: "toolbar",
    id: "toolbar",
    paddingY: 0,
    paddingX: 0,
    width: 130,
    type: "clean",
    css: "headerAtas",
    elements: [
      {
        css: "headerAtas",
        view: "label",
        label: "<img src='../gambar/logo.png' height='25'>",
      },
    ],
  };

  var menuAtas = {
    view: "menu",
    autowidth: true,
    openAction: "click",
    borderless: true,
    css: "headerAtas",
    type: "clean",
    data: [
      { id: "dashboard.html", value: "", icon: "bars" },
      {
        id: "1",
        value: "Master",
        icon: "pencil-square-o",
        submenu: [
          { id: "master-program-studi.html", value: "Program Studi" },
          { id: "master-mata-kuliah.html", value: "Mata Kuliah" },
          { id: "master-kurikulum.html", value: "Kurikulum" },
          { id: "master-kelas.html", value: "Kelas" },
          { id: "master-ruang.html", value: "Ruang" },
        ],
      },
      {
        id: "2",
        value: "Mahasiswa",
        icon: "users",
        submenu: [
          { id: "import-mahasiswa.html", value: "Mahasiswa Baru" },
          { id: "master-mahasiswa.html", value: "Data Mahasiswa" },
          { id: "hak-akses-krs.html", value: "Hak Akses KRS" },
          { id: "buku-induk.html", value: "Buku Induk" },
        ],
      },
      {
        id: "3",
        value: "Dosen",
        icon: "user",
        submenu: [
          { id: "dosen.html", value: "Data Dosen" },
          { id: "penugasan-dosen.html", value: "Penugasan Dosen" },
        ],
      },
      {
        id: "4",
        value: "Perkuliahan",
        icon: "mortar-board",
        submenu: [
          { id: "kelas-perkuliahan.html", value: "Kelas Perkuliahan" },
          { id: "kuliah-mahasiswa.html", value: "Kuliah Mahasiswa" },
          { id: "khs-murni.html", value: "KHS Mhs" },
          /*
				{ id:"3Khs", value:"KHS Perkuliahan", submenu:[ 
					{ id:"khs-murni.html", value:"KHS Mhs Murni"},
					{ id:"khs-transfer.html", value:"KHS Mhs Pindahan/Transfer"},
				]},
				*/
          { id: "mahasiswa-keluar.html", value: "Mahasiswa Lulus/Keluar" },
        ],
      },
      {
        id: "5",
        value: "Pelengkap",
        icon: "wrench",
        submenu: [
          { id: "semester.html", value: "Tahun Akademik - Semester" },
          { id: "bobot-nilai.html", value: "Bobot Nilai" },
          { id: "direktur-wadir.html", value: "Diektur - Wadir" },
          { id: "akun-admin.html", value: "Ubah Akun Admin" },
        ],
      },
      {
        id: "6",
        value: "Sync ke Neo Feeder",
        icon: "send",
        submenu: [
          { id: "ws-token.html", value: "Generate Token" },
          { id: "ws-dosen.html", value: "Dosen" },
          { id: "ws-dosen-pt.html", value: "Penugasan Dosen" },
          { id: "ws-mahasiswa.html", value: "Mahasiswa" },
          { id: "ws-pendidikan-mahasiswa.html", value: "Pendidikan Mahasiswa" },
          { id: "ws-matakuliah.html", value: "Mata Kuliah" },
          { id: "ws-kurikulum.html", value: "Kurikulum" },
          {
            id: "ws-matakuliah-kurikulum.html",
            value: "Mata Kuliah Kurikulum",
          },
          { id: "ws-kelas-perkuliahan.html", value: "Kelas Perkuliahan" },
          { id: "ws-ajar-dosen.html", value: "Ajar Dosen" },
          { id: "ws-krs-nilai.html", value: "KRS & Nilai" },
          {
            id: "ws-kuliah-mahasiswa.html",
            value: "Aktifitas Kuliah Mahasiswa",
          },
          { id: "ws-mahasiswa-keluar.html", value: "Mahasiswa Lulus/Keluar" },
        ],
      },
    ],
    on: {
      onMenuItemClick: function (id) {
        if (
          id != 0 &&
          id != 1 &&
          id != 2 &&
          id != 3 &&
          id != 4 &&
          id != 5 &&
          id != 6 &&
          id != "3Khs"
        ) {
          bukaHalaman(id);
        }
      },
    },
  };

  var kampus = {
    type: "clean",
    cols: [
      //{ css:"kampus", url:"sopingi/satuan_pendidikan/tampil/"+wSia.apiKey+"/"+Math.random(), template: "#npsn# - #nm_lemb#", width: 100},
      {
        css: "kampus",
        url: "sopingi/semester/berlaku/" + wSia.apiKey + "/" + Math.random(),
        id: "nm_smt",
        template: "Semester : #nm_smt#",
        width: 180,
      },
      {
        id: "keluar",
        css: "kampus",
        view: "button",
        type: "form",
        label: "Keluar",
        width: 70,
        click: function () {
          keluarProses();
        },
      },
    ],
  };

  var bukaHalaman = function (hal) {
    routes.navigate("/" + hal, { trigger: true });
  };

  var ui_wsia = {
    id: "layout_utama",
    type: "clean",
    rows: [
      {
        view: "toolbar",
        type: "clean",
        css: "headerAtas",
        borderless: true,
        cols: [menuBarJudul, menuAtas, kampus],
      },
      { id: "halaman", template: "Loading..." },
      {
        template: "SIAKAD | POLITEKNIK INDONUSA Surakarta",
        css: "headerAtas",
        height: 30,
      },
    ],
  };

  var layout = new WebixView({
    config: ui_wsia,
    el: ".app_wsia",
  }).render();

  //Router
  var routes = new (Backbone.Router.extend({
    routes: {
      "": "index",
      ":hal": "halaman",
      login: "login",
    },
    index: function () {
      var wSia = webix.storage.session.get("wSia");
      if (wSia === null || wSia == "") {
        kembaliKeLogin();
      }

      halaman = layout.root.getChildViews()[1];
      dashboard.el = halaman;
      dashboard.render();
    },
    halaman: function (hal) {
      var wSia = webix.storage.session.get("wSia");
      if (wSia === null || wSia == "") {
        kembaliKeLogin();
      }

      halaman = layout.root.getChildViews()[1];
      if (hal == "master-program-studi.html") {
        masterProgramStudi.el = halaman;
        masterProgramStudi.render();

        webix.ui({
          view: "window",
          id: "winSms",
          width: 800,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Program Studi",
                id: "judulWinSms",
              },
              { view: "icon", icon: "close", click: "$$('winSms').hide();" },
            ],
          },
          body: webix.copy(formProgramStudi),
        });

        $$("tambahSms").attachEvent("onItemClick", function () {
          $$("winSms").show();
          $$("judulWinSms").setValue("Tambah Program Studi");
          $$("formSms").setValues({
            xid_sms: webix.uid(),
            aksi: "tambah",
          });
          $$("kode_prodi").enable();
        });

        $$("ubahSms").attachEvent("onItemClick", function () {
          if ($$("dataTableSms").getSelectedId() != null) {
            data = $$("dataTableSms").getSelectedItem();
            $$("winSms").show();
            $$("judulWinSms").setValue("Ubah Program Studi");
            data.aksi = "ubah";
            $$("formSms").setValues(data);
            $$("kode_prodi").disable();
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusSms").attachEvent("onItemClick", function () {
          if ($$("dataTableSms").getSelectedId() != null) {
            data = $$("dataTableSms").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusSms(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanSms").attachEvent("onItemClick", simpanSms);
        $$("refreshSms").attachEvent("onItemClick", refreshSms);
      } else if (hal == "master-mata-kuliah.html") {
        masterMataKuliah.el = halaman;
        masterMataKuliah.render();

        webix.ui({
          view: "window",
          id: "winMataKuliah",
          width: 800,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Mata Kuliah",
                id: "judulWinMataKuliah",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winMataKuliah').hide();",
              },
            ],
          },
          body: webix.copy(formMataKuliah),
        });

        $$("tambahMataKuliah").attachEvent("onItemClick", function () {
          $$("winMataKuliah").show();
          $$("judulWinMataKuliah").setValue("Tambah Mata Kuliah");
          $$("formMataKuliah").setValues({
            xid_mk: webix.uid(),
            aksi: "tambah",
          });
        });

        $$("ubahMataKuliah").attachEvent("onItemClick", function () {
          if ($$("dataTableMataKuliah").getSelectedId() != null) {
            data = $$("dataTableMataKuliah").getSelectedItem();
            $$("winMataKuliah").show();
            $$("judulWinMataKuliah").setValue("Ubah Mata Kuliah");
            data.aksi = "ubah";
            data.a_sap = parseInt(data.a_sap);
            data.a_silabus = parseInt(data.a_silabus);
            data.a_bahan_ajar = parseInt(data.a_bahan_ajar);
            data.acara_prak = parseInt(data.acara_prak);
            data.a_diktat = parseInt(data.a_diktat);
            $$("formMataKuliah").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusMataKuliah").attachEvent("onItemClick", function () {
          if ($$("dataTableMataKuliah").getSelectedId() != null) {
            data = $$("dataTableMataKuliah").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusMataKuliah(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("sks_tm").attachEvent("onChange", function (baru, lama) {
          $$("sks_mk").setValue(
            $$("sks_tm").getValue() +
              $$("sks_prak").getValue() +
              $$("sks_prak_lap").getValue() +
              $$("sks_sim").getValue(),
          );
        });

        $$("sks_prak").attachEvent("onChange", function (baru, lama) {
          $$("sks_mk").setValue(
            $$("sks_tm").getValue() +
              $$("sks_prak").getValue() +
              $$("sks_prak_lap").getValue() +
              $$("sks_sim").getValue(),
          );
        });

        $$("sks_prak_lap").attachEvent("onChange", function (baru, lama) {
          $$("sks_mk").setValue(
            $$("sks_tm").getValue() +
              $$("sks_prak").getValue() +
              $$("sks_prak_lap").getValue() +
              $$("sks_sim").getValue(),
          );
        });

        $$("sks_sim").attachEvent("onChange", function (baru, lama) {
          $$("sks_mk").setValue(
            $$("sks_tm").getValue() +
              $$("sks_prak").getValue() +
              $$("sks_prak_lap").getValue() +
              $$("sks_sim").getValue(),
          );
        });

        $$("simpanMataKuliah").attachEvent("onItemClick", simpanMataKuliah);
        $$("refreshMataKuliah").attachEvent("onItemClick", refreshMataKuliah);
      } else if (hal == "master-kurikulum.html") {
        masterKurikulum.el = halaman;
        masterKurikulum.render();

        webix.ui({
          view: "window",
          id: "winKurikulum",
          width: 500,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Kurikulum",
                id: "judulWinKurikulum",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winKurikulum').hide();",
              },
            ],
          },
          body: webix.copy(formKurikulum),
        });

        $$("tambahKurikulum").attachEvent("onItemClick", function () {
          $$("winKurikulum").show();
          $$("judulWinKurikulum").setValue("Tambah Kurikulum");
          $$("formKurikulum").setValues({
            xid_kurikulum_sp: webix.uid(),
            aksi: "tambah",
          });
        });

        $$("ubahKurikulum").attachEvent("onItemClick", function () {
          if ($$("dataTableKurikulum").getSelectedId() != null) {
            data = $$("dataTableKurikulum").getSelectedItem();
            $$("winKurikulum").show();
            $$("judulWinKurikulum").setValue("Ubah Kurikulum");
            data.aksi = "ubah";
            $$("formKurikulum").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusKurikulum").attachEvent("onItemClick", function () {
          if ($$("dataTableKurikulum").getSelectedId() != null) {
            data = $$("dataTableKurikulum").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusKurikulum(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("jml_sks_wajib").attachEvent("onChange", function (baru, lama) {
          $$("jml_sks_lulus").setValue(
            $$("jml_sks_wajib").getValue() + $$("jml_sks_pilihan").getValue(),
          );
        });

        $$("jml_sks_pilihan").attachEvent("onChange", function (baru, lama) {
          $$("jml_sks_lulus").setValue(
            $$("jml_sks_wajib").getValue() + $$("jml_sks_pilihan").getValue(),
          );
        });

        $$("simpanKurikulum").attachEvent("onItemClick", simpanKurikulum);
        $$("refreshKurikulum").attachEvent("onItemClick", refreshKurikulum);

        $$("dataTableKurikulum").on_click.btnMK = function (e, id, trg) {
          data = $$("dataTableKurikulum").getItem(id);
          $$("judulKurikulumMk").setHTML(
            "Mata Kuliah Kurikulum: <b>" +
              data.nm_kurikulum_sp +
              "</b> (" +
              data.vnm_lemb +
              ")",
          );
          $$("id_sms_mk").setValue(data.xid_sms);
          $$("id_kurikulum_sp_mk").setValue(data.xid_kurikulum_sp);
          $$("dataTableMataKuliahKurikulum").clearAll();
          $$("dataTableMataKuliahKurikulum").load(
            "sopingi/mata_kuliah_kurikulum/tampil/" +
              wSia.apiKey +
              "/" +
              data.xid_kurikulum_sp,
          );
          $$("viewMataKuliahKurikulum").show();
          return false;
        };

        $$("kembaliKurikulum").attachEvent("onItemClick", function () {
          $$("masterKurikulum").back();
        });

        //MATA KULIAH KURIKULUM
        webix.ui({
          view: "window",
          id: "winMataKuliahKurikulum",
          width: 500,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Mata Kuliah Kurikulum",
                id: "judulWinMataKuliahKurikulum",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winMataKuliahKurikulum').hide();",
              },
            ],
          },
          body: webix.copy(formMataKuliahKurikulum),
        });

        $$("tambahMataKuliahKurikulum").attachEvent("onItemClick", function () {
          $$("winMataKuliahKurikulum").show();
          $$("judulWinMataKuliahKurikulum").setValue(
            "Tambah Mata Kuliah Kurikulum",
          );
          $$("formMataKuliahKurikulum").setValues({
            aksi: "tambah",
            xid_kurikulum_sp: $$("id_kurikulum_sp_mk").getValue(),
          });
          $$("id_mk").define(
            "options",
            "sopingi/mata_kuliah_kurikulum/pilihProdi/" +
              wSia.apiKey +
              "/" +
              $$("id_sms_mk").getValue(),
          );
          $$("id_mk").refresh();
        });

        $$("ubahMataKuliahKurikulum").attachEvent("onItemClick", function () {
          if ($$("dataTableMataKuliahKurikulum").getSelectedId() != null) {
            data = $$("dataTableMataKuliahKurikulum").getSelectedItem();
            $$("winMataKuliahKurikulum").show();
            $$("id_mk").define(
              "options",
              "sopingi/mata_kuliah_kurikulum/pilihProdi/" +
                wSia.apiKey +
                "/" +
                $$("id_sms_mk").getValue(),
            );
            $$("id_mk").refresh();
            $$("judulWinMataKuliahKurikulum").setValue(
              "Ubah Mata Kuliah Kurikulum",
            );
            data.aksi = "ubah";
            data.xid_kurikulum_sp = $$("id_kurikulum_sp_mk").getValue();
            data.a_wajib = parseInt(data.a_wajib);
            $$("formMataKuliahKurikulum").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusMataKuliahKurikulum").attachEvent("onItemClick", function () {
          if ($$("dataTableMataKuliahKurikulum").getSelectedId() != null) {
            data = $$("dataTableMataKuliahKurikulum").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusMataKuliahKurikulum(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanMataKuliahKurikulum").attachEvent(
          "onItemClick",
          simpanMataKuliahKurikulum,
        );
        $$("refreshMataKuliahKurikulum").attachEvent(
          "onItemClick",
          refreshMataKuliahKurikulum,
        );
      } else if (hal == "master-kelas.html") {
        masterSiakadKelas.el = halaman;
        masterSiakadKelas.render();

        webix.ui({
          view: "window",
          id: "winSiakadKelas",
          width: 400,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Kelas",
                id: "judulWinSiakadKelas",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winSiakadKelas').hide();",
              },
            ],
          },
          body: webix.copy(formSiakadKelas),
        });

        $$("tambahSiakadKelas").attachEvent("onItemClick", function () {
          $$("winSiakadKelas").show();
          $$("judulWinSiakadKelas").setValue("Tambah Kelas");
          $$("formSiakadKelas").setValues({
            id_nm_kls: webix.uid(),
            aksi: "tambah",
          });
        });

        $$("ubahSiakadKelas").attachEvent("onItemClick", function () {
          if ($$("dataTableSiakadKelas").getSelectedId() != null) {
            $$("angkatan").define(
              "options",
              "sopingi/siakad_angkatan/pilih/" +
                wSia.apiKey +
                "/" +
                Math.random(),
            );
            $$("angkatan").refresh();
            data = $$("dataTableSiakadKelas").getSelectedItem();
            $$("winSiakadKelas").show();
            $$("judulWinSiakadKelas").setValue("Ubah Kelas");
            data.aksi = "ubah";
            $$("formSiakadKelas").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusSiakadKelas").attachEvent("onItemClick", function () {
          if ($$("dataTableSiakadKelas").getSelectedId() != null) {
            data = $$("dataTableSiakadKelas").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusSiakadKelas(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanSiakadKelas").attachEvent("onItemClick", simpanSiakadKelas);
        $$("refreshSiakadKelas").attachEvent("onItemClick", refreshSiakadKelas);
      } else if (hal == "import-mahasiswa.html") {
        importMahasiswa.el = halaman;
        importMahasiswa.render();

        var angkatan = "-";

        webix.ui({
          view: "window",
          id: "winMahasiswaBaruMigrasi",
          width: 900,
          height: 600,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Pendidikan Mahasiswa Migrasi",
                id: "judulWinMahasiswaBaruMigrasi",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winMahasiswaBaruMigrasi').hide();",
              },
            ],
          },
          body: webix.copy(mahasiswaBaruMigrasi),
        });

        $$("angkatanDU").attachEvent("onChange", function (baru, lama) {
          angkatan = baru;
          $$("dataTableMahasiswaDU").clearAll();
          $$("dataTableMahasiswaDU").load(
            "sopingi/pendaftar/tampil/" + wSia.apiKey + "/" + angkatan,
          );

          $$("dataTableMahasiswaBaru").clearAll();
          $$("dataTableMahasiswaBaru").load(
            "sopingi/mahasiswa/baru/" + wSia.apiKey + "/" + angkatan,
          );
        });

        $$("refreshMahasiswaDU").attachEvent("onItemClick", function () {
          $$("dataTableMahasiswaDU").clearAll();
          $$("dataTableMahasiswaDU").load(
            "sopingi/pendaftar/tampil/" + wSia.apiKey + "/" + angkatan,
          );
        });

        $$("refreshMahasiswaBaru").attachEvent("onItemClick", function () {
          $$("dataTableMahasiswaBaru").clearAll();
          $$("dataTableMahasiswaBaru").load(
            "sopingi/mahasiswa/baru/" + wSia.apiKey + "/" + angkatan,
          );
        });

        $$("importMahasiswaDU").attachEvent("onItemClick", function () {
          var du = $$("dataTableMahasiswaDU").serialize();
          var dataImport = { du: du, aksi: "import" };
          var dataKirim = JSON.stringify(dataImport);
          proses_tampil();
          webix
            .ajax()
            .post(
              "sopingi/mahasiswa/import/" + wSia.apiKey + "/" + Math.random(),
              dataKirim,
              {
                success: function (text, xml, xhr) {
                  proses_hide();
                  var hasil = JSON.parse(text);
                  if (hasil.berhasil) {
                    $$("dataTableMahasiswaDU").clearAll();
                    $$("dataTableMahasiswaDU").load(
                      "sopingi/pendaftar/tampil/" +
                        wSia.apiKey +
                        "/" +
                        angkatan,
                    );
                    $$("dataTableMahasiswaBaru").clearAll();
                    $$("dataTableMahasiswaBaru").load(
                      "sopingi/mahasiswa/baru/" + wSia.apiKey + "/" + angkatan,
                    );
                  } else {
                    webix.alert(hasil.pesan);
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
        });

        $$("pendidikanMahasiswaBaru").attachEvent("onItemClick", function () {
          $$("winMahasiswaBaruMigrasi").show();
        });

        $$("excelMahasiswaBaru").attachEvent("onItemClick", function () {
          window.open(
            "sopingi-excel/mahasiswa/belumnim/" + wSia.apiKey + "/" + angkatan,
            "_blank",
          );
        });

        $$("importExcelMahasiswaMigrasi").attachEvent(
          "onItemClick",
          function () {
            $$("uploaderExcelMahasiswaMigrasi").send(function (response) {
              if (response)
                webix.modalbox({
                  title: "Hasil Import",
                  buttons: ["Oke deh..."],
                  width: 750,
                  text: response.pesan + "Gagal Import:<br>" + response.gagal,
                });
            });
          },
        );
      } else if (hal == "master-mahasiswa.html") {
        masterMahasiswa.el = halaman;
        masterMahasiswa.render();

        //Panel Kiri Mahasiswa
        $$("menuMahasiswa").attachEvent("onItemClick", function (id) {
          if (id == "biodata_mahasiswa") {
            $$("viewMahasiswaDetail").show();
          } else if (id == "pendidikan_mahasiswa") {
            $$("viewMahasiswaPendidikan").show();
            dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            $$("dataTablePendidikan").clearAll();
            $$("dataTablePendidikan").load(
              "sopingi/pendidikan/tampil/" + wSia.apiKey + "/" + dataMhs.xid_pd,
            );
          } else if (id == "krs_mahasiswa") {
            $$("viewMahasiswaKRS").show();
            dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            $$("dataTableKrs").clearAll();
            $$("dataTableKrs").load(
              "sopingi/nilai/tampilKRS/" +
                wSia.apiKey +
                "/" +
                dataMhs.xid_reg_pd,
            );
          } else if (id == "khs_mahasiswa") {
            proses_tampil();
            $$("viewMahasiswaKHS").show();
            dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            webix.ajax().get(
              "sopingi/bobot_nilai/tampilSms/" +
                wSia.apiKey +
                "/" +
                dataMhs.xid_sms,
              {},
              {
                success: function (text, xml, xhr) {
                  proses_hide();
                  bobot = JSON.parse(text);
                  abobot = Object.values(bobot);

                  $$("dataTableKhs").clearAll();
                  $$("dataTableKhs").load(
                    "sopingi/nilai/tampilKHS/" +
                      wSia.apiKey +
                      "/" +
                      dataMhs.xid_reg_pd,
                  );
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
          } else if (id == "transkip_mahasiswa") {
            $$("viewMahasiswaTranskip").show();
            dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            $$("dataTableTranskip").clearAll();
            $$("dataTableTranskip").load(
              "sopingi/nilai/tampilTranskip/" +
                wSia.apiKey +
                "/" +
                dataMhs.xid_reg_pd,
            );
          } else if (id == "pass_mahasiswa") {
            $$("viewAkunMahasiswa").show();
            var dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            $$("nipdUbahPass").setValue(dataMhs.nipd);

            $$("fotoMhs").load(
              "sopingi/mahasiswa/foto/" + wSia.apiKey + "/" + dataMhs.nipd,
            );
            $$("kkMhs").load(
              "sopingi/mahasiswa/kk/" + wSia.apiKey + "/" + dataMhs.nipd,
            );
          }
        });

        //Mahasiswa Biodata
        /*
							$$("nm_ibu_kandung").attachEvent("onChange",function(baru,lama){
								$$('vnm_ibu_kandung').setValue(baru);
							});
							*/

        $$("tambahMahasiswa").attachEvent("onItemClick", function () {
          $$("formMahasiswaDetail").clear();
          $$("formMahasiswaDetail").setValues({
            kewarganegaraan: "Indonesia",
            aksi: "tambah",
          });
          $$("panelKiriMahasiswaDetail").hide();

          $$("judulMahasiswaDetail").setHTML("Tambah Mahasiswa");
          $$("masterMahasiswaDetail").show();
          $$("no_pend").enable();
        });

        $$("dataTableMahasiswa").on_click.btnMhsDetail = function (e, id, trg) {
          $$("dataTableMahasiswa").select(id);
          data = $$("dataTableMahasiswa").getItem(id);
          data.vnm_ibu_kandung = data.nm_ibu_kandung;
          data.aksi = "ubah";
          $$("formMahasiswaDetail").setValues(data);

          $$("menuMahasiswa").select("biodata_mahasiswa");
          $$("judulMahasiswaDetail").setHTML(
            "Detail Mahasiswa: <b>" + data.nm_pd + "</b> (" + data.nipd + ")",
          );
          $$("masterMahasiswaDetail").show();
          $$("viewMahasiswaDetail").show();
          $$("panelKiriMahasiswaDetail").show();
          $$("no_pend").disable();

          //wilayah
          $$("id_wil")
            .getList()
            .load(
              "sopingi/wilayah/tampil/" +
                wSia.apiKey +
                "/" +
                Math.random() +
                "?id=" +
                data.id_wil,
            );

          return false;
        };

        $$("kembaliMahasiswa").attachEvent("onItemClick", function () {
          $$("masterMahasiswa").back();
          //$$("panelKiriMahasiswaDetail").hide();
        });

        $$("simpanMahasiswa").attachEvent("onItemClick", simpanMahasiswa);
        $$("refreshMahasiswa").attachEvent("onItemClick", refreshMahasiswa);

        $$("hapusMahasiswa").attachEvent("onItemClick", function () {
          if ($$("dataTableMahasiswa").getSelectedId() != null) {
            data = $$("dataTableMahasiswa").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusMahasiswa(data.xid_pd, data.xid_reg_pd);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        //IMPORT MAHASISWA SPMB
        $$("importMahasiswa").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Import",
            cancel: "Tidak",
            text: "Yakin mau IMPORT data Mahasiswa dari Daftar Ulang ?",
            callback: function (jwb) {
              if (jwb) {
                proses_tampil();
                webix.ajax().get(
                  "sopingi/mahasiswa_import/import/" +
                    wSia.apiKey +
                    "/" +
                    Math.random(),
                  {},
                  {
                    success: function (text, xml, xhr) {
                      proses_hide();
                      var hasil = JSON.parse(text);
                      if (hasil.berhasil) {
                        refreshMahasiswa();
                        webix.alert(
                          hasil.pesan +
                            "<hr>Akun Default Mahasiswa<br>Username: NIM, Password: No Pendaftaran",
                        );
                      } else {
                        webix.alert({
                          title: "Gagal Import",
                          text: hasil.pesan,
                          type: "alert-error",
                        });
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
        });

        //Mahasiswa KRS
        webix.ui({
          view: "window",
          id: "winMhsKelasKuliah",
          width: 800,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Kelas Mata Kuliah",
                id: "judulMhsWinKelasKuliah",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winMhsKelasKuliah').hide();",
              },
            ],
          },
          body: webix.copy(formKRS),
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
            dataSudahKrs.aksi = "tampilKRS";
            dataSudahKrs.data = JSON.stringify(data);
            dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            proses_tampil();
            //console.log(JSON.stringify(dataSudahKrs));
            dataKirim = JSON.stringify(dataSudahKrs);
            webix
              .ajax()
              .post(
                "sopingi/kelas_kuliah/tampilKRS/" +
                  wSia.apiKey +
                  "/" +
                  dataMhs.xid_reg_pd,
                dataKirim,
                {
                  success: function (text, xml, xhr) {
                    proses_hide();
                    dataBelumKrs = JSON.parse(text);
                    $$("dataTableKelasPerkuliahan").clearAll();
                    $$("dataTableKelasPerkuliahan").define(
                      "data",
                      dataBelumKrs,
                    );
                    $$("dataTableKelasPerkuliahan").refresh();
                    $$("winMhsKelasKuliah").show();
                    $$("judulMhsWinKelasKuliah").setValue(
                      "Tambah Kelas Mata Kuliah",
                    );
                    $$("formKRS").setValues({
                      aksi: "tambah",
                    });
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
          } else {
            webix.alert({
              title: "Informasi",
              text: "Tidak dapat menambah SKS lagi..<br>(Maksimal 24 SKS)",
              type: "alert-error",
            });
          }
        });

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
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("refreshKRS").attachEvent("onItemClick", function () {
          dataMhs = $$("dataTableMahasiswa").getSelectedItem();
          $$("dataTableKrs").clearAll();
          $$("dataTableKrs").load(
            "sopingi/nilai/tampilKRS/" + wSia.apiKey + "/" + dataMhs.xid_reg_pd,
          );
        });

        $$("unduhKRS").attachEvent("onItemClick", unduhKRS);

        //MAHASISWA KHS
        $$("simpanKhs").attachEvent("onItemClick", simpanKHS);
        $$("refreshKhs").attachEvent("onItemClick", refreshKHS);
        $$("unduhKhs").attachEvent("onItemClick", unduhKHS);

        //MAHASISWA TRANSKIP
        $$("refreshTranskip").attachEvent("onItemClick", refreshTranskip);
        $$("unduhTranskip").attachEvent("onItemClick", unduhTranskip);

        //MAHASISWA PASS
        $$("simpanAkunMahasiswa").attachEvent(
          "onItemClick",
          simpanAkunMahasiswa,
        );

        //BULK UPDATE PA
        webix.ui({
          view: "window",
          id: "winBulkPA",
          width: 500,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              { view: "label", label: "Bulk Update Pembimbing Akademik" },
              { view: "icon", icon: "close", click: "$$('winBulkPA').hide();" },
            ],
          },
          body: webix.copy(formBulkPA),
        });

        $$("bulkMahasiswaPA").attachEvent("onItemClick", function () {
          $$("winBulkPA").show();
        });

        $$("prosesBulkPA").attachEvent("onItemClick", function () {
          bulkUbahPA();
        });
      } else if (hal == "buku-induk.html") {
        masterBukuInduk.el = halaman;
        masterBukuInduk.render();

        $$("refreshSms").attachEvent("onItemClick", refreshSms);

        $$("downloadBukuInduk").attachEvent("onItemClick", function () {
          if ($$("dataTableSms").getSelectedId() != null) {
            data = $$("dataTableSms").getSelectedItem();
            //alert(JSON.stringify(data));
            window.open(
              wSia.domain +
                "/baak/sopingi/buku_induk/download/" +
                wSia.apiKey +
                "/" +
                data.kode_prodi +
                "_" +
                wSia.ta,
              "_blank",
            );
          } else {
            peringatan("Informasi", "Belum ada program studi yang dipilih");
          }
        });
      } else if (hal == "hak-akses-krs.html") {
        halamanHakAksesKRS.el = halaman;
        halamanHakAksesKRS.render();

        webix.ui({
          view: "window",
          id: "winHakAkses",
          width: 900,
          height: 550,
          position: "center",
          modal: true,
          scroll: "y",
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Hak Akses Mahasiswa",
                width: 250,
              },
              { template: "", borderless: true },
              {
                view: "icon",
                icon: "close",
                click: "$$('winHakAkses').hide();",
              },
            ],
          },
          body: webix.copy(formModalMhs),
        });

        $$("tambahHakAkses").attachEvent("onItemClick", function () {
          refreshModalMhs();
          $$("winHakAkses").show();
        });

        $$("hapusHakAkses").attachEvent("onItemClick", function () {
          if ($$("dataTableHakAkses").getSelectedId() != null) {
            data = $$("dataTableHakAkses").getSelectedItem();
            data.aksi = "mahasiswaHapusAkses";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Anda yakin akan menghapus data ini ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusHakAkses(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("refreshHakAkses").attachEvent("onItemClick", refreshHakAkses);

        $$("refreshModalMhs").attachEvent("onItemClick", refreshModalMhs);
        $$("tambahModalMhs").attachEvent("onItemClick", tambahModalMhs);
      } else if (hal == "dosen.html") {
        masterDosen.el = halaman;
        masterDosen.render();

        webix.ui({
          view: "window",
          id: "winDosen",
          width: 450,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              { view: "label", label: "Tambah Dosen", id: "judulWinDosen" },
              { view: "icon", icon: "close", click: "$$('winDosen').hide();" },
            ],
          },
          body: webix.copy(formDosen),
        });

        $$("tambahDosen").attachEvent("onItemClick", function () {
          $$("winDosen").show();
          $$("judulWinDosen").setValue("Tambah Dosen");
          $$("formDosen").setValues({
            xid_ptk: webix.uid(),
            aksi: "tambah",
          });
          $$("nidn").enable();
        });

        $$("ubahDosen").attachEvent("onItemClick", function () {
          if ($$("dataTableDosen").getSelectedId() != null) {
            data = $$("dataTableDosen").getSelectedItem();
            $$("winDosen").show();
            $$("judulWinDosen").setValue("Ubah Dosen");
            data.aksi = "ubah";
            $$("formDosen").setValues(data);
            $$("nidn").disable();
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusDosen").attachEvent("onItemClick", function () {
          if ($$("dataTableDosen").getSelectedId() != null) {
            data = $$("dataTableDosen").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusDosen(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanDosen").attachEvent("onItemClick", simpanDosen);
        $$("refreshDosen").attachEvent("onItemClick", refreshDosen);
      } else if (hal == "penugasan-dosen.html") {
        masterPenugasanDosen.el = halaman;
        masterPenugasanDosen.render();

        webix.ui({
          view: "window",
          id: "winPenugasanDosen",
          width: 400,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Penugasan Dosen",
                id: "judulWinPenugasanDosen",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winPenugasanDosen').hide();",
              },
            ],
          },
          body: webix.copy(formPenugasanDosen),
        });

        $$("tambahPenugasanDosen").attachEvent("onItemClick", function () {
          webix
            .ajax()
            .get(
              "sopingi/dosen/pilih/" + wSia.apiKey + "/" + Math.random(),
              {},
              function (text, xml, xhr) {
                id_ptk = JSON.parse(text);
                optionsId_ptk = {
                  filter: function (item, value) {
                    return (
                      item.value
                        .toString()
                        .toLowerCase()
                        .indexOf(value.toLowerCase()) != -1
                    );
                  },
                  body: {
                    template: "#value#",
                    data: id_ptk,
                  },
                };

                $$("xid_ptk").define("options", optionsId_ptk);
                $$("xid_ptk").refresh();
                $$("xid_ptk").enable();
              },
            );

          $$("winPenugasanDosen").show();
          $$("judulWinPenugasanDosen").setValue("Tambah Penugasan Dosen");
          $$("formPenugasanDosen").setValues({
            xid_reg_ptk: webix.uid(),
            aksi: "tambah",
          });
        });

        $$("ubahPenugasanDosen").attachEvent("onItemClick", function () {
          webix
            .ajax()
            .get(
              "sopingi/dosen/pilih/" + wSia.apiKey + "/" + Math.random(),
              {},
              function (text, xml, xhr) {
                id_ptk = JSON.parse(text);
                optionsId_ptk = {
                  filter: function (item, value) {
                    return (
                      item.value
                        .toString()
                        .toLowerCase()
                        .indexOf(value.toLowerCase()) != -1
                    );
                  },
                  body: {
                    template: "#value#",
                    data: id_ptk,
                  },
                };

                $$("xid_ptk").define("options", optionsId_ptk);
                $$("xid_ptk").refresh();
                $$("xid_ptk").disable();
              },
            );

          if ($$("dataTablePenugasanDosen").getSelectedId() != null) {
            data = $$("dataTablePenugasanDosen").getSelectedItem();
            $$("winPenugasanDosen").show();
            $$("judulWinPenugasanDosen").setValue("Ubah Penugasan Dosen");
            data.aksi = "ubah";
            $$("formPenugasanDosen").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusPenugasanDosen").attachEvent("onItemClick", function () {
          if ($$("dataTablePenugasanDosen").getSelectedId() != null) {
            data = $$("dataTablePenugasanDosen").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusPenugasanDosen(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanPenugasanDosen").attachEvent(
          "onItemClick",
          simpanPenugasanDosen,
        );
        $$("refreshPenugasanDosen").attachEvent(
          "onItemClick",
          refreshPenugasanDosen,
        );
      } else if (hal == "kelas-perkuliahan.html") {
        masterKelasKuliah.el = halaman;
        masterKelasKuliah.render();

        webix.ui({
          view: "window",
          id: "winKelasKuliah",
          width: 800,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Kelas Perkuliahan",
                id: "judulWinKelasKuliah",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winKelasKuliah').hide();",
              },
            ],
          },
          body: webix.copy(formKelasKuliah),
        });

        $$("tambahKelasKuliah").attachEvent("onItemClick", function () {
          $$("winKelasKuliah").show();
          $$("judulWinKelasKuliah").setValue("Tambah Kelas Kuliah");
          $$("formKelasKuliah").setValues({
            xid_kls: webix.uid(),
            aksi: "tambah",
          });
        });

        $$("ubahKelasKuliah").attachEvent("onItemClick", function () {
          if ($$("dataTableKelas").getSelectedId() != null) {
            data = $$("dataTableKelas").getSelectedItem();
            $$("winKelasKuliah").show();
            $$("judulWinKelasKuliah").setValue("Ubah Kelas Kuliah");
            data.aksi = "ubah";
            data.id_mk = data.xid_mk;
            $$("formKelasKuliah").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusKelasKuliah").attachEvent("onItemClick", function () {
          if ($$("dataTableKelas").getSelectedId() != null) {
            data = $$("dataTableKelas").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusKelasKuliah(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("id_sms").attachEvent("onChange", function (baru, lama) {
          $$("id_kurikulum_sp").define(
            "options",
            "sopingi/kurikulum/pilih/" + wSia.apiKey + "/" + baru,
          );
          $$("id_kurikulum_sp").refresh();
        });

        $$("id_kurikulum_sp").attachEvent("onChange", function (baru, lama) {
          webix
            .ajax()
            .get(
              "sopingi/mata_kuliah_kurikulum/pilih/" + wSia.apiKey + "/" + baru,
              {},
              function (text, xml, xhr) {
                id_mk = JSON.parse(text);
                optionsId_mk = {
                  filter: function (item, value) {
                    return (
                      item.value
                        .toString()
                        .toLowerCase()
                        .indexOf(value.toLowerCase()) != -1
                    );
                  },
                  body: {
                    template: "#value#",
                    data: id_mk,
                  },
                };

                $$("id_mk").define("options", optionsId_mk);
                $$("id_mk").refresh();
              },
            );
        });

        $$("simpanKelasKuliah").attachEvent("onItemClick", simpanKelasKuliah);
        $$("refreshKelasKuliah").attachEvent("onItemClick", refreshKelasKuliah);

        $$("dataTableKelas").on_click.btnMhsDetail = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          dataPanelKelas = [
            { judul: "Program Studi:", konten: data.prodi },
            { judul: "Semester:", konten: data.smt },
            { judul: "Mata Kuliah:", konten: data.kode_mk + "-" + data.nm_mk },
            { judul: "Nama Kelas:", konten: data.nm_kls },
          ];
          $$("panelKelasKuliah").clearAll();
          $$("panelKelasKuliah").define("data", dataPanelKelas);
          $$("panelKelasKuliah").refresh();
          $$("xid_klsMhs").setValue(data.xid_kls);

          proses_tampil();
          webix.ajax().get(
            "sopingi/bobot_nilai/tampilSms/" + wSia.apiKey + "/" + data.id_sms,
            {},
            {
              success: function (text, xml, xhr) {
                proses_hide();
                bobot = JSON.parse(text);
                abobot = Object.values(bobot);

                $$("dataTableMahasiswaKelas").clearAll();
                $$("dataTableMahasiswaKelas").load(
                  "sopingi/nilai/tampil/" + wSia.apiKey + "/" + data.xid_kls,
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
        };

        $$("dataTableKelas").on_click.btnDosenDetail = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          dataPanelKelas = [
            { judul: "Program Studi:", konten: data.prodi },
            { judul: "Semester:", konten: data.smt },
            { judul: "Mata Kuliah:", konten: data.kode_mk + "-" + data.nm_mk },
            { judul: "Nama Kelas:", konten: data.nm_kls },
          ];
          $$("panelKelasKuliah2").clearAll();
          $$("panelKelasKuliah2").define("data", dataPanelKelas);
          $$("panelKelasKuliah2").refresh();
          $$("xid_klsDosen").setValue(data.xid_kls);

          $$("dataTableDosenKelas").clearAll();
          $$("dataTableDosenKelas").load(
            "sopingi/ajar_dosen/tampil/" + wSia.apiKey + "/" + data.xid_kls,
          );
          $$("viewDosenKelas").show();
          return false;
        };

        $$("dataTableKelas").on_click.btnMhsAbsen = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          unduhAbsenMhs(data.xid_kls);
          return false;
        };

        $$("dataTableKelas").on_click.btnDosenAbsen = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          unduhAbsenDosenPDF(data.xid_kls);
          return false;
        };

        $$("dataTableKelas").on_click.btnKartuUTS = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          unduhUjianKartuUTSPDF(data.xid_kls);
          return false;
        };

        $$("dataTableKelas").on_click.btnKartuUAS = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          unduhUjianKartuUASPDF(data.xid_kls);
          return false;
        };

        $$("dataTableKelas").on_click.btnAbsenUTS = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          unduhUjianAbsenUTSPDF(data.xid_kls);
          return false;
        };

        $$("dataTableKelas").on_click.btnAbsenUAS = function (e, id, trg) {
          $$("dataTableKelas").select(id);
          data = $$("dataTableKelas").getItem(id);
          unduhUjianAbsenUASPDF(data.xid_kls);
          return false;
        };

        $$("kembaliKelasKuliah").attachEvent("onItemClick", function () {
          $$("masterKelasKuliah").back();
        });

        $$("kembaliKelasKuliah2").attachEvent("onItemClick", function () {
          $$("masterKelasKuliah").back();
        });

        /* MAHASISWA KELAS */
        $$("refreshMhsKelas").attachEvent("onItemClick", refreshMhsKelas);
        $$("unduhAbsenMhs").attachEvent("onItemClick", function () {
          ((id = $$("xid_klsMhs").getValue()), unduhAbsenMhs(id));
        });
        $$("simpanKhsKelas").attachEvent("onItemClick", simpanKHSKelas);

        /* DOSEN KELAS */
        webix.ui({
          view: "window",
          id: "winDosenKelas",
          width: 600,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Dosen Pengajar",
                id: "judulWinDosenKelas",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winDosenKelas').hide();",
              },
            ],
          },
          body: webix.copy(formDosenKelas),
        });

        $$("tambahDosenKelas").attachEvent("onItemClick", function () {
          var dataKelas = $$("dataTableKelas").getSelectedItem();
          webix
            .ajax()
            .get(
              "sopingi/dosen_pt/pilih/" + wSia.apiKey + "/" + dataKelas.id_sms,
              {},
              function (text, xml, xhr) {
                id_reg_ptk = JSON.parse(text);
                optionsId_reg_ptk = {
                  filter: function (item, value) {
                    return (
                      item.value
                        .toString()
                        .toLowerCase()
                        .indexOf(value.toLowerCase()) != -1
                    );
                  },
                  body: {
                    template: "#value#",
                    data: id_reg_ptk,
                  },
                };

                $$("xid_reg_ptk").define("options", optionsId_reg_ptk);
                $$("xid_reg_ptk").refresh();
              },
            );

          $$("winDosenKelas").show();
          $$("judulWinDosenKelas").setValue("Tambah Dosen Pengajar");
          $$("formDosenKelas").setValues({
            xid_ajar: webix.uid(),
            xid_klsAjarDosen: $$("xid_klsDosen").getValue(),
            aksi: "tambah",
          });
        });

        $$("ubahDosenKelas").attachEvent("onItemClick", function () {
          webix
            .ajax()
            .get(
              "sopingi/dosen_pt/pilih/" + wSia.apiKey + "/" + Math.random(),
              {},
              function (text, xml, xhr) {
                id_reg_ptk = JSON.parse(text);
                optionsId_reg_ptk = {
                  filter: function (item, value) {
                    return (
                      item.value
                        .toString()
                        .toLowerCase()
                        .indexOf(value.toLowerCase()) != -1
                    );
                  },
                  body: {
                    template: "#value#",
                    data: id_reg_ptk,
                  },
                };

                $$("xid_reg_ptk").define("options", optionsId_reg_ptk);
                $$("xid_reg_ptk").refresh();
              },
            );

          if ($$("dataTableDosenKelas").getSelectedId() != null) {
            data = $$("dataTableDosenKelas").getSelectedItem();
            $$("winDosenKelas").show();
            $$("judulWinDosenKelas").setValue("Ubah Dosen Pengajar");
            data.aksi = "ubah";
            data.xid_klsAjarDosen = $$("xid_klsDosen").getValue();
            $$("formDosenKelas").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusDosenKelas").attachEvent("onItemClick", function () {
          if ($$("dataTableDosenKelas").getSelectedId() != null) {
            data = $$("dataTableDosenKelas").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusDosenKelas(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("dataTableDosenKelas").on_click.btnDosenAbsen = function (
          e,
          id,
          trg,
        ) {
          $$("dataTableDosenKelas").select(id);
          data = $$("dataTableDosenKelas").getItem(id);
          unduhAbsenDosen(data.xid_ajar);
          return false;
        };

        $$("simpanDosenKelas").attachEvent("onItemClick", simpanDosenKelas);
        $$("refreshDosenKelas").attachEvent("onItemClick", refreshDosenKelas);
      } else if (hal == "dosen.html") {
        masterDosen.el = halaman;
        masterDosen.render();
      } else if (hal == "kartu-ujian-murni.html") {
        masterKartuUjianMurni.el = halaman;
        masterKartuUjianMurni.render();

        $$("refreshKartuUjianMurni").attachEvent(
          "onItemClick",
          refreshKartuUjianMurni,
        );
        $$("unduhKartuUjianMurni").attachEvent(
          "onItemClick",
          unduhKartuUjianMurni,
        );

        $$("dataTableKartuUjianMurni").on_click.cetakUTS = function (
          e,
          id,
          trg,
        ) {
          $("#cekUTS").prop("checked", false);
          $("#cekUAS").prop("checked", false);
          $$("dataTableKartuUjianMurni").eachRow(function (baris) {
            record = this.getItem(baris);
            record["kartuUAS"] = 0;
            $$("dataTableKartuUjianMurni").updateItem(baris, record);
          });
        };

        $$("dataTableKartuUjianMurni").on_click.cetakUAS = function (
          e,
          id,
          trg,
        ) {
          $("#cekUAS").prop("checked", false);
          $("#cekUTS").prop("checked", false);
          $$("dataTableKartuUjianMurni").eachRow(function (baris) {
            record = this.getItem(baris);
            record["kartuUTS"] = 0;
            $$("dataTableKartuUjianMurni").updateItem(baris, record);
          });
        };

        $("#cekUTS").click(function () {
          if ($("#cekUTS").is(":checked")) {
            $("#cekUAS").prop("checked", false);
            $$("dataTableKartuUjianMurni").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUTS"] = 1;
              record["kartuUAS"] = 0;
              $$("dataTableKartuUjianMurni").updateItem(baris, record);
            });
          } else {
            $$("dataTableKartuUjianMurni").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUTS"] = 0;
              $$("dataTableKartuUjianMurni").updateItem(baris, record);
            });
          }
        });

        $("#cekUAS").click(function () {
          if ($("#cekUAS").is(":checked")) {
            $("#cekUTS").prop("checked", false);
            $$("dataTableKartuUjianMurni").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUAS"] = 1;
              record["kartuUTS"] = 0;
              $$("dataTableKartuUjianMurni").updateItem(baris, record);
            });
          } else {
            $$("dataTableKartuUjianMurni").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUAS"] = 0;
              $$("dataTableKartuUjianMurni").updateItem(baris, record);
            });
          }
        });
      } else if (hal == "kartu-ujian-transfer.html") {
        masterKartuUjianTransfer.el = halaman;
        masterKartuUjianTransfer.render();

        $$("refreshKartuUjianTransfer").attachEvent(
          "onItemClick",
          refreshKartuUjianTransfer,
        );
        $$("unduhKartuUjianTransfer").attachEvent(
          "onItemClick",
          unduhKartuUjianTransfer,
        );

        $$("dataTableKartuUjianTransfer").on_click.cetakUTS = function (
          e,
          id,
          trg,
        ) {
          $("#cekUTS").prop("checked", false);
          $("#cekUAS").prop("checked", false);
          $$("dataTableKartuUjianTransfer").eachRow(function (baris) {
            record = this.getItem(baris);
            record["kartuUAS"] = 0;
            $$("dataTableKartuUjianTransfer").updateItem(baris, record);
          });
        };

        $$("dataTableKartuUjianTransfer").on_click.cetakUAS = function (
          e,
          id,
          trg,
        ) {
          $("#cekUAS").prop("checked", false);
          $("#cekUTS").prop("checked", false);
          $$("dataTableKartuUjianTransfer").eachRow(function (baris) {
            record = this.getItem(baris);
            record["kartuUTS"] = 0;
            $$("dataTableKartuUjianTransfer").updateItem(baris, record);
          });
        };

        $("#cekUTS").click(function () {
          if ($("#cekUTS").is(":checked")) {
            $("#cekUAS").prop("checked", false);
            $$("dataTableKartuUjianTransfer").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUTS"] = 1;
              record["kartuUAS"] = 0;
              $$("dataTableKartuUjianTransfer").updateItem(baris, record);
            });
          } else {
            $$("dataTableKartuUjianTransfer").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUTS"] = 0;
              $$("dataTableKartuUjianTransfer").updateItem(baris, record);
            });
          }
        });

        $("#cekUAS").click(function () {
          if ($("#cekUAS").is(":checked")) {
            $("#cekUTS").prop("checked", false);
            $$("dataTableKartuUjianTransfer").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUAS"] = 1;
              record["kartuUTS"] = 0;
              $$("dataTableKartuUjianTransfer").updateItem(baris, record);
            });
          } else {
            $$("dataTableKartuUjianTransfer").eachRow(function (baris) {
              record = this.getItem(baris);
              record["kartuUAS"] = 0;
              $$("dataTableKartuUjianTransfer").updateItem(baris, record);
            });
          }
        });
      } else if (hal == "khs-murni.html") {
        masterKHSMurni.el = halaman;
        masterKHSMurni.render();

        $$("refreshKHSMurni").attachEvent("onItemClick", refreshKHSMurni);
        $$("unduhKHSMurni").attachEvent("onItemClick", unduhKHSMurni);

        $$("dataTableKHSMurni").on_click.cetakKHS = function (e, id, trg) {
          $("#cekKHS").prop("checked", false);
          $$("dataTableKHSMurni").eachRow(function (baris) {
            record = this.getItem(baris);
            $$("dataTableKHSMurni").updateItem(baris, record);
          });
        };

        $("#cekKHS").click(function () {
          if ($("#cekKHS").is(":checked")) {
            $$("dataTableKHSMurni").eachRow(function (baris) {
              record = this.getItem(baris);
              record["khs"] = 1;
              $$("dataTableKHSMurni").updateItem(baris, record);
            });
          } else {
            $$("dataTableKHSMurni").eachRow(function (baris) {
              record = this.getItem(baris);
              record["khs"] = 0;
              $$("dataTableKHSMurni").updateItem(baris, record);
            });
          }
        });
      } else if (hal == "khs-transfer.html") {
        masterKHSTransfer.el = halaman;
        masterKHSTransfer.render();

        $$("refreshKHSTransfer").attachEvent("onItemClick", refreshKHSTransfer);
        $$("unduhKHSTransfer").attachEvent("onItemClick", unduhKHSTransfer);

        $$("dataTableKHSTransfer").on_click.cetakKHS = function (e, id, trg) {
          $("#cekKHS").prop("checked", false);
          $$("dataTableKHSTransfer").eachRow(function (baris) {
            record = this.getItem(baris);
            $$("dataTableKHSTransfer").updateItem(baris, record);
          });
        };

        $("#cekKHS").click(function () {
          if ($("#cekKHS").is(":checked")) {
            $$("dataTableKHSTransfer").eachRow(function (baris) {
              record = this.getItem(baris);
              record["khs"] = 1;
              $$("dataTableKHSTransfer").updateItem(baris, record);
            });
          } else {
            $$("dataTableKHSTransfer").eachRow(function (baris) {
              record = this.getItem(baris);
              record["khs"] = 0;
              $$("dataTableKHSTransfer").updateItem(baris, record);
            });
          }
        });
      } else if (hal == "mahasiswa-keluar.html") {
        masterMahasiswaKeluar.el = halaman;
        masterMahasiswaKeluar.render();

        webix.ui({
          view: "window",
          id: "winMahasiswaKeluar",
          width: 600,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Dosen Pengajar",
                id: "judulWinMahasiswaKeluar",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winMahasiswaKeluar').hide();",
              },
            ],
          },
          body: webix.copy(formMahasiswaKeluar),
        });

        $$("tambahMahasiswaKeluar").attachEvent("onItemClick", function () {
          /*
									webix.ajax().get("sopingi/mahasiswa/pilih/"+wSia.apiKey+"/"+Math.random(),{}, function(text, xml, xhr){
										id_reg_pd=JSON.parse(text);
										optionsId_reg_pd= {
										    filter:function(item, value){
										       return (item.value.toString().toLowerCase().indexOf(value.toLowerCase()) != -1)
										    },
										    body:{
										        template:"#value#",
										        data:id_reg_pd
										    }
										};
									
										$$("xid_reg_pd").define("options",optionsId_reg_pd);
										$$("xid_reg_pd").refresh();
									});
									*/

          //17-9-2019
          $$("xid_reg_pd").define("options", {
            keyPressTimeout: 500,
            body: {
              dataFeed:
                "sopingi/mahasiswa/pilih/" + wSia.apiKey + "/" + Math.random(),
              ready: function () {
                this.select(this.data.getFirstId());
              },
            },
          });

          $$("xid_reg_pd").refresh();

          $$("winMahasiswaKeluar").show();
          $$("judulWinMahasiswaKeluar").setValue(
            "Tambah Mahasiswa Lulus/Keluar",
          );
          $$("formMahasiswaKeluar").setValues({
            aksi: "ubahJenisKeluar",
          });

          $$("xid_reg_pd").enable();
        });

        $$("ubahMahasiswaKeluar").attachEvent("onItemClick", function () {
          if ($$("dataTableMahasiswaKeluar").getSelectedId() != null) {
            data = $$("dataTableMahasiswaKeluar").getSelectedItem();

            webix
              .ajax()
              .get(
                "sopingi/mahasiswa/pilihUbah/" +
                  wSia.apiKey +
                  "/" +
                  data.xid_reg_pd,
                {},
                function (text, xml, xhr) {
                  id_reg_pd = JSON.parse(text);
                  optionsId_reg_pd = {
                    filter: function (item, value) {
                      return (
                        item.value
                          .toString()
                          .toLowerCase()
                          .indexOf(value.toLowerCase()) != -1
                      );
                    },
                    body: {
                      template: "#value#",
                      data: id_reg_pd,
                    },
                  };

                  $$("xid_reg_pd").define("options", optionsId_reg_pd);
                  $$("xid_reg_pd").refresh();
                },
              );

            $$("winMahasiswaKeluar").show();
            $$("judulWinMahasiswaKeluar").setValue(
              "Ubah Mahasiswa Lulus/Kelua",
            );
            data.aksi = "ubahJenisKeluar";
            $$("formMahasiswaKeluar").setValues(data);
            $$("xid_reg_pd").disable();
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusMahasiswaKeluar").attachEvent("onItemClick", function () {
          if ($$("dataTableMahasiswaKeluar").getSelectedId() != null) {
            data = $$("dataTableMahasiswaKeluar").getSelectedItem();
            data.aksi = "hapusJenisKeluar";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusMahasiswaKeluar(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanMahasiswaKeluar").attachEvent(
          "onItemClick",
          simpanMahasiswaKeluar,
        );
        $$("refreshMahasiswaKeluar").attachEvent(
          "onItemClick",
          refreshMahasiswaKeluar,
        );

        $$("excelMahasiswaKeluar").attachEvent("onItemClick", function () {
          var id_smt = $$("nm_smt").getValues().id_smt;
          var nm_smt = $$("nm_smt").getValues().nm_smt.replace("/", "-");
          webix.toExcel($$("dataTableMahasiswaKeluar"), {
            filename: "Mahasiswa Lulus-Keluar " + id_smt,
            name: nm_smt,
            spans: true,
            styles: true,
            columns: {
              index: true,
              nipd: true,
              nm_pd: true,
              jk: true,
              vnm_lemb: true,
              angkatan: true,
              ket_keluar: true,
              tgl_keluar: true,
              sk_yudisium: { header: "SK Yudisium" },
              tgl_sk_yudisium: { header: "Tgl SK Yudisium" },
              no_seri_ijazah: { header: "No Seri Ijazah" },
              judul_skripsi: { header: "Judul Tugas Akhir" },
              id_smt: { header: "SMT Lulus" },
              ips: { header: "IPS" },
              sks_smt: { header: "SKS Semester" },
              ipk: { header: "IPK" },
              sks_total: { header: "SKS Total" },
            },
          });
        });
      } else if (hal == "semester.html") {
        masterSemester.el = halaman;
        masterSemester.render();

        webix.ui({
          view: "window",
          id: "winSemester",
          width: 400,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Tahun Akademik",
                id: "judulWinSemester",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winSemester').hide();",
              },
            ],
          },
          body: webix.copy(formSemester),
        });

        $$("dataTableSemester").on_click.btnSemesterAktif = function (
          e,
          id,
          trg,
        ) {
          $$("dataTableSemester").select(id);
          data = $$("dataTableSemester").getItem(id);

          data.aksi = "statusAktif";
          webix.confirm({
            title: "Konfirmasi",
            ok: "Ya",
            cancel: "Tidak",
            text: "Yakin mengaktifkan semester:<br>" + data.nm_smt + " ?",
            callback: function (jwb) {
              if (jwb) {
                statusSemester(data);
              }
            },
          });

          return false;
        };

        $$("tambahSemester").attachEvent("onItemClick", function () {
          $$("winSemester").show();
          $$("judulWinSemester").setValue("Tambah Tahun Akademik");
          $$("formSemester").setValues({
            aksi: "tambah",
          });
        });

        $$("hapusSemester").attachEvent("onItemClick", function () {
          if ($$("dataTableSemester").getSelectedId() != null) {
            data = $$("dataTableSemester").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusSemester(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanSemester").attachEvent("onItemClick", simpanSemester);
        $$("refreshSemester").attachEvent("onItemClick", refreshSemester);
      } else if (hal == "bobot-nilai.html") {
        masterBobotNilai.el = halaman;
        masterBobotNilai.render();

        webix.ui({
          view: "window",
          id: "winBobotNilai",
          width: 400,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Bobot Nilai",
                id: "judulWinBobotNilai",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winBobotNilai').hide();",
              },
            ],
          },
          body: webix.copy(formBobotNilai),
        });

        $$("tambahBobotNilai").attachEvent("onItemClick", function () {
          $$("winBobotNilai").show();
          $$("judulWinBobotNilai").setValue("Tambah Bobot Nilai");
          $$("formBobotNilai").setValues({
            aksi: "tambah",
          });
        });

        $$("ubahBobotNilai").attachEvent("onItemClick", function () {
          if ($$("dataTableBobotNilai").getSelectedId() != null) {
            data = $$("dataTableBobotNilai").getSelectedItem();
            $$("winBobotNilai").show();
            $$("judulWinBobotNilai").setValue("Ubah Bobot Nilai");
            data.aksi = "ubah";
            $$("formBobotNilai").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusBobotNilai").attachEvent("onItemClick", function () {
          if ($$("dataTableBobotNilai").getSelectedId() != null) {
            data = $$("dataTableBobotNilai").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusBobotNilai(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanBobotNilai").attachEvent("onItemClick", simpanBobotNilai);
        $$("refreshBobotNilai").attachEvent("onItemClick", refreshBobotNilai);

        //persen nilai
        $$("simpanPersenNilai").attachEvent("onItemClick", function () {
          if ($$("formPersenNilai").validate()) {
            var dataKirim = $$("formPersenNilai").getValues();

            dataKirim.aksi = "persennilai";
            dataKirim = JSON.stringify(dataKirim);
            proses_tampil();
            webix
              .ajax()
              .post(
                "sopingi/bobot_nilai/persennilai/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
                dataKirim,
                {
                  success: function (text, xml, xhr) {
                    proses_hide();
                    var hasil = xml.json();
                    if (hasil.berhasil) {
                      webix.message(hasil.pesan);
                    } else {
                      webix.alert({
                        title: "Gagal Simpan",
                        text: "Gagal simpan persen nilai",
                        type: "alert-error",
                      });
                    }
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
          } else {
            webix.alert({
              title: "Kesalahan",
              text: "Persen Nilai Tidak Valid",
              type: "alert-error",
            });
          }
        });
      } else if (hal == "direktur-wadir.html") {
        halamanDirWadir.el = halaman;
        halamanDirWadir.render();

        $$("simpanDirWadir").attachEvent("onItemClick", simpanDirWadir);
      } else if (hal == "akun-admin.html") {
        halamanAkun.el = halaman;
        halamanAkun.render();

        $$("simpanAkun").attachEvent("onItemClick", simpanAkun);
      } else if (hal == "kuliah-mahasiswa.html") {
        halamanKuliahMahasiswa.el = halaman;
        halamanKuliahMahasiswa.render();

        webix.ui({
          view: "window",
          id: "winKuliahMahasiswa",
          width: 400,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Proses Hitung",
                id: "judulWinKuliahMahasiswa",
              },
              {
                view: "icon",
                icon: "close",
                id: "tutupWinKuliahMahasiswa",
                click: "$$('winKuliahMahasiswa').hide();",
              },
            ],
          },
          body: {
            view: "list",
            id: "listKuliahMahasiswa",
            template: "#proses#",
          },
        });

        webix.ui({
          view: "window",
          id: "winKuliahMahasiswaStatus",
          width: 400,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Ubah Status",
                id: "judulWinKuliahMahasiswaStatus",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winKuliahMahasiswaStatus').hide();",
              },
            ],
          },
          body: {
            view: "form",
            id: "formKuliahMahasiswaStatus",
            elements: [
              { view: "text", name: "id_aktifitas", hidden: true },
              { view: "text", name: "nipd", label: "NIM", disabled: true },
              { view: "text", name: "nm_pd", label: "Nama", disabled: true },
              {
                view: "text",
                name: "vnm_lemb",
                label: "Program Studi",
                disabled: true,
              },
              {
                view: "text",
                name: "vid_smt",
                label: "Semester",
                disabled: true,
              },
              {
                view: "richselect",
                name: "id_stat_mhs",
                label: "Status",
                options: [
                  { id: "A", value: "A - Aktif" },
                  { id: "C", value: "C - Cuti" },
                  { id: "N", value: "N - Non Aktif" },
                  { id: "K", value: "K - Keluar" },
                  { id: "L", value: "L - Lulus" },
                ],
              },
              {
                cols: [
                  {},
                  { view: "button", id: "ubahStatusMahasiswa", value: "Ubah" },
                  {},
                ],
              },
            ],
          },
        });

        $$("updateKuliahMahasiswaAktif").attachEvent(
          "onItemClick",
          cekKuliahMahasiswa,
        );

        $$("updateKuliahMahasiswaStatus").attachEvent(
          "onItemClick",
          function () {
            if ($$("dataTableKuliahMahasiswa").getSelectedId() != null) {
              var data = $$("dataTableKuliahMahasiswa").getSelectedItem();
              data.aksi = "ubah";
              $$("formKuliahMahasiswaStatus").setValues(data);
              $$("winKuliahMahasiswaStatus").show();
              $$("judulWinKuliahMahasiswaStatus").setValue(
                "Ubah Status: " + data.nipd + " - " + data.nm_pd,
              );
            } else {
              peringatan("Informasi", "Belum ada data yang dipilih");
            }
          },
        );

        $$("updateKuliahMahasiswaNonAktif").attachEvent(
          "onItemClick",
          cekKuliahMahasiswaNon,
        );

        $$("ubahStatusMahasiswa").attachEvent(
          "onItemClick",
          ubahStatusMahasiswa,
        );

        $$("excelKuliahMahasiswa").attachEvent("onItemClick", function () {
          var id_smt = $$("nm_smt").getValues().id_smt;
          var nm_smt = $$("nm_smt").getValues().nm_smt.replace("/", "-");
          webix.toExcel($$("dataTableKuliahMahasiswa"), {
            filename: "IPS " + id_smt,
            name: nm_smt,
            spans: true,
            styles: true,
          });
        });
      } else if (hal == "master-ruang.html") {
        masterSiakadRuang.el = halaman;
        masterSiakadRuang.render();

        webix.ui({
          view: "window",
          id: "winSiakadRuang",
          width: 300,
          position: "center",
          modal: true,
          head: {
            view: "toolbar",
            margin: -4,
            cols: [
              {
                view: "label",
                label: "Tambah Ruang",
                id: "judulWinSiakadRuang",
              },
              {
                view: "icon",
                icon: "close",
                click: "$$('winSiakadRuang').hide();",
              },
            ],
          },
          body: webix.copy(formSiakadRuang),
        });

        $$("tambahSiakadRuang").attachEvent("onItemClick", function () {
          $$("winSiakadRuang").show();
          $$("judulWinSiakadRuang").setValue("Tambah Ruang");
          $$("formSiakadRuang").setValues({
            aksi: "tambah",
          });
        });

        $$("ubahSiakadRuang").attachEvent("onItemClick", function () {
          if ($$("dataTableSiakadRuang").getSelectedId() != null) {
            var data = $$("dataTableSiakadRuang").getSelectedItem();
            $$("winSiakadRuang").show();
            $$("judulWinSiakadRuang").setValue("Ubah Ruang");
            data.aksi = "ubah";
            $$("formSiakadRuang").setValues(data);
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("hapusSiakadRuang").attachEvent("onItemClick", function () {
          if ($$("dataTableSiakadRuang").getSelectedId() != null) {
            var data = $$("dataTableSiakadRuang").getSelectedItem();
            data.aksi = "hapus";
            webix.confirm({
              title: "Konfirmasi",
              ok: "Ya",
              cancel: "Tidak",
              text: "Yakin akan menghapus data yang dipilih ?",
              callback: function (jwb) {
                if (jwb) {
                  hapusSiakadRuang(data);
                }
              },
            });
          } else {
            peringatan("Informasi", "Belum ada data yang dipilih");
          }
        });

        $$("simpanSiakadRuang").attachEvent("onItemClick", simpanSiakadRuang);
        $$("refreshSiakadRuang").attachEvent("onItemClick", refreshSiakadRuang);
      } else if (hal == "ws-token.html") {
        wsToken.el = halaman;
        wsToken.render();

        $$("refreshWsToken").attachEvent("onItemClick", refreshWsToken);
      } else if (hal == "ws-mahasiswa.html") {
        wsMahasiswa.el = halaman;
        wsMahasiswa.render();

        $$("refreshWsMahasiswa").attachEvent("onItemClick", refreshWsMahasiswa);
        $$("syncWsMahasiswa").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsMahasiswa();
              }
            },
          });
        });
      } else if (hal == "ws-pendidikan-mahasiswa.html") {
        wsPendidikanMahasiswa.el = halaman;
        wsPendidikanMahasiswa.render();

        $$("refreshWsPendidikanMahasiswa").attachEvent(
          "onItemClick",
          refreshWsPendidikanMahasiswa,
        );
        $$("syncWsPendidikanMahasiswa").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsPendidikanMahasiswa();
              }
            },
          });
        });
      } else if (hal == "ws-matakuliah.html") {
        wsMataKuliah.el = halaman;
        wsMataKuliah.render();

        $$("refreshWsMataKuliah").attachEvent(
          "onItemClick",
          refreshWsMataKuliah,
        );
        $$("syncWsMataKuliah").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsMataKuliah();
              }
            },
          });
        });
      } else if (hal == "ws-kurikulum.html") {
        wsKurikulum.el = halaman;
        wsKurikulum.render();

        $$("refreshWsKurikulum").attachEvent("onItemClick", refreshWsKurikulum);
        $$("syncWsKurikulum").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsKurikulum();
              }
            },
          });
        });
      } else if (hal == "ws-matakuliah-kurikulum.html") {
        wsMataKuliahKurikulum.el = halaman;
        wsMataKuliahKurikulum.render();

        $$("refreshWsMataKuliahKurikulum").attachEvent(
          "onItemClick",
          refreshWsMataKuliahKurikulum,
        );
        $$("syncWsMataKuliahKurikulum").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsMataKuliahKurikulum();
              }
            },
          });
        });
      } else if (hal == "ws-kelas-perkuliahan.html") {
        wsKelasPerkuliahan.el = halaman;
        wsKelasPerkuliahan.render();

        $$("refreshWsKelasPerkuliahan").attachEvent(
          "onItemClick",
          refreshWsKelasPerkuliahan,
        );
        $$("syncWsKelasPerkuliahan").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsKelasPerkuliahan();
              }
            },
          });
        });
      } else if (hal == "ws-ajar-dosen.html") {
        wsAjarDosen.el = halaman;
        wsAjarDosen.render();

        $$("refreshWsAjarDosen").attachEvent("onItemClick", refreshWsAjarDosen);
        $$("syncWsAjarDosen").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsAjarDosen();
              }
            },
          });
        });
      } else if (hal == "ws-krs-nilai.html") {
        wsNilai.el = halaman;
        wsNilai.render();

        $$("refreshWsNilai").attachEvent("onItemClick", refreshWsNilai);
        $$("syncWsNilai").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsNilai();
              }
            },
          });
        });
      } else if (hal == "ws-kuliah-mahasiswa.html") {
        wsKuliahMahasiswa.el = halaman;
        wsKuliahMahasiswa.render();

        $$("refreshWsKuliahMahasiswa").attachEvent(
          "onItemClick",
          refreshWsKuliahMahasiswa,
        );
        $$("syncWsKuliahMahasiswa").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsKuliahMahasiswa();
              }
            },
          });
        });
      } else if (hal == "ws-mahasiswa-keluar.html") {
        wsMahasiswaKeluar.el = halaman;
        wsMahasiswaKeluar.render();

        $$("refreshWsMahasiswaKeluar").attachEvent(
          "onItemClick",
          refreshWsMahasiswaKeluar,
        );
        $$("syncWsMahasiswaKeluar").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync ke feeder ?",
            callback: function (jwb) {
              if (jwb) {
                syncWsMahasiswaKeluar();
              }
            },
          });
        });
      } else if (hal == "ws-dosen.html") {
        wsDosen.el = halaman;
        wsDosen.render();

        $$("refreshWsDosen").attachEvent("onItemClick", refreshWsDosen);
        $$("syncWsDosen").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync feeder ke siakad?",
            callback: function (jwb) {
              if (jwb) {
                syncWsDosen();
              }
            },
          });
        });
      } else if (hal == "ws-dosen-pt.html") {
        wsDosenPt.el = halaman;
        wsDosenPt.render();

        $$("refreshWsDosenPt").attachEvent("onItemClick", refreshWsDosenPt);
        $$("syncWsDosenPt").attachEvent("onItemClick", function () {
          webix.confirm({
            title: "Konfirmasi",
            ok: "Sync Sekarang",
            cancel: "Tidak",
            text: "Yakin akan melakukan sync feeder ke siakad?",
            callback: function (jwb) {
              if (jwb) {
                syncWsDosenPt();
              }
            },
          });
        });
      } else {
        dashboard.el = halaman;
        dashboard.render();
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
});
