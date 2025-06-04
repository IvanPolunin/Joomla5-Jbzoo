// Turn radios into btn-group
jQuery(function($) {
	/**
	 * Turn radios into btn-group
	 */
	// Check if any plugin parameters are there and must be inverted before processing
	if($('#datasource_plugin_parameters').length) {
		$('#datasource_plugin_parameters fieldset.btn-group').each(function(index, fieldsetElement){
			var lastLabel = $('label:last-child', fieldsetElement).clone(true, true).end().remove();
			$(fieldsetElement).prepend(lastLabel);
		})
	}
	
	var container = document.querySelectorAll('.btn-group');
	for (var i = 0; i < container.length; i++) {
		var labels = container[i].querySelectorAll('label');
		for (var j = 0; j < labels.length; j++) {
			labels[j].classList.add('btn');
			var inputValue = $('input[type=radio]', labels[j]).val();
			if ((j % 2) == 1 && inputValue !== '' && parseInt(inputValue) < 1) {
				labels[j].classList.add('btn-outline-danger');
			} 
			if ((j % 2) == 1 && inputValue === '') {
				labels[j].classList.add('btn-outline-primary');
			} else {
				labels[j].classList.add('btn-outline-success');
			}
		}
	}

	var btsGrouped = document.querySelectorAll('.btn-group input[checked=checked]');
	for (var i = 0, l = btsGrouped.length; l>i; i++) {
		var self   = btsGrouped[i],
		    attrId = self.id,
		    label = document.querySelector('label[for=' + attrId + ']');
		if (self.parentNode.parentNode.classList.contains('btn-group-reversed')) {
			if (self.value === 0) {
				label.classList.add('active');
				label.classList.add('btn');
				label.classList.add('btn-outline-success');
			} else {
				label.classList.add('active');
				label.classList.add('btn');
				label.classList.add('btn-outline-danger');
			}
		} else {
			if (self.value === 0) {
				label.classList.add('active');
				label.classList.add('btn');
				label.classList.add('btn-outline-danger');
			} else {
				if (self.value === '') {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-primary');
				} else {
					label.classList.add('active');
					label.classList.add('btn');
					label.classList.add('btn-outline-success');
				}
			}
		}
	}
	
	// Perform columns ordering
	$('a[data-ordering-form]').on('click', function(jqEvent){
		let orderingOrder = $(jqEvent.target).attr('data-ordering-order');
		let orderingDirection = $(jqEvent.target).attr('data-ordering-direction');
		let orderingTask = $(jqEvent.target).attr('data-ordering-task');
		Joomla.tableOrdering(orderingOrder, orderingDirection, orderingTask);
		return false;
	});
	
	// Always ensure to reset the other switcher button
	$(document).on('click', "fieldset[data-bs-toggle=buttons] label.btn", function(jqEvent) {
		if(jqEvent.target.nodeName.toUpperCase() == 'INPUT' || $(jqEvent.target).attr('disabled')) {
			return true;
		}
		
		var label = $(jqEvent.target).addClass('active');
		var input = $('input[type=radio]', label);

		var otherLabel = label.parents('fieldset').find("label").not(label);
		if (otherLabel.hasClass('active')) {
			otherLabel.removeClass('active btn-success btn-danger btn-primary');
		}
	});

	// Override the default switcher button colors/class for multiple selection switcher buttons
	var multipleSwitchers = $("div.controls > label:nth-child(3), div.controls > fieldset > label:nth-child(3)");
	multipleSwitchers.each(function(index, elem){
		var parentContainer = $(elem).parent();
		$('label', parentContainer).removeClass('btn-outline-success btn-outline-danger')
		// We are not in the configuration view
		if(parentContainer.prop("tagName").toLowerCase() != 'fieldset') {
			$('label:first-child', parentContainer).addClass('btn-outline-primary');
			$('label:not(:first-child)', parentContainer).addClass('btn-outline-success');
		} else {
			$('label', parentContainer).addClass('btn-outline-success');
		}
	});
	
	// Ensure that only input labels with 'No' value will be dangered 
	var doubledSwitchers = $("div.controls > label:nth-child(2), div.controls > fieldset > label:nth-child(2)");
	doubledSwitchers.each(function(index, elem){
		var inputValue = $('input', elem).val();
		if(inputValue != 0 && inputValue != '' && inputValue != '') {
			$(elem).removeClass('btn-outline-danger').addClass('btn-outline-success');
		}
	});
	
	/**
	 * Enables bootstrap popover
	 */
	[].slice.call(document.querySelectorAll('a.hasPopover.google, span.hasPopover.google')).map(function (popoverEl) {
		return new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'left',
			html : true
		});
	});
	[].slice.call(document.querySelectorAll('label.hasPopover, button.hasPopover, div.hasPopover, span.hasPopover, div.controls a.hasPopover')).map(function (popoverEl) {
		return new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'top',
			html : true
		});
	});
	[].slice.call(document.querySelectorAll('span.hasRightPopover, a.hasPopover.dialog_trigger')).map(function (popoverEl) {
		return new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'right',
			html : true
		});
	});
	[].slice.call(document.querySelectorAll('thead a.hasPopover')).map(function (popoverEl) {
		return new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger : 'hover',
			placement : 'top',
			html : true
		});
	});
	
	/**
	 * Enables bootstrap tooltip
	 */
	[].slice.call(document.querySelectorAll('label.hasTooltip, img.hasTooltip, a.hasTooltip, span.hasTooltip, a.hasTip, *[rel=tooltip], a.page-link')).map(function (tooltipEl) {
		let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
			trigger:'hover', 
			placement:'top', 
			html: true
		});
		
		return tooltipInstance;
	});

	/**
	 * Remove empty ordering spans
	 */
	$('td.order > span').filter(function() {
		var hasChild = !$('a', this).length;
		return hasChild;
	}).remove();
	// Recover the legacy save order button in async way on the next cycle
	setTimeout(function() {
		$('a.saveorder').removeAttr('onclick').removeAttr('style');
	}, 1);

	/**
	 * Add custom select
	 */
	$('table.headerlist select[name=filter_state]').addClass('form-select').removeClass('form-control');

	// Remove configuration spacer empty div
	$('span.spacer').parent('div.control-label').next('div.controls').remove();
	
	/**
	 * Accordion panels local storage memoize and set open
	 */
	var defaultAccordionObject = {
		'accordion_cpanel' : 'seo_stats',
		'accordion_datasource_pluginimport' : 'datasource_pluginimport',
		'accordion_datasource_details' : 'datasource_details',
		'accordion_datasource_excludecats' : 'datasource_excludecats',
		'accordion_datasource_excludearticles' : 'datasource_excludearticles',
		'accordion_datasource_workflowstages' : 'datasource_workflowstages',
		'accordion_datasource_excludemenu' : 'datasource_excludemenu',
		'accordion_datasource_menupriorities' : 'datasource_menupriorities',
		'accordion_datasource_catspriorities' : 'datasource_catspriorities',
		'accordion_datasource_parameters' : 'datasource_parameters',
		'accordion_datasource_sqlquery' : 'datasource_sqlquery',
		'accordion_datasource_xmlparameters' : 'datasource_xmlparameters',
		'accordion_datasource_sqlquery_maintable' : 'datasource_sqlquery_maintable',
		'accordion_datasource_sqlquery_jointable1' : 'datasource_sqlquery_jointable1',
		'accordion_datasource_sqlquery_jointable2' : 'datasource_sqlquery_jointable2',
		'accordion_datasource_sqlquery_jointable3' : 'datasource_sqlquery_jointable3',
		'accordion_datasource_sqlquery_autogenerated' : 'datasource_sqlquery_autogenerated',
		'accordion_datasource_sqlquery_querystring' : 'datasource_sqlquery_querystring',
		'accordion_pingomatic_details' : 'pingomatic_details',
		'accordion_pingomatic_services' : 'pingomatic_services',
		'accordion_aigenerator_details':'aigenerator_details',
		'accordion_aigenerator_contents_results':'contents_results',
		'accordion_datasets_details' : 'datasets_details',
		'accordion_datasets_datasources' : 'datasets_datasources',
		'accordion_datasource_plugin_parameters' : 'datasource_plugin_parameters',
		'accordion_datasource_raw_links' : 'datasource_raw_links',
		'accordion_replacements_details' : 'replacements_details',
		'accordion_replacements_regex' : 'replacements_regex',
		'jmap_googlegraph_accordion' : 'jmap_googlestats_graph',
		'jmap_googlegeo_accordion' : 'jmap_googlestats_geo',
		'jmap_googletraffic_accordion' : 'jmap_googlestats_traffic',
		'jmap_googlereferrer_accordion' : 'jmap_googlestats_referrers',
		'jmap_googlesearches_accordion' : 'jmap_googlestats_searches',
		'jmap_googlesystems_accordion' : 'jmap_googlestats_systems',
		'jmap_googlepages_accordion' : 'jmap_googlestats_pages',
		'jmap_googlestats_webmasters_sitemaps_accordion' : 'jmap_googlestats_webmasters_sitemaps',
		'jmap_google_search_console_accordion' : 'jmap_google_search_console',
		'jmap_google_inspectionurl_accordion' : 'jmap_google_inspectionurl',
		'jmap_googleconsole_query_accordion' : 'jmap_google_query',
		'jmap_googleconsole_pages_accordion' : 'jmap_google_pages',
		'jmap_googleconsole_device_accordion' : 'jmap_google_device',
		'jmap_googleconsole_country_accordion' : 'jmap_google_country',
		'jmap_googleconsole_date_accordion' : 'jmap_google_date',
        'jmap_google_pagespeed_summary_accordion' : 'jmap_google_pagespeed_summary',
        'jmap_google_pagespeed_performance_accordion' : 'jmap_google_pagespeed_performance',
        'jmap_google_pagespeed_assets_accordion' : 'jmap_google_pagespeed_assets',
        'jmap_google_pagespeed_seo_accordion' : 'jmap_google_pagespeed_seo',
        'jmap_google_pagespeed_overview_accordion' : 'jmap_google_pagespeed_overview'
	};
	
	
	[].slice.call(document.querySelectorAll('#accordion_cpanel, div.sqlquerier, div.sqlquerier div.card.card-warning, #ga-dash div.card, form.webmasters_cards div.card')).map(function (accordionEl) {
		accordionEl.addEventListener('shown.bs.collapse', function(event){
			if (!$(event.target).hasClass('card-block')) {
				return;
			}
			event.stopPropagation();
			
			// Trigger window resize to force graph resizing
			if (event.target.id == 'jmap_status' || event.target.id == 'seo_stats' || $(event.target).hasClass('accordion-chart')) {
				$(window).trigger('resize');
			}
			
			var localStorageAccordion = $.jStorage.get('accordionOpened', defaultAccordionObject);
			localStorageAccordion[this.id] = event.target.id;
			$.jStorage.set('accordionOpened', localStorageAccordion);
			
			// Scroll to accordion header if needed
			if (document.body.scrollHeight > window.innerHeight && $(this).attr('id') != 'accordion_cpanel') {
				$('html, body').animate({
					scrollTop : $("#" + event.target.id).prev().offset().top - 180
				}, 500);
			}
			
			// Add open state
			$(event.target).prev().addClass('opened');
		});
		
		accordionEl.addEventListener('hide.bs.collapse', function(event){
			if (!$(event.target).hasClass('card-block')) {
				return;
			}
			event.stopPropagation();
			var localStorageAccordion = $.jStorage.get('accordionOpened', defaultAccordionObject);
			if (localStorageAccordion[this.id] == event.target.id) {
				delete localStorageAccordion[this.id];
				$.jStorage.set('accordionOpened', localStorageAccordion);
			}
			
			// Remove open state
			$(event.target).prev().removeClass('opened');
		});
	});
	
	$.each($.jStorage.get('accordionOpened', defaultAccordionObject), function(namespace, element) {
		if ($('#' + element, '#' + namespace).length) {
			$('#' + element, '#' + namespace).addClass('show').prev().addClass('opened');
		}
	});

	/**
	 * Tab panels local storage memoize and set open
	 */
	var defaultTabObject = {
		'tab_configuration' : 'preferences'
	};
	
	[].slice.call(document.querySelectorAll('#adminForm .nav.nav-tabs')).map(function (tabEl) {
		tabEl.addEventListener('shown.bs.tab', function(event) {
			var localStorageTab = $.jStorage.get('tabOpened', defaultTabObject);
			var assignedID = this.id ? this.id : $(this).parents('div.row').attr('id');
			var assignedValue = $(event.target).data('element') ? $(event.target).data('element') : $(event.target).attr('href').substr(1)
			localStorageTab[assignedID] = assignedValue;
			$.jStorage.set('tabOpened', localStorageTab);
			
			// Add accessibility ARIA
			$('li.nav-item', this).removeAttr('aria-selected').attr('aria-selected', 'false');
			$('li.nav-item', this).removeAttr('tabindex').attr('tabindex', -1);
			$(event.target).parent('li').attr({'aria-selected':'true', 'tabindex':0});
			
			// Ensure that the label input checked will be active
			$("fieldset[data-bs-toggle=buttons] > label.btn > input:checked").each(function(i, element) {
				var parentLabel = $(element).parent('label.btn');
				if (!parentLabel.hasClass('active')) {
					parentLabel.addClass('active');
				}
			});
		});
	});

	$.each($.jStorage.get('tabOpened', defaultTabObject), function(namespace, element) {
		var nodeElement = $('a[data-element=' + element + ']', '#' + namespace).get(0);
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
	});
	
	$(document).on('click', 'joomla-field-permissions ul[role=tablist] li', function(jqEvent){
		var tabTargetId = $('a', this).attr('id');
		$.jStorage.set('jmapFieldPermissions', tabTargetId);
	});
	if($.jStorage.get('jmapFieldPermissions')) {
		setTimeout(function(){
			var memoizedTabId = $.jStorage.get('jmapFieldPermissions');
			if($('#' + memoizedTabId).length) {
				$('#' + memoizedTabId).get(0).click();
			}
		}, 150);
	}

	// Check for a specific tab trigger using url hash
	var hashQueryStringRequest = window.location.hash.substr(2);
	if(hashQueryStringRequest == 'licensepreferences') {
		var nodeElement = $('a[data-element=preferences]').get(0);
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
		$('#params_registration_email-lbl').css('color', 'red');
		$('#params_registration_email').css('border', '2px solid red');
		$('html, body').animate({
            scrollTop: $('#params_registration_email').offset().top - 120
        }, 800);
	}
	if(hashQueryStringRequest == 'google_analytics_ga4property') {
		var nodeElement = $('a[data-element=google_analytics]').get(0);
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
		$('#params_ga_property_id-lbl').css('color', 'red');
		$('#params_ga_property_id').css('border', '2px solid red');
		$('html, body').animate({
            scrollTop: $('#params_ga_property_id').offset().top - 120
        }, 800);
	}
	if(hashQueryStringRequest == 'chatgpt_apikey_missing') {
		var nodeElement = $('a[data-element=features]').get(0);
		if(nodeElement) {
			var tabInstance = new bootstrap.Tab(nodeElement);
			tabInstance.show();
		}
		$('#params_chatgpt_apikey-lbl').css('color', 'red');
		$('#params_chatgpt_apikey').css('border', '2px solid red');
		$('html, body').animate({
            scrollTop: $('#params_chatgpt_apikey').offset().top - 120
        }, 800);
	}

	/**
	 * Hide state select on phone
	 */
	$('#filter_state, #filter_type').addClass('d-none d-md-inline-block');

	/**
	 * Manage config template for html sitemap
	 */
	$('<div/>').insertAfter('#params_sitemap_html_template').css('background-image', 'url(components/com_jmap/images/templates.png)').addClass('sitemap_template');
	$('#params_sitemap_html_template').css({
		'width' : '150px',
		'float' : 'left',
		'transition' : 'none'
	}).on('change', function(jqEvent) {
		var nextDivPlaceholder = $(this).next('div');
		var indexSelected = $('#params_sitemap_html_template option:selected').index() || 0;
		var backgroundDisplacement = -(indexSelected * 181);
		nextDivPlaceholder.css('background-position', '0 ' + backgroundDisplacement + 'px');
	}).trigger('change');

	// Manage the hide/show of subcontrols for mindmap templating styles
	var sitemapTemplate = $('select[name=params\\[sitemap_html_template\\]]').val();
	if (sitemapTemplate != 'mindmap') {
		$('*.mindmap_styles').hide();
	}
	$('select[name=params\\[sitemap_html_template\\]]').on('change', function() {
		if ($(this).val() == 'mindmap') {
			$('*.mindmap_styles').slideDown();
		} else {
			$('*.mindmap_styles').slideUp();
		}
	});

	// Manage the hide/show of subcontrols for custom images tags
	var customTagsValue = $('input[name=params\\[custom_images_processor\\]]:checked').val();
	if (customTagsValue == 0) {
		$('*.customtags_styles').hide();
	}
	$('input[name=params\\[custom_images_processor\\]]').parent('label.btn').on('click', function() {
		if ($('input', this).val() == 1) {
			$('*.customtags_styles').slideDown();
		} else {
			$('*.customtags_styles').slideUp();
		}
	});

	// Manage the hide/show of subcontrols for rich snippets
	var searchboxTypeValue = $('input[name=params\\[searchbox_type\\]]:checked').val();
	if(searchboxTypeValue != 'custom') {
		$('*.searchbox_styles').hide();
	}
	$('input[name=params\\[searchbox_type\\]]').parent('label.btn').on('click', function(){
		if($('input', this).val() == 'custom') {
			$('*.searchbox_styles').slideDown();
		} else {
			$('*.searchbox_styles').slideUp();
		}
	});
	
	// Manage the hide/show of subcontrols for gojs templating styles
	var sitemapTemplate = $('select[name=params\\[sitemap_html_template\\]]').val();
	if (sitemapTemplate != 'gojs') {
		$('*.gojs_styles').hide();
	}
	$('select[name=params\\[sitemap_html_template\\]]').on('change', function() {
		if ($(this).val() == 'gojs') {
			$('*.gojs_styles').slideDown();
		} else {
			$('*.gojs_styles').slideUp();
		}
	});
	
	// Manage the hide/show of subcontrols for Analytics API
	var analyticsWebServiceValue = $('select[name=params\\[analytics_service\\]]').val();
	if(analyticsWebServiceValue != 'google') {
		$('*.analyticsapi_styles').hide();
		$('*.analytics_api').hide();
	}
	$('select[name=params\\[analytics_service\\]]').on('change', function(){
		if($(this).val() == 'google') {
			$('*.analyticsapi_styles').slideDown();
			if($('select[name=params\\[analytics_api\\]]').val() == 'data') {
				$('*.analytics_api').slideDown();
			}
			if($('select[name=params\\[analytics_api\\]]').val() != 'data') {
				$('*.no_analytics_api').slideDown();
			} else {
				$('*.no_analytics_api').slideUp();
			}
		} else {
			$('*.analyticsapi_styles').slideUp();
			$('*.analytics_api').slideUp();
			$('*.no_analytics_api').slideDown();
		}
	});
	
	// Manage the hide/show of subcontrols for Analytics API Property ID
	var analyticsApiValue = $('select[name=params\\[analytics_api\\]]').val();
	if(analyticsApiValue != 'data') {
		$('*.analytics_api').hide();
		$('*.no_analytics_api').show();
	} else {
		if(analyticsWebServiceValue == 'google') {
			$('*.no_analytics_api').hide();
		}
	}
	$('select[name=params\\[analytics_api\\]]').on('change', function(){
		if($(this).val() == 'data') {
			$('*.analytics_api').slideDown();
			$('*.no_analytics_api').slideUp();
		} else {
			$('*.analytics_api').slideUp();
			$('*.no_analytics_api').slideDown();
		}
	});
	
	// Manage the hide/show of subcontrols for Google Indexing API
	var searchboxTypeValue = $('input[name=params\\[enable_google_indexing_api\\]]:checked').val();
	if(searchboxTypeValue == 0) {
		$('*.googleindexing').hide();
	}
	$('input[name=params\\[enable_google_indexing_api\\]]').on('click', function(){
		var inputValue = parseInt($(this).val());
		if(inputValue) {
			$('*.googleindexing').slideDown();
		} else {
			$('*.googleindexing').slideUp();
		}
	});
	
	// Mutex add/remove slashes
	$('input[name=params\\[metainfo_remove_trailing_slash\\]]').on('click', function(){
		var currentValue = parseInt($(this).val());
		if(currentValue == 1) {
			$('#params_metainfo_add_trailing_slash1').parent('label.btn').trigger('click');
		}
	});
	$('input[name=params\\[metainfo_add_trailing_slash\\]]').on('click', function(){
		var currentValue = parseInt($(this).val());
		if(currentValue == 1) {
			$('#params_metainfo_remove_trailing_slash1').parent('label.btn').trigger('click');
		}
	});
	
	// Manage the hide/show of subcontrols for ChatGPT API
	var chatgptApiValue = $('select[name=params\\[chatgpt_api\\]]').val();
	if(chatgptApiValue != 'chatcompletions') {
		$('*.chatgptapi').hide();
	}
	$('select[name=params\\[chatgpt_api\\]]').on('change', function(){
		if($(this).val() == 'chatcompletions') {
			$('*.chatgptapi').slideDown();
		} else {
			$('*.chatgptapi').slideUp();
		}
	});
	
	// Create color picker controls
	$("input[id*=_color], input[id*=color_]").after('<div class="colorpicker_preview"><div></div></div>')
	var loadColor = function(elem, colorHex) {
		// Set input HEX color value
		$(elem).val(colorHex);
		$(elem).ColorPickerSetColor(colorHex);

		// Set background color of preview box
		var nextElPreview = $(elem).next('div.colorpicker_preview');
		$('div', nextElPreview).css('background-color', colorHex);
	}

	// Check if ColorPicker plugin is loaded
	if ($.fn.ColorPicker) {
		$("input[id*=_color], input[id*=color_]").ColorPicker({
			onSubmit : function(hsb, hex, rgb, el) {
				loadColor(el, '#' + hex);
			}
		});
		$("input[id*=_color], input[id*=color_]").each(function(k, elem) {
			var colorValue = $(elem).val();
			loadColor(elem, colorValue);
		});
	}

	// Show generic waiter
	var showGenericWaiter = function(mainContainerID) {
		// Get div popover container width to center waiter
		$('body').prepend('<img/>').children('img').attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif').css({
			'position' : 'absolute',
			'left' : '50%',
			'top' : '50%',
			'margin-left' : '-64px',
			'width' : '128px',
			'z-index' : '99999'
		});
	};
	$('#ga-dash button, *.waiter').on('click', function(jqEvent) {
		showGenericWaiter();
	});

	// Manage generic resetter buttons for multiple fields
	$('button[data-reset]').on('click', function(jqEvent) {
		jqEvent.preventDefault();
		var elementsClassToReset = $(this).data('reset');
		$('*.' + elementsClassToReset).each(function(index, element) {
			$(element).val('');
		});
		$('#adminForm').submit();
	});

	// Flag the changed domain for SEO stats and GTester
	$('#params_seostats_custom_link').on('change', function(jqEvent) {
		$(this).attr('data-changed', 1);
	});
	$('label[for^=params_seostats_site_query]').on('click', function(jqEvent) {
		$('#params_seostats_custom_link').attr('data-changed', 1);
	});
	$('#params_seostats_service').on('change', function(jqEvent) {
		$('#params_seostats_custom_link').attr('data-changed', 1);
	});
	
	/**
	 * Prevent default scrolling hover main accordion body and scroll
	 * programmatically the document
	 */
	$('div.card-block.card-overflow').on('wheel', function(jqEvent) {
		if (jqEvent.originalEvent && jqEvent.originalEvent.wheelDelta) {
			if (jqEvent.originalEvent.wheelDelta)
				jqEvent.delta = jqEvent.originalEvent.wheelDelta;

			var newBodyScroll = $(document).scrollTop() - jqEvent.delta;
			$(document).scrollTop(newBodyScroll);
			jqEvent.preventDefault();
			return false;
		}
	});
	
	/**
	 * Bind change based on fields edit
	 */
	$('#original_text,#target_text').on('keyup', function(jqEvent){
		$('input[name=autogenerate]').val(1);
	});
	
	// Add the button to run the crawler test
	$('#params_regex_images_crawler').addClass('pull-left').after('<label id="crawler_test" class="badge bg-primary spacer"><span class="icon icon-cogs"></span> Crawler test</label>');
	$('#crawler_test').on('click', function(jqEvent) {
		window.open('index.php?option=com_jmap&task=config.checkEntityCrawler&tmpl=component', 'crawler_test', 'width=1024,height=768');
	});
	
	// Reset Google Authentication data
	var authCodeField = $('#params_google_indexing_authcode');
	$('#google_authentication_reset').on('click', function(jqEvent){
		authCodeField.val('');
		$('#params_google_indexing_authtoken').val('');
		$('#toolbar-save button').trigger('click');
	});
	if(authCodeField.val() != '' && $('div.control-group.googleindexing span.badge').hasClass('bg-success')) {
		authCodeField.attr('readonly', true);
	}
	
	$('input.field-media-input').each(function(index, elem){
	    $(elem).css('visibility','hidden');
	});
	// Observe media field image selection change
	$('div.field-media-preview').each(function(index, elem){
		// Create an observer instance for each element to observe
		var observer = new MutationObserver(function(mutations) {
			var image = $('img', elem);
			if(image.length) {
				let relatedInputField = $(elem).next('div').find('input.field-media-input');
				relatedInputField.val(relatedInputField.val().split('#')[0]);
			}
		});
		observer.observe(elem, { childList: true });
	});
	setTimeout(function(){
	    $('input.field-media-input').each(function(index, elem){
    		elem.value = elem.value.split('#')[0];
    		$(elem).css('visibility','visible');
	    }); 
	}, 300);
	
	// Override permissions tab retrieve ACL
	$('#permissions select[data-onchange-task]').removeAttr('data-onchange-task').on('change', function(jqEvent) {
	    const {
	    	target
	    } = jqEvent;

	    const icon = document.getElementById(`icon_${target.id}`);
	    icon.removeAttribute('class');
	    icon.setAttribute('class', 'joomla-icon joomla-field-permissions__spinner');

	    const {
	    	value
	    } = target;

	    const id = target.id.replace('params_rules_', '');
	    const lastUnderscoreIndex = id.lastIndexOf('_');
	    const permissionData = {
	    		comp: 'com_jmap',
	    		action: id.substring(0, lastUnderscoreIndex),
	    		rule: id.substring(lastUnderscoreIndex + 1),
	    		value,
	    		title: 'com_jmap'
	    };

	    Joomla.removeMessages();

	    Joomla.request({
	    	url: document.querySelector('joomla-field-permissions').getAttribute('data-uri'),
	    	method: 'POST',
	    	data: JSON.stringify(permissionData),
	    	perform: true,
	    	headers: {
	    		'Content-Type': 'application/json'
	    	},
	    	onSuccess: data => {
	    		let response;

		        try {
		          response = JSON.parse(data);
		        } catch (e) {
		          console.log(e);
		        }

		        icon.removeAttribute('class');

		        if (response.data && response.data.result) {
		        	icon.setAttribute('class', 'joomla-icon joomla-field-permissions__allowed');
		        	const badgeSpan = target.parentNode.parentNode.nextElementSibling.querySelector('span');
		        	badgeSpan.removeAttribute('class');
		        	badgeSpan.setAttribute('class', response.data.class);
		        	badgeSpan.innerHTML = response.data.text;
		        }

		        if (typeof response.messages === 'object' && response.messages !== null) {
		        	Joomla.renderMessages(response.messages);
	        		if (response.data && response.data.result) {
		        	  	icon.setAttribute('class', 'joomla-icon joomla-field-permissions__allowed');
		          	} else {
		        	  	icon.setAttribute('class', 'joomla-icon joomla-field-permissions__denied');
		          	}
		        }
	    	},
	    	onError: xhr => {
	    		// Remove the spinning icon.
	    		icon.removeAttribute('style');
	    		Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr, xhr.statusText));
	    		icon.setAttribute('class', 'joomla-icon joomla-field-permissions__denied');
	    	}
	    });
	});
});

/**
 * Compatibility functions for classical save ordering
 */
JMapSaveOrder = function ( n, task ) {
	JMapCheckAllCheckbox( n, task );
};

JMapCheckAllCheckbox = function ( n, task ) {
	task = task ? task : 'saveorder';

	var j, box;

	for ( j = 0; j <= n; j++ ) {
		box = document.adminForm[ 'cb' + j ];

		if ( box ) {
			box.checked = true;
		} else {
			alert( "You cannot change the order of items, as an item in the list is `Checked Out`" );
			return;
		}
	}

	Joomla.submitform( task );
};