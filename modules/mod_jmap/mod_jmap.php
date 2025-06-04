<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use JExtStore\Module\Jmap\Site\Helper\JmapHelper;
use JExtstore\Component\JMap\Administrator\Framework\Loader;

/**
 * Module for sitemap footer navigation
 *
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @since 3.0
 */
// Include the syndicate functions only once
if($params->get('height_auto', 1)) {
	JmapHelper::jmapInjectAutoHeightScript();
}

$scroll = htmlspecialchars($params->get('scrolling'));
$width	= htmlspecialchars($params->get('width'));
if(stripos($width, 'px') === false && stripos($width, '%') === false) {
	$width .= 'px';
}
$height = htmlspecialchars($params->get('height'));
$height = preg_replace('/(%|px)/i', '', $height);

$onLoad = $params->get('height_auto', 1) ? 'onload="jmapIFrameAutoHeight(\'jmap_sitemap_nav_' . $module->id . '\')"' : '';
$dataset = (int)$params->get('dataset', null);
$dataset = $dataset ? '&amp;dataset=' . $dataset : ''; 
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''));

// Check for multilanguage
$app = Factory::getApplication();
$currentLanguageQueryString = null;
$currentSefLanguage = null;
if ($app->isClient('site')) {
	$multilangEnabled = $app->getLanguageFilter();
	$currentSefLanguage = $multilangEnabled ?  $app->getLanguage()->getLocale() : null;
	if(is_array($currentSefLanguage)) {
		$partialSef = explode('_', $currentSefLanguage[2]);
		$sefLang = array_shift($partialSef);
		$currentLanguageQueryString = '&amp;lang=' . $sefLang;
		$currentSefLanguage = $sefLang . '/';
	}
}

// Standard routing, full raw query string
$targetIFrameUrl = Uri::base() . 'index.php?option=com_jmap&amp;view=sitemap&amp;tmpl=component&amp;jmap_module=' . $module->id . $dataset . $currentLanguageQueryString;

// Setup the lazy loading mode for the iframe
$iframeLazyLoading = $params->get('iframe_loading_mode', 'lazy') == 'lazy' ? 'lazy' : 'eager';

// Legacy routing /en, /de, etc
if($params->get('legacy_routing', 0)) {
	// Try to check for an active htaccess file
	$index = null;
	if(!$app->get ( 'sef_rewrite' )) {
		$index = 'index.php/';
	}
	$targetIFrameUrl = Uri::base() . $index . $currentSefLanguage . '?option=com_jmap&amp;view=sitemap&amp;tmpl=component&amp;jmap_module=' . $module->id . $dataset;
}

if($params->get('module_rendering_mode', 'iframe') == 'iframe') {
	// Module iframe rendering
	require ModuleHelper::getLayoutPath('mod_jmap', $params->get('layout', 'default'));
} else {
	/**
	 * Component execute and fetch
	 * Load language files
	 * Auto loader setup
	 * Register autoloader prefix
	 */
	// Manage partial language translations
	$jLang = $app->getLanguage ();
	$jLang->load ( 'com_jmap', JPATH_ROOT . '/components/com_jmap', 'en-GB', true, true );
	if ($jLang->getTag () != 'en-GB') {
		$jLang->load ( 'com_jmap', JPATH_SITE, null, true, false );
		$jLang->load ( 'com_jmap', JPATH_SITE . '/components/com_jmap', null, true, false );
	}
	
	require_once JPATH_ADMINISTRATOR . '/components/com_jmap/Framework/Loader.php';
	Loader::setup();
	Loader::registerNamespacePsr4 ( 'JExtstore\\Component\\JMap\\Administrator', JPATH_ADMINISTRATOR . '/components/com_jmap' );
	
	// Class aliasing
	if(!class_exists('JMapRoute')) {
		class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Helpers\\Route', 'JMapRoute');
	}
	
	// Instantiate model
	$extensionMVCFactory = $app->bootComponent('com_jmap')->getMVCFactory();
	$sitemapModel = $extensionMVCFactory->createModel('Sitemap', 'Site', ['document_format'=>'html', 'jmap_module'=>$module->id]);
	$sitemapModel->setState('format', 'html');
	$cparams = $sitemapModel->getComponentParams();
	$cparams->set('show_title', 0);
	
	$view = $extensionMVCFactory->createView('Sitemap', 'Site', 'Html');
	$view->setModel($sitemapModel, true);
	$view->addTemplatePath(JPATH_ROOT . '/components/com_jmap/tmpl/sitemap');
	$contents = $view->display();
	
	echo $contents;
}