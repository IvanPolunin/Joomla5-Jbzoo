<?php
namespace JExtStore\Module\Jmap\Site\Helper;
/**
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;

/**
 * Module for sitemap footer navigation
 *
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @since 3.0
 */
class JmapHelper {
	/** 
	 * Inject singleton script on page if multiple inclusions detected for nav modules
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	public static function jmapInjectAutoHeightScript() {
		static $jmapAutoHeightScript;
		if(!$jmapAutoHeightScript) {
			$doc = Factory::getApplication()->getDocument();
			$doc->getWebAssetManager()->addInlineScript('function jmapIFrameAutoHeight(e){setTimeout(function(){var t=0;if(!document.all){if(!!window.chrome){document.getElementById(e).style.height=0}t=document.getElementById(e).contentDocument.body.scrollHeight;document.getElementById(e).style.height=t+150+"px"}else if(document.all){if(!!window.performance){var n=document.getElementById(e);var r=n.contentWindow.document||n.contentDocument;var t=Math.max(r.body.offsetHeight,r.body.scrollHeight);t+=150;n.style.height=t+"px";n.setAttribute("height",t)}else{t=document.frames(e).document.body.scrollHeight;document.all.jmap_sitemap_nav.style.height=t+150+"px"}}},10)}');
			$jmapAutoHeightScript = true;
		}
	}
}
