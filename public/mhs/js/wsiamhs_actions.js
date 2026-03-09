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

function peringatan(judul, pesan) {
  webix.alert({
    title: judul,
    ok: "Ok",
    text: pesan,
  });
}

function kembaliKeLogin() {
  webix.storage.session.remove("wSiaMhs");
  window.location = wSiaMhs.domain + "/mhs/login";
}

window.handleLinkGoogleResponse = function (response) {
  if (response.credential) {
    var wSiaMhs = webix.storage.session.get("wSiaMhs");

    // Show loading
    proses_tampil();

    webix
      .ajax()
      .headers({
        "Content-Type": "application/json",
      })
      .post(
        "sopingi/profil/link_google/" + wSiaMhs.apiKey + "/" + Math.random(),
        JSON.stringify({ token: response.credential }),
        function (text) {
          proses_hide();
          try {
            var hasil = JSON.parse(text);
            if (hasil.status == "success") {
              webix.alert({
                title: "Berhasil",
                type: "alert-success",
                text: hasil.message,
              });
            } else {
              webix.alert({
                title: "Gagal",
                type: "alert-error",
                text: hasil.message,
              });
            }
          } catch (e) {
            console.error("Error parsing response:", e);
            webix.alert({
              title: "Error",
              type: "alert-error",
              text: "Terjadi kesalahan sistem",
            });
          }
        },
      );
  }
};

window.unlinkGoogleAccount = function () {
  webix.confirm({
    title: "Konfirmasi",
    ok: "Ya, Putuskan",
    cancel: "Batal",
    text: "Anda yakin ingin memutuskan hubungan akun Google? Anda tidak akan bisa login dengan Google lagi sampai Anda menautkannya kembali.",
    callback: function (result) {
      if (result) {
        var wSiaMhs = webix.storage.session.get("wSiaMhs");
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
              try {
                var hasil = JSON.parse(text);
                if (hasil.status == "success") {
                  webix.alert({
                    title: "Berhasil",
                    type: "alert-success",
                    text: hasil.message,
                    callback: function () {
                      // Refresh page or re-render form to show Link button
                      // Since we are in a single page app context, maybe just re-selecting the menu is enough, or reloading page
                      // Simplest is to reload the form/view
                      window.location.href = "#akun.html";
                      window.location.reload();
                    },
                  });
                } else {
                  webix.alert({
                    title: "Gagal",
                    type: "alert-error",
                    text: hasil.message,
                  });
                }
              } catch (e) {
                console.error("Error parsing response:", e);
                webix.alert({
                  title: "Error",
                  type: "alert-error",
                  text: "Terjadi kesalahan sistem",
                });
              }
            },
          );
      }
    },
  });
};

/*AKUN*/
function simpanAkun(id, e) {
  if ($$("formAkun").validate()) {
    data = $$("formAkun").getValues();

    // Validate password fields
    if (!data.pass || data.pass.trim() === "") {
      webix.alert({
        title: "Kesalahan",
        ok: "Ok",
        text: "Password lama harus diisi",
      });
      return;
    }

    if (!data.passBaru || data.passBaru.trim() === "") {
      webix.alert({
        title: "Kesalahan",
        ok: "Ok",
        text: "Password baru harus diisi",
      });
      return;
    }

    if (!data.passBaru1 || data.passBaru1.trim() === "") {
      webix.alert({
        title: "Kesalahan",
        ok: "Ok",
        text: "Ulangi password baru harus diisi",
      });
      return;
    }

    if (data.passBaru.length < 6) {
      webix.alert({
        title: "Kesalahan",
        ok: "Ok",
        text: "Password baru minimal 6 karakter",
      });
      return;
    }

    if (data.passBaru !== data.passBaru1) {
      webix.alert({
        title: "Kesalahan",
        ok: "Ok",
        text: "Password baru tidak sama. Silakan cek kembali.",
      });
      return;
    }

    if (data.pass === data.passBaru) {
      webix.alert({
        title: "Informasi",
        ok: "Ok",
        text: "Password baru tidak boleh sama dengan password lama",
      });
      return;
    }

    proses_tampil();
    dataKirim = JSON.stringify($$("formAkun").getValues());

    webix
      .ajax()
      .post(
        "sopingi/mahasiswa/aksi/" + wSiaMhs.apiKey + "/" + Math.random(),
        dataKirim,
        function (response, d, xhr) {
          proses_hide();
          try {
            hasil = JSON.parse(response);

            if (hasil.berhasil) {
              webix.confirm({
                title: "Berhasil",
                ok: "Logout Sekarang",
                cancel: "Nanti",
                text:
                  hasil.pesan +
                  "<br><br>Disarankan untuk logout dan login kembali dengan password baru.",
                callback: function (result) {
                  if (result) {
                    // Logout
                    window.location.href = "logout";
                  } else {
                    // Clear form
                    $$("formAkun").setValues({
                      pass: "",
                      passBaru: "",
                      passBaru1: "",
                      aksi: "ubahAkun",
                    });
                  }
                },
              });
            } else {
              peringatan("Kesalahan", hasil.pesan);
            }
          } catch (e) {
            console.error("Error parsing response:", e, response);
            peringatan(
              "Kesalahan",
              "Terjadi kesalahan saat memproses response server",
            );
          }
        },
      );
  }
}

