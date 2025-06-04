/**
* Manage client tasks inside component CPanel 
* 
* @package JMAP::CPANEL::administrator::components::com_jmap 
* @subpackage js 
* @author Joomla! Extensions Store
* @copyright (C) 2021 Joomla! Extensions Store
* @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
jQuery(function($){
	var CPanel = $.newClass ({
		/**
		 * Main selector
		 * 
		 * @access public
		 * @property prototype
		 * @var array
		 */
		selector : null,
		
		/**
		 * Target selector
		 * 
		 * @access public
		 * @property prototype
		 * @var array
		 */
		targetSelector : null, 
		
		/**
		 * Canvas context
		 * 
		 * @access public
		 * @param String
		 */
		canvasContext : null,
		
		/**
		 * Chart data to render, copy from global injected scope
		 * 
		 * @access private
		 * @var Object
		 */
    	chartData : {},
    	
    	/**
		 * Status of the model save entity process for the robots entries
		 * 
		 * @access private
		 * @var Object
		 */
    	modelSaveEntitySuccess : false,
    	
    	/**
		 * Charts options
		 * 
		 * @access private
		 * @var Object
		 */
    	chartOptions : {animation:true, scaleFontSize: 11, scaleOverride: true, scaleSteps:1, scaleStepWidth: 50},

    	/**
		 * First progress snippet
		 * 
		 * @access private
		 * @var String
		 */
		firstProgress : '<div class="progress">' +
							'<div id="progressBar1" class="progress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">' +
								'<label class="visually-hidden">' + COM_JMAP_PROGRESSMODELSUBTITLE + '</label>' +
							'</div>' +
						'</div>',

		/**
		 * Writing XML sitemap progress snippet
		 * 
		 * @access private
		 * @var String
		 */
		writingProgress : '<div class="progress">' +
							'<div id="progressBarWriting" class="progress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
								'<label class="visually-hidden">' + COM_JMAP_PROGRESSMODELSUBTITLE + '</label>' +
							'</div>' +
						'</div>',
    	/**
    	 * Update status selector
    	 * 
    	 * @access private
    	 * @var String
    	 */
    	updateStatusSelector : '#updatestatus label.bg-danger',
    	
    	/**
    	 * Update process button snippet with placeholder to trigger the update process
    	 * 
    	 * @access private
    	 * @var String
    	 */
    	updateButtonSnippet : '<button id="updatebtn" data-bs-content="' + COM_JMAP_EXPIREON + '%EXPIREON%" class="btn btn-sm btn-primary">' + 
    							'<span class="icon-upload" aria-hidden="true"></span> ' + COM_JMAP_CLICKTOUPDATE + 
    						  '</button>',

		/**
		 * Object initializer
		 * 
		 * @access public
		 * @param string selector 
		 */
		init : function(selector, targetSelector) {
			var context = this;

			this.constructor.prototype.selector = selector;
			this.constructor.prototype.targetSelector = targetSelector;
			
			//Registrazione eventi
			this.registerEvents();
			
			// Get target canvas context 2d to render chart
        	if(!!document.createElement('canvas').getContext && $('#chart_canvas').length) {
        		this.constructor.prototype.canvasContext = $('#chart_canvas').get(0).getContext('2d');
        		
        		$(window).on('resize', {bind:this}, function(event){
        			event.data.bind.resizeRepaintCanvas();
            	});
            	
        		$(document).on('click', '#menu-collapse', function(event){
            		setTimeout(function(){
            			context.resizeRepaintCanvas();
            		}, 300);
            	});
        		
            	// Start generation
            	setTimeout(function(context){
            		context.resizeRepaintCanvas(true);
            	}, 300, this);
        	}
        	
        	// Trigger the updates license status checker
        	setTimeout(function(){
        		context.checkUpdatesLicenseStatus();
        	}, 500);
		},
	
		/**
		 * Register events for user interaction
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		registerEvents : function() {
			var context = this;
			
			// Register events select articoli
			$(this.selector).on('change', {bind:this}, function(event) {
				// Disabled complementary dropdown
				switch(event.target.id) {
					case 'menu_datasource_filters':
						event.target.value ? $('#datasets_filters').attr('disabled', true) : $('#datasets_filters').attr('disabled', false);
						break;
						
					case 'datasets_filters':
						event.target.value ? $('#menu_datasource_filters').attr('disabled', true) : $('#menu_datasource_filters').attr('disabled', false);
						break;
						
					case 'language_option':
						var originalLangValue = $(event.target).val();
						var langValue = $(event.target).val().replace('-', '_');
						if( window.sessionStorage !== null ) {
							sessionStorage.setItem('jmap_seodashboard_language', originalLangValue);
						}
						$('#jmap_langflag').remove();
						$(event.target).parent().append('<img id="jmap_langflag" src="' + jmap_baseURI + 'media/mod_languages/images/' + langValue + '.gif" alt="flag"/>');
						break;
				}
				
				event.data.bind.refreshCtrls(event.target, event.target.value); 
			});
			// Trigger change by default on page load to populate language query string at startup, check if there is a session storage value
			if( window.sessionStorage !== null ) {
				var seoDashboardLanguage = sessionStorage.getItem('jmap_seodashboard_language');
				if(seoDashboardLanguage) {
					$('#language_option option').removeAttr('selected');
					$('#language_option option:not(:first-child)[value="' + seoDashboardLanguage + '"]').attr('selected', true).prop('selected', true);
				}
			}
			$('#language_option').trigger('change');
			
			// Trigger if multilanguage is off and random links are on
			if(!$('#language_option').length && jmap_linksRandom) {
				this.refreshCtrls();
			}
			
			// Trigger if multilanguage is off and force format for links is on
			if(!$('#language_option').length && jmap_forceFormat) {
				this.refreshCtrls();
			}
			
			// Enables custom tooltips
			function showCustomTooltip(event) {
				var $target = $(event.target);
				var position = $target.position();
				var tooltipContent = $target.data('bs-original-title');
				$target.after('<div class="jmaptooltip">' + tooltipContent + '</div>');
				$('div.jmaptooltip').css({
					'display':'block',
					'top': position.top - 238 + 'px',
					'left': position.left - 95 + 'px'
				});
			}

			// Create a function to hide the tooltip
			function hideCustomTooltip() {
				$('div.jmaptooltip').remove();
			}

			[].slice.call(document.querySelectorAll('label.fas.fa-bolt.hasClickPopover')).map(function (button) {
				// When mouse hovers over the button, show the tooltip
				button.addEventListener('mouseover', showCustomTooltip);

				// When mouse leaves the button, hide the tooltip
				button.addEventListener('mouseout', hideCustomTooltip);
			});
			
			// Enables bootstrap popover
			[].slice.call(document.querySelectorAll('label.hasClickPopover')).map(function (popoverEl) {
				let popoverInstance = new bootstrap.Popover(popoverEl,{
					template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger: 'click', 
					placement: 'left',
					sanitize: false,
					html: true
				});
				
				popoverEl.addEventListener('shown.bs.popover', function(){
					$(context.selector).trigger('change', [true]);
					$('div.jmaptooltip').remove();
				})
				
				return popoverInstance;
			});
			
			[].slice.call(document.querySelectorAll('input.hasClickPopover')).map(function (popoverEl) {
				let popoverInstance = new bootstrap.Popover(popoverEl,{
					template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger: 'click', 
					placement: 'top',
					container: '#xmlsitemap_export',
					html: true,
					sanitize: false,
					title: function() {
						return COM_JMAP_CRONJOB_GENERATED_SITEMAP_FILE;
					},
					content: function(element) {
						if(element) {
							var queryString = $(element).val();
						} else {
							var queryString = $(this).val();
						}
						
						// Split into key/value pairs
					    var queries = queryString.split("&");
					    var params = {};

					    // Convert the array of strings into an object
					    for ( i = 0, l = queries.length; i < l; i++ ) {
					        temp = queries[i].split('=');
					        params[temp[0]] = temp[1];
					    }
					    
					    // Build the file name
					    var sitemapFrontendFilename = jmap_splittingStatus && params.format != 'videos' ? 'sitemapindex_' : 'sitemap_';
				    	sitemapFrontendFilename += params.format;
				    	
					    if(params.hasOwnProperty('lang')) {
					    	sitemapFrontendFilename += '_' + params.lang;
					    }
					    if(params.hasOwnProperty('dataset')) {
					    	sitemapFrontendFilename += '_dataset' + params.dataset;
					    }
					    if(params.hasOwnProperty('Itemid')) {
					    	sitemapFrontendFilename += '_menuid' + params.Itemid;
					    }
					    sitemapFrontendFilename += '.xml';
					    
						var concatenatePingXmlFormat = 	"<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/ping?sitemap=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'><span class='pinger-text'>" + COM_JMAP_PING_GOOGLE + "</span></a>" +
			 											"<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" + jmap_indexnow_apikey + "&url=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'><span class='pinger-text'>" + COM_JMAP_PING_BING + "</span></a>" +
	 													"<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'><span class='pinger-text'>" + COM_JMAP_PING_YANDEX + "</span></a>" +
														"<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" + encodeURIComponent(jmap_livesite + '/' + sitemapFrontendFilename) + "'><span class='pinger-text'>" + COM_JMAP_PING_BAIDU + "</span></a>";
					    
						return '<input type="text" class="popover-body" value="' + jmap_livesite + '/' + sitemapFrontendFilename + '"/>' +
							   '<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="' + COM_JMAP_PING_SITEMAP_CRONJOB + '" data-bs-content="' + concatenatePingXmlFormat + '"></label>' +
							   '<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="' + COM_JMAP_ROBOTS_SITEMAP_ENTRY_CRONJOB + '" data-role="saveentity"></label>';
					}
				});
				
				popoverEl.addEventListener('shown.bs.popover', function(){
					var targetPopover = $(this).parents('#xmlsitemap_export').find('div.popover');
					targetPopover.css('z-index', 99999);
					
					// Enable bootstrap tooltip
					[].slice.call(document.querySelectorAll('#xmlsitemap_export label.hasTooltip')).map(function (tooltipEl) {
						let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
							trigger:'hover', 
							placement:'top', 
							html: true
						});
						
						tooltipEl.addEventListener('shown.bs.tooltip', function(){
							$('div.tooltip.fade.show').css('z-index', 99999);
						})
						
						return tooltipInstance;
					});
					
					[].slice.call(document.querySelectorAll('#xmlsitemap_export label.hasClickPopover')).map(function (popoverEl) {
						let previousPopoverInstance = bootstrap.Popover.getInstance(popoverEl);
						if(previousPopoverInstance) {
							previousPopoverInstance.dispose();
						}
						
						let popoverInstance = new bootstrap.Popover(popoverEl,{
							template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
							trigger: 'click', 
							placement: 'left',
							sanitize: false,
							html: true
						});
						
						popoverEl.addEventListener('shown.bs.popover', function(){
							var targetInnerPopover = $('div.popover.fade.show');
							targetInnerPopover.css('z-index', 999999);
						})
						
						return popoverInstance;
					});
				});
				
				return popoverInstance;
			});
			
			// Ensure closing it when click on other DOM elements
			$(document).on('click', 'body', function(jqEvent){
				if(!$(jqEvent.target).hasClass('hasClickPopover') && !$(jqEvent.target).hasClass('popover-body')) {
					[].slice.call(document.querySelectorAll('label.hasClickPopover, input.hasClickPopover, li.hasClickPopover')).map(function (popoverEl) {
						if(popoverEl) {
							let popoverInstance = bootstrap.Popover.getInstance(popoverEl);
							if(popoverInstance) {
								popoverInstance.hide();
								$('div[class*="bs-popover"]').remove();
							}
						}
					});
					[].slice.call(document.querySelectorAll('div[class*="bs-tooltip"]')).map(function (tooltipEl) {
						if(tooltipEl) {
							let tooltipInstance = bootstrap.Tooltip.getInstance(tooltipEl);
							if(tooltipInstance) {
								tooltipInstance.hide();
							}
							$(tooltipEl).remove();
						}
					});
				}
			});
			
			// First Fancybox content type for XML sitemaps format generation/export
			if($('a.fancybox').length) {
				$("a.fancybox").fancybox({
					beforeClose : function () {
						[].slice.call(document.querySelectorAll('#xmlsitemap_export label.hasClickPopover')).map(function (popoverEl) {
							let popoverInstance = bootstrap.Popover.getInstance(popoverEl);
							if(popoverInstance) {
								popoverInstance.hide();
								$('div[class*="bs-popover"]').remove();
							}
						});
					}
				});
			}
			
			// Google maps Fancybox loading and instance
			if($('a.fancybox[data-role="opengmap"]').length) {
				$('a.fancybox[data-role="opengmap"]').fancybox({
					beforeLoad: function() {
						$('#gmap').show();
						map = new GMaps({
					        div: '#gmap',
					        lat: 40.730610,
					        lng: -73.935242,
					        zoom: 1
					      });
						
						GMaps.geocode({
							  address: jmap_geositemapAddress,
							  callback: function(results, status) {
							    if (status == 'OK') {
							      var latlng = results[0].geometry.location;
							      map.setCenter(latlng.lat(), latlng.lng());
							      map.addMarker({
							        lat: latlng.lat(),
							        lng: latlng.lng()
							      });
							      map.setZoom(10);
							    }
							  }
							});
					},
					afterClose : function () {
						$('#gmap').remove();
						$('a[data-role=opengmap]').after('<div id="gmap"></div>');
					},
					title : jmap_geositemapAddress
				});
			}
			
			if($('a.fancybox.rss').length) {
				$("a.fancybox.rss").fancybox({
					minWidth: '680',
					afterLoad:function(upcoming) {
						$('div.fancybox-mobile').addClass('fancybox_rss')
						$('div.fancybox-mobile div.fancybox-inner').addClass('fancybox_rss');
					}
				});
			}
			
			if($('a.fancybox_iframe').length) {
				$("a.fancybox_iframe").fancybox({
					type:'iframe',
					minWidth: '300',
					maxWidth: '800',
					minHeight: '640',
					maxHeight: '640',
					afterLoad:function(upcoming){
						$($('iframe[id^=fancybox]')).attr('scrolling','no');
					}
				});
			}
			
			$('#fancy_closer').on('click', function(){
				parent.jQuery.fancybox.close();
				return false;
			});
			
			[].slice.call(document.querySelectorAll('label.hasRobotsPopover')).map(function (popoverEl) {
				return new bootstrap.Popover(popoverEl,{
					template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'hover',
					placement:'bottom'
				});
			});
			
			// Pinger window open to win on iframe crossorigin limitations
			$(document).on('click', 'a.pinger', function(jqEvent){
				// Prevent open link
				jqEvent.preventDefault();
				
				// Simple window post to the search engine for HTML submission
				if($(this).data('type') != 'rpc') {
					var thisLinkToPing = $(this).attr('href');
					if(thisLinkToPing.indexOf('google') != -1) {
						context.googleSitemapPing(this);
					}
					if(thisLinkToPing.indexOf('yandex') != -1) {
						window.open(thisLinkToPing, 'pingwindow', 'width=800,height=480');
					}
				} else {
					var thisLinkToPing = $(this).attr('href');
					if(thisLinkToPing.indexOf('baidu') != -1) {
						context.rpcSitemapPing(this);
					}
					if(thisLinkToPing.indexOf('bing') != -1) {
						context.indexnowSitemapPing(this);
					}
				}
				return false;
			});
			
			// Label to manage saveEntity on sitemap model
			$(document).on('click', 'label[data-role=saveentity]', function() {
				// Trigger JS app processing to create root sitemap file
				var ajaxTargetLink = $(this).prevAll('input').val();
				// Start model ajax saveEntity
				context.openSaveEntityProgress(ajaxTargetLink);
			});
			
			// Robots add entries
			$('#robots_adder').on('click', {bind:this}, function(jqEvent){
				jqEvent.preventDefault();
				
				// Call the adder callback
				jqEvent.data.bind.addRobotsEntry(); 
			});
			
			// Component updater ignition start
			$(document).on('click', '#updatebtn', function(jqEvent){
				context.performComponentUpdate();
			});
			
			// Pinger window open to win on iframe crossorigin limitations
			$(document).on('click', '#xmlsitemap_export a.writerbutton', function(jqEvent){
				// Prevent open link
				jqEvent.preventDefault();
				
				context.writeSitemapFile(this);
				
				return false;
			});
			
			// Support for copy Clipoard buttons, new API and legacy API
			if(navigator.clipboard) {
				$(document).on('click', '#jmap_seo div.single_container.xmlcontainer label.bg-primary,#xmlsitemap_export label.bg-primary,#rssfeed label.bg-primary', function(jqEvent){
					navigator.clipboard.writeText($(jqEvent.target).nextAll('input.sitemap_links').val())
					.then(function() {
						let tooltipInstance = new bootstrap.Tooltip($(jqEvent.target).parent('.xmlcontainer').get(0),{
							trigger : 'click', 
							placement : 'top',
							title : COM_JMAP_COPIED_LINK,
							template: '<div class="tooltip jmap_copy_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
						});
						
						tooltipInstance.show();
						
						setTimeout(function(){
							tooltipInstance.dispose();
						}, 2000);
					})
					.catch(function(err) {
					});
					return false;
				});
			} else {
				$(document).on('click', '#jmap_seo div.single_container.xmlcontainer label.bg-primary,#xmlsitemap_export label.bg-primary,#rssfeed label.bg-primary', function(jqEvent){
					try {
						var placeholderInput = $(jqEvent.target).nextAll('input.sitemap_links').get(0).select();  
						// Now that we've selected the text, execute the copy command  
						var successful = document.execCommand('copy');  
						if(successful) {
							let tooltipInstance = new bootstrap.Tooltip($(jqEvent.target).parent('.xmlcontainer').get(0),{
								trigger : 'click', 
								placement : 'top',
								title : COM_JMAP_COPIED_LINK,
								template: '<div class="tooltip jmap_copy_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
							});
							
							tooltipInstance.show();
							
							setTimeout(function(){
								tooltipInstance.dispose();
							}, 2000);
						}
						// Remove the selections - NOTE: Should use
						// removeRange(range) when it is supported  
						window.getSelection().removeAllRanges();  
					} catch(err) {  
					}
					return false;
				});
			}
		},
	
		/**
		 * Refresh input link types and a types inside lightbox
		 * 
		 * @access public
		 * @method prototype
		 * @param String value the language value selected
		 * @return void 
		 */
		refreshCtrls : function(elem, value) {
			// Controls->param mapping intelligent append/replace
			var controlParamMapper = {'language_option':'&lang=',
									  'menu_datasource_filters':'&Itemid=',
									  'datasets_filters':'&dataset='
									 };
			var mappedQueryStringParam = controlParamMapper[$(elem).attr('id')]; 
			
			// Inject default option
			$(this.targetSelector).each(function(index, item) {
				switch($(item).prop('tagName').toLowerCase()) {
					case 'a':
						var appendValue = '';
						// If chosen valid language
						if(value) {
							if($(item).attr('data-role') == 'pinger') {
								appendValue = encodeURIComponent(mappedQueryStringParam + value);
							} else {
								appendValue = mappedQueryStringParam + value;
							}
						}
						
						var currentValue = $(item).attr('href');
						// Existing param
						if(currentValue.match(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"))) {
							if($(item).data('language') == 1 && $(elem).attr('id') == 'language_option') {} else {
								currentValue = currentValue.replace(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"), appendValue);
							}
						} else {
							// Case new param appended
							if($(item).data('language') == 1 && $(elem).attr('id') == 'language_option') {
								var defaultSiteLanguage = $($('option', elem).get(0)).val();
								currentValue = currentValue + (mappedQueryStringParam + defaultSiteLanguage);
							} else {
								currentValue = currentValue + appendValue;
							}
						}
						
						// Resetting value
						$(item).attr('href', currentValue);
						
						// Propagate language flags
						if(elem && elem.id == 'language_option' && !$(item).data('language') && $(item).data('role') != 'pinger') {
							var langValue = $(elem).val().replace('-', '_');
							$('img[data-role=jmap_langflag]', item).remove();
							$(item).append('<img data-role="jmap_langflag" src="' + jmap_baseURI + 'media/mod_languages/images/' + langValue + '.gif" alt="flag"/>');
						}
					break;
					
					case 'input':
					default: 
						var appendValue = '';
						// If chosen valid language
						if(value) {
							if($(item).attr('data-role') == 'pinger') {
								appendValue = encodeURIComponent(mappedQueryStringParam + value);
							} else {
								appendValue = mappedQueryStringParam + value;
							}
						}
						var currentValue = $(item).val();
						
						// If auto versioning reset version parameter
						if(jmap_linksRandom) {
							currentValue = currentValue.replace(new RegExp(".ver=\\d+", "gi"), '');
						}
						
						// If auto append extra format query string parameter reset it
						if(jmap_forceFormat && $(item).attr('data-role') == 'sitemap_links_sef' && !$(item).attr('data-html')) {
							currentValue = currentValue.replace(new RegExp(".format=.+", "gi"), '');
						}
						
						// Manage double mode for no-SEF or SEF links
						if($(item).attr('data-role') == 'sitemap_links_sef') {
							switch($(elem).attr('id')) {
								case 'language_option':
									var regexString = typeof(jmap_sef_alias_links) !== 'undefined' ? jmap_sef_alias_links : 'component';
									// Existing param
									if(currentValue.match(new RegExp("http.*/.{2}/" + regexString + "|http.*/.{2}-.{2}/" + regexString, "i"))) {
										// If it's required to remove the default language code for a multilanguage website, leave it unaltered
										if(jmap_default_language_code) {
											if(jmap_default_language_code == value) {
												currentValue = currentValue.replace(new RegExp(".{2}/" + regexString + "|.{2}-.{2}/" + regexString, "i"), '' + regexString);
											} else {
												if($(item).data('language') != 1) {
													currentValue = currentValue.replace(new RegExp(".{2}/" + regexString + "|.{2}-.{2}/" + regexString, "i"), value + '/' + regexString);
												}
											}
										} else {
											if($(item).data('language') != 1) {
												currentValue = currentValue.replace(new RegExp(".{2}/" + regexString + "|.{2}-.{2}/" + regexString, "i"), value + '/' + regexString);
											}
										}
									} else {
										// If it's required to remove the default language code for a multilanguage website, leave it unaltered
										if(jmap_default_language_code) {
											if(jmap_default_language_code == value) {
												currentValue = currentValue;
											} else {
												// Case new param appended
												if($(item).data('language') != 1) {
													currentValue = currentValue.replace(new RegExp(regexString, "i"), value + '/' + regexString);
												}
											}
										} else {
											// Case new param appended
											if($(item).data('language') != 1) {
												currentValue = currentValue.replace(new RegExp(regexString, "i"), value + '/' + regexString);
											} else {
												var defaultSiteLanguage = $($('option', elem).get(0)).val();
												currentValue = currentValue.replace(new RegExp(regexString, "i"), defaultSiteLanguage + '/' + regexString);
											}
										}
									}
									break;
									
								case 'menu_datasource_filters':
										// Existing param
										if(currentValue.match(new RegExp("Itemid", "gi"))) {
											if(value) {
												currentValue = currentValue.replace(new RegExp("Itemid=\\d+", "gi"), 'Itemid=' + value);
											} else {
												currentValue = currentValue.replace(new RegExp("\\?Itemid=\\d+", "gi"), '');
											}
										} else {
											// Case new param appended
											if(value) {
												currentValue = currentValue + '?Itemid=' + value;
											}
										}
									break;
								case 'datasets_filters':
									// Existing param
									if(currentValue.match(new RegExp("dataset", "gi"))) {
										if(value) {
											currentValue = currentValue.replace(new RegExp("\\d+-dataset", "gi"), value + '-dataset');
										} else {
											currentValue = currentValue.replace(new RegExp("/\\d-formatted/\\d+-dataset", "gi"), '');
										}
									} else {
										// Case new param appended
										if(value) {
											if($(item).attr('data-html') == 1 && currentValue.indexOf('sitemap/html') == -1) {
												if(currentValue.indexOf('/component/jmap') == -1) {
													currentValue = currentValue + '/sitemap/html/0-formatted/' + value + '-dataset';
												} else {
													currentValue = currentValue + '/html/0-formatted/' + value + '-dataset';
												}
											} else {
												currentValue = currentValue + '/0-formatted/' + value + '-dataset';
											}
										}
									}
								break;
							}
							var currentDataValueNoSef = $(item).attr('data-valuenosef');
							// Existing param
							if(currentDataValueNoSef.match(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"))) {
								$(item).attr('data-valuenosef', currentDataValueNoSef.replace(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"), appendValue));
							} else {
								// Case new param appended
								$(item).attr('data-valuenosef', currentDataValueNoSef + appendValue);
							}
						} else {
							// Existing param
							if(currentValue.match(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"))) {
								if($(item).data('language') == 1 && $(elem).attr('id') == 'language_option') {} else {
									currentValue = currentValue.replace(new RegExp(mappedQueryStringParam + "[^&.]+", "gi"), appendValue);
								}
							} else {
								// Case new param appended
								if($(item).data('language') == 1 && $(elem).attr('id') == 'language_option') {
									var defaultSiteLanguage = $($('option', elem).get(0)).val();
									currentValue = currentValue + (mappedQueryStringParam + defaultSiteLanguage);
								} else {
									currentValue = currentValue + appendValue;
								}
							}
						}
						
						// Auto append extra query string param for sitemap versioning AKA force GWT cache to refresh 
						if(jmap_linksRandom) {
							// Already a query string?
							if(currentValue.match(new RegExp("\\?", "gi"))) {
								currentValue += '&ver=' + Math.floor((Math.random() * 10000) + 1);
							} else {
								// New query string append
								currentValue += '?ver=' + Math.floor((Math.random() * 10000) + 1);
							}
						}
						
						// Auto append extra format query string parameter
						if(jmap_forceFormat && $(item).attr('data-role') == 'sitemap_links_sef' && !$(item).attr('data-html')) {
							var linkFormat = $(item).data('valuenosef').match(/format=([a-z]+)/i);
							// Already a query string?
							if(currentValue.match(new RegExp("\\?", "gi"))) {
								currentValue += '&format=' + linkFormat[1];
							} else {
								// New query string append
								currentValue += '?format=' + linkFormat[1];
							}
						}
						
						// Resetting value
						$(item).val(currentValue);
						$(item).attr('value', currentValue);
					break;
				}
	  		}); 
		},
		
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @param String ajaxLink
		 * @return void 
		 */
		openSaveEntityProgress : function(ajaxLink) {
			var context = this;

			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="progressModal1" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_ROBOTSPROGRESSTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay').fadeOut();
			
			var modalOptions = {
					backdrop:'static'
				};
			
			let modalEl = document.querySelector('#progressModal1');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();
			
			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#progressModal1 div.modal-body').css({'width':'95%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_ROBOTSPROGRESSSUBTITLE + '</p>');
				
				setTimeout(function(){
					context.modelSaveEntity(ajaxLink).always(function(){
						if(this.modelSaveEntitySuccess) {
							// Set 100% for progress
							$('#progressBar1').css({'width':'100%'});
							// Append exit message
							$('#progressInfo1').append('<p>' + COM_JMAP_ROBOTSPROGRESSSUBTITLESUCCESS + '</p>');
							setTimeout(function(){
								// Remove all
								let modalEl = document.querySelector('#progressModal1');
								if(modalEl) {
									bootstrap.Modal.getInstance(modalEl).hide();
								}
							}, 3000);
						} else {
							// Set 100% for progress
							$('#progressBar1').css({'width':'100%'}).addClass('bg-danger');
							// Append exit message
							$('#progressInfo1').append('<p>' + COM_JMAP_ROBOTSPROGRESSSUBTITLEERROR + '</p>');
							setTimeout(function(){
								// Remove all
								let modalEl = document.querySelector('#progressModal1');
								if(modalEl) {
									bootstrap.Modal.getInstance(modalEl).hide();
								}
							}, 3000);
						}
					});
				}, 500);
			});
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal', function(event) {
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay').fadeIn();
			});
		},
		
		/**
		 * Switch ajax submit form to model business logic
		 * 
		 * @access private
		 * @param String ajaxLink
		 * @return Promise
		 */
		modelSaveEntity : function(ajaxLink) {
			// Extra object to send to server
			var ajaxParams = { 
					idtask : 'robotsSitemapEntry',
					template : 'json',
					param: ajaxLink
			     };
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxParams); 

			// Request JSON2JSON
			return $.ajax({
		        type: "POST",
		        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
		        dataType: 'json',
		        context: this,
		        async: true,
		        data: {data : uniqueParam } , 
		        success: function(data, textStatus, jqXHR)  {
					// Set result value
					this.modelSaveEntitySuccess = data.result;
					// If errors found inside model working
					if(!this.modelSaveEntitySuccess && data.errorMsg) {
						$('#progressInfo1').append('<p>' + data.errorMsg + '</p>');
					}
	            },
				error: function(jqXHR, textStatus, error){
					// Append error details
					$('#progressInfo1').append('<p>' + error.message + '</p>');
				}
			}); 
		},
		
		 /**
		 * Interact with ChartJS lib to generate charts
		 * 
		 * @access private
		 * @return Void
		 */
        generateLineChart : function(animation) {
        	var bind = this;
        	// Instance Chart object lib
        	var chartJS = new JMapChart(this.canvasContext);
        	
        	// Max value encountered
        	var maxValue = 9;
        	
        	// Normalize chart data to render
        	this.constructor.prototype.chartData.labels = new Array();
        	this.constructor.prototype.chartData.datasets = new Array();
        	var subDataSet = new Array();
            $.each(jmapChartData, function(label, value){
            	var labelSuffix = label.replace(/([A-Z])/g, "_$1").toUpperCase()
            	bind.constructor.prototype.chartData.labels[bind.chartData.labels.length] = eval('COM_JMAP_' + labelSuffix + '_CHART');;
            	subDataSet[subDataSet.length] = value = parseInt(value);
            	if(value > maxValue) {
            		maxValue = value;
            	}
            });
            
            // Override scale
            this.constructor.prototype.chartOptions.scaleStepWidth = 10;
            if((maxValue / 100) > 0) {
            	var multiplier = parseInt(maxValue / 100);
            	this.constructor.prototype.chartOptions.scaleStepWidth = 10 + (multiplier * 10);
            }
            this.constructor.prototype.chartOptions.scaleSteps = parseInt((maxValue / this.chartOptions.scaleStepWidth) + 1);
            
            this.constructor.prototype.chartData.datasets[0] = {
            		fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					data : subDataSet
            };
        	
            // Override options
            this.constructor.prototype.chartOptions.animation = animation;
            
            // Paint chart on canvas
        	chartJS.Line(this.chartData, this.chartOptions);
        },
        
        /**
		 * Make fluid canvas width with repaint on resize
		 * 
		 * @access private
		 * @return Void
		 */
        resizeRepaintCanvas : function(animation) {
        	// Get HTMLCanvasElement
            var canvas = $('#chart_canvas').get(0);
            var visibilityParent = $(canvas).parents('#jmap_status').css('display');
            if(visibilityParent == 'none') {
            	return;
            }
            // Get parent container width
            var containerWidth = $(canvas).parent().width();
            // Set dinamically canvas width
            canvas.width  = containerWidth;
            $(canvas).css('min-width', canvas.width);
            canvas.height = 170;
            // Repaint canvas contents
            this.generateLineChart(animation);
        },
        
		/**
		 * Make fluid canvas width with repaint on resize
		 * 
		 * @access private
		 * @return Void
		 */
        addRobotsEntry : function() {
        	// Reuse snippets
			var validationSnippet = '<ul class="errorlist"><li class="validation badge bg-danger">' + COM_JMAP_ROBOTS_REQUIRED + '</li></ul>';
			var messageSnippet =    '<div class="robots_messages alert alert-success">' +
										'<h4 class="alert-heading">Message</h4>' +
										'<p>' + COM_JMAP_ROBOTS_ENTRY_ADDED + '</p>' +
									'</div>';

        	// Retrieve values
			var robotsRule = $('#robots_rule').val();
			var robotsEntry = $('#robots_entry').val();
			
			if(robotsEntry) {
				// Append text to the text area
				$('#robots_contents').val(function(_, val){
					return val + '\n' + robotsRule + robotsEntry; 
				});
				
				// Scroll to bottom the textarea
				$("#robots_contents").scrollTop($("#robots_contents")[0].scrollHeight);
				
				// Reset value
				$('#robots_entry').val('');
				
				// Append message
				$('#system-message-container').html(messageSnippet);
				setTimeout(function(){
					$('.robots_messages').fadeOut(500, function(){
						$(this).remove();
					});
				},1000);
			} else {
				$('#robots_entry').next('ul').remove().end().after(validationSnippet);
				$('#robots_entry').addClass('error');
				
				$('#robots_entry').on('keyup', function(jqEvent){
					$(this).removeClass('error');
					$(this).next('ul').remove();
				});
			}
        },
        
		/**
		 * Perform the remote check to validate the updates status license
		 * If the license is valid the update button will be shown
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		checkUpdatesLicenseStatus : function() {
			var updateSnippet = this.updateButtonSnippet;
			var replacements = {"%EXPIREON%":""};

			// Is there an outdated status?
			if($(this.updateStatusSelector).length) {
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'getLicenseStatus',
						param: {}
				     };
				// Unique param 'data'
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Request JSON2JSON
				$.ajax({
			        type: "POST",
			        url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
			        dataType: 'json',
			        context: this,
			        async: true,
			        data: {data : uniqueParam } , 
			        success: function(data, textStatus, jqXHR)  {
						// If the updates informations are successful go on, ignore every error condition
						if(data.success) {
							replacements = {"%EXPIREON%":data.expireon};
							
							updateSnippet = updateSnippet.replace(/%\w+%/g, function(all) {
								   return replacements[all] || all;
								});
							
							// Now append the update button beside the status label
							$(this.updateStatusSelector).parent().after(updateSnippet);
							
							// Apply the popover
							new bootstrap.Popover(document.querySelector('#updatebtn'),{
								template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
								trigger:'hover',
								placement:'right'
							});
						}
		            }
				}); 
			}
		},
		
		/**
		 * Start the managed update process of the componenent showing 
		 * progress bar and error messages to the user
		 * 
		 * @access public
		 * @property prototype
		 * @return void 
		 */
		performComponentUpdate : function() {
			var context = this;
			
			// Delete update old SEO Stats
			if(window.sessionStorage !== null){
				sessionStorage.removeItem('seostats');
				sessionStorage.removeItem('seostats_service');
				sessionStorage.removeItem('seostats_targeturl');
			}
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="progressModal1" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_UPDATEPROGRESSTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : false
				};
			
			let modalEl = document.querySelector('#progressModal1');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();
			
			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#progressModal1 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_DOWNLOADING_UPDATE_SUBTITLE + '</p>');
				
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'downloadComponentUpdate',
						param: {}
				};
				var uniqueParam = JSON.stringify(ajaxParams); 
				
				// Requests JSON2JSON chained
				var chained = $.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
					type : "POST",
					data : {
						data : uniqueParam
					},
					dataType : "json"
				}).then(function(data) {
					$('#progressBar1').css({'width':'75%'});
					// Inform user process initializing
					$('#progressInfo1').empty().append('<p>' + COM_JMAP_INSTALLING_UPDATE_SUBTITLE + '</p>');
					
					// Phase 1 OK, go with the next Phase 2
					if(data.result) {
						// Extra object to send to server
						var ajaxParams = { 
								idtask : 'installComponentUpdate',
								param: {}
						};
						var uniqueParam = JSON.stringify(ajaxParams); 
						return $.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
							type : "POST",
							data : {
								data : uniqueParam
							},
							dataType : "json"
						});
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBar1').css({'width':'100%'}).addClass('bg-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
						setTimeout(function(){
							// Remove all
							let modalEl = document.querySelector('#progressModal1');
							if(modalEl) {
								bootstrap.Modal.getInstance(modalEl).hide();
							}
						}, 3000);
						
						// Stop the chained promises
						return $.Deferred().reject();
					}
				});
				
				chained.done(function( data ) {
					// Data retrieved from url2 as provided by the first request
					if(data.result) {
						// Phase 2 OK, set 100% width and mark as completed the whole process
						$('#progressBar1').css({'width':'100%'}).addClass('bg-success');
						// Inform user process initializing
						$('#progressInfo1').empty().append('<p>' + COM_JMAP_COMPLETED_UPDATE_SUBTITLE + '</p>');
						
						// Now refresh page
						setTimeout(function(){
							window.location.reload();
						}, 1500);
					} else {
						// Set 100% for progress
						$('#progressBar1').css({'width':'100%'}).addClass('bg-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
						setTimeout(function(){
							// Remove all
							let modalEl = document.querySelector('#progressModal1');
							if(modalEl) {
								bootstrap.Modal.getInstance(modalEl).hide();
							}
						}, 3000);
					}
				});
			});
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
			});
		},
		
		/**
		 * Ping/submit the sitemap by AJAX using a remote XML-RPC service
		 * 
		 * @access public
		 * @property prototype
		 * @param HTMLElement element
		 * @return void 
		 */
		writeSitemapFile : function(element) {
			// Retrieve the url of the submitting sitemap clicked
			var sitemapLink = $(element).attr('href');
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="writingModal">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_WRITE_XML_WRITE + '</h4>' +
								        		'<label class="closewriting fas fa-times-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.writingProgress + '</p>' +
								        		'<p id="progressWriting"></p>' +
							        		'</div>' +
							        	'</div>' +
						        	'</div>' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay').fadeOut();
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : true
				};
			
			let modalEl = document.querySelector('#writingModal');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();

			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#writingModal div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBarWriting').css({'width':'50%'});
				// Inform user process initializing
				$('#progressWriting').empty().append('<p>' + COM_JMAP_WRITE_XML_WRITING + '</p>');
				
				// Requests JSON2JSON chained
				$.ajax(sitemapLink + '&cronjobclient=1', {
					type : "GET",
					dataType : 'xml',
					context : this
				}).then(function(data) {
					// Phase 1 OK, go with the next Phase 2
					if(data) {
						var filePath = $(data).find('filepath').text();
						var fileUrl = $(data).find('fileurl').text();
						
						$('#progressBarWriting').css({'width':'100%'}).addClass('bg-success');
						// Inform user process initializing
						$('#progressWriting').empty().append('<p>' + COM_JMAP_WRITE_XML_SITEMAP_WRITTEN.replace('%s', filePath) + '</p>');
						$('#progressWriting').append('<p>' + COM_JMAP_WRITE_XML_SITEMAP_URL + '<a target="_blank" href="' + fileUrl + '">' + fileUrl + '</a></p>');
						$('#progressBarWriting').removeClass('progress-bar-striped progress-bar-animated');
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBarWriting').css({'width':'100%'}).addClass('bg-danger');
						// Append exit message
						$('#progressWriting').empty().append('<p>' + data.exception_message + '</p>');
					}
				});
			});
			
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay').fadeIn();
			});
			
			// Live event binding for close button AKA stop process
			$(document).on('click', 'label.closewriting', function(jqEvent){
				bootstrap.Modal.getInstance(document.querySelector('#writingModal')).hide();
			});
		},
		
		/**
		 * Ping/submit the sitemap by AJAX using a remote XML-RPC service
		 * 
		 * @access public
		 * @property prototype
		 * @param HTMLElement element
		 * @return void 
		 */
		rpcSitemapPing : function(element) {
			// Retrieve the url of the submitting sitemap clicked
			var URIs = $(element).attr('href').split('?');
			var sitemapLink = decodeURIComponent(URIs[1]);
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="pingingModal">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PINGING_SITEMAP_TOBAIDU + '</h4>' +
								        		'<label class="closepinging fas fa-times-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        	'</div>' +
						        	'</div>' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay, div.popover').fadeOut();
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : true
				};

			let modalEl = document.querySelector('#pingingModal');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();

			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#pingingModal div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOBAIDU_PLEASEWAIT + '</p>');
				
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'submitSitemapToBaidu',
						param: sitemapLink
				     };
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Requests JSON2JSON chained
				$.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
					type : "POST",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).then(function(data) {
					// Phase 1 OK, go with the next Phase 2
					if(data.result) {
						$('#progressBar1').css({'width':'100%'}).addClass('bg-success');
						$('#progressBar1').removeClass('progress-bar-striped progress-bar-animated');
						// Inform user process initializing
						$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOBAIDU_COMPLETE + '</p>');
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBar1').css({'width':'100%'}).addClass('bg-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
					}
					
					setTimeout(function(){
						// Remove all
						let modalEl = document.querySelector('#pingingModal');
						if(modalEl) {
							bootstrap.Modal.getInstance(modalEl).hide();
						}
					}, 2000);
				});
			});
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay, div.popover').fadeIn();
			});
			
			// Live event binding for close button AKA stop process
			$(document).on('click', 'label.closepinging', function(jqEvent){
				bootstrap.Modal.getInstance(document.querySelector('#pingingModal')).hide();
			});
		},

		/**
		 * Ping/submit the sitemap by AJAX using a remote IndexNow API for Bing
		 * 
		 * @access public
		 * @property prototype
		 * @param HTMLElement element
		 * @return void 
		 */
		indexnowSitemapPing : function(element) {
			// Retrieve the url of the submitting sitemap clicked
			var URIs = $(element).attr('href').split('?');
			var sitemapLink = decodeURIComponent(URIs[1]);
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="pingingModal">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PINGING_SITEMAP_TOBING + '</h4>' +
								        		'<label class="closepinging fas fa-times-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        	'</div>' +
						        	'</div>' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay, div.popover').fadeOut();
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : true
				};

			let modalEl = document.querySelector('#pingingModal');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();

			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#pingingModal div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOBING_PLEASEWAIT + '</p>');
				
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'submitSitemapToBing',
						param: sitemapLink
				     };
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Requests JSON2JSON chained
				$.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
					type : "POST",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).then(function(data) {
					// Phase 1 OK, go with the next Phase 2
					if(data.result) {
						$('#progressBar1').css({'width':'100%'}).addClass('bg-success');
						$('#progressBar1').removeClass('progress-bar-striped progress-bar-animated');
						// Inform user process initializing
						$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOBING_COMPLETE + '</p>');
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBar1').css({'width':'100%'}).addClass('bg-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
					}
					
					setTimeout(function(){
						// Remove all
						let modalEl = document.querySelector('#pingingModal');
						if(modalEl) {
							bootstrap.Modal.getInstance(modalEl).hide();
						}
					}, 2000);
				});
			});
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay, div.popover').fadeIn();
			});
			
			// Live event binding for close button AKA stop process
			$(document).on('click', 'label.closepinging', function(jqEvent){
				bootstrap.Modal.getInstance(document.querySelector('#pingingModal')).hide();
			});
		},
		
		/**
		 * Ping/submit the sitemap by AJAX using a remote IndexNow API for Bing
		 * 
		 * @access public
		 * @property prototype
		 * @param HTMLElement element
		 * @return void 
		 */
		googleSitemapPing : function(element) {
			// Retrieve the url of the submitting sitemap clicked
			var URIs = $(element).attr('href').split('?q=');
			var sitemapLink = decodeURIComponent(URIs[1]);
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="pingingModal">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PINGING_SITEMAP_TOGOOGLE + '</h4>' +
								        		'<label class="closepinging fas fa-times-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + this.firstProgress + '</p>' +
								        		'<p id="progressInfo1"></p>' +
							        		'</div>' +
							        	'</div>' +
						        	'</div>' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			// Remove fancybox overlay if added cronjob link
			$('div.fancybox-overlay, div.popover').fadeOut();
			
			var modalOptions = {
					backdrop : 'static',
					keyboard : true
				};

			let modalEl = document.querySelector('#pingingModal');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();

			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#pingingModal div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOGOOGLE_PLEASEWAIT + '</p>');
				
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'submitSitemapToGoogle',
						param: sitemapLink
				     };
				var uniqueParam = JSON.stringify(ajaxParams); 

				// Requests JSON2JSON chained
				$.ajax("../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json", {
					type : "POST",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).then(function(data) {
					// Phase 1 OK, go with the next Phase 2
					if(data.result) {
						$('#progressBar1').css({'width':'100%'}).addClass('bg-success');
						$('#progressBar1').removeClass('progress-bar-striped progress-bar-animated');
						// Inform user process initializing
						$('#progressInfo1').empty().append('<p>' + COM_JMAP_PINGING_SITEMAP_TOGOOGLE_COMPLETE + '</p>');
					} else {
						// Phase 1 KO, stop the process with error here and don't go on
						$('#progressBar1').css({'width':'100%'}).addClass('bg-danger');
						// Append exit message
						$('#progressInfo1').empty().append('<p>' + data.exception_message + '</p>');
					}
					
					setTimeout(function(){
						// Remove all
						let modalEl = document.querySelector('#pingingModal');
						if(modalEl) {
							bootstrap.Modal.getInstance(modalEl).hide();
						}
					}, 2000);
				});
			});
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
				// Recover fancybox overlay if added cronjob link
				$('div.fancybox-overlay, div.popover').fadeIn();
			});
			
			// Live event binding for close button AKA stop process
			$(document).on('click', 'label.closepinging', function(jqEvent){
				bootstrap.Modal.getInstance(document.querySelector('#pingingModal')).hide();
			});
		}
	});
 
	// Start JS application
	$.cpanelTasks = new CPanel('#language_option, #menu_datasource_filters, #datasets_filters', 'input[data-role=sitemap_links], input[data-role=sitemap_links_sef], a[data-role=pinger], a[data-role=torefresh], #xmlsitemap a[href*=sitemap], #xmlsitemap_xslt a[href*=sitemap], #xmlsitemap_export a[href*=sitemap], #rssfeed a[href*=sitemap], a.jmap_analyzer, a.jmap_metainfo, a.jmap_seospider');
});