/* DILARANG AKSES */
var wSiaMhs = webix.storage.session.get("wSiaMhs");

var aksesDitolak = new WebixView({
  config: {
    type: "clean",
    rows: [
      { template: "", height: 50 },
      {
        type: "clean",
        template:
          "<center><img src='../gambar/logo_center.png' width='200'></center>",
      },
      { template: "", height: 20 },
      { template: "<h2 align='center'>Belum Masa KRS</h2>", height: 70 },
      { template: "" },
    ],
  },
});

var winDitolak = new webix.ui({
  view: "window",
  height: 250,
  width: 300,
  head: "Hak Akses Ditolak",
  position: "center",
  body: {
    template: "Maaf, masa pengisian KRS sudah ditutup",
  },
});

//Utama
var formUtama = new WebixView({
  config: {
    type: "clean",
    rows: [
      { template: "", height: 5 },
      {
        type: "clean",
        height: 160,
        template:
          "<center><img src='../gambar/logo_center.png' height='150'></center>",
      },
      {
        cols: [
          { template: "" },
          {
            view: "form",
            id: "formUtama",
            css: "formLogin",
            scroll: false,
            width: 500,
            borderless: true,
            elements: [
              {
                view: "fieldset",
                label: "Halaman Mahasiswa",
                body: {
                  template:
                    "<h2 class='info' align='center'>SELAMAT DATANG DI SIAKAD<br>POLITEKNIK INDONUSA SURAKARTA</h2>",
                  height: 80,
                  borderless: true,
                },
              },
            ],
          },
          { template: "" },
        ],
      },
      { template: "" },
    ],
  },
});

webix.type(webix.ui.list, {
  name: "myUploader",
  scroll: false,
  template: function (f, type) {
    var html = "<div class='overall'><div class='name'>" + f.name + "</div>";
    html +=
      "<div class='remove_file'><span style='color:#AAA' class='cancel_icon'></span></div>";
    html += "<div class='status'>";
    html +=
      "<div class='progress " +
      f.status +
      "' style='width:" +
      (f.status == "transfer" || f.status == "server"
        ? f.percent + "%"
        : "0px") +
      "'></div>";
    html +=
      "<div class='message " + f.status + "'>" + type.status(f) + "</div>";
    html += "</div>";
    html += "<div class='size'>" + f.sizetext + "</div></div>";
    return html;
  },
  status: function (f) {
    var messages = {
      server: "Berhasil",
      error: "Gagal",
      client: "Siap Upload",
      transfer: f.percent + "%",
    };
    return messages[f.status];
  },
  on_click: {
    remove_file: function (ev, id) {
      $$(this.config.uploader).files.remove(id);
    },
  },
  autoheight: true,
  borderless: true,
});

/* HALAMAN AKUN */
var halamanAkun = new WebixView({
  config: {
    type: "clean",
    rows: [
      {
        view: "toolbar",
        paddingY: 10,
        paddingX: 20,
        css: "toolbar_premium",
        cols: [
          { view: "label", label: "PENGATURAN AKUN", css: "header_title" },
          {},
        ],
      },
      {
        view: "scrollview",
        scroll: "y",
        body: {
          padding: 20,
          rows: [
            {
              cols: [
                {
                  // Kolom Kiri: Profil & Foto
                  width: 350,
                  rows: [
                    {
                      view: "form",
                      id: "formFoto",
                      css: "card_premium",
                      padding: 24,
                      elements: [
                        {
                          template: "<div class='card_title'>Foto Profil</div>",
                          height: 35,
                          borderless: true,
                        },
                        {
                          id: "fotoMhs",
                          template: function (obj) {
                            var timestamp = new Date().getTime();
                            return `<center>
                                                            <div class='profile_image_container'>
                                                                <img src='foto/${wSiaMhs.nipdMd5}.jpg?v=${timestamp}' class='profile_img' onerror="this.src='../gambar/no-foto.jpg'">
                                                            </div>
                                                        </center>`;
                          },
                          height: 180,
                          borderless: true,
                        },
                        {
                          view: "uploader",
                          id: "foto",
                          name: "upload",
                          label: "Pilih Foto",
                          css: "webix_secondary",
                          autosend: false,
                          multiple: false,
                          upload:
                            "uploader/mahasiswa/foto/" +
                            wSiaMhs.apiKey +
                            "/" +
                            Math.random(),
                          accept: "image/png, image/jpeg, image/jpg",
                          height: 45,
                          on: {
                            onBeforeFileAdd: function (item) {
                              var type = item.type.toLowerCase();
                              if (
                                type != "jpg" &&
                                type != "png" &&
                                type != "jpeg"
                              ) {
                                webix.message(
                                  "Hanya file JPG, JPEG, atau PNG yang diperbolehkan",
                                );
                                return false;
                              }

                              $$("updateFoto").show();

                              // Preview Image
                              var file = item.file;
                              var reader = new FileReader();
                              reader.onload = function (e) {
                                var imgHtml = `<center>
                                                                    <div class='profile_image_container'>
                                                                        <img src='${e.target.result}' class='profile_img'>
                                                                    </div>
                                                                </center>`;
                                $$("fotoMhs").setHTML(imgHtml);
                              };
                              reader.readAsDataURL(file);
                            },
                            onFileUpload: function (item, response) {
                              // Handle Success
                              console.log("Upload Success:", response);
                              var result = response; // Webix usually parses it
                              if (typeof response === "string") {
                                try {
                                  result = JSON.parse(response);
                                } catch (e) {
                                  result = {};
                                }
                              }

                              var timestamp =
                                result.timestamp || new Date().getTime();
                              $$("fotoMhs").setHTML(
                                "<center><div class='profile_image_container'><img src='foto/" +
                                  wSiaMhs.nipdMd5 +
                                  ".jpg?v=" +
                                  timestamp +
                                  "' class='profile_img'></div></center>",
                              );

                              if (item && item.id)
                                $$("foto").files.remove(item.id);

                              webix.message({
                                type: "success",
                                text:
                                  result.message || "Foto berhasil diupload",
                              });
                              $$("updateFoto").hide();
                            },
                            onFileUploadError: function (item, response) {
                              // Handle Error
                              console.log("Upload Error:", response);
                              var msg = "Gagal mengupload foto";
                              if (response && response.message)
                                msg = response.message;

                              // Try to read invalid response
                              if (
                                typeof response === "string" &&
                                response.includes("error")
                              ) {
                                // manually logging mechanism if needed
                              }

                              peringatan("Kesalahan", msg);
                              $$("updateFoto").enable();
                            },
                          },
                        },
                        {
                          view: "button",
                          id: "updateFoto",
                          label: "Upload Sekarang",
                          css: "webix_primary",
                          height: 45,
                          hidden: true, // Show only when file selected
                          click: function () {
                            updateFoto();
                          },
                        },
                      ],
                    },
                    { height: 20 },
                    {
                      view: "form",
                      id: "formGoogleLink",
                      css: "card_premium",
                      padding: 24,
                      elements: [
                        {
                          template:
                            "<div class='card_title'>Login Google</div>",
                          height: 35,
                          borderless: true,
                        },
                        {
                          template:
                            "<div class='card_desc'>Tautkan akun Google Poltek untuk kemudahan login.</div>",
                          height: 60,
                          borderless: true,
                        },
                        {
                          id: "google_btn_container",
                          view: "template",
                          height: 80,
                          borderless: true,
                          template: "<div class='google_link_btn_v2'></div>",
                          on: {
                            onAfterRender: function () {
                              var scope = this;
                              webix
                                .ajax()
                                .get(
                                  "sopingi/profil/tampil/" +
                                    wSiaMhs.apiKey +
                                    "/" +
                                    Math.random(),
                                  function (text) {
                                    var data = JSON.parse(text);
                                    var container = scope.$view.querySelector(
                                      ".google_link_btn_v2",
                                    );
                                    if (data.email_poltek) {
                                      container.innerHTML = `
                                                                        <div class='linked_account'>
                                                                            <div class='email_text'>${data.email_poltek}</div>
                                                                            <span class='unlink_btn' onclick='window.unlinkGoogleAccount()'>Putuskan</span>
                                                                        </div>`;
                                    } else {
                                      if (typeof google !== "undefined") {
                                        google.accounts.id.initialize({
                                          client_id:
                                            "594821951155-gjnu9qb2g2sltb67qvrr6frjmggvc6n5.apps.googleusercontent.com",
                                          callback: handleLinkGoogleResponse,
                                        });
                                        google.accounts.id.renderButton(
                                          container,
                                          {
                                            theme: "filled_blue",
                                            size: "large",
                                            width: 280,
                                          },
                                        );
                                      }
                                    }
                                  },
                                );
                            },
                          },
                        },
                      ],
                    },
                  ],
                },
                { width: 25 },
                {
                  // Kolom Kanan: Password SIAKAD & Akun Moodle
                  rows: [
                    {
                      view: "form",
                      id: "formAkun",
                      css: "card_premium",
                      padding: 24,
                      elements: [
                        {
                          template:
                            "<div class='card_title'><i class='fa fa-lock'></i> Keamanan SIAKAD</div>",
                          height: 35,
                          borderless: true,
                        },
                        {
                          cols: [
                            {
                              view: "text",
                              id: "pass",
                              name: "pass",
                              type: "password",
                              label: "Password Lama",
                              labelPosition: "top",
                              required: true,
                              placeholder: "Isi password saat ini",
                            },
                            { width: 15 },
                            {
                              view: "text",
                              id: "passBaru1",
                              name: "passBaru1",
                              type: "password",
                              label: "Password Baru",
                              labelPosition: "top",
                              required: true,
                              placeholder: "Min. 6 karakter",
                            },
                          ],
                        },
                        {
                          cols: [
                            {
                              view: "text",
                              id: "passBaru",
                              name: "passBaru",
                              type: "password",
                              label: "Konfirmasi Password Baru",
                              labelPosition: "top",
                              required: true,
                              placeholder: "Ulangi password baru",
                            },
                            { width: 15 },
                            {
                              view: "button",
                              id: "simpanAkun",
                              label: "Ganti Password",
                              css: "webix_primary",
                              align: "right",
                              width: 180,
                              height: 45,
                              inputHeight: 45,
                              css: "btn_action",
                            },
                          ],
                          paddingY: 10,
                        },
                        {
                          view: "text",
                          id: "aksi",
                          name: "aksi",
                          value: "ubahAkun",
                          hidden: true,
                        },
                      ],
                    },
                    { height: 25 },
                    {
                      view: "form",
                      id: "formMoodle",
                      css: "card_premium moodle_card",
                      padding: 24,
                      elements: [
                        {
                          cols: [
                            {
                              template:
                                "<div class='card_title'><i class='fa fa-graduation-cap'></i> Akun Learning (Moodle)</div>",
                              borderless: true,
                            },
                            {
                              id: "moodle_status_badge",
                              template:
                                "<div class='status_badge loading'>Checking...</div>",
                              width: 120,
                              borderless: true,
                            },
                          ],
                        },
                        {
                          template:
                            "<div class='card_desc'>Kelola email dan password khusus untuk akses portal E-Learning.</div>",
                          height: 40,
                          borderless: true,
                        },
                        {
                          cols: [
                            {
                              view: "text",
                              id: "moodle_email",
                              name: "email",
                              label: "Email Learning",
                              labelPosition: "top",
                              placeholder: "Contoh: username@gmail.com",
                            },
                            { width: 15 },
                            {
                              view: "text",
                              id: "moodle_pass",
                              name: "password",
                              type: "password",
                              label: "Password Baru (Opsional)",
                              labelPosition: "top",
                              placeholder: "Kosongkan jika tidak diubah",
                            },
                          ],
                        },
                        {
                          template:
                            "<div class='moodle_hint'>*Syarat Password: Minimal 8 karakter, ada Huruf Besar, Huruf Kecil, Angka, dan Simbol.</div>",
                          height: 40,
                          borderless: true,
                        },
                        {
                          cols: [
                            {},
                            {
                              view: "button",
                              id: "btnUpdateMoodle",
                              label: "Perbarui Akun Learning",
                              css: "webix_primary btn_moodle",
                              width: 250,
                              height: 45,
                            },
                          ],
                        },
                      ],
                    },
                  ],
                },
              ],
            },
            { height: 100 },
          ],
        },
      },
    ],
  },
});

