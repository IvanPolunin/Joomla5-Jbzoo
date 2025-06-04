<?php
/**
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Module for sitemap footer navigation
 *
 * @author Joomla! Extensions Store
 * @package JMAP::modules::mod_jmap
 * @since 3.0
 */
?>
<iframe <?php echo $onLoad;?>
	id="jmap_sitemap_nav_<?php echo $module->id;?>"
	src="<?php echo $targetIFrameUrl;?>"
	scrolling="<?php echo $scroll; ?>"
	loading="<?php echo $iframeLazyLoading;?>"
	title="JSitemap sitemap"
	style="border:none;width:<?php echo $width; ?>;height:<?php echo $height; ?>px;"
	class="wrapper <?php echo $moduleclass_sfx; ?>" >
</iframe>