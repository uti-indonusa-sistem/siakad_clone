webix.proxy.idata = {
	$proxy:true,
	load:function(view, callback){
		var url = this.source;
		url += (url.indexOf("?") == -1 ) ? "?": "&";

		var details = arguments[2];
		var count = details?details.count:view.config.datafetch || 0;
		var start = details?details.start:0;

		url += "count="+count;
		url += start?"&start="+start:"";
		
		if (view.getState){
		    var state = view.getState(); 
		        var params = [];
		    if (state){
		         if (state.sort)
		            params.push("sort["+state.sort.id+"]="+
		              encodeURIComponent(state.sort.dir));
		         if (state.filter)
		            for (var key in state.filter)
		               params.push("filter["+key+"]="+
		                encodeURIComponent(state.filter[key]));
		         if(params.length){
		           url = url+((url.indexOf("?")==-1)?"?":"&");
		           url += params.join("&");
		         }
		     }
		}
		
		callback.push({ success:this._checkLoadNext});
		webix.ajax(url, callback, view);

		view.$ready.push(this._attachHandlers);
	}, 
	_attachHandlers:function(){
		var proxy  = this.config.url;
		
		if(this.config.columns)
			this.attachEvent("onScrollY", webix.bind(proxy._loadNext, this));
		else
			this.attachEvent("onAfterScroll", webix.bind(proxy._loadNext, this));
		
		
		this.attachEvent("onAfterFilter", function(){ 
			
			this.clearAll();
			proxy._dontLoadNext = false;
			this.load(proxy);	
			
		});
		    
	},
	_checkLoadNext:function(text, data, loader){
		if(!data.json().length)
			this.data.url._dontLoadNext = true;
	},
	_loadNext:function(){
		var proxy  = this.config.url;
		var contentScroll =  this.getScrollState().y+this.$view.clientHeight;
		var last = this.getItemNode(this.getLastId());
		//console.log(proxy._dontLoadNext);
		//console.log(this.getScrollState().y+"  "+this.$view.clientHeight+"  "+contentScroll+" "+last.offsetTop);
		if(last && contentScroll>last.offsetTop && !proxy._dontLoadNext) {
			//this.loadNext(this.config.datafetch, this.count()+1);
			this.loadNext(this.config.datafetch, this.count());
		}
	}
};