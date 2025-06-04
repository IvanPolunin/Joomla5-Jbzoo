/**
 * Pingomatic form links JS client with asyncronous features form submit
 * 
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap 
 * @subpackage js 
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
*/
(function($) {
	var PingomaticClient = function (formElem, configOptions) {
		/**
		 * Target Form HTMLFormElement
		 * 
		 * @access private
		 * @var Object
		 */
		var targetForm = formElem;
		
		/**
		 * Default plugin options
		 * 
		 * @access private
		 * @var Object
		 */
		var defaultOptions = {};

		/**
		 * Plugin options
		 * 
		 * @access private
		 * @var Object
		 */
		var pluginOptions;
	 
		/**
		 * Open first operation progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		function openPingomaticProgress() {
			// Show first progress
			var firstProgress = '<div class="progress">' +
									'<div id="progressBar1" class="progress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
										'<label class="visually-hidden">' + COM_JMAP_PROGRESSPINGOMATICTITLE + '</label>' +
									'</div>' +
								'</div>';
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="progressModal1" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSPINGOMATICTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + firstProgress + '</p>' +
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
					backdrop:'static'
				};
			
			let modalEl = document.querySelector('#progressModal1');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();

			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#progressModal1 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar1').css({'width':'50%'});
				// Inform user process initializing
				$('#progressInfo1').empty().append(COM_JMAP_PROGRESSPINGOMATICSUBTITLE);
				
				// Start iframe load post form
				pingomaticAjaxSubmit();
			});
			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$('.modal-backdrop').remove();
				$(this).remove();
			});
		};
		
		/**
		 * Open second operation progress bar
		 * 
		 * @access private
		 * @return void 
		 */
		function openModelProgress() {
			// Show second progress
			var secondProgress = '<div class="progress">' +
									'<div id="progressBar2" class="progress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100">' +
										'<label class="visually-hidden">' + COM_JMAP_PROGRESSMODELTITLE + '</label>' +
									'</div>' +
								'</div>';
			
			// Build modal dialog
			var modalDialog =	'<div class="jmapmodal modal fade" id="progressModal2" tabindex="-1" role="dialog" aria-labelledby="progressModal" aria-hidden="true">' +
									'<div class="modal-dialog">' +
										'<div class="modal-content">' +
											'<div class="modal-header">' +
								        		'<h4 class="modal-title">' + COM_JMAP_PROGRESSMODELTITLE + '</h4>' +
							        		'</div>' +
							        		'<div class="modal-body">' +
								        		'<p>' + secondProgress + '</p>' +
								        		'<p id="progressInfo2"></p>' +
							        		'</div>' +
							        		'<div class="modal-footer">' +
								        	'</div>' +
							        	'</div><!-- /.modal-content -->' +
						        	'</div><!-- /.modal-dialog -->' +
						        '</div>';
			// Inject elements into content body
			$('body').append(modalDialog);
			
			var modalOptions = {
					backdrop:false
				};
			
			let modalEl = document.querySelector('#progressModal2');
			let modalInstance = new bootstrap.Modal(modalEl, modalOptions);
			modalInstance.show();
			
			// Set 33% for progress
			modalEl.addEventListener('shown.bs.modal', function(event) {
				$('#progressModal2 div.modal-body').css({'width':'90%', 'margin':'auto'});
				$('#progressBar2').css({'width':'33%'});
				// Inform user process initializing
				$('#progressInfo2').append(COM_JMAP_PROGRESSMODELSUBTITLE);
				setTimeout(function(){
					var modelAjaxSubmitCallback = function(operationResult){
						if(operationResult) {
							// Set 100% for progress
							$('#progressBar2').css({'width':'100%'});
							$('#progressBar2').addClass('bg-success').removeClass('progress-bar-striped progress-bar-animated');
							// Append exit message
							$('#progressInfo2').append(COM_JMAP_PROGRESSMODELSUBTITLE2SUCCESS);
							setTimeout(function(){
								// Remove all
								let modalEl1 = document.querySelector('#progressModal1');
								if(modalEl1) {
									bootstrap.Modal.getInstance(modalEl1).hide();
								}
								let modalEl2 = document.querySelector('#progressModal2');
								if(modalEl2) {
									bootstrap.Modal.getInstance(modalEl2).hide();
								}
							}, 2000);
						} else {
							// Set 100% for progress
							$('#progressBar2').css({'width':'100%'}).addClass('bg-danger');
							// Append exit message
							$('#progressInfo2').append('<p>' + COM_JMAP_PROGRESSMODELSUBTITLE2ERROR + '</p>');
							setTimeout(function(){
								// Remove all
								$('#progressBar2').removeClass('bg-danger');
								let modalEl1 = document.querySelector('#progressModal1');
								if(modalEl1) {
									bootstrap.Modal.getInstance(modalEl1).hide();
								}
								let modalEl2 = document.querySelector('#progressModal2');
								if(modalEl2) {
									bootstrap.Modal.getInstance(modalEl2).hide();
								}
							}, 3000);
						}
					};
					modelAjaxSubmit(modelAjaxSubmitCallback);
				}, 500);
			});

			
			// Remove backdrop after removing DOM modal
			modalEl.addEventListener('hidden.bs.modal',function(){
				$(this).remove();
			});
		};
		
		/**
		 * Make simulated ajax submit form to Pingomatic with hidden iframe
		 * 
		 * @access private
		 * @return Boolean
		 */
		function pingomaticAjaxSubmit() {
			// Set dinamically task target in form to ajaxserver.display
			$(targetForm).attr('target', 'pingomatic_iframe');
			$(targetForm).attr('action', jmap_urischeme + '://pingomatic.com/ping/');
			
			// Submit to Pingomatics services
			$('#pingomatic_ajaxloader').empty();
			var pingUrl = encodeURIComponent($('#linkurl').val().split(/[?#]/)[0]);
			var pingTitle = encodeURIComponent($('#title').val());
			var enabledCommonServices = $('#common div.service_control input[type=radio][value=1]:checked').each(function(index, service){
				var serviceHost = $(service).data('host');
				const regex = /(indexnow|webcrawler)/g;
				const matches = serviceHost.match(regex);
				
				if(!matches) {
					// Extra object to send to server
					var ajaxParams = {
						idtask : 'pingomaticServicesProxy',
						template : 'json',
						param: {
							serviceurl: serviceHost + pingUrl + '+' + pingTitle,
						}
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
				        success: function(data, textStatus, jqXHR){},
						error: function(jqXHR, textStatus, error){}
					}); 
				} else {
					$.ajax({
						url: serviceHost + pingUrl + '+' + pingTitle,
						dataType: "jsonp",
						success: function(data, textStatus, jqXHR){},
						error: function(jqXHR, textStatus, error){}
					});
				}
			});
			
			// No exception handling, so always return true
			return formSubmitWithCallback($(targetForm), $('#pingomatic_iframe'), function() {
				// Reset resources data on form
				$(targetForm).removeAttr('target');
				$(targetForm).attr('action', 'index.php');
				// Set 100% for progress
				$('#progressBar1').css({'width':'100%'}).addClass('bg-success').removeClass('progress-bar-striped progress-bar-animated');
				$('#progressInfo1').append(COM_JMAP_PROGRESSPINGOMATICSUBTITLE2SUCCESS);
				
				// Wait for 100% progress
				setTimeout(function(){
					// Rethrow to create progress
					openModelProgress();
				}, 500);
			});
		};

		/**
		 * Submit a form to an iframe and execute callback on load complete
		 * 
		 * @access private
		 * @return Boolean
		 */
		function formSubmitWithCallback(form, frame, successFunction) {
			// Set callback
			var callback = function () {
				if(successFunction) {
					successFunction();
				}
				frame.off('load', callback);
		   };
		   
		   // Bind callback to iframe load event 
		   frame.on('load', callback);
		   
		   // Trigger form submit to iframe if at least one service is selected, go on with the callback otherwise
		   if($('input[name^=chk_][value=1]:checked', targetForm).length) {
			   form.trigger('submit');
		   } else {
			   callback();
		   }
		   
		   // No exception handling, so always return true
		   return true;
		}
		
		/**
		 * Switch ajax submit form to model business logic
		 * 
		 * @access private
		 * @param Object callback
		 * @return Void
		 */
		function modelAjaxSubmit(callback) {
			// Increment pre HTTP request
			$('#progressBar2').css({'width':'66%'});
			// Final status for model operation
			var success = false;
			// Reset resources data on form for security safe async not avoidable
			$(targetForm).removeAttr('target');
			$(targetForm).attr('action', 'index.php');
			
			// Prepare form to submit via ajax
			$(targetForm).ajaxForm();
			
			// Extra object to send to server
			var ajaxParams = { 
					idtask : 'storeUpdatePingomatic',
					template : 'json',
					param: $('input[name=id]', targetForm).val()
			     };
			// Unique param 'data'
			var uniqueParam = JSON.stringify(ajaxParams); 

			// Setup initial options object for real ajaxSubmit JSON2JSON
			var ajaxSubmitOptions = {
					type: 'POST',
					dataType: 'json',
					async: true,
					data: {data: uniqueParam},
					beforeSerialize: function(formData, formObject, options){
						// Set dinamically task target in form to ajaxserver.display
						$('input[name=task]', targetForm).val('ajaxserver.display');
						
						//Add dinamically document format to form submitted
						$(targetForm).append('<input type="hidden" name="format" value="json"/>');
					},
					success: function(data, textStatus, jqXHR){
						var data;
						// Set result value
						success = data.result;
						
						// If errors found inside model working
						if(!success && data.errorMsg) {
							$('#progressInfo2').append( data.errorMsg + ' - ');
						}
						// Reset dinamically task target in form to null empty value
						jqXHR.always(function(){
							$('input[name=task]', targetForm).val('');
							$('input[name=format][value=json]', targetForm).remove();
							// Append last ping time from server table
							$('#lastping', targetForm).html(data.lastping);
							$('input[type=hidden][name=lastping]', targetForm).val(data.lastpingMySql);
							// Check if new pinged stored records and update hidden field to avoid duplicates
							if(!parseInt($('input[name=id]').val())) {
								$('input[name=id]').val(data.id);
							}
						});

						callback(success);
					},
					error: function(jqXHR, textStatus, error){
						// Append error details
						$('#progressInfo2').append(error.message + ' - ');
						// Reset dinamically task target in form to null empty value
						jqXHR.always(function(){
							$('input[name=task]', targetForm).val('');
						});
						
						callback(success);
					}
			};
			// Prepare form to submit via ajax
			$(targetForm).ajaxSubmit(ajaxSubmitOptions);
		};
		
		/**
		 * Public interface to submit ajax form to Pingomatic web service
		 * 
		 * @access public
		 * @return Void
		 */
		this.submitFormPingomatic = function() {
			// Start first progress appending
			openPingomaticProgress();
		};
		
		/**
		 * Init function dummy constructor
		 * @access private
		 * @method IIFE
		 * @return Void
		 */
		(function init() {
			// Init options
			pluginOptions = $.extend({}, defaultOptions, configOptions );
		}).call(this);
	};
	
	/**
	 * jQuery Pingomatic plugin
	 */
	$.fn.Pingomatic = function(options) {
		// Cycle on pre initialized wrapped set
		this.each(function(index, formElem){
			// Do form element validation
			if(!$(formElem).validate()) {
				return false;
			}	
			
			// Mutex radio button, at least 1 enabled
			var enabledServices = $('input[name^=chk_][value=1], input[name^=ajs_][value=1]', formElem);
			var countEnabledServices = 0;
			$.each(enabledServices, function(index, elem){
				if($(elem).prop('checked')) {
					countEnabledServices++;
				}
			});
			if(!countEnabledServices) {
				var requiredHtmlChunk = '<label class="validation services badge bg-danger">' + COM_JMAP_SELECTONESERVICE + '</label>';
				$('div.card:last-child').after(requiredHtmlChunk);
				
				// Register to disable error label
				$('label.radio[for^=chk_],label.radio[for^=ajs_]').on('click', function(){
					$('label.validation.services').remove();
				});
				return false;
			}
			
			// Instance of Pingomatic manager
			var client = new PingomaticClient(formElem, options);
			
			// Call submit sending ajax form to Pingomatic
			client.submitFormPingomatic();
		});
	};
})(jQuery);

// Popover configuration
jQuery(function($){
	var iFrameContext = 'div.popover-body';
	/**
	 * Enables bootstrap popover
	 */
	
	[].slice.call(document.querySelectorAll('label.hasClickPopover')).map(function (popoverEl) {
		let popoverInstance = new bootstrap.Popover(popoverEl,{
			template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			trigger:'click', 
			html:true,
			sanitize: false,
			placement: 'bottom'
		});
		
		popoverEl.addEventListener('shown.bs.popover', function(){
			$('div.popover-body').empty();
			$('div.popover-body').css({'padding': '5px', 'overflow': 'hidden'});
			$('<iframe id="sitemap_iframe" src="' + jmap_baseURI + 'index.php?option=com_jmap&view=sitemap&tmpl=component&pingiframe=1' + '"></iframe>').appendTo(iFrameContext);
			$('iframe', iFrameContext).css({'border':'none', 'height':'300px'});
			// Get div popover container width to center waiter
			var containerWidth = $('div.popover-body').width() / 2;
			$('div.popover-body').prepend('<div/>').children('div').text(COM_JMAP_LOADING).css({
	            'position': 'absolute',
	            'font-size': '12px',
	            'margin': '75px ' + parseInt(containerWidth - 50) + 'px'
	        });
			$('div.popover-body').prepend('<img/>').children('img').attr('src', jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif').css({
	            'position': 'absolute',
	            'margin': '50px ' + parseInt(containerWidth - 12) + 'px',
	            'width': '24px'
	        });
			
			$('#sitemap_iframe').on('load', function(){
				var doc = this.contentDocument ? this.contentDocument : (this.contentWindow.document || this.document);
				var script = doc.createElement("script");
				script.type = "text/javascript";
				script.src = jmap_baseURI + 'administrator/components/com_jmap/js/urlpicker.js';
				doc.body.appendChild(script);
				$('div.popover-body img, div.popover-body div').remove();
			});
		})
		
		return popoverInstance;
	});
	
	$(document).on('click', 'h3.popover-header', function(){
		[].slice.call(document.querySelectorAll('label.hasClickPopover')).map(function (popoverEl) {
			if(popoverEl) {
				let popoverInstance = bootstrap.Popover.getInstance(popoverEl);
				if(popoverInstance) {
					popoverInstance.hide();
				}
			}
		});
	});
	
	// Add class to broadcast button
	$('.icon-broadcast').parent().addClass('btn-primary');
});