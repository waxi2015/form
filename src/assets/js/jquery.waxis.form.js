// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {

	"use strict";

		// undefined is used here as the undefined global variable in ECMAScript 3 is
		// mutable (ie. it can be changed by someone else). undefined isn't really being
		// passed in so we can ensure the value of it is truly undefined. In ES5, undefined
		// can no longer be modified.

		// window and document are passed through as local variable rather than global
		// as this (slightly) quickens the resolution process and can be more efficiently
		// minified (especially when both are regularly referenced in your plugin).

		// Create the defaults once
		var pluginName = "wxform",
				defaults = {
				propertyName: "value"
		};

		// The actual plugin constructor
		function WXForm ( element, options ) {
				this.element = element;
				// jQuery has an extend method which merges the contents of two or
				// more objects, storing the result in the first object. The first object
				// is generally empty as we don't want to alter the default options for
				// future instances of the plugin
				this.settings = $.extend( {}, defaults, options );
				this._defaults = defaults;
				this._name = pluginName;
				this.init();
		}

		// Avoid Plugin.prototype conflicts
		$.extend(WXForm.prototype, {
				init: function () {
					// Place initialization logic here
					// You already have access to the DOM element and
					// the options via the instance, e.g. this.element
					// and this.settings
					// you can add more functions like the one below and
					// call them like so: this.yourOtherFunction(this.element, this.settings).
					
					this.formId = $(this.element).attr('id');

					this.initValidation();

					this.setRemoveCloneButtons();
					this.initCloneButton();
					this.initRemoveCloneButton();
					this.initDecorators();

					this.initInputTooltip();
					this.initCssHelpers();
				},

				initCssHelpers : function () {
					$('#' + this.formId).find('.wax-element .input-group').each(function(k,v) {
						if ($(this).find('.form-control + .input-group-addon').length > 0) {
							$(this).closest('.wax-element').addClass('with-suffix');
						}
					})
				},

				initDecorators : function () {
					$('#' + this.formId).find('[data-decorator-type]').each(function(){
						switch ($(this).attr('data-decorator-type')) {
							case 'charlimit':
								var options = {
									type: 'char',
									goal: $(this).attr('data-decorator-limit'),
								}

								if ($(this).attr('data-decorator-message') !== undefined) {
									options.msg = $(this).attr('data-decorator-message');
								}

								$(this).counter(options);
								break;

							case 'wordlimit':
								var options = {
									type: 'word',
									goal: $(this).attr('data-decorator-limit'),
								}

								if ($(this).attr('data-decorator-message') !== undefined) {
									options.msg = $(this).attr('data-decorator-message');
								}

								$(this).counter(options);
								break;
						}
					})
				},

				initValidation : function () {
					var that = this;

					$('#' + this.formId).formValidation({
						framework: 'bootstrap',
						excluded: [':disabled'],
						icon: {
					        valid: 'fa fa-check',
					        invalid: 'fa fa-remove',
					        validating: 'fa fa-refresh'
					    },
					   // container: 'tooltip',
					    trigger: 'blur',
					    //live: false,
					    verbose: false
					})
			        .on('success.form.fv', function(e) {
			           window.scrolledToErrorField = false;
			            // Prevent form submission
			            
			            // Some instances you can use are
			            var $form = $(e.target),        // The form instance
			                fv    = $(e.target).data('formValidation'); // FormValidation instance

						if ($(that.element).attr('method').toLowerCase() == 'ajax') {
			           		//e.preventDefault();
							that.initAjaxSubmit();
							//$(this.element).submit();
						}
			        })
			        .on('prevalidate.form.fv', function(e) {
			           window.scrolledToErrorField = false;
			        });
				},

				setRemoveCloneButtons: function () {
					var that = this;

					$(this.element).find('[data-clone-removable="true"]').each(function(){
						var remove = $(this).find('.remove-clone-button');

						if (remove.length <= 0) {
							that.addRemoveTag($(this));
						}
					})
				},

				addRemoveTag : function (element) {
					var tree = element.attr('data-tree'),
						cloneCount = $('input[name="clone-' + tree + '"').val() * 1,
						removeTag = '<a href="" class="remove-clone-button wax-remove-clone-button" rel="' + tree + '"><span class="fa fa-remove"></span></a>';

					element.find('.remove-clone-button').detach();
					element.prepend(removeTag);
				},

				initInputTooltip : function () {
					//$(this.element).find('[data-toggle="tooltip"]').tooltip();
				},

				initCloneButton : function () {
					var cloneButtonSelector = '#' + $(this.element).attr('id') + ' .clone-button',
						wxform = this;

					$(document).off('click', cloneButtonSelector).on('click', cloneButtonSelector, {} ,function(e){
						e.preventDefault();

						var that = this,
							nodeTree = $(this).attr('rel');

						wxform.getClone(nodeTree, function (response) {
							if (!response.error) {
								$(that).closest('.wax-clone-container').before(response.html);
								wxform.setRemoveCloneButtons();
							}

							wxform.refreshCloneCount(nodeTree);
						});
					})
				},

				getClone : function (nodeTree, callback) {
					var formDescriptor = $(this.element).find('[name=formDescriptor]').val(),
						currentCloneCount = $(this.element).find('[data-tree="'+nodeTree+'"][data-clone="true"]').length;

					$.post('/wx/form/cloning',{nodeTree:nodeTree, clones:currentCloneCount, formDescriptor:formDescriptor, locale:Lang.getLocale(), _token:$(this.element).find('[name="_token"]').val()}, function (response) {
						callback(response);
					});
				},

				refreshCloneCount : function (nodeTree) {
					var cloneCount = $(this.element).find('[data-tree="' + nodeTree + '"][data-clone="true"]').length;
					$(this.element).find('input[name="clone-' + nodeTree + '"]').val(cloneCount);
				},

				setSectionWithErrors : function () {
					var form = $('#' + $(this.element).attr('id')),
						target = form.find('.has-error:first'),
						section = target.closest('section'),
						sectionId = section.attr('id'),
						tabs = form.find('.wax-form-tabs'),
						tab = tabs.find('[href="' + sectionId + '"]');

					tab.addClass('contains-error');

					tab.trigger('click');

					$('html, body, .st-container').animate({
				        scrollTop: target.offset().top
				    }, 1000);
				},

				setStepWithErrors : function () {
					var form = $('#' + $(this.element).attr('id')),
						target = form.find('.has-error:first'),
						section = target.closest('section'),
						sectionId = section.attr('id'),
						steps = form.find('.wax-form-steps'),
						step = steps.find('[href="' + sectionId + '"]');

					steps.find('a').removeClass('disabled');
					step.addClass('contains-error');

					step.trigger('click');

					$('html, body, .st-container').animate({
				        scrollTop: target.offset().top
				    }, 1000);
				},

				setLanguageWithErrors : function () {
					var form = $('#' + $(this.element).attr('id')),
						target = form.find('.has-error:first'),
						language = target.closest('.wax-element').find('[data-language]').attr('data-language'),
						selectors = form.find('.wax-form-language-selector'),
						tab = selectors.find('[href="' + language + '"]');

					tab.addClass('contains-error').trigger('click');

					$('html, body, .st-container').animate({
				        scrollTop: target.offset().top
				    }, 1000);
				},

				scrollToElementWithError : function () {
					var form = $('#' + $(this.element).attr('id')),
						target = form.find('.has-error:first');

					$('html, body, .st-container').animate({
				        scrollTop: target.offset().top
				    }, 1000);
				},

				initRemoveCloneButton : function () {
					var removeCloneButtonSelector = '#' + $(this.element).attr('id') + ' .remove-clone-button',
						wxform = this;

					$(document).off('click', removeCloneButtonSelector).on('click',removeCloneButtonSelector, {} ,function(e){
						e.preventDefault();

						var nodeTree = $(this).attr('rel'),
							dom = $(this).closest('[data-tree="'+nodeTree+'"]'),
							brackets = new RegExp("\[[0-9]{1,}\]");

						dom.find('[name*="["]').each(function() {
							var removableNumber = parseInt($(this).attr('name').match(brackets)[0].replace(/\[(.*?)\]/g,"$1")),
								removableName = $(this).attr('name').replace(brackets,'');

							wxform.removeValidation($(this).attr('name'))

							$(this).closest('form').find('[name*="["]').each(function() {
								var number = parseInt($(this).attr('name').match(brackets)[0].replace(/\[(.*?)\]/g,"$1")),
									name = $(this).attr('name').replace(brackets,'');

								if (number > removableNumber && name == removableName) {
									$(this).attr('name', $(this).attr('name').replace(number, number - 1))
								}
							})
						});

						dom.remove();

						wxform.refreshCloneCount(nodeTree);
					})
				},

				removeValidation : function (name) {
					var formId = $('[name="'+name+'"]').closest('form').attr('id');

					$('#' + formId).formValidation('removeField', name);
				},

				initAjaxSubmit : function () {
					var wxform = this,
						form = $(this.element),
						success = form.attr('data-success') !== undefined ? form.attr('data-success') : false,
						error = form.attr('data-error') !== undefined ? form.attr('data-error') : false,
						before = form.attr('data-before') !== undefined ? form.attr('data-before') : false,
						after = form.attr('data-after') !== undefined ? form.attr('data-after') : false;

					form.unbind('submit').bind('submit', function(e){
						e.preventDefault();

						var data = form.serialize();

						if (wxform.settings.params !== undefined) {
							$.each(wxform.settings.params, function (k,v) {
								if (data.length > 0) {
									data += '&'
								}
								data += k + '=' + v;
							});
						}

						executeFunctionByName(before, window);

						data += '&locale=' + Lang.getLocale();

						$.post('/wx/form/validateform', data, function (response) {
							letLeave = true;
							
							executeFunctionByName(after, window, response);

							if (response.valid  !== undefined && response.valid.toString() == 'true') {
								executeFunctionByName(success, window, response);
							} else {
								executeFunctionByName(error, window, response);
							}

							if (response.message !== undefined && response.message.length > 0) {
								if (response.valid  !== undefined && response.valid.toString() == 'true') {
									if (toastr) { toastr.success(response.message, Lang.get('form.success_msg_title')); }
								} else {
									if (toastr) { toastr.error(response.message, Lang.get('form.error_msg_title')); }
								}
							}

							form.replaceWith(response.html);

							if (response.valid.toString() == 'false') {
								if ($('#' + form.attr('id')).find('.has-error').length > 0) {
									if ($('#' + form.attr('id')).find('.wax-form-tabs, .wax-form-steps').length > 0) {
										wxform.setSectionWithErrors();
										wxform.setStepWithErrors();	
									} else {
										if ($('#' + form.attr('id')).find('.wax-form-language-selector').length > 0) {
											wxform.setLanguageWithErrors();	
										} else {
											wxform.scrollToElementWithError();
										}
									}
								}
							}
						});
					})
				}
		});

		// A really lightweight plugin wrapper around the constructor,
		// preventing against multiple instantiations
		$.fn[ pluginName ] = function ( options ) {
				return this.each(function() {
						if ( !$.data( this, "plugin_" + pluginName ) ) {
								$.data( this, "plugin_" + pluginName, new WXForm( this, options ) );
						}
				});
		};

})( jQuery, window, document );

function executeFunctionByName(functionName, context) {
	if (!functionName) {
		return false;
	}

	var args = [].slice.call(arguments).splice(2);
	var namespaces = functionName.split(".");
	var func = namespaces.pop();
	for(var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func].apply(this, args);
};

function createOptionsFromJson (json) {
	var html = '';

	$.each(json, function (value, label) {
		var option = '<option value="'+value+'" label="'+label+'">'+label+'</option>';
		html += option;
	})

	return html;
};

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

$.extend({
    keyCount : function(o) {
        if(typeof o == "object") {
            var i, count = 0;
            for(i in o) {
                if(o.hasOwnProperty(i)) {
                    count++;
                }
            }
            return count;
        } else {
            return false;
        }
    }
});


/*
	Form validation plugin settings: 
	http://formvalidation.io/settings
*/