/* MOODLE / LEARNING */
function updateMoodleAccount() {
  var form = $$("formMoodle");
  var values = form.getValues();

  if (!values.email) {
    peringatan("Kesalahan", "Email Learning harus diisi");
    return;
  }

  // Simple password validation if provided
  if (values.password && values.password.length < 8) {
    peringatan("Kesalahan", "Password Learning minimal 8 karakter");
    return;
  }

  webix.confirm({
    title: "Konfirmasi Update",
    text: "Pastikan data email sudah benar. Anda akan menggunakan data ini untuk login ke Learning. Lanjutkan?",
    callback: function (result) {
      if (result) {
        proses_tampil();

        webix
          .ajax()
          .headers({
            "Content-Type": "application/json",
          })
          .post(
            "sopingi/profil/update_learning/" +
              wSiaMhs.apiKey +
              "/" +
              Math.random(),
            JSON.stringify(values),
            function (text) {
              proses_hide();
              try {
                var hasil = JSON.parse(text);
                if (hasil.status == "success") {
                  webix.message({
                    type: "success",
                    text: hasil.message,
                  });
                  // Refresh status
                  loadMoodleStatus();
                } else {
                  peringatan("Gagal", hasil.message);
                }
              } catch (e) {
                peringatan("Error", "Gagal memproses data server");
              }
            },
          );
      }
    },
  });
}

function loadMoodleStatus() {
  var scope = $$("formMoodle");
  if (!scope) return;

  // Add ?moodle=1 to trigger Moodle API check in backend
  webix
    .ajax()
    .get(
      "sopingi/profil/tampil/" +
        wSiaMhs.apiKey +
        "/" +
        Math.random() +
        "?moodle=1",
      function (text) {
        try {
          var data = JSON.parse(text);
          $$("moodle_email").setValue(data.moodle_email || "");

          var badge = $$("moodle_status_badge");
          if (data.moodle_status == "Aktif") {
            badge.setHTML("<div class='status_badge aktif'>Aktif</div>");
            $$("btnUpdateMoodle").enable();
          } else {
            badge.setHTML(
              "<div class='status_badge nonaktif'>Belum Aktif</div>",
            );
            $$("btnUpdateMoodle").disable();
            $$("moodle_email").disable();
            $$("moodle_pass").disable();
          }
        } catch (e) {
          console.error("Load Moodle Error", e);
        }
      },
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
    // Disable button to prevent double click
    $$("updateFoto").disable();

    // Send upload, logic handled in onFileUpload event in wsiamhs_routes.js
    $$("foto").send();
  } else {
    peringatan("Kesalahan", "File belum dipilih");
  }
}

