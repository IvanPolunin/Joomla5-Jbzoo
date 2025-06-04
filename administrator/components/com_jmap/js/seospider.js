/**
 * Spider client for SEO reports and issues reporter
 * 
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var SeoSpider = function() {
		/**
		 * Target sitemap link
		 * 
		 * @access private
		 * @var String
		 */
		var targetSitemapLink = null;
		
		/**
		 * Promises array
		 * 
		 * @access private
		 * @var Array
		 */
		var promisesCollection = new Array();
		
		/**
		 * Timings of each promise start and resolve
		 * 
		 * @access private
		 * @var Array
		 */
		var promisesCollectionTimings = new Array();
		
		/**
		 * Titles collection
		 * 
		 * @access private
		 * @var Object
		 */
		var titlesCollection = {};
		
		/**
		 * Descriptions collection
		 * 
		 * @access private
		 * @var Object
		 */
		var descriptionsCollection = {};
		
		/**
		 * Timeout reference for arrows
		 * 
		 * @access private
		 * @var Object
		 */
		var arrowsTimeout = null;
		
		/**
		 * Mapping structure for performance/rating review of page load time
		 * 
		 * @access private
		 * @var Object
		 */
		var mappingRatings = {
			level1 : {
				labelcolor : 'success',
				tooltip : COM_JMAP_SEOSPIDER_PAGELOAD_FAST,
				lowerlimit : 0,
				upperlimit : 3
			},
			level4 : {
				labelcolor : 'warning',
				tooltip : COM_JMAP_SEOSPIDER_PAGELOAD_AVERAGE,
				lowerlimit : 3,
				upperlimit : 6
			},
			level6 : {
				labelcolor : 'danger',
				tooltip : COM_JMAP_SEOSPIDER_PAGELOAD_SLOW,
				lowerlimit : 6,
				upperlimit : 99999
			}
		}
		
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
		 * Generate an unique hash for a title or description string in input
		 * 
		 * @access private
		 * @return Object
		 */
		var generateHash = function(string) {
			 var hash = 0, i, chr, len;
			  if (string.length == 0) return hash;
			  for (i = 0, len = string.length; i < len; i++) {
				  chr   = string.charCodeAt(i);
				  hash  = ((hash << 5) - hash) + chr;
				  hash |= 0; // Convert to 32bit integer
			  }
			  return hash;
		};
		
		/**
		 * Register user events for interface controls
		 * 
		 * @access private
		 * @param Boolean initialize
		 * @return Void
		 */
		var addListeners = function(initialize) {
			// Start the precaching process, first operation is enter the progress modal mode
			$('a.jmap_seospider').on('click.seospider', function(jqEvent){
				// Prevent click link default
				jqEvent.preventDefault();
				
				// Show striped progress started generation
				showProgress(true, 50, 'striped', COM_JMAP_SEOSPIDER_STARTED_SITEMAP_GENERATION);
				
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
			}).on('click.seospider', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_SEOSPIDER_CRAWLING_LINKS);
			});
			$('#adminForm select[class!=noanalyzer]').on('change.seospider', function(jqEvent){
				showProgress(true, 100, 'striped', COM_JMAP_SEOSPIDER_CRAWLING_LINKS);
			});
			$('#adminForm table.adminlist th a.hasTooltip').on('click.seospider', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_SEOSPIDER_CRAWLING_LINKS);
			});
			
			// Live event binding only once on initialize, avoid repeated handlers and executed callbacks
			if(initialize) {
				// Live event binding for close button AKA stop process
				$(document).on('click.seospider', 'label.closeprecaching', function(jqEvent){
					let modalEl = document.querySelector('#seospider_process');
					if(modalEl) {
						bootstrap.Modal.getInstance(modalEl).hide();
					}
				});
			}
			
			// Append a dialog with links list detail
			$('div[data-bind="{title-duplicates}"], div[data-bind="{desc-duplicates}"]').on('click.seospider', function(jqEvent){
				// Ensure to not execute noduplicates badge
				if($(this).hasClass('noduplicates')) {
					return false;
				}
				
				// Remove any previous instance
				$('#details_dialog').remove();
				
				var dialogTitle = '';
				var dialogContents = new Array();
				var thisLinkToSkip = $(this).data('link');
				var thisTitleHash = $(this).data('titlehash');
				var thisDescriptionHash = $(this).data('descriptionhash');
				var didascaly = '';
				
				// Determine the type of the dialog and title
				var thisBind = $(this).data('bind');
				switch(thisBind) {
					case '{title-duplicates}':
						dialogTitle = COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_TITLE;
						dialogContents = titlesCollection[thisTitleHash];
						didascaly = COM_JMAP_SEOSPIDER_TITLE_DETAILS + $(this).parents('tr').find('div[data-bind="{title}"] div.seospider_textlabel').text();
						break;
					
					case '{desc-duplicates}':
						dialogTitle = COM_JMAP_SEOSPIDER_DIALOG_DUPLICATES_DESCRIPTION;
						dialogContents = descriptionsCollection[thisDescriptionHash];
						didascaly = COM_JMAP_SEOSPIDER_DESCRIPTION_DETAILS + $(this).parents('tr').find('div[data-bind="{desc}"] div.seospider_textlabel').text();
						break;
				}
				showDuplicatesDetails(dialogTitle, dialogContents, didascaly, thisLinkToSkip);
			});
			
			// Append a dialog with links list detail
			$('div.trigger_content_analysis').on('click.seospider', function(jqEvent){
				// Remove any previous instance
				$('#analysis_dialog').remove();
				var linkToAnalyze = $(this).data('link');
				showContentAnalysis(linkToAnalyze);
			});
			
			// Append a dialog with headings editing
			if(typeof(jmap_overrideheadings) !== 'undefined' && parseInt(jmap_overrideheadings) == 1) {
				$(document).on('click.seospider', 'div[data-bind="{h1}"],div[data-bind="{h2}"],div[data-bind="{h3}"]', function(jqEvent){
					// Only open headings dialog when really needed
					if(!$(jqEvent.target).hasClass('seospider-headings')) {
						return false;
					}
					// Remove any previous instance
					$('#headings_dialog').remove();
					var linkToAnalyze = $(this).parents('tr').data('link');
					var headingTag = $(this).data('bind').replace(/[\{\}]/gi, '');
					showHeadingsDialog(linkToAnalyze, headingTag);
				});
			}
			
			// Append a dialog with canonical editing
			if(typeof(jmap_overridecanonical) !== 'undefined' && parseInt(jmap_overridecanonical) == 1) {
				$(document).on('click.seospider', 'div[data-bind="{canonical}"]', function(jqEvent){
					// Remove any previous instance
					$('#canonical_dialog').remove();
					var linkToAnalyze = $(this).parents('tr').data('link');
					showCanonicalDialog(linkToAnalyze);
				});
			}
			
			// Bind the save/delete buttons for headings
			$(document).on('click.seospider', '#save_heading, #delete_heading', function(jqEvent){
				var buttonAction = $(this).data('action');
				var buttonHeading = $(this).data('heading');
				saveDataStatus(buttonAction, buttonHeading);
			});
			
			// Bind the save/delete buttons for canonical
			$(document).on('click.seospider', '#save_canonical, #delete_canonical', function(jqEvent){
				var buttonAction = $(this).data('action');
				saveCanonicalDataStatus(buttonAction);
			});
			
			// Bind the start analysis button
			$(document).on('click.seospider', '#start_analysis', function(jqEvent){
				$('#focus_keyword').removeClass('error');
				
				// Validate the required field
				var focusKeyword = $('#focus_keyword').val();
				if(!focusKeyword) {
					$('#focus_keyword').addClass('error').focus();
					return false;
				}
				
				// Set the running interface
				$('span.seospider-cogicon', this).addClass('running');
				$('span.seospider-labelicon-start', this).text(COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_STARTED);
				
				var linkToAnalyze = $('#analysis_dialog').data('linktoanalyze');
				// Start here the real analysis process
				startLinkAnalysis(linkToAnalyze, focusKeyword);
			});
			$(document).on('keyup.seospider', '#focus_keyword, #override_heading_content, #override_canonical_content', function(jqEvent){
				$(this).removeClass('error');
			});
			
			$(document).on('keyup.seospider', '#override_heading_content, #override_canonical_content', function(jqEvent){
				$(this).next('ul.seospider_validation_errorlist').remove();
			});
			
			// Closer dialog button
			$(document).on('click.seospider', 'label.closedialog', function(jqEvent){
				$(this).parents('#details_dialog, #analysis_dialog, #headings_dialog, #canonical_dialog').remove();
			});
			
			// Link duplicate with scroller
			$(document).on('click.seospider', 'li.seospider_duplicate a, a.seospider_duplicate', function(jqEvent){
				// Reset timeout if any
				if(typeof(arrowsTimeout) !== 'undefined') {
					clearTimeout(arrowsTimeout);
				}
				
				var anchorTarget = $(this).attr('href');
				var elementTarget = $('a[data-role="link"][href="' + anchorTarget + '"]');
				if(elementTarget.length) {
					$('html, body').animate({
						scrollTop: elementTarget.offset().top - 95
					}, 500);
				}
				// Append an indicator arrow
				$(elementTarget).next('span.seospider_indicator').remove();
				$(elementTarget).after('<span class="seospider_indicator fas fa-arrow-circle-left"></span>');
				arrowsTimeout = setTimeout(function(){
					$('span.seospider_indicator').remove();
				}, 3500);
				
				return false;
			});
			
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
				// Show progress
				var progressBar = 	'<div id="progress_bar" class="progress progress-bar progress-bar-animated progress-bar-' + type + '" role="progressbar" style="width: 0" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100">' +
										'<label class="visually-hidden">' + status + '</label>' +
									'</div>';
				
				// Build modal dialog
				var modalDialog =	'<div class="jmapmodal modal fade" id="seospider_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
										'<div class="modal-dialog">' +
											'<div class="modal-content">' +
												'<div class="modal-header">' +
									        		'<h4 class="modal-title">' + COM_JMAP_SEOSPIDER_TITLE + '</h4>' +
									        		'<label class="closeprecaching fas fa-times-circle"></label>' +
									        		'<p class="modal-subtitle">' + COM_JMAP_SEOSPIDER_PROCESS_RUNNING + '</p>' +
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
				let modalEl = document.querySelector('#seospider_process');
				let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
				modalInstance.show();
				
				// Async event progress showed and styling
				modalEl.addEventListener('shown.bs.modal', function(event) {
					$('#seospider_process div.modal-body').css({'width':'90%', 'margin':'auto'});
					$('#progress_bar').css({'width':percentage + '%'});
					
					// Start AJAX GET request for sitemap generation in the cache folder
					startSitemapCaching(targetSitemapLink);
				});
				
				// Remove backdrop after removing DOM modal
				modalEl.addEventListener('hidden.bs.modal',function(jqEvent){
					$('.modal-backdrop').remove();
					$(this).remove();
					
					// Redirect to MVC core cpanel, discard seospider
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
						let modalEl = document.querySelector('#seospider_process');
						if(modalEl) {
							bootstrap.Modal.getInstance(modalEl).hide();
						}
					}, 3500);
				}
			}
		}
		
		/**
		 * The first operation is to generate and precache the requested sitemap and links
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
					data: {'seospiderjsclient' : true}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(COM_JMAP_SEOSPIDER_ERROR_STORING_FILE, textStatus);
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
				showProgress(false, 100, 'striped', COM_JMAP_SEOSPIDER_GENERATION_COMPLETE);
				
				// Parse sitemap parameters
				var sitemapParams = parseURL(targetSitemapLink).params;
				var sitemapLang = sitemapParams.lang ? '&sitemaplang=' + sitemapParams.lang : '';
				var sitemapDataset = sitemapParams.dataset ? '&sitemapdataset=' + sitemapParams.dataset : '';
				var sitemapMenuID = sitemapParams.Itemid ? '&sitemapitemid=' + sitemapParams.Itemid : '';
				
				// Redirect to MVC core
				window.location.href = 'index.php?option=com_jmap&task=seospider.display&jsclient=1' + sitemapLang + sitemapDataset + sitemapMenuID;
			}, function(errorText, error) {
				// Do stuff and exit
				showProgress(false, 100, 'normal', errorText, 'bg-danger');
			});
		};
		
		/**
		 * Show the duplicated links dialog details with scroll interaction
		 * 
		 * @access private
		 * @return Void
		 */
		var showDuplicatesDetails = function(modalTitle, modalContents, didascalyFooter, linkToSkip) {
			var contentsString = '';
			
			if(modalContents.length) {
				$.each(modalContents, function(index, value){
					value = decodeURIComponent(value);
					if(value == linkToSkip) {
						return true;
					}
					contentsString += '<li class="seospider_duplicate"><a href="' + value + '">' + value + '</a> <label class="fas fa-arrows-alt-v" aria-hidden="true"></label></li>';
				});
			}
			
			// Build modal dialog
			var detailsDialog = '<div id="details_dialog" class="card card-primary">' +
									'<div class="card-header">' +
								    	'<h3 class="card-title">' + modalTitle + '</h3>' +
								    	'<label class="closedialog fas fa-times-circle"></label>' +
								    '</div>' +
								    '<div class="card-body card-block">' +
								    	'<ul class="seospider_duplicate">' + contentsString + '</ul>' +
								    '</div>' +
								    '<div class="card-footer">' + 
								    	didascalyFooter + 
								    	'<div>' + COM_JMAP_SEOSPIDER_SELECTED_LINK_DETAILS + 
								    		'<a class="seospider_duplicate" href="' + linkToSkip + '">' + linkToSkip + '</a>' +
								    	'</div>' +
								    '</div>' +
								 '</div>';
			// Inject elements into content body
			$('body').append(detailsDialog);
			
			// Bind the draggable feature
			$('#details_dialog').draggable({ 
				handle: 'div.card-header'
			});
		}
		
		/**
		 * Show the SEO Content Analysis dialog
		 * 
		 * @access private
		 * @return Void
		 */
		var showContentAnalysis = function(linkToAnalyze) {
			var contentsString = '';
			
			// Build modal dialog
			var analysisDialog = '<div id="analysis_dialog" class="card card-primary">' +
									'<div class="card-header">' +
								    	'<h3 class="card-title">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DIALOG_TITLE + '</h3>' +
								    	'<a class="seospider_analyzed_link badge" target="_blank" href="' + linkToAnalyze + '">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_LINK +  linkToAnalyze + '</a>' +
								    	'<label class="closedialog fas fa-times-circle"></label>' +
								    '</div>' +
								    '<div class="card-body card-block">' +
								    	'<label class="badge bg-primary analysis-labels analysis-labels-main">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_FOCUS_KEYWORD + '</label>' +
								    	'<input id="focus_keyword" class="analysis-input" type="text" value="" placeholder="' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_CHOOSE_KEYWORD + '"/>' +
								    	'<button id="start_analysis" class="btn btn-success active">' +
								    		'<span class="seospider-cogicon"></span><span class="seospider-labelicon-start">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_START  + '</span>' +
								    	'</button>' +
								    '</div>' +
								    '<div class="card-footer">' + 
								    	'<div id="pagescore">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DIALOG_FOOTER + '</div>' +
								    '</div>' +
								 '</div>';
			// Inject elements into content body
			$('body').append(analysisDialog);
			$('#analysis_dialog').data('linktoanalyze', linkToAnalyze);
			
			// Bind the draggable feature
			$('#analysis_dialog').draggable({ 
				handle: 'div.card-header'
			});
		}

		/**
		 * Show the headings dialog
		 * 
		 * @access private
		 * @param String linkToAnalyze
		 * @param String hTag
		 * @return Void
		 */
		var showHeadingsDialog = function(linkToAnalyze, hTag) {
			// Async request current real page
			var pagePromise = $.Deferred(function(defer) {
				$.ajax({
					type : "GET",
					url : linkToAnalyze,
				}).done(function(data, textStatus, jqXHR) {
					// Check response HTTP status code
					defer.resolve(data, jqXHR.status);
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					defer.resolve(null, jqXHR.status);
				});
			}).promise();

			pagePromise.then(function(responseData, status) {
				// Fetch the Hx tag and compile the current textarea
				// Set the parsed wrapped set
				var responseDataWrappedSet = $(responseData.trim());
				var headingsArray = responseDataWrappedSet.find(hTag);
				
				// Get the text of first Hx heading found
				var headingText = $(headingsArray[0]).text().trim();
				
				$('#original_heading_content').text(headingText);
			}).always(function(){
				$('div.headings-original span.seospider-cogicon').removeClass('running');
			});
			
			// Fetch the Hx override for this URL and compile the override textarea
			// Object to send to server
			var ajaxparams = {
				idtask : 'fetchHeadingOverride',
				param: {linkurl:linkToAnalyze, headingtag: hTag}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			var serverModelPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						defer.resolve(data.headingtext, data);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			serverModelPromise.then(function(message, dataResponse) {
				// Fetch the Hx override for this URL and compile the override textarea
				$('#override_heading_content').val(message);
				
				// Enable the delete btn only if there is an override
				if(message) {
					$('#delete_heading').removeAttr('disabled');
				}
				
			}).always(function(message){
				$('#override_heading_content').removeAttr('disabled');
				$('div.headings-override span.seospider-cogicon').removeClass('running');
				$('#save_heading').removeAttr('disabled').attr('data-heading', hTag);
				$('#delete_heading').attr('data-heading', hTag);
			});
			
			
			var contentsString = '';
			
			// Build modal dialog
			var headingsDialog = '<div id="headings_dialog" class="card card-primary">' +
									'<div class="card-header">' +
								    	'<h3 class="card-title">' + COM_JMAP_SEOSPIDER_HEADINGS_DIALOG_TITLE + hTag.toUpperCase() + '</h3>' +
								    	'<a class="seospider_analyzed_link badge" target="_blank" href="' + linkToAnalyze + '">' + COM_JMAP_SEOSPIDER_HEADINGS_LINK +  linkToAnalyze + '</a>' +
								    	'<label class="closedialog fas fa-times-circle"></label>' +
								    '</div>' +
								    '<div class="card-body card-block">' +
								    	'<div class="headings-original">' +
									    	'<label class="badge bg-primary headings-labels"><span class="seospider-cogicon running"></span><span class="seospider-label-desc">' + COM_JMAP_SEOSPIDER_HEADINGS_ORIGINAL_HEADING + '</span></label>' +
									    	'<textarea id="original_heading_content" class="headings-content" readonly></textarea>' +
								    	'</div>' +
								    	'<div class="headings-override">' +
									    	'<label class="badge bg-primary headings-labels"><span class="seospider-cogicon running"></span><span class="seospider-label-desc">' + COM_JMAP_SEOSPIDER_HEADINGS_OVERRIDE_HEADING + '</span></label>' +
									    	'<textarea id="override_heading_content" class="headings-content" disabled></textarea>' +
								    	'</div>' +
								    	'<button id="save_heading" class="btn btn-primary active" data-action="saveHeading" disabled>' +
								    		'<span class="fas fa-save" aria-hidden="true"></span> <span class="seospider-labelicon-start">' + COM_JMAP_SEOSPIDER_HEADINGS_SAVE  + '</span>' +
								    	'</button>' +
								    	'<button id="delete_heading" class="btn btn-danger active" data-action="deleteHeading" disabled>' +
							    			'<span class="fas fa-times-circle" aria-hidden="true"></span> <span class="seospider-labelicon-start">' + COM_JMAP_SEOSPIDER_HEADINGS_DELETE  + '</span>' +
							    		'</button>' +
								    '</div>' +
								    '<div class="card-footer">' + 
								    	'<div id="headings_opmessage"></div>' +
								    '</div>' +
								 '</div>';
			// Inject elements into content body
			$('body').append(headingsDialog);
			$('#headings_dialog').data('linktoanalyze', linkToAnalyze);
			
			// Bind the draggable feature
			$('#headings_dialog').draggable({ 
				handle: 'div.card-header'
			});
		}

		/**
		 * Show the canonical dialog
		 * 
		 * @access private
		 * @param String linkToAnalyze
		 * @return Void
		 */
		var showCanonicalDialog = function(linkToAnalyze) {
			// Async request current real page
			var pagePromise = $.Deferred(function(defer) {
				$.ajax({
					type : "GET",
					url : linkToAnalyze,
				}).done(function(data, textStatus, jqXHR) {
					// Check response HTTP status code
					defer.resolve(data, jqXHR.status);
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					defer.resolve(null, jqXHR.status);
				});
			}).promise();

			pagePromise.then(function(responseData, status) {
				// Fetch the canonical tag and compile the current input field
				// Set the parsed wrapped set
				var responseDataWrappedSet = $(responseData.trim());
				var canonical = responseDataWrappedSet.filter('link[rel=canonical]');
				
				// Found a canonical tag?
				if(canonical.length) {
					$('#original_canonical_content').val(decodeURIComponent(canonical.attr('href')));
				}
			}).always(function(){
				$('div.canonical-original span.seospider-cogicon').removeClass('running');
			});
			
			// Fetch the canonical override for this URL and compile the override input field
			// Object to send to server
			var ajaxparams = {
				idtask : 'fetchCanonicalOverride',
				param: {linkurl:linkToAnalyze}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			var serverModelPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "POST",
					url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						defer.resolve(data.canonicaltext, data);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			serverModelPromise.then(function(message, dataResponse) {
				// Fetch the canonical override for this URL and compile the override input field
				$('#override_canonical_content').val(message);
				
				// Enable the delete btn only if there is an override
				if(message) {
					$('#delete_canonical').removeAttr('disabled');
				}
			}).always(function(message){
				$('#override_canonical_content').removeAttr('disabled');
				$('div.canonical-override span.seospider-cogicon').removeClass('running');
				$('#save_canonical').removeAttr('disabled');
			});
			
			
			var contentsString = '';
			
			// Build modal dialog
			var canonicalDialog = '<div id="canonical_dialog" class="card card-primary">' +
									'<div class="card-header">' +
								    	'<h3 class="card-title">' + COM_JMAP_SEOSPIDER_CANONICAL_DIALOG_TITLE + '</h3>' +
								    	'<a class="seospider_analyzed_link badge" target="_blank" href="' + linkToAnalyze + '">' + COM_JMAP_SEOSPIDER_CANONICAL_LINK +  linkToAnalyze + '</a>' +
								    	'<label class="closedialog fas fa-times-circle"></label>' +
								    '</div>' +
								    '<div class="card-body card-block">' +
								    	'<div class="canonical-original">' +
									    	'<label class="badge bg-primary canonical-labels"><span class="seospider-cogicon running"></span><span class="seospider-label-desc">' + COM_JMAP_SEOSPIDER_CANONICAL_ORIGINAL_HEADING + '</span></label>' +
									    	'<input type="text" id="original_canonical_content" class="canonical-content" readonly />' +
								    	'</div>' +
								    	'<div class="canonical-override">' +
									    	'<label class="badge bg-primary canonical-labels"><span class="seospider-cogicon running"></span><span class="seospider-label-desc">' + COM_JMAP_SEOSPIDER_CANONICAL_OVERRIDE_HEADING + '</span></label>' +
									    	'<input type="text" id="override_canonical_content" class="canonical-content" disabled />' +
								    	'</div>' +
								    	'<button id="save_canonical" class="btn btn-primary active" data-action="saveCanonical" disabled>' +
								    		'<span class="fas fa-save"></span> <span class="seospider-labelicon-start">' + COM_JMAP_SEOSPIDER_CANONICAL_SAVE  + '</span>' +
								    	'</button>' +
								    	'<button id="delete_canonical" class="btn btn-danger active" data-action="deleteCanonical" disabled>' +
							    			'<span class="fas fa-times-circle"></span> <span class="seospider-labelicon-start">' + COM_JMAP_SEOSPIDER_CANONICAL_DELETE  + '</span>' +
							    		'</button>' +
								    '</div>' +
								    '<div class="card-footer">' + 
								    	'<div id="canonical_opmessage"></div>' +
								    '</div>' +
								 '</div>';
			// Inject elements into content body
			$('body').append(canonicalDialog);
			$('#canonical_dialog').data('linktoanalyze', linkToAnalyze);
			
			// Bind the draggable feature
			$('#canonical_dialog').draggable({ 
				handle: 'div.card-header'
			});
		}
		
		/**
		 * Process the asyncronous analysis of links showed in the SeoSpider list
		 * It performs parallel async requests for each link evaluating the HTTP status code in response and acting accordingly
		 *
		 * @access private
		 * @return Void
		 */
		var startLinksCrawling = function() {
			// Retrieve all the links to analyze on page
			var linksToAnalyze = $('a[data-role=link]');
			var successIcon = ' src="' + jmap_baseURI + 'administrator/components/com_jmap/images/icon-16-tick.png"/>';
			var failureIcon = ' src="' + jmap_baseURI + 'administrator/components/com_jmap/images/publish_x.png"/>';
			
			// Due to the page loading time feature, force the crawler delay to at least 500ms
			if(jmap_crawlerDelay < 500) {
				jmap_crawlerDelay = 500;
			}
			
			// No ajax request if no links to analyze
			if(!linksToAnalyze.length) {
				return;
			}

			$.each(linksToAnalyze, function(index, link){
				var targetCrawledLink = $('a[data-role="link"]').get(index);
				var targetStatus = $('div[data-bind="{status}"]').get(index);
				var targetTitle = $('div[data-bind="{title}"]').get(index);
				var targetDesc = $('div[data-bind="{desc}"]').get(index);
				var targetH1 = $('div[data-bind="{h1}"]').get(index);
				var targetH2 = $('div[data-bind="{h2}"]').get(index);
				var targetH3 = $('div[data-bind="{h3}"]').get(index);
				var targetCanonical = $('div[data-bind="{canonical}"]').get(index);
				var targetAnalysis = $('div.trigger_content_analysis').get(index);
				var targetPageLoad = $('div[data-bind="{pageload}"]').get(index);
				
				promisesCollection[index] = $.Deferred(function(defer) {
					setTimeout(function(){
						// Save the start time of this async promise
						var beforeDateObject = new Date()
						promisesCollectionTimings[index] = beforeDateObject.getTime();
						
						$.ajax({
							type : "GET",
							url : $(link).attr('href'),
						}).done(function(data, textStatus, jqXHR) {
							// Check response HTTP status code
							defer.resolve(data, jqXHR.status);
						}).fail(function(jqXHR, textStatus, errorThrown) {
							// Error found
							defer.resolve(null, jqXHR.status);
						});
					}, index * jmap_crawlerDelay);
				}).promise();
				
				promisesCollection[index].then(function(responseData, status) {
					// STEP 1 - Status validation and reporting
					if(status == 200) {
						$(targetStatus).html('<span class="badge bg-success seospider hasTooltip nocursor" title="' + COM_JMAP_SEOSPIDER_LINKVALID + '">' + status + '</span>');
					} else {
						$(targetStatus).html('<span class="badge bg-danger seospider hasTooltip nocursor" title="' + COM_JMAP_SEOSPIDER_LINK_NOVALID + '">' + status + '</span>');
						$(targetTitle).html('-');
						$(targetDesc).html('-');
						$(targetH1).html('-');
						$(targetH2).html('-');
						$(targetH3).html('-');
						$(targetCanonical).html('-');
						$(targetAnalysis).remove();
						$(targetPageLoad).html('-');
						return;
					}
					
					// Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());

					// STEP 2 - Title retrieval and reporting
					var title = responseDataWrappedSet.filter('title').text().trim() || '-';
					var titleBadge = '';
					// Manage title validity
					if(title && title != '-') {
						switch(true) {
							case (title.length < 50):
								titleBadge = '<div class="badge bg-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_TITLE_TOOSHORT_DESC + '">' + COM_JMAP_SEOSPIDER_TITLE_TOOSHORT + '</div>';
								break;
							
							case (title.length > 90):
								titleBadge = '<div class="badge bg-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_TITLE_TOOLONG_DESC + '">' + COM_JMAP_SEOSPIDER_TITLE_TOOLONG + '</div>';
								break;
						}
					} else {
						titleBadge = '<div class="badge bg-danger seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_TITLE_MISSING_DESC + '">' + COM_JMAP_SEOSPIDER_TITLE_MISSING + '</div>';
					}
					$(targetTitle).html(titleBadge + '<div class="seospider_textlabel">' + title + '</div>');
					linksToAnalyze[index]['seospider_title'] = title;
					
					// STEP 3 - Description retrieval and reporting
					var description = responseDataWrappedSet.filter('meta[name=description]').attr('content') || '';
					var descriptionBadge = '';
					description = description.trim();
					description = description || '-';
					// Manage description validity
					if(description && description != '-') {
						switch(true) {
							case (description.length < 130):
								descriptionBadge = '<div class="badge bg-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT_DESC + '">' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOSHORT + '</div>';
								break;
							
							case (description.length > 180):
								descriptionBadge = '<div class="badge bg-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG_DESC + '">' + COM_JMAP_SEOSPIDER_DESCRIPTION_TOOLONG + '</div>';
								break;
						}
					} else {
						descriptionBadge = '<div class="badge bg-danger seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING_DESC + '">' + COM_JMAP_SEOSPIDER_DESCRIPTION_MISSING + '</div>';
					}
					$(targetDesc).html(descriptionBadge + '<div class="seospider_textlabel">' + description + '</div>');
					linksToAnalyze[index]['seospider_description'] = description;
					
					// STEP 4 - Headers retrieval and reporting
					var H1Array = new Array();
					var isHeadingH1Override = '';
					var isHeadingH1OverrideText = '';
					$.each(responseDataWrappedSet.find('h1'), function (index, headerTag) {
						// Mark as an override heading
						if(!isHeadingH1Override) {
							isHeadingH1Override = $(headerTag).data('jmap-heading-override') ? ' seospider-headings-override' : '';
							isHeadingH1OverrideText = isHeadingH1Override ? COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE_ACTIVE : '';
						}
						
						// If the first heading is overriden mark it as bold
						if(index == 0 && isHeadingH1Override) {
							H1Array[index] = '<b class="badge bg-warning seospider-headings nodash">' + $(headerTag).text() + '</b>';
						} else {
							H1Array[index] = $(headerTag).text();
						}
					});
					var H1 = H1Array.join(' | ') || '-';
					if(jmap_overrideheadings && H1 != '-') {
						H1 = '<span class="seospider seospider-headings hasTooltip' + isHeadingH1Override + '" title="' + COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE + isHeadingH1OverrideText +'">' + H1 + '</span>';
					}
					
					var H2Array = new Array();
					var isHeadingH2Override = '';
					var isHeadingH2OverrideText = '';
					$.each(responseDataWrappedSet.find('h2'), function (index, headerTag) {
						// Mark as an override heading
						if(!isHeadingH2Override) {
							isHeadingH2Override = $(headerTag).data('jmap-heading-override') ? ' seospider-headings-override' : '';
							isHeadingH2OverrideText = isHeadingH2Override ? COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE_ACTIVE : '';
						}
						
						// If the first heading is overriden mark it as bold
						if(index == 0 && isHeadingH2Override) {
							H2Array[index] = '<b class="badge bg-warning seospider-headings nodash">' + $(headerTag).text() + '</b>';
						} else {
							H2Array[index] = $(headerTag).text();
						}
					});
					var H2 = H2Array.join(' | ') || '-';
					if(jmap_overrideheadings && H2 != '-') {
						H2 = '<span class="seospider seospider-headings hasTooltip' + isHeadingH2Override + '" title="' + COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE + isHeadingH2OverrideText + '">' + H2 + '</span>';
					}
					
					var H3Array = new Array();
					var isHeadingH3Override = '';
					var isHeadingH3OverrideText = '';
					$.each(responseDataWrappedSet.find('h3'), function (index, headerTag) {
						// Mark as an override heading
						if(!isHeadingH3Override) {
							isHeadingH3Override = $(headerTag).data('jmap-heading-override') ? ' seospider-headings-override' : '';
							isHeadingH3OverrideText = isHeadingH3Override ? COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE_ACTIVE : '';
						}
						
						// If the first heading is overriden mark it as bold
						if(index == 0 && isHeadingH3Override) {
							H3Array[index] = '<b class="badge bg-warning seospider-headings nodash">' + $(headerTag).text() + '</b>';
						} else {
							H3Array[index] = $(headerTag).text();
						}
					});
					var H3 = H3Array.join(' | ') || '-';
					if(jmap_overrideheadings && H3 != '-') {
						H3 = '<span class="seospider seospider-headings hasTooltip' + isHeadingH3Override + '" title="' + COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE + isHeadingH3OverrideText + '">' + H3 + '</span>';
					}
					
					$(targetH1).html(H1);
					$(targetH2).html(H2);
					$(targetH3).html(H3);
					
					// Report missing H1 and H2 tags
					if(!H1Array.length && !H2Array.length) {
						var noticeHeadersMissing = '<div class="badge bg-danger seospider seospider-badge-padding hasTooltip" title="' + COM_JMAP_SEOSPIDER_HEADERS_MISSING_DESC + '">' + COM_JMAP_SEOSPIDER_HEADERS_MISSING + '</div>';
						$(targetH1).html(noticeHeadersMissing);
						$(targetH2).html(noticeHeadersMissing);
					}
					
					// STEP 5 - Canonical retrieval and reporting
					var canonical = responseDataWrappedSet.filter('link[rel=canonical]');
					var canonicalValue = canonical.attr('href') || '';
					canonicalValue = decodeURIComponent(canonicalValue);
					
					// Mark as an override canonical
					if(jmap_overridecanonical) {
						var isCanonicalOverrideEmpty = canonicalValue ? '' : ' seospider-canonical-override-empty';
						var isCanonicalOverride = $(canonical).data('jmap-canonical-override') ? ' seospider-canonical-override' : '';
						var isCanonicalOverrideText = isCanonicalOverride ? COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE_ACTIVE : '';
						$(targetCanonical).html('<span class="fas seospider seospider-canonical hasTooltip' + isCanonicalOverride + isCanonicalOverrideEmpty + '" title="' + COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE + isCanonicalOverrideText + '">' + canonicalValue + '</span>');
					} else {
						$(targetCanonical).text(canonicalValue);
					}
					
					// STEP 6 - Count duplicated titles
					if(title && title != '-') {
						// Initialize as Array if not defined
						if(typeof(titlesCollection[generateHash(title)]) === 'undefined'){
							titlesCollection[generateHash(title)] = new Array();
						}
						titlesCollection[generateHash(title)].push(link);
					}
					
					// STEP 7 - Count duplicated descriptions
					if(description && description != '-') {
						// Initialize as Array if not defined
						if(typeof(descriptionsCollection[generateHash(description)]) === 'undefined'){
							descriptionsCollection[generateHash(description)] = new Array();
						}
						descriptionsCollection[generateHash(description)].push(link);
					}
					
					// STEP 8 - Check if the noindex directive is in place
					var indexingDirective = responseDataWrappedSet.filter('meta[name=robots]').attr('content') || '';
					if(indexingDirective) {
						var isNoIndex = indexingDirective.indexOf('noindex') >= 0;
						if(isNoIndex) {
							$(targetCrawledLink).before('<div class="badge bg-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_NOINDEX_DESC + '">' + COM_JMAP_SEOSPIDER_NOINDEX + '</div>');
						}
						var isNoFollow = indexingDirective.indexOf('nofollow') >= 0;
						if(isNoFollow) {
							$(targetCrawledLink).before('<div class="badge bg-warning seospider hasTooltip" title="' + COM_JMAP_SEOSPIDER_NOFOLLOW_DESC + '">' + COM_JMAP_SEOSPIDER_NOFOLLOW + '</div>');
						}
					}
					
					// STEP 9 - Calculate the page loading time
					// When defered object complete get new timing
					var afterDateObject = new Date();
					var afterTiming = afterDateObject.getTime();

					// Find elapsed time to load site and format for users
					var elapsedTiming = (afterTiming - promisesCollectionTimings[index]) / 1000;
					// Detect speed level based on this test
					$.each(mappingRatings, function(index, mappingObject) {
						// Found the level based on this timing test
						if(elapsedTiming > mappingObject.lowerlimit && elapsedTiming < mappingObject.upperlimit) {
							$(targetPageLoad).html('<div title="' + mappingObject.tooltip + '" class="badge bg-' + mappingObject.labelcolor + ' seospider nodash hasLeftTooltip">' + elapsedTiming + 's</div>');
						}
					});
				}).always(function(){
					var analyzedLink = $(link).attr('href');
					
					// Refresh tooltips
					[].slice.call(document.querySelectorAll('tr[data-link="' + analyzedLink + '"] *.seospider.hasTooltip')).map(function (tooltipEl) {
						let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
							trigger:'hover', 
							placement:'top', 
							html: true
						});
						
						return tooltipInstance;
					});
					[].slice.call(document.querySelectorAll('tr[data-link="' + analyzedLink + '"] *.seospider.hasLeftTooltip')).map(function (tooltipEl) {
						let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
							trigger:'hover', 
							placement:'left', 
							container:'body'
						});
						
						return tooltipInstance;
					});
				});
			});
			
			// When all promises are resolved start the async duplicated title/desc count
			$.when.apply($, promisesCollection).then(function() {
				// Start analysis for each link
				$.each(linksToAnalyze, function(index, link){
					// Find the target elements
					var targetTitleDuplicates = $('div[data-bind="{title-duplicates}"]').get(index);
					var targetDescDuplicates = $('div[data-bind="{desc-duplicates}"]').get(index);
					
					// Calculate duplicates, 0 or -1 AKA no duplicates, > 0 AKA at least 1 duplicate
					if(link['seospider_title']) {
						var thisTitleHash = generateHash(link['seospider_title']);
						
						var titlesDuplicates = 0;
						if(typeof(titlesCollection[thisTitleHash]) !== 'undefined') {
							titlesDuplicates = parseInt(titlesCollection[thisTitleHash].length) - 1;
						}
						
						titlesDuplicates = titlesDuplicates > 0 ? titlesDuplicates : 0;
						
						// Find the correct badge class
						var badgeTitleClass = titlesDuplicates > 0 ? 'bg-danger' : 'bg-success';
						var badgeDetails = titlesDuplicates > 0 ? COM_JMAP_SEOSPIDER_OPEN_DETAILS : '';
						
						// Assign badge
						$(targetTitleDuplicates).html('<span class="badge ' + badgeTitleClass + ' seospider-duplicates hasTooltip" title="' + badgeDetails + '">' + titlesDuplicates + '</span>');
						
						// Disable and exclude no duplicates badge
						if(!titlesDuplicates) {
							$(targetTitleDuplicates).addClass('noduplicates');
						}
					} else {
						// Fallback
						$(targetTitleDuplicates).html('-').addClass('noduplicates');
					}
					$(targetTitleDuplicates).attr('data-link', link.href);
					$(targetTitleDuplicates).attr('data-titlehash', thisTitleHash);
					
					if(link['seospider_description']) {
						var thisDescriptionHash = generateHash(link['seospider_description']);
						
						var descriptionsDuplicates = 0;
						if(typeof(descriptionsCollection[thisDescriptionHash]) !== 'undefined') {
							descriptionsDuplicates = parseInt(descriptionsCollection[thisDescriptionHash].length) - 1;
						}
						
						descriptionsDuplicates = descriptionsDuplicates > 0 ? descriptionsDuplicates : 0;
						
						// Find the correct badge class
						var badgeDescriptionClass = descriptionsDuplicates > 0 ? 'bg-danger' : 'bg-success';
						var badgeDetails = descriptionsDuplicates > 0 ? COM_JMAP_SEOSPIDER_OPEN_DETAILS : '';
						
						// Assign badge
						$(targetDescDuplicates).html('<span class="badge ' + badgeDescriptionClass + ' seospider-duplicates hasTooltip" title="' + badgeDetails + '">' + descriptionsDuplicates + '</span>');
						
						// Disable and exclude no duplicates badge
						if(!descriptionsDuplicates) {
							$(targetDescDuplicates).addClass('noduplicates');
						}
					} else {
						// Fallback
						$(targetDescDuplicates).html('-').addClass('noduplicates');
					}
					
					// Assign data hash
					$(targetDescDuplicates).attr('data-link', link.href);
					$(targetDescDuplicates).attr('data-descriptionhash', thisDescriptionHash);
				});
				
				// Refresh tooltips
				[].slice.call(document.querySelectorAll('*.seospider-duplicates.hasTooltip')).map(function (tooltipEl) {
					let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
						trigger:'hover', 
						placement:'top', 
						html: true
					});
					
					return tooltipInstance;
				});
				
				var seospiderTable = $('table.seospiderlist').clone();
				$(seospiderTable).find('*.bg-success').wrap('<font COLOR="#FFFFFF"></font>').parents('td').attr({'BGCOLOR':'#3c763d'});
				$(seospiderTable).find('*.bg-danger').wrap('<font COLOR="#FFFFFF"></font>').parents('td').attr({'BGCOLOR':'#d9534f'});
				$(seospiderTable).find('*.bg-warning').wrap('<font COLOR="#FFFFFF"></font>').parents('td').attr({'BGCOLOR':'#f89406'});
				$(seospiderTable).find('*.bg-warning:not(.nodash)').append(' - ');
				$(seospiderTable).find('div[data-bind]').filter(function(index){
					return $(this).text() === '-';
				}).text(' ');
				$(seospiderTable).find('br').remove();

				var seospiderTableHtml = seospiderTable.html();
				seospiderTableHtml = seospiderTableHtml.replace(/<a/g, '<div');
				seospiderTableHtml = seospiderTableHtml.replace(/<\/a>/g, '</div>');

				// Create a unique file name for download
				var saveDate = new Date();
				var saveDateYear = saveDate.getFullYear();
				
				var saveDateMonth = parseInt(saveDate.getMonth()) + 1;
				saveDateMonth = saveDateMonth < 10 ? '0' + saveDateMonth : saveDateMonth;
				
				var saveDateDay = saveDate.getDate();
				saveDateDay = saveDateDay < 10 ? '0' + saveDateDay : saveDateDay;
				
				var saveDateHour = saveDate.getHours();
				saveDateHour = saveDateHour < 10 ? '0' + saveDateHour : saveDateHour;
				
				var saveDateMinute = saveDate.getMinutes();
				saveDateMinute = saveDateMinute < 10 ? '0' + saveDateMinute : saveDateMinute;
				
				var saveDateSecond = saveDate.getSeconds();
				saveDateSecond = saveDateSecond < 10 ? '0' + saveDateSecond : saveDateSecond;
				
				var filename = 'seospider_report_' + 
							    saveDateYear + '-' +
							    saveDateMonth + '-' +
							    saveDateDay + '_' +
							    saveDateHour + ':' +
							    saveDateMinute + ':' +
							    saveDateSecond + '.xls';

				$('#toolbar-arrow-down-2').after('<a id="toolbar-download-link" class="btn btn-sm btn-primary"><span class="icon-arrow-down-2"></span> ' + COM_JMAP_EXPORT_XLS + '</a>');
				$('#toolbar-download-link').attr('href', 'data:text/html;charset=utf-8,' + encodeURIComponent('<table>' + seospiderTableHtml + '</table>'))
												.attr('download', filename);
				$('#toolbar-arrow-down-2').remove();
				
				// Enable custom element buttons
				$('#toolbar-upload').removeAttr('disabled');
				
				$('#toolbar-download').removeAttr('disabled');
				var arrowDownTask = $('#toolbar-download').data('task');
				$('#toolbar-download').attr('task', arrowDownTask);
				
			});
		};
		
		/**
		 * Process the asyncronous analysis of links showed in the SeoSpider list
		 * It performs parallel async requests for each link evaluating the HTTP status code in response and acting accordingly
		 *
		 * @access private
		 * @param String linkToAnalyze
		 * @return Void
		 */
		var startLinkAnalysis = function(linkToAnalyze, focusKeyword) {
			// No ajax request if no valid link and keyword specified
			if(!linkToAnalyze || !focusKeyword) {
				// Reset the analyzing button
				$('span.seospider-cogicon').removeClass('running');
				$('span.seospider-labelicon-start').text(COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_START);
				return;
			}
			
			// Clear previous results
			$('label.label-results, ul.analysis-results, #pagescore_slider, #pagescore_numeric').remove();
			
			// Focus keyword regular expression object
			var reObject = new RegExp(focusKeyword, 'gi');
			
			// Initialize semaphores
			var semaphoreRed = '<div class="trigger_content_analysis_red"></div>';
			var semaphoreYellow = '<div class="trigger_content_analysis_yellow"></div>';
			var semaphoreGreen = '<div class="trigger_content_analysis_green"></div>';
			
			// Init vars
			var keywordRepetitionsInTags = 0;
			var finalPageScore = 0;
			var maxScore = 6;
			
			// Async request
			var analysisPromise = $.Deferred(function(defer) {
				$.ajax({
					type : "GET",
					url : linkToAnalyze,
				}).done(function(data, textStatus, jqXHR) {
					// Check response HTTP status code
					defer.resolve(data, jqXHR.status);
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					defer.resolve(null, jqXHR.status);
				});
			}).promise();

			analysisPromise.then(function(responseData, status) {
				// STEP 1 - Status validation and reporting
				if(status == 200 && responseData) {
					// All went ok, now analyze and report data
					$('#analysis_dialog div.card-block').append('<label class="badge bg-primary analysis-labels label-results">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_RESULTS + '</label>');
					var ulResults = $('<ul/>');
					ulResults.addClass('analysis-results');
					
					// STEP 1 - Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());

					// STEP 2 - Title retrieval and reporting
					var title = responseDataWrappedSet.filter('title').text().trim() || '';
					var keywordInTitle = title.match(reObject);
					if(keywordInTitle) {
						var titleResult = semaphoreGreen + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_TITLE_KEYWORD;
						keywordRepetitionsInTags += keywordInTitle.length;
						finalPageScore += 1;
					} else {
						var titleResult = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_TITLE_NOKEYWORD;
					}
					ulResults.append('<li>' + titleResult + '</li>');
					
					// STEP 3 - Description retrieval and reporting
					var description = responseDataWrappedSet.filter('meta[name=description]').attr('content') || '';
					var keywordInDescription = description.match(reObject);
					if(keywordInDescription) {
						var descriptionResult = semaphoreGreen + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DESCRIPTION_KEYWORD;
						keywordRepetitionsInTags += keywordInDescription.length;
						finalPageScore += 1;
					} else {
						var descriptionResult = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_DESCRIPTION_NOKEYWORD;
					}
					ulResults.append('<li>' + descriptionResult + '</li>');
					
					// STEP 4 - Headers retrieval and reporting
					var H1Array = new Array();
					$.each(responseDataWrappedSet.find('h1'), function (index, headerTag) {
						H1Array[index] = $(headerTag).text();
					});
					var H1 = H1Array.join(' ') || '';
					var keywordInH1 = H1.match(reObject);
					
					var H2Array = new Array();
					$.each(responseDataWrappedSet.find('h2'), function (index, headerTag) {
						H2Array[index] = $(headerTag).text();
					});
					var H2 = H2Array.join(' ') || '';
					var keywordInH2 = H2.match(reObject);
					
					var H3Array = new Array();
					$.each(responseDataWrappedSet.find('h3'), function (index, headerTag) {
						H3Array[index] = $(headerTag).text();
					});
					var H3 = H3Array.join(' ') || '';
					var keywordInH3 = H3.match(reObject);
					
					if(keywordInH1) {
						var headersResult = semaphoreGreen + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_H1_KEYWORD;
						finalPageScore += 1;
					} else if(keywordInH2 || keywordInH3) {
						var headersResult = semaphoreYellow + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_H2_H3_KEYWORD;
						finalPageScore += 0.5;
					} else {
						var headersResult = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_HEADERS_NO_KEYWORD;
					}
					ulResults.append('<li>' + headersResult + '</li>');
					if(keywordInH1) {
						keywordRepetitionsInTags += keywordInH1.length;
					}
					if(keywordInH2) {
						keywordRepetitionsInTags += keywordInH2.length;
					}
					if(keywordInH3) {
						keywordRepetitionsInTags += keywordInH3.length;
					}
					
					// STEP 5 - Get the URL of the page
					var URL = linkToAnalyze;
					URL = URL.replace(/-/g, ' ');
					var keywordInURL = URL.match(reObject);
					if(keywordInURL) {
						var keywordinurlResult = semaphoreGreen + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_INURL_KEYWORD;
						finalPageScore += 1;
					} else {
						var keywordinurlResult = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_INURL_NOKEYWORD;
					}
					ulResults.append('<li>' + keywordinurlResult + '</li>');
					
					// STEP 6 - Count occurrences in the page - title, description and headers
					var keywordTotal = responseDataWrappedSet.text().match(reObject);
					if(keywordTotal) {
						var keywordTotalReps = keywordTotal.length;
						if((keywordTotalReps - keywordRepetitionsInTags) > 0 ) {
							var keywordRepetitions = semaphoreGreen + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_REPS_KEYWORD;
							finalPageScore += 1;
						} else {
							var keywordRepetitions = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_REPS_NOKEYWORD;
						}
					} else {
						var keywordRepetitions = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_REPS_NOKEYWORD;
					}
					ulResults.append('<li>' + keywordRepetitions + '</li>');
					
					// STEP 7 - Check in the ALT attribute of images
					var altImagesKeyword = responseDataWrappedSet.find('img[alt*="' + focusKeyword.replace(/"/g, '\\"') + '"]');
					if(altImagesKeyword.length) {
						var altImagesResult = semaphoreGreen + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_ALTIMAGES_KEYWORD;
						finalPageScore += 1;
					} else {
						var altImagesResult = semaphoreRed + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_ALTIMAGES_NOKEYWORD;
					}
					ulResults.append('<li>' + altImagesResult + '</li>');
					
					// STEP 8 - Calculation of the page score
					var sliderClass = '';
					switch(true) {
						case (finalPageScore <= 2):
							sliderClass = 'score_red';
							resultClass = '';
						break;
							
						case (finalPageScore > 2 && finalPageScore <= 4):
							sliderClass = 'score_yellow';
						break;
						
						case (finalPageScore > 4 && finalPageScore <= 6):
							sliderClass = 'score_green';
						break;
					}
					// Append page score
					$('#pagescore').append('<div id="pagescore_slider"><div id="inner_slider"></div></div><span id="pagescore_numeric" class="badge ' + sliderClass + '">'  + finalPageScore + ' / ' + maxScore + '</span>');
					$('#pagescore_slider #inner_slider').css('width', ( finalPageScore * 16.66) + '%' ).addClass(sliderClass);
					
					// Append analysis results
					$('#analysis_dialog div.card-block').append(ulResults);
				} else {
					// An error in the AJAX request occurred, kindly inform user and return
					$('#analysis_dialog div.card-block').append('<label class="badge bg-danger analysis-labels label-results">' + COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_ERROR + status + '</label>');
					return;
				}
			}).always(function(){
				// Reset the analyzing button
				$('span.seospider-cogicon').removeClass('running');
				$('span.seospider-labelicon-start').text(COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_START);
			});
		};

		/**
		 * Manage the data saving for the headings tags
		 * in the model database table
		 * 
		 * @access private
		 * @param String action
		 * @param String heading
		 * @return Void
		 */
		var saveDataStatus = function(action, heading) {
			// Validate the overriden textarea
			if(action == 'saveHeading' && !$('#override_heading_content').val()) {
				$('#override_heading_content').addClass('error')
											  .next('ul.seospider_validation_errorlist').remove().end()
											  .after('<ul class="seospider_validation_errorlist"><li class="validation badge bg-danger">' + COM_JMAP_ROBOTS_REQUIRED +'</li></ul>');
				return;
			}
			if(action == 'deleteHeading' && !$('#override_heading_content').val()) {
				$('#override_heading_content').removeClass('error').next('ul.seospider_validation_errorlist').remove();
			}
			
			// Start the cog icon engine
			$('div.headings-override span.seospider-cogicon').addClass('running');
			
			// Retrieve informations
			var targetLink = $('#headings_dialog a.seospider_analyzed_link').attr('href');
			var overrideContent = $('#override_heading_content').val();
			
			// If the HTML support is not enabled ensure to clean in realtime even the override content textarea
			if(typeof(jmap_overrideheadingsHtml) !== 'undefined' && parseInt(jmap_overrideheadingsHtml) != 1) {
				var cleanedOverrideContent = overrideContent.replace(/(<([^>]+)>)/ig,"");
				$('#override_heading_content').val(cleanedOverrideContent);
			}
			
			// Object to send to server
			var ajaxparams = {
				idtask : action,
				param : {
					linkurl : targetLink,
					headingTag : heading,
					fieldValue : overrideContent
				}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			// Request JSON2JSON
			var headingsPromise = $.Deferred(function(defer) {
				var footerMessage = action == 'saveHeading' ? COM_JMAP_SEOSPIDER_HEADINGS_SAVING_OVERRIDE : COM_JMAP_SEOSPIDER_HEADINGS_DELETING_OVERRIDE;
				$('#headings_opmessage').html('<label class="badge bg-warning"><span class="seospider-cogicon running"></span><span class="seospider-label-desc">' + footerMessage + '</span></label>');
				
				$.ajax({
					type : "POST",
					url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						var userMessage = data.exception_message || COM_JMAP_SEOSPIDER_HEADINGS_SAVED_MESSAGE;
						defer.resolve(userMessage);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			headingsPromise.then(function(message) {
				// Append the operation result to the footer 
				$('#headings_opmessage').html('<label class="badge bg-success"><span class="fas fa-check-circle" aria-hidden="true"></span> ' + message + '</label>');
				
				// If this is a save action ensure that the 'delete' button is always enabled
				if(action == 'saveHeading') {
					$('#delete_heading').removeAttr('disabled');
				}
				
				// If this is a delete action go on to clear the textarea for this heading
				if(action == 'deleteHeading') {
					$('#override_heading_content').val('');
					$('#delete_heading').attr('disabled', true);
				}
				
				// Refetch the updated current tag content
				$.get(targetLink, function(responseData) {
					// Fetch the Hx tag and compile the current textarea
					// Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());
					var headingsArray = responseDataWrappedSet.find(heading);
					
					// Get the text of first Hx heading found
					var headingText = $(headingsArray[0]).text().trim();
					
					$('#original_heading_content').text(headingText);
					
					// On save/delete action refresh even the SEO Spider row
					// Check if there are other headings
					var additionalHeadings = '';
					if(headingsArray.length > 1) {
						var headingsArrayJoin = new Array();
						headingsArray.each(function(index, heading){
							if(index < 1) {
								return true;
							}
							headingsArrayJoin[index] = $(heading).text();
						});
						additionalHeadings =  headingsArrayJoin.join(' | ');
					}
					
					var rowScope = $('table.seospiderlist tr[data-link="' + targetLink + '"]');
					if(action == 'saveHeading') {
						$('div[data-bind="{' + heading + '}"] span.seospider-headings', rowScope).html('<b class="badge bg-warning seospider-headings">' + headingText + '</b>' + additionalHeadings)
																								 .attr('title', COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE + COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE_ACTIVE)
																								 .attr('data-bs-original-title', COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE + COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE_ACTIVE);
					} else if(action == 'deleteHeading'){
						$('div[data-bind="{' + heading + '}"] span.seospider-headings', rowScope).html(headingText + additionalHeadings)
																								 .attr('title', COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE)
																								 .attr('data-bs-original-title', COM_JMAP_SEOSPIDER_HEADINGS_EDIT_OVERRIDE);
					}
				});
			}, function(errorText, error) {
				// Do stuff and exit
				$('#headings_opmessage').html('<label class="badge bg-danger"><span class="fas fa-exclamation-triangle" aria-hidden="true"></span> ' + errorText+ '</label>');
			}).always(function(){
				// Start the cog icon engine
				$('div.headings-override span.seospider-cogicon').removeClass('running');
				setTimeout(function(){
					$('#headings_opmessage').empty();
				}, 2000);
			});
		};
		
		/**
		 * Manage the data saving for the canonical tags
		 * in the model database table
		 * 
		 * @access private
		 * @param String action
		 * @return Void
		 */
		var saveCanonicalDataStatus = function(action) {
			// Retrieve informations and validate the URL for the canonical field
			var targetLink = $('#canonical_dialog a.seospider_analyzed_link').attr('href');
			var overrideContent = $('#override_canonical_content').val();
			var urlValidator = new RegExp("^https?://(.+.)+.{2,4}(/.*)?$", "");
			var isValidUrl = urlValidator.test(overrideContent);
			
			// Validate the overriden input field
			if(action == 'saveCanonical' && (!overrideContent || !isValidUrl)) {
				$('#override_canonical_content').addClass('error')
											 	.next('ul.seospider_validation_errorlist').remove().end()
												.after('<ul class="seospider_validation_errorlist"><li class="validation badge bg-danger">' + COM_JMAP_CANONICAL_URL_REQUIRED +'</li></ul>');
				return;
			}
			if(action == 'deleteCanonical' && (!overrideContent || !isValidUrl)) {
				$('#override_canonical_content').removeClass('error').next('ul.seospider_validation_errorlist').remove();
			}
			
			// Start the cog icon engine
			$('div.canonical-override span.seospider-cogicon').addClass('running');
			
			// Object to send to server
			var ajaxparams = {
				idtask : action,
				param : {
					linkurl : targetLink,
					fieldValue : overrideContent
				}
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			// Request JSON2JSON
			var canonicalPromise = $.Deferred(function(defer) {
				var footerMessage = action == 'saveCanonical' ? COM_JMAP_SEOSPIDER_CANONICAL_SAVING_OVERRIDE : COM_JMAP_SEOSPIDER_CANONICAL_DELETING_OVERRIDE;
				$('#canonical_opmessage').html('<label class="badge bg-warning"><span class="seospider-cogicon running"></span><span class="seospider-label-desc">' + footerMessage + '</span></label>');
				
				$.ajax({
					type : "POST",
					url: "../administrator/index.php?option=com_jmap&task=ajaxserver.display&format=json",
					dataType : 'json',
					context : this,
					data : {
						data : uniqueParam
					}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(data.exception_message, textStatus);
						return false;
					}
					
					// Check response all went well
					if(data.result) {
						var userMessage = data.exception_message || COM_JMAP_SEOSPIDER_CANONICAL_SAVED_MESSAGE;
						defer.resolve(userMessage);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			canonicalPromise.then(function(message) {
				// Append the operation result to the footer 
				$('#canonical_opmessage').html('<label class="badge bg-success"><span class="fas fa-check-circle"></span> ' + message + '</label>');
				
				// If this is a save action ensure that the 'delete' button is always enabled
				if(action == 'saveCanonical') {
					$('#delete_canonical').removeAttr('disabled');
				}
				
				// If this is a delete action go on to clear the input value for this canonical tag, then restore the delete button as disabled
				if(action == 'deleteCanonical') {
					$('#override_canonical_content').val('');
					$('#delete_canonical').attr('disabled', true);
				}
				
				// Refetch the updated current tag content
				$.get(targetLink, function(responseData) {
					// Fetch the canonical tag and compile the current input field again
					// Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());
					var canonical = responseDataWrappedSet.filter('link[rel=canonical]').attr('href') || '';
					canonical = decodeURIComponent(canonical);
					
					$('#original_canonical_content').val(canonical);
					
					var rowScope = $('table.seospiderlist tr[data-link="' + targetLink + '"]');
					if(action == 'saveCanonical') {
						$('div[data-bind="{canonical}"] span.seospider-canonical', rowScope).addClass('seospider-canonical-override')
																							.removeClass('seospider-canonical-override-empty')
																							.text(canonical)
																						    .attr('title', COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE + COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE_ACTIVE)
																						    .attr('data-bs-original-title', COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE + COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE_ACTIVE);
					} else if(action == 'deleteCanonical'){
						var bindedCanonical = $('div[data-bind="{canonical}"] span.seospider-canonical', rowScope);
						
						bindedCanonical.removeClass('seospider-canonical-override').text(canonical)
								 	   .attr('title', COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE)
									   .attr('data-bs-original-title', COM_JMAP_SEOSPIDER_CANONICAL_EDIT_OVERRIDE);
						// Restore the empty canonical class if the canonical tag is empty so missing
						if(!canonical) {
							bindedCanonical.addClass('seospider-canonical-override-empty');
						}
					}
				});
			}, function(errorText, error) {
				// Do stuff and exit
				$('#canonical_opmessage').html('<label class="badge bg-danger"><span class="fas fa-exclamation-triangle"></span> ' + errorText+ '</label>');
			}).always(function(){
				// Start the cog icon engine
				$('div.canonical-override span.seospider-cogicon').removeClass('running');
				setTimeout(function(){
					$('#canonical_opmessage').empty();
				}, 2000);
			});
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
			
			[].slice.call(document.querySelectorAll('div.hasLeftTooltip')).map(function (tooltipEl) {
				let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
					trigger:'hover', 
					placement:'left', 
					html: true
				});
				
				return tooltipInstance;
			});
			
			/// Execute analysis only if the view Seospider is executed
			if($('table.seospiderlist').length) {
				var arrowDownTask = $('#toolbar-arrow-down-2').attr('task');
				$('#toolbar-arrow-down-2').attr('disabled', true).attr('data-task', arrowDownTask).removeAttr('task');
				
				var uploadTask = $('#toolbar-upload').attr('task');
				$('#toolbar-upload').attr('disabled', true).attr('data-task', uploadTask).removeAttr('task');
				
				var downloadTask = $('#toolbar-download').attr('task');
				$('#toolbar-download').attr('disabled', true).attr('data-task', downloadTask).removeAttr('task');
				
				// Start to analyze the validation status if enabled the async mode
				startLinksCrawling();
			}
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapSeoSpider = new SeoSpider();
	});
})(jQuery);