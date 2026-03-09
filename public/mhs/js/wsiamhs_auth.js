cekLogin();
function cekLogin() {
	webix.ajax().get("cekLogin", {}, {
		success: function (response, data, xhr) {
			//proses_hide();
			hasil = JSON.parse(response);
			if (hasil.masihLogin) {
				webix.message(hasil.pesan);
				webix.storage.session.put('wSiaMhs', { domain: hasil.domain, nipd: hasil.nipd, xid_reg_pd: hasil.xid_reg_pd, nm_pd: hasil.nm_pd, apiKey: hasil.apiKey, nipdMd5: hasil.nipdMd5, mulai_smt: hasil.mulai_smt })
				var targetHash = window.location.hash;
				window.location = hasil.domain + "/mhs/main" + targetHash;
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