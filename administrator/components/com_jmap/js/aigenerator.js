/**
 * AI Contents Generator
 * 
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap 
 * @subpackage js 
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
(function($) {
	var AIContentGenerator = function () {
		/**
		 * Timeout of the closer for popup
		 * 
		 * @access private
		 * @var String
		 */
		var timeoutCloser = null;
		
		/**
		 * Open AI Generator progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		var openAIGeneratorProgress = function() {
			// Show first progress
			var firstProgress = '<div class="progress">' +
									'<div id="progressbar_aigenerator" class="progress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
										'<label class="visually-hidden">' + COM_JMAP_PROGRESSAIGENERATORTITLE + '</label>' +
									'</div>' +
								'</div>';
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="aigenerator_process" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSAIGENERATORTITLE + '</h4>' +
								        		'<label class="closeaigenerator fas fa-times-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + firstProgress + '</p>' +
								        		'<p id="progressInfoLine"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			
			var modalOptions = {
					backdrop:'static'
				};
			
			let modalEl = document.querySelector('#aigenerator_process');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();

			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#aigenerator_process div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressbar_aigenerator').css({'width':'100%'});
				// Inform user process initializing
				$('#progressInfoLine').empty().append(COM_JMAP_PROGRESSAIGENERATORSUBTITLE);
			});
		};
		
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @param String ajaxLink
		 * @return void 
		 */
		var openCreateContentProgress = function(contentType, scope) {
			// Show first progress
			var titleOfContent = contentType == 'article' ? COM_JMAP_PROGRESSAIGENERATOR_ARTICLE_CREATION_TITLE : COM_JMAP_PROGRESSAIGENERATOR_MODULE_CREATION_TITLE;
			var firstProgress = '<div class="progress">' +
									'<div id="progressbar_aigenerator_createcontent" class="progress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
										'<label class="visually-hidden">' + titleOfContent + '</label>' +
									'</div>' +
								'</div>';
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="progressAiGeneratorCreateContentModal" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + titleOfContent + '</h4>' +
								        		'<label class="close-ai-content-creation fas fa-times-circle"></label>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + firstProgress + '</p>' +
								        		'<p id="progressInfoContentLine"></p>' +
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
			
			let modalEl = document.querySelector('#progressAiGeneratorCreateContentModal');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();
			
			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#progressAiGeneratorCreateContentModal div.modal-body').css({'width':'95%', 'margin':'auto'});
				$('#progressbar_aigenerator_createcontent').css({'width':'50%'});
				// Inform user process initializing
				var subtitleOfContent = contentType == 'article' ? COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_ARTICLE : COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_MODULE;
				$('#progressInfoContentLine').empty().append('<p class="aigenerator-process-message">' + subtitleOfContent + '</p>');
				
				setTimeout(function(){
					modelSaveEntity(contentType, scope).always(function(responseData){
						// Always remove the processing message
						$('p.aigenerator-process-message').remove();
						if(responseData.result) {
							// Set 100% for progress
							$('#progressbar_aigenerator_createcontent').css({'width':'100%'}).removeClass('progress-bar-striped progress-bar-animated').addClass('bg-success');
							// Append complete message
							if(contentType == 'article') {
								$('#progressInfoContentLine').append('<p>' + COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_ARTICLE_SUCCESS + '<a target="_blank" href="index.php?option=com_content&task=article.edit&id=' + responseData.content_id + '" class="badge bg-secondary">' + COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_EDIT_ARTICLE + '</a></p>');
							} else {
								$('#progressInfoContentLine').append('<p>' + COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_SUBTITLE_MODULE_SUCCESS + '<a target="_blank" href="index.php?option=com_modules&task=module.edit&id=' + responseData.content_id + '" class="badge bg-secondary">' + COM_JMAP_PROGRESSAIGENERATOR_CONTENT_CREATION_EDIT_MODULE + '</a></p>');
							}
						} else {
							// Set 100% for progress
							$('#progressbar_aigenerator_createcontent').css({'width':'100%'}).removeClass('progress-bar-striped progress-bar-animated').addClass('bg-danger');
							// Append exit message
							if(contentType == 'article') {
								$('#progressInfoContentLine').append('<p>' + COM_JMAP_PROGRESSAIGENERATOR_ARTICLE_CREATION_ERROR + '<span class="badge bg-danger">' + responseData.exception_message + '</span></p>');
							} else {
								$('#progressInfoContentLine').append('<p>' + COM_JMAP_PROGRESSAIGENERATOR_MODULE_CREATION_ERROR + '<span class="badge bg-danger">' + responseData.exception_message + '</span></p>');
							}
							timeoutCloser = setTimeout(function(){
								// Remove all
								let modalEl = document.querySelector('#progressAiGeneratorCreateContentModal');
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
				// Reset the timeout
				clearTimeout(timeoutCloser);
			});
		};
		
		/**
		 * Register user events for interface controls
		 * 
		 * @access private
		 * @param Boolean initialize
		 * @return Void
		 */
		var addListeners = function(initialize) {
			// Append a dialog for the audit tool
			$('#jform_api label.radio:not(.active),#jform_removeimgs label.radio:not(.active),#generatebutton').on('click.aigenerator', function(jqEvent){
				if($(jqEvent.target).is('input') || $(jqEvent.target).is('a')) {
					$('#toolbar-cogs button').trigger('click');
				}
			});
			
			// Support for copy Clipoard buttons, new API and legacy API
			if(navigator.clipboard) {
				$(document).on('click', '#accordion_aigenerator_contents_results div.card-header.hasPopover', function(jqEvent){
					var context = $(this);
					var snippetTitle = $('h4', context).text().trim();
					if (window.event.ctrlKey) {
						var snippetDescription = context.next('div.card-body').html().trim();
					} else {
						var snippetDescription = context.next('div.card-body').text().trim();
					}
					
					navigator.clipboard.writeText(snippetTitle + ' - ' + snippetDescription)
					.then(function() {
						let tooltipInstance = new bootstrap.Tooltip($('h4',context).get(0),{
							trigger : 'click', 
							placement : 'bottom',
							title : COM_JMAP_AIGENERATOR_COPIED_CONTENT,
							template: '<div class="tooltip jmap_copy_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
						});
						
						tooltipInstance.show();
						
						setTimeout(function(){
							tooltipInstance.dispose();
						}, 2000);
					})
					.catch(function(err) {
						var error = err;
					});
					return false;
				});
			}
			
			// Live event binding only once on initialize, avoid repeated handlers and executed callbacks
			if(initialize) {
				// Live event binding for close button AKA stop process
				$(document).on('click.aigenerator', 'label.closeaigenerator', function(jqEvent){
					let modalEl = document.querySelector('#aigenerator_process');
					if(modalEl) {
						bootstrap.Modal.getInstance(modalEl).hide();
					}
					window.stop();
				});
				
				// Live event binding for close button AKA stop process
				$(document).on('click.aigenerator', 'label.close-ai-content-creation', function(jqEvent){
					let modalEl = document.querySelector('#progressAiGeneratorCreateContentModal');
					if(modalEl) {
						bootstrap.Modal.getInstance(modalEl).hide();
					}
				});
				
				// Add content creation events
				$('button.create-article').on('click', function(){
					let scope = $(this).parents('div.card.text-black.bg-light');
					openCreateContentProgress('article', scope);
				});
				$('button.create-module').on('click', function(){
					let scope = $(this).parents('div.card.text-black.bg-light');
					openCreateContentProgress('module', scope);
				});
			}
		};
		
		/**
		 * Re-process images to ensure that at least a src is available
		 * 
		 * @access private
		 * @param Boolean initialize
		 * @return Void
		 */
		var reProcessImagesSrc = function(initialize) {
			var imgNodes = $('#contents_results div.card img');
			imgNodes.each(function(index, img){
				var imgNode = $(img);
				var hasASrc = img.hasAttribute('src') || img.hasAttribute('srcset');
				
				// The img is orphan, check if there is a data attribute to resume
				if(!hasASrc || imgNode.attr('src') == 'src') {
					var dataAttributes = ['data-src', 'data-original', 'data-lazyload', 'data-dt-lazy-src', 'data-lazy-src', 'data-noloadsrcset'];
					$.each(dataAttributes, function(index, dataAttribute){
						var dataValue = imgNode.attr(dataAttribute);
						if(dataValue) {
							if(dataAttribute != 'data-noloadsrcset') {
								imgNode.attr('src', dataValue);
							} else {
								imgNode.attr('srcset', dataValue);
							}
						}
					});
				}
			});
		};
		
		/**
		 * Switch ajax submit form to model business logic
		 * 
		 * @access private
		 * @param String ajaxLink
		 * @return Promise
		 */
		var modelSaveEntity = function(contentType, scope) {
			var contentTitle = $('div.card-header h4', scope).text().trim();
			var contentBody = $('div.card-body', scope).html().trim();
			var contentLanguage = $('#aigenerator_language option:selected').data('languagecode') || '*';
			
			// Consider the default language selection as the language 'ALL' case
			var contentLanguageIndex = $('#aigenerator_language option:selected').index();
			if(contentLanguageIndex == 0){
				contentLanguage = '*';
			}
			
			// Extra object to send to server
			var ajaxParams = { 
					idtask : 'aigeneratorContentCreate',
					template : 'json',
					param: {
						type: contentType,
						title: contentTitle,
						body: contentBody,
						language: contentLanguage
					}
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
	            },
				error: function(jqXHR, textStatus, error){
					// Append error details
					$('#progressInfoContentLine').append('<p>' + error + '</p>');
				}
			}); 
		};
		
		/**
		 * Public interface to the contents generator
		 * 
		 * @access public
		 * @return Void
		 */
		this.openProgressContentGeneration = function() {
			// Start first progress appending
			openAIGeneratorProgress();
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
			
			$('#api_google-lbl').addClass('btn-outline-success').removeClass('btn-outline-primary')
			
			// Show/hide params for the generator API
			var generatorAPISelected = $('input[name=api]:checked').val();
			if(generatorAPISelected != 'openai') {
				$('tr.openai-params').hide();
			} else {
				$('tr.openai-params').show();
			}
			
			// Re-process images to ensure that at least a src is available
			reProcessImagesSrc();
		}).call(this);
		
	};
	
	// On DOM Ready
	$(function() {
		window.JMapAIContentGenerator = new AIContentGenerator();
	});
})(jQuery);