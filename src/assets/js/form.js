var global = this;

var waxform = {
	password: function (options) {
		var form = $(options.form),
			field = form.find('[name="' + options.fieldName + '"]'),
			container = field.closest('.wax-password');

		container.find('.wax-show-password').click(function(e){
			e.preventDefault();

			if (field.attr('type') == 'password') {
				field.attr('type', 'text');
			} else {
				field.attr('type', 'password');
			}
		});
	},
	loader: function (options) {
		var form = $(options.form);

		form.find('#' + options.id).addClass('btn-can-load');
		form.find('#' + options.id).attr('data-loading-text', "<span class='fa fa-spinner fa-spin fa-3x fa-fw'></span>");
	},
	colorpicker : function (element) {
		element.colorPicker({
			customBG: '#222',
			margin: '10px -2px 0',
			doRender: 'div div',
			cssAddon: // could also be in a css file instead
				'.cp-color-picker{border:1px solid #e7e7e7; z-index: 100; padding:10px 10px 0;' +
					'background:#fff; overflow:visible; border-radius:3px;}' +
				'.cp-color-picker:after{content:""; display:block; ' +
					'position:absolute; top:-15px; left:12px; border:8px solid #fff;' +
					'border-color: transparent transparent #fff}' +
				// simulate border...
				'.cp-color-picker:before{content:""; display:block; ' +
					'position:absolute; top:-16px; left:12px; border:8px solid #fff;' +
					'border-color: transparent transparent #e7e7e7}' +
				'.cp-xy-slider:active {cursor:none;}' +
				'.cp-xy-slider{border:1px solid #e7e7e7; margin-bottom:10px;}' +
				'.cp-xy-cursor{width:12px; height:12px; margin:-6px}' +
				'.cp-z-slider{margin-left:10px; border:1px solid #e7e7e7;}' +
				'.cp-z-cursor{border-width:5px; margin-top:-5px;}' +
				'.cp-color-picker .cp-alpha{margin:10px 0 0; height:6px; border-radius:6px;' +
					'overflow:visible; border:1px solid #e7e7e7; box-sizing:border-box;' +
					'background: linear-gradient(to right, rgba(238,238,238,1) 0%,rgba(238,238,238,0) 100%);}' +
				'.cp-color-picker .cp-alpha{margin:10px 0}' +
				'.cp-alpha-cursor{background: #fff; border-radius: 100%;' +
					'width:14px; height:14px; margin:-5px -7px; border:1px solid #666!important;' +
					'box-shadow:inset -2px -4px 3px #ccc}',
			renderCallback: function($elm, toggled) {
				if (typeof toggled === 'boolean') {
					$('.cp-alpha', this.$UI).css('display', 'none')
				}
			}
		});
	},
	tags : function (options) {
		var form = $(options.form);

		var bloodhound = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			identify: function(obj) { return obj.text; },
			local: options.items
		});

		var items = function (q, sync) {
			if (q == '') {
				sync(bloodhound.all());
			}

			else {
				bloodhound.search(q, sync);
			}
		}
		var elem = form.find('[name="'+options.fieldName+'"]');
	
		elem.tagsinput({
			typeaheadjs: [{
				minLength: 0,
				highlight: true,
			},{
				limit: 50,
				name: options.id,
				displayKey: 'text',
				source: items,
			}],
			itemText: 'text',
			itemValue: 'id',
			freeInput: false,
			trimValue: true,
			allowDuplicates: false,
			onTagExists: function(item, $tag) {
				$tag.hide().fadeIn();
			}
		});

		elem.on('beforeItemAdd', function(event) {
			var found = false;
			$.each(elem.tagsinput('items'),function(k,v){
				if (v.id == event.item.id) {
					found = true;
				}
			})
			if (found) {
				event.cancel = true;
			}
		});

		elem.bind('change',function(e) {
            form.formValidation('revalidateField', options.fieldName);
        })
		
		$.each(options.values, function(k,v){
			elem.tagsinput('add', { id: v.id, text: v.text });
		});
	},

	radiogroup : function (options) {
		var form = $(options.form);

		form.find('[name="'+options.fieldName+'"]').change(function(){
			$(this).closest('form').formValidation('revalidateField', $(this).attr('name'));
		})
	},

	textarea : function (options) {
		var form = $(options.form);

		form.find('[name="'+options.fieldName+'"]').autogrow({vertical: true, horizontal: false});
	},

	tabs : function (options) {
		var form = $(options.form);

		var shouldShrink = function(section) {
			var tabsContainer = section.find('.wax-form-tabs'),
				tabsContainerWidth = tabsContainer.outerWidth(),
				visibleTabs = tabsContainer.find('> a:not(.hided), .dropdown:not(.hided)'),
				visibleTabsWidth = 0;

			visibleTabs.each(function(){
				visibleTabsWidth += $(this).outerWidth();
			});

			if (tabsContainerWidth < visibleTabsWidth) {
				return true;
			}

			return false;
		}

		var shrink = function (section) {
			var tabsContainer = section.find('.wax-form-tabs');

			var hided = tabsContainer.find('> a:not(.hided):last').addClass('hided');

			section.find('.dropdown a[data-href="'+hided.attr('data-href')+'"]').removeClass('hided');

			tabsContainer.find('.dropdown').removeClass('hided');

			if (hided.hasClass('active')) {
				tabsContainer.find('.dropdown-toggle').addClass('active');
			}
		}

		var shouldGrow = function(section) {
			var tabsContainer = section.find('.wax-form-tabs'),
				tabsContainerWidth = tabsContainer.outerWidth(),

				visibleTabs = tabsContainer.find('> a:not(.hided)'),
				visibleTabsWidth = 0,
				
				dropdownTab = tabsContainer.find('> .dropdown'),
				dropdownTabWidth = dropdownTab.outerWidth(),

				firstInvisibleTab = tabsContainer.find('> a.hided:first'),
				firstInvisibleTabWidth = firstInvisibleTab.outerWidth(),

				hiddenTabsCount = tabsContainer.find('> a.hided').length;

			visibleTabs.each(function(){
				visibleTabsWidth += $(this).outerWidth();
			});

			if (hiddenTabsCount > 1) {
				if (visibleTabsWidth + dropdownTabWidth + firstInvisibleTabWidth <= tabsContainerWidth) {
					grow(section);
				}
			} else if (hiddenTabsCount == 1) {
				if (visibleTabsWidth + firstInvisibleTabWidth <= tabsContainerWidth) {
					grow(section);
					tabsContainer.find('.dropdown').addClass('hided');
				}
			}
		}

		var grow = function (section) {
			var tabsContainer = section.find('.wax-form-tabs');

			var revealed = tabsContainer.find('> a.hided:first').removeClass('hided');

			section.find('.dropdown a[data-href="'+revealed.attr('data-href')+'"]').addClass('hided');

			if (revealed.hasClass('active')) {
				tabsContainer.find('.dropdown-toggle').removeClass('active');
			}
		}

		var resizeTabs = function (section) {
			if (shouldShrink(section)) {
				shrink(section);
				resizeTabs(section);
			} else if (shouldGrow(section)) {
				grow(section);
				resizeTabs(section);
			}
		}

		form.find('section').each(function(){
			var section = $(this);

			setTimeout(function(){
				resizeTabs(section);
			}, 100);

			$(window).resize(function(){
				resizeTabs(section);
			})

			section.find('.wax-brow:not(:first):not(.no-tab)').addClass('hidden');
			section.find('.wax-brow:first:not(.no-tab)').addClass('shown');
			section.find('.wax-form-tabs a:first').addClass('active');

			section.find('.wax-form-tabs a').click(function(e){
				e.preventDefault();

				var tab = $(this).attr('data-href');

				if (tab !== undefined && tab != '') {
					section.find('.wax-form-tabs a').removeClass('active');
					section.find('.wax-form-tabs a[data-href="' + tab + '"]').addClass('active');
					section.find('.wax-form-tabs a[data-href="' + tab + '"]:not(.hided)').closest('.dropdown').find('.dropdown-toggle').addClass('active');

					section.find('.wax-brow:not(.no-tab)').removeClass('shown').addClass('hidden');
					section.find('.wax-brow[data-tab="' + $(this).attr('data-href') + '"]').removeClass('hidden').addClass('shown');
				}
			});
		});

		var anchor = window.location.hash;
		if (anchor !== undefined && anchor != '') {
			anchor = anchor.substring(1);

			setTimeout(function(){
				form.find('.wax-form-tabs a[data-href="'+anchor+'"]').click();
			},150);
		}
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

			waxform.corrigateStepX(form);

			$('html, body, .st-container').animate({
		        scrollTop: form.offset().top
		    }, 500);
		})

		form.find('.wax-step-nav').click(function(){
			var lastActive = form.find('.wax-form-steps a.active').prev().addClass('completed');
		});

		$(window).resize(function(){
			waxform.corrigateStepX(form);
		})
	},

	corrigateStepX : function (form) {
		var center = $(window).width() / 2,
			active = form.find('.wax-form-steps a.active');

		if (active.length == 0) {
			return;
		}

		var activeLeft = active.offset().left,
			activeCenter = (active.outerWidth() / 2) + activeLeft,
			currentTransform = parseInt(form.find('.steps-container').css('transform').split(',')[4]) || 0,
			correctionX = center - activeCenter,
			newTransform = currentTransform + correctionX;

		form.find('.wax-form-steps .steps-container').css({"transform":"translateX("+newTransform+"px)"});
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

		var format = options.format ? options.format : false,
			prefix = options.prefix ? options.prefix : '',
			suffix = options.suffix ? options.suffix : '';

		if (options.value !== undefined) {
			sliderOptions.value = options.value;
		}

		var onChange = function (slideEvt) {
			$(this).trigger('change');

			if (typeof slideEvt.value == 'object') {
				$.each(options.values, function(k,v){
					$('input[name="'+options.fieldName+'"]:eq('+k+')').val(slideEvt.value[k]);	
				})

				if (options.minSelector !== undefined) {
					var value = slideEvt.value[0];

					if (format) {
						value = value.formatMoney(0, ',', ' ');
					}

					$(options.minSelector).text(prefix + value + suffix);
				}

				if (options.maxSelector !== undefined) {
					var value = slideEvt.value[1];

					if (format) {
						value = value.formatMoney(0, ',', ' ');
					}

					$(options.maxSelector).text(prefix + value + suffix);
				}
			} else {
				$('input[name="'+options.fieldName+'"]').val(slideEvt.value);

				if (options.minSelector !== undefined) {
					var value = slideEvt.value;

					if (format) {
						value = value.formatMoney(0, ',', ' ');
					}

					$(options.minSelector).text(prefix + value + suffix);
				}
			}

            var $field = $(slideEvt.target);

            $field
                .closest('.form-group')
                    .find('.percentageValue')
                    .html($field.slider('getValue') + '%');

            form.formValidation('revalidateField', options.fieldName);
		}

		$('#'+options.fieldId+'-container').slider(sliderOptions)
			.on('slide', function(slideEvt) {
				onChange(slideEvt);
			})
			.on('slideStop', function(slideEvt) {
				onChange(slideEvt);
			});

		$('#'+options.fieldId+'-container').change(function(){
			$('input[name="'+options.fieldName+'"]').val($(this).val());
		})

		if (options.minSelector !== undefined) {
			var value = parseInt(options.values[0]);
			
			//if (format && typeof value == 'integer') {
			if (format) {
				value = value.formatMoney(0, ',', ' ');
			}
			
			$(options.minSelector).text(prefix + value + suffix);
		}

		if (options.maxSelector !== undefined && options.values !== undefined) {
			var value = options.values[1];

			//if (format && typeof value == 'integer') {
			if (format) {
				value = (value).formatMoney(0, ',', ' ');
			}
			
			$(options.maxSelector).text(prefix + value + suffix);
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

			params['locale'] = Lang.getLocale();

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
				formData.append('locale', Lang.getLocale());
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
				$('#'+options.fieldId+'-previews .empty').addClass('hidden');

				var previewBlock = $('#'+options.fieldId+'-preview-template').find('.file').clone();

				previewBlock.find('.file-path-template').attr('href', options.fileUrlBase + response.file).removeClass('file-path-template');
				previewBlock.find('.file-name-template').html(response.filename).removeClass('file-name-template');
				previewBlock.find('.file-name-download-template').attr('download', response.filename).removeClass('file-name-download-template');
				previewBlock.find('.file-name-input-template').val(response.file).attr('name', options.fieldName + '[0][file]').removeClass('file-name-input-template');

				var checkboxTemplate = previewBlock.find('.checkbox-template').clone();
				var inputTemplate = previewBlock.find('.input-template').clone();

				if (options.fields !== undefined) {
					$.each(options.fields, function(k,v){
						switch (v.type) {
							case 'checkbox':
								var checkboxInstance = checkboxTemplate.clone();
								checkboxInstance.removeClass('checkbox-template');

								checkboxInstance.attr('name', options.fieldName+'[0]['+v.name+']');

								previewBlock.find('.input-template').before(checkboxInstance);
								break;

							case 'input':
							case '':
								var inputInstance = inputTemplate.clone();
								inputInstance.removeClass('input-template');

								inputInstance.attr('name', options.fieldName+'[0]['+v.name+']');
								inputInstance.attr('placeholder', v.placeholder);

								if (v.type.length > 0) {
									inputInstance.attr('type', v.type);
								}

								previewBlock.find('.input-template').before(inputInstance);
								break;
						}
					})
				}

				previewBlock.find('.checkbox-template').remove();
				previewBlock.find('.input-template').remove();

				$('#'+options.fieldId+'-previews').append(previewBlock);

				refreshMultifileKeys();

				form.formValidation('revalidateField', options.name);
			},
			sending : function (file, xhr, formData) {
				formData.append('formDescriptor', form.find('[name="formDescriptor"]').val());
				formData.append('element', options.fieldPlainName);
				formData.append('type', 'file');
				formData.append('locale', Lang.getLocale());
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

				$('#'+options.fieldId+'-previews .image-default').addClass('hidden');
				$('#remove-image-'+options.fieldId).removeClass('hidden');

				if (options.onSuccess.length > 0) {
					executeFunctionByName(options.onSuccess, window);
				}
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
				formData.append('locale', Lang.getLocale());
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
			$('#'+options.fieldId+'-previews .image-default').removeClass('hidden');
			$('#remove-image-'+options.fieldId).addClass('hidden');

			if (options.onRemove.length > 0) {
				executeFunctionByName(options.onRemove, window);
			}
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
				formData.append('locale', Lang.getLocale());
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
					preview += '	<a href="" class="remove"><span class="fa fa-remove"></span></a> <a href="' + response.previewUrl + response.file + '" target="_blank" class="name">' + response.filename + '</span>';
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

			toolbar_Toolbar : options.toolbar,
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
					url :'/wx/form/prefetch/',
					prepare : function (settings) {
						settings.type = 'GET';
						settings.data = {}
						settings.data.element = options.fieldPlainName;
						settings.data._token = form.find('input[name="_token"]').val();
						settings.data.locale = Lang.getLocale();
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
						settings.type = 'GET';
						settings.data = {}
						settings.data._token = form.find('input[name="_token"]').val();
						settings.data.locale = Lang.getLocale();
						settings.data.descriptor = form.find('input[name="formDescriptor"]').val();

						if (options.dynamicParams !== undefined) {
							settings.data.test = $('#is-test').val();
							$.each(options.dynamicParams, function(k,v){
								settings.data[k] = $('[name="'+v+'"]').val()
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
				if (options.itemsSync !== undefined) {
					sync(items.get(options.itemsSync));
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

		var toggleFields = function (elem, compareWithElemValue) {
			var domType = $(elem)[0].tagName.toLowerCase(),
				type = $(elem).attr('type'),
				element = form.find('[id^="'+options.cleanId+'"]'),
				container = element.closest(options.hideSelector),
				isValueTrue = options.isValueArray != true && options.value == true ? true : false,
				compareValue = $(elem).val();

			if (compareWithElemValue === false) {
				compareValue = $('[name="'+$(elem).attr('name')+'"]').val()
			}

			if (domType != 'input') {
				type = domType;
			}

			switch (type) {
				case 'checkbox':
					if ($(elem).is(':checked')) {
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
							inputValue = compareValue;

						$.each(values, function(k,v){
							if (v.toString() == inputValue.toString()) {
								condition = true;
							}
						})
					} else {
						var condition = compareValue.toString() == options.value.toString();
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
		}

		var elems = form.find('[name="'+options.conditionFieldName+'"], [name="'+options.conditionFieldName+'[]"], [name^="'+options.conditionFieldName+'['+options.nth+']"]');

		elems.bind('change', function(e){
			e.preventDefault();

			toggleFields(this, true);			
		})

		// for init
		elems.each(function(){
			toggleFields(this, false);			
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
					languages = form.find('.wax-form-language-selector'),
					buttons = $('.btn-can-load');

				buttons.button('reset');

				if (tabs.length > 0) {
					var brow = target.closest('.wax-brow').attr('data-tab'),
						tab = tabs.find('a[href="' + brow + '"]');

					tab.addClass('contains-error');

					if (window.scrolledToErrorField == false) {
						window.scrolledToErrorField = true;

						tab.trigger('click');

						$('html, body, .st-container').animate({
					        scrollTop: target.closest('.wax-element').offset().top
					    }, 500);
					}
				}

				if (steps.length > 0) {
					var section = target.closest('section').attr('id'),
						step = steps.find('a[href="' + section + '"]');

					step.addClass('contains-error').removeClass('disabled');

					if (window.scrolledToErrorField == false) {
						window.scrolledToErrorField = true;

						step.trigger('click');

						$('html, body, .st-container').animate({
					        scrollTop: target.closest('.wax-element').offset().top
					    }, 500);
					}
				}

				if (languages.length > 0) {
					var language = target.attr('data-language'),
						tab = target.closest('form').find('.wax-form-language-selector a[href="' + language + '"]');

					tab.addClass('contains-error');

					if (window.scrolledToErrorField == false) {
						window.scrolledToErrorField = true;

						tab.trigger('click');

						$('html, body, .st-container').animate({
					        scrollTop: target.closest('.wax-element').offset().top
					    }, 500);
					}
				}

				if (window.scrolledToErrorField == false) {
					window.scrolledToErrorField = true;

					$('html, body, .st-container').animate({
				        scrollTop: target.closest('.wax-element').offset().top
				    }, 500);
				}
			},
			onSuccess: function(e) {
				letLeave = false;

				var target = $(e.target),
					tabs = form.find('.wax-form-tabs'),
					steps = form.find('.wax-form-steps'),
					section = target.closest('section'),
					sectionId = section.attr('id'),
					brow = target.closest('.wax-brow'),
					dataTab = brow.attr('data-tab'),
					tab = tabs.find('a[href="' + dataTab + '"]'),
					step = steps.find('a[href="' + sectionId + '"]'),
					language = target.attr('data-language');

				if (section.find('.has-error').length == 0) {
					step.removeClass('contains-error');
				}

				if (brow.find('.has-error').length == 0) {
					tab.removeClass('contains-error');
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