var wSiaMhs = webix.storage.session.get("wSiaMhs");

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

function reloadBimbinganDataTable() {
  if (
    !$$("menuPembimbing") ||
    !$$("selectBimbinganKelas") ||
    !$$("pembimbingRiwayatDataTable")
  )
    return;

  var th = $$("menuPembimbing").getSelectedId();
  var kelas = $$("selectBimbinganKelas").getValue();

  if (th && kelas) {
    var url =
      "sopingi/pa_aktifitas/tampil/" + wSiaMhs.apiKey + "/" + th + "_" + kelas;
    $$("pembimbingRiwayatDataTable").clearAll();
    $$("pembimbingRiwayatDataTable").load(url);
  }
}

function peringatan(judul, pesan) {
  webix.alert({
    title: judul,
    ok: "Ok",
    text: pesan,
  });
}

function kembaliKeLogin() {
  webix.storage.session.remove("wSiaMhs");
  window.location = wSiaMhs.domain + "/dosen/login";
}

/*AKUN*/
function simpanAkun(id, e) {
  if ($$("formAkun").validate()) {
    proses_tampil();
    data = $$("formAkun").getValues();
    if (data.passBaru1 == data.passBaru) {
      dataKirim = JSON.stringify($$("formAkun").getValues());
      webix
        .ajax()
        .post(
          "sopingi/dosen/aksi/" + wSiaMhs.apiKey + "/" + Math.random(),
          dataKirim,
          function (response, d, xhr) {
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

/*MAHASISWA*/
function simpanMahasiswa(id, e) {
  if ($$("formMahasiswaDetail").validate()) {
    proses_tampil();
    dataKirim = JSON.stringify($$("formMahasiswaDetail").getValues());
    aksi = $$("formMahasiswaDetail").getValues().aksi;
    webix
      .ajax()
      .post(
        "sopingi/mahasiswa/" +
          aksi +
          "/" +
          wSiaMhs.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              peringatan("Berhasil", hasil.pesan);
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

/*FOTO*/
function updateFoto() {
  var file_id = $$("foto").files.getFirstId(); //getting the ID
  if (typeof file_id != "undefined") {
    $$("foto").send(function () {
      $$("fotoMhs").setHTML(
        "<center><img src='foto/" +
          wSiaMhs.nidnMd5 +
          ".jpg?" +
          Math.random() +
          "' height='140'></center>",
      );
      $$("foto").files.remove(file_id);
    });
  } else {
    peringatan("Kesalahan", "File belum dipilih");
  }
}

/*KRS*/

function simpanKRS(id, e) {
  if ($$("formKRS").validate()) {
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
          "sopingi/nilai/tambah/" + wSiaMhs.apiKey + "/" + Math.random(),
          dataKirim,
          {
            success: function (response, data, xhr) {
              proses_hide();
              hasil = JSON.parse(response);
              if (hasil.berhasil) {
                webix.message(hasil.pesan);
                $$("dataTableKrs").clearAll();
                $$("dataTableKrs").load(
                  "sopingi/nilai/tampil/" +
                    wSiaMhs.apiKey +
                    "/" +
                    Math.random(),
                );
                $$("formKRS").refresh();
                $$("winKelasKuliah").hide();
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
    .post(
      "sopingi/nilai/" + aksi + "/" + wSiaMhs.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            $$("dataTableKrs").clearAll();
            $$("dataTableKrs").load(
              "sopingi/nilai/tampil/" + wSiaMhs.apiKey + "/" + Math.random(),
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

function unduhKRS() {
  window.open(
    wSiaMhs.domain +
      "/dosen/sopingi/krs_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      Math.random(),
    "_blank",
  );
}

/*KHS*/
function khsPDF() {
  id_smt = $$("khs_id_smt").getValue();
  if (id_smt != "") {
    id = id_smt;
  } else {
    id = Math.random();
  }
  window.open(
    wSiaMhs.domain +
      "/dosen/sopingi/khs_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      id,
    "_blank",
  );
}

function transkipPDF() {
  window.open(
    wSiaMhs.domain +
      "/dosen/sopingi/transkip_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      Math.random(),
    "_blank",
  );
}

/*AKTIFITAS BIMBINGAN*/
function simpanPembimbing() {
  if ($$("formPembimbing").validate()) {
    proses_tampil();
    var data = $$("formPembimbing").getValues();
    aksi = data.aksi;
    dataKirim = JSON.stringify(data);
    webix
      .ajax()
      .post(
        "sopingi/pa_aktifitas/" +
          aksi +
          "/" +
          wSiaMhs.apiKey +
          "/" +
          Math.random(),
        dataKirim,
        {
          success: function (response, data, xhr) {
            proses_hide();
            hasil = JSON.parse(response);
            if (hasil.berhasil) {
              webix.message(hasil.pesan);
              var th = $$("menuPembimbing").getSelectedId();
              var kelas = $$("selectBimbinganKelas").getValue();
              $$("pembimbingRiwayatDataTable").clearAll();
              $$("pembimbingRiwayatDataTable").load(
                "sopingi/pa_aktifitas/tampil/" +
                  wSiaMhs.apiKey +
                  "/" +
                  th +
                  "_" +
                  kelas,
              );
              $$("winPembimbing").hide();
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
}

function hapusPembimbing(data) {
  proses_tampil();
  id = data.id;
  aksi = data.aksi;
  dataKirim = JSON.stringify(data);
  webix
    .ajax()
    .post(
      "sopingi/pa_aktifitas/" + aksi + "/" + wSiaMhs.apiKey + "/" + id,
      dataKirim,
      {
        success: function (response, data, xhr) {
          proses_hide();
          hasil = JSON.parse(response);
          if (hasil.berhasil) {
            webix.message(hasil.pesan);
            var th = $$("menuPembimbing").getSelectedId();
            var kelas = $$("selectBimbinganKelas").getValue();
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

/*proses keluar*/
function keluarProses() {
  webix.confirm({
    title: "Konfirmasi Keluar",
    ok: "Ya",
    cancel: "Tidak",
    text: "Yakin mau Keluar dari SIAKAD ?",
    callback: function (jwb) {
      if (jwb) {
        webix.ajax().get("logout", {}, function (text, xml, xhr) {
          kembaliKeLogin();
        });
      } else {
        $$("sideKiri").unselectAll();
      }
    },
  });
}

/* GOOGLE LINK */
function handleLinkGoogleResponse(response) {
  if (response.credential) {
    proses_tampil();
    var dataKirim = JSON.stringify({ token: response.credential });
    webix
      .ajax()
      .post(
        "sopingi/profil/link_google/" + wSiaMhs.apiKey + "/" + Math.random(),
        dataKirim,
        function (text) {
          proses_hide();
          var hasil = JSON.parse(text);
          if (hasil.berhasil) {
            webix.message({ type: "success", text: hasil.pesan });
            // Update Session Storage
            var stored = webix.storage.session.get("wSiaMhs");
            stored.email_poltek = hasil.email_poltek; // If returned from server
            webix.storage.session.put("wSiaMhs", stored);

            // Refresh the button view
            if ($$("google_btn_container")) $$("google_btn_container").render();
          } else {
            peringatan("Gagal Tautkan", hasil.pesan);
          }
        },
      );
  }
}

window.unlinkGoogleAccount = function () {
  webix.confirm({
    title: "Konfirmasi",
    ok: "Ya, Putuskan",
    cancel: "Batal",
    text: "Yakin ingin memutuskan tautan akun Google @poltekindonusa.ac.id Anda?",
    callback: function (result) {
      if (result) {
        proses_tampil();
        webix
          .ajax()
          .post(
            "sopingi/profil/unlink_google/" +
              wSiaMhs.apiKey +
              "/" +
              Math.random(),
            {},
            function (text) {
              proses_hide();
              var hasil = JSON.parse(text);
              if (hasil.berhasil) {
                webix.message(hasil.pesan);
                // Update Session Storage
                var stored = webix.storage.session.get("wSiaMhs");
                stored.email_poltek = null;
                webix.storage.session.put("wSiaMhs", stored);

                // Refresh the button view
                if ($$("google_btn_container"))
                  $$("google_btn_container").render();
              } else {
                peringatan("Gagal", hasil.pesan);
              }
            },
          );
      }
    },
  });
};
