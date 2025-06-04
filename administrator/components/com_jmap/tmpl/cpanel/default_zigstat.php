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
<!-- ZIGSTAT SEOSTATS -->
<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-line fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_mozrank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_SEMRUSHRANK');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-certificate fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_mozdomainauth}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_DOMAINAUTH');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-star fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_pageviews}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_PAGEVIEWS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-tachometer-alt fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_serpkeywords}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_SERPKEYWORDS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-exchange-alt fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_backlinks}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_BACKLINKS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-area fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_openpagerank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_GLOBALRANK');?></li>
	</ul>
</div>

<div class="single_stat_rowseparator"></div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-user fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_dailyvisitor}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_DAILYVISITORS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-copy fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_followbacklinks}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_FOLLOWBACKLINKS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-copy fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{zigstat_nofollowbacklinks}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_ZIGSTAT_NOFOLLOWBACKLINKS');?></li>
	</ul>
</div>

<div class="card card-body card-stats card-maxwidth card-hidden" data-bind="{website_report_text}"></div>