/*KK*/
function updateKk() {
  var file_id = $$("kk").files.getFirstId(); //getting the ID
  if (typeof file_id != "undefined") {
    $$("kk").send(function () {
      $$("kkMhs").load(
        "sopingi/mahasiswa/cekkk/" + wSiaMhs.apiKey + "/" + Math.random(),
      );
      $$("kk").files.remove(file_id);
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
      "/mhs/sopingi/krs_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      Math.random(),
    "_blank",
  );
}

// UPDATE ANDRE24012024
function krsPdf() {
  id_smt = $$("krs_id_smt").getValue();
  if (id_smt != "") {
    id = id_smt;
  } else {
    id = Math.random();
  }
  window.open(
    wSiaMhs.domain +
      "/mhs/sopingi/krs_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      id,
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
      "/mhs/sopingi/khs_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      id,
    "_blank",
  );
}

function transkipPDF() {
  window.open(
    wSiaMhs.domain +
      "/mhs/sopingi/transkip_pdf/download/" +
      wSiaMhs.apiKey +
      "/" +
      Math.random(),
    "_blank",
  );
}

/* KARTU UJIAN - Refactored for One-Page Print with Tagihan Check */
function cetakKartuHelper(tipe, id_smt) {
  if (!id_smt || id_smt === "") {
    id_smt = "-";
  }

  // Step 1: Check tagihan first via API
  var cekTagihanUrl =
    wSiaMhs.domain +
    "/mhs/sopingi/cek_tagihan_ujian/" +
    tipe +
    "/" +
    wSiaMhs.apiKey +
    "/" +
    id_smt;
  var kartuPdfUrl =
    wSiaMhs.domain +
    "/mhs/sopingi/kartu_ujian_pdf/" +
    tipe +
    "/" +
    wSiaMhs.apiKey +
    "/" +
    id_smt;

  proses_tampil(); // Show loading indicator

  webix
    .ajax()
    .get(cekTagihanUrl, function (text, data, xhr) {
      try {
        var result = JSON.parse(text);

        if (result.lunas === true) {
          // Lunas: Proceed to print card
          webix
            .ajax()
            .get(kartuPdfUrl, function (pdfText, pdfData, pdfXhr) {
              proses_hide();

              // Print via hidden iframe
              var iframeId = "print_frame_" + new Date().getTime();
              var iframe = document.createElement("iframe");

              iframe.style.position = "fixed";
              iframe.style.top = "-10000px";
              iframe.style.left = "-10000px";
              iframe.style.width = "1px";
              iframe.style.height = "1px";
              iframe.id = iframeId;

              document.body.appendChild(iframe);

              var doc = iframe.contentWindow.document;
              doc.open();
              doc.write(pdfText);
              doc.close();

              // Cleanup after delay
              setTimeout(function () {
                var f = document.getElementById(iframeId);
                if (f) f.parentNode.removeChild(f);
              }, 60000);
            })
            .fail(function () {
              proses_hide();
              webix.alert({
                title: "Gagal",
                type: "alert-error",
                text: "Gagal memuat kartu ujian. Periksa koneksi internet.",
              });
            });
        } else {
          // Belum Lunas: Show tagihan list
          proses_hide();

          var tagihanData = result.data || [];
          var totalKekurangan = result.total_kekurangan_formatted || "Rp 0";

          // Build table HTML
          var tableRows = "";
          for (var i = 0; i < tagihanData.length; i++) {
            var t = tagihanData[i];
            tableRows +=
              "<tr>" +
              "<td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>" +
              (i + 1) +
              "</td>" +
              "<td style='padding: 12px; border-bottom: 1px solid #eee;'>" +
              (t.nama_biaya || "-") +
              "</td>" +
              "<td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>" +
              formatRupiahJs(t.tagihan || 0) +
              "</td>" +
              "<td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>" +
              formatRupiahJs(t.terbayar || 0) +
              "</td>" +
              "<td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>" +
              formatRupiahJs(t.potongan || 0) +
              "</td>" +
              "<td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: #dc3545;'>" +
              formatRupiahJs(t.kekurangan || 0) +
              "</td>" +
              "</tr>";
          }

          var errorHtml =
            "<div style='padding:20px;'>" +
            "<div style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;'>" +
            "<h2 style='margin:0 0 15px 0;'><i class='fa fa-exclamation-circle'></i> Tidak Dapat Mencetak Kartu Ujian " +
            tipe +
            "</h2>" +
            "<p style='margin:0; opacity: 0.9;'>Anda memiliki tunggakan pembayaran sebesar <strong style='font-size: 1.2em;'>" +
            totalKekurangan +
            "</strong> yang harus dilunasi terlebih dahulu.</p>" +
            "</div>" +
            "<div style='background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);'>" +
            "<h3 style='margin: 0 0 15px 0; color: #333;'><i class='fa fa-list-alt' style='color: #dc3545;'></i> Daftar Tagihan Belum Lunas</h3>" +
            "<div style='overflow-x:auto;'>" +
            "<table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>" +
            "<thead>" +
            "<tr style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>" +
            "<th style='padding: 12px; text-align: center; width: 50px;'>No</th>" +
            "<th style='padding: 12px; text-align: left;'>Nama Biaya</th>" +
            "<th style='padding: 12px; text-align: right;'>Tagihan</th>" +
            "<th style='padding: 12px; text-align: right;'>Terbayar</th>" +
            "<th style='padding: 12px; text-align: right;'>Potongan</th>" +
            "<th style='padding: 12px; text-align: right;'>Kekurangan</th>" +
            "</tr>" +
            "</thead>" +
            "<tbody>" +
            tableRows +
            "</tbody>" +
            "<tfoot>" +
            "<tr style='background: #f8f9fa; font-weight: bold;'>" +
            "<td colspan='5' style='padding: 12px; text-align: right;'>Total Kekurangan:</td>" +
            "<td style='padding: 12px; text-align: right; color: #dc3545; font-size: 1.1em;'>" +
            totalKekurangan +
            "</td>" +
            "</tr>" +
            "</tfoot>" +
            "</table>" +
            "</div>" +
            "<div style='margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 8px;'>" +
            "<p style='margin:0; color: #2e7d32;'><i class='fa fa-info-circle'></i> Silakan hubungi bagian keuangan untuk melakukan pembayaran. Setelah pembayaran lunas, Anda dapat mencetak kartu ujian.</p>" +
            "</div>" +
            "</div>" +
            "</div>";

          if ($$("kartuUjianContent")) {
            $$("kartuUjianContent").setHTML(errorHtml);
          }
        }
      } catch (e) {
        proses_hide();
        console.error("Error parsing tagihan response:", e);
        webix.alert({
          title: "Gagal",
          type: "alert-error",
          text: "Gagal mengecek status pembayaran. Periksa koneksi internet.",
        });
      }
    })
    .fail(function () {
      proses_hide();
      webix.alert({
        title: "Gagal",
        type: "alert-error",
        text: "Gagal mengecek status pembayaran. Periksa koneksi internet.",
      });
    });
}

// Helper function to format Rupiah in JS
function formatRupiahJs(amount) {
  if (typeof amount !== "number") amount = parseInt(amount) || 0;
  return "Rp " + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function cetakKartuUTS(id_smt) {
  cetakKartuHelper("UTS", id_smt);
}

function cetakKartuUAS(id_smt) {
  cetakKartuHelper("UAS", id_smt);
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
