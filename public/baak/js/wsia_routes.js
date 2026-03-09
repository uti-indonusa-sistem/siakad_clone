var wSia = webix.storage.session.get("wSia");

/* DASHBOARD */
var dashboard = new WebixView({
  config: {
    type: "clean",
    borderless: true,
    rows: [
      { template: "", height: 50 },
      {
        cols: [
          { template: "" },
          {
            template:
              "<h2 class='infoAdmin' align='center'>SELAMAT DATANG DI ADMINISTRATOR<br>siakad.poltekindonusa.ac.id</h2><br><br><center><img src='../gambar/logo_pt.png' height='200'></center>",
            height: 400,
            borderless: true,
            css: "dashboard",
          },
          { template: "" },
        ],
      },
      { template: "" },
    ],
  },
});

//BUKU INDUK
var masterBukuInduk = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Buku Induk Mahasiswa",
        body: {
          template:
            "Digunakan Untuk Menampilkan Data Mahasiswa Sesuai Tahun Angkatan Dan Program Studi",
        },
        width: 200,
      },
      {
        rows: [
          {
            template: "Buku Induk Mahasiswa",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshSms",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "downloadBukuInduk",
                label: "Download",
                type: "iconButton",
                icon: "download",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            id: "dataTableSms",
            select: true,
            columns: [
              { id: "index", header: "No", width: 40 },
              { id: "kode_prodi", header: "Kode", width: 90, sort: "int" },
              {
                id: "nm_lemb",
                header: [
                  {
                    text: "Nama Program Studi",
                    colspan: 2,
                    css: { "text-align": "center" },
                  },
                  "Indonesia",
                ],
                fillspace: true,
              },
              { id: "nm_lemb_en", header: ["", "Inggris"], fillspace: true },
              {
                id: "nm_jenj_didik",
                header: "Jenjang Pendidikan",
                fillspace: true,
                sort: "string",
              },
            ],
            url: "sopingi/sms/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });
              },
            },
          },
        ],
      },
    ],
  },
});

/* PROGRAM STUDI */
var masterProgramStudi = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Master Program Studi",
        body: { template: "Digunakan untuk mengelola program studi" },
        width: 200,
      },
      {
        rows: [
          {
            template: "Master Program Studi",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahSms",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshSms",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahSms",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusSms",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            id: "dataTableSms",
            select: true,
            columns: [
              { id: "index", header: "No", width: 40 },
              { id: "kode_prodi", header: "Kode", width: 90, sort: "int" },
              {
                id: "nm_lemb",
                header: [
                  {
                    text: "Nama Program Studi",
                    colspan: 2,
                    css: { "text-align": "center" },
                  },
                  "Indonesia",
                ],
                width: 250,
              },
              { id: "nm_lemb_en", header: ["", "Inggris"], width: 250 },
              {
                id: "nm_jenj_didik",
                header: "Jenjang Pendidikan",
                width: 100,
                sort: "string",
              },
              {
                id: "id_sms",
                header: "ID Feeder",
                width: 250,
                template: (o) => {
                  if (o.id_sms != "") {
                    return o.id_sms;
                  } else {
                    return "Belum diatur";
                  }
                },
              },
            ],
            url: "sopingi/sms/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });
              },
            },
          },
        ],
      },
    ],
  },
});

var formProgramStudi = {
  view: "form",
  id: "formSms",
  width: 500,
  elements: [
    {
      view: "text",
      name: "kode_prodi",
      id: "kode_prodi",
      label: "Kode Program Studi",
      labelWidth: 200,
      required: true,
    },
    {
      view: "text",
      name: "nm_lemb",
      id: "nm_lemb",
      label: "Nama Program Studi",
      labelWidth: 200,
      required: true,
    },
    {
      view: "text",
      name: "nm_lemb_en",
      id: "nm_lemb_en",
      label: "Nama Program Studi (Inggris)",
      labelWidth: 200,
      required: true,
    },
    {
      view: "combo",
      name: "id_jenj_didik",
      id: "id_jenj_didik",
      label: "Jenjang",
      labelWidth: 200,
      value: 22,
      required: true,
      options: [
        { id: 23, value: "D4" },
        { id: 22, value: "D3" },
        { id: 20, value: "D1" },
      ],
    },
    {
      view: "text",
      name: "id_sms",
      label: "ID Feeder",
      labelWidth: 200,
      labelBottom: "Lihat di NeoFeeder / Kosongkan jika belum ada",
    },
    {
      view: "text",
      name: "xid_sms",
      id: "xid_sms",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        {},
        { view: "button", id: "simpanSms", value: "Simpan", type: "form" },
        {},
      ],
    },
  ],
};

/* MASTER MATA KULIAH */
var masterMataKuliah = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Informasi Mata Kuliah",
        width: 200,
        body: {
          template:
            "Master mata kuliah digunakan untuk pendataan mata kuliah yang akan diajarkan, WAJIB diisi sebelum membuat kurikulum baru",
        },
      },
      {
        rows: [
          {
            template: "Master Mata Kuliah",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahMataKuliah",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshMataKuliah",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahMataKuliah",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusMataKuliah",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableMataKuliah",
            columns: [
              { id: "index", header: "No", width: 40 },
              { id: "kode_mk", header: "Kode MK", sort: "string" },
              {
                id: "nm_mk",
                header: ["Mata Kuliah", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nm_mk_en",
                header: ["Mata Kuliah (Inggris)", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              { id: "sks_mk", header: "SKS", sort: "int" },
              {
                id: "nm_lemb",
                header: ["Program Studi", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "vjns_mk",
                header: ["Jenis MK", { content: "selectFilter" }],
                sort: "string",
              },
              {
                id: "vkel_mk",
                header: ["Kelompok MK", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
            ],
            pager: "pagerMataKuliah",
            url:
              "sopingi/mata_kuliah/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jMataKuliah = this.data.order.length;
                $("#jMataKuliah").html(jMataKuliah);
              },
              onAfterFilter: function () {
                jMataKuliah = this.data.order.length;
                $("#jMataKuliah").html(jMataKuliah);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jMataKuliah = this.data.order.length;
                $("#jMataKuliah").html(jMataKuliah);
              },
            },
          },
          {
            view: "pager",
            id: "pagerMataKuliah",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jMataKuliah'>  </span></b> Mata Kuliah",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  },
});

var jenis_mk = [
  { id: "A", value: "Wajib" },
  { id: "B", value: "Pilihan" },
  { id: "C", value: "Wajib Peminatan" },
  { id: "D", value: "Pilihan Peminatan" },
  { id: "S", value: "Tugas akhir/Skripsi/Tesis/Disertasi" },
];

var kelompok_mk = [
  { id: "A", value: "MPK-Pengembangan Kepribadian" },
  { id: "B", value: "MKK-Keilmuan dan Ketrampilan" },
  { id: "C", value: "MKB-Keahlian Berkarya" },
  { id: "D", value: "MPB-Perilaku Berkarya" },
  { id: "E", value: "MBB-Berkehidupan Bermasyarakat" },
  { id: "F", value: "MKU/MKDU" },
  { id: "G", value: "MKDK" },
  { id: "H", value: "MKK" },
];

var formMataKuliah = {
  rows: [
    {
      view: "scrollview",
      id: "scrollSoal",
      height: 500,
      scroll: "y",
      body: {
        view: "form",
        id: "formMataKuliah",
        borderless: true,
        elements: [
          {
            view: "text",
            label: "Kode Mata Kuliah",
            name: "kode_mk",
            id: "kode_mk",
            placeholder: "Kode mata kuliah",
            required: true,
            invalidMessage: "Kode mata kuliah belum diisi",
            inputWidth: 250,
            attributes: { maxlength: 20 },
          },
          {
            view: "text",
            label: "Nama Mata Kuliah",
            name: "nm_mk",
            id: "nm_mk",
            placeholder: "Nama mata kuliah",
            required: true,
            invalidMessage: "Nama mata kuliah belum diisi",
            attributes: { maxlength: 200 },
          },
          {
            view: "text",
            label: "Nama Mata Kuliah (Inggris)",
            name: "nm_mk_en",
            id: "nm_mk_en",
            placeholder: "Nama mata kuliah bhs. inggris",
            required: true,
            invalidMessage: "Nama mata kuliah bhs. inggris belum diisi",
            attributes: { maxlength: 200 },
          },
          {
            view: "richselect",
            label: "Program Studi",
            name: "id_sms",
            id: "id_sms",
            placeholder: "Pilih Program Studi",
            required: true,
            invalidMessage: "Program studi belum dipilih",
            options: "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
            value: "",
          },
          {
            view: "radio",
            label: "Jenis Mata Kuliah",
            name: "jns_mk",
            id: "jns_mk",
            placeholder: "Jenis mata kuliah",
            required: true,
            options: jenis_mk,
            required: true,
            invalidMessage: "Jenis mata kuliah belum dipilih",
          },
          {
            view: "richselect",
            label: "Kelompok Mata Kuliah",
            name: "kel_mk",
            id: "kel_mk",
            placeholder: "Pilih kelompok mata kuliah",
            options: kelompok_mk,
            required: true,
            invalidMessage: "Kelompok mata kuliah belum dipilih",
          },

          {
            view: "counter",
            label: "SKS Tatap Muka",
            name: "sks_tm",
            id: "sks_tm",
            validate: webix.rules.isNumber,
            invalidMessage: "Type harus numerik",
          },
          {
            view: "counter",
            label: "SKS Praktikum",
            name: "sks_prak",
            id: "sks_prak",
            validate: webix.rules.isNumber,
            invalidMessage: "Type harus numerik",
          },
          {
            view: "counter",
            label: "SKS Praktikum Lapangan",
            name: "sks_prak_lap",
            id: "sks_prak_lap",
            validate: webix.rules.isNumber,
            invalidMessage: "Type harus numerik",
          },
          {
            view: "counter",
            label: "SKS Simulasi",
            name: "sks_sim",
            id: "sks_sim",
            validate: webix.rules.isNumber,
            invalidMessage: "Type harus numerik",
          },
          {
            view: "text",
            label: "SKS Mata Kuliah",
            name: "sks_mk",
            id: "sks_mk",
            validate: webix.rules.isNumber,
            required: true,
            readonly: true,
            inputWidth: 100,
            invalidMessage: "SKS mata kuliah belum terisi/ Type harus numerik",
          },

          {
            view: "toggle",
            type: "iconButton",
            name: "a_sap",
            id: "a_sap",
            offIcon: "close",
            onIcon: "check",
            offLabel: "Tidak Ada SAP",
            onLabel: "Ada SAP",
          },
          {
            view: "toggle",
            type: "iconButton",
            name: "a_silabus",
            id: "a_silabus",
            offIcon: "close",
            onIcon: "check",
            offLabel: "Tidak Ada Silabus",
            onLabel: "Ada Silabus",
          },
          {
            view: "toggle",
            type: "iconButton",
            name: "a_bahan_ajar",
            id: "a_bahan_ajar",
            offIcon: "close",
            onIcon: "check",
            offLabel: "Tidak Ada Bahan Ajar",
            onLabel: "Ada Bahan Ajar",
          },
          {
            view: "toggle",
            type: "iconButton",
            name: "acara_prak",
            id: "acara_prak",
            offIcon: "close",
            onIcon: "check",
            offLabel: "Tidak Ada Acara Praktik",
            onLabel: "Ada Acara Praktik",
          },
          {
            view: "toggle",
            type: "iconButton",
            name: "a_diktat",
            id: "a_diktat",
            offIcon: "close",
            onIcon: "check",
            offLabel: "Tidak Ada Diktat",
            onLabel: "Ada Diktat",
          },
          {
            view: "text",
            name: "xid_mk",
            id: "xid_mk",
            required: true,
            hidden: true,
          },
          {
            view: "text",
            name: "aksi",
            id: "aksi",
            required: true,
            hidden: true,
          },
          {
            cols: [
              { template: " ", borderless: true },
              {
                view: "button",
                id: "simpanMataKuliah",
                label: "Simpan",
                type: "form",
                width: 120,
                borderless: true,
              },
              { template: " ", borderless: true },
            ],
          },
        ],
        rules: {
          sks_mk: function (value) {
            return value > 0;
          },
        },
        elementsConfig: {
          labelPosition: "top",
        },
      },
    },
  ],
};

/* MASTER KURIKULUM */
var viewKurikulum = {
  id: "viewKurikulum",
  type: "space",
  cols: [
    {
      header: "Informasi Kurikulum",
      width: 200,
      body: {
        template:
          "Master Kurikulum digunakan untuk pendataan kurikulum beserta mata kuliahnya, WAJIB diisi sebelum membuat kelas perkuliahan",
      },
    },
    {
      rows: [
        {
          template: "Master Kurikulum",
          type: "header",
        },
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "tambahKurikulum",
              label: "Tambah",
              type: "iconButton",
              icon: "plus",
              width: 100,
            },
            {
              view: "button",
              id: "refreshKurikulum",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            { template: "", borderless: true },
            {
              view: "button",
              id: "ubahKurikulum",
              label: "Ubah",
              type: "iconButton",
              icon: "edit",
              width: 100,
            },
            {
              view: "button",
              id: "hapusKurikulum",
              label: "Hapus",
              type: "iconButton",
              icon: "remove",
              width: 100,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          id: "dataTableKurikulum",
          columns: [
            { id: "index", header: "No", width: 40 },
            {
              id: "nm_kurikulum_sp",
              header: ["Nama Kurikulum", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "vnm_lemb",
              header: ["Program Studi", { content: "selectFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "vid_smt_berlaku",
              header: ["Mulai Berlaku", { content: "selectFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "jml_sks_lulus",
              header: [
                {
                  text: "Aturan SKS",
                  colspan: 3,
                  css: { "text-align": "center" },
                },
                "Lulus",
              ],
              width: 60,
            },
            { id: "jml_sks_wajib", header: ["", "Wajib"], width: 60 },
            { id: "jml_sks_pilihan", header: ["", "Pilihan"], width: 60 },
            {
              id: "sks_mk_wajib",
              header: [
                {
                  text: "SKS Mata Kuliah",
                  colspan: 2,
                  css: { "text-align": "center" },
                },
                "Wajib",
              ],
              width: 60,
            },
            { id: "sks_mk_pilihan", header: ["", "Pilihan"], width: 60 },
            {
              id: "",
              header: "Mata Kuliah",
              template:
                "<button class='btnMK'><i class='webix_icon fa-folder-open'></i> Mata Kuliah</button>",
              width: 120,
            },
          ],
          pager: "pagerKurikulum",
          hover: "tableHover",
          url: "sopingi/kurikulum/tampil/" + wSia.apiKey + "/" + Math.random(),
          on: {
            onBeforeLoad: function () {
              this.showOverlay(
                "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
              );
            },
            onAfterLoad: function () {
              this.hideOverlay();
              jKurikulum = this.data.order.length;
              $("#jKurikulum").html(jKurikulum);
            },
            onAfterFilter: function () {
              jKurikulum = this.data.order.length;
              $("#jKurikulum").html(jKurikulum);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jKurikulum = this.data.order.length;
              $("#jKurikulum").html(jKurikulum);
            },
          },
        },
        {
          view: "pager",
          id: "pagerKurikulum",
          template:
            "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKurikulum'>  </span></b> Kurikulum",
          size: 15,
          group: 5,
          animate: {
            direction: "left",
            type: "slide",
          },
        },
      ],
    },
  ],
};

var formKurikulum = {
  view: "form",
  id: "formKurikulum",
  borderless: true,
  elements: [
    {
      view: "text",
      label: "Nama Kurikulum",
      name: "nm_kurikulum_sp",
      id: "nm_kurikulum_sp",
      placeholder: "Maksimal 60 digit",
      required: true,
      invalidMessage: "Nama kurikulum belum diisi",
      inputWidth: 300,
      attributes: { maxlength: 60 },
    },
    {
      view: "richselect",
      label: "Program Studi",
      name: "id_sms",
      id: "id_sms",
      placeholder: "Pilih Program Studi",
      required: true,
      invalidMessage: "Program studi belum dipilih",
      options: "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
      value: "",
    },
    {
      view: "richselect",
      label: "Mulai berlaku",
      name: "id_smt_berlaku",
      id: "id_smt_berlaku",
      placeholder: "Pilih Semester",
      required: true,
      options:
        "sopingi/semester/pilihSemua/" + wSia.apiKey + "/" + Math.random(),
      required: true,
      invalidMessage: "Semester belum dipilih",
    },
    {
      view: "counter",
      label: "Jumlah SKS Wajib",
      name: "jml_sks_wajib",
      id: "jml_sks_wajib",
      validate: webix.rules.isNumber,
      invalidMessage: "Type harus numerik",
    },
    {
      view: "counter",
      label: "Jumlah SKS Pilihan",
      name: "jml_sks_pilihan",
      id: "jml_sks_pilihan",
      validate: webix.rules.isNumber,
      invalidMessage: "Type harus numerik",
    },
    {
      view: "text",
      label: "Jumlah SKS",
      name: "jml_sks_lulus",
      id: "jml_sks_lulus",
      validate: webix.rules.isNumber,
      required: true,
      readonly: true,
      inputWidth: 100,
      invalidMessage: "Jumlah SKS belum terisi/ Type harus numerik",
    },

    {
      view: "text",
      name: "xid_kurikulum_sp",
      id: "xid_kurikulum_sp",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanKurikulum",
          label: "Simpan",
          type: "form",
          width: 120,
          borderless: true,
        },
        { template: " ", borderless: true },
      ],
    },
  ],
  rules: {
    jml_sks_lulus: function (value) {
      return value > 0;
    },
  },
  elementsConfig: {
    labelPosition: "top",
  },
};

var viewMataKuliahKurikulum = {
  id: "viewMataKuliahKurikulum",
  type: "space",
  cols: [
    {
      header: "Informasi Mata Kuliah Kurikulum",
      width: 200,
      body: {
        template:
          "Master Mata Kuliah Kurikulum digunakan untuk memasukkan mata kuliah ke dalam kurikulum, WAJIB diisi sebelum membuat kelas perkuliahan",
      },
    },
    {
      rows: [
        {
          type: "header",
          borderless: true,
          cols: [
            {
              view: "button",
              id: "kembaliKurikulum",
              type: "icon",
              icon: "chevron-left",
              label: "Kembali",
              width: 100,
              css: "headerBackBg",
            },
            {
              view: "template",
              id: "judulKurikulumMk",
              template: "#judulKurikulum#",
              borderless: true,
              css: "headerBg",
            },
            { view: "text", name: "id_sms_mk", id: "id_sms_mk", hidden: true },
            {
              view: "text",
              name: "id_kurikulum_sp_mk",
              id: "id_kurikulum_sp_mk",
              hidden: true,
            },
          ],
        },
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "tambahMataKuliahKurikulum",
              label: "Tambah",
              type: "iconButton",
              icon: "plus",
              width: 100,
            },
            {
              view: "button",
              id: "refreshMataKuliahKurikulum",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            { template: "", borderless: true },
            {
              view: "button",
              id: "ubahMataKuliahKurikulum",
              label: "Ubah",
              type: "iconButton",
              icon: "edit",
              width: 100,
            },
            {
              view: "button",
              id: "hapusMataKuliahKurikulum",
              label: "Hapus",
              type: "iconButton",
              icon: "remove",
              width: 100,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          id: "dataTableMataKuliahKurikulum",
          columns: [
            { id: "index", header: "No", width: 40 },
            { id: "kode_mk", header: "Kode MK", sort: "string" },
            {
              id: "nm_mk",
              header: ["Mata Kuliah", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "sks_mk",
              header: [
                { colspan: 5, text: "SKS", css: { "text-align": "center" } },
                "Mata Kuliah",
              ],
            },
            { id: "sks_tm", header: ["", "Tatap Muka"] },
            { id: "sks_prak", header: ["", "Praktikum"] },
            { id: "sks_prak_lap", header: ["", "Praktikum Lapangan"] },
            { id: "sks_sim", header: ["", "Simulasi"] },
            {
              id: "smt",
              header: ["Semester", { content: "selectFilter" }],
              sort: "int",
            },
            { id: "va_wajib", header: "Wajib?", sort: "string" },
          ],
          pager: "pagerMataKuliahKurikulum",
          hover: "tableHover",
          url:
            "sopingi/mata_kuliah_kurikulum/tampil/" +
            wSia.apiKey +
            "/" +
            Math.random(),
          on: {
            onBeforeLoad: function () {
              this.showOverlay(
                "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
              );
            },
            onAfterLoad: function () {
              this.hideOverlay();
              jMataKuliahKurikulum = this.data.order.length;
              $("#jMataKuliahKurikulum").html(jMataKuliahKurikulum);
            },
            onAfterFilter: function () {
              jMataKuliahKurikulum = this.data.order.length;
              $("#jMataKuliahKurikulum").html(jMataKuliahKurikulum);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jMataKuliahKurikulum = this.data.order.length;
              $("#jMataKuliahKurikulum").html(jMataKuliahKurikulum);
            },
          },
        },
        {
          view: "pager",
          id: "pagerMataKuliahKurikulum",
          template:
            "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jMataKuliahKurikulum'>  </span></b> Mata Kuliah",
          size: 15,
          group: 5,
          animate: {
            direction: "left",
            type: "slide",
          },
        },
      ],
    },
  ],
};

var formMataKuliahKurikulum = {
  view: "form",
  id: "formMataKuliahKurikulum",
  borderless: true,
  elements: [
    {
      view: "combo",
      label: "Mata Kuliah",
      name: "id_mk",
      id: "id_mk",
      placeholder: "Pilih Mata Kuliah",
      required: true,
      invalidMessage: "Mata kuliah belum dipilih",
      options:
        "sopingi/mata_kuliah/pilihProdi/" + wSia.apiKey + "/" + Math.random(),
      value: "",
    },
    {
      view: "counter",
      label: "Semester",
      name: "smt",
      id: "smt",
      required: true,
      validate: webix.rules.isNumber,
      invalidMessage: "Semester belu diisi/ Type harus numerik",
    },
    {
      view: "toggle",
      name: "a_wajib",
      id: "a_wajib",
      type: "iconButton",
      offIcon: "close",
      onIcon: "check",
      offLabel: "Tidak Wajib",
      onLabel: "Wajib",
      inputWidth: 100,
    },
    {
      view: "text",
      name: "id_mk_kurikulum",
      id: "id_mk_kurikulum",
      required: true,
      hidden: true,
    },
    {
      view: "text",
      name: "xid_kurikulum_sp",
      id: "xid_kurikulum_sp",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanMataKuliahKurikulum",
          label: "Simpan",
          type: "form",
          width: 120,
          borderless: true,
        },
        { template: " ", borderless: true },
      ],
    },
  ],
  rules: {
    smt: function (value) {
      return value > 0;
    },
  },
  elementsConfig: {
    labelPosition: "top",
  },
};

var masterKurikulum = new WebixView({
  config: {
    id: "masterKurikulum",
    cells: [viewKurikulum, viewMataKuliahKurikulum],
  },
});

/* MASTER SIAKAD KELAS */
var masterSiakadKelas = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Informasi Master Kelas",
        width: 200,
        body: {
          template:
            "Master kelas digunakan untuk membuat nama kelas yang akan digunakan di dalam pembuatan Kelas Perkuliahan.",
        },
      },
      {
        rows: [
          {
            template: "Master Kelas",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahSiakadKelas",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshSiakadKelas",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahSiakadKelas",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusSiakadKelas",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableSiakadKelas",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "id_nm_kls",
                header: ["Nama Kelas", { content: "textFilter" }],
                sort: "string",
              },
              {
                id: "angkatan",
                header: ["Angkatan", { content: "selectFilter" }],
                sort: "string",
              },
              {
                id: "abc",
                header: ["Kelas", { content: "selectFilter" }],
                sort: "string",
              },
              //{ id:"urutan", header:"Urutan", sort:"int"}
            ],
            pager: "pagerSiakadKelas",
            url:
              "sopingi/siakad_kelas/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jSiakadKelas = this.data.order.length;
                $("#jSiakadKelas").html(jSiakadKelas);
              },
              onAfterFilter: function () {
                jSiakadKelas = this.data.order.length;
                $("#jSiakadKelas").html(jSiakadKelas);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jSiakadKelas = this.data.order.length;
                $("#jSiakadKelas").html(jSiakadKelas);
              },
            },
          },
          {
            view: "pager",
            id: "pagerSiakadKelas",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jSiakadKelas'>  </span></b> Kelas",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  },
});

var formSiakadKelas = {
  view: "form",
  id: "formSiakadKelas",
  borderless: true,
  elements: [
    {
      view: "richselect",
      label: "Angkatan Mahasiswa",
      name: "angkatan",
      id: "angkatan",
      placeholder: "Pilih Angkatan",
      required: true,
      invalidMessage: "Angkatan belum dipilih",
      options:
        "sopingi/siakad_angkatan/pilih/" + wSia.apiKey + "/" + Math.random(),
      value: "",
      inputWidth: 200,
    },
    {
      view: "text",
      label: "Inisial Kelas (maximal 2 digit)",
      name: "abc",
      id: "abc",
      required: true,
      required: true,
      invalidMessage: "Kelas belum dipilih",
      attributes: { maxlength: 2 },
      inputWidth: 50,
    },
    //{ view:"counter", label:"Urutan", name:"urutan", id:"urutan",required:true, validate:webix.rules.isNumber,invalidMessage: "Urutan belum diisi/ Type harus numerik",max:9},
    {
      view: "text",
      name: "id_nm_kls",
      id: "id_nm_kls",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanSiakadKelas",
          label: "Simpan",
          type: "form",
          width: 120,
          borderless: true,
        },
        { template: " ", borderless: true },
      ],
    },
  ],
  rules: {
    //urutan:function(value){ return value > 0; },
  },
  elementsConfig: {
    labelPosition: "top",
  },
};

/* IMPORT MAHASISWA */
var importMahasiswa = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        rows: [
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              { view: "label", label: "Daftar Ulang", width: 100 },
              {
                view: "text",
                width: 150,
                id: "angkatanDU",
                placeholder: "Ketik Angkatan [Enter]",
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "refreshMahasiswaDU",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              {
                view: "button",
                id: "importMahasiswaDU",
                label: "Import",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableMahasiswaDU",
            fixedRowHeight: false,
            rowLineHeight: 24,
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "no_pend",
                header: ["No Daftar", { content: "serverFilter" }],
                width: 100,
              },
              {
                id: "nama",
                header: ["Nama Mahasiswa", { content: "serverFilter" }],
                fillspace: true,
              },
              {
                id: "xid_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options: "sopingi/sms/pilih/" + wSia.apiKey + "/0",
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nama_prodi;
                },
              },
              {
                id: "jenis_daftar",
                header: "Jenis Daftar",
                fillspace: true,
                template: (o) => {
                  if (o.jenis_daftar == 1) {
                    return "Peserta Didik Baru";
                  } else if (o.jenis_daftar == 2) {
                    return "Pindahan (Belum Lulus)";
                  } else if (o.jenis_daftar == 13) {
                    return "RPL Perolehan SKS (Pengalaman Kerja)";
                  } else if (o.jenis_daftar == 16) {
                    return "RPL Transfer SKS (Sudah Lulus)";
                  } else {
                    return "-";
                  }
                },
              },
              {
                id: "kelas",
                header: ["Kelas", { content: "serverFilter" }],
                width: 60,
              },
            ],
            on: {
              onBeforeLoad: function () {
                this.showOverlay("Memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                this.adjustRowHeight("nama");
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  if (obj) {
                    obj.index = i + 1;
                  }
                });
              },
            },
          },
        ],
      },
      {
        rows: [
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "label",
                label: "Mahasiswa Baru Belum Dapat NIM dan Kelas",
              },

              {
                view: "button",
                id: "refreshMahasiswaBaru",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              {
                view: "button",
                id: "pendidikanMahasiswaBaru",
                label: "Pendidikan",
                type: "iconButton",
                icon: "tag",
                width: 120,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableMahasiswaBaru",
            fixedRowHeight: false,
            rowLineHeight: 24,
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "no_pend",
                header: ["No Daftar", { content: "serverFilter" }],
                sort: "string",
                width: 100,
              },
              {
                id: "nm_pd",
                header: ["Nama Mahasiswa", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "id_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options: "sopingi/sms/pilih/" + wSia.apiKey + "/0",
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_prodi;
                },
              },
              {
                id: "jenis_daftar",
                header: ["Jenis Daftar", { content: "serverSelectFilter" }],
                options:
                  "sopingi/jenis_pendaftaran/pilih/" + wSia.apiKey + "/0",
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jns_daftar;
                },
              },
              {
                id: "kelas_spmb",
                header: ["Kls SPMB", { content: "serverFilter" }],
                width: 80,
                sort: "string",
              },
            ],
            on: {
              onBeforeLoad: function () {
                this.showOverlay("Memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                this.adjustRowHeight("nm_pd");
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  if (obj) {
                    obj.index = i + 1;
                  }
                });
              },
            },
          },
        ],
      },
    ],
  },
});

