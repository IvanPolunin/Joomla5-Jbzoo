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

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$mapWrapperClass = '';
if (!$map)
{
	$mapWrapperClass .= ' no-map';
}
?>
<div class="nrf-widget tf-mapaddress-editor<?php echo $css_class; ?>">
	<?php if ($required) { ?>
		<!-- Make Joomla client-side form validator happy by adding a fake hidden input field when the Gallery is required. -->
		<input type="hidden" required class="required" id="<?php echo $id; ?>"/>
	<?php } ?>

	<div class="tf-mapaddress-map-wrapper<?php echo $mapWrapperClass; ?>">
		<?php echo $map; ?>
	</div>
	<div class="tf-mapaddress-field-location-details">
		<?php
		foreach ($showAddressDetails as $key => $show)
		{
			if ($key === 'address')
			{
				continue;
			}
			
			$lang_key = 'NR_' . strtoupper($key);
			$placeholder = $lang_key;

			if ($key === 'address')
			{
				$placeholder .= '_ADDRESS_HINT';
			}

			$input_type = $show ? 'text' : 'hidden';
			$visibility_class = $show ? 'visible' : 'hidden';
			?>
			<div class="control-group stack <?php echo $key; ?> is-<?php echo $visibility_class; ?>">
				<div class="control-label"><label for="<?php echo $id ?>-field-address-field-<?php echo $key ?>"><?php echo Text::_($lang_key); ?></label></div>
				<div class="controls">
					<input
						type="<?php echo $input_type; ?>"
						id="<?php echo $id; ?>-field-address-field-<?php echo $key ?>"
						class="form-control w-100 tf-mapaddress-field-<?php echo $key ?>"
						name="<?php echo $name; ?>[address][<?php echo $key; ?>]"
						placeholder="<?php echo Text::_($placeholder); ?>"
						value="<?php echo isset($address[$key]) ? $address[$key] : ''; ?>"
						autocomplete="off"
					/>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>