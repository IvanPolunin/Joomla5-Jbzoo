<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

if (!isset($choices) || !is_array($choices) || !count($choices))
{
	return;
}

$mode = isset($mode) ? $mode : 'svg';
$gap = isset($gap) && !empty($gap) ? $gap : 'inherit';
$class = isset($class) ? ' ' . $class : '';
$columns = isset($columns) ? $columns : null;
$id = isset($id) ? $id : null;

$class .= ' ' . $id;

if ($columns)
{
	Factory::getDocument()->addStyleDeclaration('
		.tf-choiceselector-control.' . $id . ' {
			--columns: ' . $columns . ';
			--gap: ' . $gap . ';
		}'
	);
}

HTMLHelper::stylesheet('plg_system_nrframework/controls/choiceselector.css', ['relative' => true, 'version' => 'auto']);
?>
<div class="tf-choiceselector-control mode-<?php echo $mode; ?><?php echo $class; ?>">
	<?php
		$i = 0;
		foreach ($choices as $key => $_value)
		{
			$_value = !is_string($_value) ? (array) $_value : $_value;
			
			$id = $name . '_' . (empty($item_id) ? $key : $item_id);
			
			$image = isset($_value['image']) ? $_value['image'] : false;
			$icon = isset($_value['icon']) ? $_value['icon'] : false;
			$label = isset($_value['label']) ? $_value['label'] : $_value;
			$pro = isset($_value['pro']) ? (bool) $_value['pro'] : false;
			?>
			<div class="tf-choiceselector-control--item<?php echo $pro ? ' pro' : ''; ?>"<?php echo $pro ? ' data-pro-only="' . Text::_($label) . '"' : ''; ?>>
					<?php echo $pro ? '<span class="pro">' . Text::_('NR_PRO') . '</span>' : ''; ?>
					
					<input type="radio" id="fpf-control-input-item_<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo $key; ?>"<?php echo $value == $key ? ' checked="checked"' : ''; ?> />
					<label for="fpf-control-input-item_<?php echo $id; ?>">
						<?php echo $mode == 'svg' && !empty($icon) ? $icon : ''; ?>
						<span class="text"><?php echo Text::_($label); ?></span>
					</label>
			</div>
			<?php
			$i++;
		}
	?>
</div>