/*HALAMAN PROFIL MAHASISWA */

var alamatView = {
  view: "scrollview",
  id: "alamatView",
  scroll: "y",
  body: {
    rows: [
      {
        view: "text",
        label: "No. KK",
        name: "no_kk",
        id: "no_kk",
        placeholder: "Nomor KK tanpa tanda baca",
        invalidMessage: "No KK belum diisi",
        inputWidth: 300,
        attributes: { maxlength: 16 },
      },
      {
        view: "text",
        label: "NIK",
        name: "nik",
        id: "nik",
        required: true,
        placeholder: "Nomor KTP tanpa tanda baca",
        invalidMessage: "NIK belum diisi",
        inputWidth: 300,
        attributes: { maxlength: 16 },
      },
      {
        view: "text",
        label: "Negara",
        name: "kewarganegaraan",
        id: "kewarganegaraan",
        required: true,
        value: "Indonesia",
        readonly: true,
        inputWidth: 300,
      },
      {
        view: "text",
        label: "Jalan",
        name: "jln",
        id: "jln",
        placeholder: "Jalan alamat rumah (Jika ada)",
        inputWidth: 700,
      },
      {
        view: "text",
        label: "Dusun",
        name: "nm_dsn",
        id: "nm_dsn",
        placeholder: "Nama dusun (Jika ada)",
        inputWidth: 350,
      },
      {
        view: "counter",
        label: "RT",
        name: "rt",
        id: "rt",
        placeholder: "RT (Jika ada)",
        inputWidth: 200,
      },
      {
        view: "counter",
        label: "RW",
        name: "rw",
        id: "rw",
        placeholder: "RW (Jika ada)",
        inputWidth: 200,
      },
      {
        view: "text",
        label: "Kelurahan",
        name: "ds_kel",
        id: "ds_kel",
        required: true,
        placeholder: "Nama Kelurahan/ desa",
        inputWidth: 350,
        invalidMessage: "Kelurahan belum diisi",
      },
      {
        view: "text",
        label: "Kode POS",
        name: "kode_pos",
        id: "kode_pos",
        placeholder: "Kode Pos",
        inputWidth: 250,
        attributes: { maxlength: 5 },
      },

      {
        view: "combo",
        label: "Kecamatan",
        name: "id_wil",
        id: "id_wil",
        options:
          "sopingi/wilayah/tampil/" + wSiaMhs.apiKey + "/" + Math.random(),
        placeholder: "Ketik kecamatan sampai muncul Kab dan Provinsi",
        required: true,
        invalidMessage: "Wilayah belum dipilih",
        inputWidth: 700,
      },
      {
        view: "richselect",
        label: "Jenis Tinggal",
        name: "id_jns_tinggal",
        id: "id_jns_tinggal",
        placeholder: "Pilih Jenis Tinggal",
        required: true,
        invalidMessage: "Jenis tinggal belum dipilih",
        options: [
          { id: 1, value: "Bersama orang tua" },
          { id: 2, value: "Wali" },
          { id: 3, value: "Kost" },
          { id: 4, value: "Asrama" },
          { id: 5, value: "Panti asuhan" },
          { id: 99, value: "Lainnya" },
        ],
        inputWidth: 350,
      },
      {
        view: "text",
        label: "Telepon",
        name: "telepon_rumah",
        id: "telepon_rumah",
        required: true,
        placeholder: "Telepon Rumah",
        inputWidth: 350,
      },
      {
        view: "text",
        label: "Handphone",
        name: "telepon_seluler",
        id: "telepon_seluler",
        placeholder: "No. Handphone",
        inputWidth: 350,
      },
      {
        view: "text",
        label: "Email",
        name: "email",
        id: "email",
        placeholder: "Email",
        required: true,
        inputWidth: 400,
      },
      {
        cols: [
          {
            view: "radio",
            label: "Penerima KPS",
            name: "a_terima_kps",
            id: "a_terima_kps",
            required: true,
            inputWidth: 250,
            options: [
              { id: "0", value: "Tidak" },
              { id: "1", value: "Ya" },
            ],
            invalidMessage: "KPS belum dipilih",
          },
          { template: " ", borderless: true, width: 10 },
          {
            view: "text",
            label: "No. KPS",
            name: "no_kps",
            id: "no_kps",
            placeholder: "No. Kartu Perlindungan Sosial",
            inputWidth: 300,
          },
          { template: "*KPS: Kartu Perlindungan Sosial", borderless: true },
        ],
      },
    ],
  },
};

