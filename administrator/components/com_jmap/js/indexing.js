/**
 * Indexing tester utilities
 * 
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var Indexing = function() {
		/**
		 * The first operation is get informations about published data sources
		 * and start cycle over all the records using promises and recursion
		 * 
		 * @access private
		 * @param String keyword
		 * @param String language
		 * @return Void
		 */
		var getKeywordsSuggestion = function(keyword, language) {
		    //Set the global keyword variable
		    JMapSupersuggest.setKeywordVariable(keyword, language);

		    //Generate array of new Keywords (ABC method).
		    var sKeywords = JMapSupersuggest.generateKeywords(keyword);
		    //Create a variable that keeps track of how many callbacks we have left before we are done.
		    JMapSupersuggest.setCallbackNumer(sKeywords.length);

		    //This actually starts the process of getting the suggestions
		    JMapSupersuggest.getSuggestionsFromG(sKeywords, language);
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
			// Bind the event listener for the user interaction
			var containerWidth = $('div.popover').width(); 
			
			if(!$('#search').val()) {
				$('label[data-role=keywords_suggestion]').css('opacity', 0);
			}
			
			// Enables bootstrap popover
			let popoverEl = document.querySelector('label.hasClickPopover[data-role=keywords_suggestion]');
			new bootstrap.Popover(popoverEl, {
				template : '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
				trigger: 'click', 
				placement: 'right', 
				html: true,
				sanitize: false,
				container: 'body',
				content: function() {
					return $('<img/>')
						.attr({
							'src': jmap_baseURI + 'administrator/components/com_jmap/images/loading.gif',
							'class': 'waiterinfo'})
						.css({
				            'position': 'relative',
				            'width': '32px',
				            'height': '32px',
				            'max-width': 'inherit'});
					}
			});
			popoverEl.addEventListener('shown.bs.popover', function(){
				// Add a unique class namespace to the popover
				$('body div.popover:last-child').addClass('keywords_suggestion');
				
			    // Get input from user
			    var keyword = $('#search').val();
			    var langIso = $('#acceptlanguage').val() || 'en-GB';
			    var language = langIso.split('-').shift();

			    // Return if no user input.
			    if (!keyword) {
			      return false;
			    }

			    // Disable button to keep user from submitting twice.
			    $('#search').prop('disabled', true);
			    
				// Call here the async data fetch
				getKeywordsSuggestion(keyword, language);
			});
			
			// Ensure closing it when click on other DOM elements
			$(document).on('click', 'body', function(jqEvent){
				if( !$(jqEvent.target).hasClass('hasClickPopover') && 
					!$(jqEvent.target).hasClass('popover-body') && 
					!$(jqEvent.target).parents('div.popover-body').length) {
					[].slice.call(document.querySelectorAll('label.hasClickPopover')).map(function (popoverEl) {
						if(popoverEl) {
							let popoverInstance = bootstrap.Popover.getInstance(popoverEl);
							if(popoverInstance) {
								popoverInstance.hide();
							}
						}
					});
				}
			});
			
			// Bind live event handler to show the keyword suggestor button
			$('#search').on('keyup', function(jqEvent){
				if($(this).val()) {
					$('label[data-role=keywords_suggestion]').css('opacity', 1);
				} else {
					$('label[data-role=keywords_suggestion]').css('opacity', 0);
				}
			});
		}).call(this);
	}

	// On DOM Ready
	$(function() {
		window.JMapIndexing = new Indexing();
	});
})(jQuery);