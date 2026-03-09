var wSia = webix.storage.session.get("wSia");

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

function kembaliKeLogin() {
  webix.storage.session.remove("wSia");
  window.location = wSia.domain + "/baak/login";
}

function peringatan(judul, pesan) {
  webix.alert({
    title: judul,
    ok: "Ok",
    text: pesan,
  });
}

/*PROGRAM STUDI*/
function simpanSms(id, e) {
  if ($$("formSms").validate()) {
    proses_tampil();
    $$("winSms").hide();
    dataKirim = JSON.stringify($$("formSms").getValues());
    id = $$("formSms").getValues().xid_mk;
    aksi = $$("formSms").getValues().aksi;
    webix
      .ajax()
      .post("sopingi/sms/" + aksi + "/" + wSia.apiKey + "/" + id, dataKirim, {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableSms").clearAll();
            $$("dataTableSms").load(
              "sopingi/sms/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formSms").refresh();
            $$("winSms").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
            $$("winSms").show();
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
      });
  }
}

function refreshSms(id, e) {
  $$("dataTableSms").clearAll();
  $$("dataTableSms").load(
    "sopingi/sms/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formSms").refresh();
}

function hapusSms(data) {
  proses_tampil();
  id = data.xid_sms;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post("sopingi/sms/" + aksi + "/" + wSia.apiKey + "/" + id, dataKirim, {
      success: function (response, data, xhr) {
        proses_hide();
        hasil = JSON.parse(response);
        if (hasil.berhasil) {
          webix.message(hasil.pesan);
          $$("dataTableSms").clearAll();
          $$("dataTableSms").load(
            "sopingi/sms/tampil/" + wSia.apiKey + "/" + Math.random(),
          );
          $$("formSms").refresh();
          $$("winSms").hide();
        } else {
          peringatan("Kesalahan!", hasil.pesan);
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
    });
}

/*MATA KULIAH*/
function simpanMataKuliah(id, e) {
  if ($$("formMataKuliah").validate()) {
    proses_tampil();
    $$("winMataKuliah").hide();
    dataKirim = JSON.stringify($$("formMataKuliah").getValues());
    id = $$("formMataKuliah").getValues().xid_mk;
    aksi = $$("formMataKuliah").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/mata_kuliah/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableMataKuliah").clearAll();
              $$("dataTableMataKuliah").load(
                "sopingi/mata_kuliah/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
              );
              $$("formMataKuliah").refresh();
              $$("winMataKuliah").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winMataKuliah").show();
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
  }
}

function refreshMataKuliah(id, e) {
  $$("dataTableMataKuliah").clearAll();
  $$("dataTableMataKuliah").load(
    "sopingi/mata_kuliah/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formMataKuliah").refresh();
}

function hapusMataKuliah(data) {
  proses_tampil();
  id = data.xid_mk;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/mata_kuliah/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableMataKuliah").clearAll();
            $$("dataTableMataKuliah").load(
              "sopingi/mata_kuliah/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formMataKuliah").refresh();
            $$("winMataKuliah").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*KURIKULUM*/
function simpanKurikulum(id, e) {
  if ($$("formKurikulum").validate()) {
    proses_tampil();
    $$("winKurikulum").hide();
    dataKirim = JSON.stringify($$("formKurikulum").getValues());
    id = $$("formKurikulum").getValues().xid_kurikulum_sp;
    aksi = $$("formKurikulum").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/kurikulum/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableKurikulum").clearAll();
              $$("dataTableKurikulum").load(
                "sopingi/kurikulum/tampil/" + wSia.apiKey + "/" + Math.random(),
              );
              $$("formKurikulum").refresh();
              $$("winKurikulum").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winKurikulum").show();
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
  }
}

function refreshKurikulum(id, e) {
  $$("dataTableKurikulum").clearAll();
  $$("dataTableKurikulum").load(
    "sopingi/kurikulum/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formKurikulum").refresh();
}

function hapusKurikulum(data) {
  proses_tampil();
  id = data.xid_kurikulum_sp;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/kurikulum/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableKurikulum").clearAll();
            $$("dataTableKurikulum").load(
              "sopingi/kurikulum/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formKurikulum").refresh();
            $$("winKurikulum").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*MATA KULIAH KURIKULUM*/
function simpanMataKuliahKurikulum(id, e) {
  if ($$("formMataKuliahKurikulum").validate()) {
    proses_tampil();
    $$("winMataKuliahKurikulum").hide();
    dataKirim = JSON.stringify($$("formMataKuliahKurikulum").getValues());
    id = $$("formMataKuliahKurikulum").getValues().xid_kurikulum_sp;
    aksi = $$("formMataKuliahKurikulum").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/mata_kuliah_kurikulum/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableMataKuliahKurikulum").clearAll();
              id = $$("id_kurikulum_sp_mk").getValue();
              $$("dataTableMataKuliahKurikulum").load(
                "sopingi/mata_kuliah_kurikulum/tampil/" +
                  wSia.apiKey +
                  "/" +
                  id,
              );
              $$("formMataKuliahKurikulum").refresh();
              $$("winMataKuliahKurikulum").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winMataKuliahKurikulum").show();
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
  }
}

function refreshMataKuliahKurikulum(id, e) {
  $$("dataTableMataKuliahKurikulum").clearAll();
  id = $$("id_kurikulum_sp_mk").getValue();
  $$("dataTableMataKuliahKurikulum").load(
    "sopingi/mata_kuliah_kurikulum/tampil/" + wSia.apiKey + "/" + id,
  );
  $$("formMataKuliahKurikulum").refresh();
}

function hapusMataKuliahKurikulum(data) {
  proses_tampil();
  id = data.id_kurikulum_sp + "|" + data.id_mk;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/mata_kuliah_kurikulum/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableMataKuliahKurikulum").clearAll();
            id = $$("id_kurikulum_sp_mk").getValue();
            $$("dataTableMataKuliahKurikulum").load(
              "sopingi/mata_kuliah_kurikulum/tampil/" + wSia.apiKey + "/" + id,
            );
            $$("formMataKuliahKurikulum").refresh();
            $$("winMataKuliahKurikulum").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*MASTER KELAS SIAKAD*/
function simpanSiakadKelas(id, e) {
  if ($$("formSiakadKelas").validate()) {
    proses_tampil();
    $$("winSiakadKelas").hide();
    dataKirim = JSON.stringify($$("formSiakadKelas").getValues());
    id = $$("formSiakadKelas").getValues().id_nm_kls;
    aksi = $$("formSiakadKelas").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/siakad_kelas/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableSiakadKelas").clearAll();
              $$("dataTableSiakadKelas").load(
                "sopingi/siakad_kelas/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
              );
              $$("formSiakadKelas").refresh();
              $$("winSiakadKelas").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winSiakadKelas").show();
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
  }
}

function refreshSiakadKelas(id, e) {
  $$("dataTableSiakadKelas").clearAll();
  $$("dataTableSiakadKelas").load(
    "sopingi/siakad_kelas/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formSiakadKelas").refresh();
}

function hapusSiakadKelas(data) {
  proses_tampil();
  id = data.id_nm_kls;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/siakad_kelas/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableSiakadKelas").clearAll();
            $$("dataTableSiakadKelas").load(
              "sopingi/siakad_kelas/tampil/" +
                wSia.apiKey +
                "/" +
                Math.random(),
            );
            $$("formSiakadKelas").refresh();
            $$("winSiakadKelas").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*DOSEN*/
function simpanDosen(id, e) {
  if ($$("formDosen").validate()) {
    proses_tampil();
    $$("winDosen").hide();
    dataKirim = JSON.stringify($$("formDosen").getValues());
    id = $$("formDosen").getValues().xid_ptk;
    aksi = $$("formDosen").getValues().aksi;

    if (aksi == "tambah" && dataKirim.passBaru == "") {
      peringatan("Kesalahan!", "Password Belum Diisi");
      return;
    } else if (aksi == "tambah" && dataKirim.passBaru != dataKirim.passBaru1) {
      peringatan("Kesalahan!", "Password Tidak Sama");
      return;
    } else if (
      dataKirim.passBaru != "" &&
      dataKirim.passBaru != dataKirim.passBaru1
    ) {
      peringatan("Kesalahan!", "Password Baru Tidak Sama");
      return;
    }

    webix
      .ajax()
      .post("sopingi/dosen/" + aksi + "/" + wSia.apiKey + "/" + id, dataKirim, {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableDosen").clearAll();
            $$("dataTableDosen").load(
              "sopingi/dosen/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formDosen").refresh();
            $$("winDosen").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
            $$("winDosen").show();
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
      });
  }
}

function refreshDosen(id, e) {
  $$("dataTableDosen").clearAll();
  $$("dataTableDosen").load(
    "sopingi/dosen/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formDosen").refresh();
}

function hapusDosen(data) {
  proses_tampil();
  id = data.xid_ptk;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post("sopingi/dosen/" + aksi + "/" + wSia.apiKey + "/" + id, dataKirim, {
      success: function (response, data, xhr) {
        proses_hide();
        hasil = JSON.parse(response);
        if (hasil.berhasil) {
          webix.message(hasil.pesan);
          $$("dataTableDosen").clearAll();
          $$("dataTableDosen").load(
            "sopingi/dosen/tampil/" + wSia.apiKey + "/" + Math.random(),
          );
          $$("formDosen").refresh();
          $$("winDosen").hide();
        } else {
          peringatan("Kesalahan!", hasil.pesan);
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
    });
}

/*PENUGASAN DOSEN*/
function simpanPenugasanDosen(id, e) {
  if ($$("formPenugasanDosen").validate()) {
    proses_tampil();
    $$("winPenugasanDosen").hide();
    dataKirim = JSON.stringify($$("formPenugasanDosen").getValues());
    id = $$("formPenugasanDosen").getValues().xid_reg_ptk;
    aksi = $$("formPenugasanDosen").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/dosen_pt/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTablePenugasanDosen").clearAll();
              $$("dataTablePenugasanDosen").load(
                "sopingi/dosen_pt/tampil/" + wSia.apiKey + "/" + Math.random(),
              );
              $$("formPenugasanDosen").refresh();
              $$("winPenugasanDosen").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winPenugasanDosen").show();
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
  }
}

function refreshPenugasanDosen(id, e) {
  $$("dataTablePenugasanDosen").clearAll();
  $$("dataTablePenugasanDosen").load(
    "sopingi/dosen_pt/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formPenugasanDosen").refresh();
}

function hapusPenugasanDosen(data) {
  proses_tampil();
  id = data.xid_reg_ptk;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/dosen_pt/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTablePenugasanDosen").clearAll();
            $$("dataTablePenugasanDosen").load(
              "sopingi/dosen_pt/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formPenugasanDosen").refresh();
            $$("winPenugasanDosen").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*KELAS KULIAH*/
function simpanKelasKuliah(id, e) {
  if ($$("formKelasKuliah").validate()) {
    proses_tampil();
    $$("winKelasKuliah").hide();
    dataKirim = JSON.stringify($$("formKelasKuliah").getValues());
    id = $$("formKelasKuliah").getValues().xid_kls;
    aksi = $$("formKelasKuliah").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/kelas_kuliah/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableKelas").clearAll();
              $$("dataTableKelas").load(
                "idata->sopingi/kelas_kuliah/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
              );
              $$("formKelasKuliah").refresh();
              $$("winKelasKuliah").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winKelasKuliah").show();
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
  }
}

function refreshKelasKuliah(id, e) {
  $$("dataTableKelas").clearAll();
  $$("dataTableKelas").load(
    "idata->sopingi/kelas_kuliah/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formKelasKuliah").refresh();
}

function hapusKelasKuliah(data) {
  proses_tampil();
  id = data.xid_kls;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/kelas_kuliah/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableKelas").clearAll();
            $$("dataTableKelas").load(
              "idata->sopingi/kelas_kuliah/tampil/" +
                wSia.apiKey +
                "/" +
                Math.random(),
            );
            $$("formKelasKuliah").refresh();
            $$("winKelasKuliah").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*DOSEN KELAS KULIAH*/
function simpanDosenKelas(id, e) {
  if ($$("formDosenKelas").validate()) {
    proses_tampil();
    $$("winDosenKelas").hide();
    dataKirim = JSON.stringify($$("formDosenKelas").getValues());
    id = $$("formDosenKelas").getValues().xid_ajar;
    aksi = $$("formDosenKelas").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/ajar_dosen/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableDosenKelas").clearAll();
              id = $$("xid_klsDosen").getValue();
              $$("dataTableDosenKelas").load(
                "sopingi/ajar_dosen/tampil/" + wSia.apiKey + "/" + id,
              );
              $$("formDosenKelas").refresh();
              $$("winDosenKelas").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winDosenKelas").show();
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
  }
}

function refreshDosenKelas(id, e) {
  $$("dataTableDosenKelas").clearAll();
  id = $$("xid_klsDosen").getValue();
  $$("dataTableDosenKelas").load(
    "sopingi/ajar_dosen/tampil/" + wSia.apiKey + "/" + id,
  );
  $$("formDosenKelas").refresh();
}

function hapusDosenKelas(data) {
  proses_tampil();
  id = data.xid_ajar;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/ajar_dosen/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableDosenKelas").clearAll();
            id = $$("xid_klsDosen").getValue();
            $$("dataTableDosenKelas").load(
              "sopingi/ajar_dosen/tampil/" + wSia.apiKey + "/" + id,
            );
            $$("formDosenKelas").refresh();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

function unduhAbsenDosen(xid_ajar) {
  window.open(
    "sopingi/absen_dosen_pdf/download/" + wSia.apiKey + "/" + xid_ajar,
    "_blank",
  );
}

function refreshMhsKelas(id, e) {
  $$("dataTableMahasiswaKelas").clearAll();
  id = $$("xid_klsMhs").getValue();
  $$("dataTableMahasiswaKelas").load(
    "sopingi/nilai/tampil/" + wSia.apiKey + "/" + id,
  );
}

function unduhAbsenMhs(id_kls) {
  window.open(
    "sopingi/absen_mhs_pdf/download/" + wSia.apiKey + "/" + id_kls,
    "_blank",
  );
}

function unduhAbsenDosenPDF(id_kls) {
  window.open(
    "sopingi/absen_dosen_pdf/download/" + wSia.apiKey + "/" + id_kls,
    "_blank",
  );
}

function unduhUjianKartuUTSPDF(id_kls) {
  window.open(
    "sopingi/ujian_kartu_cetak/uts/" + wSia.apiKey + "/" + id_kls,
    "_blank",
  );
}

function unduhUjianKartuUASPDF(id_kls) {
  window.open(
    "sopingi/ujian_kartu_cetak/uas/" + wSia.apiKey + "/" + id_kls,
    "_blank",
  );
}

function unduhUjianAbsenUTSPDF(id_kls) {
  window.open(
    "sopingi/absen_ujian_cetak/uts/" + wSia.apiKey + "/" + id_kls,
    "_blank",
  );
}

function unduhUjianAbsenUASPDF(id_kls) {
  window.open(
    "sopingi/absen_ujian_cetak/uas/" + wSia.apiKey + "/" + id_kls,
    "_blank",
  );
}

/*MAHASISWA*/
function simpanMahasiswa(id, e) {
  if ($$("formMahasiswaDetail").validate()) {
    proses_tampil();
    dataKirim = JSON.stringify($$("formMahasiswaDetail").getValues());
    aksi = $$("formMahasiswaDetail").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/mahasiswa/" + aksi + "/" + wSia.apiKey + "/" + Math.random(),
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableMahasiswa").clearAll();
              $$("dataTableMahasiswa").load(
                "idata->sopingi/mahasiswa/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
              );

              $$("formMahasiswaDetail").clear();
              $$("formMahasiswaDetail").setValues({
                kewarganegaraan: "Indonesia",
                aksi: "tambah",
              });
              $$("masterMahasiswa").back();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
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
  }
}

function refreshMahasiswa(id, e) {
  $$("dataTableMahasiswa").clearAll();
  $$("dataTableMahasiswa").load(
    "idata->sopingi/mahasiswa/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formMahasiswaDetail").refresh();
}

function hapusMahasiswa(xid_pd, xid_reg_pd) {
  proses_tampil();
  data = { xid_pd: xid_pd, xid_reg_pd: xid_reg_pd, aksi: "hapus" };
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post("sopingi/mahasiswa/hapus/" + wSia.apiKey + "/" + xid_pd, dataKirim, {
      success: function (response, data, xhr) {
        proses_hide();
        hasil = JSON.parse(response);
        if (hasil.berhasil) {
          webix.message(hasil.pesan);
          $$("dataTableMahasiswa").clearAll();
          $$("dataTableMahasiswa").load(
            "idata->sopingi/kelas_kuliah/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
          );
          $$("formMahasiswaDetail").refresh();
        } else {
          peringatan("Kesalahan!", hasil.pesan);
        }
      },
      error: function (text, data, xhr) {
        proses_hide();
        webix.alert({
          title: "Gagal Koneksi",
          text: "Tidak dapat terhubung dengan server!",
          type: "alert-error",
        });
      },
    });
}

/*KRS*/

function simpanKRS(id, e) {
  if ($$("formKRS").validate()) {
    dataMhs = $$("dataTableMahasiswa").getSelectedItem();

    var dataKrs = [];
    jSKSbaru = 0;
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

    totalSKS = jSKS + jSKSbaru;

    if (totalSKS <= 24) {
      proses_tampil();
      $$("formKRS").setValues({ kelas: dataKrs }, true);
      dataKirim = JSON.stringify($$("formKRS").getValues());

      webix
        .ajax()
        .post(
          "sopingi/nilai/tambah/" + wSia.apiKey + "/" + dataMhs.xid_reg_pd,
          dataKirim,
          {
            success: function (response, data, xhr) {
              proses_hide();
              hasil = JSON.parse(response);
              if (hasil.berhasil) {
                webix.message(hasil.pesan);
                $$("dataTableKrs").clearAll();
                dataMhs = $$("dataTableMahasiswa").getSelectedItem();
                $$("dataTableKrs").load(
                  "sopingi/nilai/tampilKRS/" +
                    wSia.apiKey +
                    "/" +
                    dataMhs.xid_reg_pd,
                );
                $$("formKRS").refresh();
                $$("winMhsKelasKuliah").hide();
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
    } else {
      webix.alert({
        title: "Informasi",
        text: "Jumlah SKS melebihi 24 SKS. Silahkan cek kembali mata kuliah yang diambil",
        type: "alert-error",
      });
    }
  } else {
    peringatan("Kesalahan!", "Form Tidak Valid");
  }
}

function hapusKRS(data) {
  proses_tampil();
  id = data.id_nilai;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post("sopingi/nilai/" + aksi + "/" + wSia.apiKey + "/" + id, dataKirim, {
      success: function (response, data, xhr) {
        proses_hide();
        hasil = JSON.parse(response);
        if (hasil.berhasil) {
          webix.message(hasil.pesan);
          $$("dataTableKrs").clearAll();
          dataMhs = $$("dataTableMahasiswa").getSelectedItem();
          $$("dataTableKrs").load(
            "sopingi/nilai/tampilKRS/" + wSia.apiKey + "/" + dataMhs.xid_reg_pd,
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
    });
}

function unduhKRS() {
  dataMhs = $$("dataTableMahasiswa").getSelectedItem();
  window.open(
    wSia.domain +
      "/baak/sopingi/krs_pdf/download/" +
      wSia.apiKey +
      "/" +
      dataMhs.xid_reg_pd,
    "_blank",
  );
}

/*KHS Per @*/
function simpanKHS() {
  proses_tampil();

  var itemNilai = new Array();
  $$("dataTableKhs").eachRow(function (baris) {
    id_nilai = $$("dataTableKhs").getItem(baris).id_nilai;
    nilai_angka = $$("dataTableKhs").getItem(baris).nilai_angka;
    nilai_huruf = $$("dataTableKhs").getItem(baris).nilai_huruf;
    nilai_indeks = $$("dataTableKhs").getItem(baris).nilai_indeks;
    itemNilai.push({
      id_nilai: id_nilai,
      nilai_angka: nilai_angka,
      nilai_huruf: nilai_huruf,
      nilai_indeks: nilai_indeks,
    });
  });

  var nilai = { aksi: "ubahNilai", nilai: itemNilai };

  dataKirim = JSON.stringify(nilai);
  dataMhs = $$("dataTableMahasiswa").getSelectedItem();
  webix
    .ajax()
    .post(
      "sopingi/nilai/ubahNilai/" + wSia.apiKey + "/" + dataMhs.xid_pd,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableKhs").clearAll();
            dataMhs = $$("dataTableMahasiswa").getSelectedItem();
            $$("dataTableKhs").load(
              "sopingi/nilai/tampilKHS/" +
                wSia.apiKey +
                "/" +
                dataMhs.xid_reg_pd,
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

function refreshKHS() {
  $$("dataTableKhs").clearAll();
  dataMhs = $$("dataTableMahasiswa").getSelectedItem();
  $$("dataTableKhs").load(
    "sopingi/nilai/tampilKHS/" + wSia.apiKey + "/" + dataMhs.xid_reg_pd,
  );
}

function unduhKHS() {
  dataMhs = $$("dataTableMahasiswa").getSelectedItem();
  window.open(
    wSia.domain +
      "/baak/sopingi/khs_per_cetak/KHS/" +
      wSia.apiKey +
      "/" +
      dataMhs.xid_reg_pd,
    "_blank",
  );
}

/*KHS Per Kelas */
function simpanKHSKelas() {
  proses_tampil();

  var itemNilai = new Array();
  $$("dataTableMahasiswaKelas").eachRow(function (baris) {
    id_nilai = $$("dataTableMahasiswaKelas").getItem(baris).id_nilai;
    nilai_angka = $$("dataTableMahasiswaKelas").getItem(baris).nilai_angka;
    nilai_huruf = $$("dataTableMahasiswaKelas").getItem(baris).nilai_huruf;
    nilai_indeks = $$("dataTableMahasiswaKelas").getItem(baris).nilai_indeks;
    itemNilai.push({
      id_nilai: id_nilai,
      nilai_angka: nilai_angka,
      nilai_huruf: nilai_huruf,
      nilai_indeks: nilai_indeks,
    });
  });

  var nilai = { aksi: "ubahNilai", nilai: itemNilai };

  dataKirim = JSON.stringify(nilai);
  dataKelas = $$("dataTableKelas").getSelectedItem();
  webix
    .ajax()
    .post(
      "sopingi/nilai/ubahNilai/" + wSia.apiKey + "/" + dataKelas.xid_kls,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableMahasiswaKelas").clearAll();
            dataKelas = $$("dataTableKelas").getSelectedItem();
            $$("dataTableMahasiswaKelas").load(
              "sopingi/nilai/tampil/" + wSia.apiKey + "/" + dataKelas.xid_kls,
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

/*TRANSKIP*/
function refreshTranskip() {
  $$("dataTableTranskip").clearAll();
  dataMhs = $$("dataTableMahasiswa").getSelectedItem();
  $$("dataTableTranskip").load(
    "sopingi/nilai/tampilTranskip/" + wSia.apiKey + "/" + dataMhs.xid_reg_pd,
  );
}

function unduhTranskip() {
  dataMhs = $$("dataTableMahasiswa").getSelectedItem();
  window.open(
    wSia.domain +
      "/baak/sopingi/transkip_pdf/TRANSKIP/" +
      wSia.apiKey +
      "/" +
      dataMhs.xid_reg_pd,
    "_blank",
  );
}

/*KARTU UJIAN MURNI*/
function refreshKartuUjianMurni() {
  $$("dataTableKartuUjianMurni").clearAll();
  $$("dataTableKartuUjianMurni").load(
    "idata->sopingi/kartu_ujian/tampilMurni/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
  $$("dataTableKartuUjianMurni").refresh();
}

function unduhKartuUjianMurni() {
  var itemKartuUTS = new Array();
  var itemKartuUAS = new Array();
  $$("dataTableKartuUjianMurni").data.each(function (dataKartu) {
    if (dataKartu.kartuUTS) {
      itemUTS = dataKartu.no_pend;
      itemKartuUTS.push(itemUTS);
    }

    if (dataKartu.kartuUAS) {
      itemUAS = dataKartu.no_pend;
      itemKartuUAS.push(itemUAS);
    }
  });

  if (itemKartuUTS.length > 0 || itemKartuUAS.length > 0) {
    var tanggal = new Date();
    var waktu =
      "Tgl " +
      tanggal.getDate() +
      "-" +
      (tanggal.getMonth() + 1) +
      "-" +
      tanggal.getFullYear() +
      " Jam " +
      tanggal.getHours() +
      "-" +
      tanggal.getMinutes() +
      "-" +
      tanggal.getSeconds();
    if (itemKartuUTS.length > 0) {
      var nilai = { aksi: "UTS", no_pend: itemKartuUTS };
      var kartu = "UTS";
      var namaFile = "Kartu-UTS-Murni " + waktu + ".pdf";
    } else if (itemKartuUAS.length > 0) {
      var nilai = { aksi: "UAS", no_pend: itemKartuUAS };
      var kartu = "UAS";
      var namaFile = "Kartu-UAS-Murni " + waktu + ".pdf";
    } else {
      var nilai = { aksi: "TDKADA", no_pend: "" };
      var kartu = "TDKADA";
    }

    dataKirim = JSON.stringify(nilai);
    proses_tampil();
    webix
      .ajax()
      .response("blob")
      .post(
        "sopingi/kartu_ujian_pdf/" + kartu + "/" + wSia.apiKey + "/Murni",
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();

            var file = new Blob([data]);
            var fileURL = window.URL.createObjectURL(file);

            var a = document.createElement("a");
            a.href = fileURL;
            a.setAttribute("target", "_blank");
            a.download = namaFile;
            document.body.appendChild(a);
            a.click();

            //window.open(fileURL+"?"+namaFile,"_blank");
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
      text: "Tidak ada yang dicetak",
      type: "alert-error",
    });
  }
}

/*KARTU UJIAN Transfer*/
function refreshKartuUjianTransfer() {
  $$("dataTableKartuUjianTransfer").clearAll();
  $$("dataTableKartuUjianTransfer").load(
    "idata->sopingi/kartu_ujian/tampilTransfer/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
  $$("dataTableKartuUjianTransfer").refresh();
}

function unduhKartuUjianTransfer() {
  var itemKartuUTS = new Array();
  var itemKartuUAS = new Array();
  $$("dataTableKartuUjianTransfer").data.each(function (dataKartu) {
    if (dataKartu.kartuUTS) {
      itemUTS = dataKartu.no_pend;
      itemKartuUTS.push(itemUTS);
    }

    if (dataKartu.kartuUAS) {
      itemUAS = dataKartu.no_pend;
      itemKartuUAS.push(itemUAS);
    }
  });

  if (itemKartuUTS.length > 0 || itemKartuUAS.length > 0) {
    var tanggal = new Date();
    var waktu =
      "Tgl " +
      tanggal.getDate() +
      "-" +
      (tanggal.getMonth() + 1) +
      "-" +
      tanggal.getFullYear() +
      " Jam " +
      tanggal.getHours() +
      "-" +
      tanggal.getMinutes() +
      "-" +
      tanggal.getSeconds();

    if (itemKartuUTS.length > 0) {
      var nilai = { aksi: "UTS", no_pend: itemKartuUTS };
      var kartu = "UTS";
      var namaFile = "Kartu-UTS-Transfer " + waktu + ".pdf";
    } else if (itemKartuUAS.length > 0) {
      var nilai = { aksi: "UAS", no_pend: itemKartuUAS };
      var kartu = "UAS";
      var namaFile = "Kartu-UAS-Transfer " + waktu + ".pdf";
    } else {
      var nilai = { aksi: "TDKADA", no_pend: "" };
      var kartu = "TDKADA";
    }

    dataKirim = JSON.stringify(nilai);
    proses_tampil();
    webix
      .ajax()
      .response("blob")
      .post(
        "sopingi/kartu_ujian_pdf/" + kartu + "/" + wSia.apiKey + "/Transfer",
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();

            var file = new Blob([data]);
            var fileURL = window.URL.createObjectURL(file);

            var a = document.createElement("a");
            a.href = fileURL;
            a.setAttribute("target", "_blank");
            a.download = namaFile;
            document.body.appendChild(a);
            a.click();

            //window.open(fileURL+"?"+namaFile,"_blank");
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
      text: "Tidak ada yang dicetak",
      type: "alert-error",
    });
  }
}

/*KHS MURNI*/
function refreshKHSMurni() {
  $$("dataTableKHSMurni").clearAll();
  $$("dataTableKHSMurni").load(
    "idata->sopingi/khs/tampilMurni/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("dataTableKHSMurni").refresh();
}

function unduhKHSMurni() {
  var itemKHS = new Array();
  $$("dataTableKHSMurni").data.each(function (dataKHS) {
    if (dataKHS.khs) {
      itemKHS.push(dataKHS.xid_reg_pd);
    }
  });

  if (itemKHS.length > 0) {
    var tanggal = new Date();
    var waktu =
      tanggal.getDate() +
      "" +
      (tanggal.getMonth() + 1) +
      "" +
      tanggal.getFullYear() +
      "" +
      tanggal.getHours() +
      "" +
      tanggal.getMinutes() +
      "" +
      tanggal.getSeconds();

    var nilai = { aksi: "KHS", xid_reg_pd: itemKHS };
    var kartu = "KHS";
    var namaFile = "KHS-Murni-" + waktu + ".doc";

    dataKirim = JSON.stringify(nilai);
    proses_tampil();
    webix
      .ajax()
      .response("blob")
      .post(
        "sopingi/khs_cetak/" + kartu + "/" + wSia.apiKey + "/Murni",
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();

            var file = new Blob([data], { type: "application/vnd.ms-word" });
            var fileURL = window.URL.createObjectURL(file);

            var a = document.createElement("a");
            a.setAttribute("target", "_blank");
            a.href = fileURL;
            a.download = namaFile;
            document.body.appendChild(a);
            a.click();

            //window.open(fileURL+"?"+namaFile,"_blank");
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
      text: "Tidak ada yang dicetak",
      type: "alert-error",
    });
  }
}

/*KHS Transfer*/
function refreshKHSTransfer() {
  $$("dataTableKHSTransfer").clearAll();
  $$("dataTableKHSTransfer").load(
    "idata->sopingi/khs/tampilTransfer/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("dataTableKHSTransfer").refresh();
}

function unduhKHSTransfer() {
  var itemKHS = new Array();
  $$("dataTableKHSTransfer").data.each(function (dataKHS) {
    if (dataKHS.khs) {
      itemKHS.push(dataKHS.xid_reg_pd);
    }
  });

  if (itemKHS.length > 0) {
    var tanggal = new Date();
    var waktu =
      "" +
      tanggal.getDate() +
      "" +
      (tanggal.getMonth() + 1) +
      "" +
      tanggal.getFullYear() +
      "" +
      tanggal.getHours() +
      "" +
      tanggal.getMinutes() +
      "" +
      tanggal.getSeconds();

    var nilai = { aksi: "KHS", xid_reg_pd: itemKHS };
    var kartu = "KHS";
    var namaFile = "KHS-Transfer-" + waktu + ".doc";

    dataKirim = JSON.stringify(nilai);
    proses_tampil();
    webix
      .ajax()
      .response("blob")
      .post(
        "sopingi/khs_cetak/" + kartu + "/" + wSia.apiKey + "/Transfer",
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();

            var file = new Blob([data]);
            var fileURL = window.URL.createObjectURL(file);

            var a = document.createElement("a");
            a.href = fileURL;
            a.setAttribute("target", "_blank");
            a.download = namaFile;
            document.body.appendChild(a);
            a.click();

            //window.open(fileURL+"?"+namaFile,"_blank");
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
      text: "Tidak ada yang dicetak",
      type: "alert-error",
    });
  }
}

/*MAHASISWA KELUAR*/
function simpanMahasiswaKeluar(id, e) {
  if ($$("formMahasiswaKeluar").validate()) {
    proses_tampil();
    $$("winMahasiswaKeluar").hide();
    var dataKirim = JSON.stringify($$("formMahasiswaKeluar").getValues());
    var id = $$("formMahasiswaKeluar").getValues().xid_reg_pd;
    var aksi = $$("formMahasiswaKeluar").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/mahasiswa/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableMahasiswaKeluar").clearAll();
              $$("dataTableMahasiswaKeluar").load(
                "sopingi/mahasiswa/keluar/" + wSia.apiKey + "/" + Math.random(),
              );
              $$("formMahasiswaKeluar").refresh();
              $$("winMahasiswaKeluar").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winMahasiswaKeluar").show();
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
  }
}

function refreshMahasiswaKeluar(id, e) {
  $$("dataTableMahasiswaKeluar").clearAll();
  $$("dataTableMahasiswaKeluar").load(
    "sopingi/mahasiswa/keluar/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formMahasiswaKeluar").refresh();
}

function hapusMahasiswaKeluar(data) {
  proses_tampil();
  id = data.xid_ptk;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/mahasiswa/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableMahasiswaKeluar").clearAll();
            $$("dataTableMahasiswaKeluar").load(
              "sopingi/mahasiswa/keluar/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formMahasiswaKeluar").refresh();
            $$("winMahasiswaKeluar").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*SEMESTER*/
function simpanSemester(id, e) {
  if ($$("formSemester").validate()) {
    proses_tampil();
    $$("winSemester").hide();
    dataKirim = JSON.stringify($$("formSemester").getValues());
    id = $$("formSemester").getValues().xid_ptk;
    aksi = $$("formSemester").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/semester/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableSemester").clearAll();
              $$("dataTableSemester").load(
                "sopingi/semester/tampil/" + wSia.apiKey + "/" + Math.random(),
              );
              $$("formSemester").refresh();
              $$("winSemester").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winSemester").show();
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
  }
}

function refreshSemester(id, e) {
  $$("dataTableSemester").clearAll();
  $$("dataTableSemester").load(
    "sopingi/semester/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formSemester").refresh();
}

function hapusSemester(data) {
  proses_tampil();
  id = data.xid_ptk;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/semester/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableSemester").clearAll();
            $$("dataTableSemester").load(
              "sopingi/semester/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formSemester").refresh();
            $$("winSemester").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

function statusSemester(data) {
  proses_tampil();
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/semester/" + aksi + "/" + wSia.apiKey + "/" + Math.random(),
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableSemester").clearAll();
            $$("dataTableSemester").load(
              "sopingi/semester/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formSemester").refresh();
            $$("winSemester").hide();
          } else {
            webix.message(hasil.pesan);
          }
        },
        error: function (text, data, xhr) {
          proses_hide();
          webix.alert({
            title: "Gagal Akses",
            text: "Tidak dapat terhubung dengan server!",
            type: "alert-error",
          });
        },
      },
    );
}

/*BOBOT NILAI*/
function simpanBobotNilai(id, e) {
  if ($$("formBobotNilai").validate()) {
    proses_tampil();
    $$("winBobotNilai").hide();
    dataKirim = JSON.stringify($$("formBobotNilai").getValues());
    aksi = $$("formBobotNilai").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/bobot_nilai/" + aksi + "/" + wSia.apiKey + "/" + Math.random(),
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableBobotNilai").clearAll();
              $$("dataTableBobotNilai").load(
                "sopingi/bobot_nilai/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
              );
              $$("formBobotNilai").refresh();
              $$("winBobotNilai").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winBobotNilai").show();
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
  }
}

function refreshBobotNilai(id, e) {
  $$("dataTableBobotNilai").clearAll();
  $$("dataTableBobotNilai").load(
    "sopingi/bobot_nilai/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formBobotNilai").refresh();
}

function hapusBobotNilai(data) {
  proses_tampil();
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/bobot_nilai/" + aksi + "/" + wSia.apiKey + "/" + Math.random(),
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableBobotNilai").clearAll();
            $$("dataTableBobotNilai").load(
              "sopingi/bobot_nilai/tampil/" + wSia.apiKey + "/" + Math.random(),
            );
            $$("formBobotNilai").refresh();
            $$("winBobotNilai").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

/*AKUN*/
function simpanAkun(id, e) {
  if ($$("formAkun").validate()) {
    proses_tampil();
    data = $$("formAkun").getValues();
    aksi = data.aksi;
    if (data.passBaru1 == data.passBaru) {
      dataKirim = JSON.stringify($$("formAkun").getValues());
      webix
        .ajax()
        .post(
          "sopingi/user/" + aksi + "/" + wSia.apiKey + "/" + Math.random(),
          dataKirim,
          {
            success: function (response, d, xhr) {
              proses_hide();
              hasil = JSON.parse(response);
              if (hasil.berhasil) {
                peringatan("Berhasil", hasil.pesan);
                $$("formAkun").setValues({
                  pass: "",
                  passBaru: "",
                  passBaru1: "",
                  aksi: "ubahAkun",
                });
              } else {
                peringatan("Kesalahan", hasil.pesan);
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
      proses_hide();
      webix.alert({
        title: "Informasi",
        ok: "Ok",
        text: "Password baru tidak sama",
      });
    }
  }
}

/*DIR WADIR*/
function simpanDirWadir(id, e) {
  if ($$("formDirWadir").validate()) {
    proses_tampil();
    data = $$("formDirWadir").getValues();
    data.aksi = "ubahDirWadir";
    dataKirim = JSON.stringify(data);
    webix
      .ajax()
      .post(
        "sopingi/satuan_pendidikan/ubahDirWadir/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: function (response, d, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              peringatan("Berhasil", hasil.pesan);
            } else {
              peringatan("Kesalahan", hasil.pesan);
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
  }
}

/*proses keluar*/
function keluarProses() {
  webix.confirm({
    title: "Konfirmasi Keluar",
    ok: "Ya",
    cancel: "Tidak",
    text: "Yakin mau Keluar dari SIAKAD ?",
    callback: function (jwb) {
      if (jwb) {
        proses_tampil();
        webix.ajax().get(
          "logout",
          {},
          {
            success: function (text, xml, xhr) {
              proses_hide();
              kembaliKeLogin();
            },
            error: function (response, data, xhr) {
              proses_hide();
              webix.alert({
                title: "Kesalahan",
                text: "Gagal terkoneksi dengan server..!",
                type: "alert-error",
              });
            },
          },
        );
      } else {
        $$("sideKiri").unselectAll();
      }
    },
  });
}

//HAK AKSES
function refreshHakAkses() {
  $$("dataTableHakAkses").clearAll();
  $$("dataTableHakAkses").load(
    "idata->sopingi/mahasiswa/tampilHakAkses/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
}

function hapusHakAkses(data) {
  proses_tampil();
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/mahasiswa/" + aksi + "/" + wSia.apiKey + "/" + Math.random(),
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableHakAkses").clearAll();
            $$("dataTableHakAkses").load(
              "idata->sopingi/mahasiswa/tampilHakAkses/" +
                wSia.apiKey +
                "/" +
                Math.random(),
            );
            $$("dataTableHakAkses").refresh();
          } else {
            webix.message(hasil.pesan);
          }
        },
        error: function (text, data, xhr) {
          proses_hide();
          webix.alert({
            title: "Gagal Akses",
            text: "Tidak dapat terhubung dengan server!",
            type: "alert-error",
          });
        },
      },
    );
}

function refreshModalMhs() {
  $$("dataTableModalMhs").clearAll();
  $$("dataTableModalMhs").load(
    "idata->sopingi/mahasiswa/tampilBelumHakAkses/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
}

function tambahModalMhs() {
  var itemMhs = new Array();

  $$("dataTableModalMhs").data.each(function (data) {
    if (data.hakAkses) {
      itemMhs.push(data.xid_pd);
    }
  });

  if (itemMhs.length > 0) {
    var nilai = { aksi: "mahasiswaTambahAkses", xid_pd: itemMhs };

    dataKirim = JSON.stringify(nilai);
    $$("winHakAkses").hide();
    proses_tampil();
    webix
      .ajax()
      .post(
        "sopingi/mahasiswa/mahasiswaTambahAkses/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              refreshHakAkses();
              $$("winHakAkses").hide();
            } else {
              webix.message(hasil.pesan);
              $$("winHakAkses").show();
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
  } else {
    webix.alert({
      title: "Informasi",
      text: "Tidak ada yang ditambahkan",
      type: "alert-error",
    });
  }
}

//KULIAH MAHASISWA
function refreshKuliahMahasiswa() {
  $$("dataTableKuliahMahasiswa").clearAll();
  $$("dataTableKuliahMahasiswa").load(
    "sopingi/kuliah_mahasiswa/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function cekKuliahMahasiswa() {
  proses_tampil();
  $$("listKuliahMahasiswa").clearAll();
  $$("tutupWinKuliahMahasiswa").hide();
  var nilai = { aksi: "cek" };

  dataKirim = JSON.stringify(nilai);
  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi/kuliah_mahasiswa/cek/" + wSia.apiKey + "/" + Math.random(),
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("judulWinKuliahMahasiswa").setValue(
              "Proses hitung " + hasil.jumlah + " mahasiswa aktif",
            );
            $$("winKuliahMahasiswa").show();

            $$("listKuliahMahasiswa").add({
              id: 0,
              total: hasil.jumlah,
              jumlah: 0,
              proses:
                "<span class='spinner rotate'></span> Sedang diproses....",
            });
            updateKuliahMahasiswa(0);
          } else {
            webix.message(hasil.pesan);
            $$("tutupWinKuliahMahasiswa").show();
          }
        },
        error: function (text, data, xhr) {
          proses_hide();
          $$("tutupWinKuliahMahasiswa").show();
          webix.alert({
            title: "Gagal Akses",
            text: "Tidak dapat terhubung dengan server!",
            type: "alert-error",
          });
        },
      },
    );
}

function updateKuliahMahasiswa(offset) {
  proses_tampil();
  var nilai = { aksi: "update" };

  dataKirim = JSON.stringify(nilai);

  webix
    .ajax()
    .post(
      "sopingi/kuliah_mahasiswa/update/" + wSia.apiKey + "/" + offset,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);

            var id = $$("listKuliahMahasiswa").getLastId();
            var item = $$("listKuliahMahasiswa").getItem(id);
            var diproses = item["jumlah"] + hasil.jumlah;
            item["jumlah"] = diproses;
            item["proses"] =
              diproses + " dari " + item["total"] + " BERHASIL diproses";
            $$("listKuliahMahasiswa").updateItem(id, item);

            if (hasil.jumlah >= 500) {
              $$("listKuliahMahasiswa").add({
                id: hasil.berikutnya,
                jumlah: item["jumlah"],
                total: item["total"],
                proses:
                  "<span class='spinner rotate'></span> Sedang diproses....",
              });
              updateKuliahMahasiswa(hasil.berikutnya);
            } else {
              $$("listKuliahMahasiswa").add({
                id: hasil.berikutnya,
                jumlah: item["jumlah"],
                total: item["total"],
                proses: "SELESAI..",
              });

              $$("tutupWinKuliahMahasiswa").show();
              refreshKuliahMahasiswa();
            }
          } else {
            $$("tutupWinKuliahMahasiswa").show();
            webix.message(hasil.pesan);
          }
        },
        error: function (text, data, xhr) {
          proses_hide();
          $$("tutupWinKuliahMahasiswa").show();
          webix.alert({
            title: "Gagal Akses",
            text: "Tidak dapat terhubung dengan server!",
            type: "alert-error",
          });
        },
      },
    );
}

function cekKuliahMahasiswaNon() {
  proses_tampil();
  $$("listKuliahMahasiswa").clearAll();
  $$("tutupWinKuliahMahasiswa").hide();
  var nilai = { aksi: "ceknon" };

  dataKirim = JSON.stringify(nilai);
  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi/kuliah_mahasiswa/ceknon/" + wSia.apiKey + "/" + Math.random(),
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("judulWinKuliahMahasiswa").setValue(
              "Proses hitung " + hasil.jumlah + " mahasiswa non aktif",
            );
            $$("winKuliahMahasiswa").show();

            $$("listKuliahMahasiswa").add({
              id: 0,
              total: hasil.jumlah,
              jumlah: 0,
              proses:
                "<span class='spinner rotate'></span> Sedang diproses....",
            });
            updateKuliahMahasiswaNon(0);
          } else {
            webix.message(hasil.pesan);
            $$("tutupWinKuliahMahasiswa").show();
          }
        },
        error: function (text, data, xhr) {
          proses_hide();
          $$("tutupWinKuliahMahasiswa").show();
          webix.alert({
            title: "Gagal Akses",
            text: "Tidak dapat terhubung dengan server!",
            type: "alert-error",
          });
        },
      },
    );
}

function updateKuliahMahasiswaNon(offset) {
  proses_tampil();
  var nilai = { aksi: "updatenon" };

  dataKirim = JSON.stringify(nilai);

  webix
    .ajax()
    .post(
      "sopingi/kuliah_mahasiswa/updatenon/" + wSia.apiKey + "/" + offset,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);

            var id = $$("listKuliahMahasiswa").getLastId();
            var item = $$("listKuliahMahasiswa").getItem(id);
            var diproses = item["jumlah"] + hasil.jumlah;
            item["jumlah"] = diproses;
            item["proses"] =
              diproses +
              " dari " +
              item["total"] +
              " BERHASIL diproses non aktif";
            $$("listKuliahMahasiswa").updateItem(id, item);

            if (hasil.jumlah >= 500) {
              $$("listKuliahMahasiswa").add({
                id: hasil.berikutnya,
                jumlah: item["jumlah"],
                total: item["total"],
                proses:
                  "<span class='spinner rotate'></span> Sedang diproses....",
              });
              updateKuliahMahasiswaNon(hasil.berikutnya);
            } else {
              $$("listKuliahMahasiswa").add({
                id: hasil.berikutnya,
                jumlah: item["jumlah"],
                total: item["total"],
                proses: "SELESAI..",
              });

              $$("tutupWinKuliahMahasiswa").show();
              refreshKuliahMahasiswa();
            }
          } else {
            $$("tutupWinKuliahMahasiswa").show();
            webix.message(hasil.pesan);
          }
        },
        error: function (text, data, xhr) {
          proses_hide();
          $$("tutupWinKuliahMahasiswa").show();
          webix.alert({
            title: "Gagal Akses",
            text: "Tidak dapat terhubung dengan server!",
            type: "alert-error",
          });
        },
      },
    );
}

function ubahStatusMahasiswa(id, e) {
  if ($$("formKuliahMahasiswaStatus").validate()) {
    proses_tampil();

    dataKirim = JSON.stringify($$("formKuliahMahasiswaStatus").getValues());
    webix
      .ajax()
      .post(
        "sopingi/kuliah_mahasiswa/ubah/" + wSia.apiKey + "/" + Math.random(),
        dataKirim,
        function (response, d, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("winKuliahMahasiswaStatus").hide();
            refreshKuliahMahasiswa();
          } else {
            webix.alert({
              title: "Gagal Ubah",
              text: hasil.pesan,
              type: "alert-error",
            });
          }
        },
      );
  }
}

/*AKUN PASS MAHASISWA*/
function simpanAkunMahasiswa(id, e) {
  if ($$("formAkunMahasiswa").validate()) {
    proses_tampil();
    data = $$("formAkunMahasiswa").getValues();
    if (data.passBaru1 == data.passBaru) {
      dataKirim = JSON.stringify($$("formAkunMahasiswa").getValues());
      webix
        .ajax()
        .post(
          "sopingi/mahasiswa/pass/" + wSia.apiKey + "/" + Math.random(),
          dataKirim,
          function (response, d, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("formAkun").setValues({
                passBaru: "",
                passBaru1: "",
                nipd: "",
                aksi: "pass",
              });
            } else {
              webix.alert({
                title: "Gagal Ubah",
                text: hasil.pesan,
                type: "alert-error",
              });
            }
          },
        );
    } else {
      proses_hide();
      webix.alert({
        title: "Informasi",
        ok: "Ok",
        text: "Password baru tidak sama",
      });
    }
  }
}

/*MASTER Ruang SIAKAD*/
function simpanSiakadRuang(id, e) {
  if ($$("formSiakadRuang").validate()) {
    proses_tampil();
    $$("winSiakadRuang").hide();
    dataKirim = JSON.stringify($$("formSiakadRuang").getValues());
    id = $$("formSiakadRuang").getValues().id_nm_kls;
    aksi = $$("formSiakadRuang").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/siakad_ruang/" + aksi + "/" + wSia.apiKey + "/" + id,
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              $$("dataTableSiakadRuang").clearAll();
              $$("dataTableSiakadRuang").load(
                "sopingi/siakad_ruang/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
              );
              $$("formSiakadRuang").refresh();
              $$("winSiakadRuang").hide();
            } else {
              peringatan("Kesalahan!", hasil.pesan);
              $$("winSiakadRuang").show();
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
  }
}

function refreshSiakadRuang(id, e) {
  $$("dataTableSiakadRuang").clearAll();
  $$("dataTableSiakadRuang").load(
    "sopingi/siakad_ruang/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
  $$("formSiakadRuang").refresh();
}

function hapusSiakadRuang(data) {
  proses_tampil();
  id = data.id_nm_kls;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/siakad_ruang/" + aksi + "/" + wSia.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableSiakadRuang").clearAll();
            $$("dataTableSiakadRuang").load(
              "sopingi/siakad_ruang/tampil/" +
                wSia.apiKey +
                "/" +
                Math.random(),
            );
            $$("formSiakadRuang").refresh();
            $$("winSiakadRuang").hide();
          } else {
            peringatan("Kesalahan!", hasil.pesan);
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
}

//SYNC FEEDER
//ws token
function refreshWsToken(id, e) {
  $$("dataTableWsToken").clearAll();
  $$("dataTableWsToken").load(
    "sopingi-feeder/token/generate/" + wSia.apiKey + "/" + Math.random(),
  );
}

//ws MAHASISWA
function refreshWsMahasiswa(id, e) {
  $$("dataTableWsMahasiswa").clearAll();
  $$("dataTableWsMahasiswa").load(
    "sopingi-feeder/mahasiswa/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsMahasiswa(id, e) {
  var jumlah = $$("dataTableWsMahasiswa").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsMahasiswa").eachRow(async function (row) {
    var ws_record = $$("dataTableWsMahasiswa").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/mahasiswa/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
            } else {
              ws_record.statusSync = hasil.pesan;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsMahasiswa").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsMahasiswa").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws PENDIDIKAN MAHASISWA
function refreshWsPendidikanMahasiswa(id, e) {
  $$("dataTableWsPendidikanMahasiswa").clearAll();
  $$("dataTableWsPendidikanMahasiswa").load(
    "sopingi-feeder/mahasiswa_pt/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsPendidikanMahasiswa(id, e) {
  var jumlah = $$("dataTableWsPendidikanMahasiswa").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsPendidikanMahasiswa").eachRow(async function (row) {
    var ws_record = $$("dataTableWsPendidikanMahasiswa").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/mahasiswa_pt/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
            } else {
              ws_record.statusSync = hasil.pesan;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsPendidikanMahasiswa").updateItem(
              row,
              ws_record,
            );
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsPendidikanMahasiswa").updateItem(
              row,
              ws_record,
            );
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws MATA KULIAH
function refreshWsMataKuliah(id, e) {
  $$("dataTableWsMataKuliah").clearAll();
  $$("dataTableWsMataKuliah").load(
    "sopingi-feeder/mata_kuliah/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsMataKuliah(id, e) {
  var jumlah = $$("dataTableWsMataKuliah").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsMataKuliah").eachRow(async function (row) {
    var ws_record = $$("dataTableWsMataKuliah").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/mata_kuliah/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsMataKuliah").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsMataKuliah").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

function prosesUpdateIdMkMataKuliah() {
  var row = $$("dataTableWsMataKuliah").getSelectedId();
  var ws_record = $$("dataTableWsMataKuliah").getSelectedItem();
  ws_record.aksi = "getMataKuliah";
  var dataKirim = JSON.stringify(ws_record);
  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi-feeder/mata_kuliah/" +
        ws_record.aksi +
        "/" +
        wSia.apiKey +
        "/" +
        Math.random(),
      dataKirim,
      {
        success: async function (response, data, xhr) {
          proses_hide();
          var hasil = JSON.parse(response);
          if (hasil.berhasil) {
            ws_record.statusSync = 1;
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          } else {
            ws_record.statusSync = "error";
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          }
          await $$("dataTableWsMataKuliah").updateItem(row, ws_record);
        },
        error: async function (response, data, xhr) {
          proses_hide();
          webix.alert({
            title: "Gagal Koneksi",
            text: "Tidak dapat terhubung dengan internet atau server feeder!",
            type: "alert-error",
          });

          ws_record.statusSync = "error";
          ws_record.pesan =
            "Tidak dapat terhubung dengan internet atau server feeder!";
          ws_record.feeder_result = null;
          await $$("dataTableWsMataKuliah").updateItem(row, ws_record);
        },
      },
    );
}

//ws KURIKULUM
function refreshWsKurikulum(id, e) {
  $$("dataTableWsKurikulum").clearAll();
  $$("dataTableWsKurikulum").load(
    "sopingi-feeder/kurikulum/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsKurikulum(id, e) {
  var jumlah = $$("dataTableWsKurikulum").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsKurikulum").eachRow(async function (row) {
    var ws_record = $$("dataTableWsKurikulum").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/kurikulum/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsKurikulum").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsKurikulum").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws MATA KULIAH KURIKULUM
function refreshWsMataKuliahKurikulum(id, e) {
  $$("dataTableWsMataKuliahKurikulum").clearAll();
  $$("dataTableWsMataKuliahKurikulum").load(
    "sopingi-feeder/mata_kuliah_kurikulum/tampil/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
}

function syncWsMataKuliahKurikulum(id, e) {
  var jumlah = $$("dataTableWsMataKuliahKurikulum").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsMataKuliahKurikulum").eachRow(async function (row) {
    var ws_record = $$("dataTableWsMataKuliahKurikulum").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/mata_kuliah_kurikulum/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsMataKuliahKurikulum").updateItem(
              row,
              ws_record,
            );
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsMataKuliahKurikulum").updateItem(
              row,
              ws_record,
            );
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

function prosesUpdateIdMataKuliahKurikulum() {
  var row = $$("dataTableWsMataKuliahKurikulum").getSelectedId();
  var ws_record = $$("dataTableWsMataKuliahKurikulum").getSelectedItem();
  ws_record.aksi = "getMataKuliahKurikulum";
  var dataKirim = JSON.stringify(ws_record);
  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi-feeder/mata_kuliah_kurikulum/" +
        ws_record.aksi +
        "/" +
        wSia.apiKey +
        "/" +
        Math.random(),
      dataKirim,
      {
        success: async function (response, data, xhr) {
          proses_hide();
          var hasil = JSON.parse(response);
          if (hasil.berhasil) {
            ws_record.statusSync = 1;
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          } else {
            ws_record.statusSync = "error";
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          }
          await $$("dataTableWsMataKuliahKurikulum").updateItem(row, ws_record);
        },
        error: async function (response, data, xhr) {
          proses_hide();
          webix.alert({
            title: "Gagal Koneksi",
            text: "Tidak dapat terhubung dengan internet atau server feeder!",
            type: "alert-error",
          });

          ws_record.statusSync = "error";
          ws_record.pesan =
            "Tidak dapat terhubung dengan internet atau server feeder!";
          ws_record.feeder_result = null;
          await $$("dataTableWsMataKuliahKurikulum").updateItem(row, ws_record);
        },
      },
    );
}

//ws KELAS PERKULIAHAN
function refreshWsKelasPerkuliahan(id, e) {
  $$("dataTableWsKelasPerkuliahan").clearAll();
  $$("dataTableWsKelasPerkuliahan").load(
    "sopingi-feeder/kelas_perkuliahan/tampil/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
}

function syncWsKelasPerkuliahan(id, e) {
  var jumlah = $$("dataTableWsKelasPerkuliahan").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsKelasPerkuliahan").eachRow(async function (row) {
    var ws_record = $$("dataTableWsKelasPerkuliahan").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/kelas_perkuliahan/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsKelasPerkuliahan").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsKelasPerkuliahan").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

function prosesUpdateIdKelasPerkuliahan() {
  var row = $$("dataTableWsKelasPerkuliahan").getSelectedId();
  var ws_record = $$("dataTableWsKelasPerkuliahan").getSelectedItem();
  ws_record.aksi = "getKelasPerkuliahan";
  var dataKirim = JSON.stringify(ws_record);
  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi-feeder/kelas_perkuliahan/" +
        ws_record.aksi +
        "/" +
        wSia.apiKey +
        "/" +
        Math.random(),
      dataKirim,
      {
        success: async function (response, data, xhr) {
          proses_hide();
          var hasil = JSON.parse(response);
          if (hasil.berhasil) {
            ws_record.statusSync = 1;
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          } else {
            ws_record.statusSync = hasil.pesan;
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          }
          await $$("dataTableWsKelasPerkuliahan").updateItem(row, ws_record);
        },
        error: async function (response, data, xhr) {
          proses_hide();
          webix.alert({
            title: "Gagal Koneksi",
            text: "Tidak dapat terhubung dengan internet atau server feeder!",
            type: "alert-error",
          });

          ws_record.statusSync = "error";
          ws_record.pesan =
            "Tidak dapat terhubung dengan internet atau server feeder!";
          ws_record.feeder_result = null;
          await $$("dataTableWsKelasPerkuliahan").updateItem(row, ws_record);
        },
      },
    );
}

//ws AJAR DOSEN
function refreshWsAjarDosen(id, e) {
  $$("dataTableWsAjarDosen").clearAll();
  $$("dataTableWsAjarDosen").load(
    "sopingi-feeder/ajar_dosen/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsAjarDosen(id, e) {
  var jumlah = $$("dataTableWsAjarDosen").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsAjarDosen").eachRow(async function (row) {
    var ws_record = $$("dataTableWsAjarDosen").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/ajar_dosen/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsAjarDosen").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsAjarDosen").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

function prosesUpdateIdAjarDosen() {
  var row = $$("dataTableWsAjarDosen").getSelectedId();
  var ws_record = $$("dataTableWsAjarDosen").getSelectedItem();
  ws_record.aksi = "getAjarDosen";
  var dataKirim = JSON.stringify(ws_record);
  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi-feeder/ajar_dosen/" +
        ws_record.aksi +
        "/" +
        wSia.apiKey +
        "/" +
        Math.random(),
      dataKirim,
      {
        success: async function (response, data, xhr) {
          proses_hide();
          var hasil = JSON.parse(response);
          if (hasil.berhasil) {
            ws_record.statusSync = 1;
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          } else {
            ws_record.statusSync = hasil.pesan;
            ws_record.pesan = hasil.pesan;
            ws_record.feeder_result = hasil.feeder_result;
            webix.message(hasil.pesan);
          }
          await $$("dataTableWsAjarDosen").updateItem(row, ws_record);
        },
        error: async function (response, data, xhr) {
          proses_hide();
          webix.alert({
            title: "Gagal Koneksi",
            text: "Tidak dapat terhubung dengan internet atau server feeder!",
            type: "alert-error",
          });

          ws_record.statusSync = "error";
          ws_record.pesan =
            "Tidak dapat terhubung dengan internet atau server feeder!";
          ws_record.feeder_result = null;
          await $$("dataTableWsAjarDosen").updateItem(row, ws_record);
        },
      },
    );
}

//ws KRS NILAI
function refreshWsNilai(id, e) {
  $$("dataTableWsNilai").clearAll();
  $$("dataTableWsNilai").load(
    "sopingi-feeder/nilai/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsNilai(id, e) {
  var jumlah = $$("dataTableWsNilai").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsNilai").eachRow(async function (row) {
    var ws_record = $$("dataTableWsNilai").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/nilai/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsNilai").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsNilai").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws KULIAH MAHASISWA
function refreshWsKuliahMahasiswa(id, e) {
  $$("dataTableWsKuliahMahasiswa").clearAll();
  $$("dataTableWsKuliahMahasiswa").load(
    "sopingi-feeder/kuliah_mahasiswa/tampil/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
}

function syncWsKuliahMahasiswa(id, e) {
  var jumlah = $$("dataTableWsKuliahMahasiswa").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsKuliahMahasiswa").eachRow(async function (row) {
    var ws_record = $$("dataTableWsKuliahMahasiswa").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/kuliah_mahasiswa/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsKuliahMahasiswa").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsKuliahMahasiswa").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws MAHASISWA KELUAR
function refreshWsMahasiswaKeluar(id, e) {
  $$("dataTableWsMahasiswaKeluar").clearAll();
  $$("dataTableWsMahasiswaKeluar").load(
    "sopingi-feeder/mahasiswa_keluar/tampil/" +
      wSia.apiKey +
      "/" +
      Math.random(),
  );
}

function syncWsMahasiswaKeluar(id, e) {
  var jumlah = $$("dataTableWsMahasiswaKeluar").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsMahasiswaKeluar").eachRow(async function (row) {
    var ws_record = $$("dataTableWsMahasiswaKeluar").getItem(row);
    ws_record.aksi = "sync";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/mahasiswa_keluar/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsMahasiswaKeluar").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsMahasiswaKeluar").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws DOSEN
function refreshWsDosen(id, e) {
  $$("dataTableWsDosen").clearAll();
  $$("dataTableWsDosen").load(
    "sopingi-feeder/dosen/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsDosen(id, e) {
  var jumlah = $$("dataTableWsDosen").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsDosen").eachRow(async function (row) {
    var ws_record = $$("dataTableWsDosen").getItem(row);
    ws_record.aksi = "getDosen";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/dosen/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsDosen").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsDosen").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

//ws DOSEN PT
function refreshWsDosenPt(id, e) {
  $$("dataTableWsDosenPt").clearAll();
  $$("dataTableWsDosenPt").load(
    "sopingi-feeder/dosen_pt/tampil/" + wSia.apiKey + "/" + Math.random(),
  );
}

function syncWsDosenPt(id, e) {
  var jumlah = $$("dataTableWsDosenPt").count();
  var counter = 0;
  proses_tampil();
  $$("dataTableWsDosenPt").eachRow(async function (row) {
    var ws_record = $$("dataTableWsDosenPt").getItem(row);
    ws_record.aksi = "getPenugasanDosen";
    var dataKirim = JSON.stringify(ws_record);

    await webix
      .ajax()
      .post(
        "sopingi-feeder/dosen_pt/" +
          ws_record.aksi +
          "/" +
          wSia.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: async function (response, data, xhr) {
            var hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              ws_record.statusSync = 1;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
            } else {
              ws_record.statusSync = hasil.pesan;
              ws_record.pesan = hasil.pesan;
              ws_record.feeder_result = hasil.feeder_result;
              webix.message(hasil.pesan);
            }
            await $$("dataTableWsDosenPt").updateItem(row, ws_record);
            counter++;
          },
          error: async function (response, data, xhr) {
            webix.alert({
              title: "Gagal Koneksi",
              text: "Tidak dapat terhubung dengan internet atau server feeder!",
              type: "alert-error",
            });

            ws_record.statusSync =
              "Tidak dapat terhubung dengan internet atau server feeder!";
            await $$("dataTableWsDosenPt").updateItem(row, ws_record);
            counter++;
          },
        },
      );

    //console.log(counter);
    if (counter == jumlah) {
      proses_hide();
    }
  });
}

function bulkUbahPA() {
  var ids = [];
  $$("dataTableMahasiswa").data.each(function (obj) {
    if (obj.ch_mhs) ids.push(obj.xid_reg_pd);
  });

  if (ids.length == 0) {
    webix.message({
      type: "error",
      text: "Pilih mahasiswa terlebih dahulu melalui checkbox",
    });
    return;
  }

  if (!$$("formBulkPA").validate()) {
    webix.message({ type: "error", text: "Pilih Dosen PA terlebih dahulu" });
    return;
  }

  var values = $$("formBulkPA").getValues();
  var dataKirim = JSON.stringify({
    aksi: "bulkUbahPA",
    pa: values.pa,
    ids: ids,
  });

  proses_tampil();
  webix
    .ajax()
    .post(
      "sopingi/mahasiswa/bulkUbahPA/" + wSia.apiKey + "/" + Math.random(),
      dataKirim,
      {
        success: function (text, xml, xhr) {
          proses_hide();
          var res = JSON.parse(text);
          if (res.berhasil) {
            webix.message(res.pesan);
            $$("winBulkPA").hide();
            refreshMahasiswa();
          } else {
            webix.alert(res.pesan);
          }
        },
        error: function () {
          proses_hide();
          webix.alert("Gagal koneksi ke server");
        },
      },
    );
}
