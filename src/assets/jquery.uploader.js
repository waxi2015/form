;(function($){

	var plugins = {};

	$.fn.waxuploader = function ( parameters ) {
	
		var elem = this;

		var id = this.attr('id');

		if (plugins[id] === undefined) {
			plugins[id] = {
				params : {},
			}
		}

		var init = function () {
			

			plugins[id].inited = true;
		}

		switch (typeof parameters) {

			// first param is PARAMS
			case 'object':
				plugins[id].params = $.extend(plugins[id].params, parameters);

				init();
				break;

			// first param is a API CALL
			case 'string':
				switch (action) {
					case 'refresh':
						refresh();
						break;

					// just INIT
					default:
						init();
						break;
				}
				break;

			default:
				// just INIT
				init();
				break;
		}

		return this;
	}

	
}(jQuery))