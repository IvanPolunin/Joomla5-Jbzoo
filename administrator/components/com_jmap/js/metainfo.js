/**
 * Meta info manager
 * 
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var Metainfo = function() {
		/**
		 * Target sitemap link
		 * 
		 * @access private
		 * @var String
		 */
		var targetSitemapLink = null;
		
		/**
		 * Message timeout handler
		 * 
		 * @access private
		 * @var Object
		 */
		var msgTimeout = null;
		
		/**
		 * Promises array
		 * 
		 * @access private
		 * @var Array
		 */
		var promisesCollection = new Array();
		
		/**
		 * Promises array collection for saving multiple records
		 * 
		 * @access private
		 * @var Array
		 */
		var savePromisesCollection = new Array();
		
		/**
		 * Max title length on desktop
		 * 
		 * @access private
		 * @var Integer
		 */
		var maxLengthTitleDesktop = 573;
		
		/**
		 * Max description length on desktop
		 * 
		 * @access private
		 * @var Integer
		 */
		var maxLengthDescriptionDesktop = 930;

		/**
		 * Max title length on mobile
		 * 
		 * @access private
		 * @var Integer
		 */
		var maxLengthTitleMobile = 550;
		
		/**
		 * Max description length on mobile
		 * 
		 * @access private
		 * @var Integer
		 */
		var maxLengthDescriptionMobile = 905;

		/**
		 * Title font on desktop
		 * 
		 * @access private
		 * @var String
		 */
		var titleFontDesktop = '20px Arial,sans-serif';
		
		/**
		 * Description font on desktop
		 * 
		 * @access private
		 * @var String
		 */
		var descriptionFontDesktop = '14px Arial,sans-serif';

		/**
		 * Title font on mobile
		 * 
		 * @access private
		 * @var String
		 */
		var titleFontMobile = '16px Roboto';
		
		/**
		 * Description font on mobile
		 * 
		 * @access private
		 * @var String
		 */
		var descriptionFontMobile = '14px Roboto';
		
		/**
		 * Bind the media popovers to images for FB debugger
		 * 
		 * @access private
		 * @param String selector
		 * @return void 
		 */
		var bindMediaPopovers = function(selector) {
			const popoverTriggerList = document.querySelectorAll(selector)
			const popoverList = [...popoverTriggerList].map(function(popoverTriggerEl) {
				const bsPopover = new bootstrap.Popover(popoverTriggerEl, {
					placement: 'top',
					trigger: 'hover',
					html: true
				})
				
				bsPopover._config.content = COM_JMAP_OPEN_FB_DEBUGGER;
				bsPopover.setContent();
			});
		};
		
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		var showMessages = function(message, state) {
			var messageSnippet = '<div id="jmap_alert_message" class="alert alert-' + state + '">' +
						            '<span class="fas fa-info-circle" aria-hidden="true"></span><span> ' + message + '</span>' +
						          '</div>';		
			
			clearTimeout(msgTimeout);
			$('#jmap_alert_message').remove();
			
			$('#alert_append').append(messageSnippet);
			$('#jmap_alert_message').fadeIn('fast');
			
			var timerReady = $.Deferred();
			$.when(timerReady).done(function(response){
				$('#jmap_alert_message').fadeOut('fast', function(){
					$('#jmap_alert_message').remove();
				});
			});
			
			msgTimeout = setTimeout(function(){
				timerReady.resolve();
			}, 3000);
		};
		
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
			// Start the generation process, first operation is enter the progress modal mode
			$('a.jmap_metainfo').on('click.metainfo', function(jqEvent){
				// Prevent click link default
				jqEvent.preventDefault();
				
				// Show striped progress started generation
				showProgress(true, 50, 'striped', COM_JMAP_METAINFO_STARTED_SITEMAP_GENERATION);
				
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
			}).on('click.metainfo', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_METAINFO_ANALYZING_LINKS);
			});
			$('#limit').on('change.metainfo', function(jqEvent){
				showProgress(true, 100, 'striped', COM_JMAP_METAINFO_ANALYZING_LINKS);
			});
			$('#adminForm table.adminlist th a.hasTooltip').on('click.metainfo', function(jqEvent){
				// Show striped progress started generation
				showProgress(true, 100, 'striped', COM_JMAP_METAINFO_ANALYZING_LINKS);
			});
			
			// Register button task actions
			// Save metainfo
			$('#adminForm button[data-action=savemeta]').on('click.metainfo', function(jqEvent) {
				// Prevent button default
				jqEvent.preventDefault();
				
				// Retrive information to save
				var rowIdentifier = $(this).data('save');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var metaTitle = $('textarea[data-titleidentifier=' + rowIdentifier + ']').val();
				var metaDesc = $('textarea[data-descidentifier=' + rowIdentifier + ']').val();
				var metaImage = $('input[data-mediaidentifier=' + rowIdentifier + '], #jform_media_identifier_' + rowIdentifier).val();
				var robotsDirective = $('select[data-robotsidentifier=' + rowIdentifier + ']').val();
				var publishedStatus = $('input[name=published' + (rowIdentifier-1) + ']:checked').prop('value');
				var excludedStatus = $('input[name=excluded' + (rowIdentifier-1) + ']:checked').prop('value');

				// Perform validation, at least one of title/desc/robots
				// must be a valid value
				if (!metaTitle && !metaDesc && !robotsDirective && !metaImage) {
					showMessages(COM_JMAP_METAINFO_SET_ATLEAST_ONE, 'warning');
					return false;
				}

				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					meta_title : metaTitle,
					meta_desc : metaDesc,
					meta_image : metaImage,
					robots : robotsDirective,
					published : publishedStatus,
					excluded : excludedStatus
				};
				
				// Now save to server side
				saveDataStatus('saveMeta', dataObject, rowIdentifier);
				
				return false;
			});
			
			// Delete metainfo record
			$('#adminForm button[data-action=deletemeta]').on('click.metainfo', function(jqEvent) {
				// Prevent button default
				jqEvent.preventDefault();
				
				// Retrive information to save
				var rowIdentifier = $(this).data('delete');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier
				};
				
				// Now save to server side
				saveDataStatus('deleteMeta', dataObject);
				
				// Now reset row data
				var parentRow = $(this).parents('tr');
				$('select.robots_directive, input.mediaimagefield', parentRow).val('');
				$('textarea.metainfo', parentRow).text('').val('');
				$('div.field-media-preview', parentRow).empty().append('<span class="field-media-preview-icon fas fa-image"></span>');
				rowButtonStatus(parentRow);
				
				// Now reset characters counter
				$('span.labelmetainfo span.jmap_metainfo_counter', parentRow).text(0);
				$('span.labelmetainfo span.jmap_metainfo_counter_pixel', parentRow).text('0px');
				// Reset also a possible danger class
				$('span.labelmetainfo', parentRow).removeClass('bg-danger').addClass('bg-primary');
				
				// Finally reset the exclude state in the case there was one. Take care that the record is completely deleted
				$('fieldset[data-action=excludedmeta] div.controls label:last-child', parentRow).trigger('click', [true]);
				
				return false;
			});
			
			// Change metainfo record state
			$('#adminForm fieldset[data-action=statemeta] label').on('click.metainfo', function(jqEvent, noTrigger) {
				// Avoid triggering if not user click
				if(noTrigger || jqEvent.target.nodeName == 'INPUT' || $(jqEvent.target).attr('disabled')) {
					return true;
				}
				
				// Retrive information to save
				var rowIdentifier = $(this).parents('fieldset').data('state');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var publishedState = $(this).children('input').val();
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					field : 'published',
					fieldValue : parseInt(publishedState)
				};
				
				// Now save to server side
				saveDataStatus('stateMeta', dataObject);
				
				return true;
			});
			
			$('#adminForm fieldset[data-action=excludedmeta] label').on('click.metainfo', function(jqEvent, noTrigger) {
				// Avoid triggering if not user click
				if(noTrigger || jqEvent.target.nodeName == 'INPUT') {
					return true;
				}
				
				// Retrive information to save
				var parentRow = $(this).parents('tr');
				var rowIdentifier = $(this).parents('fieldset').data('state');
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var excludedState = $(this).children('input').val();
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					field : 'excluded',
					fieldValue : parseInt(excludedState) 
				};
				
				// Now save to server side
				saveDataStatus('stateMeta', dataObject);
				
				// Switch by default to unpublished if it's a new empty record store
				if($('fieldset[data-action=statemeta] div.controls label:last-child', parentRow).hasClass('jmap_inactive')) {
					$('fieldset[data-action=statemeta] div.controls label:last-child', parentRow).trigger('click', [true]);
				}
				
				return true;
			});
			
			// Generate metainfo based on the ChatGPT API button
			$(document).on('click.metainfo', '#adminForm button.jmap-chatgpt-metagenerator', function(jqEvent) {
				jqEvent.preventDefault();
				jqEvent.stopPropagation();
				
				// Retrive information to save
				var metadataType = '';
				var parentTD = $(this).parents('td');
				var textareaIdentifier = $('textarea', parentTD);
				
				if($(textareaIdentifier).attr('data-titleidentifier')) {
					var rowIdentifier = $(textareaIdentifier).data('titleidentifier');
					metadataType = 'title';
				} else if($(textareaIdentifier).attr('data-descidentifier')) {
					var rowIdentifier = $(textareaIdentifier).data('descidentifier');
					metadataType = 'description';
				}
				
				var linkIdentifier = $('a[data-linkidentifier=' + rowIdentifier + ']').attr('href');
				var sitemapLanguage = $('#adminForm input[name=sitemaplanguage]').val();
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier,
					type : metadataType,
					sitemaplang : sitemapLanguage
				};
				
				// Now save to server side
				generateChatGPTMeta(dataObject, textareaIdentifier);
				
				return false;
			});
			
			// Bind an event handler for textarea writing, used to count characters and lock/unlock disabled buttons
			$('table.adminlist tbody textarea, table.adminlist tbody input').on('keyup.metainfo', function(jqEvent) {
				var parentRow = $(this).parents('tr');
				// Update notify button status callback
				rowButtonStatus(parentRow);
			});
			
			// Observe media field image selection change
			$('div.field-media-preview').each(function(index, elem){
				// Create an observer instance for each element to observe
				var observer = new MutationObserver(function(mutations) {
					var image = $('img', elem);
					if(image.length) {
						var parentRow = $(elem).parents('tr');
						rowButtonStatus(parentRow);
					}
					bindMediaPopovers('div.field-media-preview img');
				});
				observer.observe(elem, { childList: true });
			});
			
			// Required for Joomla 3.5+
			$('table.adminlist tbody button.button-clear').on('click.metainfo', {context:this}, function(jqEvent) {
				var parentRow = $(this).parents('tr');
				var indentifier = $('td.link_loc a', parentRow).data('linkidentifier');
				// Update notify button status callback
				setTimeout(function(context){
					context.refreshRowStatus(parentRow, indentifier);
				}, 1, jqEvent.data.context)
			});
			
			// Required for Joomla 3.7+ in order to clear the modal-body and avoid multiple iframes append
			[].slice.call(document.querySelectorAll('*[id*=imageModal_jform_media_identifier]')).map(function (modalEl) {
				modalEl.addEventListener('hide.bs.modal', function(){
					$('div.modal-body', this).empty();
				});
			});
			
			$('table.adminlist tbody select.robots_directive').on('change.metainfo', function(jqEvent) {
				var parentRow = $(this).parents('tr');
				// Update notify button status callback
				rowButtonStatus(parentRow);
			});

			// Live event binding only once on initialize, avoid repeated
			// handlers and executed callbacks
			if (initialize) {
				// Live event binding for close button AKA stop process
				$(document).on('click.metainfo', 'label.closeprecaching', function(jqEvent) {
					let modalEl = document.querySelector('#metainfo_process');
					if(modalEl) {
						bootstrap.Modal.getInstance(modalEl).hide();
					}
				});
			}
			
			// Attach save all event listener
			$('joomla-toolbar-button[data-jmaptask*=metainfo\\.saveAll] button').on('click', function(jqEvent){
				jqEvent.preventDefault();
			
				// Start processing
				$('button[data-action=savemeta].jmap_modified:not([disabled])').trigger('click.metainfo');
				
				// When all promises are resolved start the async duplicated title/desc count
				$.when.apply($, savePromisesCollection).then(function() {
					showMessages(COM_JMAP_ALL_METAINFO_SAVED, 'success');
				});
				
				return false;
			});
			
			// Attach auto populate event listener
			$('joomla-toolbar-button[data-jmaptask*=metainfo\\.autoPopulate] button').on('click', function(jqEvent){
				jqEvent.preventDefault();
			
				// Start processing
				autoPopulateMetainfo();
				
				return false;
			});
			
			// Facebook debugger link open
			$(document).on('click', 'div.field-media-preview img', function(jqEvent){
				var rowLinkIdentifier = $('td.link_loc a[data-role=link]', $(this).parents('tr')).attr('href');
				window.open('https://developers.facebook.com/tools/debug/?q=' + encodeURIComponent(rowLinkIdentifier) , '_blank');
			});
			
			// Bind popovers to the media img field
			bindMediaPopovers('div.field-media-preview img');
		};
		
		/**
		 * Show progress dialog bar with informations about the ongoing started
		 * process
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
				var modalDialog =	'<div class="jmapmodal modal fade" id="metainfo_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
										'<div class="modal-dialog">' +
											'<div class="modal-content">' +
												'<div class="modal-header">' +
									        		'<h4 class="modal-title">' + COM_JMAP_METAINFO_TITLE + '</h4>' +
									        		'<label class="closeprecaching fas fa-times-circle"></label>' +
									        		'<p class="modal-subtitle">' + COM_JMAP_METAINFO_PROCESS_RUNNING + '</p>' +
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
				
				let modalEl = document.querySelector('#metainfo_process');
				let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
				modalInstance.show();
				
				// Async event progress showed and styling
				modalEl.addEventListener('shown.bs.modal', function(event) {
					$('#metainfo_process div.modal-body').css({'width':'90%', 'margin':'auto'});
					$('#progress_bar').css({'width':percentage + '%'});
					
					// Start AJAX GET request for sitemap generation in the cache folder
					startSitemapCaching(targetSitemapLink);
				});
				
				// Remove backdrop after removing DOM modal
				modalEl.addEventListener('hidden.bs.modal',function(jqEvent){
					$('.modal-backdrop').remove();
					$(this).remove();
					
					// Redirect to MVC core cpanel, discard metainfo process
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
						let modalEl = document.querySelector('#metainfo_process');
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
					data: {'metainfojsclient' : true}
				}).done(function(data, textStatus, jqXHR) {
					if(!data.result) {
						// Error found
						defer.reject(COM_JMAP_METAINFO_ERROR_STORING_FILE, textStatus);
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
				showProgress(false, 100, 'striped', COM_JMAP_METAINFO_GENERATION_COMPLETE);
				
				// Parse sitemap parameters
				var sitemapParams = parseURL(targetSitemapLink).params;
				var sitemapLang = sitemapParams.lang ? '&sitemaplang=' + sitemapParams.lang : '';
				var sitemapDataset = sitemapParams.dataset ? '&sitemapdataset=' + sitemapParams.dataset : '';
				var sitemapMenuID = sitemapParams.Itemid ? '&sitemapitemid=' + sitemapParams.Itemid : '';
				
				// Redirect to MVC core
				window.location.href = 'index.php?option=com_jmap&task=metainfo.display&metainfojsclient=1' + sitemapLang + sitemapDataset + sitemapMenuID;
			}, function(errorText, error) {
				// Do stuff and exit
				showProgress(false, 100, 'normal', errorText, 'bg-danger');
			});
		};
		
		/**
		 * Manage the data saving and the status change for each sitemap record
		 * in the model database table
		 * 
		 * @access private
		 * @param String action
		 * @param String dataObject
		 * @param Int rowIdentifier
		 * @return Void
		 */
		var saveDataStatus = function(action, dataObject, rowIdentifier) {
			// Object to send to server
			var ajaxparams = {
				idtask : action,
				param: dataObject
			};

			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			// Request JSON2JSON
			var metainfoPromise = $.Deferred(function(defer) {
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
						var userMessage = data.exception_message || COM_JMAP_METAINFO_SAVED;
						defer.resolve(userMessage, data);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			metainfoPromise.then(function(message, dataResponse) {
				// Update process status, we started
				if(action == 'stateMeta' && dataResponse.result && dataResponse.exception_message) { } else {
					showMessages(message, 'success');
				}
			}, function(errorText, error) {
				// Do stuff and exit
				showMessages(errorText, 'error');
			});

			// Assign the promise to the collection
			if(rowIdentifier) {
				savePromisesCollection[rowIdentifier] = metainfoPromise;
			}
		};
		
		/**
		 * Manage the data saving and the status change for each sitemap record
		 * in the model database table
		 * 
		 * @access private
		 * @param String dataObject
		 * @param HTMLElement targetTextArea
		 * @return Void
		 */
		var generateChatGPTMeta = function(dataObject, targetTextArea) {
			// Object to send to server
			var ajaxparams = {
				idtask : 'generateChatGPTMeta',
				param: dataObject,
				DIModels: [
					{
						'modelside' : 1,
						'modelname' : 'Aigenerator'
					},
					{
						'modelside' : 1,
						'modelname' : 'Metainfo'
					}
				]
			};

			// Store the row and column context
			var parentColumn = $(targetTextArea).parents('td');
			var parentRow = $(targetTextArea).parents('tr');
			
			// Set the working icon
			$('button.jmap-chatgpt-metagenerator img.jmap-chatgpt-icon', parentColumn).attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif');
			
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxparams);
			
			// Request JSON2JSON
			var metainfoPromise = $.Deferred(function(defer) {
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
						var resultMessage = data.exception_message || COM_JMAP_AIGENERATOR_METAINFO_GENERATED;
						defer.resolve(resultMessage, data);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					// Error found
					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
					defer.reject('-' + genericStatus + '- ' + errorThrown);
				});
			}).promise();

			metainfoPromise.then(function(message, dataResponse) {
				// Update process status, we started
				if(dataResponse.result && dataResponse.exception_message) { } else {
					showMessages(message, 'success');
					
					// Populate the just generated metadata in the matching field
					$(targetTextArea).text(dataResponse.generated).val(dataResponse.generated);
					
					// Update refresh status
					rowButtonStatus(parentRow);
					$(targetTextArea).trigger('keyup.metainfo');
				}
			}, function(errorText, error) {
				// Do stuff and exit
				showMessages(errorText, 'error');
			}).always(function(){
				$('button.jmap-chatgpt-metagenerator img.jmap-chatgpt-icon', parentColumn).attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/chatgpt-logo.svg');
			});
		};
		
		/**
		 * Evaluate and keep synced the enabled buttons status based on the current row values
		 * 
		 * @access private
		 * @param specificRow
		 * @return Void
		 */
		var rowButtonStatus = function(specificRow) {
			// Search for each row if at least one of the 3 values is specified and only in that case enable buttons
			var finalSelector = specificRow || 'table.adminlist tbody tr';
			
			$(finalSelector).each(function(index, tableRow){
				var titleValue = $('textarea.metatitle', tableRow).val();
				var descValue = $('textarea.metadesc', tableRow).val();
				var imageValue = $('input.mediaimagefield', tableRow).val();
				var robotsValue = $('select.robots_directive', tableRow).val();
				
				// If none of the value are available disable buttons
				if(!titleValue && !descValue && !imageValue && !robotsValue) {
					$('td > button, fieldset.radio[data-action=statemeta] label', tableRow).attr('disabled', true);
					$('fieldset.radio[data-action=statemeta] label', tableRow).addClass('jmap_inactive');
					if(specificRow) {
						$('button[data-action=savemeta]', tableRow).removeClass('jmap_modified');
					}
				} else {
					$('td > button, fieldset.radio[data-action=statemeta] label', tableRow).removeAttr('disabled');
					$('fieldset.radio[data-action=statemeta] label', tableRow).removeClass('jmap_inactive');
					if(specificRow) {
						$('button[data-action=savemeta]', tableRow).addClass('jmap_modified');
					}
				}
			});
		};
		
		/**
		 * Extend the jQuery prototype with a plugin to count and validate
		 * the length of the textarea characters
		 */
		var addTextareaLengthvalidation = function() {
			// jQuery prototype
			$.fn.textWidth = function(text, font) {
				if (!$.fn.textWidth.fakeEl) {
					$.fn.textWidth.fakeEl = $('<span class="jmapmetainfo_fake_element">').appendTo(document.body);
				}
				$.fn.textWidth.fakeEl.text(text).css('font', font);
				return parseInt($.fn.textWidth.fakeEl.width());
			};
			
			$.fn.jmapCharacterCount = function charCount(options) {
				var defaults = {
						limit : 100
				};
				var options = $.extend({}, defaults, options);
				if (this.length) {
					return $(this).each(function charCountEachElement() {
						var $this = $(this);
						var addElements = function() {
							$this.next('img.hasTooltip').remove();
							$this.container = $('<span/>');
							$this.counterCharacter = $('<span/>').attr('title', COM_JMAP_CHARACTERS).addClass('badge labelmetainfo').addClass('bg-primary').html( '<span class="jmapmetainfo_chars_count fas fa-align-left"></span><span class="jmap_metainfo_counter">' + 0 + '</span>'); 
							$this.counterPxDesktop = $('<span/>').attr('title', COM_JMAP_PIXEL_DESKTOP).addClass('badge labelmetainfo').addClass('bg-primary').html( '<span class="jmapmetainfo_pixel_count fas fa-desktop"></span><span class="jmap_metainfo_counter_pixel">' + 0 + 'px</span>');
							$this.counterPxMobile = $('<span/>').attr('title', COM_JMAP_PIXEL_MOBILE).addClass('badge labelmetainfo').addClass('bg-primary').html( '<span class="jmapmetainfo_pixel_count fas fa-mobile-alt"></span><span class="jmap_metainfo_counter_pixel">' + 0 + 'px</span>');
							
							$this.after($this.container);
							$this.container.append($this.counterCharacter);
							$this.container.append($this.counterPxDesktop);
							$this.container.append($this.counterPxMobile);
							
							// ChatGPT generation to enable based by settings
							if(jmap_chatgptGeneration) {
								if($this.hasClass('metatitle')) {
									var chatGPTTooltip = 'Click to generate the meta title with ChatGPT';
								} else {
									var chatGPTTooltip = 'Click to generate the meta description with ChatGPT';
								}
								$this.chatgptGenerationButton = $('<button class="jmap-chatgpt-metagenerator" title="' + chatGPTTooltip + '"><img class="jmap-chatgpt-icon" src="' + jmap_baseURI + 'administrator/components/com_jmap/images/chatgpt-logo.svg" alt="ChatGPT"></button>');
								
								$this.container.append($this.chatgptGenerationButton);
							}
						};
						$this.update = function() {
							var length = $this.val().length;
							if($this.hasClass('metatitle')) {
								var desktopLength = $.fn.textWidth($this.val(), titleFontDesktop);
								var mobileLength = $.fn.textWidth($this.val(), titleFontMobile);
							} else if($this.hasClass('metadesc')) {
								var desktopLength = $.fn.textWidth($this.val(), descriptionFontDesktop);
								var mobileLength = $.fn.textWidth($this.val(), descriptionFontMobile);
							}
							$this.counterCharacter.html('<span title="' + COM_JMAP_CHARACTERS + '" class="jmapmetainfo_chars_count fas fa-align-left"></span><span class="jmap_metainfo_counter">' + length + '</span>');
							$this.counterPxDesktop.html('<span title="' + COM_JMAP_PIXEL_DESKTOP + '" class="jmapmetainfo_pixel_count fas fa-desktop"></span><span class="jmap_metainfo_counter_pixel">' + desktopLength + 'px</span>');
							$this.counterPxMobile.html('<span title="' + COM_JMAP_PIXEL_MOBILE + '" class="jmapmetainfo_pixel_count fas fa-mobile-alt"></span><span class="jmap_metainfo_counter_pixel">' + mobileLength + 'px</span>');
							// Warning class for the characters count
							if (options.limit < length) {
								$this.counterCharacter.removeClass('bg-primary');
								$this.counterCharacter.addClass('bg-danger');
							} else {
								$this.counterCharacter.removeClass('bg-danger');
								$this.counterCharacter.addClass('bg-primary');
							}
							// Warning class for title pixel count
							if($this.hasClass('metatitle')) {
								if (maxLengthTitleDesktop < desktopLength) {
									$this.counterPxDesktop.removeClass('bg-primary');
									$this.counterPxDesktop.addClass('bg-danger');
								} else {
									$this.counterPxDesktop.removeClass('bg-danger');
									$this.counterPxDesktop.addClass('bg-primary');
								}
								if (maxLengthTitleMobile < mobileLength) {
									$this.counterPxMobile.removeClass('bg-primary');
									$this.counterPxMobile.addClass('bg-danger');
								} else {
									$this.counterPxMobile.removeClass('bg-danger');
									$this.counterPxMobile.addClass('bg-primary');
								}
							}
							// Warning class for description pixel count
							if($this.hasClass('metadesc')) {
								if (maxLengthDescriptionDesktop < desktopLength) {
									$this.counterPxDesktop.removeClass('bg-primary');
									$this.counterPxDesktop.addClass('bg-danger');
								} else {
									$this.counterPxDesktop.removeClass('bg-danger');
									$this.counterPxDesktop.addClass('bg-primary');
								}
								if (maxLengthDescriptionMobile < mobileLength) {
									$this.counterPxMobile.removeClass('bg-primary');
									$this.counterPxMobile.addClass('bg-danger');
								} else {
									$this.counterPxMobile.removeClass('bg-danger');
									$this.counterPxMobile.addClass('bg-primary');
								}
							}
						};
						$this.on('keyup.metainfo', function(event) {
							$this.update();
						});
						addElements();
						$this.update();
					});
				}
			};
		};
		
		/**
		 * Process the asyncronous analysis of links
		 * Auto populates the metainfo links records providing the auto click on the save button
		 *
		 * @access private
		 * @return Void
		 */
		var autoPopulateMetainfo = function() {
			// Retrieve all the links to analyze on page
			var linksToAnalyze = $('a[data-role=link]');
			
			// No ajax request if no links to analyze
			if(!linksToAnalyze.length) {
				return;
			}

			$.each(linksToAnalyze, function(index, link){
				var targetCrawledLink = $('a[data-role="link"]').get(index);
				var targetTitle = $('textarea[data-bind="{title}"]').get(index);
				var targetDesc = $('textarea[data-bind="{desc}"]').get(index);
				var targetRobots = $('select[data-bind="{robots}"]').get(index);
				var targetSocialImage = $('input.field-media-input.mediaimagefield').get(index);
				
				// Preappend a loading progress waiter to each processed link
				$(targetCrawledLink).after('<img class="metainfo_loader" width="16px" height="16px" src="' + jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif' + '"/>');
				
				promisesCollection[index] = $.Deferred(function(defer) {
					setTimeout(function(){
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
					// Always remove the waiter
					$(targetCrawledLink).next('img.metainfo_loader').remove();
					
					// STEP 1 - If the status validation is not 200 OK simply skip to next link promise
					if(status != 200) {
						return;
					}
					
					// Set init status for this row iteration
					var titlePopulated = false;
					var descPopulated = false;
					var robotsPopulated = false;
					var socialImagePopulated = false;
					
					// Set the parsed wrapped set
					var responseDataWrappedSet = $(responseData.trim());

					// STEP 2 - Title retrieval and reporting
					var title = responseDataWrappedSet.filter('title').text().trim();
					// Remove the site name suffix/prefix part in the title if any
					if(jmap_siteNamePageTitles > 0) {
						switch (jmap_siteNamePageTitles) {
							// Prepended before prefix
							case 1:
								title = title.replace(jmap_siteName + ' - ', '');
								break;
							
							// Appended after suffix
							case 2:
								title = title.replace(' - ' + jmap_siteName, '');
								break;
						}
					}
					
					// Manage title validity, preserve existing values
					var existingTargetTitle = $(targetTitle).text();
					if(title && !existingTargetTitle && jmap_metainfoAutoGenerateMetatitle != 2) {
						$(targetTitle).text( title ).val( title );
						titlePopulated = true;
					}
					
					// Evaluate the meta title excerpt auto-generation
					if(!titlePopulated && !existingTargetTitle && jmap_metainfoAutoGenerateMetatitle) {
						var autoGeneratedTitleText = '';
						var titleSource = $(jmap_metainfoAutoGenerateMetatitleCssSelector, $.parseHTML(responseData)).text().trim().replace(/\s\s+/g, ' ');
						
						var titleSourceLength = titleSource.length;
						if(titleSourceLength > 0) {
							if(titleSourceLength > jmap_metainfoAutoGenerateMetatitleMaxLength) {
								autoGeneratedTitleText = titleSource.substr(0, jmap_metainfoAutoGenerateMetatitleMaxLength) + '...';
							} else {
								autoGeneratedTitleText = titleSource;
							}
							
							$(targetTitle).text( autoGeneratedTitleText ).val( autoGeneratedTitleText );
							titlePopulated = true;
						}
					}
					
					// STEP 3 - Description retrieval and reporting
					var description = responseDataWrappedSet.filter('meta[name=description]').attr('content') || '';
					description = description.trim();
					// Manage description validity, preserve existing values
					var existingTargetDesc = $(targetDesc).text();
					if(description && !existingTargetDesc && jmap_metainfoAutoGenerateMetadescription != 2) {
						$(targetDesc).text( description ).val( description );
						descPopulated = true;
					}
					
					// Evaluate the meta description excerpt auto-generation
					if(!descPopulated && !existingTargetDesc && jmap_metainfoAutoGenerateMetadescription) {
						var autoGeneratedDescriptionText = '';
						var descriptionSource = $(jmap_metainfoAutoGenerateMetadescriptionCssSelector, $.parseHTML(responseData)).text().trim().replace(/\s\s+/g, ' ');
						
						var descriptionSourceLength = descriptionSource.length;
						if(descriptionSourceLength > 0) {
							if(descriptionSourceLength > jmap_metainfoAutoGenerateMetadescriptionMaxLength) {
								autoGeneratedDescriptionText = descriptionSource.substr(0, jmap_metainfoAutoGenerateMetadescriptionMaxLength) + '...';
							} else {
								autoGeneratedDescriptionText = descriptionSource;
							}
							
							$(targetDesc).text( autoGeneratedDescriptionText ).val( autoGeneratedDescriptionText );
							descPopulated = true;
						}
					}
					
					// STEP 4 - Robots directive retrieval and selecting
					var robots = responseDataWrappedSet.filter('meta[name=robots]').attr('content') || '';
					robots = robots.trim();
					// Manage description validity, preserve existing values
					if(robots && !$(targetRobots).val()) {
						$(targetRobots).val( robots );
						robotsPopulated = true;
					}
					
					// STEP 5 - Social image retrieval and selecting
					if(jmap_metainfoAutopopulateSocialimageSelector) {
						var socialImage = responseDataWrappedSet.find(jmap_metainfoAutopopulateSocialimageSelector).attr('src') || 
										  responseDataWrappedSet.find(jmap_metainfoAutopopulateSocialimageSelector).attr('data-src') || 
										  '';
						socialImage = socialImage.trim().replace(/^\//, "");
						// Manage description validity, preserve existing values
						if(socialImage && !$(targetSocialImage).val()) {
							// Check for sub folder overlap
							var baseChunksLastChunk = jmap_baseURI.replace(/\/$/, "").split('/').pop();
							var imageChunksFirstChunk = socialImage.split('/')[0];
							if(baseChunksLastChunk == imageChunksFirstChunk) {
								socialImage = socialImage.replace(baseChunksLastChunk + '/', '');
							}
							
							// Ensure that the image is not dirty by joomlaimage
							if(socialImage.indexOf('#') !== -1) {
								var cleanImageChunk = socialImage.split('#')[0];
								if(cleanImageChunk) {
									socialImage = cleanImageChunk;
								}
							}
							
							$(targetSocialImage).val( socialImage ).attr('value', socialImage);
							
							var containerPreview = $(targetSocialImage).parent().prev('div.field-media-preview');
							$('span', containerPreview).remove();
							
							// Regex pattern for checking absolute URL
				            var pattern = /^http(s)?:\/\//i; 
				            
				            // Check if pattern is there in the string or not with .test() method 
				            if (pattern.test(socialImage)) { 
				            	$(containerPreview).append('<img src="' + socialImage + '" />');
				            } 
				            else { 
				            	$(containerPreview).append('<img src="' + jmap_baseURI + socialImage + '" />');
				            } 
							
							socialImagePopulated = true;
						}
					}
					
					// Now trigger the change event for this row
					if(titlePopulated || descPopulated || robotsPopulated || socialImagePopulated) {
						$(targetTitle).trigger('keyup.metainfo');
						$(targetDesc).trigger('keyup.metainfo');
						// Finally trigger the save data button to persist
						var targetSaveBtn = $('button[data-action=savemeta]').get(index);
						$(targetSaveBtn).trigger('click.metainfo');
					}
				});
			});
		};
		
		/**
		 * Request a refresh of the row status
		 * 
		 * @access public
		 * @param Object parentRow
		 * @param Integer identifier
		 * @return Void
		 */
		this.refreshRowStatus = function(parentRow, identifier) {
			rowButtonStatus(parentRow);
			
			// Check if it's a deletion meta image after a button click in the media widget
			var titleValue = $('textarea.metatitle', parentRow).val();
			var descValue = $('textarea.metadesc', parentRow).val();
			var imageValue = $('input.mediaimagefield', parentRow).val();
			var robotsValue = $('select.robots_directive', parentRow).val();
			
			// If none of the values are available AKA empty record delete on server
			if(!titleValue && !descValue && !imageValue && !robotsValue) {
				// Retrive information to save
				var linkIdentifier = $('a[data-linkidentifier=' + identifier + ']').attr('href');
				
				// Now build the object to send to server endpoint
				var dataObject = {
					linkurl : linkIdentifier
				};
				
				// Now save to server side
				saveDataStatus('deleteMeta', dataObject);
			}
		};
		
		/**
		 * Request a refresh of the row status
		 * 
		 * @access public
		 * @return Void
		 */
		this.startMetaCount = function() {
			$('table.adminlist tbody textarea.metatitle').jmapCharacterCount({
	            limit: 70
	        });
			
			$('table.adminlist tbody textarea.metadesc').jmapCharacterCount({
	            limit: 160
	        });
			
			// Enable Bootstrap tooltips
			[].slice.call(document.querySelectorAll('span.labelmetainfo')).map(function (tooltipEl) {
				let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
					trigger:'hover', 
					placement:'top', 
					html: true
				});
				
				return tooltipInstance;
			});
			[].slice.call(document.querySelectorAll('button.jmap-chatgpt-metagenerator')).map(function (tooltipEl) {
				let tooltipInstance = new bootstrap.Tooltip(tooltipEl,{
					trigger:'hover', 
					placement:'top', 
					html: true
				});
				
				return tooltipInstance;
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
			// Search for each row if at least one of the 3 values is specified and only in that case enable buttons
			rowButtonStatus();
			
			// Extend jQuery with plugins
			addTextareaLengthvalidation();
			
			// Manage the async font loading for the update controls
			if(typeof(WebFont) !== 'undefined') {
				WebFont.load({
					google: {
						families: ['Roboto:400']
					}
				});
			}
			
			// Always enable the 'Delete all metainfo' button
			if(typeof(COM_JMAP_DELETE_ALL_META_DESC) !== 'undefined') {
				$('#toolbar-delete').attr('confirm-message', COM_JMAP_DELETE_ALL_META_DESC);
			}
			
			$('joomla-toolbar-button[task*=metainfo\\.saveAll]').removeAttr('task').attr('data-jmaptask', 'metainfo.saveAll');
			$('joomla-toolbar-button[task*=metainfo\\.autoPopulate]').removeAttr('task').attr('data-jmaptask', 'metainfo.autoPopulate');
			
			$('td.metaimage input.field-media-input.mediaimagefield').removeAttr('readonly');
			
			// Add UI events
			addListeners.call(this, true);
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapMetainfo = new Metainfo();
	});
})(jQuery);