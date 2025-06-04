/**
 * Import/export data sources file utility
 * 
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var FileSources = function() {
		/**
		 * Snippet to append for file uploader
		 * 
		 * @access private
		 * @var String
		 */
		var uploaderSnippet ='<div id="uploadrow" style="display: none;">' +
								'<span class="input-group">' +
									'<span class="input-group-text"><span class="fas fa-upload" aria-hidden="true"></span> ' + COM_JMAP_PICKFILE + '</span>' +
									'<input type="file" class="form-control" id="datasourceimport" name="datasourceimport" value="">' +
								'</span>' +
								'<button class="btn btn-primary btn-sm" id="startimport">' + COM_JMAP_STARTIMPORT + '</button> ' +
								'<button class="btn btn-primary btn-sm" id="cancelimport">' + COM_JMAP_CANCELIMPORT + '</button>' +
							'</div>';
		
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
			// Detect if there is a toolbar group
			var hasGroup = !!$('#toolbar-importexport-group').length;
			
			// Remove predefined Joomla behavior and attach custom feature
			if(!hasGroup) {
				$('#toolbar-upload').removeAttr('task');
				$('#toolbar-upload button').removeAttr('onclick');
				$('#toolbar-upload button').on('click', function(jqEvent){
					jqEvent.preventDefault();
					
					// Append uploader row
					if(!$(this).parent('#toolbar-upload').attr('disabled')) {
						$('#uploadrow').slideDown();
					}
					
					return false;
				});
			} else {
				$('joomla-toolbar-button[task="metainfo.importEntities"]').removeAttr('task').on('click', function(jqEvent){
					jqEvent.preventDefault();
				
					// Append uploader row
					if(!$(this).parent('#toolbar-refresh').attr('disabled')) {
						$('#uploadrow').slideDown();
						$('div.dropdown-menu').removeClass('show');
					}
					
					return false;
				});
			}
			
			// Append uploader row
			$('#uploadrow').remove();
			$('#adminForm table:first-child').before(uploaderSnippet)
			
			// Bind the uploader button
			$('#startimport').on('click', function(jqEvent){
				// Validate input
				var fileInput = $('#datasourceimport');
				if(!fileInput.val()) {
					fileInput.css('border', '1px solid #F00');
					$('#uploadrow span.validation.bg-danger').remove();
					$('#uploadrow').append('<span class="validation badge bg-danger">' + COM_JMAP_REQUIRED + '</span>');
					fileInput.on('click', function(jqEvent){
						$(this).css('border', '1px solid #ccc').next('span.validation').remove();
					});
					return false;
				}
				 
				// Change the task and submit miniform uploader
				var currentMvcCore = $('#adminForm input[name=task]').val().split('.');
				
				$('#adminForm').attr('enctype', 'multipart/form-data');
				$('#adminForm input[name=task]').val(currentMvcCore[0] + '.importEntities');
				$('#adminForm').trigger('submit');
			});
			
			// Cancel upload operation
			$('#cancelimport').on('click', function(jqEvent){
				jqEvent.preventDefault();
				$('#uploadrow').slideUp();
				
				return false;
			});
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapFileSources = new FileSources();
	});
})(jQuery);