cekLogin();
function cekLogin(){
	webix.ajax().get("cekLogin", {}, {
		success: function(response, data, xhr){
				//proses_hide();
				hasil=JSON.parse(response);
				if (hasil.masihLogin) {
					webix.message(hasil.pesan);
					webix.storage.session.put('wSia', { domain: hasil.domain, ta: hasil.ta, apiKey:hasil.apiKey });
					window.location=hasil.domain+"/baak/main";
				} else {
					webix.alert({
					    title: "Info Login",
					    text: hasil.pesan,
					    type:"alert-error"
					})
				}
		},
		error:function(response, data, xhr){
			//proses_hide();
			webix.alert({
			    title: "Kesalahan",
			    text: "Gagal terkoneksi dengan server..!",
			    type:"alert-error"
			})
		}
	});  
}