<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::stylesheet('plg_system_nrframework/chainedfields.css', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script('plg_system_nrframework/chainedfields.js', ['relative' => true, 'version' => 'auto']);

extract($displayData);

if (!$data)
{
	return;
}
?>
<div
	class="nr-chained-fields"
	data-data-source="<?php echo $data_source; ?>"
	data-csv="<?php echo htmlspecialchars(json_encode($csv)); ?>"
	data-base-url="<?php echo Uri::base(); ?>"
	data-loading="<?php echo Text::_('NR_LOADING'); ?>">
	<?php
	if (!$value)
	{
		foreach ($data['inputs'] as $key => $input)
		{
			$selectedOrDisabled = isset($data['choices'][$key]) && !$data['choices'][$key]['isSelected'] && $key !== 0 ? 'disabled' : 'selected';
			?>
			<select <?php echo $selectedOrDisabled; ?> name="<?php echo $input['name']; ?>" id="<?php echo $input['id']; ?>" class="nr-chained-fields-select form-select">
				<option class="placeholder" data-original="<?php echo $input['label']; ?>" value=""><?php echo $input['label']; ?></option>
				<?php
				if ($key == 0)
				{
					foreach ($data['choices'] as $key => $choice)
					{
						?><option value="<?php echo $choice['value']; ?>"><?php echo $choice['text']; ?></option><?php
					}
				}
				?>
			</select>
			<?php
		}
	}
	else
	{
		foreach ($value as $key => $value)
		{
			$selectedOrDisabled = empty($value['choices']) ? ' disabled' : '';
			?>
			<select<?php echo $selectedOrDisabled; ?> name="<?php echo $value['name']; ?>" id="<?php echo $value['id']; ?>" class="nr-chained-fields-select form-select">
				<option class="placeholder" data-original="<?php echo $value['label']; ?>" value=""><?php echo $value['label']; ?></option>
				<?php
				if ($value['choices'])
				{
					foreach ($value['choices'] as $key => $choice)
					{
						?><option<?php echo $choice['isSelected'] ? ' selected' : ''; ?> value="<?php echo $choice['value']; ?>"><?php echo $choice['text']; ?></option><?php
					}
				}
				?>
			</select>
			<?php
		}
	}
	?>
</div>