var ortuView = {
  view: "scrollview",
  id: "ortuView",
  scroll: "y",
  body: {
    rows: [
      { template: "Profil Ayah", type: "section" },
      {
        view: "text",
        label: "Nama Ayah",
        name: "nm_ayah",
        id: "nm_ayah",
        required: true,
        placeholder: "Nama Ayah kandung",
        invalidMessage: "Nama Ayah belum diisi",
        inputWidth: 500,
      },
      {
        view: "datepicker",
        label: "Tanggal Lahir",
        name: "tgl_lahir_ayah",
        id: "tgl_lahir_ayah",
        format: "%d-%m-%Y",
        required: true,
        placeholder: "Tanggal lahir",
        invalidMessage: "Tanggal lahir ayah belum diisi",
        inputWidth: 250,
        stringResult: true,
      },
      {
        view: "richselect",
        label: "Pendidikan",
        name: "id_jenjang_pendidikan_ayah",
        id: "id_jenjang_pendidikan_ayah",
        placeholder: "Pilih Pendidikan",
        required: true,
        invalidMessage: "Pendidikan ayah belum dipilih",
        options: [
          { id: 0, value: "Tidak sekolah" },
          { id: 1, value: "PAUD" },
          { id: 2, value: "TK / sederajat" },
          { id: 3, value: "Putus SD" },
          { id: 4, value: "SD / sederajat" },
          { id: 5, value: "SMP / sederajat" },
          { id: 6, value: "SMA / sederajat" },
          { id: 7, value: "Paket A" },
          { id: 8, value: "Paket B" },
          { id: 9, value: "Paket C" },
          { id: 20, value: "D1" },
          { id: 21, value: "D2" },
          { id: 22, value: "D3" },
          { id: 23, value: "D4" },
          { id: 25, value: "Profesi" },
          { id: 30, value: "S1" },
          { id: 32, value: "Sp-1" },
          { id: 35, value: "S2" },
          { id: 37, value: "Sp-2" },
          { id: 40, value: "S3" },
          { id: 90, value: "Non formal" },
          { id: 91, value: "Informal" },
          { id: 99, value: "Lainnya" },
        ],
        inputWidth: 280,
      },
      {
        view: "richselect",
        label: "Pekerjaan",
        name: "id_pekerjaan_ayah",
        id: "id_pekerjaan_ayah",
        placeholder: "Pilih Pekerjaan",
        required: true,
        invalidMessage: "Pekerjaan ayah belum dipilih",
        options: [
          { id: 1, value: "Tidak bekerja" },
          { id: 2, value: "Nelayan" },
          { id: 3, value: "Petani" },
          { id: 4, value: "Peternak" },
          { id: 5, value: "PNS/TNI/Polri" },
          { id: 6, value: "Karyawan Swasta" },
          { id: 7, value: "Pedagang Kecil" },
          { id: 8, value: "Pedagang Besar" },
          { id: 9, value: "Wiraswasta" },
          { id: 10, value: "Wirausaha" },
          { id: 11, value: "Buruh" },
          { id: 12, value: "Pensiunan" },
          { id: 99, value: "Lainnya" },
          { id: 98, value: "Sudah Meninggal" },
        ],
        inputWidth: 280,
      },
      {
        view: "richselect",
        label: "Penghasilan",
        name: "id_penghasilan_ayah",
        id: "id_penghasilan_ayah",
        placeholder: "Pilih Penghasilan",
        required: true,
        invalidMessage: "Penghasilan ayah belum dipilih",
        options: [
          { id: 0, value: "Tidak ada" },
          { id: 11, value: "Kurang dari Rp. 500,000" },
          { id: 12, value: "Rp. 500,000 - Rp. 999,999" },
          { id: 13, value: "Rp. 1,000,000 - Rp. 1,999,999" },
          { id: 14, value: "Rp. 2,000,000 - Rp. 4,999,999" },
          { id: 15, value: "Rp. 5,000,000 - Rp. 20,000,000" },
          { id: 16, value: "Lebih dari Rp. 20,000,000" },
        ],
        inputWidth: 350,
      },

      { template: "Profil Ibu", type: "section" },
      {
        view: "text",
        label: "Nama Ibu",
        name: "vnm_ibu_kandung",
        id: "vnm_ibu_kandung",
        readonly: true,
        placeholder: "Nama ibu kandung",
        inputWidth: 500,
      },
      {
        view: "datepicker",
        label: "Tanggal Lahir",
        name: "tgl_lahir_ibu",
        id: "tgl_lahir_ibu",
        format: "%d-%m-%Y",
        required: true,
        placeholder: "Tanggal lahir",
        invalidMessage: "Tanggal lahir ibu belum diisi",
        inputWidth: 250,
        stringResult: true,
      },
      {
        view: "richselect",
        label: "Pendidikan",
        name: "id_jenjang_pendidikan_ibu",
        id: "id_jenjang_pendidikan_ibu",
        placeholder: "Pilih Pendidikan",
        required: true,
        invalidMessage: "Pendidikan ibu belum dipilih",
        options: [
          { id: 0, value: "Tidak sekolah" },
          { id: 1, value: "PAUD" },
          { id: 2, value: "TK / sederajat" },
          { id: 3, value: "Putus SD" },
          { id: 4, value: "SD / sederajat" },
          { id: 5, value: "SMP / sederajat" },
          { id: 6, value: "SMA / sederajat" },
          { id: 7, value: "Paket A" },
          { id: 8, value: "Paket B" },
          { id: 9, value: "Paket C" },
          { id: 20, value: "D1" },
          { id: 21, value: "D2" },
          { id: 22, value: "D3" },
          { id: 23, value: "D4" },
          { id: 25, value: "Profesi" },
          { id: 30, value: "S1" },
          { id: 32, value: "Sp-1" },
          { id: 35, value: "S2" },
          { id: 37, value: "Sp-2" },
          { id: 40, value: "S3" },
          { id: 90, value: "Non formal" },
          { id: 91, value: "Informal" },
          { id: 99, value: "Lainnya" },
        ],
        inputWidth: 280,
      },
      {
        view: "richselect",
        label: "Pekerjaan",
        name: "id_pekerjaan_ibu",
        id: "id_pekerjaan_ibu",
        placeholder: "Pilih Pekerjaan",
        required: true,
        invalidMessage: "Pekerjaan ibu belum dipilih",
        options: [
          { id: 1, value: "Tidak bekerja" },
          { id: 2, value: "Nelayan" },
          { id: 3, value: "Petani" },
          { id: 4, value: "Peternak" },
          { id: 5, value: "PNS/TNI/Polri" },
          { id: 6, value: "Karyawan Swasta" },
          { id: 7, value: "Pedagang Kecil" },
          { id: 8, value: "Pedagang Besar" },
          { id: 9, value: "Wiraswasta" },
          { id: 10, value: "Wirausaha" },
          { id: 11, value: "Buruh" },
          { id: 12, value: "Pensiunan" },
          { id: 99, value: "Lainnya" },
          { id: 98, value: "Sudah Meninggal" },
        ],
        inputWidth: 280,
      },
      {
        view: "richselect",
        label: "Penghasilan",
        name: "id_penghasilan_ibu",
        id: "id_penghasilan_ibu",
        placeholder: "Pilih Penghasilan",
        required: true,
        invalidMessage: "Penghasilan ibu belum dipilih",
        options: [
          { id: 0, value: "Tidak ada" },
          { id: 11, value: "Kurang dari Rp. 500,000" },
          { id: 12, value: "Rp. 500,000 - Rp. 999,999" },
          { id: 13, value: "Rp. 1,000,000 - Rp. 1,999,999" },
          { id: 14, value: "Rp. 2,000,000 - Rp. 4,999,999" },
          { id: 15, value: "Rp. 5,000,000 - Rp. 20,000,000" },
          { id: 16, value: "Lebih dari Rp. 20,000,000" },
        ],
        inputWidth: 350,
      },
    ],
  },
};

