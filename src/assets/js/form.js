var global = this;

var waxform = {
	tabs : function (options) {
		var form = $(options.form);

		form.find('section:not(:first)').hide();
		form.find('.wax-form-tabs a:first').addClass('active');

		form.find('.wax-form-tabs a').click(function(e){
			e.preventDefault();

			var section = $(this).attr('href');

			form.find('.wax-form-tabs a').removeClass('active');
			form.find('.wax-form-tabs a[href="' + section + '"]').addClass('active');

			form.find('section').hide();
			form.find('section#' + $(this).attr('href')).show();
		})
	},

	steps : function (options) {
		var form = $(options.form);

		form.find('section:not(:first)').hide();
		form.find('.wax-form-steps a:not(:first)').addClass('disabled');
		form.find('.wax-form-steps a:first').addClass('active');

		form.find('.wax-form-steps a, .wax-step-nav').click(function(e){
			e.preventDefault();

			if ($(this).hasClass('disabled')) {
				return false;
			}

			var section = $(this).attr('href');

			form.find('.wax-form-steps a').removeClass('active');
			form.find('.wax-form-steps a[href="' + section + '"]').addClass('active').removeClass('disabled');

			form.find('section').hide();
			form.find('section#' + section).show();
		})
	},

	languages : function (options) {
		var form = $(options.form),
			selectors = form.find('.wax-form-language-selector a'),
			first = selectors.first(),
			language = first.attr('href');

		first.addClass('active');

		form.find('.wax-translatable').hide();
		form.find('.wax-translatable [data-language="' + language + '"]').closest('.wax-element').show();

		selectors.click(function(e){
			e.preventDefault();

			selectors.removeClass('active');
			$(this).addClass('active');

			var language = $(this).attr('href');

			form.find('.wax-translatable').hide();
			form.find('.wax-translatable [data-language="' + language + '"]').closest('.wax-element').show();
		})
	},

	swap : function (options) {
		$("#" + options.fieldId).bootstrapSwitch({
			size : options['size'],
			onText : options.onText,
			offText : options.offText
		});
	},

	slider : function (options) {
		var form = $(options.form);

		var sliderOptions = {
			min : options.min,
			max : options.max,
			step : options.step,
			tooltip : options.tooltip,
			orientation : options.orientation,
			enabled : options.enabled,
			range: options.range,
		};

		if (options.value !== undefined) {
			sliderOptions.value = options.value;
		}

		$('#'+options.fieldId+'-container').slider(sliderOptions)
			.on('slide', function(slideEvt) {
				if (typeof slideEvt.value == 'object') {
					$.each(options.values, function(k,v){
						$('input[name="'+options.fieldName+'"]:eq('+k+')').val(slideEvt.value[k]);	
					})

					if (options.minSelector !== undefined) {
						$(options.minSelector).text(slideEvt.value[0]);
					}

					if (options.maxSelector !== undefined) {
						$(options.maxSelector).text(slideEvt.value[1]);
					}
				} else {
					$('input[name="'+options.fieldName+'"]').val(slideEvt.value);

					if (options.minSelector !== undefined) {
						$(options.minSelector).text(slideEvt.value);
					}
				}

	            var $field = $(slideEvt.target);

	            $field
	                .closest('.form-group')
	                    .find('.percentageValue')
	                    .html($field.slider('getValue') + '%');

	            form.formValidation('revalidateField', options.fieldName);

			});

		if (options.minSelector !== undefined) {
			$(options.minSelector).text(options.values[0]);
		}

		if (options.maxSelector !== undefined && options.values !== undefined) {
			$(options.maxSelector).text(options.values[1]);
		}
	},

	select : function (options) {
		var form = $(options.form);

		$('#' + options.fieldId).bind('change', function () {
			var that = this;

			var params = {
				descriptor : form.find('input[name="formDescriptor"]').val(),
				element : options.fieldPlainName,
				_token : form.find('input[name="_token"]').val()
			};

			$.each(options.params, function(k,v){
				params[v] = $(that).val();
			})

			$.post('/wx/form/loaddata', params, function (response) {
				$('[name="'+options.fieldName+'"]')
					.html(createOptionsFromJson(response))
					.trigger('change');

				if (!options.isOriginal) {
					$('[name="'+options.fieldName+'"]').selectpicker('refresh');
				}
			})
		})
	},

	bootstrapSelect : function (options) {
		var form = $(options.form);

		$('[name="'+options.fieldName+'"]')
			.selectpicker()
			.bind('change',function(e) {
                form.formValidation('revalidateField', options.fieldName);
            })

		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
		    $('[name="'+options.fieldName+'"]').selectpicker('mobile');
		}
	},

	multiimage : function (options) {
		Dropzone.autoDiscover = false;

		var form = $(options.form),
			element = $('#' + options.fieldId);

		var initRemoveImages = function () {
			var removeSelector = '#'+options.fieldId+'-previews .preview-block .remove';

			$(document).off('click', removeSelector).on('click', removeSelector, {} , function(e){
				e.preventDefault();

				$(this).closest('.preview-block').remove();

				if (options.maxFiles !== undefined) {
					global[options.fieldId].options.maxFiles = (parseInt(global[options.fieldId].options.maxFiles) + 1);
				}

				refreshMultiimageKeys();
			})
		}

		var refreshMultiimageKeys = function () {
			$('#'+options.fieldId+'-previews .preview-block').each(function(key){
				$(this).find('input').each(function(){
					var nameParts = $(this).attr('name').split('['),
						keyPart = nameParts.length - 2,
						currentName = nameParts[keyPart].replace(']',''),
						newName = '';

					$.each(nameParts, function(k,v){
						if (k == 0) {
							newName += v;
						} else if (k != keyPart) {
							newName += '[' + v;
						} else {
							newName += '[' + key + ']';
						}
					})

					$(this).attr('name', newName);
				})
			})
		}

		element.dropzone({
			autoDiscover: false,
			url: "/wx/form/upload",
			paramName: 'image',
			previewsContainer: '.dropzone-previews',
			createImageThumbnails: false,
			error: function(file, message){
				alert(message);
			},
			init: function () {
				global[options.fieldId] = this;
			},
			totaluploadprogress : function (progress) {
				$('#'+options.fieldId+'-progress').removeClass('hidden');
				$('#'+options.fieldId+'-progress .bar').css({width: progress + '%'});
			},
			maxFilesize: options.maxFilesize,
			acceptedFiles: options.acceptedFiles,
			dictDefaultMessage: options.dictDefaultMessage,
			success : function (file, response){
				$('#'+options.fieldId+'-progress').addClass('hidden');

				var img = '<a href="' + options.previewUrlBase + response.file + '" rel="group" class="image" style="background-image: url(' + options.thumbnailUrlBase + response.file + ')"></a>',
					input = '<input type="hidden" name="' + options.fieldName + '[0][image]" value="' + response.file + '" />';

				var previewBlock = '<div class="preview-block">';
					previewBlock += img;
					previewBlock += '<a href="" class="remove"><span class="fa fa-remove"></span></a>';
					previewBlock += input;

				if (options.fields !== undefined) {
					$.each(options.fields, function(k,v){
						previewBlock += '<input id="'+options.fieldId+'-'+v.name+'" name="'+options.fieldName+'[0]['+v.name+']" placeholder="'+v.placeholder+'" />';
					})
				}

				previewBlock += '</div>';


				$('#'+options.fieldId+'-previews').append(previewBlock);

				refreshMultiimageKeys();

				form.formValidation('revalidateField', options.fieldName);
			},
			sending : function (file, xhr, formData) {
				formData.append('formDescriptor', form.find('[name="formDescriptor"]').val());
				formData.append('element', options.fieldPlainName);
				formData.append('type', 'image');
				formData.append('_token', form.find('[name="_token"]').val());
			}
		});
	
		if (options.maxFiles !== undefined) {
			global[options.fieldId].options.maxFiles = options.maxFiles;
		}

		initRemoveImages();

		$('.image-previews').sortable({
			change: function () {
				refreshMultiimageKeys();
			}
		});

		$('#'+options.fieldId+'-previews .image').fancybox();
	},

	multifile : function (options) {
		Dropzone.autoDiscover = false;

		var form = $(options.form),
			element = $('#' + options.fieldId);

		var initRemoveFiles = function () {
			var removeSelector = '#'+options.fieldId+'-previews .file .remove';

			$(document).off('click', removeSelector).on('click', removeSelector, {} , function(e){
				e.preventDefault();

				$(this).closest('.file').remove();

				if (options.maxFiles !== undefined) {
					global[options.fieldId].options.maxFiles = (parseInt(global[options.fieldId].options.maxFiles) + 1);
				}

				refreshMultifileKeys();
			})
		}

		var refreshMultifileKeys = function () {
			$('#'+options.fieldId+'-previews .file').each(function(key){
				$(this).find('input').each(function(){
					var nameParts = $(this).attr('name').split('['),
						keyPart = nameParts.length - 2,
						currentName = nameParts[keyPart].replace(']',''),
						newName = '';

					$.each(nameParts, function(k,v){
						if (k == 0) {
							newName += v;
						} else if (k != keyPart) {
							newName += '[' + v;
						} else {
							newName += '[' + key + ']';
						}
					})

					$(this).attr('name', newName);
				})
			})
		}

		element.dropzone({
			autoDiscover: false,
			url: "/wx/form/upload",
			paramName: 'file',
			previewsContainer: '.dropzone-previews',
			createImageThumbnails: false,
			maxFilesize: options.maxFilesize,
			acceptedFiles: options.acceptedFiles,
			dictDefaultMessage: options.dictDefaultMessage,
			error: function(file, message){
				alert(message);
			},
			init: function () {
				global[options.fieldId] = this;
			},
			totaluploadprogress : function (progress) {
				$('#'+options.fieldId+'-progress').removeClass('hidden');
				$('#'+options.fieldId+'-progress .bar').css({width: progress + '%'});
			},
			success : function (file, response){
				$('#'+options.fieldId+'-progress').addClass('hidden');

				var previewBlock = '<div class="file">';
					previewBlock += '	<a href="" class="remove"><span class="fa fa-remove"></a></a>';
					previewBlock += '	<a class="name" href="' + options.fileBaseUrl + response.file + '">' + response.file + '</a>';
					previewBlock += '	<input type="hidden" name="' + options.fieldName + '[0][file]" value="' + response.file + '" />';

				if (options.fields !== undefined) {
					$.each(options.fields, function(k,v){
						previewBlock += '<input id="'+options.fieldId+'-'+v.name+'" name="'+options.fieldName+'[0]['+v.name+']" placeholder="'+v.placeholder+'" />';
					})
				}

				previewBlock += '</div>';

				$('#'+options.fieldId+'-previews').append(previewBlock);

				refreshMultifileKeys();

				form.formValidation('revalidateField', options.name);
			},
			sending : function (file, xhr, formData) {
				formData.append('formDescriptor', form.find('[name="formDescriptor"]').val());
				formData.append('element', options.fieldPlainName);
				formData.append('type', 'file');
				formData.append('_token', form.find('[name="_token"]').val());
			}
		});
	
		if (options.maxFiles !== undefined) {
			global[options.fieldId].options.maxFiles = options.maxFiles;
		}

		initRemoveFiles();

		$('.file-previews').sortable({
			change: function () {
				refreshMultifileKeys();
			}
		});
	},

	image : function (options) {
		var form = $(options.form),
			element = $('#' + options.fieldId);

		Dropzone.autoDiscover = false;

		element.dropzone({
			autoDiscover: false,
			url: "/wx/form/upload",
			paramName: 'image',
			previewsContainer: '.dropzone-previews',
			createImageThumbnails: false,
			uploadprogress: function(file, progress, bytesSent) {
				$('#'+options.fieldId+'-progress').removeClass('hidden');
				$('#'+options.fieldId+'-progress .bar').css({width: progress + '%'});
			},
			maxFiles: 1,
			error: function(file, message){
				alert(message);
			},
			maxFilesize: options.maxFilesize,
			acceptedFiles: options.acceptedFiles,
			dictDefaultMessage: options.dictDefaultMessage,
			success : function (file, response){
				$('#'+options.fieldId+'-progress').addClass('hidden');

				$('#'+options.fieldId+'-previews img').not('.image-default').remove();
				$('#'+options.fieldId+'-previews').append('<img class="image-preview" src="' + options.imageUrlBase + response.file + '" />');

				$('input[name="'+options.fieldName+'"]').val(response.file).trigger('change');

				//$('#'+options.fieldId+'-previews .image-default').addClass('hidden');
				$('#remove-image-'+options.fieldId).removeClass('hidden');
			},
			addedfile: function() {
				if (this.files[1] != null){
					this.removeFile(this.files[0]);
				}
			},
			sending : function (file, xhr, formData) {
				formData.append('formDescriptor', form.find('[name="formDescriptor"]').val());
				formData.append('element', options.fieldPlainName);
				formData.append('type', 'image');
				formData.append('_token', form.find('[name="_token"]').val());
			},
			init : function () {
				$('[name="'+options.fieldName+'"]').bind('change',function(e) {
		            form.formValidation('revalidateField', options.fieldName);
		        });
			}
		});

		$('#remove-image-'+options.fieldId).unbind('click').bind('click', function(e){
			e.preventDefault();

			$('#'+options.fieldId+'-previews img').not('.image-default').remove();
			$('input[name="'+options.fieldName+'"]').val('').trigger('change');
			//$('#'+options.fieldId+'-previews .image-default').removeClass('hidden');
			$('#remove-image-'+options.fieldId).addClass('hidden');
		})
	},

	file : function (options) {
		Dropzone.autoDiscover = false;

		var form = $(options.form),
			element = $('#' + options.fieldId);

		element.dropzone({
			autoDiscover: false,
			url: "/wx/form/upload",
			paramName: 'file',
			previewsContainer: '.dropzone-previews',
			createImageThumbnails: false,
			maxFilesize: options.maxFilesize,
			acceptedFiles: options.acceptedFiles,
			dictDefaultMessage: options.dictDefaultMessage,
			maxFiles: 1,
			uploadprogress: function(file, progress, bytesSent) {
				$('#'+options.fieldId+'-progress').removeClass('hidden');
				$('#'+options.fieldId+'-progress .bar').css({width: progress + '%'});
			},
			error: function(file, message){
				alert(message);
			},
			addedfile: function() {
				if (this.files[1] != null){
					this.removeFile(this.files[0]);
				}
			},
			sending : function (file, xhr, formData) {
				formData.append('formDescriptor', form.find('[name="formDescriptor"]').val());
				formData.append('element', options.fieldPlainName);
				formData.append('type', 'file');
				formData.append('_token', form.find('[name="_token"]').val());
			},
			init : function () {
				$('[name="'+options.fieldName+'"]').bind('change',function(e) {
		            form.formValidation('revalidateField', options.fieldName);
		        });
			},
			success : function (file, response){
				$('#'+options.fieldId+'-progress').addClass('hidden');

				var preview =  '<div class="file">';
					preview += '	<a href="" class="remove"><span class="fa fa-remove"></span></a> <a href="' + response.path + response.file + '" target="_blank" class="name">' + response.file + '</span>';
					preview += '</div>';

				$('#'+options.fieldId+'-previews').html(preview);

				$('input[name="'+options.fieldName+'"]').val(response.file).trigger('change');
			},
		});

		var removeSelector = '.file-previews .remove';

		$(document).off('click', removeSelector).on('click', removeSelector, {} , function(e){
			e.preventDefault();

			$(this).closest('.wax-file').find('.value').val('').trigger('change');

			$(this).parent().remove();
		})
	},

	editor : function (options) {
		var form = $(options.form);

		var getWysiwygHeight = function () {
			if ($("#" + options.fieldId).closest('.wax-section').find('.wax-element').not('.wax-editor').length > 0) {
				return 200;
			}

			var documentH 	= Math.max(document.documentElement.clientHeight, window.innerHeight || 0),
				sideH		= $('#menu-header').outerHeight(true) + $('#menu').outerHeight(true) + $('.menu-footer').outerHeight(true) + 60,
				minus		= $('.header').outerHeight(true) + $('.wax-form-language-selector').outerHeight(true) + $('.wax-form-tabs').outerHeight(true) + 114,
				height		= documentH - minus;

			if (height < sideH - minus)
				height = sideH - minus;

			if (height < 200) {
				height = 200;
			}
				
			return height;
		}

		$('#' + options.fieldId).ckeditor(function(){
			$("#" + options.fieldId).ckeditor(function(){
				this.on('blur', function(){
					form.formValidation('revalidateField', options.fieldName);
				});
			});
		},{
			height : getWysiwygHeight(),
			language : 'hu',
			skin : 'minimalist',
			filebrowserBrowseUrl : '/libs/ckeditor/plugins/kcfinder/browse.php?type=files',
			filebrowserImageBrowseUrl : '/libs/ckeditor/plugins/kcfinder/browse.php?type=images',
			filebrowserFlashBrowseUrl : '/libs/ckeditor/plugins/kcfinder/browse.php?type=flash',
			filebrowserUploadUrl : '/libs/ckeditor/plugins/kcfinder/upload.php?type=files',
			filebrowserImageUploadUrl : '/libs/ckeditor/plugins/kcfinder/upload.php?type=images',
			filebrowserFlashUploadUrl : '/libs/ckeditor/plugins/kcfinder/upload.php?type=flash',
			fontSize_sizes : '11/11px,12/12px,13/13px,14/14px,15/15px,16/16px,17/17px,18/18px,19/19px,20/20px,21/21px,22/22px,23/23px,24/24px',
			format_tags : 'p;h2;h3;h4;h5',
			toolbar : 'Toolbar',
			entities : false,
			basicEntities : false,
			entities_greek : false,
			entities_latin : false,
			entities_additional : '',
			htmlEncodeOutput : false,
			allowedContent : true,
			extraPlugins : 'font,table,youtube,letterspacing,onchange',
			contentsCss  : options.css,
			forcePasteAsPlainText : true,

			font_names : 'Arial,Times New Roman,Verdana,' + 'Open Sans/Open Sans,',

			toolbar_Toolbar :
			[
				[ 'Format' ],
				[ 'Bold', 'Italic', 'Underline', 'Strike'],
				['BulletedList','NumberedList','Outdent','Indent'],
				['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
				['Image','Youtube',',Table','Link','Unlink'],
				['Undo','Redo'],
				[ 'Source' ]
			],
		});

		$(window).resize(function(){
			$("#" + options.fieldId).closest('.wax-element').find(".cke_contents").css({height:getWysiwygHeight()});
		})
	},

	autocomplete : function (options) {

		var form = $(options.form);

		var autocompleteNew = $('input[name="'+options.fieldName+'[new]"]'),
			autocompleteValue = $('input[name="'+options.fieldName+'[value]"]');

		var bloodhoundOptions = {
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace(options.display),
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			identify: function(obj) { return obj[options.display].toLowerCase(); },
		};

		switch (options.mode) {
			case 'local':
				bloodhoundOptions.local = options.local;
				break;

			case 'prefetch':
				bloodhoundOptions.prefetch = {
					cache: false,
					url :'/wx/form/prefetch/?element=' + options.fieldPlainName,
					prepare : function (settings) {
						settings.type = 'POST';
						settings.data = {}
						settings.data._token = form.find('input[name="_token"]').val();
						settings.data.descriptor = form.find('input[name="formDescriptor"]').val();
						return settings;
					}
				}
				break;

			case 'remote':
				bloodhoundOptions.remote = {
					cache : true,
					wildcard : '%QUERY',
					url :'/wx/form/suggest/?element=' + options.fieldPlainName + '&query=%QUERY',
					prepare : function (query, settings) {
						settings.url = settings.url.replace('%QUERY', query);
						settings.type = 'POST';
						settings.data = {}
						settings.data._token = form.find('input[name="_token"]').val();
						settings.data.descriptor = form.find('input[name="formDescriptor"]').val();

						if (options.dynamicParams !== undefined) {
							settings.data.test = $('#is-test').val();
							$.each(options.dynamicParams, function(k,v){
								settings.data[k] = $('[name="'+v+'"').val()
							})
						}
						
						return settings;
					}
				}
				break;
		}
		
		var items = new Bloodhound(bloodhoundOptions);

		function fetchItems (q, sync, async) {
			if (q === '') {
				if (options.syncItems !== undefined) {
					sync(syncItems);
				}
			} else {
				if (options.mode == 'remote') {
					items.search(q, sync, function(datums) {
						replaceKeyValue(datums, q);
						return async(datums);
					});
				} else {
					items.search(q, function(datums) {
						replaceKeyValue(datums, q);
						return sync(datums);
					});
				}
			}
		}

		function replaceKeyValue (datums, q) {
			var valueFound = false;

			if (datums.length >= 1) {
				var match = false;

				$.each(datums, function(k, v) {
					if (v[options.display].toLowerCase() == q.toLowerCase()) {
						match = v.autocompletekey;
					}
				})

				if (match || match === 0 || match === '0') {
					valueFound = true;

					autocompleteNew.val('false');

					if (options.isKeyAsValue) {
						autocompleteValue.val(match);
					} else {
						autocompleteValue.val(q);
					}
				}
			}

			if (!valueFound) {
				autocompleteNew.val('true');
				autocompleteValue.val(q);
			}
		}

		$('#' + options.fieldId).typeahead({
			hint: 		options.hint,
			highlight: 	options.highlight,
			minLength: 	options.minLength
		},
		{
			source: 	fetchItems,
			limit: 		options.limit,
			display: 	options.display,
			displayKey: options.display,
			name: 		options.fieldId,
			templates: {
				empty: function (context) {
					if (context.query === '') {
						return false;
					} else {
						return [options.emptyHtml].join('\n')
					}
				},
				suggestion: Handlebars.compile(options.suggestionHtml)
			}
		}).bind('typeahead:select', function(ev, suggestion) {
			if (options.isKeyAsValue) {
				var value = suggestion.autocompletekey;
			} else {
				var value = suggestion[options.display];
			}

			autocompleteValue.val(value);
			autocompleteNew.val('false');

			if (options.onSelect !== undefined) {
				executeFunctionByName(options.onSelect, window, suggestion);	
			}
		})
		.on('typeahead:selected', function(e, suggestion, dataSetName) {
            form.formValidation('revalidateField', options.fieldName + '[display]');
        })
        .on('typeahead:closed', function(e) {
            form.formValidation('revalidateField', options.fieldName + '[display]');
        });;

		$('#' + options.fieldId).bind('keyup', function () {
			if ($(this).val() == '') {
				autocompleteValue.val('');
				autocompleteNew.val('');
			}
		})
	},

	addCondition : function (options) {
		var form = $(options.form),
			changeEnabled = function (isEnabled, container) {
				if (isEnabled) {
					container.removeClass('hidden');
				} else {
					container.addClass('hidden');
				}

				container.find('input, select, textarea')
					.prop(
						'disabled',
						(isEnabled ? false : true)
					);

				switch (options.type) {
					case 'swap':
						$('[name="'+options.targetFieldName+'"]')
							.bootstrapSwitch('disabled', (isEnabled ? false : true));
						break;

					case 'slider':
						$('#'+options.targetFieldId+'-container')
							.slider(isEnabled ? 'enable' : 'enable')
							.slider('refresh')
							.slider('relayout');
						break;
				}
			};

		form.find('[name="'+options.conditionFieldName+'"], [name="'+options.conditionFieldName+'[]"], [name^="'+options.conditionFieldName+'['+options.nth+']"]').bind('change', function(e){
			e.preventDefault();

			var domType = $(this)[0].tagName.toLowerCase(),
				type = $(this).attr('type'),
				element = form.find('[id^="'+options.cleanId+'"]'),
				container = element.closest(options.hideSelector),
				isValueTrue = options.isValueArray != true && options.value == true ? true : false;

			if (domType != 'input') {
				type = domType;
			}

			switch (type) {
				case 'checkbox':
					if ($(this).is(':checked')) {
						changeEnabled(true, container);
					} else {
						changeEnabled(false, container);
					}
					break;

				case 'select':
				case 'radio':
					if (options.isValueArray) {
						var values = options.value,
							condition = false,
							inputValue = $(this).val();

						$.each(values, function(k,v){
							if (v == inputValue) {
								condition = true;
							}
						})
					} else {
						var condition = $(this).val() == options.value;
					}

					if (condition) {
						changeEnabled(true, container);
					} else {
						changeEnabled(false, container);
					}
					break;
			}

			if (options.type == 'select' && !options.isOriginal) {
				$('[name="'+options.targetFieldName+'"]').selectpicker('refresh');
			}
		})
	},

	addValidator : function (options) {
		var form = $(options.form);

		form.formValidation('addField', options.field, {
			validators : options.validators,
			icon: options.icon !== undefined ? options.icon : true,
			onError: function(e) {
				letLeave = false;
				
				var target = $(e.target),
					tabs = form.find('.wax-form-tabs'),
					steps = form.find('.wax-form-steps'),
					languages = form.find('.wax-form-language-selector');

				if (tabs.length > 0) {
					var section = target.closest('section').attr('id'),
						tab = tabs.find('a[href="' + section + '"]');

					tab.addClass('contains-error');

					if (window.scrolledToErrorField == false) {
						window.scrolledToErrorField = true;

						tab.trigger('click');

						$('html, body').animate({
					        scrollTop: target.offset().top
					    }, 1000);
					}
				}

				if (steps.length > 0) {
					var section = target.closest('section').attr('id'),
						step = steps.find('a[href="' + section + '"]');

					step.addClass('contains-error').removeClass('disabled');

					if (window.scrolledToErrorField == false) {
						window.scrolledToErrorField = true;

						step.trigger('click');

						$('html, body').animate({
					        scrollTop: target.offset().top
					    }, 1000);
					}
				}

				if (languages.length > 0) {
					var language = target.attr('data-language'),
						tab = target.closest('form').find('.wax-form-language-selector a[href="' + language + '"]');

					tab.addClass('contains-error');

					if (window.scrolledToErrorField == false) {
						window.scrolledToErrorField = true;

						tab.trigger('click');

						$('html, body').animate({
					        scrollTop: target.offset().top
					    }, 1000);
					}
				}

				if (window.scrolledToErrorField == false) {
					window.scrolledToErrorField = true;

					$('html, body').animate({
				        scrollTop: target.offset().top
				    }, 1000);
				}
			},
			onSuccess: function(e) {
				letLeave = false;

				var target = $(e.target),
					tabs = form.find('.wax-form-tabs'),
					steps = form.find('.wax-form-steps'),
					section = target.closest('section'),
					sectionId = section.attr('id'),
					tab = tabs.find('a[href="' + sectionId + '"]'),
					step = steps.find('a[href="' + sectionId + '"]'),
					language = target.attr('data-language');

				if (section.find('.has-error').length == 0) {
					tab.removeClass('contains-error');
					step.removeClass('contains-error');
				}

				var languageContainsError = false;
				target.closest('form').find('[data-language="' + language + '"]').each(function(k,v){
					if ($(this).closest('.wax-element').hasClass('has-error')) {
						languageContainsError = true;
					}
				})

				if (!languageContainsError) {
					target.closest('form').find('.wax-form-language-selector a[href="' + target.attr('data-language') + '"]').removeClass('contains-error');
				}
			},
		})
	}
}