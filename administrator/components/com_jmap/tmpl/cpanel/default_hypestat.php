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
<!-- HYPESTAT SEOSTATS -->
<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-line fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{hypestat_rank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_HYPESTAT_PAGE_RANK');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-user fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{daily_unique_visitors}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_DAILY_UNIQUE_VISITORS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-user fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{monthly_visitors}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_MONTHLY_VISITORS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-copy fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{pages_per_visit}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_HYPESTAT_PAGES_PER_VISIT');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-file fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{daily_pageviews}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_HYPESTAT_DAILY_PAGEVIEWS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-exchange-alt fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{backlinks}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_HYPESTAT_BACKLINKS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<ul class="subdescription_stats">
		<li data-bind="{website_screen}" class="es-stat-no fancybox-image"></li>
	</ul>
</div>

<div class="single_stat_rowseparator"></div>

<div class="card card-body card-stats card-hidden" data-bind="{website_report_text}"></div>