var waliView = {
  view: "scrollview",
  id: "waliView",
  scroll: "y",
  body: {
    rows: [
      { template: "Profil Wali", type: "section" },
      {
        view: "text",
        label: "Nama Wali",
        name: "nm_wali",
        id: "nm_wali",
        placeholder: "Nama wali",
        invalidMessage: "Nama wali belum diisi",
        inputWidth: 500,
      },
      {
        view: "datepicker",
        label: "Tanggal Lahir",
        name: "tgl_lahir_wali",
        id: "tgl_lahir_wali",
        format: "%d-%m-%Y",
        placeholder: "Tanggal lahir",
        invalidMessage: "Tanggal lahir wali belum diisi",
        inputWidth: 250,
        stringResult: true,
      },
      {
        view: "richselect",
        label: "Pendidikan",
        name: "id_jenjang_pendidikan_wali",
        id: "id_jenjang_pendidikan_wali",
        placeholder: "Pilih Pendidikan",
        invalidMessage: "Pendidikan wali belum dipilih",
        options: [
          { id: 0, value: "Tidak sekolah" },
          { id: 1, value: "PAUD" },
          { id: 2, value: "TK / sederajat" },
          { id: 3, value: "Putus SD" },
          { id: 4, value: "SD / sederajat" },
          { id: 5, value: "SMP / sederajat" },
          { id: 6, value: "SMA / sederajat" },
          { id: 7, value: "Paket A" },
          { id: 8, value: "Paket B" },
          { id: 9, value: "Paket C" },
          { id: 20, value: "D1" },
          { id: 21, value: "D2" },
          { id: 22, value: "D3" },
          { id: 23, value: "D4" },
          { id: 25, value: "Profesi" },
          { id: 30, value: "S1" },
          { id: 32, value: "Sp-1" },
          { id: 35, value: "S2" },
          { id: 37, value: "Sp-2" },
          { id: 40, value: "S3" },
          { id: 90, value: "Non formal" },
          { id: 91, value: "Informal" },
          { id: 99, value: "Lainnya" },
        ],
        inputWidth: 280,
      },
      {
        view: "richselect",
        label: "Pekerjaan",
        name: "id_pekerjaan_wali",
        id: "id_pekerjaan_wali",
        placeholder: "Pilih Pekerjaan",
        invalidMessage: "Pekerjaan wali belum dipilih",
        options: [
          { id: 1, value: "Tidak bekerja" },
          { id: 2, value: "Nelayan" },
          { id: 3, value: "Petani" },
          { id: 4, value: "Peternak" },
          { id: 5, value: "PNS/TNI/Polri" },
          { id: 6, value: "Karyawan Swasta" },
          { id: 7, value: "Pedagang Kecil" },
          { id: 8, value: "Pedagang Besar" },
          { id: 9, value: "Wiraswasta" },
          { id: 10, value: "Wirausaha" },
          { id: 11, value: "Buruh" },
          { id: 12, value: "Pensiunan" },
          { id: 99, value: "Lainnya" },
          { id: 98, value: "Sudah Meninggal" },
        ],
        inputWidth: 280,
      },
      {
        view: "richselect",
        label: "Penghasilan",
        name: "id_penghasilan_wali",
        id: "id_penghasilan_wali",
        placeholder: "Pilih Penghasilan",
        invalidMessage: "Penghasilan wali belum dipilih",
        options: [
          { id: 0, value: "Tidak ada" },
          { id: 11, value: "Kurang dari Rp. 500,000" },
          { id: 12, value: "Rp. 500,000 - Rp. 999,999" },
          { id: 13, value: "Rp. 1,000,000 - Rp. 1,999,999" },
          { id: 14, value: "Rp. 2,000,000 - Rp. 4,999,999" },
          { id: 15, value: "Rp. 5,000,000 - Rp. 20,000,000" },
          { id: 16, value: "Lebih dari Rp. 20,000,000" },
        ],
        inputWidth: 350,
      },
    ],
  },
};

var kebutuhanView = {
  view: "scrollview",
  id: "kebutuhanView",
  scroll: "y",
  body: {
    rows: [
      { template: "Mahasiswa", type: "section" },
      {
        cols: [
          {
            rows: [
              {
                view: "checkbox",
                id: "mhs_a_kk_a",
                name: "mhs_a_kk_a",
                label: "A - Tuna netra",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_b",
                name: "mhs_a_kk_b",
                label: "B - Tuna rungu",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_c",
                name: "mhs_a_kk_c",
                label: "C - Tuna grahita ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_c1",
                name: "mhs_a_kk_c1",
                label: "C1 - Tuna grahita ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_d",
                name: "mhs_a_kk_d",
                label: "D - Tuna daksa ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_d1",
                name: "mhs_a_kk_d1",
                label: "D1 - Tuna daksa sedang",
                labelWidth: 150,
                labelAlign: "right",
              },
            ],
          },
          {
            rows: [
              {
                view: "checkbox",
                id: "mhs_a_kk_e",
                name: "mhs_a_kk_e",
                label: "E - Tuna laras",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_f",
                name: "mhs_a_kk_f",
                label: "F - Tuna wicara",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_h",
                name: "mhs_a_kk_h",
                label: "H - Hiperaktif",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_i",
                name: "mhs_a_kk_i",
                label: "I - Cerdas Istimewa",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_j",
                name: "mhs_a_kk_j",
                label: "J - Bakat Istimewa",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_k",
                name: "mhs_a_kk_k",
                label: "K - Kesulitan Belajar",
                labelWidth: 150,
                labelAlign: "right",
              },
            ],
          },
          {
            rows: [
              {
                view: "checkbox",
                id: "mhs_a_kk_n",
                name: "mhs_a_kk_n",
                label: "N - Narkoba",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_o",
                name: "mhs_a_kk_o",
                label: "O - Indigo",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_p",
                name: "mhs_a_kk_p",
                label: "P - Down Syndrome",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "mhs_a_kk_q",
                name: "mhs_a_kk_q",
                label: "Q - Autis",
                labelWidth: 150,
                labelAlign: "right",
              },
              { template: " ", borderless: true },
            ],
          },
          { template: " ", borderless: true },
        ],
      },
      { template: "Ayah", type: "section" },
      {
        cols: [
          {
            rows: [
              {
                view: "checkbox",
                id: "ayah_a_kk_a",
                name: "ayah_a_kk_a",
                label: "A - Tuna netra",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_b",
                name: "ayah_a_kk_b",
                label: "B - Tuna rungu",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_c",
                name: "ayah_a_kk_c",
                label: "C - Tuna grahita ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_c1",
                name: "ayah_a_kk_c1",
                label: "C1 - Tuna grahita ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_d",
                name: "ayah_a_kk_d",
                label: "D - Tuna daksa ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_d1",
                name: "ayah_a_kk_d1",
                label: "D1 - Tuna daksa sedang",
                labelWidth: 150,
                labelAlign: "right",
              },
            ],
          },
          {
            rows: [
              {
                view: "checkbox",
                id: "ayah_a_kk_e",
                name: "ayah_a_kk_e",
                label: "E - Tuna laras",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_f",
                name: "ayah_a_kk_f",
                label: "F - Tuna wicara",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_h",
                name: "ayah_a_kk_h",
                label: "H - Hiperaktif",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_i",
                name: "ayah_a_kk_i",
                label: "I - Cerdas Istimewa",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_j",
                name: "ayah_a_kk_j",
                label: "J - Bakat Istimewa",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_k",
                name: "ayah_a_kk_k",
                label: "K - Kesulitan Belajar",
                labelWidth: 150,
                labelAlign: "right",
              },
            ],
          },
          {
            rows: [
              {
                view: "checkbox",
                id: "ayah_a_kk_n",
                name: "ayah_a_kk_n",
                label: "N - Narkoba",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_o",
                name: "ayah_a_kk_o",
                label: "O - Indigo",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_p",
                name: "ayah_a_kk_p",
                label: "P - Down Syndrome",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ayah_a_kk_q",
                name: "ayah_a_kk_q",
                label: "Q - Autis",
                labelWidth: 150,
                labelAlign: "right",
              },
              { template: " ", borderless: true },
            ],
          },
          { template: " ", borderless: true },
        ],
      },
      { template: "Ibu", type: "section" },
      {
        cols: [
          {
            rows: [
              {
                view: "checkbox",
                id: "ibu_a_kk_a",
                name: "ibu_a_kk_a",
                label: "A - Tuna netra",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_b",
                name: "ibu_a_kk_b",
                label: "B - Tuna rungu",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_c",
                name: "ibu_a_kk_c",
                label: "C - Tuna grahita ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_c1",
                name: "ibu_a_kk_c1",
                label: "C1 - Tuna grahita ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_d",
                name: "ibu_a_kk_d",
                label: "D - Tuna daksa ringan",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_d1",
                name: "ibu_a_kk_d1",
                label: "D1 - Tuna daksa sedang",
                labelWidth: 150,
                labelAlign: "right",
              },
            ],
          },
          {
            rows: [
              {
                view: "checkbox",
                id: "ibu_a_kk_e",
                name: "ibu_a_kk_e",
                label: "E - Tuna laras",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_f",
                name: "ibu_a_kk_f",
                label: "F - Tuna wicara",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_h",
                name: "ibu_a_kk_h",
                label: "H - Hiperaktif",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_i",
                name: "ibu_a_kk_i",
                label: "I - Cerdas Istimewa",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_j",
                name: "ibu_a_kk_j",
                label: "J - Bakat Istimewa",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_k",
                name: "ibu_a_kk_k",
                label: "K - Kesulitan Belajar",
                labelWidth: 150,
                labelAlign: "right",
              },
            ],
          },
          {
            rows: [
              {
                view: "checkbox",
                id: "ibu_a_kk_n",
                name: "ibu_a_kk_n",
                label: "N - Narkoba",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_o",
                name: "ibu_a_kk_o",
                label: "O - Indigo",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_p",
                name: "ibu_a_kk_p",
                label: "P - Down Syndrome",
                labelWidth: 150,
                labelAlign: "right",
              },
              {
                view: "checkbox",
                id: "ibu_a_kk_q",
                name: "ibu_a_kk_q",
                label: "Q - Autis",
                labelWidth: 150,
                labelAlign: "right",
              },
              { template: " ", borderless: true },
            ],
          },
          { template: " ", borderless: true },
        ],
      },
    ],
  },
};

