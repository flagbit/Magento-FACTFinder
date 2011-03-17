var FactfinderSCIC = Class.create({
	url:  null,
	data: null,
	classname: null,
	mapping: null,
	request: null,
	regex: new RegExp(/product\/([0-9]+)\//),
	initialize: function(classname, data, mapping, url) {
		this.classname = classname;
		this.data = data;
		this.mapping = mapping;
		this.url = url;
	},
	
	init: function() {
		$$(this.classname+' a',this.classname+' button').each(function(element) {
			this.mapping.each(function(pair, index) {
				if(element.readAttribute('href') && element.readAttribute('href') == pair.key){
					return this.prepareElement(element, pair.value, 'click');
				}
				if(element.readAttribute('onclick') && element.readAttribute('onclick').indexOf(pair.key) >= 0){
					return this.prepareElement(element, pair.value, 'click');
				}
			}.bind(this));

			if(element.readAttribute('href')) {
				var match = this.regex.exec(element.readAttribute('href'));
				if(match && match[1]) {
					return this.prepareElement(element, match[1], 'click');
				}
			}

			if(element.readAttribute('onclick')) {
				var match = this.regex.exec(element.readAttribute('onclick'));
				if(match && match[1]) {
					return this.prepareElement(element, match[1], 'click');
				}
			}
		}.bind(this));
	},

	prepareElement: function(element, id, eventType) {
		Event.observe(element, 'click', function(event) {
			this.recordRequest(id, eventType);
		}.bind(this));
	},

	recordRequest: function(id, eventType) {

		var data = this.data.get(id);
		data.event = eventType;
				
		this.request = new Ajax.Request(this.url, {
			method: 'post',
			parameters: data
		});
		return false;
	}
});