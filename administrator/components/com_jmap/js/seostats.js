/**
 * Precaching client, this is the main application that interacts with server
 * side code for sitemap incremental generation and precaching process
 * 
 * @package JMAP::AJAXPRECACHING::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var SeoStats = function() {
		/**
		 * Add the loading waiter icon during asyncronous operations
		 * 
		 * @access private
		 * @param HTMLElement targetElement
		 * @return Void
		 */
		var addLoadingWaiter = function(targetElement) {
			var containerWidth = targetElement.width(); 

			// Append waiter and text fetching data in progress
			targetElement.append('<div class="waiterinfo"></div>')
				.children('div.waiterinfo')
				.text(COM_JMAP_SEOSTATS_LOADING)
				.css({
					'position': 'absolute',
		            'top': '125px',
		            'left': (containerWidth - (parseInt(containerWidth / 2) + 100) ) + 'px',
		            'width': '200px'
	        });
			
			targetElement.append('<img/>')
				.children('img')
				.attr({
					'src': jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif',
					'class': 'waiterinfo'})
				.css({
		            'position': 'absolute',
		            'top': '50px',
		            'left': (containerWidth - (parseInt(containerWidth / 2) + 32) ) + 'px',
		            'width': '64px'
	        });
		};
		
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @return Void
		 */
		var getSeoStatsData = function() {
			// Object to send to server
			var ajaxparams = {
				idtask : 'fetchSeoStats',
				template : 'json',
				param: {}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			// Request JSON2JSON
			var seoStatsPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(data === null) {
						// Error found
						defer.reject(COM_JMAP_NULL_RESPONSEDATA, 'notice');
						return false;
					}
					
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, data.errorlevel, data.seostats, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result && data.seostats) {
						defer.resolve(data.seostats);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1) + ': ' + jqXHR.status ;
					defer.reject(COM_JMAP_ERROR_HTTP + '-' + genericStatus + '- ' + errorThrown, 'error');
				});
			}).promise();

			seoStatsPromise.then(function(responseData) {
				// SEO stats correctly retrieved from the model layer, now updates the view accordingly
				formatSeoStats(responseData);
				
				// We have SEO stats, if sessionStorage is supported store data and avoid unuseful additional requests
				if( window.sessionStorage !== null ) {
					sessionStorage.setItem('seostats', JSON.stringify(responseData));
					sessionStorage.setItem('seostats_service', responseData.service);
					sessionStorage.setItem('seostats_targeturl', responseData.targeturl);
				}
			}, function(errorText, errorLevel, responseData, error) {
				// Do stuff and exit
				if(responseData) {
					formatSeoStats(responseData);
				}
				
				// Show little user error notification based on fatal php circumstances
				$('#seo_stats').append('<div class="alert alert-' + errorLevel + '">' + errorText + '</div>');
			}).always(function(){
				// Async request completed, now remove waiters info
				setTimeout(function(){
					$('*.waiterinfo').remove();
				}, 500);
			})
		};

		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @param String linkUrl
		 * @param HTMLElement clickedElement
		 * @param Object sessionStats
		 * @return Void
		 */
		var getCompetitorStatsData = function(linkUrl, clickedElement, sessionStats) {
			// Object to send to server
			var ajaxparams = {
				idtask : 'fetchCompetitorStats',
				template : 'json',
				param: linkUrl
			};

			// Add the loading waiter
			if(!sessionStats) {
				addLoadingWaiter($(clickedElement).parents('div.popover'));
			}
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			if(!sessionStats) {
				// Request JSON2JSON
				var competitorStatsPromise = $.Deferred(function(defer) {
					$.ajax({
						type : "POST",
						url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
						dataType : 'json',
						context : this,
						data : {
							data : uniqueParam
						}
					}).done(function(data, textStatus, jqXHR) {
						if(data === null) {
							// Error found
							defer.reject(COM_JMAP_NULL_RESPONSEDATA, 'notice');
							return false;
						}
						
						if(!data.result) {
							// Error found
							defer.reject(data.exception_message, data.errorlevel, textStatus);
							return false;
						}
						
						// Check response all went well
						if(data.result && data.competitorstats) {
							defer.resolve(data.competitorstats);
						}
					}).fail(function(jqXHR, textStatus, errorThrown) {
						// Error found
						var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1) + ': ' + jqXHR.status ;
						defer.reject(COM_JMAP_ERROR_HTTP + '-' + genericStatus + '- ' + errorThrown, 'error');
					});
				}).promise();
			} else {
				// Found session stats, create and resolve the promise immediately
				var competitorStatsPromise = $.Deferred(function(defer) {
					defer.resolve(sessionStats, true);
				}).promise();
			}

			competitorStatsPromise.then(function(responseData, bySession) {
				// Competitor stats correctly retrieved from the model layer, now updates the view accordingly
				// Enables bootstrap popover
				let popoverInstance = new bootstrap.Popover(clickedElement, {
					placement: 'top',
					title: COM_JMAP_COMPETITOR_STATS + linkUrl,
					html: true,
					container: 'body',
					trigger: 'manual',
					content: function() {
						// Format data only if are not already formatted and stored by session
						if(!bySession) {
							if(!isNaN(parseInt(responseData.googlepages))) {
								responseData.googlepages = parseInt(responseData.googlepages).toLocaleString().replace(/,/g, '.');
							}
							
							if(!isNaN(parseInt(responseData.googlebacklinks))) {
								responseData.googlebacklinks = parseInt(responseData.googlebacklinks).toLocaleString().replace(/,/g, '.');
							}
							
							if(!isNaN(parseInt(responseData.googlerelated))) {
								responseData.googlerelated = parseInt(responseData.googlerelated).toLocaleString().replace(/,/g, '.');
							}
							
							if(!isNaN(parseInt(responseData.bingpages))) {
								responseData.bingpages = parseInt(responseData.bingpages).toLocaleString().replace(/,/g, '.');
							}
							
							if(!isNaN(parseInt(responseData.bingbacklinks))) {
								responseData.bingbacklinks = parseInt(responseData.bingbacklinks).toLocaleString().replace(/,/g, '.');
							}
						}
						
						return 	'<div class="competitor-stats-container">' +
									'<div class="competitor-stats-row">' +
										'<span class="badge bg-primary">' + COM_JMAP_COMPETITOR_GOOGLE_PAGES + '<span class="badge pull-right">' + responseData.googlepages + '</span></span>' +
										'<span class="badge bg-primary">' + COM_JMAP_COMPETITOR_GOOGLE_BACKLINKS + '<span class="badge pull-right">' + responseData.googlebacklinks + '</span></span>' +
										'<span class="badge bg-primary">' + COM_JMAP_COMPETITOR_GOOGLE_RELATED + '<span class="badge pull-right">' + responseData.googlerelated + '</span></span>' +
										'<span class="badge bg-primary">' + COM_JMAP_COMPETITOR_BING_PAGES + '<span class="badge pull-right">' + responseData.bingpages + '</span></span>' +
										'<span class="badge bg-primary">' + COM_JMAP_COMPETITOR_BING_BACKLINKS + '<span class="badge pull-right">' + responseData.bingbacklinks + '</span></span>' +
							        '</div>' +
								'</div>';
					}				
				});
				popoverInstance.show();
				
				// We have SEO stats, if sessionStorage is supported store data and avoid unuseful additional requests
				if( window.sessionStorage !== null && !bySession) {
					sessionStorage.setItem('competitorstats.' + linkUrl, JSON.stringify(responseData));
				}
			}, function(errorText, errorLevel, error) {
				// Do stuff and exit
				let popoverInstance = new bootstrap.Popover(clickedElement, {
					placement: 'top',
					title: COM_JMAP_COMPETITOR_STATS + linkUrl,
					html: true,
					container: 'body',
					trigger: 'manual',
					content: function() {
						return '<div class="alert alert-' + errorLevel + '">' + errorText + '</div>';
					}
				});
					
				popoverInstance.show();
			}).always(function(){
				// Bind the popover closing
				$('body').on('click', function(jqEvent) {
					if($(jqEvent.target).parents('div.competitor-stats-container').length) {
						return false;
					}
					let popoverInstance = bootstrap.Popover.getInstance(clickedElement);
					if(popoverInstance) {
						popoverInstance.dispose();
					}
				});
				
				// Async request completed, now remove waiters info
				$('*.waiterinfo').remove();
			});
		};

		/**
		 * Format the Seo Stats
		 * 
		 * @access private
		 * @return Void
		 */
		var formatStatscropSeoStats = function(seoStats) {
			// Statscrop rank
			var statscropRankFontSize = '';
			var statscropRankFormatted = seoStats.globalrank;
			if(!isNaN(parseInt(statscropRankFormatted))) {
				statscropRankFormatted = parseInt(statscropRankFormatted).toLocaleString().replace(/,/g, '.');
				if(statscropRankFormatted.length > 7) {
					statscropRankFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{global_rank\\}]').html('<span' + statscropRankFontSize + '>' + statscropRankFormatted + '</span>');
			
			// Daily visitors
			var dailyVisitorsFontSize = '';
			var dailyVisitorsFormatted = seoStats.dailyvisitors;
			if(!isNaN(parseInt(dailyVisitorsFormatted))) {
				dailyVisitorsFormatted = parseInt(dailyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(dailyVisitorsFormatted.length > 7) {
					dailyVisitorsFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{daily_visitors\\}]').html('<span' + dailyVisitorsFontSize + '>' + dailyVisitorsFormatted + '</span>');
			

			// Daily Page Views
			// Daily visitors
			var bounceRateFontSize = '';
			var bounceRateFormatted = seoStats.bouncerate;
			if(!isNaN(parseInt(bounceRateFormatted))) {
				bounceRateFormatted = parseFloat(bounceRateFormatted).toLocaleString().replace(/,/g, '.');
				if(bounceRateFormatted.length > 7) {
					bounceRateFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{daily_pageviews\\}]').html('<span' + bounceRateFontSize + '>' + bounceRateFormatted + '</span>');
			
			$('li[data-bind=\\{page_load_time\\}]').html(seoStats.pageloadtime);

			var starsNumericalChunk = seoStats.openpagerank;
			var intStars = parseInt(starsNumericalChunk);
			var floatTotal = parseFloat(starsNumericalChunk);
			var restEmptyStars = parseInt(10 - floatTotal);
			var fullStars = '';
			for (var i = 0; i < intStars; i++) {
				fullStars += '<span class="fas fa-star"></span>';
			}
			var halfStar = (floatTotal - intStars) > 0 ? '<span class="fas fa-star-half-alt"></span>' : '';
			var emptyStars = '';
			for (var e = 0; e < restEmptyStars; e++) {
				emptyStars += '<span class="far fa-star"></span>';
			}
			$('li[data-bind=\\{rating_stars\\}]').html(fullStars + halfStar + emptyStars);
			
			// Open Page Rank
			$('li[data-bind=\\{openpagerank\\}]').html(seoStats.openpagerank);
			
			// Google SERP indexed links
			var indexedFontSize = '';
			if(seoStats.googleindexedlinks.length > 7) {
				indexedFontSize = ' style="font-size:18px"';
			}
			$('li[data-bind=\\{google_indexed_links\\}]').html('<span' + indexedFontSize + '>' + seoStats.googleindexedlinks + '</span>');
			
			// Website internal links
			if(typeof(seoStats.linksinternal) === 'object' && seoStats.linksinternal) {
				var linksInternal = '';
				$.each(seoStats.linksinternal.list['in'], function(index, linkInternalObject){
					// Limit top 30 linksinternal
					if((index + 1) > 30) {
						return false;
					}
					
					if(typeof(linkInternalObject.domain) !== 'undefined' && linkInternalObject.domain != '(Other)') {
						linksInternal += ('<div class="linkinternaldomain">' + linkInternalObject.domain + '</div>');
					}
				});
				var linksinternalListEl = $('li[data-bind=\\{links_internal\\}]');
				linksinternalListEl.attr('data-bs-content', linksInternal).addClass('clickable');
				var linksInternalLinksPopover = new bootstrap.Popover(linksinternalListEl.get(0),{
					template : '<div class="popover statscrop_linksinternal_list_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
				$(document).on('click', 'div.statscrop_linksinternal_list_popover div.linkinternaldomain', function(jqEvent){
					var linkUrl = $(this).text();
					var internalinksStatsInSession = false;
					if( window.sessionStorage !== null ) {
						internalinksStatsInSession = JSON.parse(sessionStorage.getItem('competitorstats.' + linkUrl));
					}
					getCompetitorStatsData(linkUrl, this, internalinksStatsInSession);
					return false;
				});
			}
			
			// Website external links
			if(typeof(seoStats.linksexternal) === 'object' && seoStats.linksexternal) {
				var linksExternal = '';
				$.each(seoStats.linksexternal.list['out'], function(index, linkExternalObject){
					// Limit top 30 linksexternal
					if((index + 1) > 30) {
						return false;
					}
					
					if(typeof(linkExternalObject.domain) !== 'undefined' && linkExternalObject.domain != '(Other)') {
						linksExternal += ('<div class="linkexternaldomain">' + linkExternalObject.domain + '</div>');
					}
				});
				var linksexternalListEl = $('li[data-bind=\\{links_external\\}]');
				linksexternalListEl.attr('data-bs-content', linksExternal).addClass('clickable');
				var linksExternalLinksPopover = new bootstrap.Popover(linksexternalListEl.get(0),{
					template : '<div class="popover statscrop_linksexternal_list_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
				$(document).on('click', 'div.statscrop_linksexternal_list_popover div.linkexternaldomain', function(jqEvent){
					var linkUrl = $(this).text();
					var externalinksStatsInSession = false;
					if( window.sessionStorage !== null ) {
						externalinksStatsInSession = JSON.parse(sessionStorage.getItem('competitorstats.' + linkUrl));
					}
					getCompetitorStatsData(linkUrl, this, externalinksStatsInSession);
					return false;
				});
			}
			
			// Keywords
			if(typeof(seoStats.keywords) === 'object' && seoStats.keywords) {
				var statsTags = '';
				$.each(seoStats.keywords.list, function(index, tagObject){
					// Limit top 30 keywords
					if((index + 1) > 30) {
						return false;
					}
					
					if(typeof(tagObject.keyword) !== 'undefined' && tagObject.keyword != '(Other)') {
						statsTags += ('<div>' + tagObject.keyword + ' / ' + tagObject.percent + '</div>');
					}
				});
				var keywordsListEl = $('li[data-bind=\\{keywords\\}]');
				keywordsListEl.attr('data-bs-content', statsTags).addClass('clickable');
				new bootstrap.Popover(keywordsListEl.get(0),{
					template : '<div class="popover statscrop_keywords_list_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
			}
			
			// Backlinkers
			if(typeof(seoStats.backlinkers) === 'object' && seoStats.backlinkers) {
				var statsBacklinkers = '';
				$.each(seoStats.backlinkers.list, function(index, domainObject){
					// Limit top 10 domains
					if((index + 1) > 10) {
						return false;
					}
					
					if(typeof(domainObject.domain) !== 'undefined') {
						statsBacklinkers += ('<div class="backlinker">' + domainObject.domain + '</div>');
					}
				});
				var backlinkersEl = $('li[data-bind=\\{backlinkers\\}]');
				backlinkersEl.attr('data-bs-content', statsBacklinkers).addClass('clickable');
				new bootstrap.Popover(backlinkersEl.get(0),{
					template : '<div class="popover statscrop_backlinkers_list_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
				$(document).on('click', 'div.statscrop_backlinkers_list_popover div.backlinker', function(jqEvent){
					var linkUrl = $(this).text();
					var backlinkerStatsInSession = false;
					if( window.sessionStorage !== null ) {
						backlinkerStatsInSession = JSON.parse(sessionStorage.getItem('competitorstats.' + linkUrl));
					}
					getCompetitorStatsData(linkUrl, this, backlinkerStatsInSession);
					return false;
				});
			}
			
			// Countries
			if(typeof(seoStats.countries) === 'object' && seoStats.countries) {
				var statsCountries = '';
				$.each(seoStats.countries.list, function(index, countryObject){
					// Limit top 30 keywords
					if((index + 1) > 30) {
						return false;
					}
					
					if(typeof(countryObject.name) !== 'undefined' && countryObject.name != '(Other)') {
						statsCountries += ('<div>' + countryObject.name + ' / ' + countryObject.percent + '</div>');
					}
				});
				var countriesListEl = $('li[data-bind=\\{countries\\}]');
				countriesListEl.attr('data-bs-content', statsCountries).addClass('clickable');
				new bootstrap.Popover(countriesListEl.get(0),{
					template : '<div class="popover statscrop_countries_list_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
			}
			
			// Subdomains
			if(typeof(seoStats.subdomains) === 'object' && seoStats.subdomains) {
				var subdomainInternal = '';
				$.each(seoStats.subdomains.list, function(index, subdomainObject){
					// Limit top 30 subdomains
					if((index + 1) > 30) {
						return false;
					}
					
					if(typeof(subdomainObject.subdomain) !== 'undefined' && subdomainObject.subdomain != '(Other)') {
						subdomainInternal += ('<div>' + subdomainObject.subdomain + ' / ' + subdomainObject.percent + '</div>');
					}
				});
				var subdomainsListEl = $('li[data-bind=\\{subdomains\\}]');
				subdomainsListEl.attr('data-bs-content', subdomainInternal).addClass('clickable');
				var subdomainInternalLinksPopover = new bootstrap.Popover(subdomainsListEl.get(0),{
					template : '<div class="popover statscrop_subdomains_list_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
			}
			
			// Build the traffic graph, get target canvas context 2d to render chart
        	if(!!document.createElement('canvas').getContext && $('#statscanvas').length) {
        		var generateStatsLineChart = function(animation) {
        			// Skip empty data
        			seoStatsTrafficgraphData = seoStats.trafficgraph.data;
        			
        			if(seoStatsTrafficgraphData && seoStatsTrafficgraphData.hasOwnProperty('length')) {
        				var arrayLength = seoStatsTrafficgraphData.length;
        				if(arrayLength == 0) {
        					$('div.seostatschart').remove();
        					return;
        				}
        			}
        			
        			if(seoStatsTrafficgraphData == null) {
        				$('div.seostatschart').remove();
    					return;
        			}
        			
        			// Get HTMLCanvasElement
                    var canvas = $('#statscanvas').get(0);
                    
                    var visiblityParent = $(canvas).parents('#seo_stats').css('display');
                    if(visiblityParent == 'none') {
                    	return;
                    }
                    // Get parent container width
                    var containerWidth = $(canvas).parent().width();
                    // Set dinamically canvas width
                    canvas.width = containerWidth;
                    $(canvas).css('min-width', canvas.width);
                    canvas.height = 170;
                    
        			var chartData = {};
                    var chartOptions = {animation:true, scaleFontSize: 10, scaleOverride: true, scaleSteps:1, scaleStepWidth: 50};
                    
                	// Instance Chart object lib
                    var canvasContext = canvas.getContext('2d');
                	var chartJS = new JMapChart(canvasContext);
                	
                	// Max value encountered
                	var maxValue = 10;
                	
                	// Normalize chart data to render
                	chartData.labels = new Array();
                	chartData.datasets = new Array();
                	var subDataSet = new Array();
                	var modulo = 14 - parseInt ( containerWidth / 100);
                	if($(window).width() > 2500) {
                		modulo = 18 - parseInt ( containerWidth / 100);
                	}
                	var counter = 0;
                	var lastValue = null;
                	var firstLabel = false;
                	var allValues = seoStatsTrafficgraphData.length < 14 ? true : false;
                    $.each(seoStatsTrafficgraphData, function(index, statObject){
                    	if(counter == 0) {
                    		counter++;
                    		return true;
                    	}
                    	
						// Parse key value and htmlentitydecode
                    	statObject = decodeURIComponent(statObject);
                    	statObject = statObject.split(':');
                    	
                    	var label = statObject[0];
                    	var value = statObject[1];

                    	if((counter % modulo == 0 || allValues) && parseInt(value)) {
                    		if(!firstLabel) {
                        		label = '         ' + label;
                        		firstLabel = true;
                    		} else {
                    			label = '        ' + label;
                    		}
                			chartData.labels[chartData.labels.length] = label;
                    		subDataSet[subDataSet.length] = value = parseInt(value);
                    		if(value > maxValue) {
                    			maxValue = value;
                    		}
                    	}
                    	lastValue = parseInt(value);
                    	counter++;
                    });
                    
                    chartData.labels[chartData.labels.length] = COM_JMAP_STATSCROP_GRAPH_TODAY;
                    subDataSet[subDataSet.length] = lastValue;
                    if(lastValue > maxValue) {
            			maxValue = lastValue;
            		}
                    
                    // Override scale
                    chartOptions.scaleStepWidth = 10;
                    if((maxValue / 100) > 0) {
                    	var multiplier = parseInt(maxValue / 50);
                    	chartOptions.scaleStepWidth = 10 + (multiplier * 10);
                    }
                    chartOptions.scaleSteps = parseInt((maxValue / chartOptions.scaleStepWidth) + 1);
                    
                    chartData.datasets[0] = {
                    		fillColor : "rgba(151,187,205,0.5)",
        					strokeColor : "rgba(151,187,205,1)",
        					pointColor : "rgba(151,187,205,1)",
        					pointStrokeColor : "#fff",
        					data : subDataSet
                    };
                	
                    // Override options
                    chartOptions.animation = animation; 
                    
                    // Paint chart on canvas
                	chartJS.Line(chartData, chartOptions);
                }
        	
        		$(window).on('resize', {bind:this}, function(event){
        			generateStatsLineChart();
            	})
            	
            	$(document).on('click', '#menu-collapse', function(event){
	        		setTimeout(function(){
	        			generateStatsLineChart();
	        		}, 300);
	        	});
            	
            	// Start generation
            	setTimeout(function(){
            		generateStatsLineChart(true);
            	}, 0)
        	}
        	
        	// Website screen
        	if(seoStats.websitescreen) {
        		var imageLink = $(seoStats.websitescreen).attr('src');
        		$('li[data-bind=\\{statscrop_website_screen\\}]').html('<a class="statscrop_website_screen" href="' + imageLink + '">' + seoStats.websitescreen + '</a>');
        		
        		// Now bind fancybox effect on the newly appended chart image
        		$('li.fancybox-image a.statscrop_website_screen')
        		.attr('title', COM_JMAP_WEBSITE_SCREEN)
        		.fancybox({
        			type: 'image',
        			openEffect	: 'elastic',
        			closeEffect	: 'elastic'
        		});
        	}
		};
		
		/**
		 * Format the Siterankdata Seo Stats
		 * 
		 * @access private
		 * @return Void
		 */
		var formatSiterankdataSeoStats = function(seoStats) {
			// Siterankdata rank
			var rankFontSize = '';
			var rankFormatted = seoStats.rank;
			if(!isNaN(parseInt(rankFormatted))) {
				rankFormatted = parseInt(rankFormatted).toLocaleString().replace(/,/g, '.');
				if(rankFormatted.length > 7) {
					rankFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{siterankdata_rank\\}]').html('<span' + rankFontSize + '>' + rankFormatted + '</span>');
			
			var dailyVisitorsFontSize = '';
			var dailyVisitorsFormatted = seoStats.dailyvisitors;
			if(!isNaN(parseInt(dailyVisitorsFormatted))) {
				dailyVisitorsFormatted = parseInt(dailyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(dailyVisitorsFormatted.length > 7) {
					dailyVisitorsFontSize = ' style="font-size:18px"';
				}
				if(dailyVisitorsFormatted.length > 9) {
					dailyVisitorsFontSize = ' style="font-size:14px"';
				}
			}
			$('li[data-bind=\\{daily_unique_visitors\\}]').html('<span' + dailyVisitorsFontSize + '>' + dailyVisitorsFormatted + '</span>');
			
			var monthlyVisitorsFontSize = '';
			var monthlyVisitorsFormatted = seoStats.monthlyvisitors;
			if(!isNaN(parseInt(monthlyVisitorsFormatted))) {
				monthlyVisitorsFormatted = parseInt(monthlyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(monthlyVisitorsFormatted.length > 7) {
					monthlyVisitorsFontSize = ' style="font-size:18px"';
				}
				if(monthlyVisitorsFormatted.length > 9) {
					monthlyVisitorsFontSize = ' style="font-size:14px"';
				}
			}
			$('li[data-bind=\\{monthly_visitors\\}]').html('<span' + monthlyVisitorsFontSize + '>' + monthlyVisitorsFormatted + '</span>');
			
			var yearlyVisitorsFontSize = '';
			var yearlyVisitorsFormatted = seoStats.yearlyvisitors;
			if(!isNaN(parseInt(yearlyVisitorsFormatted))) {
				yearlyVisitorsFormatted = parseInt(yearlyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(yearlyVisitorsFormatted.length > 7) {
					yearlyVisitorsFontSize = ' style="font-size:18px"';
				}
				if(yearlyVisitorsFormatted.length > 9) {
					yearlyVisitorsFontSize = ' style="font-size:14px"';
				}
			}
			$('li[data-bind=\\{yearly_visitors\\}]').html('<span' + yearlyVisitorsFontSize + '>' + yearlyVisitorsFormatted + '</span>');
			
			// Siterankdata competitors
			if(typeof(seoStats.siterankdatacompetitors) === 'object' && seoStats.siterankdatacompetitors) {
				var siterankdataCompetitors = '';
				$.each(seoStats.siterankdatacompetitors.data, function(index, domainObject){
					// Limit top 10 domains
					if((index + 1) > 15) {
						return false;
					}
					
					if(typeof(domainObject.competitor) !== 'undefined') {
						siterankdataCompetitors += ('<div class="competitor">' + domainObject.competitor + '</div>');
					}
				});
				
				var siterankdataCompetitorsEl = $('li[data-bind=\\{siterankdata_competitors\\}]');
				siterankdataCompetitorsEl.attr('data-bs-content', siterankdataCompetitors).addClass('clickable');
				new bootstrap.Popover(siterankdataCompetitorsEl.get(0),{
					template : '<div class="popover siterankdata_competitors_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
				
				$(document).on('click', 'div.siterankdata_competitors_popover div.competitor', function(jqEvent){
					var linkUrl = $(this).text();
					var competitorStatsInSession = false;
					if( window.sessionStorage !== null ) {
						competitorStatsInSession = JSON.parse(sessionStorage.getItem('competitorstats.' + linkUrl));
					}
					getCompetitorStatsData(linkUrl, this, competitorStatsInSession);
					return false;
				});
			}
			
			// Website screen
			var imageLink = $(seoStats.websitescreen).attr('src');
			$('li[data-bind=\\{website_screen\\}]').html('<a class="siterankdata_website_screen" href="' + imageLink + '">' + seoStats.websitescreen + '</a>');
			
			// Now bind fancybox effect on the newly appended chart image
			$('li.fancybox-image a.siterankdata_website_screen')
				.attr('title', COM_JMAP_WEBSITE_SCREEN)
				.fancybox({
					type: 'image',
			    	openEffect	: 'elastic',
			    	closeEffect	: 'elastic'
			});
		};
		
		/**
		 * Format the Hypestat Seo Stats
		 * 
		 * @access private
		 * @return Void
		 */
		var formatHypestatSeoStats = function(seoStats) {
			// Siterankdata rank
			var rankFontSize = '';
			var rankFormatted = seoStats.rank;
			if(!isNaN(parseInt(rankFormatted))) {
				rankFormatted = parseInt(rankFormatted).toLocaleString().replace(/,/g, '.');
				if(rankFormatted.length > 7) {
					rankFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{hypestat_rank\\}]').html('<span' + rankFontSize + '>' + rankFormatted + '</span>');
			
			var dailyVisitorsFontSize = '';
			var dailyVisitorsFormatted = seoStats.dailyvisitors;
			if(!isNaN(parseInt(dailyVisitorsFormatted))) {
				dailyVisitorsFormatted = parseInt(dailyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(dailyVisitorsFormatted.length > 7) {
					dailyVisitorsFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{daily_unique_visitors\\}]').html('<span' + dailyVisitorsFontSize + '>' + dailyVisitorsFormatted + '</span>');
			
			var monthlyVisitorsFontSize = '';
			var monthlyVisitorsFormatted = seoStats.monthlyvisitors;
			if(!isNaN(parseInt(monthlyVisitorsFormatted))) {
				monthlyVisitorsFormatted = parseInt(monthlyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(monthlyVisitorsFormatted.length > 7) {
					monthlyVisitorsFontSize = ' style="font-size:18px"';
				}
				if(monthlyVisitorsFormatted.length > 9) {
					monthlyVisitorsFontSize = ' style="font-size:14px"';
				}
			}
			$('li[data-bind=\\{monthly_visitors\\}]').html('<span' + monthlyVisitorsFontSize + '>' + monthlyVisitorsFormatted + '</span>');
			
			$('li[data-bind=\\{pages_per_visit\\}]').html(seoStats.pagespervisit);
			
			var dailyPageviewsFontSize = '';
			var dailyPageviewsFormatted = seoStats.dailypageviews;
			if(!isNaN(parseInt(dailyPageviewsFormatted))) {
				dailyPageviewsFormatted = parseInt(dailyPageviewsFormatted).toLocaleString().replace(/,/g, '.');
				if(dailyPageviewsFormatted.length > 7) {
					dailyPageviewsFontSize = ' style="font-size:18px"';
				}
				if(dailyPageviewsFormatted.length > 9) {
					dailyPageviewsFontSize = ' style="font-size:14px"';
				}
			}
			$('li[data-bind=\\{daily_pageviews\\}]').html('<span' + dailyPageviewsFontSize + '>' + dailyPageviewsFormatted + '</span>');
			
			var backlinksFontSize = '';
			var backlinksFormatted = seoStats.backlinks;
			if(!isNaN(parseInt(backlinksFormatted))) {
				backlinksFormatted = backlinksFormatted.replace(/,/g, '.');
				if(backlinksFormatted.length > 7) {
					backlinksFontSize = ' style="font-size:18px"';
				}
				if(backlinksFormatted.length > 9) {
					backlinksFontSize = ' style="font-size:14px"';
				}
			}
			$('li[data-bind=\\{backlinks\\}]').html('<span' + backlinksFontSize + '>' + backlinksFormatted + '</span>');
			
			// Website screen
			var imageLink = $(seoStats.websitescreen).attr('src');
			$('li[data-bind=\\{website_screen\\}]').html('<a class="hypestat_website_screen" href="' + imageLink + '">' + seoStats.websitescreen + '</a>');
			
			// Now bind fancybox effect on the newly appended chart image
			$('li.fancybox-image a.hypestat_website_screen')
				.attr('title', COM_JMAP_WEBSITE_SCREEN)
				.fancybox({
					type: 'image',
			    	openEffect	: 'elastic',
			    	closeEffect	: 'elastic'
			});
			
			$('div[data-bind=\\{website_report_text\\}]').html(seoStats.reporttext).removeClass('card-hidden');
			if(!seoStats.reporttext){
				$('div[data-bind=\\{website_report_text\\}]').addClass('card-empty');
			}
		};
		
		/**
		 * Format the XRanks Seo Stats
		 * 
		 * @access private
		 * @return Void
		 */
		var formatXRanksSeoStats = function(seoStats) {
			// Siterankdata rank
			var rankFontSize = '';
			var rankFormatted = seoStats.globalrank;
			rankFormatted = rankFormatted.replace(/,/g, '.');
			if(rankFormatted.length > 7) {
				rankFontSize = ' style="font-size:18px"';
			}
			$('li[data-bind=\\{global_rank\\}]').html('<span' + rankFontSize + '>' + rankFormatted + '</span>');
			
			$('li[data-bind=\\{organic_visit\\}]').html(seoStats.organicvisit);
			
			$('li[data-bind=\\{domain_authority\\}]').html(seoStats.domainauthority);
			
			$('li[data-bind=\\{traffic\\}]').html(seoStats.traffic);
			
			$('li[data-bind=\\{backlinks\\}]').html(seoStats.backlinks);
			
			$('li[data-bind=\\{organic_search_traffic\\}]').html(seoStats.organicsearchtraffic);
			
			var dailyVisitorsFontSize = '';
			var dailyVisitorsFormatted = seoStats.usersperday;
			dailyVisitorsFormatted = dailyVisitorsFormatted.replace(/,/g, '.');
			if(dailyVisitorsFormatted.length > 7) {
				dailyVisitorsFontSize = ' style="font-size:18px"';
			}
			$('li[data-bind=\\{users_per_day\\}]').html('<span' + dailyVisitorsFontSize + '>' + dailyVisitorsFormatted + '</span>');
			
			$('li[data-bind=\\{openpagerank\\}]').html(seoStats.openpagerank);
			
			$('li[data-bind=\\{semrushrank\\}]').html(seoStats.semrushrank);
			
			$('li[data-bind=\\{semrushkeywords\\}]').html(seoStats.semrushkeywords);
			
			$('li[data-bind=\\{organictraffic\\}]').html(seoStats.organictraffic);
			
			var pageViewsFontSize = '';
			var pageViewsFormatted = seoStats.pageviewsperday;
			pageViewsFormatted = pageViewsFormatted.replace(/,/g, '.');
			if(pageViewsFormatted.length > 7) {
				pageViewsFontSize = ' style="font-size:18px"';
			}
			$('li[data-bind=\\{pageviews_per_day\\}]').html('<span' + pageViewsFontSize + '>' + pageViewsFormatted + '</span>');
			
			// XRanks competitors
			if(typeof(seoStats.competitors) === 'object' && seoStats.competitors) {
				var xranksCompetitors = '';
				$.each(seoStats.competitors.data, function(index, domainObject){
					// Limit top 10 domains
					if((index + 1) > 15) {
						return false;
					}
					
					if(typeof(domainObject.Dn) !== 'undefined') {
						xranksCompetitors += ('<div class="competitor">' + domainObject.Dn + '</div>');
					}
				});
				
				var xranksdataCompetitorsEl = $('li[data-bind=\\{xranks_competitors\\}]');
				xranksdataCompetitorsEl.attr('data-bs-content', xranksCompetitors).addClass('clickable');
				new bootstrap.Popover(xranksdataCompetitorsEl.get(0),{
					template : '<div class="popover xranksdata_competitors_popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					trigger:'click',
					placement:'top',
					html:true
				});
				
				$(document).on('click', 'div.xranksdata_competitors_popover div.competitor', function(jqEvent){
					var linkUrl = $(this).text();
					var competitorStatsInSession = false;
					if( window.sessionStorage !== null ) {
						competitorStatsInSession = JSON.parse(sessionStorage.getItem('competitorstats.' + linkUrl));
					}
					getCompetitorStatsData(linkUrl, this, competitorStatsInSession);
					return false;
				});
			}
			
			// Website screen
			var imageLink = $(seoStats.websitescreen).attr('src');
			if(imageLink.indexOf('s3.amazonaws') === -1) {
				$('li[data-bind=\\{website_screen\\}]').html('<a class="xranks_website_screen" href="' + imageLink + '">' + seoStats.websitescreen + '</a>');
			}
			
			// Now bind fancybox effect on the newly appended chart image
			$('li.fancybox-image a.xranks_website_screen')
				.attr('title', COM_JMAP_WEBSITE_SCREEN)
				.fancybox({
					type: 'image',
			    	openEffect	: 'elastic',
			    	closeEffect	: 'elastic'
			});
			
			$('div[data-bind=\\{website_report_text\\}]').html(seoStats.reporttext).removeClass('card-hidden');
			if(!seoStats.reporttext){
				$('div[data-bind=\\{website_report_text\\}]').addClass('well-empty');
			}
		};
		
		/**
		 * Format the Zigstat Seo Stats
		 * 
		 * @access private
		 * @return Void
		 */
		var formatZigstatSeoStats = function(seoStats) {
			// Semrush rank
    		    	var mozRankFontSize = '';
    		    	var mozRankLinksFormatted = seoStats.mozrank;
    		    	if(!isNaN(parseInt(mozRankLinksFormatted))) {
    		    	    mozRankLinksFormatted = parseInt(mozRankLinksFormatted).toLocaleString().replace(/,/g, '.');
    		    	    if(mozRankLinksFormatted.length > 7) {
    		    		mozRankFontSize = ' style="font-size:18px"';
    		    	    }
    		    	}
    		    	$('li[data-bind=\\{zigstat_mozrank\\}]').html('<span' + mozRankFontSize + '>' + mozRankLinksFormatted + '</span>');
			
			$('li[data-bind=\\{zigstat_mozdomainauth\\}]').html(seoStats.mozdomainauth);
			
			$('li[data-bind=\\{zigstat_pageviews\\}]').html('<span style="font-size:18px">' + seoStats.pageviews + '</span>');
			
			var serpKeywordsFontSize = '';
			var serpKeywordsFormatted = seoStats.serpkeywords;
			if(!isNaN(parseInt(serpKeywordsFormatted))) {
				serpKeywordsFormatted = parseInt(serpKeywordsFormatted).toLocaleString().replace(/,/g, '.');
				if(serpKeywordsFormatted.length > 7) {
					serpKeywordsFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{zigstat_serpkeywords\\}]').html('<span' + serpKeywordsFontSize + '>' + serpKeywordsFormatted + '</span>');
			
			var mozBacklinksFontSize = '';
			var mozBacklinksFormatted = seoStats.backlinks;
			if(!isNaN(parseInt(mozBacklinksFormatted))) {
				mozBacklinksFormatted = parseInt(mozBacklinksFormatted).toLocaleString().replace(/,/g, '.');
				if(mozBacklinksFormatted.length > 7) {
					mozBacklinksFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{zigstat_backlinks\\}]').html('<span' + mozBacklinksFontSize + '>' + mozBacklinksFormatted + '</span>');
			
			// Open Page Rank
			$('li[data-bind=\\{zigstat_openpagerank\\}]').html(seoStats.openpagerank);
			
			var dailyVisitorsFontSize = '';
			var dailyVisitorsFormatted = seoStats.dailyvisitors;
			if(!isNaN(parseInt(dailyVisitorsFormatted))) {
				dailyVisitorsFormatted = parseInt(dailyVisitorsFormatted).toLocaleString().replace(/,/g, '.');
				if(dailyVisitorsFormatted.length > 7) {
					dailyVisitorsFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{zigstat_dailyvisitor\\}]').html('<span' + dailyVisitorsFontSize + '>' + dailyVisitorsFormatted + '</span>');
			
			var followBacklinksFontSize = '';
			var followBacklinksFormatted = seoStats.followbacklinks;
			if(!isNaN(parseInt(followBacklinksFormatted))) {
			    followBacklinksFormatted = parseInt(followBacklinksFormatted).toLocaleString().replace(/,/g, '.');
				if(followBacklinksFormatted.length > 7) {
				    followBacklinksFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{zigstat_followbacklinks\\}]').html('<span' + followBacklinksFontSize + '>' + followBacklinksFormatted + '</span>');
			
			var nofollowBacklinksFontSize = '';
			var nofollowBacklinksFormatted = seoStats.nofollowbacklinks;
			if(!isNaN(parseInt(nofollowBacklinksFormatted))) {
			    nofollowBacklinksFormatted = parseInt(nofollowBacklinksFormatted).toLocaleString().replace(/,/g, '.');
				if(nofollowBacklinksFormatted.length > 7) {
				    nofollowBacklinksFontSize = ' style="font-size:18px"';
				}
			}
			$('li[data-bind=\\{zigstat_nofollowbacklinks\\}]').html('<span' + nofollowBacklinksFontSize + '>' + nofollowBacklinksFormatted + '</span>');
			
			$('div[data-bind=\\{website_report_text\\}]').html(seoStats.reporttext).removeClass('card-hidden');
			if(!seoStats.reporttext){
				$('div[data-bind=\\{website_report_text\\}]').addClass('card-empty');
			}
		};
		
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @return Void
		 */
		var formatSeoStats = function(seoStats) {
			switch(jmap_seostats_service) {
				case 'zigstat':
					formatZigstatSeoStats(seoStats);
				break;
				
				case 'hypestat':
					formatHypestatSeoStats(seoStats);
				break;
				
				case 'siterankdata':
					formatSiterankdataSeoStats(seoStats);
				break;
				
				case 'xranks':
					formatXRanksSeoStats(seoStats);
				break;
				
				case 'statscrop':
				default:
					formatStatscropSeoStats(seoStats);
				break;
			}
			
			// Show stats
			$('div.single_stat_rowseparator').fadeIn(200);
			$('#seo_stats div.single_stat_container').fadeIn(200);
		};
		
		/**
		 * Function dummy constructor
		 * 
		 * @access private
		 * @param String
		 *            contextSelector
		 * @method <<IIFE>>
		 * @return Void
		 */
		(function __construct() {
			// Check firstly if seostats are already in the sessionStorage
			if( window.sessionStorage !== null ) {
				var sessionSeoStats = sessionStorage.getItem('seostats');
				var sessionSeoStatsService = sessionStorage.getItem('seostats_service');
				var sessionSeoStatsTargeturl = sessionStorage.getItem('seostats_targeturl');
				
				// Check if there is no more matching between the session service and the current enabled service
				if((sessionSeoStatsService || (sessionSeoStats && !sessionSeoStatsService)) && (jmap_seostats_service != sessionSeoStatsService || jmap_seostats_targeturl != sessionSeoStatsTargeturl)) {
					// Reset all and reload
					sessionStorage.removeItem('seostats');
					sessionStorage.removeItem('seostats_service');
					sessionStorage.removeItem('seostats_targeturl');
					window.location.reload();
					return;
				}
				
				// Seo stats found in local session storage, go on to formatting data without a new request
				if(sessionSeoStats) {
					sessionSeoStats = JSON.parse(sessionSeoStats);
					
					// Format local data
					formatSeoStats(sessionSeoStats);
					
					// Remove waiter
					$('*.waiterinfo').remove();
					
					// Avoid to go on with a new async request
					return;
				}
			}
			
			// Add the initial loading waiter
			setTimeout(function(){
				addLoadingWaiter($('#seo_stats'));
			}, 500);
			
			// Get stats data from remote services using Promise, and populate user interface when resolved
			getSeoStatsData();

		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapSeoStats = new SeoStats();
	});
})(jQuery);