var viewMahasiswaDetail = new WebixView({
  config: {
    id: "viewMahasiswaDetail",
    type: "space",
    rows: [
      {
        view: "toolbar",
        paddingY: 2,
        cols: [
          { view: "label", template: "Biodata Mahasiswa", borderless: true },
          {
            view: "button",
            id: "simpanMahasiswa",
            label: "Simpan",
            type: "iconButton",
            icon: "edit",
            width: 100,
          },
        ],
      },
      {
        view: "form",
        id: "formMahasiswaDetail",
        borderless: true,
        url: "sopingi/mahasiswa/tampil/" + wSiaMhs.apiKey + "/" + Math.random(),
        elements: [
          {
            cols: [
              {
                rows: [
                  {
                    view: "text",
                    label: "No.Daftar",
                    name: "no_pend",
                    id: "no_pend",
                    required: true,
                    placeholder: "No. Daftar (SPMB)",
                    invalidMessage: "No.Daftar belum diisi",
                    inputWidth: 450,
                    labelWidth: 150,
                    readonly: true,
                  },
                  {
                    view: "text",
                    label: "Nama",
                    name: "nm_pd",
                    id: "nm_pd",
                    required: true,
                    placeholder: "Nama Lengkap Sesuai Ijazah",
                    invalidMessage: "Nama belum diisi",
                    inputWidth: 400,
                    labelWidth: 150,
                  },
                  {
                    view: "text",
                    label: "Tempat Lahir",
                    name: "tmpt_lahir",
                    id: "tmpt_lahir",
                    required: true,
                    placeholder: "Tempat lahir",
                    invalidMessage: "Tempat lahir belum diisi",
                    inputWidth: 300,
                    labelWidth: 150,
                  },
                  {
                    view: "datepicker",
                    label: "Tanggal Lahir",
                    name: "tgl_lahir",
                    id: "tgl_lahir",
                    format: "%d-%m-%Y",
                    required: true,
                    editable: true,
                    placeholder: "dd-mm-yyyy",
                    invalidMessage: "Tanggal lahir belum diisi",
                    inputWidth: 300,
                    labelWidth: 150,
                    stringResult: true,
                  },
                  {
                    view: "radio",
                    label: "Jenis Kelamin",
                    name: "jk",
                    id: "jk",
                    required: true,
                    options: [
                      { id: "L", value: "Laki-laki" },
                      { id: "P", value: "Perempuan" },
                    ],
                    invalidMessage: "Jenis kelamin belum dipilih",
                    inputWidth: 350,
                    labelWidth: 150,
                  },
                  {
                    view: "richselect",
                    label: "Agama",
                    name: "id_agama",
                    id: "id_agama",
                    placeholder: "Pilih Agama",
                    required: true,
                    invalidMessage: "Agama belum dipilih",
                    options: [
                      { id: 1, value: "Islam" },
                      { id: 2, value: "Kristen" },
                      { id: 3, value: "Katholik" },
                      { id: 4, value: "Hindu" },
                      { id: 5, value: "Budha" },
                      { id: 6, value: "Konghucu" },
                      { id: 99, value: "Lainnya" },
                    ],
                    inputWidth: 280,
                    labelWidth: 150,
                  },
                  {
                    view: "text",
                    label: "Nama Ibu Kandung",
                    name: "nm_ibu_kandung",
                    id: "nm_ibu_kandung",
                    required: true,
                    placeholder: "Nama Ibu kandung sesuai KTP",
                    invalidMessage: "Nama ibu kandung belum diisi",
                    inputWidth: 400,
                    labelWidth: 150,
                  },
                ],
              },
              { template: " ", borderless: true, width: 50 },
              {
                css: "dataAkademik",
                rows: [
                  {
                    view: "text",
                    label: "No. Induk Mahasiswa",
                    name: "nipd",
                    id: "nipd",
                    placeholder: "NIM",
                    invalidMessage: "No.Daftar belum diisi",
                    inputWidth: 350,
                    labelWidth: 150,
                    readonly: true,
                  },
                  {
                    view: "richselect",
                    label: "Program Studi",
                    name: "id_sms",
                    id: "id_sms",
                    placeholder: "Pilih Program Studi",
                    invalidMessage: "Program studi belum dipilih",
                    options:
                      "sopingi/sms/pilih/" +
                      wSiaMhs.apiKey +
                      "/" +
                      Math.random(),
                    value: "",
                    inputWidth: 450,
                    labelWidth: 150,
                    readonly: true,
                  },
                  {
                    view: "richselect",
                    label: "Mulai Masuk",
                    name: "mulai_smt",
                    id: "mulai_smt",
                    placeholder: "Pilih Semester",
                    options:
                      "sopingi/semester/pilihSemua/" +
                      wSiaMhs.apiKey +
                      "/" +
                      Math.random(),
                    invalidMessage: "Semester belum dipilih",
                    inputWidth: 350,
                    labelWidth: 150,
                    readonly: true,
                  },
                  {
                    view: "richselect",
                    label: "Jenis Daftar",
                    name: "id_jns_daftar",
                    id: "id_jns_daftar",
                    placeholder: "Pilih Jenis Daftar",
                    invalidMessage: "Jenis Daftar belum dipilih",
                    options: [
                      { id: 1, value: "Mahasiswa Baru" },
                      { id: 2, value: "Pindahan/Transfer" },
                    ],
                    inputWidth: 350,
                    labelWidth: 150,
                    readonly: true,
                  },
                  {
                    view: "richselect",
                    label: "Kelas",
                    name: "kelas",
                    id: "kelas",
                    placeholder: "Pilih nama kelas",
                    invalidMessage: "Nama kelas belum dipilih",
                    options:
                      "sopingi/siakad_kelas/pilih/" +
                      wSiaMhs.apiKey +
                      "/" +
                      Math.random(),
                    inputWidth: 450,
                    labelWidth: 150,
                    readonly: true,
                  },
                  {
                    view: "combo",
                    label: "Pembimbing Akademik",
                    name: "pa",
                    id: "pa",
                    placeholder: "Pilih Dosen",
                    invalidMessage: "Pembimbing Akademik belum dipilih",
                    options:
                      "sopingi/dosen/pilih/" +
                      wSiaMhs.apiKey +
                      "/" +
                      Math.random(),
                    inputWidth: 450,
                    labelWidth: 150,
                    readonly: true,
                  },
                ],
              },
            ],
          },
          {
            view: "tabbar",
            id: "tabbar",
            value: "alamatView",
            multiview: true,
            options: [
              { value: "Alamat", id: "alamatView" },
              { value: "Orang Tua", id: "ortuView" },
              { value: "Wali", id: "waliView" },
              { value: "Kebutuhan Khusus", id: "kebutuhanView" },
            ],
          },
          { cells: [alamatView, ortuView, waliView, kebutuhanView] },

          { view: "text", name: "xid_pd", id: "xid_pd", hidden: true },
          { view: "text", name: "xid_reg_pd", id: "xid_reg_pd", hidden: true },
          {
            view: "text",
            name: "aksi",
            id: "aksi",
            value: "simpan",
            required: true,
            hidden: true,
          },
        ],
        on: {
          onValidationError: function (key, obj) {
            webix.message({ type: "error", text: key });
          },
        },
        elementsConfig: {
          labelPosition: "left",
          labelWidth: 100,
        },
      },
    ],
  },
});

