/**
* FACTFinder_Suggest
*
* @category Mage
* @package FACTFinder_Suggest
* @author Flagbit Magento Team <magento@flagbit.de>
* @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
* @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @link http://www.flagbit.de
*
*/

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
    },

    updateChoices: function(choices) {
        if(!this.changed && this.hasFocus) {
            this.update.innerHTML = choices;
            Element.cleanWhitespace(this.update);
            Element.cleanWhitespace(this.update.down());

            if(this.update.firstChild && this.update.select('.selectable-item')) {
                this.entryCount =
                    this.update.select('.selectable-item').length;
                for (var i = 0; i < this.entryCount; i++) {
                    var entry = this.getEntry(i);
                    entry.autocompleteIndex = i;
                    this.addObservers(entry);
                }
            } else {
                this.entryCount = 0;
            }

            this.stopIndicator();
            this.index = 0;

            if(this.entryCount==1 && this.options.autoSelect) {
                this.selectEntry();
                this.hide();
            } else {
                this.render();
            }
        }
    },

    getEntry: function(index) {
        return this.update.select('.selectable-item')[index];
    }
});

var FactFinderSuggest = Class.create(Varien.searchForm, {
    initialize : function($super, form, field, emptyText, i18n, defaultChannel) {
        $super(form, field, emptyText);
        this.i18n = i18n;
        this.defaultChannel = defaultChannel;
    },

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
                // uncomment for debugging
                //, onHide : function(element, update) {}
            }
        );
        this.request.caller = this;
    },

    _loadData: function(data) {
        this.request.updateChoices(this.loadDataCallback(data));
    },

    _selectAutocompleteItem : function(element){
        if(element.attributes.rel) {
            document.location.href = element.attributes.rel.nodeValue;
        } else if(element.title) {
            this.form.insert('<input type="hidden" name="queryFromSuggest" value="true" />');
            this.form.insert('<input type="hidden" name="userInput" value="'+this.field.value+'" />');

            this.field.value = element.title;

            this.form.submit();
        }
    },

    loadDataCallback: function (data) {
        data = data.suggestions;

        // Try to get channel from search request, if no channel was found
        data.each(function (item) {
            if (!item.channel) {
                var params = item.searchParams.toQueryParams();
                if (params.channel) {
                    item.channel = params.channel;
                }
            }
        });


        var content = '<ul>';
        content += '<li style="display: none" class="selected selectable-item"></li>';
        var currentChannel = '';
        var currentType = '';
        if (data.length) {
            if (data[0].channel != this.defaultChannel) {
                content += '<li class="delimiter">' + this.translate('Channel: ' + data[0].channel) + '</li>';
            }
            currentChannel = data[0].channel;
        }

        data.each(function(item) {
            if (item.channel != currentChannel) {
                content += '<li class="delimiter">' + this.translate('Channel: ' + item.channel) + '</li>';
                currentChannel = item.channel;
            }

            if (item.type != currentType) {
                content += '<li class="delimiter">' + this.translate(item.type) + '</li>';
                currentType = item.type;
            }

            var temp = '';
            temp += '<li title="' + item.name + '" class="selectable-item ' + item.type + '"';
            temp += ' rel="' + this.getItemUrl(item)+ '"';
            temp += '>';

            temp += '<span class="amount">' + (item.hitCount == 0 ? '' : item.hitCount) + '</span>';
            if (item.image) {
                temp += '<img src="' + item.image + '" title="' + item.name + '" class="thumbnail"/>';
            }
            temp += item.name;
            temp += '</li>';
            content += temp;
        }.bind(this));

        content += '</ul>';

        return content;
    },

    translate: function (string) {
        // Internationalization lookup:
        // Add a new anonymous object for every string you want to internationalize (with the property being the string).
        // These objects consist of one string for each locale, where the property is the locale code.
        if (this.i18n[string] === undefined) {
            return string;
        } else {
            return this.i18n[string];
        }
    },

    getItemUrl: function (item) {
        if (item.attributes.deeplink) {
            return item.attributes.deeplink;
        }

        var qPos = item.searchParams.indexOf('?');

        var url = this.form.action;
        if (url.indexOf('?') > 0) {
            url += '&' + item.searchParams.substring(qPos + 1);
        } else {
            url += item.searchParams.substring(qPos);
        }

        return url;
    }
});
