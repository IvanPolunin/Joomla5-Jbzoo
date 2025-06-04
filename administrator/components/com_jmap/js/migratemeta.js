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
	var MigrateMetaDescription = function() {
		/**
		 * Snippet to append for file migration
		 * 
		 * @access private
		 * @var String
		 */
		var migrationSnippet ='<div id="migraterow" style="display: none;">' +
								'<span class="input-group">' +
									'<span class="input-group-text"><span class="fas fa-globe" aria-hidden="true"></span> ' + COM_JMAP_MIGRATE_META_PREVIOUS_DOMAIN + '</span>' +
									'<input type="text" class="form-control" id="migratemeta_currentdomain" name="migratemeta_currentdomain" value="">' +
								'</span>' +
								'<span class="input-group ms-1">' +
    								'<span class="input-group-text"><span class="fas fa-globe" aria-hidden="true"></span> ' + COM_JMAP_MIGRATE_META_NEW_DOMAIN + '</span>' +
    								'<input type="text" class="form-control" id="migratemeta_newdomain" name="migratemeta_newdomain" value="">' +
    							'</span>' +
								'<button class="btn btn-primary btn-sm ms-1" id="migrationconfirm">' + COM_JMAP_MIGRATE_META_CONFIRM + '</button> ' +
								'<button class="btn btn-primary btn-sm" id="migrationcancel">' + COM_JMAP_MIGRATE_META_CANCEL + '</button>' +
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
			// Append migration row
			$('#migraterow').remove();
			$('#adminForm table.full.headerlist').before(migrationSnippet)
			
			// Attach custom feature
			$('joomla-toolbar-button[task="metainfo.migrateEntities"]').removeAttr('task').on('click', function(jqEvent){
				jqEvent.preventDefault();
			
				// Append migration row
				if(!$(this).parent('#toolbar-refresh').attr('disabled')) {
					$('#migraterow').slideDown();
					$('div.dropdown-menu').removeClass('show');
				}
				
				return false;
			});
			
			// Bind the migration button
			$('#migrationconfirm').on('click', function(jqEvent){
				// Validate input
				var currentDomain = $('#migratemeta_currentdomain');
				var newDomain = $('#migratemeta_newdomain');
				var currentDomainValue = currentDomain.val() || null;
				var newDomainValue = newDomain.val() || null;
				
				if(!currentDomainValue) {
					currentDomain.css('border', '1px solid #F00');
					$('#migraterow span.validation.bg-danger').remove();
					$('#migraterow').append('<span class="validation badge bg-danger">' + COM_JMAP_REQUIRED + '</span>');
					currentDomain.on('click', function(jqEvent){
						$(this).css('border', '1px solid #ccc').next('span.validation').remove();
					});
					return false;
				}
				
				if(!newDomainValue) {
					newDomain.css('border', '1px solid #F00');
					$('#migraterow span.validation.bg-danger').remove();
					$('#migraterow').append('<span class="validation badge bg-danger">' + COM_JMAP_REQUIRED + '</span>');
					newDomain.on('click', function(jqEvent){
						$(this).css('border', '1px solid #ccc').next('span.validation').remove();
					});
					return false;
				}
				
				// Validator function for URL
				var fieldValidator = function(e, t) {
					var n = new RegExp(t, "");
					return n.test(e)
				}
				if(!fieldValidator(currentDomainValue, "^https?://(.+.)+.{2,4}(/.*)?$")) {
					currentDomain.css('border', '1px solid #F00');
					$('#migraterow span.validation.bg-danger').remove();
					$('#migraterow').append('<span class="validation badge bg-danger">' + COM_JMAP_INVALID_URL + '</span>');
					currentDomain.on('click', function(jqEvent){
						$(this).css('border', '1px solid #ccc').next('span.validation').remove();
					});
					return false;
				}
				if(!fieldValidator(newDomainValue, "^https?://(.+.)+.{2,4}(/.*)?$")) {
					newDomain.css('border', '1px solid #F00');
					$('#migraterow span.validation.bg-danger').remove();
					$('#migraterow').append('<span class="validation badge bg-danger">' + COM_JMAP_INVALID_URL + '</span>');
					newDomain.on('click', function(jqEvent){
						$(this).css('border', '1px solid #ccc').next('span.validation').remove();
					});
					return false;
				}
				 
				// Change the task and submit miniform migration
				var currentMvcCore = $('#adminForm input[name=task]').val().split('.');
				
				$('#adminForm').attr('enctype', 'multipart/form-data');
				$('#adminForm input[name=task]').val(currentMvcCore[0] + '.migrateEntities');
				$('#adminForm').trigger('submit');
			});
			
			// Cancel migration operation
			$('#migrationcancel').on('click', function(jqEvent){
				jqEvent.preventDefault();
				$('#migraterow').slideUp();
				
				return false;
			});
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapMigrateMetaDescription = new MigrateMetaDescription();
	});
})(jQuery);