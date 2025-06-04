<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Manage partial language translations
$jLang = Factory::getApplication()->getLanguage();
$jLang->load('com_jmap', JPATH_BASE . '/components/com_jmap', 'en-GB', true, true);
if($jLang->getTag() != 'en-GB') {
	$jLang->load('com_jmap', JPATH_BASE, null, true, false);
	$jLang->load('com_jmap', JPATH_BASE . '/components/com_jmap', null, true, false);
}
$doc = Factory::getApplication()->getDocument();
$currentVersion = strval(simplexml_load_file(JPATH_BASE . '/components/com_jmap/jmap.xml')->version);
$doc->getWebAssetManager()->addInlineScript ( 'jQuery(function($){$("div.module-wrapper div.card-header h2[id*=jsitemap]").prepend("<span class=\"fas fa-sitemap\" aria-hidden=\"true\"></span>")});function jmapCompareVersions(e,a){var r,t,c=">",n=0,s={dev:-6,alpha:-5,a:-5,beta:-4,b:-4,RC:-3,rc:-3,"#":-2,p:1,pl:1},u=function(e){return e=(""+e).replace(/[_\-+]/g,"."),e=e.replace(/([^.\d]+)/g,".$1.").replace(/\.{2,}/g,"."),e.length?e.split("."):[-8]},l=function(e){return e?isNaN(e)?s[e]||-7:parseInt(e,10):0};for(e=u(e),a=u(a),t=Math.max(e.length,a.length),r=0;t>r;r++)if(e[r]!==a[r]){if(e[r]=l(e[r]),a[r]=l(a[r]),e[r]<a[r]){n=-1;break}if(e[r]>a[r]){n=1;break}}if(!c)return n;switch(c){case">":case"gt":return n>0;case">=":case"ge":return n>=0;case"<=":case"le":return 0>=n;case"===":case"=":case"eq":return 0===n;case"<>":case"!==":case"ne":return 0!==n;case"":case"<":case"lt":return 0>n;default:return null}};jQuery.get("index.php?option=com_jmap&task=cpanel.getUpdates&format=raw",function(a){a&&"object"==typeof a&&(jQuery("span[data-bind=jmap_version]").html(a.latest),jmapCompareVersions(a.latest,"'.$currentVersion.'")?jQuery("i.icon-cancel, span[data-status=outdated]").show():jQuery("i.icon-checkmark, span[data-status=updated]").show())},"json");');

$doc->getWebAssetManager()->useScript('jquery');
$doc->getWebAssetManager()->useScript('jquery-noconflict');

// Class aliasing
if(!class_exists('JMapRoute')) {
	class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Helpers\\Route', 'JMapRoute');
}
?>
<nav class="quick-icons" aria-label="Quick Links System" tabindex="-1" id="ui-skip-91">
	<ul class="nav flex-wrap row-fluid px-3">
		<li class="quickicon quickicon-single col mb-3">
			<a href="<?php echo \JMapRoute::_('index.php?option=com_jmap'); ?>" class="d-flex align-items-center justify-content-center" role="button">
				<img style="width:24px; height:24px;" alt="" src="<?php echo Uri::base() . 'components/com_jmap/images/jmap-32x32.png'?>" />
				<span style="margin-left:5px"><?php echo Text::_('COM_JMAP_CPANEL');?></span>
			</a>
		</li>
		
		<li class="quickicon quickicon-single col mb-3">
			<a href="<?php echo \JMapRoute::_('index.php?option=com_jmap&task=config.display'); ?>" class="d-flex align-items-center justify-content-center" role="button">
				<img style="width:24px; height:24px;" alt="" src="<?php echo Uri::base() . 'components/com_jmap/images/icon-32-config.png'?>" />
				<span style="margin-left:5px"><?php echo Text::_('COM_JMAP_CONFIG');?></span>
			</a>
		</li>
		
		<li class="quickicon quickicon-single col mb-3">
			<a href="<?php echo \JMapRoute::_('index.php?option=com_jmap'); ?>" class="d-flex align-items-center justify-content-center" role="button">
				<?php echo Text::_('COM_JMAP_MODULEPANEL_STATE');?>
			</a>
		</li>
	</ul>
</nav>