var FactFinderAjax = {
  getTransport: function() {
	return new jXHR();
  },

  activeRequestCount: 0
};

FactFinderAjax.Response = Class.create(Ajax.Response, {
	
  initialize: function(request){
    this.request = request;
    var transport  = this.transport  = request.transport,
        readyState = this.readyState = transport.readyState;

    if((readyState > 2 && !Prototype.Browser.IE) || readyState == 4) {
      this.status       = this.getStatus();
      this.statusText   = this.getStatusText();
      this.responseText = String.interpret(transport.responseText);
      this.headerJSON   = this._getHeaderJSON();
    }

    if(readyState == 4) {
      var xml = transport.responseXML;
      this.responseXML  = Object.isUndefined(xml) ? null : xml;
      this.responseJSON = this._getResponseJSON();
    }
  }
});

FactFinderAjax.Request = Class.create(Ajax.Request, {
  _complete: false,

  initialize: function(url, options) {
    this.options = {
      method:       'get',
      asynchronous: true,
      contentType:  'application/x-www-form-urlencoded',
      encoding:     'UTF-8',
      parameters:   '',
      evalJSON:     true,
      evalJS:       true
    };
    Object.extend(this.options, options || { });

    this.options.method = this.options.method.toLowerCase();

    if (Object.isString(this.options.parameters))
      this.options.parameters = this.options.parameters.toQueryParams();
    else if (Object.isHash(this.options.parameters))
      this.options.parameters = this.options.parameters.toObject();
	
    this.transport = FactFinderAjax.getTransport();
    this.request(url);
  },

  request: function(url) {
    this.url = url;
    this.method = this.options.method;
    var params = Object.clone(this.options.parameters);

    if (!['get', 'post'].include(this.method)) {
      // simulate other verbs over post
      params['_method'] = this.method;
      this.method = 'post';
    }

    this.parameters = params;

    if (params = Object.toQueryString(params)) {
      // when GET, append parameters to URL
      if (this.method == 'get')
        this.url += (this.url.include('?') ? '&' : '?') + params + '&jquery_callback=?&callback=?';
      else if (/Konqueror|Safari|KHTML/.test(navigator.userAgent))
        params += '&_=';
    }

    try {

      var response = new FactFinderAjax.Response(this);
      if (this.options.onCreate) this.options.onCreate(response);
      Ajax.Responders.dispatch('onCreate', this, response);

      this.transport.open(this.method.toUpperCase(), this.url,
        this.options.asynchronous);

      if (this.options.asynchronous) this.respondToReadyState.bind(this).defer(1);

      this.transport.onreadystatechange = this.onStateChange.bind(this);
      this.setRequestHeaders();

      this.body = this.method == 'post' ? (this.options.postBody || params) : null;
      this.transport.send(this.body);

      /* Force Firefox to handle ready state 4 for synchronous requests */
      if (!this.options.asynchronous && this.transport.overrideMimeType)
        this.onStateChange();

    }
    catch (e) {
      this.dispatchException(e);
    }
  },

  isSameOrigin: function() {
    var m = this.url.match(/^\s*https?:\/\/[^\/]*/);
    return !m || (m[0] == '#{protocol}//#{domain}#{port}'.interpolate({
      protocol: location.protocol,
      domain: document.domain,
      port: location.port ? ':' + location.port : ''
    }));
  }
});

var FactFinderAutocompleter = Class.create(Ajax.Autocompleter, {
	caller: null,
	rq: null,
  getUpdatedChoices: function() {
    this.startIndicator();

    var entry = encodeURIComponent(this.options.paramName) + '=' +
      encodeURIComponent(this.getToken());

    this.options.parameters = this.options.callback ?
      this.options.callback(this.element, entry) : entry;

    if(this.options.defaultParams)
      this.options.parameters += '&' + this.options.defaultParams;

    this.rq = new FactFinderAjax.Request(this.url, this.options);
	this.rq.transport.onreadystatechange = this.caller._loadData.bind(this.caller);
  }
})

var FactFinderSuggest = Class.create(Varien.searchForm, {
	request: null,
	
    initAutocomplete : function(url, destinationElement){
		this.request = new FactFinderAutocompleter(
            this.field,
            destinationElement,
            url,
            {
				parameters: 'format=JSONP',
                paramName: 'query',
                method: 'get',
                minChars: 2,
                updateElement: this._selectAutocompleteItem.bind(this),
                onShow : function(element, update) {
                    if(!update.style.position || update.style.position=='absolute') {
                        update.style.position = 'absolute';
                        Position.clone(element, update, {
                            setHeight: false,
                            offsetTop: element.offsetHeight
                        });
                    }
                    Effect.Appear(update,{duration:0});
                }
            }
        );
		this.request.caller = this;
    },
	
	_loadData: function(data) {
		var content = '<ul>';
		content += '<li style="display: none" class="selected"></li>';
		data.each(function(item) {
			var hitCount = item.hitCount == '0' ? '' : item.hitCount;
			content += '<li title="'+item.query+'"><span class="amount">' + hitCount + '</span>' + item.query + '</li>';
		});		
		content += '</ul>';
		
		this.request.updateChoices(content);
	},

    _selectAutocompleteItem : function(element){		
        if(element.title){		
			this.form.insert('<input type="hidden" name="queryFromSuggest" value="true" />');
			this.form.insert('<input type="hidden" name="userInput" value="'+this.field.value+'" />');
			
            this.field.value = element.title;
        }
        this.form.submit();
    }
});
