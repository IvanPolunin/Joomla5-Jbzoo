<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2018 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

HTMLHelper::stylesheet('plg_system_nrframework/controls/unit_selector.css', ['relative' => true, 'version' => 'auto']);

$multipleUnits = count($units) > 1;

if ($multipleUnits)
{
	HTMLHelper::script('plg_system_nrframework/controls/unit_selector.js', ['relative' => true, 'version' => 'auto']);
}
?>
<div class="tf-unit-control-dropdown<?php echo $multipleUnits ? ' has-multiple-units' : ''; ?><?php echo isset($dropdown_class) && !empty($dropdown_class) ? ' ' . $dropdown_class : ''; ?>">
	<div class="tf-unit-control-dropdown--opener"<?php echo count($units) > 1 ? ' title="' . Text::_('NR_SELECT_UNIT') . '"' : ''; ?>>
		<span class="tf-unit-control-dropdown--opener--selected--unit"><?php echo $unit; ?></span>
		<?php if ($multipleUnits): ?>
			<svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 5.46666L0 1.46666L0.933333 0.533325L4 3.59999L7.06667 0.533325L8 1.46666L4 5.46666Z" fill="currentColor" /></svg>
		<?php endif; ?>
	</div>
	<?php if ($multipleUnits): ?>
	<ul class="tf-unit-control-dropdown--drop">
		<?php foreach ($units as $unit_key): ?>
			<li class="tf-unit-control-dropdown--drop--item<?php echo $unit_key === $unit ? ' selected' : ''; ?>"><?php echo $unit_key; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	<?php
	if (!isset($hide_input))
	{
		$layout = new FileLayout('input_value', JPATH_PLUGINS . '/system/nrframework/layouts/controls/unit');
		echo $layout->render($displayData);
	}
	?>
</div>