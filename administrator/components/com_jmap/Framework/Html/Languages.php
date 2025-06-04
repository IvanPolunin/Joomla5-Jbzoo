<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Html;
/**  
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Languages available
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class Languages {
	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @param boolean $allLanguages
	 * @param boolean $customSelect
	 * @return array
	 */
	public static function getAvailableLanguageOptions($allLanguages = false, $customSelect = false) {
		$knownLangs = LanguageHelper::getLanguages();
		$defaultLanguageSef = null;
		 
		// Get default site language
		$langParams = ComponentHelper::getParams('com_languages');
		// Setup predefined site language
		$defaultLanguageCode = $langParams->get('site');
		
		foreach ($knownLangs as $knownLang) {
			if($knownLang->lang_code == $defaultLanguageCode) {
				$defaultLanguageSef = $knownLang->sef;
				break;
			}
		}
		
		if($allLanguages) {
			$langs[] = HTMLHelper::_('select.option',  '*', Text::_('COM_JMAP_DATASOURCE_LANGUAGES_ALL' ) );
		} else {
			if($customSelect) {
				$langs[] = HTMLHelper::_('select.option',  $defaultLanguageSef, '- ' . Text::_('COM_JMAP_DEFAULT_SITE_LANG_LANGUAGES_ALL' ) . ' -' );
			} else {
				$langs[] = HTMLHelper::_('select.option',  $defaultLanguageSef, '- ' . Text::_('COM_JMAP_DEFAULT_SITE_LANG' ) . ' -' );
			}
		}
		
		if($customSelect) {
			$langs[0]->languagecode = $knownLang->lang_code;
			// Create found languages options
			foreach ($knownLangs as $langIndex=>$langObject) {
				// Extract tag lang
				$langs[$langIndex + 1] = HTMLHelper::_('select.option',  $langObject->sef, $langObject->title );
				$langs[$langIndex + 1]->languagecode = $langObject->lang_code;
			}
		} else {
			// Create found languages options
			foreach ($knownLangs as $langObject) {
				// Extract tag lang
				$langs[] = HTMLHelper::_('select.option',  $langObject->sef, $langObject->title );
			}
		}

		return $langs;
	}
}