/* HALAMAN KRS */
var formatUangId = webix.Number.numToStr({
  groupDelimiter: ".",
  groupSize: 3,
  decimalDelimiter: ",",
  decimalSize: 0,
});
var halamanTagihanKrs = new WebixView({
  config: {
    type: "space",
    borderless: true,
    rows: [
      {
        view: "toolbar",
        borderless: true,
        height: 30,
        cols: [
          {
            template: "KRS - Tagihan Pembayaran Belum Lunas",
            css: "headerBg",
            borderless: true,
          },
        ],
      },
      {
        template:
          "Mohon maaf Anda belum bisa mengisi KRS. Silahkan lakukan pembayaran berikut ini untuk melanjutkan pengisian KRS",
        height: 40,
      },
      {
        view: "datatable",
        select: true,
        id: "dataTableTagihanKrs",
        fixedRowHeight: false,
        columns: [
          { id: "index", header: "No", width: 40 },
          { id: "nama_biaya", header: "Nama Biaya", adjust: "data" },
          {
            id: "tagihan",
            header: "Tagihan",
            format: formatUangId,
            width: 120,
          },
          {
            id: "terbayar",
            header: "Terbayar",
            format: formatUangId,
            width: 120,
          },
          {
            id: "potongan",
            header: "Potongan",
            format: formatUangId,
            width: 120,
          },
          {
            id: "kekurangan",
            header: "Kekurangan",
            format: formatUangId,
            width: 120,
          },
        ],

        on: {
          "data->onStoreUpdated": function () {
            this.data.each(function (obj, i) {
              obj.index = i + 1;
            });
          },
        },
      },
    ],
  }, //config
});

