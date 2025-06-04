<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Language;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage language
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Multilanguage fallback utility class
 * 
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage language
 * @since 2.0
 */
class Multilang extends Language {
	/**
	 * Method to determine if the language filter plugin is enabled.
	 * This works for both site and administrator.
	 *
	 * @return  boolean  True if site is supporting multiple languages; false otherwise.
	 *
	 * @since   2.5.4
	 */
	public static function isEnabled() {
		// Flag to avoid doing multiple database queries.
		static $tested = false;

		// Status of language filter plugin.
		static $enabled = false;

		// Get application object.
		$app = Factory::getApplication();

		// If being called from the front-end, we can avoid the database query.
		if ($app->isClient('site')) {
			$enabled = $app->getLanguageFilter();
			return $enabled;
		}

		// If already tested, don't test again.
		if (!$tested) {
			// Determine status of language filter plug-in.
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );

			$query->select('enabled');
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
			$query->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
			$db->setQuery($query);

			$enabled = $db->loadResult();
			$tested = true;
		}

		return $enabled;
	}
	
	/**
	 * Get the sef string for the current language
	 *
	 * @access public
	 * @return string
	 */
	public static function getCurrentSefLanguage() {
		static $defaultLanguageSef;
		if($defaultLanguageSef) {
			return $defaultLanguageSef;
		}
		
		$knownLangs = LanguageHelper::getLanguages();
			
		// Setup predefined site language
		$defaultLanguageCode = Factory::getApplication()->getLanguage()->getTag();
	
		foreach ($knownLangs as $knownLang) {
			if($knownLang->lang_code == $defaultLanguageCode) {
				$defaultLanguageSef = $knownLang->sef;
				break;
			}
		}
	
		return $defaultLanguageSef;
	}
	
	/** 
	 * Load language ID
	 * 
	 * @access public
	 * @param string $languagTag
	 * @return int
	 * 
	 */
	public static function loadLanguageID($languageTag) {
		// Determine status of language filter plug-in.
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
		
		$query->select('lang_id');
		$query->from($db->quoteName('#__languages'));
		$query->where($db->quoteName('lang_code') . ' = ' . $db->quote($languageTag));
		$db->setQuery($query);
		
		$langID = $db->loadResult();
		return $langID;
	}
	
	/**
	 * Load language sef
	 *
	 * @access public
	 * @param string $languagTag
	 * @return int
	 *
	 */
	public static function loadLanguageSEF($languageTag) {
		// Determine status of language filter plug-in.
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
		
		$query->select('sef');
		$query->from($db->quoteName('#__languages'));
		$query->where($db->quoteName('lang_code') . ' = ' . $db->quote($languageTag));
		$db->setQuery($query);
		
		$langID = $db->loadResult();
		return $langID;
	}
	
	/**
	 * Load language ID
	 *
	 * @access public
	 * @param string $languagTag
	 * @return int
	 *
	 */
	public static function loadLanguageTitle($languageSef) {
		// Determine status of language filter plug-in.
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
		
		$query->select('title');
		$query->from($db->quoteName('#__languages'));
		$query->where($db->quoteName('sef') . ' = ' . $db->quote($languageSef));
		$db->setQuery($query);
		
		$langTitle = $db->loadResult();
		return $langTitle;
	}
	
	/**
	 * Retrieves the default site language, also evaluating the Remove URL Language Code
	 *
	 * @access public
	 * @return string
	 *
	 */
	public static function getDefaultSiteLanguage() {
		$languageComponentParams = ComponentHelper::getParams('com_languages');
		$defaultSiteLanguage = $languageComponentParams->get('site');
		
		$languageFilterPlugin = PluginHelper::getPlugin('system', 'languagefilter');
		if(isset($languageFilterPlugin->params)) {
			$pluginParams = json_decode($languageFilterPlugin->params);
			if($pluginParams->remove_default_prefix) {
				$defaultSiteLanguageSef = self::loadLanguageSEF($defaultSiteLanguage);
				return $defaultSiteLanguageSef;
			} else {
				return '';
			}
		} else {
			return '';
		}
	}
	
	/**
	 * Override Language instantiator
	 *
	 * @access	public
	 */
	public static function getInstance($lang = null, $debug = false) {
		$conf = Factory::getApplication()->getConfig();
	
		if(is_null($lang)) {
			$locale = $conf->get('language');
		} else {
			$locale = $lang;
		}
	
		$langInstance = new Multilang($locale);
		$langInstance->setDebug($conf->get('debug_lang'));
	
		return $langInstance;
	}
}