var mahasiswaBaruMigrasi = {
  rows: [
    {
      view: "toolbar",
      paddingY: 2,
      cols: [
        {
          view: "button",
          id: "excelMahasiswaBaru",
          label: "Excel",
          type: "iconButton",
          icon: "download",
          width: 100,
        },
        {
          view: "label",
          label:
            "&#8592; silahkan unduh file mahasiswa baru kemudian ubah NIM, Jenis Daftar, Kelas, PA, Asal PT, Asal Prodi, SKS Diakui",
        },
      ],
    },
    {
      height: 40,
      template:
        "File excel yang sudah terisi LENGKAP dan VALID bisa di import menggunakan form berikut",
      borderless: true,
    },
    {
      view: "uploader",
      value: "Pilih File Excel",
      autosend: false,
      id: "uploaderExcelMahasiswaMigrasi",
      multiple: false,
      name: "files",
      link: "listExcelMahasiswaMigrasi",
      upload:
        "sopingi-excel/mahasiswa/uploadbelumnim/" +
        wSia.apiKey +
        "/" +
        Math.random(),
    },
    {
      view: "list",
      id: "listExcelMahasiswaMigrasi",
      type: "uploader",
      autoheight: true,
      borderless: true,
    },
    {
      cols: [
        {},
        {
          view: "button",
          label: "Import Excel",
          id: "importExcelMahasiswaMigrasi",
        },
        {},
      ],
    },
    { height: 40, template: " ", borderless: true },
  ],
};

/* BULK UPDATE PA */
var formBulkPA = {
  view: "form",
  id: "formBulkPA",
  elements: [
    {
      view: "label",
      label:
        "Pilih Pembimbing Akademik yang baru untuk mahasiswa yang dipilih:",
    },
    {
      view: "combo",
      label: "Pembimbing Akademik",
      name: "pa",
      id: "paBulk",
      placeholder: "Pilih Dosen",
      required: true,
      options: "sopingi/dosen/pilih/" + wSia.apiKey + "/" + Math.random(),
    },
    {
      cols: [
        {
          view: "button",
          id: "prosesBulkPA",
          label: "Update Sekarang",
          type: "form",
          css: "webix_primary",
        },
        { view: "button", label: "Batal", click: () => $$("winBulkPA").hide() },
      ],
    },
  ],
  elementsConfig: {
    labelPosition: "top",
  },
};