var halamanKrs = new WebixView({
  config: {
    type: "space",
    borderless: true,
    rows: [
      {
        view: "toolbar",
        borderless: true,
        cols: [
          { template: "KRS Mahasiswa", css: "headerBg", borderless: true },
          {
            view: "button",
            label: "Tambah",
            id: "tambahKRS",
            type: "iconButton",
            icon: "plus",
            width: 100,
          },
          {
            view: "button",
            id: "hapusKRS",
            label: "Hapus",
            type: "iconButton",
            icon: "remove",
            width: 100,
          },
          {
            view: "button",
            label: "Refresh",
            id: "refreshKRS",
            type: "iconButton",
            icon: "refresh",
            width: 100,
          },
          {
            view: "button",
            label: "Download KRS PDF",
            id: "unduhKRS",
            type: "iconButton",
            icon: "file-pdf-o",
            width: 150,
          },
        ],
      },
      {
        view: "datatable",
        select: true,
        footer: true,
        id: "dataTableKrs",
        fixedRowHeight: false,
        columns: [
          {
            id: "index",
            header: "No",
            width: 40,
            footer: {
              text: "Jumlah SKS:",
              colspan: 4,
              css: { "text-align": "right", "font-weight": "bold" },
            },
          },
          { id: "nm_kls", header: "Kelas", adjust: "data" },
          { id: "kode_mk", header: "Kode MK", adjust: "data", sort: "string" },
          {
            id: "nm_mk",
            header: "Nama Mata Kuliah",
            fillspace: true,
            sort: "string",
          },
          {
            id: "vsks_mk",
            header: "Jml SKS",
            width: 40,
            sort: "string",
            footer: {
              id: "jSKS",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "vsks_tm",
            header: [
              {
                text: "Komposisi SKS",
                colspan: 3,
                css: { "text-align": "center" },
              },
              "T",
            ],
            width: 40,
            footer: {
              id: "jSKSt",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "vsks_prak",
            header: ["", "P"],
            width: 40,
            footer: {
              id: "jSKSp",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "vsks_prak_lap",
            header: ["", "K"],
            width: 40,
            footer: {
              id: "jSKSk",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          { id: "vid_smt", header: "Semester", adjust: "data", sort: "string" },
          {
            id: "learning_link",
            header: "Learning",
            width: 100,
            template: function (obj) {
              if (obj.learning_status === "Tersedia") {
                return (
                  "<a href='" +
                  obj.learning_link +
                  "' target='_blank' class='webix_button webix_primary' style='line-height:20px; padding:2px 10px; text-decoration:none; color:white; border-radius:4px;'>Akses</a>"
                );
              } else {
                return "<span style='color:#ccc'>-</span>";
              }
            },
          },
          {
            id: "dosen_pengampu",
            header: "Dosen Pengajar",
            adjust: "data",
            sort: "string",
          },
        ],
        pager: "pagerKrs",
        url: "sopingi/nilai/tampil/" + wSiaMhs.apiKey + "/" + Math.random(),
        on: {
          onBeforeLoad: function () {
            this.showOverlay(
              "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
            );
            $$("tambahKRS").disable();
            $$("hapusKRS").disable();
            $$("refreshKRS").disable();
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

            $$("tambahKRS").enable();
            $$("hapusKRS").enable();
            $$("refreshKRS").enable();
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
          },
        },
      },
      {
        view: "pager",
        id: "pagerKrs",
        css: "pager",
        template:
          "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKrs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS",
        size: 12,
        group: 5,
        animate: {
          direction: "left",
          type: "slide",
        },
      },
      {
        template:
          "Bagi yang ingin menambah mata kuliah untuk <b>Perbaikan Nilai</b>, silahkan konsultasi <b>Terlebih Dahulu</b> dengan Pembimbing Akademik masing-masing, sebelum mengisi KRS Online",
        height: 60,
      },
    ],
  }, //config
});

function checkbox_krs(obj, common, value) {
  if (value) {
    return "<div class='webix_table_checkbox webix_icon fa-check checked'> diambil</div>";
  } else {
    return "<div class='webix_table_checkbox webix_icon fa-close notchecked'> tidak diambil</div>";
  }
}

var formKRS = {
  rows: [
    {
      view: "scrollview",
      id: "scrollKRS",
      scroll: "y",
      height: 500,
      width: 800,
      body: {
        view: "form",
        id: "formKRS",
        borderless: true,
        elements: [
          {
            template:
              "<ul class='info_krs'><li>Pastikan mata kuliah yang diambil sudah TERCENTANG</li><li>Khusus mata kuliah Pendidikan Agama, silahkan pilih salah satu</li></ul>",
            height: 60,
            borderless: true,
          },
          {
            view: "datatable",
            label: "Mata Kuliah",
            id: "dataTableKelasPerkuliahan",
            autoheight: true,
            checkboxRefresh: true,
            columns: [
              { id: "index", header: "No", width: 30 },
              { id: "nm_kls", header: "Kelas", width: 50 },
              { id: "kode_mk", header: "Kode MK", sort: "string", width: 60 },
              {
                id: "nm_mk",
                header: "Nama Mata Kuliah",
                fillspace: true,
                sort: "string",
              },
              { id: "vsks_mk", header: "SKS", sort: "string", width: 40 },
              { id: "vid_smt", header: "Semester", width: 120 },
              {
                id: "ambilKelas",
                header: "Ambil (Klik Centang)",
                template: checkbox_krs,
                width: 150,
              },
            ],
            //url:"sopingi/kelas_kuliah/tampil/"+wSiaMhs.apiKey+"/"+Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
                $$("simpanKRS").disable();
              },
              onAfterLoad: function () {
                this.hideOverlay();
                $$("simpanKRS").enable();
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });
              },
            },
          },
          {
            view: "text",
            name: "aksi",
            id: "aksi",
            required: true,
            hidden: true,
            value: "tambah",
          },
        ],
        elementsConfig: {
          labelPosition: "top",
        },
      },
    },
    {
      cols: [
        { template: " ", borderless: true },
        { view: "icon", icon: "hand-o-right" },
        {
          view: "button",
          id: "simpanKRS",
          label: "Tambahkan Kelas Mata Kuliah",
          type: "form",
          width: 200,
          borderless: true,
        },
        { template: " ", borderless: true },
      ],
    },
  ],
};

/*TOLAK KRS*/
var halamanKrsDitolak = new WebixView({
  config: {
    type: "space",
    borderless: true,
    rows: [
      {
        view: "toolbar",
        borderless: true,
        height: 30,
        cols: [
          { template: "KRS Mahasiswa", css: "headerBg", borderless: true },
        ],
      },
      {
        template:
          "<h1 align='center'>Mohon maaf!, Anda belum diperbolehkan mengisi KRS. Silahkan melakukan registrasi terlebih dahulu dengan membayar kekurangan administrasi sebagai berikut</h1>",
        height: 110,
      },
      {
        id: "tagihan",
        view: "datatable",
        columns: [
          { id: "jenis_pembayaran", header: "Keterangan" },
          { id: "total", header: "Total", format: webix.i18n.intFormat },
          {
            id: "tagihan",
            header: "Tagihan Sampai Semester ini",
            adjust: true,
            format: webix.i18n.intFormat,
          },
          {
            id: "dibayar",
            header: "Sudah Dibayar",
            format: webix.i18n.intFormat,
          },
          { id: "potongan", header: "Potongan", format: webix.i18n.intFormat },
          {
            id: "kurang",
            header: "Kekurangan",
            format: webix.i18n.intFormat,
            cssFormat: function (value) {
              if (value != 0) {
                return "kurang";
              }
            },
          },
        ],
      },
    ],
  },
});

/* HALAMAN KRS LAMA UPDATE ANDRE 24012024*/
var halamanKrsLama = new WebixView({
  config: {
    id: "viewMahasiswaKRSLama",
    type: "space",
    borderless: true,
    rows: [
      {
        view: "toolbar",
        borderless: true,
        cols: [
          {
            template: "KRS Mahasiswa",
            css: "headerBg",
            borderless: true,
            width: 100,
          },
          {
            view: "richselect",
            label: "",
            name: "krs_id_smt",
            id: "krs_id_smt",
            placeholder: "Pilih semester",
            options:
              "sopingi/semester/pilih/" + wSiaMhs.apiKey + "/" + Math.random(),
            borderless: true,
            width: 220,
          },
          {
            view: "button",
            label: "KRS PDF",
            id: "krsPdf",
            type: "iconButton",
            icon: "file-pdf-o",
            width: 100,
          },
        ],
      },
      {
        view: "datatable",
        select: true,
        footer: true,
        id: "dataTableKrsLama",
        fixedRowHeight: false,
        columns: [
          {
            id: "index",
            header: "No",
            width: 40,
            footer: {
              text: "Jumlah SKS:",
              colspan: 4,
              css: { "text-align": "right", "font-weight": "bold" },
            },
          },
          { id: "nm_kls", header: "Kelas", adjust: "data" },
          { id: "kode_mk", header: "Kode MK", adjust: "data", sort: "string" },
          {
            id: "nm_mk",
            header: "Nama Mata Kuliah",
            fillspace: true,
            sort: "string",
          },
          {
            id: "vsks_mk",
            header: "Jml SKS",
            width: 40,
            sort: "string",
            footer: {
              id: "jSKS",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "vsks_tm",
            header: [
              {
                text: "Komposisi SKS",
                colspan: 3,
                css: { "text-align": "center" },
              },
              "T",
            ],
            width: 40,
            footer: {
              id: "jSKSt",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "vsks_prak",
            header: ["", "P"],
            width: 40,
            footer: {
              id: "jSKSp",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "vsks_prak_lap",
            header: ["", "K"],
            width: 40,
            footer: {
              id: "jSKSk",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          { id: "vid_smt", header: "Semester", adjust: "data", sort: "string" },
          {
            id: "dosen_pengampu",
            header: "Dosen Pengajar",
            adjust: "data",
            sort: "string",
          },
        ],
        pager: "pagerKrs",
        url: "sopingi/nilai/tampilW/" + wSiaMhs.apiKey + "/" + Math.random(),
        on: {
          onBeforeLoad: function () {
            this.showOverlay(
              "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
            );
            $$("krsPdf").disable();
            $$("krs_id_smt").disable();
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
            $$("krsPdf").enable();
            $$("krs_id_smt").enable();
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
          },
        },
      },
      {
        view: "pager",
        id: "pagerKrs",
        css: "pager",
        template:
          "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKrs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS",
        size: 12,
        group: 5,
        animate: {
          direction: "left",
          type: "slide",
        },
      },
    ],
  },
});

/* HALAMAN KARTU UJIAN */
var halamanKartuUjian = new WebixView({
  config: {
    id: "viewKartuUjian",
    type: "space",
    borderless: true,
    rows: [
      {
        view: "toolbar",
        borderless: true,
        cols: [
          {
            template: "Kartu Ujian",
            css: "headerBg",
            borderless: true,
            width: 120,
          },
          {
            view: "richselect",
            label: "Semester",
            name: "ujian_id_smt",
            id: "ujian_id_smt",
            placeholder: "Pilih semester",
            options:
              "sopingi/semester/pilih/" + wSiaMhs.apiKey + "/" + Math.random(),
            borderless: true,
            width: 250,
          },
          {},
          {
            view: "button",
            label: "Cetak Kartu UTS",
            id: "cetakUTS",
            type: "iconButton",
            icon: "file-pdf-o",
            width: 150,
            css: "webix_primary",
          },
          {
            view: "button",
            label: "Cetak Kartu UAS",
            id: "cetakUAS",
            type: "iconButton",
            icon: "file-pdf-o",
            width: 150,
            css: "webix_danger",
          },
        ],
      },
      {
        id: "kartuUjianContent",
        template:
          "<div style='padding:20px;'>" +
          "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;'>" +
          "<h2 style='margin:0 0 15px 0;'><i class='fa fa-file-text-o'></i> Kartu Ujian Online</h2>" +
          "<p style='margin:0; opacity: 0.9;'>Cetak kartu ujian untuk mengikuti Ujian Tengah Semester (UTS) atau Ujian Akhir Semester (UAS).</p>" +
          "</div>" +
          "<div style='display: flex; gap: 20px; flex-wrap: wrap;'>" +
          "<div style='flex: 1; min-width: 280px; background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);'>" +
          "<h3 style='margin: 0 0 15px 0; color: #333;'><i class='fa fa-info-circle' style='color: #667eea;'></i> Petunjuk</h3>" +
          "<ol style='margin: 0; padding-left: 20px; color: #555; line-height: 1.8;'>" +
          "<li>Pilih semester yang diinginkan (opsional, default semester aktif)</li>" +
          "<li>Klik tombol <b>Cetak Kartu UTS</b> atau <b>Cetak Kartu UAS</b></li>" +
          "<li>Sistem akan mengecek status pembayaran Anda</li>" +
          "<li>Jika pembayaran lunas, kartu ujian akan terunduh</li>" +
          "<li>Jika ada tunggakan, akan ditampilkan daftar tagihan</li>" +
          "</ol>" +
          "</div>" +
          "<div style='flex: 1; min-width: 280px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 12px; padding: 25px;'>" +
          "<h3 style='margin: 0 0 15px 0; color: #856404;'><i class='fa fa-exclamation-triangle'></i> Perhatian</h3>" +
          "<ul style='margin: 0; padding-left: 20px; color: #856404; line-height: 1.8;'>" +
          "<li>Kartu ujian <b>WAJIB</b> dibawa saat mengikuti ujian</li>" +
          "<li>Mahasiswa tanpa kartu ujian <b>tidak diperkenankan</b> mengikuti ujian</li>" +
          "<li>Pastikan pembayaran sudah lunas sebelum mencetak kartu</li>" +
          "<li>Hubungi bagian keuangan jika ada pertanyaan mengenai pembayaran</li>" +
          "</ul>" +
          "</div>" +
          "</div>" +
          "</div>",
        borderless: true,
      },
    ],
  },
});

/* HALAMAN KHS */
var halamanKhs = new WebixView({
  config: {
    id: "viewMahasiswaKHS",
    type: "space",
    borderless: true,
    rows: [
      {
        view: "toolbar",
        borderless: true,
        cols: [
          {
            template: "KHS Mahasiswa",
            css: "headerBg",
            borderless: true,
            width: 100,
          },
          {
            view: "richselect",
            label: "",
            name: "khs_id_smt",
            id: "khs_id_smt",
            placeholder: "Pilih semester",
            options:
              "sopingi/semester/pilih/" + wSiaMhs.apiKey + "/" + Math.random(),
            borderless: true,
            width: 220,
          },
          {
            view: "button",
            label: "KHS PDF",
            id: "khsPDF",
            type: "iconButton",
            icon: "file-pdf-o",
            width: 100,
          },
          {},
          {
            view: "button",
            label: "Transkip PDF",
            id: "transkipPDF",
            type: "iconButton",
            icon: "file-pdf-o",
            width: 120,
          },
        ],
      },
      {
        view: "datatable",
        select: true,
        footer: true,
        id: "dataTableKhs",
        columns: [
          {
            id: "index",
            header: "No",
            width: 40,
            footer: {
              text: "Jumlah SKS:",
              colspan: 4,
              css: { "text-align": "right", "font-weight": "bold" },
            },
          },
          { id: "nm_kls", header: "Kelas", adjust: "data" },
          { id: "kode_mk", header: "Kode MK", adjust: "data", sort: "string" },
          {
            id: "nm_mk",
            header: "Nama Mata Kuliah",
            fillspace: true,
            sort: "string",
          },
          {
            id: "vsks_mk",
            header: "Jml SKS",
            css: { "text-align": "center" },
            width: 70,
            format: webix.i18n.numberFormat,
            footer: {
              id: "jSKS",
              content: "summColumn",
              css: { "text-align": "center", "font-weight": "bold" },
            },
          },
          {
            id: "nilai_angka",
            header: [
              { text: "Nilai", colspan: 3, css: { "text-align": "center" } },
              "Angka",
            ],
            css: { "text-align": "center" },
            format: webix.i18n.numberFormat,
            width: 60,
            footer: {
              text: "&sum; SKS*N.Indeks:",
              colspan: 3,
              css: { "text-align": "right", "font-weight": "bold" },
            },
            editor: "text",
          },
          {
            id: "nilai_huruf",
            header: ["", "Huruf"],
            css: { "text-align": "center" },
            width: 60,
          },
          {
            id: "nilai_indeks",
            header: ["", "Indeks"],
            css: { "text-align": "right" },
            format: webix.i18n.numberFormat,
            width: 60,
          },
          {
            id: "sksXindeks",
            header: "SKS*N.Indeks",
            css: { "text-align": "right" },
            format: webix.i18n.numberFormat,
            width: 90,
            footer: {
              content: "summColumn",
              css: { "text-align": "right", "font-weight": "bold" },
            },
          },
        ],
        pager: "pagerKhs",
        url: "sopingi/nilai/tampilKhs/" + wSiaMhs.apiKey + "/" + Math.random(),
        on: {
          onBeforeLoad: function () {
            this.showOverlay(
              "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
            );
            $$("khs_id_smt").disable();
            $$("khsPDF").disable();
            $$("transkipPDF").disable();
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

            $$("khs_id_smt").enable();
            $$("khsPDF").enable();
            $$("transkipPDF").enable();
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
          },
        },
      },
      {
        view: "pager",
        id: "pagerKhs",
        css: "pager",
        size: 12,
        group: 5,
        template:
          "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKhs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS, Indeks Prestasi Semester = <b><span id='IPS'>0</span></b>",
      },
    ],
  },
});

/* HALAMAN BIMBINGAN */
var halamanBimbingan = new WebixView({
  config: {
    type: "line",
    rows: [
      {
        view: "toolbar",
        paddingY: 2,
        cols: [
          {
            view: "label",
            template: "Jurnal Bimbingan Akademik",
            borderless: true,
          },
          {
            view: "button",
            label: "Tambah",
            id: "tambahJurnal",
            type: "iconButton",
            icon: "plus",
            width: 100,
          },
          //{ view:"button", label:"Ubah", id:"ubahJurnal", type:"iconButton", icon:"pencil", width:100, },
          {
            view: "button",
            label: "Hapus",
            id: "hapusJurnal",
            type: "iconButton",
            icon: "trash",
            width: 100,
          },
          {
            view: "button",
            label: "Refresh",
            id: "refreshJurnal",
            type: "iconButton",
            icon: "refresh",
            width: 100,
          },
        ],
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
          {
            id: "tanggal",
            header: ["Tanggal", { content: "serverFilter" }],
            width: 150,
            sort: "server",
            format: webix.Date.dateToStr("%d-%m-%Y %H:%i:%s"),
          },
          {
            id: "konten",
            header: ["Jurnal Bimbingan", { content: "serverFilter" }],
            fillspace: true,
            sort: "server",
          },
          {
            id: "oleh",
            header: ["dibuat Oleh", { content: "serverSelectFilter" }],
            width: 100,
            sort: "server",
          },
          {
            id: "detail",
            header: "Pesan",
            width: 100,
            template:
              "<button class='btnChat btnTransparant'><i class='webix_icon fa-send'></i> Pesan</button>",
          },
        ],
        pager: "jurnalPager",
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
              } catch (e) {}
            });

            var jData = this.data.order.length;
            $("#jJurnal").html(jData);
          },
        },
      },

      {
        view: "pager",
        id: "jurnalPager",
        template:
          "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jJurnal'>  </span></b> Data",
        size: 12,
        group: 5,
        animate: {
          direction: "left",
          type: "slide",
        },
      },
    ],
  }, //config
});

var formJurnal = {
  view: "form",
  id: "formJurnal",
  borderless: true,
  elements: [
    {
      view: "textarea",
      label: "Konten Jurnal",
      name: "konten",
      required: true,
      placeholder: "Isi konten bimbingan",
      invalidMessage: "Konten belum diisi",
      height: 100,
    },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanJurnal",
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
