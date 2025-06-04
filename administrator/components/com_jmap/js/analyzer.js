/**
 * Generates an XML sitemap stored in a cache folder using AJAX 
 * for further analysis of contained links 200OK/404broken
 * 
 * @package JMAP::ANALYZER::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var Analyzer = function() {
		/**
		 * Target sitemap link
		 * 
		 * @access private
		 * @var String
		 */
		var targetSitemapLink = null;
		
		/**
		 * Parse url to grab query string params to post to server side for sitemap generation
		 * 
		 * @access private
		 * @return Object
		 */
		var parseURL = function(url) {
		    var a =  document.createElement('a');
		    a.href = url;
		    return {
		        source: url,
		        protocol: a.protocol.replace(':',''),
		        host: a.hostname,
		        port: a.port,
		        query: a.search,
		        params: (function(){
		            var ret = {},
		                seg = a.search.replace(/^\?/,'').split('&'),
		                len = seg.length, i = 0, s;
		            for (;i<len;i++) {
		                if (!seg[i]) { continue; }
		                s = seg[i].split('=');
		                ret[s[0]] = s[1];
		            }
		            return ret;
		        })(),
		        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [,''])[1],
		        hash: a.hash.replace('#',''),
		        path: a.pathname.replace(/^([^\/])/,'/$1'),
		        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [,''])[1],
		        segments: a.pathname.replace(/^\//,'').split('/')
		    };
		}
		
		/**
		 * Register user events for interface controls
		 * 
		 * @access private
		 * @param Boolean initialize
		 * @return Void
		 */
		var addListeners = function(initialize) {
			// Start the precaching process, first operation is enter the progress modal mode
			$('a.jmap_analyzer').on('click.analyzer', function(jqEvent){
				// Prevent click link default
				jqEvent.preventDefault();
				
				// Show striped progress started generation
				showProgress(true, 50, 'striped', COM_JMAP_ANALYZER_STARTED_SITEMAP_GENERATION);
				
				// Grab targeted sitemap link
				targetSitemapLink = $(this).attr('href');
			});
			
			// Register form submit event
			$('#adminForm ul.pagination li a').filter(function(){
				if($(this).hasClass('current') || $(this).hasClass('disabled')) {
					return false;
				}
				if($(this).parent().hasClass('current') || $(this).parent().hasClass('active') || $(this).parent().hasClass('disabled')) {
					return false;
				}
				return true;
			}).on('click.analyzer', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_ANALYZER_ANALYZING_LINKS);
			});
			$('#adminForm select:not(.noanalyzer)').on('change.analyzer', function(jqEvent){
				showProgress(true, 100, 'striped', COM_JMAP_ANALYZER_ANALYZING_LINKS);
			});
			$('#adminForm table.adminlist th a.hasTooltip').on('click.analyzer', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_ANALYZER_ANALYZING_LINKS);
			});
			
			// Live event binding only once on initialize, avoid repeated handlers and executed callbacks
			if(initialize) {
				// Live event binding for close button AKA stop process
				$(document).on('click.analyzer', 'label.closeprecaching', function(jqEvent){
					let modalEl = document.querySelector('#analyzer_process');
					if(modalEl) {
						bootstrap.Modal.getInstance(modalEl).hide();
					}
				});
			}
		};
		
		/**
		 * Show progress dialog bar with informations about the ongoing started process
		 * 
		 * @access private
		 * @return Void
		 */
		var showProgress = function(isNew, percentage, type, status, classColor) {
			// No progress process injected
			if(isNew) {
				// Show second progress
				var progressBar = 	'<div id="progress_bar" class="progress progress-bar progress-bar-animated progress-bar-' + type + '" role="progressbar" style="width: 0" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100">' +
										'<label class="visually-hidden">' + status + '</label>' +
									'</div>';
				
				// Build modal dialog
				var modalDialog =	'<div class="jmapmodal modal fade" id="analyzer_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
										'<div class="modal-dialog">' +
											'<div class="modal-content">' +
												'<div class="modal-header">' +
									        		'<h4 class="modal-title">' + COM_JMAP_ANALYZER_TITLE + '</h4>' +
									        		'<label class="closeprecaching fas fa-times-circle"></label>' +
									        		'<p class="modal-subtitle">' + COM_JMAP_ANALYZER_PROCESS_RUNNING + '</p>' +
								        		'</div>' +
								        		'<div class="modal-body">' +
									        		'<p>' + progressBar + '</p>' +
									        		'<p id="progress_info">' + status + '</p>' +
								        		'</div>' +
								        		'<div class="modal-footer">' +
									        	'</div>' +
								        	'</div><!-- /.modal-content -->' +
							        	'</div><!-- /.modal-dialog -->' +
							        '</div>';
				// Inject elements into content body
				$('body').append(modalDialog);
				
				// Setup modal
				var modalOptions = {
						backdrop:'static'
					};
				
				let modalEl = document.querySelector('#analyzer_process');
				let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
				modalInstance.show();
				
				// Async event progress showed and styling
				modalEl.addEventListener('shown.bs.modal', function(event) {
					$('#analyzer_process div.modal-body').css({'width':'90%', 'margin':'auto'});
					$('#progress_bar').css({'width':percentage + '%'});
					
					// Start AJAX GET request for sitemap generation in the cache folder
					startSitemapCaching(targetSitemapLink);
				});
				
				// Remove backdrop after removing DOM modal
				modalEl.addEventListener('hidden.bs.modal', function(event){
					$('.modal-backdrop').remove();
					$(this).remove();
					
					// Redirect to MVC core cpanel, discard analyzer
					window.location.href = 'index.php?option=com_jmap&task=cpanel.display'
				});
			} else {
				// Refresh only status, progress and text
				$('#progress_bar').addClass(classColor)
								  .css({'width':percentage + '%'});
				
				$('#progress_bar').removeClass('progress-bar-normal progress-bar-striped')
								  .addClass('progress-bar-' + type);
				
				$('#progress_info').html(status);		
				
				// An error has been detected, so auto close process and progress bar
				if(classColor == 'bg-danger') {
					setTimeout(function(){
						// Remove all
						let modalEl = document.querySelector('#analyzer_process');
						if(modalEl) {
							bootstrap.Modal.getInstance(modalEl).hide();
						}
					}, 3500);
				}
			}
		}
		
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @param String targetSitemapLink
		 * @return Void
		 */
		var startSitemapCaching = function(targetSitemapLink) {
			// No ajax request if no control panel generation in 2 steps
			if(!targetSitemapLink) {
				return;
			}
			// Request JSON2JSON
			var dataSourcePromise = $.Deferred(function(defer) {
				$.ajax({
					type : "GET",
					url : targetSitemapLink,
					dataType : 'json',
					context : this,
					data: {'jsclient' : true}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(COM_JMAP_ANALYZER_ERROR_STORING_FILE, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						defer.resolve();
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			dataSourcePromise.then(function() {
				// Update process status, we started
				showProgress(false, 100, 'striped', COM_JMAP_ANALYZER_GENERATION_COMPLETE);
				
				// Parse sitemap parameters
				var sitemapParams = parseURL(targetSitemapLink).params;
				var sitemapLang = sitemapParams.lang ? '&sitemaplang=' + sitemapParams.lang : '';
				var sitemapDataset = sitemapParams.dataset ? '&sitemapdataset=' + sitemapParams.dataset : '';
				var sitemapMenuID = sitemapParams.Itemid ? '&sitemapitemid=' + sitemapParams.Itemid : '';
				
				// Redirect to MVC core
				window.location.href = 'index.php?option=com_jmap&task=analyzer.display&jsclient=1' + sitemapLang + sitemapDataset + sitemapMenuID;
			}, function(errorText, error) {
				// Do stuff and exit
				showProgress(false, 100, 'normal', errorText, 'bg-danger');
			});
		};

		/**
		 * Process the asyncronous analysis of links showed in the page of the Links Analyzer
		 * It performs parallel async requests for each link evaluating the HTTP status code in response and acting accordingly
		 *
		 * @access private
		 * @return Void
		 */
		var startValidationAnalysis = function() {
			// Retrieve all the links to analyze on page
			var linksToAnalyze = $('a[data-role=link],a[data-role=neutral]');
			
			// No ajax request if no links to analyze
			if(!linksToAnalyze.length) {
				return;
			}

			$.each(linksToAnalyze, function(index, link){
				var targetValidationStatus = $('img[data-role=validation_status]').get(index);
				var targetValidationCode = $('span.httpcode').get(index);
				
				var linkValidationPromise = $.Deferred(function(defer) {
					$.ajax({
						type : "GET",
						url : $(link).attr('href'),
					}).done(function(data, textStatus, jqXHR) {
						// Check response HTTP status code
						defer.resolve(jqXHR.status);
					}).fail(function(jqXHR, textStatus, errorThrown) {
						// Check response HTTP status code
						defer.resolve(jqXHR.status);
					});
				}).promise();
				
				linkValidationPromise.always(function(validationStatus){
					if(validationStatus == 200) {
						$(targetValidationStatus).after('<img class="validation hasTooltip" title="' + COM_JMAP_ANALYZER_LINKVALID + '" src="' + 
								jmap_baseURI + 'administrator/components/com_jmap/images/icon-16-tick.png"/>').remove();
					} else if(validationStatus == 0) {
						$(targetValidationStatus).after('<img class="validation hasTooltip" title="' + COM_JMAP_ANALYZER_NOINFO + '" src="' + 
								jmap_baseURI + 'administrator/components/com_jmap/images/icon-16-notice.png"/>').remove();
					} else {
						$(targetValidationStatus).after('<img class="validation hasTooltip" title="' + COM_JMAP_ANALYZER_LINK_NOVALID + '" src="' + 
								jmap_baseURI + 'administrator/components/com_jmap/images/publish_x.png"/>').remove();
					}
					
					// Refresh tooltips
					[].slice.call(document.querySelectorAll('img.validation.hasTooltip')).map(function (tooltipEl) {
						let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
							trigger:'hover', 
							placement:'top', 
							html: true
						});
						
						return tooltipInstance;
					});
					
					// Append the HTTP status code to the correct column
					$(targetValidationCode).append(validationStatus || '-');
					if(validationStatus > 200) {
						$(targetValidationCode).addClass('errorcode');
					}
				});
			});
		};
		
		/**
		 * Process the asyncronous analysis of links showed in the page of the Links Analyzer
		 * It performs parallel async requests to the Google API to retrieve the indexing status 
		 *
		 * @access private
		 * @return Void
		 */
		var startIndexingStatusAnalysis = function() {
			// Retrieve all the links to analyze on page
			var linksToAnalyze = $('a[data-role=link]');
			
			// No ajax request if no links to analyze
			if(!linksToAnalyze.length) {
				return;
			}

			$.each(linksToAnalyze, function(index, link){
				// Extra object to send to server
				var ajaxParams = { 
						idtask : 'getIndexedStatus',
						param: $(link).attr('href')
				};
				// Unique param 'data'
				var uniqueParam = JSON.stringify(ajaxParams);
				var targetIndexingStatus = $('img[data-role=indexing_status]').get(index);
				var targetPagespeedStatus = $('img[data-role=pagespeed_status]').get(index);

				var linkAnalyisPromise = $.Deferred(function(defer) {
					$.ajax({
						type : "POST",
						url : "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
						dataType : 'json',
						context : this,
						data: {data : uniqueParam } ,
					}).done(function(data, textStatus, jqXHR) {
						if(!data.result) {
							// Error found
							defer.reject(data.exception_message, textStatus);
							return false;
						}
						
						// Check response all went well
						if(data.result) {
							defer.resolve(data.indexing_status, data);
						}
					}).fail(function(jqXHR, textStatus, errorThrown) {
						// Error found
						var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
						defer.reject('-' + genericStatus + '- ' + errorThrown);
					});
				}).promise();
				
				linkAnalyisPromise.then(function(indexingStatus, fullDataStatus) {
					if(indexingStatus == 1) {
						$(targetIndexingStatus).after('<img class="indexing hasTooltip" title="' + COM_JMAP_ANALYZER_INDEXED_LINK + '" src="' + 
								jmap_baseURI + 'administrator/components/com_jmap/images/icon-16-tick.png"/>').remove();
					} else if(indexingStatus == -1) {
						$(targetIndexingStatus).after('<img class="indexing hasTooltip" title="' + COM_JMAP_ANALYZER_NOAVAILABLE_LINK + '" src="' + 
								jmap_baseURI + 'administrator/components/com_jmap/images/icon-16-notice.png"/>').remove();
					} else if(indexingStatus == 0) {
						$(targetIndexingStatus).after('<img class="indexing hasTooltip" title="' + COM_JMAP_ANALYZER_NOINDEXED_LINK + '" src="' + 
								jmap_baseURI + 'administrator/components/com_jmap/images/publish_x.png"/>').remove();
					}
					
					var pageSpeedPopulated = false;
					if(typeof(fullDataStatus.pagespeed) !== 'undefined' && fullDataStatus.pagespeed > -1) {
						if(fullDataStatus.pagespeed >= 0 && fullDataStatus.pagespeed <= 49) {
							$(targetPagespeedStatus).after('<span class="jmap-pagespeed-label jmap-pagespeed-low hasTooltip" title="' + COM_JMAP_ANALYZER_PAGESPEED_LOW + '">' + fullDataStatus.pagespeed + '</span>');
						} else if(fullDataStatus.pagespeed >= 50 && fullDataStatus.pagespeed <= 89) {
							$(targetPagespeedStatus).after('<span class="jmap-pagespeed-label jmap-pagespeed-average hasTooltip" title="' + COM_JMAP_ANALYZER_PAGESPEED_AVERAGE + '">' + fullDataStatus.pagespeed + '</span>');
						} else if(fullDataStatus.pagespeed >= 90 && fullDataStatus.pagespeed <= 100) {
							$(targetPagespeedStatus).after('<span class="jmap-pagespeed-label jmap-pagespeed-high hasTooltip" title="' + COM_JMAP_ANALYZER_PAGESPEED_HIGH + '">' + fullDataStatus.pagespeed + '</span>');
						}
						
						pageSpeedPopulated = true;
					}
					
					if(typeof(fullDataStatus.pagespeed_lcp_score) !== 'undefined') {
						$(targetPagespeedStatus).after('<span class="jmap-pagespeed-semaphore jmap-pagespeed-semaphore-' + fullDataStatus.pagespeed_lcp_score_vote + ' hasTooltip" title="' + COM_JMAP_ANALYZER_PAGESPEED_LCP_DESC + fullDataStatus.pagespeed_lcp_score + 's' + COM_JMAP_ANALYZER_PAGESPEED_LCP_DESC_XTD + '">' + COM_JMAP_ANALYZER_PAGESPEED_LCP + '</span>');
						pageSpeedPopulated = true;
					}
					if(typeof(fullDataStatus.pagespeed_fid_score) !== 'undefined') {
						$(targetPagespeedStatus).after('<span class="jmap-pagespeed-semaphore jmap-pagespeed-semaphore-' + fullDataStatus.pagespeed_fid_score_vote + ' hasTooltip" title="' + COM_JMAP_ANALYZER_PAGESPEED_FID_DESC + fullDataStatus.pagespeed_fid_score + 'ms' + COM_JMAP_ANALYZER_PAGESPEED_FID_DESC_XTD + '">' + COM_JMAP_ANALYZER_PAGESPEED_FID + '</span>');
						pageSpeedPopulated = true;
					}
					if(typeof(fullDataStatus.pagespeed_cls_score) !== 'undefined') {
						$(targetPagespeedStatus).after('<span class="jmap-pagespeed-semaphore jmap-pagespeed-semaphore-' + fullDataStatus.pagespeed_cls_score_vote + ' hasTooltip" title="' + COM_JMAP_ANALYZER_PAGESPEED_CLS_DESC + fullDataStatus.pagespeed_cls_score + COM_JMAP_ANALYZER_PAGESPEED_CLS_DESC_XTD + '">' + COM_JMAP_ANALYZER_PAGESPEED_CLS + '</span>');
						pageSpeedPopulated = true;
					}
					
					let tooltipInstanceStatus = bootstrap.Tooltip.getInstance(targetPagespeedStatus);
					if(tooltipInstanceStatus) {
						tooltipInstanceStatus.dispose();
					}
					
					if(!pageSpeedPopulated) {
						$(targetPagespeedStatus).after('-');
					}
					
					$(targetPagespeedStatus).remove();
				}, function(errorText, error) {
					$(targetIndexingStatus).after('<img style="width:16px;height:16px" class="indexing hasTooltip" title="' + errorText + '" src="' + 
													jmap_baseURI + 'administrator/components/com_jmap/images/icon-32-delete.png"/>').remove();
					$(targetPagespeedStatus).after('-').remove();
				}).always(function(){
					// Refresh tooltips
					[].slice.call(document.querySelectorAll('img.indexing.hasTooltip,span.jmap-pagespeed-label.hasTooltip,span.jmap-pagespeed-semaphore.hasTooltip')).map(function (tooltipEl) {
						let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
							trigger:'hover', 
							placement:'top', 
							html: true
						});
						
						// Bind the popover closing
						$('body').on('click', function(jqEvent) {
							if(tooltipInstance) {
								tooltipInstance.hide();
							}
						});
						
						return tooltipInstance;
					});
				});
			});
		};
		
		/**
		 * Filters on page results using DOM for async mode of the Analyzer
		 *
		 * @access public
		 * @return Void
		 */
		this.filterOnAsyncPage = function(element) {
			// Retrieve the selected filtering value
			var selectedHttpFilterVal = parseInt($(element).val());
			
			if(selectedHttpFilterVal) {
				// Select all the http status code retrieved in async ajax
				$('span.httpcode').each(function(index, elem){
					var rawHttpCode = parseInt($(elem).text());
					var parentTr = $(elem).parents('tr');
					// Is it matching with the filter?
					if(rawHttpCode != selectedHttpFilterVal) {
						parentTr.hide();
					} else {
						parentTr.show();
					}
				});
			} else {
				// Show back all rows if exiting from a filter value
				$('table.analyzerlist tr').show();
			}
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
			// Add UI events
			addListeners.call(this, true);
			
			/// Execute analysis only if the view Analyzer is executed
			if($('table.analyzerlist').length) {
				// Start to analyze the indexing status
				startIndexingStatusAnalysis();

				// Start to analyze the validation status if enabled the async mode
				if(jmap_validationAnalysis == 1) {
					startValidationAnalysis();
				}
			}
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapAnalyzer = new Analyzer();
	});
})(jQuery);