/* MASTER MAHASISWA */
var viewMahasiswa = {
  id: "viewMahasiswa",
  type: "space",
  cols: [
    {
      header: "Informasi Mahasiswa",
      width: 200,
      body: {
        rows: [
          {
            template:
              "Master mahasiswa digunakan untuk pengolahan data mahasiswa, Bagi mahasiswa baru WAJIB diinputkan sebelum pengisian KRS",
            height: 100,
          },
          {
            view: "button",
            id: "importMahasiswa",
            label: "IMPORT DARI SPMB",
            type: "iconButton",
            icon: "download",
            hidden: true,
          },
          {},
          //{ template: "*Import dari SPMB hanya yang sudah daftar ulang"}
        ],
      },
    },
    {
      rows: [
        {
          template: "Master Mahasiswa",
          type: "header",
        },
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "tambahMahasiswa",
              label: "Tambah",
              type: "iconButton",
              icon: "plus",
              width: 100,
            },
            {
              view: "button",
              id: "refreshMahasiswa",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            {
              view: "button",
              id: "bulkMahasiswaPA",
              label: "Bulk PA",
              type: "iconButton",
              icon: "users",
              width: 100,
              css: "webix_secondary",
            },
            { template: "", borderless: true },
            {
              view: "button",
              id: "hapusMahasiswa",
              label: "Hapus",
              type: "iconButton",
              icon: "remove",
              width: 100,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          id: "dataTableMahasiswa",
          columns: [
            //{ id:"index", header:"No", width:40},
            {
              id: "ch_mhs",
              header: { content: "masterCheckbox" },
              template: "{common.checkbox()}",
              width: 40,
            },
            {
              id: "nm_pd",
              header: ["Nama Mahasiswa", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "nipd",
              header: ["NIM", { content: "textFilter" }],
              sort: "string",
              width: 100,
            },
            {
              id: "jk",
              header: ["L/P", { content: "selectFilter" }],
              sort: "string",
              width: 50,
            },
            {
              id: "tgl_lahir",
              header: ["Tgl Lahir", { content: "textFilter" }],
              format: webix.Date.dateToStr("%d-%m-%Y"),
              sort: "string",
              width: 100,
            },
            {
              id: "vnm_lemb",
              header: ["Program Studi", { content: "selectFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "vid_jns_daftar",
              header: ["Jenis Daftar", {
                content: "selectFilter",
                options: [
                  { id: "Mahasiswa Baru", value: "Mahasiswa Baru" },
                  { id: "Pindahan/Transfer", value: "Pindahan/Transfer" },
                  { id: "RPL Perolehan SKS", value: "RPL Perolehan SKS" },
                  { id: "RPL Transfer SKS / Karyawan", value: "RPL Transfer SKS / Karyawan" },
                  { id: "Mahasiswa K2", value: "Mahasiswa K2" },
                ]
              }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "kelas",
              header: ["Kelas", { content: "textFilter" }],
              width: 60,
              sort: "string",
            },
            {
              id: "",
              header: "Detail",
              template:
                "<button class='btnMhsDetail'><i class='webix_icon fa-folder-open'></i> Detail</button>",
              width: 100,
            },
          ],
          //pager:"pagerMahasiswa",
          hover: "tableHover",
          datafetch: 20,
          url:
            "idata->sopingi/mahasiswa/tampil/" +
            wSia.apiKey +
            "/" +
            Math.random(),
          on: {
            /*onBeforeLoad:function(){
            this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
          },
          */
            onBeforeLoad: function () {
              $("#jMahasiswa").html("Sedang memuat...");
            },
            onAfterLoad: function () {
              this.hideOverlay();
              jMahasiswa = this.data.order.length;
              $("#jMahasiswa").html(jMahasiswa);
            },
            onAfterFilter: function () {
              jMahasiswa = this.data.order.length;
              $("#jMahasiswa").html(jMahasiswa);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jMahasiswa = this.data.order.length;
              $("#jMahasiswa").html(jMahasiswa);
            },
          },
        },
        {
          template:
            "Total Ter-load: <b><span id='jMahasiswa'>  </span></b> Mahasiswa (Scroll kebawah untuk memuat data berikutnya)",
          height: 30,
          borderless: true,
        },
        /*
        ,{
        view:"pager",
        id:"pagerMahasiswa",
        template:"{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jMahasiswaX'>  </span></b> Mahasiswa",
        size:30,
        group:5,
        animate : {
          direction:"left", type:"slide"
        } 
        }*/
      ],
    },
  ],
};

var menu_mahasiswa = [
  { id: "biodata_mahasiswa", icon: "users", value: "Biodata Mahasiswa" },
  //{id: "pendidikan_mahasiswa", icon: "users", value: "Histori Pendidikan"},
  { id: "krs_mahasiswa", icon: "book", value: "KRS Mahasiswa" },
  { id: "khs_mahasiswa", icon: "book", value: "KHS Mahasiswa" },
  { id: "transkip_mahasiswa", icon: "book", value: "Transkip Mahasiswa" },
  { id: "pass_mahasiswa", icon: "book", value: "Akun Mahasiswa" },
  //{id: "aktifitas_mahasiswa", icon: "table", value:"Aktifitas Perkuliahan"},
];

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
        options: "sopingi/wilayah/tampil/" + wSia.apiKey + "/" + Math.random(),
        placeholder: "Ketik kecamatan sampai muncul Kab dan Provinsi",
        invalidMessage: "Wilayah belum dipilih",
        inputWidth: 700,
      },
      {
        view: "richselect",
        label: "Jenis Tinggal",
        name: "id_jns_tinggal",
        id: "id_jns_tinggal",
        placeholder: "Pilih Jenis Tinggal",
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
        inputWidth: 400,
      },
      {
        cols: [
          {
            view: "radio",
            label: "Penerima KPS",
            name: "a_terima_kps",
            id: "a_terima_kps",
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
        placeholder: "Nama ibu kandung",
        inputWidth: 500,
      },
      {
        view: "datepicker",
        label: "Tanggal Lahir",
        name: "tgl_lahir_ibu",
        id: "tgl_lahir_ibu",
        format: "%d-%m-%Y",
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

var viewMahasiswaDetail = {
  id: "viewMahasiswaDetail",
  type: "space",
  rows: [
    {
      view: "toolbar",
      paddingY: 2,
      cols: [
        {
          view: "label",
          template:
            "Biodata Mahasiswa |    Yang bertanda <b>*</b> : WAJIB Diisi",
          borderless: true,
        },
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
                  inputWidth: 400,
                  labelWidth: 150,
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
                  placeholder: "Tempat lahir",
                  invalidMessage: "Tempat lahir belum diisi",
                  inputWidth: 400,
                  labelWidth: 150,
                },
                {
                  view: "datepicker",
                  label: "Tanggal Lahir",
                  name: "tgl_lahir",
                  id: "tgl_lahir",
                  format: "%d-%m-%Y",
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
                  placeholder: "Nama Ibu kandung sesuai KTP",
                  invalidMessage: "Nama ibu kandung belum diisi",
                  inputWidth: 400,
                  labelWidth: 150,
                },
                {
                  view: "text",
                  label: "NISN",
                  name: "nisn",
                  placeholder: "NIS Nasional",
                  invalidMessage: "NISN belum diisi",
                  inputWidth: 300,
                  labelWidth: 150,
                },
              ],
            },
            { template: " ", borderless: true, width: 50 },
            {
              rows: [
                {
                  view: "text",
                  label: "No. Induk Mahasiswa",
                  name: "nipd",
                  id: "nipd",
                  required: true,
                  placeholder: "NIM",
                  invalidMessage: "No.Daftar belum diisi",
                  inputWidth: 350,
                  labelWidth: 150,
                },
                {
                  view: "richselect",
                  label: "Program Studi",
                  name: "id_sms",
                  id: "id_sms",
                  placeholder: "Pilih Program Studi",
                  required: true,
                  invalidMessage: "Program studi belum dipilih",
                  options:
                    "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                  value: "",
                  inputWidth: 450,
                  labelWidth: 150,
                },
                {
                  view: "richselect",
                  label: "Mulai Masuk",
                  name: "mulai_smt",
                  id: "mulai_smt",
                  placeholder: "Pilih Semester",
                  required: true,
                  options:
                    "sopingi/semester/pilihSemua/" +
                    wSia.apiKey +
                    "/" +
                    Math.random(),
                  required: true,
                  invalidMessage: "Semester belum dipilih",
                  inputWidth: 350,
                  labelWidth: 150,
                },
                {
                  view: "richselect",
                  label: "Jenis Daftar",
                  name: "id_jns_daftar",
                  id: "id_jns_daftar",
                  placeholder: "Pilih Jenis Daftar",
                  required: true,
                  invalidMessage: "Jenis Daftar belum dipilih",
                  options: [
                    { id: 1, value: "Mahasiswa Baru" },
                    { id: 2, value: "Pindahan" },
                    { id: 13, value: "RPL Perolehan SKS" },
                    { id: 16, value: "RPL Transfer SKS / Karyawan" },
                    { id: 17, value: "Mahasiswa K2" },
                  ],
                  inputWidth: 350,
                  labelWidth: 150,
                },
                {
                  view: "datepicker",
                  label: "Tanggal Masuk",
                  name: "tgl_masuk_sp",
                  format: "%d-%m-%Y",
                  editable: true,
                  placeholder: "dd-mm-yyyy",
                  invalidMessage: "Tanggal mendaftar belum diisi",
                  inputWidth: 300,
                  labelWidth: 150,
                  stringResult: true,
                },
                {
                  view: "richselect",
                  label: "Pembiayaan",
                  name: "id_pembiayaan",
                  placeholder: "Pilih Pembiayaan",
                  required: false,
                  invalidMessage: "Pembiayaan belum dipilih",
                  options: [
                    { id: 1, value: "Mandiri" },
                    { id: 2, value: "Beasiswa Tidak Penuh" },
                    { id: 3, value: "Beasiswa Penuh" },
                  ],
                  inputWidth: 300,
                  labelWidth: 150,
                },
                {
                  view: "text",
                  label: "Biaya Masuk",
                  name: "biaya_masuk",
                  placeholder: "Biaya masuk kuliah",
                  required: false,
                  invalidMessage: "Biaya masuk belum diisi",
                  inputWidth: 300,
                  labelWidth: 150,
                },
                {
                  view: "richselect",
                  label: "Kelas",
                  name: "kelas",
                  id: "kelas",
                  placeholder: "Pilih nama kelas",
                  required: true,
                  invalidMessage: "Nama kelas belum dipilih",
                  options:
                    "sopingi/siakad_kelas/pilih/" +
                    wSia.apiKey +
                    "/" +
                    Math.random(),
                  inputWidth: 450,
                  labelWidth: 150,
                },
                {
                  view: "combo",
                  label: "Pembimbing Akademik",
                  name: "pa",
                  id: "pa",
                  placeholder: "Pilih Dosen",
                  required: true,
                  invalidMessage: "Pembimbing Akademik belum dipilih",
                  options:
                    "sopingi/dosen/pilih/" + wSia.apiKey + "/" + Math.random(),
                  inputWidth: 450,
                  labelWidth: 150,
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
};

//Mahasiswa History Pendidikan
var viewMahasiswaPendidikan = {
  id: "viewMahasiswaPendidikan",
  type: "space",
  borderless: true,
  rows: [
    {
      view: "toolbar",
      borderless: true,
      cols: [
        { view: "label", template: "History Pendidikan", borderless: true },
        {
          view: "button",
          id: "tambahPendidikan",
          label: "Tambah",
          type: "iconButton",
          icon: "plus",
          width: 100,
        },
        {
          view: "button",
          id: "ubahPendidikan",
          label: "Ubah",
          type: "iconButton",
          icon: "edit",
          width: 100,
        },
        {
          view: "button",
          id: "hapusPendidikan",
          label: "Hapus",
          type: "iconButton",
          icon: "remove",
          width: 100,
        },
        {
          view: "button",
          label: "Refresh",
          id: "refreshPendidikan",
          type: "iconButton",
          icon: "refresh",
          width: 100,
        },
      ],
    },
    {
      view: "datatable",
      select: true,
      footer: true,
      id: "dataTablePendidikan",
      columns: [
        { id: "index", header: "No", width: 40 },
        { id: "nipd", header: "NIM", adjust: "data" },
        {
          id: "vid_jns_daftar",
          header: "Jenis Daftar",
          adjust: "data",
          sort: "string",
        },
        { id: "nm_smt", header: "Semester", adjust: "data", sort: "string" },
        {
          id: "tgl_masuk_sp",
          header: "Tgl.Masuk",
          adjust: "data",
          sort: "string",
          format: webix.Date.dateToStr("%d-%m-%Y"),
        },
        {
          id: "sp_nm_lemb",
          header: "Perguruan Tinggi",
          fillspace: true,
          sort: "string",
        },
        {
          id: "sms_nm_lemb",
          header: "Program Studi",
          fillspace: true,
          sort: "string",
        },
      ],
      pager: "pagerPendidikan",
      //url:"sopingi/pendidikan/tampil/"+wSia.apiKey+"/"+Math.random(),
      on: {
        onBeforeLoad: function () {
          this.showOverlay(
            "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
          );
          $$("tambahPendidikan").disable();
          $$("ubahPendidikan").disable();
          $$("hapusPendidikan").disable();
          $$("refreshPendidikan").disable();
        },
        onAfterLoad: function () {
          this.hideOverlay();
          jPendidikan = this.data.order.length;
          $("#jPendidikan").html(jPendidikan);

          $$("tambahPendidikan").enable();
          $$("ubahPendidikan").enable();
          $$("hapusPendidikan").enable();
          $$("refreshPendidikan").enable();
        },
        onAfterFilter: function () {
          jPendidikan = this.data.order.length;
          $("#jPendidikan").html(jPendidikan);
        },
        "data->onStoreUpdated": function () {
          this.data.each(function (obj, i) {
            obj.index = i + 1;
          });

          jPendidikan = this.data.order.length;
          $("#jPendidikan").html(jPendidikan);
        },
      }, //akhir on
    },
    {
      view: "pager",
      id: "pagerPendidikan",
      css: "pager",
      template:
        "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jPendidikan'>0</span></b> Pendidikan",
      size: 15,
      group: 5,
      animate: {
        direction: "left",
        type: "slide",
      },
    },
  ],
};

var formPendidikan = {};

//Mahasiswa KRS
var viewMahasiswaKRS = {
  id: "viewMahasiswaKRS",
  type: "space",
  borderless: true,
  rows: [
    {
      view: "toolbar",
      borderless: true,
      cols: [
        { view: "label", template: "KRS Mahasiswa", borderless: true },
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
        { id: "nm_kls", header: "Kelas", width: 50 },
        { id: "kode_mk", header: "Kode MK", width: 70, sort: "string" },
        {
          id: "nm_mk",
          header: "Nama Mata Kuliah",
          fillspace: true,
          sort: "string",
        },
        {
          id: "vsks_mk",
          header: "SKS",
          width: 40,
          sort: "string",
          footer: {
            id: "jSKS",
            content: "summColumn",
            css: { "text-align": "right", "font-weight": "bold" },
          },
        },
        { id: "vid_smt", header: "Semester", fillspace: true, sort: "string" },
      ],
      pager: "pagerKrs",
      //url:"sopingi/nilai/tampil/"+wSia.apiKey+"/"+Math.random(),
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
      size: 15,
      group: 5,
      animate: {
        direction: "left",
        type: "slide",
      },
    },
  ],
};

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
      borderless: true,
      body: {
        view: "form",
        id: "formKRS",
        borderless: true,
        elements: [
          {
            view: "datatable",
            label: "Mata Kuliah",
            id: "dataTableKelasPerkuliahan",
            autoheight: true,
            checkboxRefresh: true,
            columns: [
              { id: "index", header: "No", width: 30 },
              {
                id: "nm_kls",
                header: ["Kelas", { content: "selectFilter" }],
                width: 80,
              },
              { id: "kode_mk", header: "Kode MK", sort: "string", width: 60 },
              {
                id: "nm_mk",
                header: ["Nama Mata Kuliah", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              { id: "vsks_mk", header: "SKS", sort: "string", width: 40 },
              {
                id: "vid_smt",
                header: ["Semester", { content: "selectFilter" }],
                width: 150,
              },
              {
                id: "ambilKelas",
                header: "Ambil (Klik Centang)",
                template: checkbox_krs,
                width: 150,
              },
            ],
            //url:"sopingi/kelas_kuliah/tampil/"+wSia.apiKey+"/"+Math.random(),
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

//MAHASISWA KHS
var abobot; //bobot Nilai
var record;
var nilaiBaru;
var viewMahasiswaKHS = {
  id: "viewMahasiswaKHS",
  type: "space",
  borderless: true,
  rows: [
    {
      view: "toolbar",
      borderless: true,
      cols: [
        {
          view: "label",
          template:
            "KHS Mahasiswa <i>(Gunakan <b>Tab</b> untuk merubah nilai angka)</i>",
          borderless: true,
        },
        { width: 5 },
        {
          view: "button",
          label: "Simpan Nilai",
          id: "simpanKhs",
          type: "iconButton",
          icon: "save",
          width: 120,
        },
        {
          view: "button",
          label: "Refresh",
          id: "refreshKhs",
          type: "iconButton",
          icon: "refresh",
          width: 100,
        },
        {
          view: "button",
          label: "Download Khs DOC",
          id: "unduhKhs",
          type: "iconButton",
          icon: "file-word-o",
          width: 150,
        },
      ],
    },
    {
      view: "datatable",
      select: true,
      footer: true,
      editable: true,
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
      //url:"sopingi/nilai/tampilKHS/"+wSia.apiKey+"/"+Math.random(),
      on: {
        onBeforeLoad: function () {
          this.showOverlay(
            "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
          );

          $$("simpanKhs").disable();
          $$("refreshKhs").disable();
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

          ips = jSKSxIndex / jSKS;
          $("#IPS").html(ips.toFixed(2));
          $("#jSKS").html(jSKS);

          $$("simpanKhs").enable();
          $$("refreshKhs").enable();
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
        onAfterEditStop: function (data, editor, ignoreUpdate) {
          //console.log(data);
          //console.log(editor);
          record = this.getItem(editor.row);
          nilaiBaru = parseFloat(data.value);

          abobot.forEach(function (item, index) {
            //console.log(item.bobot_nilai_min);
            //console.log("a."+nilaiBaru);
            if (nilaiBaru >= parseFloat(item.bobot_nilai_min)) {
              record["nilai_huruf"] = item.nilai_huruf;
              record["nilai_indeks"] = item.nilai_indeks;
              record["sksXindeks"] = record["vsks_mk"] * record["nilai_indeks"];
            }
          });

          if (data.value == "T" || data.value == "t") {
            record["nilai_angka"] = 0;
            record["nilai_huruf"] = "T";
            record["nilai_indeks"] = 0;
          }

          if (data.value == "") {
            record["nilai_angka"] = 0;
            record["nilai_huruf"] = "";
            record["nilai_indeks"] = 0;
          }

          this.updateItem(editor.row, record);

          //update IPS
          jSKS = 0;
          jSKSxIndex = 0;
          this.eachRow(function (id) {
            var item = this.getItem(id);
            jSKS += parseInt(item.vsks_mk);
            jSKSxIndex += parseInt(item.sksXindeks);
          });

          ips = jSKSxIndex / jSKS;
          $("#IPS").html(ips.toFixed(2));
        },
      },
    },
    {
      view: "pager",
      id: "pagerKhs",
      css: "pager",
      template:
        "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKhs'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS, Indeks Prestasi Semester = <b><span id='IPS'>0</span></b> &nbsp; &nbsp (<i>Isi nilai angka dengan huruf <b>T</b> untuk nilai tunda<i>)",
      size: 15,
      group: 5,
      animate: {
        direction: "left",
        type: "slide",
      },
    },
  ],
}; //khs

//MAHASISWA TRANSKIP
var viewMahasiswaTranskip = {
  id: "viewMahasiswaTranskip",
  type: "space",
  borderless: true,
  rows: [
    {
      view: "toolbar",
      borderless: true,
      cols: [
        { view: "label", template: "Transkip Mahasiswa", borderless: true },
        { width: 5 },
        {
          view: "button",
          label: "Refresh",
          id: "refreshTranskip",
          type: "iconButton",
          icon: "refresh",
          width: 100,
        },
        {
          view: "button",
          label: "Download Transkip PDF",
          id: "unduhTranskip",
          type: "iconButton",
          icon: "file-pdf-o",
          width: 180,
        },
      ],
    },
    {
      view: "datatable",
      select: true,
      footer: true,
      id: "dataTableTranskip",
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
      pager: "pagerTranskip",
      //url:"sopingi/nilai/tampilTranskip/"+wSia.apiKey+"/"+Math.random(),
      on: {
        onBeforeLoad: function () {
          this.showOverlay(
            "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
          );

          $$("refreshTranskip").disable();
        },
        onAfterLoad: function () {
          this.hideOverlay();
          jTranskip = this.data.order.length;
          $("#jTranskip").html(jTranskip);

          jSKS = 0;
          jSKSxIndex = 0;
          this.eachRow(function (id) {
            var item = this.getItem(id);
            jSKS += parseInt(item.vsks_mk);
            jSKSxIndex += parseInt(item.sksXindeks);
          });

          ipk = jSKSxIndex / jSKS;
          $("#IPK").html(ipk.toFixed(2));
          $("#jSKS").html(jSKS);

          $$("refreshTranskip").enable();
        },
        onAfterFilter: function () {
          jTranskip = this.data.order.length;
          $("#jTranskip").html(jTranskip);
        },
        "data->onStoreUpdated": function () {
          this.data.each(function (obj, i) {
            obj.index = i + 1;
          });

          jTranskip = this.data.order.length;
          $("#jTranskip").html(jTranskip);
        },
      },
    },
    {
      view: "pager",
      id: "pagerTranskip",
      css: "pager",
      template:
        "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jTranskip'>0</span></b> Mata Kuliah, <b><span id='jSKS'>0</span></b> SKS, Indeks Prestasi Komulatif = <b><span id='IPK'>0</span></b>",
      size: 15,
      group: 5,
      animate: {
        direction: "left",
        type: "slide",
      },
    },
  ],
}; //Transkip

/* View AkunMahasiswa */
var viewAkunMahasiswa = {
  id: "viewAkunMahasiswa",
  type: "space",
  borderless: true,
  rows: [
    {
      template: "Akun Mahasiswa",
      type: "header",
    },
    {
      type: "clean",
      borderless: true,
      cols: [
        { template: " " },
        {
          view: "form",
          id: "formAkunMahasiswa",
          scroll: false,
          width: 400,
          borderless: true,
          elements: [
            {
              view: "fieldset",
              label: "Ubah Akun Mahasiswa",
              body: {
                rows: [
                  {
                    view: "text",
                    id: "passBaru1",
                    name: "passBaru1",
                    type: "password",
                    label: "Password Baru",
                    labelWidth: 180,
                    required: true,
                    invalidMessage: "Password baru belum diisi",
                  },
                  {
                    view: "text",
                    id: "passBaru",
                    name: "passBaru",
                    type: "password",
                    label: "Ulangi Password Baru",
                    labelWidth: 180,
                    required: true,
                    invalidMessage: "Ulangi Password baru belum diisi",
                  },
                  {
                    view: "text",
                    id: "nipdUbahPass",
                    name: "nipd",
                    value: "",
                    hidden: true,
                  },
                  { view: "text", name: "aksi", value: "pass", hidden: true },
                  { template: " ", borderless: true, height: 20 },
                  {
                    margin: 5,
                    cols: [
                      { template: " ", borderless: true },
                      {
                        view: "button",
                        id: "simpanAkunMahasiswa",
                        label: "Ubah Pass",
                        type: "form",
                      },
                      { template: " ", borderless: true },
                    ],
                  },
                ],
              },
            },
          ],
        },

        {
          view: "fieldset",
          label: "Foto Mahasiswa",
          body: {
            rows: [
              {
                id: "fotoMhs",
                template: "#foto#",
                height: 150,
                borderless: true,
              },
            ],
          },
        },

        {
          view: "fieldset",
          label: "Dokumen Kartu Keluarga",
          body: {
            rows: [
              {
                id: "kkMhs",
                template: "#link#",
                height: 150,
                borderless: true,
              },
            ],
          },
        },

        { template: " " },
      ],
    },

    {
      template: " ",
      borderless: true,
    },
  ],
};

var masterMahasiswaDetail = {
  id: "masterMahasiswaDetail",
  type: "space",
  cols: [
    {
      id: "panelKiriMahasiswaDetail",
      header: "Akses Cepat",
      width: 200,
      body: {
        id: "menuMahasiswa",
        view: "list",
        select: true,
        scroll: false,
        data: menu_mahasiswa,
      },
    },
    {
      rows: [
        {
          type: "header",
          borderless: true,
          cols: [
            {
              view: "button",
              id: "kembaliMahasiswa",
              type: "icon",
              icon: "chevron-left",
              label: "Kembali",
              width: 100,
              css: "headerBackBg",
            },
            {
              view: "template",
              id: "judulMahasiswaDetail",
              template: "",
              borderless: true,
              css: "headerBg",
            },
          ],
        },
        {
          id: "kontenMahasiswaDetail",
          cells: [
            viewMahasiswaDetail,
            viewMahasiswaKRS,
            viewMahasiswaKHS,
            viewMahasiswaTranskip,
            viewAkunMahasiswa,
          ],
        },
      ],
    },
  ],
};

var masterMahasiswa = new WebixView({
  config: {
    id: "masterMahasiswa",
    cells: [viewMahasiswa, masterMahasiswaDetail],
  },
});

/*HAK AKSES KRS - KEUANGAN*/

var halamanHakAksesKRS = new WebixView({
  config: {
    id: "halamanHakAksesKRS",
    type: "space",
    cols: [
      {
        header: "Informasi Hak AKSES KRS",
        width: 200,
        body: {
          template:
            "Halaman ini digunakan untuk mengelola mahasiswa yang diperbolehkan mengakses KRS.",
        },
      },
      {
        rows: [
          {
            template: "Hak Akses KRS Mahasiswa",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                label: "Refresh",
                id: "refreshHakAkses",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              {
                view: "button",
                label: "Tambah",
                id: "tambahHakAkses",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                label: "Hapus",
                id: "hapusHakAkses",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            editable: true,
            id: "dataTableHakAkses",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: ["Nama Mahasiswa", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nipd",
                header: ["NIM", { content: "textFilter" }],
                sort: "string",
                width: 100,
              },
              {
                id: "jk",
                header: ["L/P", { content: "selectFilter" }],
                sort: "string",
                width: 50,
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "vid_jns_daftar",
                header: ["Jenis Daftar", {
                  content: "selectFilter",
                  options: [
                    { id: "Mahasiswa Baru", value: "Mahasiswa Baru" },
                    { id: "Pindahan/Transfer", value: "Pindahan/Transfer" },
                    { id: "RPL Perolehan SKS", value: "RPL Perolehan SKS" },
                    { id: "RPL Transfer SKS / Karyawan", value: "RPL Transfer SKS / Karyawan" },
                    { id: "Mahasiswa K2", value: "Mahasiswa K2" }
                  ]
                }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "kelas",
                header: ["Kelas", { content: "textFilter" }],
                width: 60,
                sort: "string",
              },
            ],
            hover: "tableHover",
            datafetch: 20,
            url:
              "idata->sopingi/mahasiswa/tampilHakAkses/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                $("#jMahasiswa").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jMahasiswa = this.data.order.length;
                $("#jMahasiswa").html(jMahasiswa);
              },
              onAfterFilter: function () {
                jMahasiswa = this.data.order.length;
                $("#jMahasiswa").html(jMahasiswa);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jMahasiswa = this.data.order.length;
                $("#jMahasiswa").html(jMahasiswa);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jMahasiswa'>  </span></b> Mahasiswa (Scroll kebawah untuk memuat data berikutnya)",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
}); //config

//TAMBAH HAK AKSES PESERTA

function checkbox_hakakses(obj, common, value) {
  if (value) {
    return "<div class='webix_table_checkbox webix_icon fa-check checked2 tambahAksesMhs'> OK</div>";
  } else {
    return "<div class='webix_table_checkbox webix_icon fa-close notchecked2 tambahAksesMhs'> &nbsp;&nbsp;&nbsp; </div>";
  }
}

var formModalMhs = {
  id: "formModalMhs",
  type: "clean",
  cols: [
    {
      rows: [
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "refreshModalMhs",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            { template: "", borderless: true },
            {
              view: "button",
              id: "tambahModalMhs",
              label: "Tambahkan",
              type: "iconButton",
              icon: "plus",
              width: 100,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          editable: true,
          id: "dataTableModalMhs",
          columns: [
            { id: "index", header: "No", width: 40 },
            {
              id: "nm_pd",
              header: ["Nama Mahasiswa", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "nipd",
              header: ["NIM", { content: "textFilter" }],
              sort: "string",
              width: 100,
            },
            {
              id: "jk",
              header: ["L/P", { content: "selectFilter" }],
              sort: "string",
              width: 50,
            },
            {
              id: "vnm_lemb",
              header: ["Program Studi", { content: "selectFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "kelas",
              header: ["Kelas", { content: "textFilter" }],
              width: 60,
              sort: "string",
            },
            {
              id: "hakAkses",
              header: "Ceklist",
              css: { "text-align": "left" },
              template: checkbox_hakakses,
              width: 70,
            },
          ],
          hover: "tableHover",
          datafetch: 20,
          url:
            "idata->sopingi/mahasiswa/tampilBelumHakAkses/" +
            wSia.apiKey +
            "/" +
            Math.random(),
          on: {
            onBeforeLoad: function () {
              $("#jMahasiswa").html("Sedang memuat...");
            },
            onAfterLoad: function () {
              this.hideOverlay();
              jMahasiswa = this.data.order.length;
              $("#jMahasiswa").html(jMahasiswa);
            },
            onAfterFilter: function () {
              jMahasiswa = this.data.order.length;
              $("#jMahasiswa").html(jMahasiswa);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jMahasiswa = this.data.order.length;
              $("#jMahasiswa").html(jMahasiswa);
            },
          },
        },
        {
          template:
            "Total Ter-load: <b><span id='jMahasiswa'>  </span></b> Mahasiswa (Scroll kebawah untuk memuat data berikutnya)",
          height: 30,
          borderless: true,
        },
      ],
    },
  ],
};

/* DOSEN */
var masterDosen = new WebixView({
  config: {
    id: "masterDosen",
    type: "space",
    cols: [
      {
        header: "Informasi Dosen",
        width: 200,
        body: {
          template:
            "Master dosen digunakan untuk mengolah data dosen. Agar dosen dapat dimasukkan ke dalam kelas perkuliahan perlu ditambahkan terlebih dahulu ke dalam penugasan dosen",
        },
      },
      {
        rows: [
          {
            template: "Master Dosen",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahDosen",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshDosen",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahDosen",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusDosen",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableDosen",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_ptk_gelar",
                header: ["Nama Dosen", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nidn",
                header: "NIDN/NUP/NIDK",
                sort: "string",
                width: 150,
              },
              //{ id:"nip",header:"NIP",sort:"string"},
              {
                id: "jk",
                header: ["L/P", { content: "selectFilter" }],
                width: 80,
                sort: "string",
              },
              { id: "nm_agama", header: "Agama", sort: "int" },
              { id: "tmpt_lahir", header: "Tempat Lahir" },
              {
                id: "tgl_lahir",
                header: "Tgl.Lahir",
                format: webix.Date.dateToStr("%d-%m-%Y"),
                sort: "string",
              },
            ],
            pager: "pagerDosen",
            url: "sopingi/dosen/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jDosen = this.data.order.length;
                $("#jDosen").html(jDosen);
              },
              onAfterFilter: function () {
                jDosen = this.data.order.length;
                $("#jDosen").html(jDosen);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jDosen = this.data.order.length;
                $("#jDosen").html(jDosen);
              },
            },
          },
          {
            view: "pager",
            id: "pagerDosen",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jDosen'>  </span></b> Dosen",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  },
});

var formDosen = {
  view: "form",
  id: "formDosen",
  borderless: true,
  elements: [
    {
      view: "text",
      label: "NIDN/NUP/NIDK",
      name: "nidn",
      id: "nidn",
      placeholder: "NIDN/NUP/NIDK",
      required: true,
      invalidMessage: "NIDN/NUP/NIDK belum diisi",
      inputWidth: 150,
      attributes: { maxlength: 15 },
    },
    {
      view: "text",
      label: "Nama Dosen",
      name: "nm_ptk",
      id: "nm_ptk",
      placeholder: "Nama dosen tanpa gelar",
      required: true,
      invalidMessage: "Nama dosen belum diisi",
      inputWidth: 400,
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
      required: true,
      invalidMessage: "Jenis kelamin belum dipilih",
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
      inputWidth: 150,
    },
    {
      cols: [
        {
          view: "text",
          label: "Tempat Lahir",
          name: "tmpt_lahir",
          id: "tmpt_lahir",
          required: true,
          inputWidth: 200,
          invalidMessage: "Tempat lahir belum diisi",
        },
        {
          view: "datepicker",
          label: "Tanggal Lahir",
          name: "tgl_lahir",
          id: "tgl_lahir",
          format: "%d-%m-%Y",
          required: true,
          placeholder: "Tanggal lahir",
          invalidMessage: "Tanggal lahir ibu belum diisi",
          inputWidth: 200,
          stringResult: true,
        },
      ],
    },
    {
      cols: [
        {
          view: "text",
          label: "Gelar Depan",
          name: "gelar_depan",
          id: "gelar_depan",
          inputWidth: 150,
        },
        {
          view: "text",
          label: "Gelar Belakang",
          name: "gelar_belakang",
          id: "gelar_belakang",
          inputWidth: 150,
        },
      ],
    },
    {
      cols: [
        {
          view: "text",
          label: "Password",
          name: "passBaru",
          id: "passBaru",
          inputWidth: 180,
          type: "password",
        },
        {
          view: "text",
          label: "Ulangi Password",
          name: "passBaru1",
          id: "passBaru1",
          inputWidth: 180,
          type: "password",
        },
      ],
    },
    {
      view: "text",
      name: "xid_ptk",
      id: "xid_ptk",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanDosen",
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

/* PENUGASAN DOSEN */
var masterPenugasanDosen = new WebixView({
  config: {
    id: "masterPenugasanDosen",
    type: "space",
    cols: [
      {
        header: "Informasi Penugasan Dosen",
        width: 200,
        body: {
          template:
            "Penugasan dosen untuk pengajaran satu tahun akademik ganjil dan genap",
        },
      },
      {
        rows: [
          {
            template: "Data Penugasan Dosen",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahPenugasanDosen",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshPenugasanDosen",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahPenugasanDosen",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusPenugasanDosen",
                label: "Hapus",
                type: "iconButton",
                icon: "close",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTablePenugasanDosen",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_ptk",
                header: ["Nama Dosen", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              { id: "nidn", header: "NIDN/NUP/NIDK", sort: "string" },
              {
                id: "jk",
                header: ["L/P", { content: "selectFilter" }],
                width: 50,
                sort: "string",
              },
              { id: "thn_ajaran", header: "Thn. Ajaran", sort: "string" },
              {
                id: "prodi",
                header: ["Program Studi", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "no_srt_tgs",
                header: "No. Surat Tugas",
                fillspace: true,
                sort: "string",
              },
              {
                id: "tgl_srt_tgs",
                header: "Tgl. Surat Tugas",
                format: webix.Date.dateToStr("%d-%m-%Y"),
                sort: "string",
              },
              { id: "homebase", header: "Homebase?", sort: "string" },
            ],
            pager: "pagerPenugasanDosen",
            url: "sopingi/dosen_pt/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jPenugasanDosen = this.data.order.length;
                $("#jPenugasanDosen").html(jPenugasanDosen);
              },
              onAfterFilter: function () {
                jPenugasanDosen = this.data.order.length;
                $("#jPenugasanDosen").html(jPenugasanDosen);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jPenugasanDosen = this.data.order.length;
                $("#jPenugasanDosen").html(jPenugasanDosen);
              },
            },
          },
          {
            view: "pager",
            id: "pagerPenugasanDosen",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jPenugasanDosen'>  </span></b> Dosen",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  },
});

var formPenugasanDosen = {
  view: "form",
  id: "formPenugasanDosen",
  borderless: true,
  elements: [
    {
      view: "combo",
      label: "Dosen",
      name: "xid_ptk",
      id: "xid_ptk",
      placeholder: "Pilih Dosen",
      required: true,
      invalidMessage: "Dosen belum dipilih",
    },
    {
      view: "richselect",
      label: "Program Studi",
      name: "id_sms",
      id: "id_sms",
      placeholder: "Pilih Program Studi",
      required: true,
      invalidMessage: "Program studi belum dipilih",
      options: "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
      value: "",
    },
    {
      view: "text",
      label: "No. Surat Tugas",
      name: "no_srt_tgs",
      id: "no_srt_tgs",
      required: true,
    },
    {
      view: "datepicker",
      label: "Tgl. Surat Tugas",
      name: "tgl_srt_tgs",
      id: "tgl_srt_tgs",
      stringResult: true,
      format: "%d-%m-%Y",
      required: true,
      inputWidth: 150,
    },
    {
      view: "toggle",
      name: "a_sp_homebase",
      id: "a_sp_homebase",
      type: "iconButton",
      offIcon: "close",
      onIcon: "check",
      offLabel: "Tidak Homebase",
      onLabel: "Homebase",
      inputWidth: 200,
    },
    {
      view: "text",
      name: "xid_reg_ptk",
      id: "xid_reg_ptk",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanPenugasanDosen",
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

/* KELAS PERKULIAHAN */
var viewKelasKuliah = {
  id: "viewKelasKuliah",
  type: "space",
  cols: [
    {
      header: "Informasi Kelas Kuliah",
      width: 200,
      body: {
        template:
          "Kelas Kuliah adalah kelas perkuliahan yang ada di perguruan tinggi. WAJIB diinput sebelum pengisian KRS Oleh mahasiswa",
      },
    },
    {
      rows: [
        {
          template: "Master Kelas Perkuliahan",
          type: "header",
        },
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "tambahKelasKuliah",
              label: "Tambah",
              type: "iconButton",
              icon: "plus",
              width: 100,
            },
            {
              view: "button",
              id: "refreshKelasKuliah",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            { template: "", borderless: true },
            {
              view: "button",
              id: "ubahKelasKuliah",
              label: "Ubah",
              type: "iconButton",
              icon: "edit",
              width: 100,
            },
            {
              view: "button",
              id: "hapusKelasKuliah",
              label: "Hapus",
              type: "iconButton",
              icon: "remove",
              width: 100,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          id: "dataTableKelas",
          columns: [
            { id: "index", header: "No", width: 40 },
            {
              id: "prodi",
              header: ["Program Studi", { content: "selectFilter" }],
              fillspace: true,
              sort: "string",
            },
            { id: "id_smt", header: "Semester", sort: "string", width: 70 },
            { id: "kode_mk", header: "Kode MK", sort: "string", width: 70 },
            {
              id: "nm_mk",
              header: ["Mata Kuliah", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "nm_kls",
              header: ["Kelas", { content: "textFilter" }],
              sort: "string",
              width: 50,
            },
            { id: "sks_mk", header: "SKS", sort: "int", width: 40 },
            {
              id: "mbkm",
              header: "MBKM",
              width: 60,
              template: (o) => {
                if (o.mbkm == 1) {
                  return "Ya";
                } else {
                  return "Tidak";
                }
              },
            },
            {
              id: "peserta_kelas",
              header: [
                { colspan: 2, text: "Data", css: { "text-align": "center" } },
                "Mhs",
              ],
              template:
                "<button class='btnMhsDetail'><i class='webix_icon fa-users'></i> </button>",
              width: 50,
            },
            {
              id: "dosen_mengajar",
              header: ["", "Dosen"],
              template:
                "<button class='btnDosenDetail'><i class='webix_icon fa-user'></i> </button>",
              width: 50,
            },

            {
              id: "peserta_kelas",
              header: [
                {
                  colspan: 2,
                  text: "Absensi",
                  css: { "text-align": "center" },
                },
                "Mhs",
              ],
              template:
                "<button class='btnMhsAbsen'><i class='webix_icon fa-file-pdf-o'></i> </button>",
              width: 50,
            },
            {
              id: "absen_ujian",
              header: ["", { text: "Ujian", css: { "text-align": "center" } }],
              template:
                "<button class='btnAbsenUTS'><i class='webix_icon fa-file-word-o'></i>UTS</button> <button class='btnAbsenUAS'><i class='webix_icon fa-file-word-o'></i>UAS</button>",
              width: 130,
            },
          ],
          //pager:"pagerKelasKuliah",
          datafetch: 20,
          url:
            "idata->sopingi/kelas_kuliah/tampil/" +
            wSia.apiKey +
            "/" +
            Math.random(),
          on: {
            /*
          onBeforeLoad:function(){
            this.showOverlay("<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>");
          },
          */
            onBeforeLoad: function () {
              $("#jKelasKuliah").html("Sedang memuat...");
            },

            onAfterLoad: function () {
              this.hideOverlay();
              jKelasKuliah = this.data.order.length;
              $("#jKelasKuliah").html(jKelasKuliah);
            },
            onAfterFilter: function () {
              jKelasKuliah = this.data.order.length;
              $("#jKelasKuliah").html(jKelasKuliah);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jKelasKuliah = this.data.order.length;
              $("#jKelasKuliah").html(jKelasKuliah);
            },
          },
        },
        {
          template:
            "Total Ter-load: <b><span id='jKelasKuliah'>  </span></b> Kelas",
          height: 30,
          borderless: true,
        },
        /*,{
        view:"pager",
        id:"pagerKelasKuliah",
        template:"{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKelasKuliah'>  </span></b> Kelas",
        size:15,
        group:5,
        animate : {
          direction:"left", type:"slide"
        }
        }*/
      ],
    },
  ],
};

var formKelasKuliah = {
  view: "form",
  id: "formKelasKuliah",
  borderless: true,
  elements: [
    {
      view: "richselect",
      label: "Program Studi",
      name: "id_sms",
      id: "id_sms",
      placeholder: "Pilih Program Studi",
      required: true,
      invalidMessage: "Program studi belum dipilih",
      options: "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
      value: "",
    },
    {
      view: "richselect",
      label: "Semester",
      name: "id_smt",
      id: "id_smt",
      placeholder: "Pilih Semester",
      required: true,
      options: "sopingi/semester/pilih/" + wSia.apiKey + "/" + Math.random(),
      required: true,
      invalidMessage: "Semester belum dipilih",
    },
    {
      view: "richselect",
      label: "Kurikulum",
      name: "id_kurikulum_sp",
      id: "id_kurikulum_sp",
      placeholder: "Pilih Kurikulum",
      required: true,
      invalidMessage: "Kurikulum belum dipilih",
    },
    {
      view: "combo",
      label: "Mata Kuliah",
      name: "id_mk",
      id: "id_mk",
      placeholder: "Pilih Mata Kuliah",
      required: true,
      invalidMessage: "Mata Kuliah belum dipilih",
    },
    //{ view:"richselect", label:"Nama Kelas", name:"nm_kls", id:"nm_kls", placeholder:"Pilih nama kelas", required:true, invalidMessage: "Nama kelas belum dipilih",options:"sopingi/siakad_kelas/pilih/"+wSia.apiKey+"/"+Math.random() },
    {
      view: "text",
      label: "Nama Kelas (Harus sama dengan <b>Nama Kelas</b> di Master kelas",
      name: "nm_kls",
      id: "nm_kls",
      placeholder: "Ketik nama kelas",
      required: true,
      invalidMessage: "Nama kelas belum diisi",
      bottomLabel:
        "Jika kelas lebih dari 1 (gabungan) pisahkan dengan tanda koma tanpa spasi. Contoh: 21A,21B,21C",
    },
    {
      view: "radio",
      label: "Full MBKM",
      options: [
        { id: 0, value: "Tidak" },
        { id: 1, value: "Ya" },
      ],
      name: "mbkm",
      bottomLabel: "* Jika full MBKM, kelas tidak dilaporkan ke NeoFeeder",
    },
    {
      view: "text",
      name: "xid_kls",
      id: "xid_kls",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanKelasKuliah",
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

var viewMahasiswaKelas = {
  id: "viewMahasiswaKelas",
  type: "space",
  cols: [
    {
      header: "Kelas Perkuliahan",
      width: 250,
      body: {
        id: "panelKelasKuliah",
        view: "list",
        type: {
          templateStart: "<div class='panelKelasPerkuliahan'>",
          template: "#judul#<br><b>#konten#</b>",
          templateEnd: "</div>",
        },
      },
    },
    {
      rows: [
        {
          type: "header",
          borderless: true,
          cols: [
            {
              view: "button",
              id: "kembaliKelasKuliah",
              type: "icon",
              icon: "chevron-left",
              label: "Kembali",
              width: 100,
              css: "headerBackBg",
            },
            {
              view: "template",
              template: "Mahasiswa KRS/Peserta Kelas",
              borderless: true,
              css: "headerBg",
            },
            {
              view: "text",
              name: "xid_klsMhs",
              id: "xid_klsMhs",
              hidden: true,
            },
          ],
        },
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "refreshMhsKelas",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            {
              view: "button",
              label: "Absen MHS",
              id: "unduhAbsenMhs",
              type: "iconButton",
              icon: "file-pdf-o",
              width: 120,
            },
            { template: "", borderless: true },
            {
              view: "button",
              label: "Simpan Nilai",
              id: "simpanKhsKelas",
              type: "iconButton",
              icon: "save",
              width: 120,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          editable: true,
          id: "dataTableMahasiswaKelas",
          columns: [
            { id: "index", header: "No", width: 40 },
            { id: "xid_reg_pd", header: "No Daftar", sort: "string" },
            { id: "nipd", header: "NIM", sort: "string" },
            {
              id: "nm_pd",
              header: ["Nama Mahasiswa", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "jk",
              header: ["L/P", { content: "selectFilter" }],
              width: 80,
              sort: "string",
            },
            {
              id: "prodi",
              header: ["Program Studi", { content: "selectFilter" }],
              fillspace: true,
            },
            {
              id: "angkatan",
              header: ["Angkatan", { content: "selectFilter" }],
              width: 100,
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
          ],
          pager: "pagerMahasiswaKelas",
          hover: "tableHover",
          //url:"sopingi/kelas_kuliah/mahasiswa/"+wSia.apiKey+"/"+Math.random(),
          on: {
            onBeforeLoad: function () {
              this.showOverlay(
                "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
              );
            },
            onAfterLoad: function () {
              this.hideOverlay();
              jMahasiswaKelas = this.data.order.length;
              $("#jMahasiswaKelas").html(jMahasiswaKelas);
            },
            onAfterFilter: function () {
              jMahasiswaKelas = this.data.order.length;
              $("#jMahasiswaKelas").html(jMahasiswaKelas);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jMahasiswaKelas = this.data.order.length;
              $("#jMahasiswaKelas").html(jMahasiswaKelas);
            },
            onAfterEditStop: function (data, editor, ignoreUpdate) {
              record = this.getItem(editor.row);
              nilaiBaru = parseFloat(data.value);

              abobot.forEach(function (item, index) {
                if (nilaiBaru >= parseFloat(item.bobot_nilai_min)) {
                  record["nilai_huruf"] = item.nilai_huruf;
                  record["nilai_indeks"] = item.nilai_indeks;
                  record["sksXindeks"] =
                    record["vsks_mk"] * record["nilai_indeks"];
                }
              });

              if (data.value == "T" || data.value == "t") {
                record["nilai_angka"] = 0;
                record["nilai_huruf"] = "T";
                record["nilai_indeks"] = 0;
              }

              if (data.value == "") {
                record["nilai_angka"] = 0;
                record["nilai_huruf"] = "";
                record["nilai_indeks"] = 0;
              }

              this.updateItem(editor.row, record);
            },
          }, //on
        },
        {
          view: "pager",
          id: "pagerMahasiswaKelas",
          template:
            "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jMahasiswaKelas'>  </span></b> Mahasiswa &nbsp; &nbsp (<i>Isi nilai angka dengan huruf <b>T</b> untuk nilai tunda<i>)",
          size: 15,
          group: 5,
          animate: {
            direction: "left",
            type: "slide",
          },
        },
      ],
    },
  ],
};

var viewDosenKelas = {
  id: "viewDosenKelas",
  type: "space",
  cols: [
    {
      header: "Kelas Perkuliahan",
      width: 250,
      body: {
        id: "panelKelasKuliah2",
        view: "list",
        type: {
          templateStart: "<div class='panelKelasPerkuliahan'>",
          template: "#judul#<br><b>#konten#</b>",
          templateEnd: "</div>",
        },
      },
    },
    {
      rows: [
        {
          type: "header",
          borderless: true,
          cols: [
            {
              view: "button",
              id: "kembaliKelasKuliah2",
              type: "icon",
              icon: "chevron-left",
              label: "Kembali",
              width: 100,
              css: "headerBackBg",
            },
            {
              view: "template",
              template: "Dosen Pengajar",
              borderless: true,
              css: "headerBg",
            },
            {
              view: "text",
              name: "xid_klsDosen",
              id: "xid_klsDosen",
              hidden: true,
            },
          ],
        },
        {
          view: "toolbar",
          paddingY: 2,
          cols: [
            {
              view: "button",
              id: "tambahDosenKelas",
              label: "Tambah",
              type: "iconButton",
              icon: "plus",
              width: 100,
            },
            {
              view: "button",
              id: "refreshDosenKelas",
              label: "Refresh",
              type: "iconButton",
              icon: "refresh",
              width: 100,
            },
            { template: "", borderless: true },
            {
              view: "button",
              id: "ubahDosenKelas",
              label: "Ubah",
              type: "iconButton",
              icon: "edit",
              width: 100,
            },
            {
              view: "button",
              id: "hapusDosenKelas",
              label: "Hapus",
              type: "iconButton",
              icon: "remove",
              width: 100,
            },
          ],
        },
        {
          view: "datatable",
          select: true,
          id: "dataTableDosenKelas",
          columns: [
            { id: "index", header: "No", width: 40 },
            { id: "nidn", header: "NIDN", sort: "string" },
            {
              id: "nm_ptk",
              header: ["Nama Dosen", { content: "textFilter" }],
              fillspace: true,
              sort: "string",
            },
            {
              id: "sks_subst_tot",
              header: ["SKS", { content: "selectFilter" }],
              sort: "int",
            },
            {
              id: "jml_tm_renc",
              header: [
                {
                  colspan: 2,
                  text: "Pertemuan",
                  css: { "text-align": "center" },
                },
                "Rencana",
              ],
              width: 70,
            },
            { id: "jml_tm_real", header: ["", "Realisasi"], width: 70 },
            {
              id: "nm_jns_eval",
              header: ["Jenis Evaluasi", { content: "selectFilter" }],
              fillspace: true,
              sort: "string",
            },
            { id: "hari", header: "Hari", sort: "string", width: 70 },
            { id: "jam", header: "Jam", sort: "string" },
            { id: "ruang", header: "Ruang", sort: "string" },
            { id: "kode_gabung", header: "Kode Gabung", sort: "string" },
            {
              id: "absen_dosen",
              header: "Jurnal PDF",
              template:
                "<button class='btnDosenAbsen'><i class='webix_icon fa-file-pdf-o'></i> Jurnal</button>",
            },
          ],
          pager: "pagerDosenKelas",
          hover: "tableHover",
          //url:"sopingi/kelas_kuliah/dosen/"+wSia.apiKey+"/"+Math.random(),
          on: {
            onBeforeLoad: function () {
              this.showOverlay(
                "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
              );
            },
            onAfterLoad: function () {
              this.hideOverlay();
              jDosenKelas = this.data.order.length;
              $("#jDosenKelas").html(jDosenKelas);
            },
            onAfterFilter: function () {
              jDosenKelas = this.data.order.length;
              $("#jDosenKelas").html(jDosenKelas);
            },
            "data->onStoreUpdated": function () {
              this.data.each(function (obj, i) {
                obj.index = i + 1;
              });

              jDosenKelas = this.data.order.length;
              $("#jDosenKelas").html(jDosenKelas);
            },
          },
        },
        {
          view: "pager",
          id: "pagerDosenKelas",
          template:
            "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jDosenKelas'>  </span></b> Dosen Pengajar",
          size: 15,
          group: 5,
          animate: {
            direction: "left",
            type: "slide",
          },
        },
      ],
    },
  ],
};

var formDosenKelas = {
  view: "form",
  id: "formDosenKelas",
  borderless: true,
  elements: [
    {
      view: "combo",
      label: "Dosen",
      name: "xid_reg_ptk",
      id: "xid_reg_ptk",
      placeholder: "Pilih Dosen",
      required: true,
      invalidMessage: "Dosen belum dipilih",
    },
    {
      view: "counter",
      label: "SKS",
      name: "sks_subst_tot",
      id: "sks_subst_tot",
      required: true,
      validate: webix.rules.isNumber,
      invalidMessage: "SKS belum diisi/ Type harus numerik",
    },
    {
      view: "counter",
      label: "Jumlah Rencana Tatap Muka",
      name: "jml_tm_renc",
      id: "jml_tm_renc",
      required: true,
      validate: webix.rules.isNumber,
      invalidMessage: "Rencana tatap muka belum diisi/ Type harus numerik",
    },
    {
      view: "counter",
      label: "Jumlah Realisasi Tatap Muka",
      name: "jml_tm_real",
      id: "jml_tm_real",
      required: true,
      validate: webix.rules.isNumber,
      invalidMessage: "Type harus numerik",
    },
    {
      view: "combo",
      label: "Hari",
      name: "hari",
      id: "hari",
      options: [
        { id: "Senin", value: "Senin" },
        { id: "Selasa", value: "Selasa" },
        { id: "Rabu", value: "Rabu" },
        { id: "Kamis", value: "Kamis" },
        { id: "Jumat", value: "Jumat" },
        { id: "Sabtu", value: "Sabtu" },
        { id: "Minggu", value: "Minggu" },
      ],
      inputWidth: 100,
      required: true,
    },
    {
      view: "text",
      label: "Jam",
      name: "jam",
      id: "jam",
      inputWidth: 200,
      required: true,
    },
    {
      view: "combo",
      label: "Ruang",
      name: "ruang",
      id: "ruang",
      inputWidth: 200,
      required: true,
      options:
        "sopingi/siakad_ruang/pilih/" + wSia.apiKey + "/" + Math.random(),
    },
    {
      view: "text",
      label: "Kode Gabung",
      name: "kode_gabung",
      id: "kode_gabung",
      inputWidth: 200,
      required: false,
      bottomLabel: "*Kosongkan jika tidak ada",
    },
    {
      view: "text",
      name: "xid_ajar",
      id: "xid_ajar",
      required: true,
      hidden: true,
    },
    {
      view: "text",
      name: "xid_klsAjarDosen",
      id: "xid_klsAjarDosen",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanDosenKelas",
          label: "Simpan",
          type: "form",
          width: 120,
          borderless: true,
        },
        { template: " ", borderless: true },
      ],
    },
  ],
  rules: {
    jml_tm_renc: function (value) {
      return value > 0;
    },
  },
  elementsConfig: {
    labelPosition: "top",
  },
};

var masterKelasKuliah = new WebixView({
  config: {
    id: "masterKelasKuliah",
    cells: [viewKelasKuliah, viewMahasiswaKelas, viewDosenKelas],
  },
});

/* KARTU UJIAN MURNI + TRANSFER*/
function checkbox_UTS(obj, common, value) {
  if (value) {
    return "<div class='webix_table_checkbox webix_icon fa-check checked2 cetakUTS'> cetak</div>";
  } else {
    return "<div class='webix_table_checkbox webix_icon fa-close notchecked2 cetakUTS'> tidak cetak</div>";
  }
}

function checkbox_UAS(obj, common, value) {
  if (value) {
    return "<div class='webix_table_checkbox webix_icon fa-check checked2 cetakUAS'> cetak</div>";
  } else {
    return "<div class='webix_table_checkbox webix_icon fa-close notchecked2 cetakUAS'> tidak cetak</div>";
  }
}

var masterKartuUjianMurni = new WebixView({
  config: {
    id: "masterKartuUjianMurni",
    type: "space",
    cols: [
      {
        header: "Informasi Kartu Ujian",
        width: 200,
        body: {
          template:
            "Kartu Ujian dapat dicetak bagi yang mengisi KRS.<br>Halaman ini khusus kartu ujian mahasiswa murni",
        },
      },
      {
        rows: [
          {
            template: "Kartu Ujian Mahasiswa Murni",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshKartuUjianMurni",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "unduhKartuUjianMurni",
                label: "Download PDF",
                type: "iconButton",
                icon: "pdf",
                width: 150,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            editable: true,
            id: "dataTableKartuUjianMurni",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: "Nama Mahasiswa",
                fillspace: true,
                sort: "string",
              },
              { id: "nipd", header: "NIM", sort: "string", width: 100 },
              { id: "jk", header: "L/P", sort: "string", width: 50 },
              {
                id: "kelas",
                header: ["Kelas", { content: "textFilter" }],
                width: 60,
                sort: "string",
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "kartuUTS",
                header: [
                  {
                    text: "Cetak Kartu Ujian",
                    colspan: 2,
                    css: { "text-align": "center" },
                  },
                  "<input id='cekUTS' type='checkbox' class='webix_custom_checkbox'> UTS",
                ],
                css: { "text-align": "left" },
                template: checkbox_UTS,
                width: 130,
              },
              {
                id: "kartuUAS",
                header: [
                  "",
                  "<input id='cekUAS' type='checkbox' class='webix_custom_checkbox'> UAS",
                ],
                css: { "text-align": "left" },
                template: checkbox_UAS,
                width: 130,
              },
            ],
            hover: "tableHover",
            datafetch: 20,
            url:
              "idata->sopingi/kartu_ujian/tampilMurni/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                $("#jKartuUjianMurni").html("Sedang memuat...");
                webix.message("Loading...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jKartuUjianMurni = this.data.order.length;
                $("#jKartuUjianMurni").html(jKartuUjianMurni);
              },
              onAfterFilter: function () {
                jKartuUjianMurni = this.data.order.length;
                $("#jKartuUjianMurni").html(jKartuUjianMurni);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jKartuUjianMurni = this.data.order.length;
                $("#jKartuUjianMurni").html(jKartuUjianMurni);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jKartuUjianMurni'>  </span></b> Mahasiswa mengisi KRS",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

var masterKartuUjianTransfer = new WebixView({
  config: {
    id: "masterKartuUjianTransfer",
    type: "space",
    cols: [
      {
        header: "Informasi Kartu Ujian",
        width: 200,
        body: {
          template:
            "Kartu Ujian dapat dicetak bagi yang mengisi KRS.<br>Halaman ini khusus kartu ujian mahasiswa transfer",
        },
      },
      {
        rows: [
          {
            template: "Kartu Ujian Mahasiswa Transfer",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshKartuUjianTransfer",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "unduhKartuUjianTransfer",
                label: "Download PDF",
                type: "iconButton",
                icon: "pdf",
                width: 150,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            editable: true,
            id: "dataTableKartuUjianTransfer",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: "Nama Mahasiswa",
                fillspace: true,
                sort: "string",
              },
              { id: "nipd", header: "NIM", sort: "string", width: 100 },
              { id: "jk", header: "L/P", sort: "string", width: 50 },
              { id: "kelas", header: "Kelas", width: 60, sort: "string" },
              {
                id: "angkatan",
                header: ["Angkatan", { content: "textFilter" }],
                width: 80,
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "kartuUTS",
                header: [
                  {
                    text: "Cetak Kartu Ujian",
                    colspan: 2,
                    css: { "text-align": "center" },
                  },
                  "<input id='cekUTS' type='checkbox' class='webix_custom_checkbox'> UTS",
                ],
                css: { "text-align": "left" },
                template: checkbox_UTS,
                width: 130,
              },
              {
                id: "kartuUAS",
                header: [
                  "",
                  "<input id='cekUAS' type='checkbox' class='webix_custom_checkbox'> UAS",
                ],
                css: { "text-align": "left" },
                template: checkbox_UAS,
                width: 130,
              },
            ],
            hover: "tableHover",
            datafetch: 20,
            url:
              "idata->sopingi/kartu_ujian/tampilTransfer/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                $("#jKartuUjianTransfer").html("Sedang memuat...");
                webix.message("Loading...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jKartuUjianTransfer = this.data.order.length;
                $("#jKartuUjianTransfer").html(jKartuUjianTransfer);
              },
              onAfterFilter: function () {
                jKartuUjianTransfer = this.data.order.length;
                $("#jKartuUjianTransfer").html(jKartuUjianTransfer);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jKartuUjianTransfer = this.data.order.length;
                $("#jKartuUjianTransfer").html(jKartuUjianTransfer);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jKartuUjianTransfer'>  </span></b> Mahasiswa mengisi KRS",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/* KARTU HASIL STUDI MURNI + TRANSFER*/
function checkbox_KHS(obj, common, value) {
  if (value) {
    return "<div class='webix_table_checkbox webix_icon fa-check checked2 cetakKHS'> cetak</div>";
  } else {
    return "<div class='webix_table_checkbox webix_icon fa-close notchecked2 cetakKHS'> tidak cetak</div>";
  }
}

var masterKHSMurni = new WebixView({
  config: {
    id: "masterKHSMurni",
    type: "space",
    cols: [
      {
        header: "Informasi Kartu Hasil Studi",
        width: 200,
        body: {
          template:
            "Kartu Hasil Studi dapat dicetak bagi yang mengisi KRS.<br>Halaman ini khusus kartu hasil studi mahasiswa murni",
        },
      },
      {
        rows: [
          {
            template: "Kartu Hasil Studi Mahasiswa Murni",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshKHSMurni",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "unduhKHSMurni",
                label: "Download DOC",
                type: "iconButton",
                icon: "file",
                width: 150,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            editable: true,
            id: "dataTableKHSMurni",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: "Nama Mahasiswa",
                fillspace: true,
                sort: "string",
              },
              { id: "nipd", header: "NIM", sort: "string", width: 100 },
              { id: "jk", header: "L/P", sort: "string", width: 50 },
              {
                id: "kelas",
                header: ["Kelas", { content: "textFilter" }],
                width: 60,
                sort: "string",
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "khs",
                header: [
                  { text: "Cetak KHS", css: { "text-align": "center" } },
                  "<input id='cekKHS' type='checkbox' class='webix_custom_checkbox'> Semua",
                ],
                css: { "text-align": "left" },
                template: checkbox_KHS,
                width: 130,
              },
            ],
            hover: "tableHover",
            datafetch: 20,
            url:
              "idata->sopingi/khs/tampilMurni/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                $("#jKHSMurni").html("Sedang memuat...");
                webix.message("Loading...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jKHSMurni = this.data.order.length;
                $("#jKHSMurni").html(jKHSMurni);
              },
              onAfterFilter: function () {
                jKHSMurni = this.data.order.length;
                $("#jKHSMurni").html(jKHSMurni);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jKHSMurni = this.data.order.length;
                $("#jKHSMurni").html(jKHSMurni);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jKHSMurni'>  </span></b> Mahasiswa mengisi KRS",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

var masterKHSTransfer = new WebixView({
  config: {
    id: "masterKHSTransfer",
    type: "space",
    cols: [
      {
        header: "Informasi Kartu Hasil Studi ",
        width: 200,
        body: {
          template:
            "Kartu Hasil Studi  dapat dicetak bagi yang mengisi KRS.<br>Halaman ini khusus kartu hasil studi  mahasiswa transfer",
        },
      },
      {
        rows: [
          {
            template: "Kartu Hasil Studi Mahasiswa Transfer",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshKHSTransfer",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "unduhKHSTransfer",
                label: "Download PDF",
                type: "iconButton",
                icon: "pdf",
                width: 150,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            editable: true,
            id: "dataTableKHSTransfer",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: "Nama Mahasiswa",
                fillspace: true,
                sort: "string",
              },
              { id: "nipd", header: "NIM", sort: "string", width: 100 },
              { id: "jk", header: "L/P", sort: "string", width: 50 },
              { id: "kelas", header: "Kelas", width: 60, sort: "string" },
              {
                id: "angkatan",
                header: ["Angkatan", { content: "textFilter" }],
                width: 80,
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "khs",
                header: [
                  { text: "Cetak KHS", css: { "text-align": "center" } },
                  "<input id='cekKHS' type='checkbox' class='webix_custom_checkbox'> Semua",
                ],
                css: { "text-align": "left" },
                template: checkbox_KHS,
                width: 130,
              },
            ],
            hover: "tableHover",
            datafetch: 20,
            url:
              "idata->sopingi/khs/tampilTransfer/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                $("#jKHSTransfer").html("Sedang memuat...");
                webix.message("Loading...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jKHSTransfer = this.data.order.length;
                $("#jKHSTransfer").html(jKHSTransfer);
              },
              onAfterFilter: function () {
                jKHSTransfer = this.data.order.length;
                $("#jKHSTransfer").html(jKHSTransfer);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jKHSTransfer = this.data.order.length;
                $("#jKHSTransfer").html(jKHSTransfer);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jKHSTransfer'>  </span></b> Mahasiswa mengisi KRS",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

var masterMahasiswaKeluar = new WebixView({
  config: {
    id: "masterMahasiswaKeluar",
    type: "space",
    cols: [
      {
        header: "Informasi Mahasiswa Keluar",
        width: 200,
        body: {
          template:
            "Mahasiswa Keluar digunakan untuk pengelolaan mahasiswa yang lulus atau keluar",
        },
      },
      {
        rows: [
          {
            template: "Mahasiswa Lulus / Keluar",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahMahasiswaKeluar",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshMahasiswaKeluar",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              {
                view: "button",
                id: "excelMahasiswaKeluar",
                label: "Unduh XLS",
                type: "iconButton",
                icon: "file-excel-o",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahMahasiswaKeluar",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusMahasiswaKeluar",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableMahasiswaKeluar",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nipd",
                header: ["NIM", { content: "textFilter" }],
                sort: "string",
                width: 100,
              },
              {
                id: "nm_pd",
                header: ["Nama Mahasiswa", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "jk",
                header: ["L/P", { content: "textFilter" }],
                sort: "string",
                width: 50,
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "ket_keluar",
                header: ["Jenis Keluar", { content: "textFilter" }],
                width: 150,
                sort: "string",
              },
              {
                id: "angkatan",
                header: ["Angkatan", { content: "textFilter" }],
                width: 100,
                sort: "string",
              },
              {
                id: "tgl_keluar",
                header: ["Tgl Keluar", { content: "textFilter" }],
                format: webix.Date.dateToStr("%d-%m-%Y"),
                sort: "string",
                width: 100,
              },
            ],
            hover: "tableHover",
            datafetch: 20,
            url:
              "idata->sopingi/mahasiswa/keluar/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                $("#jMahasiswaKeluar").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jMahasiswaKeluar = this.data.order.length;
                $("#jMahasiswaKeluar").html(jMahasiswaKeluar);
              },
              onAfterFilter: function () {
                jMahasiswaKeluar = this.data.order.length;
                $("#jMahasiswaKeluar").html(jMahasiswaKeluar);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jMahasiswaKeluar = this.data.order.length;
                $("#jMahasiswaKeluar").html(jMahasiswaKeluar);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jMahasiswaKeluar'>  </span></b> Mahasiswa (Scroll kebawah untuk memuat data berikutnya)",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

var formMahasiswaKeluar = {
  view: "form",
  id: "formMahasiswaKeluar",
  borderless: true,
  elements: [
    {
      view: "combo",
      label: "Mahasiswa",
      name: "xid_reg_pd",
      id: "xid_reg_pd",
      placeholder: "Pilih Mahasiswa",
      required: true,
      invalidMessage: "Mahasiswa belum dipilih",
    },
    {
      view: "richselect",
      label: "Jenis Keluar",
      name: "id_jns_keluar",
      id: "id_jns_keluar",
      placeholder: "Pilih Jenis Keluar",
      required: true,
      invalidMessage: "Jenis Keluar belum dipilih",
      options:
        "sopingi/jenis_keluar/pilih/" + wSia.apiKey + "/" + Math.random(),
      inputWidth: 200,
    },
    {
      view: "richselect",
      label: "Semester Keluar",
      name: "id_periode_keluar",
      placeholder: "Pilih Semester",
      required: true,
      options: "sopingi/semester/pilih/" + wSia.apiKey + "/" + Math.random(),
      required: true,
      invalidMessage: "Semester belum dipilih",
      inputWidth: 200,
    },
    {
      view: "datepicker",
      label: "Tgl. Keluar",
      name: "tgl_keluar",
      id: "tgl_keluar",
      stringResult: true,
      format: "%d-%m-%Y",
      required: true,
      inputWidth: 150,
    },
    {
      view: "text",
      label: "SK Yudisium",
      name: "sk_yudisium",
      id: "sk_yudisium",
      required: true,
    },
    {
      view: "datepicker",
      label: "Tgl. SK Yudisium",
      name: "tgl_sk_yudisium",
      id: "tgl_sk_yudisium",
      stringResult: true,
      format: "%d-%m-%Y",
      required: true,
      inputWidth: 150,
    },
    {
      view: "text",
      label: "No Seri Ijazah",
      name: "no_seri_ijazah",
      id: "no_seri_ijazah",
      required: true,
    },

    {
      view: "textarea",
      label: "Judul Skripsi/ Tugas Akhir",
      name: "judul_skripsi",
      id: "judul_skripsi",
      required: true,
      height: 100,
      bottomPadding: 10,
    },

    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    { template: " ", borderless: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanMahasiswaKeluar",
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

/* MASTER SEMESTER */
var masterSemester = new WebixView({
  config: {
    id: "masterSemester",
    type: "space",
    cols: [
      {
        header: "Informasi Tahun Akademik Semester",
        width: 200,
        body: {
          template:
            "Tahun Akademik Semester digunakan untuk pengelolaan tahun dan semester untuk kegiatan akademik",
        },
      },
      {
        rows: [
          {
            template: "Manage Tahun Akademik",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                label: "Refresh",
                id: "refreshSemester",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              {
                view: "button",
                label: "Tambah",
                id: "tambahSemester",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                label: "Hapus",
                id: "hapusSemester",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableSemester",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "id_thn_ajaran",
                header: "Tahun Akademik",
                fillspace: true,
                sort: "string",
              },
              {
                id: "nm_smt",
                header: "Semester",
                fillspace: true,
                sort: "string",
              },
              { id: "statusAktif", header: "Aktif KRS", width: 150 },
            ],
            hover: "tableHover",
            pager: "pagerSemester",
            url: "sopingi/semester/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jSemester = this.data.order.length;
                $("#jSemester").html(jSemester);
              },
              onAfterFilter: function () {
                jSemester = this.data.order.length;
                $("#jSemester").html(jSemester);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jSemester = this.data.order.length;
                $("#jSemester").html(jSemester);
              },
            },
          },
          {
            view: "pager",
            id: "pagerSemester",
            css: "pager",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jSemester'> </span></b> Data",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  }, //config
});

var formSemester = {
  rows: [
    {
      view: "form",
      id: "formSemester",
      borderless: true,
      elements: [
        { template: "", height: 2 },
        {
          view: "datepicker",
          name: "id_thn_ajaran",
          id: "id_thn_ajaran",
          label: "Tahun Akademik",
          type: "year",
          format: "%Y",
          required: true,
          stringResult: true,
        },
        {
          view: "richselect",
          label: "Semester",
          name: "smt",
          id: "smt",
          placeholder: "Pilih Semester",
          required: true,
          invalidMessage: "Semester belum dipilih",
          options: [
            { id: 1, value: "Ganjil" },
            { id: 2, value: "Genap" },
            { id: 3, value: "Pendek" },
          ],
          inputWidth: 200,
        },
        { view: "text", name: "id_smt", required: true, hidden: true },
        { view: "text", name: "aksi", required: true, hidden: true },
      ],
      elementsConfig: {
        labelPosition: "top",
      },
    },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanSemester",
          label: "Simpan",
          type: "form",
          width: 120,
          borderless: true,
        },
        { template: " ", borderless: true },
      ],
    },
  ],
};

/*BOBOT NILAI*/
function cekPersen() {
  var persen_absen = parseFloat($$("persen_absen").getValue()) || 0;
  var persen_tugas = parseFloat($$("persen_tugas").getValue()) || 0;
  var persen_uts = parseFloat($$("persen_uts").getValue()) || 0;
  var persen_uas = parseFloat($$("persen_uas").getValue()) || 0;
  var persen_total = persen_absen + persen_tugas + persen_uts + persen_uas;
  $$("persen_total").setValue(persen_total);
}

var masterBobotNilai = new WebixView({
  config: {
    id: "masterBobotNilai",
    type: "space",
    cols: [
      {
        header: "Informasi Bobot Nilai",
        width: 200,
        body: {
          rows: [
            {
              template:
                "Bobot nilai digunakan untuk konversi Nilai Angka ke dalam Nilai Indeks dan Nilai Huruf. Pastikan input data sudah benar karena akan berpengaruh saat pengInputan data nilai",
            },
            {
              id: "formPersenNilai",
              view: "form",
              url:
                "sopingi/bobot_nilai/tampilpersennilai/" +
                wSia.apiKey +
                "/" +
                Math.random(),
              elements: [
                {
                  view: "text",
                  label: "Absen (%)",
                  on: { onChange: () => cekPersen() },
                  name: "persen_absen",
                  id: "persen_absen",
                  validate: "isNumber",
                  invalidMessage: "Harus numeric",
                  required: true,
                },
                {
                  view: "text",
                  label: "Tugas (%)",
                  on: { onChange: () => cekPersen() },
                  name: "persen_tugas",
                  id: "persen_tugas",
                  validate: "isNumber",
                  invalidMessage: "Harus numeric",
                  required: true,
                },
                {
                  view: "text",
                  label: "UTS (%)",
                  on: { onChange: () => cekPersen() },
                  name: "persen_uts",
                  id: "persen_uts",
                  validate: "isNumber",
                  invalidMessage: "Harus numeric",
                  required: true,
                },
                {
                  view: "text",
                  label: "UAS (%)",
                  on: { onChange: () => cekPersen() },
                  name: "persen_uas",
                  id: "persen_uas",
                  validate: "isNumber",
                  invalidMessage: "Harus numeric",
                  required: true,
                },
                {
                  view: "text",
                  label: "Total (%)",
                  name: "persen_total",
                  id: "persen_total",
                  validate: "isNumber",
                  readonly: true,
                  invalidMessage: "Harus bernilai 100",
                  required: true,
                },
                {
                  cols: [
                    {},
                    {
                      view: "button",
                      id: "simpanPersenNilai",
                      value: "Simpan",
                    },
                    {},
                  ],
                },
              ],
              elementsConfig: {
                labelPosition: "left",
                labelWidth: 80,
              },
              rules: {
                persen_total: function (value) {
                  return value == 100;
                },
              },
            },
          ],
        },
      },
      {
        rows: [
          {
            template: "Manage Bobot Nilai",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahBobotNilai",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshBobotNilai",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahBobotNilai",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusBobotNilai",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableBobotNilai",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nilai_huruf",
                header: ["Nilai Huruf", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nilai_indeks",
                header: ["Nilai Indeks", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
                css: { "text-align": "right" },
              },
              {
                id: "bobot_nilai_min",
                header: ["Nilai Minimum", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
                css: { "text-align": "right" },
              },
              {
                id: "bobot_nilai_maks",
                header: ["Nilai Maksimum", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
                css: { "text-align": "right" },
              },
            ],
            hover: "tableHover",
            pager: "pagerBobotNilai",
            url:
              "sopingi/bobot_nilai/tampil/" + wSia.apiKey + "/" + Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jBobotNilai = this.data.order.length;
                $("#jBobotNilai").html(jBobotNilai);
              },
              onAfterFilter: function () {
                jBobotNilai = this.data.order.length;
                $("#jBobotNilai").html(jBobotNilai);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jBobotNilai = this.data.order.length;
                $("#jBobotNilai").html(jBobotNilai);
              },
            },
          },
          {
            view: "pager",
            id: "pagerBobotNilai",
            css: "pager",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jBobotNilai'> </span></b> Data",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  },
});

var formBobotNilai = {
  view: "form",
  id: "formBobotNilai",
  borderless: true,
  elements: [
    {
      view: "richselect",
      label: "Program Studi",
      name: "id_sms",
      id: "id_sms",
      placeholder: "Pilih Program Studi",
      required: true,
      invalidMessage: "Program Studi belum dipilih",
      options: "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
      inputWidth: 350,
    },
    {
      view: "text",
      label: "Nilai Huruf",
      name: "nilai_huruf",
      id: "nilai_huruf",
      required: true,
      inputWidth: 80,
    },
    {
      view: "text",
      label: "Nilai Indeks",
      name: "nilai_indeks",
      id: "nilai_indeks",
      required: true,
      validate: webix.rules.isNumber,
      inputWidth: 80,
    },
    {
      view: "text",
      label: "Bobot Nilai Angka Minimal",
      name: "bobot_nilai_min",
      id: "bobot_nilai_min",
      required: true,
      validate: webix.rules.isNumber,
      inputWidth: 80,
    },
    {
      view: "text",
      label: "Bobot Nilai Angka Maksimal",
      name: "bobot_nilai_maks",
      id: "bobot_nilai_maks",
      required: true,
      validate: webix.rules.isNumber,
      inputWidth: 80,
    },
    {
      view: "text",
      name: "kode_bobot_nilai",
      id: "kode_bobot_nilai",
      required: true,
      hidden: true,
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    { template: " ", borderless: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanBobotNilai",
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

/* HALAMAN AKUN */
var halamanAkun = new WebixView({
  config: {
    type: "line",
    rows: [
      {
        template: "Akun Admin",
        type: "header",
      },
      {
        type: "clean",
        borderless: true,
        cols: [
          { template: " " },
          {
            view: "form",
            id: "formAkun",
            scroll: false,
            width: 400,
            borderless: true,
            elements: [
              {
                view: "fieldset",
                label: "Ubah Akun Admin",
                body: {
                  rows: [
                    {
                      view: "text",
                      id: "pass",
                      name: "pass",
                      type: "password",
                      label: "Password Lama",
                      labelWidth: 180,
                      required: true,
                      invalidMessage: "Password lama belum diisi",
                    },
                    {
                      view: "text",
                      id: "passBaru1",
                      name: "passBaru1",
                      type: "password",
                      label: "Password Baru",
                      labelWidth: 180,
                      required: true,
                      invalidMessage: "Password baru belum diisi",
                    },
                    {
                      view: "text",
                      id: "passBaru",
                      name: "passBaru",
                      type: "password",
                      label: "Ulangi Password Baru",
                      labelWidth: 180,
                      required: true,
                      invalidMessage: "Ulangi Password baru belum diisi",
                    },
                    {
                      view: "text",
                      id: "aksi",
                      name: "aksi",
                      value: "ubahAkun",
                      hidden: true,
                    },
                    { template: " ", borderless: true, height: 20 },
                    {
                      margin: 5,
                      cols: [
                        { template: " ", borderless: true },
                        {
                          view: "button",
                          id: "simpanAkun",
                          label: "Ubah Akun",
                          type: "form",
                        },
                        { template: " ", borderless: true },
                      ],
                    },
                  ],
                },
              },
            ],
          },
          { template: " " },
        ],
      },
      {
        template: " ",
        borderless: true,
      },
    ],
  }, //config
});

/* HALAMAN DIREKTUR DAN WADIR */
var halamanDirWadir = new WebixView({
  config: {
    id: "halamanDirWadir",
    type: "space",
    cols: [
      {
        header: "Informasi",
        width: 200,
        body: {
          template:
            "Nama Direktur dan Wakil Direktur digunakan untuk nama pada tanda tangan dokumen KHS dan Transkip",
        },
      },
      {
        rows: [
          {
            template: "Data Direktur dan Wadir",
            type: "header",
          },
          {
            type: "clean",
            borderless: true,
            cols: [
              { template: " " },
              {
                view: "form",
                id: "formDirWadir",
                scroll: false,
                width: 500,
                borderless: true,
                url:
                  "sopingi/satuan_pendidikan/tampil/" +
                  wSia.apiKey +
                  "/" +
                  Math.random(),
                elements: [
                  {
                    view: "fieldset",
                    label: "Ubah Nama Diektur dan Wadir",
                    body: {
                      rows: [
                        {
                          view: "text",
                          id: "nama_dir",
                          name: "nama_dir",
                          label: "Nama Lengkap dan Gelar Direktur",
                          labelWidth: 250,
                          required: true,
                          invalidMessage: "Nama Direktur belum diisi",
                        },
                        {
                          view: "text",
                          id: "nama_wadir",
                          name: "nama_wadir",
                          label: "Nama Lengkap dan Gelar Wakil Direktur",
                          labelWidth: 250,
                          required: true,
                          invalidMessage: "Nama Direktur belum diisi",
                        },
                        {
                          view: "text",
                          id: "aksi",
                          name: "aksi",
                          value: "ubahDirWadir",
                          hidden: true,
                        },
                        { template: " ", borderless: true, height: 20 },
                        {
                          margin: 5,
                          cols: [
                            { template: " ", borderless: true },
                            {
                              view: "button",
                              id: "simpanDirWadir",
                              label: "Ubah Nama",
                              type: "form",
                            },
                            { template: " ", borderless: true },
                          ],
                        },
                      ],
                    },
                  },
                ],
                elementsConfig: {
                  labelPosition: "top",
                },
              },
              { template: " " },
            ],
          },
          {
            template: " ",
            borderless: true,
          },
        ],
      },
    ],
  }, //config
});

/* KULIAH MAHASISWA */
var halamanKuliahMahasiswa = new WebixView({
  config: {
    id: "halamanKuliahMahasiswa",
    type: "space",
    cols: [
      {
        header: "Informasi",
        width: 200,
        body: {
          template:
            "Kuliah Mahasiswa digunakan untuk update data mahasiswa bagi yang sudah KRS<br>Menu ini wajib dijalankan setiap akan mencetak KHS",
        },
      },
      {
        rows: [
          {
            template: "Kuliah mahasiswa",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "updateKuliahMahasiswaAktif",
                label: "Hitung Aktif",
                type: "iconButton",
                icon: "share",
                width: 150,
              },
              {
                view: "button",
                id: "updateKuliahMahasiswaNonAktif",
                label: "Hitung Non Aktif",
                type: "iconButton",
                icon: "share",
                width: 150,
              },
              {
                view: "button",
                id: "updateKuliahMahasiswaStatus",
                label: "Ubah Status",
                type: "iconButton",
                icon: "pencil",
                width: 130,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "refreshKuliahMahasiswa",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              {
                view: "button",
                id: "excelKuliahMahasiswa",
                label: "Unduh XLS",
                type: "iconButton",
                icon: "file-excel-o",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableKuliahMahasiswa",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nipd",
                header: ["NIM", { content: "textFilter" }],
                width: 100,
                sort: "string",
              },
              {
                id: "nm_pd",
                header: ["Nama", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "vnm_lemb",
                header: ["Program Studi", { content: "selectFilter" }],
                fillspace: true,
                sort: "string",
              },
              //{ id:"vid_smt", header:["Semester",{content:"textFilter"}],fillspace:true,sort:"string"},
              {
                id: "ips",
                header: ["IPS", { content: "textFilter" }],
                width: 50,
                sort: "number",
              },
              {
                id: "sks_smt",
                header: ["SKS SMT", { content: "textFilter" }],
                width: 50,
                sort: "number",
              },
              {
                id: "ipk",
                header: ["IPK", { content: "textFilter" }],
                width: 50,
                sort: "number",
              },
              {
                id: "sks_total",
                header: ["SKS Total", { content: "textFilter" }],
                width: 50,
                sort: "number",
              },
              {
                id: "id_stat_mhs",
                header: ["Status", { content: "selectFilter" }],
                width: 50,
                sort: "string",
              },
            ],
            hover: "tableHover",
            pager: "pagerKuliahMahasiswa",
            url:
              "sopingi/kuliah_mahasiswa/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                jKuliahMhs = this.data.order.length;
                $("#jKuliahMhs").html(jKuliahMhs);
              },
              onAfterFilter: function () {
                jKuliahMhs = this.data.order.length;
                $("#jKuliahMhs").html(jKuliahMhs);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                jKuliahMhs = this.data.order.length;
                $("#jKuliahMhs").html(jKuliahMhs);
              },
            },
          },
          {
            view: "pager",
            id: "pagerKuliahMahasiswa",
            css: "pager",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jKuliahMhs'> </span></b> Data",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  }, //config
});

/* MASTER SIAKAD Ruang */
var masterSiakadRuang = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Informasi Master Ruang",
        width: 200,
        body: {
          template:
            "Master Ruang digunakan untuk membuat nama Ruang yang akan digunakan di dalam ajar dosen.",
        },
      },
      {
        rows: [
          {
            template: "Master Ruang",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "tambahSiakadRuang",
                label: "Tambah",
                type: "iconButton",
                icon: "plus",
                width: 100,
              },
              {
                view: "button",
                id: "refreshSiakadRuang",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "ubahSiakadRuang",
                label: "Ubah",
                type: "iconButton",
                icon: "edit",
                width: 100,
              },
              {
                view: "button",
                id: "hapusSiakadRuang",
                label: "Hapus",
                type: "iconButton",
                icon: "remove",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableSiakadRuang",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "ruang",
                header: ["Nama Ruang", { content: "textFilter" }],
                sort: "string",
              },
            ],
            pager: "pagerSiakadRuang",
            url:
              "sopingi/siakad_ruang/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jSiakadRuang = this.data.order.length;
                $("#jSiakadRuang").html(jSiakadRuang);
              },
              onAfterFilter: function () {
                var jSiakadRuang = this.data.order.length;
                $("#jSiakadRuang").html(jSiakadRuang);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jSiakadRuang = this.data.order.length;
                $("#jSiakadRuang").html(jSiakadRuang);
              },
            },
          },
          {
            view: "pager",
            id: "pagerSiakadRuang",
            template:
              "{common.prev()} {common.pages()} {common.next()} Total: <b><span id='jSiakadRuang'>  </span></b> Ruang",
            size: 15,
            group: 5,
            animate: {
              direction: "left",
              type: "slide",
            },
          },
        ],
      },
    ],
  },
});

var formSiakadRuang = {
  view: "form",
  id: "formSiakadRuang",
  borderless: true,
  elements: [
    {
      view: "text",
      label: "Nama Ruang",
      name: "ruang",
      id: "ruang",
      required: true,
      invalidMessage: "Ruang belum diisi",
    },
    { view: "text", name: "aksi", id: "aksi", required: true, hidden: true },
    {
      cols: [
        { template: " ", borderless: true },
        {
          view: "button",
          id: "simpanSiakadRuang",
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

//SYNC FEEDER
/*WS TOKEN */
var wsToken = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Generate Token NeoFeeder",
        body: {
          template:
            "Digunakan untuk melakukan tes koneksi ke server NeoFeeder dan membuat token baru. Jika mendapatkan token maka koneksi ke server NeoFeeder berhasil",
        },
        width: 200,
      },
      {
        rows: [
          {
            template: "Cek Koneksi & Generate Token NeoFeeder",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsToken",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
            ],
          },
          {
            view: "datatable",
            id: "dataTableWsToken",
            select: true,
            columns: [
              {
                id: "token",
                header: "Token",
                adjust: "data",
                template: (o) => {
                  return "Berhasil terhubung dengan Token: " + o.token;
                },
              },
            ],
            url:
              "sopingi-feeder/token/generate/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div class='preload-wrapper'><div id='preloader_1'><span></span><span></span><span></span><span></span><span></span></div></div>",
                );
              },
              onAfterLoad: function () {
                this.hideOverlay();
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });
              },
            },
          },
        ],
      },
    ],
  },
});

/*WS MAHASISWA */
var wsMahasiswa = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Mahasiswa",
        width: 200,
        body: {
          template:
            "Sync mahasiswa digunakan untuk update biodata dari siakad ke NeoFeeder. Data yang tampil hanya yang melakukan update biodata setelah sync terakhir",
        },
      },
      {
        rows: [
          {
            template: "Data Mahasiswa yang melakukan Update Biodata",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsMahasiswa",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsMahasiswa",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsMahasiswa",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: ["Nama Mahasiswa", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nipd",
                header: ["NIM", { content: "serverFilter" }],
                sort: "string",
                width: 100,
              },
              {
                id: "id_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
              },
              {
                id: "kelas",
                header: ["Kelas", { content: "serverFilter" }],
                width: 60,
                sort: "string",
              },
              {
                id: "updated_at",
                header: "Update",
                width: 130,
                sort: "string",
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.id_mahasiswa == "") {
                    return "<i class='webix_icon fa-send-o'> baru Sync</i>";
                  } else if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> update Sync </i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else {
                    return "<button class='btnMhsError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              btnMhsError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                webix.alert(data.statusSync);
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/mahasiswa/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsMahasiswa").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jMahasiswa = this.data.order.length;
                $("#jWsMahasiswa").html(jMahasiswa);
              },
              onBeforeFilter: function () {
                $("#jWsMahasiswa").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jMahasiswa = this.data.order.length;
                $("#jWsMahasiswa").html(jMahasiswa);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jMahasiswa = this.data.order.length;
                $("#jWsMahasiswa").html(jMahasiswa);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsMahasiswa'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS PENDIDIKAN MAHASISWA */
var wsPendidikanMahasiswa = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Pendidikan Mahasiswa",
        width: 200,
        body: {
          template:
            "Sync pendidikan mahasiswa digunakan untuk insert riwayat pendidikan dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template: "Data Pendidikan Mahasiswa yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsPendidikanMahasiswa",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsPendidikanMahasiswa",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsPendidikanMahasiswa",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_pd",
                header: [
                  "Nama PendidikanMahasiswa",
                  { content: "serverFilter" },
                ],
                width: 300,
                sort: "string",
              },
              {
                id: "nipd",
                header: ["NIM", { content: "serverFilter" }],
                sort: "string",
                width: 100,
              },
              {
                id: "xid_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
              },
              {
                id: "kelas",
                header: ["Kelas", { content: "serverFilter" }],
                width: 60,
                sort: "string",
              },
              {
                id: "nm_jns_daftar",
                header: ["Jenis Daftar", { content: "serverFilter" }],
                width: 100,
                sort: "string",
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.id_reg_pd == "") {
                    return "<i class='webix_icon fa-send-o'> baru Sync</i>";
                  } else if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> update Sync</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else {
                    return "<button class='btnMhsError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              btnMhsError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                webix.alert(data.statusSync);
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/mahasiswa_pt/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsPendidikanMahasiswa").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jPendidikanMahasiswa = this.data.order.length;
                $("#jWsPendidikanMahasiswa").html(jPendidikanMahasiswa);
              },
              onBeforeFilter: function () {
                $("#jWsPendidikanMahasiswa").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jPendidikanMahasiswa = this.data.order.length;
                $("#jWsPendidikanMahasiswa").html(jPendidikanMahasiswa);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jPendidikanMahasiswa = this.data.order.length;
                $("#jWsPendidikanMahasiswa").html(jPendidikanMahasiswa);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsPendidikanMahasiswa'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS MATA KULIAH */
var wsMataKuliah = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Mata Kuliah",
        width: 200,
        body: {
          template:
            "Sync mata kuliah digunakan untuk insert data mata kuliah dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template: "Data Mata Kuliah yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsMataKuliah",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsMataKuliah",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsMataKuliah",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "kode_mk",
                header: ["Kode MK", { content: "serverFilter" }],
                sort: "string",
              },
              {
                id: "nm_mk",
                header: ["Mata Kuliah", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              { id: "sks_mk", header: "SKS", sort: "int", width: 50 },
              {
                id: "id_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];
                if (data.feeder_result) {
                  var feeder_result = data.feeder_result;
                  if (feeder_result.error_code == 400) {
                    var tombol = ["Nanti aja", "Update id_mk dari NeoFeeder"];
                  }
                }
                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) {
                    if (result == 1) {
                      webix.confirm({
                        title: "Konfirmasi",
                        ok: "Update id_mk",
                        cancel: "Tidak",
                        text:
                          "Yakin akan update id_mk SIAKAD dari FEEDER ? " +
                          data.kode_mk +
                          " " +
                          data.nm_mk,
                        callback: function (jwb) {
                          if (jwb) {
                            prosesUpdateIdMkMataKuliah();
                          }
                        },
                      });
                    }
                  },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/mata_kuliah/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsMataKuliah").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jMataKuliah = this.data.order.length;
                $("#jWsMataKuliah").html(jMataKuliah);
              },
              onBeforeFilter: function () {
                $("#jWsMataKuliah").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jMataKuliah = this.data.order.length;
                $("#jWsMataKuliah").html(jMataKuliah);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jMataKuliah = this.data.order.length;
                $("#jWsMataKuliah").html(jMataKuliah);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsMataKuliah'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS KURIKULUM */
var wsKurikulum = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Kurikulum",
        width: 200,
        body: {
          template:
            "Sync kurikulum digunakan untuk insert data kurikulum dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template: "Data Kurikulum yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsKurikulum",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsKurikulum",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsKurikulum",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_kurikulum_sp",
                header: ["Nama Kurikulum", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "id_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              { id: "id_smt_berlaku", header: "Mulai Berlaku", width: 120 },
              {
                id: "jml_sks_lulus",
                header: [
                  {
                    text: "Aturan SKS",
                    colspan: 3,
                    css: { "text-align": "center" },
                  },
                  "Lulus",
                ],
                width: 60,
              },
              { id: "jml_sks_wajib", header: ["", "Wajib"], width: 60 },
              { id: "jml_sks_pilihan", header: ["", "Pilihan"], width: 60 },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                webix.alert(data.statusSync);
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/kurikulum/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsKurikulum").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jKurikulum = this.data.order.length;
                $("#jWsKurikulum").html(jKurikulum);
              },
              onBeforeFilter: function () {
                $("#jWsKurikulum").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jKurikulum = this.data.order.length;
                $("#jWsKurikulum").html(jKurikulum);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jKurikulum = this.data.order.length;
                $("#jWsKurikulum").html(jKurikulum);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsKurikulum'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS MATA KULIAH KURIKULUM */
var wsMataKuliahKurikulum = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Mata Kuliah Kurikulum",
        width: 200,
        body: {
          template:
            "Sync Mata Kuliah Kurikulum digunakan untuk insert data Mata Kuliah Kurikulum dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template: "Data Mata Kuliah Kurikulum yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsMataKuliahKurikulum",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsMataKuliahKurikulum",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsMataKuliahKurikulum",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nm_kurikulum_sp",
                header: ["Nama Kurikulum", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "id_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              {
                id: "mk",
                header: ["Nama Mata Kuliah", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "sms_mk",
                header: [
                  { text: "SKS", colspan: 5, css: { "text-align": "center" } },
                  "MK",
                ],
                width: 60,
              },
              { id: "sks_tm", header: ["", "TM"], width: 60 },
              { id: "sks_prak", header: ["", "Praktik"], width: 60 },
              { id: "sks_prak_lap", header: ["", "PrakLap"], width: 60 },
              { id: "sks_sim", header: ["", "Simulasi"], width: 60 },
              { id: "wajib", header: "Wajib", width: 60 },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];
                if (data.feeder_result) {
                  var feeder_result = data.feeder_result;
                  if (feeder_result.error_code == 630) {
                    var tombol = ["Nanti aja", "Update id_mk_kurikulum"];
                  }
                }
                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) {
                    if (result == 1) {
                      webix.confirm({
                        title: "Konfirmasi",
                        ok: "Update",
                        cancel: "Tidak",
                        text:
                          "Yakin akan update id_mk_kurikulum ? " +
                          data.mk +
                          " - " +
                          data.nm_kurikulum_sp,
                        callback: function (jwb) {
                          if (jwb) {
                            prosesUpdateIdMataKuliahKurikulum();
                          }
                        },
                      });
                    }
                  },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/mata_kuliah_kurikulum/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsMataKuliahKurikulum").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jMataKuliahKurikulum = this.data.order.length;
                $("#jWsMataKuliahKurikulum").html(jMataKuliahKurikulum);
              },
              onBeforeFilter: function () {
                $("#jWsMataKuliahKurikulum").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jMataKuliahKurikulum = this.data.order.length;
                $("#jWsMataKuliahKurikulum").html(jMataKuliahKurikulum);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jMataKuliahKurikulum = this.data.order.length;
                $("#jWsMataKuliahKurikulum").html(jMataKuliahKurikulum);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsMataKuliahKurikulum'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS KELAS PERKULIAHAN */
var wsKelasPerkuliahan = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Kelas Perkuliahan",
        width: 200,
        body: {
          template:
            "Sync Kelas Perkuliahan digunakan untuk insert data Kelas Perkuliahan dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron <b>HANYA DIATAS 20201</b>",
        },
      },
      {
        rows: [
          {
            template: "Data Kelas Perkuliahan yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsKelasPerkuliahan",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsKelasPerkuliahan",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsKelasPerkuliahan",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "xid_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              { id: "id_smt", header: "Semester", sort: "string", width: 70 },
              { id: "kode_mk", header: "Kode MK", sort: "string", width: 80 },
              {
                id: "nm_mk",
                header: ["Mata Kuliah", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nm_kls",
                header: ["Kelas", { content: "serverFilter" }],
                sort: "string",
                width: 50,
              },
              { id: "sks_mk", header: "SKS", sort: "int", width: 40 },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];
                if (data.feeder_result) {
                  var feeder_result = data.feeder_result;
                  if (feeder_result.error_code == 700) {
                    var tombol = ["Nanti aja", "Update id_kls"];
                  }
                }
                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) {
                    if (result == 1) {
                      webix.confirm({
                        title: "Konfirmasi",
                        ok: "Update",
                        cancel: "Tidak",
                        text:
                          "Yakin akan update id_kls ? " +
                          data.nm_mk +
                          " - " +
                          data.nm_kls,
                        callback: function (jwb) {
                          if (jwb) {
                            prosesUpdateIdKelasPerkuliahan();
                          }
                        },
                      });
                    }
                  },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/kelas_perkuliahan/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsKelasPerkuliahan").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jKelasPerkuliahan = this.data.order.length;
                $("#jWsKelasPerkuliahan").html(jKelasPerkuliahan);
              },
              onBeforeFilter: function () {
                $("#jWsKelasPerkuliahan").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jKelasPerkuliahan = this.data.order.length;
                $("#jWsKelasPerkuliahan").html(jKelasPerkuliahan);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jKelasPerkuliahan = this.data.order.length;
                $("#jWsKelasPerkuliahan").html(jKelasPerkuliahan);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsKelasPerkuliahan'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS AJAR DOSEN */
var wsAjarDosen = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Ajar Dosen",
        width: 200,
        body: {
          template:
            "Sync Ajar Dosen digunakan untuk insert data Ajar Dosen dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron <b>HANYA DIATAS 20201</b>",
        },
      },
      {
        rows: [
          {
            template: "Data Kelas Perkuliahan yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsAjarDosen",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsAjarDosen",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsAjarDosen",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "xid_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              {
                id: "mk",
                header: ["Mata Kuliah", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "nm_kls",
                header: ["Kelas", { content: "serverFilter" }],
                sort: "string",
                width: 50,
              },
              { id: "sks_subst_tot", header: "SKS", sort: "int", width: 40 },
              {
                id: "nm_ptk",
                header: ["Dosen", { content: "serverFilter" }],
                sort: "string",
                fillspace: true,
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 130,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];
                if (data.feeder_result) {
                  var feeder_result = data.feeder_result;
                  if (feeder_result.error_code == 920) {
                    var tombol = ["Nanti aja", "Update id_ajar"];
                  }
                }
                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) {
                    if (result == 1) {
                      webix.confirm({
                        title: "Konfirmasi",
                        ok: "Update",
                        cancel: "Tidak",
                        text:
                          "Yakin akan update id_ajar ? " +
                          data.nm_ptk +
                          " - " +
                          data.nm_mk +
                          " - " +
                          data.nm_kls,
                        callback: function (jwb) {
                          if (jwb) {
                            prosesUpdateIdAjarDosen();
                          }
                        },
                      });
                    }
                  },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/ajar_dosen/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsAjarDosen").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jAjarDosen = this.data.order.length;
                $("#jWsAjarDosen").html(jAjarDosen);
              },
              onBeforeFilter: function () {
                $("#jWsAjarDosen").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jAjarDosen = this.data.order.length;
                $("#jWsAjarDosen").html(jAjarDosen);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jAjarDosen = this.data.order.length;
                $("#jWsAjarDosen").html(jAjarDosen);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsAjarDosen'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS KRS-NILAI */
var wsNilai = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync KRS & Nilai",
        width: 200,
        body: {
          template:
            "Sync KRS & Nilai digunakan untuk insert/update data KRS & Nilai dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron <b>HANYA DIATAS 20201</b>",
        },
      },
      {
        rows: [
          {
            template: "Data KRS & Nilai yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsNilai",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsNilai",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsNilai",
            fixedRowHeight: false,
            rowLineHeight: 15,
            rowHeight: 45,
            datafetch: 500,
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "id_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              {
                id: "kelas",
                header: ["Mata Kuliah", { content: "serverFilter" }],
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.kode_mk + "<br>" + o.nm_mk + "<br>" + o.nm_kls;
                },
              },
              { id: "sks_mk", header: "SKS", sort: "int", width: 40 },
              {
                id: "mahasiswa",
                header: ["Mahasiswa", { content: "serverFilter" }],
                sort: "string",
                fillspace: true,
                template: (o) => {
                  return o.nm_pd + "<br>" + o.nipd;
                },
              },
              {
                id: "nilai_angka",
                header: "Nilai",
                sort: "string",
                width: 100,
                template: (o) => {
                  return o.nilai_angka + " - " + o.nilai_huruf;
                },
              },
              {
                id: "updated_at",
                header: "Update",
                width: 130,
                sort: "string",
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 140,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];

                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) { },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/nilai/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsNilai").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                this.adjustRowHeight("kelas", true);
                var jNilai = this.data.order.length;
                $("#jWsNilai").html(jNilai);
              },
              onBeforeFilter: function () {
                $("#jWsNilai").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jNilai = this.data.order.length;
                $("#jWsNilai").html(jNilai);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jNilai = this.data.order.length;
                $("#jWsNilai").html(jNilai);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsNilai'>  </span></b>. Untuk proses sync dibatasi 500 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS KULIAH-MAHASISWA */
var wsKuliahMahasiswa = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Kuliah Mahasiswa",
        width: 200,
        body: {
          template:
            "Sync Kuliah Mahasiswa digunakan untuk insert/update data Kuliah Mahasiswa dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron <b>HANYA DIATAS 20201</b>",
        },
      },
      {
        rows: [
          {
            template: "Data Kuliah Mahasiswa yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsKuliahMahasiswa",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsKuliahMahasiswa",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsKuliahMahasiswa",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nipd",
                header: ["NIM", { content: "serverFilter" }],
                sort: "string",
                width: 120,
              },
              {
                id: "nm_pd",
                header: ["Nama Mahasiswa", { content: "serverFilter" }],
                width: 280,
                sort: "string",
              },
              {
                id: "xid_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              {
                id: "id_stat_mhs",
                header: [
                  "Status",
                  {
                    content: "serverSelectFilter",
                    options: [
                      { id: "", value: "" },
                      { id: "A", value: "AKTIF" },
                      { id: "C", value: "CUTI" },
                      { id: "D", value: "DROP-OUT/PUTUS STUDI" },
                      { id: "K", value: "KELUAR" },
                      { id: "G", value: "SEDANG DOUBLE DEGREE" },
                      { id: "N", value: "NON-AKTIF" },
                    ],
                  },
                ],
                sort: "string",
                width: 80,
              },
              {
                id: "ips",
                header: ["IPS", { content: "serverFilter" }],
                sort: "number",
                width: 60,
              },
              {
                id: "ipk",
                header: ["IPK", { content: "serverFilter" }],
                sort: "number",
                width: 60,
              },
              {
                id: "sks_smt",
                header: ["SKSS", { content: "serverFilter" }],
                sort: "number",
                width: 70,
              },
              {
                id: "sks_total",
                header: ["SKST", { content: "serverFilter" }],
                sort: "number",
                width: 70,
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 140,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];

                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) { },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/kuliah_mahasiswa/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsKuliahMahasiswa").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jKuliahMahasiswa = this.data.order.length;
                $("#jWsKuliahMahasiswa").html(jKuliahMahasiswa);
              },
              onBeforeFilter: function () {
                $("#jWsKuliahMahasiswa").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jKuliahMahasiswa = this.data.order.length;
                $("#jWsKuliahMahasiswa").html(jKuliahMahasiswa);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jKuliahMahasiswa = this.data.order.length;
                $("#jWsKuliahMahasiswa").html(jKuliahMahasiswa);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsKuliahMahasiswa'>  </span></b>. Untuk proses sync dibatasi 500 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS MAHASISWA KELUAR */
var wsMahasiswaKeluar = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Mahasiswa Keluar",
        width: 200,
        body: {
          template:
            "Sync Mahasiswa Keluar digunakan untuk insert data Mahasiswa Keluar dari siakad ke feeder. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template: "Data Mahasiswa Keluar yang belum di-syncron",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsMahasiswaKeluar",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsMahasiswaKeluar",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsMahasiswaKeluar",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nipd",
                header: ["NIM", { content: "textFilter" }],
                sort: "string",
                width: 100,
              },
              {
                id: "nm_pd",
                header: ["Nama Mahasiswa", { content: "textFilter" }],
                fillspace: true,
                sort: "string",
              },
              {
                id: "jk",
                header: ["L/P", { content: "textFilter" }],
                sort: "string",
                width: 50,
              },
              {
                id: "xid_sms",
                header: ["Program Studi", { content: "serverSelectFilter" }],
                options:
                  "sopingi/sms/pilih/" + wSia.apiKey + "/" + Math.random(),
                fillspace: true,
                sort: "string",
                template: (o) => {
                  return o.nm_jenj_didik + "-" + o.nm_lemb;
                },
              },
              {
                id: "ket_keluar",
                header: ["Jenis Keluar", { content: "textFilter" }],
                width: 150,
                sort: "string",
              },
              {
                id: "angkatan",
                header: ["Angkatan", { content: "textFilter" }],
                width: 100,
                sort: "string",
              },
              {
                id: "tgl_keluar",
                header: ["Tgl Keluar", { content: "textFilter" }],
                format: webix.Date.dateToStr("%d-%m-%Y"),
                sort: "string",
                width: 100,
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Baru</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 140,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];

                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) { },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/mahasiswa_keluar/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsMahasiswaKeluar").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsMahasiswaKeluar").html(jMahasiswaKeluar);
              },
              onBeforeFilter: function () {
                $("#jWsMahasiswaKeluar").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsMahasiswaKeluar").html(jMahasiswaKeluar);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsMahasiswaKeluar").html(jMahasiswaKeluar);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsMahasiswaKeluar'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS DOSEN */
var wsDosen = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync Dosen",
        width: 200,
        body: {
          template:
            "Sync Dosen digunakan untuk menarik ID_PTK dari Neofeeder ke Siakad. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template: "Data Dosen di SIAKAD yang belum memiliki ID_PTK",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsDosen",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsDosen",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsDosen",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nidn",
                header: ["NIDN", { content: "serverFilter" }],
                width: 120,
              },
              {
                id: "nm_ptk",
                header: ["Nama Dosen", { content: "serverFilter" }],
                width: 450,
              },
              {
                id: "jk",
                header: ["L/P", { content: "serverFilter" }],
                width: 50,
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Butuh ID_PTK</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 140,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];

                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) { },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/dosen/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsDosen").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsDosen").html(jMahasiswaKeluar);
              },
              onBeforeFilter: function () {
                $("#jWsDosen").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsDosen").html(jMahasiswaKeluar);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsDosen").html(jMahasiswaKeluar);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsDosen'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});

/*WS DOSEN PT*/
var wsDosenPt = new WebixView({
  config: {
    type: "space",
    cols: [
      {
        header: "Sync DosenPt",
        width: 200,
        body: {
          template:
            "Sync Penugasan Dosen digunakan untuk menarik ID_REG_PTK dari Neofeeder ke Siakad setiap Tahun Akademik. Data yang tampil hanya yang belum di-syncron",
        },
      },
      {
        rows: [
          {
            template:
              "Data Penugasan Dosen di SIAKAD yang belum memiliki ID_REG_PTK",
            type: "header",
          },
          {
            view: "toolbar",
            paddingY: 2,
            cols: [
              {
                view: "button",
                id: "refreshWsDosenPt",
                label: "Refresh",
                type: "iconButton",
                icon: "refresh",
                width: 100,
              },
              { template: "", borderless: true },
              {
                view: "button",
                id: "syncWsDosenPt",
                label: "Sync",
                type: "iconButton",
                icon: "send",
                width: 100,
              },
            ],
          },
          {
            view: "datatable",
            select: true,
            id: "dataTableWsDosenPt",
            columns: [
              { id: "index", header: "No", width: 40 },
              {
                id: "nidn",
                header: ["NIDN", { content: "serverFilter" }],
                width: 110,
              },
              {
                id: "nm_ptk",
                header: ["Nama DosenPt", { content: "serverFilter" }],
                width: 430,
              },
              {
                id: "nm_prodi",
                header: ["Program Studi", { content: "serverFilter" }],
                width: 280,
              },
              {
                id: "jk",
                header: ["L/P", { content: "serverFilter" }],
                width: 50,
              },
              {
                id: "",
                header: "Status",
                template: (o) => {
                  if (o.statusSync == 0) {
                    return "<i class='webix_icon fa-send-o'> Butuh ID_REG_PTK</i>";
                  } else if (o.statusSync == 1) {
                    return "<i class='webix_icon fa-check'> Berhasil</i>";
                  } else if (o.statusSync == 2) {
                    return "<i class='webix_icon fa-check'> Update</i>";
                  } else {
                    return "<button class='syncError'><i class='webix_icon fa-exclamation'></i> Error</button>";
                  }
                },
                width: 200,
              },
            ],
            onClick: {
              syncError: function (event, id, target) {
                this.select(id);
                var data = this.getItem(id);
                var tombol = ["Ok"];

                webix.modalbox({
                  title: "Detail Error Sinkron NeoFeeder",
                  buttons: tombol,
                  text: data.pesan,
                  width: 300,
                  callback: function (result) { },
                });
              },
            },
            hover: "tableHover",
            url:
              "sopingi-feeder/dosen_pt/tampil/" +
              wSia.apiKey +
              "/" +
              Math.random(),
            on: {
              onBeforeLoad: function () {
                this.showOverlay(
                  "<div style='background:#FFFF00; padding:5px;'>Sedang memuat...</div>",
                );
                $("#jWsDosenPt").html("Sedang memuat...");
              },
              onAfterLoad: function () {
                this.hideOverlay();
                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsDosenPt").html(jMahasiswaKeluar);
              },
              onBeforeFilter: function () {
                $("#jWsDosenPt").html("Sedang memuat...");
              },
              onAfterFilter: function () {
                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsDosenPt").html(jMahasiswaKeluar);
              },
              "data->onStoreUpdated": function () {
                this.data.each(function (obj, i) {
                  obj.index = i + 1;
                });

                var jMahasiswaKeluar = this.data.order.length;
                $("#jWsDosenPt").html(jMahasiswaKeluar);
              },
            },
          },
          {
            template:
              "Total Ter-load: <b><span id='jWsDosenPt'>  </span></b>. Untuk proses sync dibatasi 50 data per transaksi",
            height: 30,
            borderless: true,
          },
        ],
      },
    ],
  },
});
