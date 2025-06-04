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
<!-- SITERANKDATA SEOSTATS -->
<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-chart-line fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{siterankdata_rank}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_SITERANKDATA_PAGE_RANK');?></li>
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
		<span class="fas fa-user fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats">
		<li data-bind="{yearly_visitors}" class="es-stat-no"></li>
		<li class="es-stat-title"><?php echo Text::_('COM_JMAP_YEARLY_VISITORS');?></li>
	</ul>
</div>

<div class="single_stat_container">
	<div class="statcircle">
		<span class="fas fa-globe fa-icon-large" aria-hidden="true"></span>
	</div>
	<ul class="subdescription_stats semrush_popovers">
		<li></li>
		<li data-bind="{siterankdata_competitors}" class="es-stat-no hasClickPopover"><?php echo Text::_('COM_JMAP_SITERANKDATA_COMPETITORS');?></li>
	</ul>
</div>

<div class="single_stat_container alexachart">
	<ul class="subdescription_stats alexachart">
		<li data-bind="{website_screen}" class="es-stat-no fancybox-image"></li>
	</ul>
</div>