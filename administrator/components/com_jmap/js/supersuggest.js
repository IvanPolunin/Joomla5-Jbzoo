/**
 * Google suggest keywords crawler
 * 
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage js
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
var JMapSupersuggest = (function($) {
	var gKeyword = "";
	var gLanguage = "en";
	var callbackNum = 0;
	var gResults = {};

	function setKeyword(keyword, language) {
		gKeyword = keyword;
		gLanguage = language;
		if(typeof(gResults[gKeyword]) !== 'undefined' && typeof(gResults[gKeyword][gLanguage]) !== 'undefined' && gResults[gKeyword][gLanguage]['length']) { } else {
			if(typeof(gResults[gKeyword]) !== 'object') {
				gResults[gKeyword] = {};
			}
			gResults[gKeyword][gLanguage] = new Array();
		}
	}

	function getKeyword() {
		return gKeyword;
	}

	function generateKeywordArr(skeyword) {
		// Add whitespace to keyword
		var kWordWithSpace = skeyword + '%20';
		// Define the output array.
		var skeywords = new Array(skeyword, kWordWithSpace);
		// Create an array with all the characters to append.
		var additionalChars = new Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

		// Create array of Keywords.
		for ( var i = 0; i < additionalChars.length; i++) {
			skeywords.push(kWordWithSpace + additionalChars[i]);
		}

		return skeywords;
	}

	function setCallbackCount(length) {
		callbackNum = length;
	}

	function changeCallbackCount(num) {
		callbackNum += num;
	}

	function getCallbackCount() {
		return callbackNum;
	}

	function getSuggestions(sKeywords) {
		// Check if already keywords present in the cache array
		if(typeof(gResults[gKeyword]) !== 'undefined' && typeof(gResults[gKeyword][gLanguage]) !== 'undefined' && gResults[gKeyword][gLanguage]['length'] > 0) {
			// Got a cache hit, retrieve here results and return
			showGResults();
			return;
		}
		
		// Loop through keywords. This will populate the gResults global
		// variable (array).
		for ( var i = 0; i < sKeywords.length; i++) {
			initializeSuggestCall(sKeywords[i]);
		}
	}

	function initializeSuggestCall(cKeyword) {
		// Create the script component that will get the JSONP response from Google's suggestor
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.id = 'script_' + cKeyword;
		script.src = 'https://suggestqueries.google.com/complete/search?client=youtube&jsonp=JMapSupersuggest.suggestCallback&q=' + cKeyword + '&hl=' + gLanguage;

		// Append the script to the header on the HTML
		document.getElementsByTagName('head')[0].appendChild(script);

		// Delete the added script to clean up the HTML (requires common.js or a version of that).
		script.remove();
	}

	function pushGResult(result) {
		gResults[gKeyword][gLanguage].push(result);
	}

	function getGResults() {
		return gResults[gKeyword][gLanguage];
	}

	function checkGStatus() {
		callbackNum -= 1
		if (callbackNum < 1) {
			// Show the results to the client.
			if(gResults[gKeyword][gLanguage]['length']) {
				showGResults();
			} else {
				showFallbackResults();
			}
		}

	}

	function privateCallback(dataWeGotViaJSONP) {
		// dataWeGotViaJSONP is an array (0 is name 1 is stuff we care about 2 is the search query object) Get the results from the JSONP response
		var rawResults = dataWeGotViaJSONP[1];
		// The results contain some info we don't care about (namely 0's). This filters them out.
		var resultsWeCareAbout = new Array();
		for ( var i = 0; i < rawResults.length; i++) {
			gResults[gKeyword][gLanguage].push(rawResults[i][0]);
		}
		checkGStatus();
	}

	function showGResults() {
		// Process results here, enable back the search keyword input field
		$('#search').prop('disabled', false);
		
		// Remove waiter
		$('img.waiterinfo').remove();

		// Show the results in the DOM (one per line).
		$('div.popover.keywords_suggestion div.popover-body').html('<ul class="keywords_results"><li>' + gResults[gKeyword][gLanguage].join("</li><li>") + '</li></ul>');
		
		// Reset counter
		callbackNum = 0;
	}
	
	function showFallbackResults() {
		// Fallback to an alternative system API
    	// Wordstream service
		$.get('http://kwrs.wordstream.com/keywords?pattern=' + gKeyword, function(response){
			if(typeof(response) === 'object' && response.code == 'OK') {
				gResults[gKeyword][gLanguage] = new Array();
				$.each(response.data.keywords, function(index, keywordObject){
					gResults[gKeyword][gLanguage].push(keywordObject.keyword);
				});
				showGResults();
			}
		}, "jsonp");
	}

	// / Public methods interface
	return {
		setKeywordVariable : function(keyword, language) {
			return setKeyword(keyword, language);
		},
		getKeywordVariable : function() {
			return getKeyword();
		},
		generateKeywords : function(seed) {
			return generateKeywordArr(seed);
		},
		setCallbackNumer : function(int) {
			return setCallbackCount(int);
		},
		editCallbackNumber : function(num) {
			return changeCallbackCount(num);
		},
		getCallbackNumer : function(int) {
			return getCallbackCount();
		},
		pushResults : function(result) {
			return pushGResult(result);
		},
		getResults : function() {
			return getGResults();
		},
		checkStatus : function() {
			return checkGStatus();
		},
		getSuggestionsFromG : function(sKeywords) {
			return getSuggestions(sKeywords);
		},
		suggestCallback : function(dataJSONP) {
			return privateCallback(dataJSONP);
		}
	};
})(jQuery);