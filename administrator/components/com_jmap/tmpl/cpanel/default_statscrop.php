<?php
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

?>
<!-- STATSCROP SEOSTATS -->
<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-line fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{global_rank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_GLOBAL_RANK');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-users fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{daily_visitors}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_DAILY_VISITORS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-compress-alt fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{daily_pageviews}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_BOUNCE_RATE');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-exchange-alt fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{page_load_time}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_PAGE_LOAD_TIME');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-star fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{rating_stars}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_RATING');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-area fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{openpagerank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_OPENPAGERANK');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-database fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{google_indexed_links}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_GOOGLE_INDEXED_LINKS');?></li>
	</ul>
</div>

<div class="single_stat_rowseparator"></div>
	
<!-- Competitors and charts row -->

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-link fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li></li>
		<li data-bind="{links_internal}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_STATSCROP_INTERNAL_LINKS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-link fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li></li>
		<li data-bind="{links_external}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_STATSCROP_EXTERNAL_LINKS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-tag fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats semrush_popovers">
		<li></li>
		<li data-bind="{keywords}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_STATSCROP_KEYWORDS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-arrow-left fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats semrush_popovers">
		<li></li>
		<li data-bind="{backlinkers}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_STATSCROP_BACKLINKERS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-globe fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats semrush_popovers">
		<li></li>
		<li data-bind="{countries}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_STATSCROP_COUNTRIES');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-link fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li></li>
		<li data-bind="{subdomains}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_STATSCROP_SUBDOMAINS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<ul class="subdescription_stats">
		<li data-bind="{statscrop_website_screen}" class="es-stat-no fancybox-image"></li>
	</ul>
</div>

<div class="single_stat_container seostatschart">
	<canvas id="statscanvas"></canvas>
	<ul class="subdescription_stats seostatschart">
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_STATSCROP_TRAFFIC_GRAPH');?></li>
	</ul>
</div>
