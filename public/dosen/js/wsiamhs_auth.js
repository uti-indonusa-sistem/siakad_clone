cekLogin();
function cekLogin() {
	webix.ajax().get("cekLogin", {}, {
		success: function (response, data, xhr) {
			//proses_hide();
			hasil = JSON.parse(response);
			if (hasil.masihLogin) {
				webix.message(hasil.pesan);
				webix.storage.session.put('wSiaMhs', { domain: hasil.domain, nidn: hasil.nidn, xid_ptk: hasil.xid_ptk, nm_ptk: hasil.nm_ptk, apiKey: hasil.apiKey, nidnMd5: hasil.nidnMd5 })
				var targetHash = window.location.hash;
				window.location = hasil.domain + "/dosen/main" + targetHash;
			} else {
				webix.alert({
					title: "Info Login",
					text: hasil.pesan,
					type: "alert-error"
				})
			}
		},
		error: function (response, data, xhr) {
			//proses_hide();
			webix.alert({
				title: "Kesalahan",
				text: "Gagal terkoneksi dengan server..!",
				type: "alert-error"
			})
		}
	});
}