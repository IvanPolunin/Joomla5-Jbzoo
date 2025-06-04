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
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_GLOBAL_RANK');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-area fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{organic_visit}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_ORGANIC_VISIT');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-star fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{domain_authority}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_DOMAIN_AUTHORITY');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-line fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{traffic}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_TRAFFIC');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-link fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{backlinks}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_BACKLINKS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-exchange-alt fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{organic_search_traffic}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_ORGANIC_SEARCH_TRAFFIC');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-users fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{users_per_day}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_USERS_PER_DAY');?></li>
	</ul>
</div>

<div class="single_stat_rowseparator"></div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-tachometer-alt fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{openpagerank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_OPENPAGERANK');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-database fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{semrushrank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_SEMRUSHRANK');?></li>
	</ul>
</div>
	
<!-- Competitors and charts row -->

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-tags fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{semrushkeywords}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_SEMRUSHKEYWORDS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-percent fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{organictraffic}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_ORGANICTRAFFIC');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-users fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{pageviews_per_day}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_XRANKS_PAGEVIEWS_PER_DAY');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-globe fa-icon-large"></span>
	</div>
	<ul class="subdescription_stats semrush_popovers">
		<li></li>
		<li data-bind="{xranks_competitors}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_XRANKS_COMPETITORS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<ul class="subdescription_stats">
		<li data-bind="{website_screen}" class="es-stat-no fancybox-image"></li>
	</ul>
</div>

<div class="single_stat_rowseparator"></div>

<div class="card card-body card-stats card-hidden" data-bind="{website_report_text}"></div>