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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

extract($displayData);

// Get all countries data
$countries = \NRFramework\Countries::getCountriesData();

// Find the default country
$default_country_code = isset($value['code']) && !empty($value['code']) && isset($countries[$value['code']]) ? $value['code'] : 'AF';

// Find the default calling code
$default_calling_code = '+' . $countries[$default_country_code]['calling_code'];

$flag_base_url = implode('/', [rtrim(Uri::root(), '/'), 'media', 'plg_system_nrframework', 'img', 'flags']);

$placeholder = !empty($placeholder) ? $placeholder : '_ _ _ _ _ _';

if ($inputmask)
{
	$placeholder = '';
}

?>
<div class="tf-phone-control<?php echo $class ? ' ' . $class : ''; ?>"<?php echo $readonly ? ' readonly' : ''; ?> data-flags-base-url="<?php echo $flag_base_url; ?>" data-id="<?php echo $id; ?>" data-required="<?php echo $required ? 'true' : 'false'; ?>">
	<?php if ($required && (empty($value['code']) || empty($value['value']))) { ?>
		<input type="hidden" required class="required tf-phone-control--validator" id="<?php echo $id; ?>"/>
	<?php } ?>
	
	<div class="tf-phone-control--skeleton tf-phone-control--flag">
		<img width="27" height="13.5" src="<?php echo implode('/', [$flag_base_url, strtolower($default_country_code) . '.png']); ?>"></img>
		<svg class="tf-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" width="19"><path fill="currentColor" d="M480-333 240-573l51-51 189 189 189-189 51 51-240 240Z"/></svg>
		<span class="tf-flag-calling-code"><?php echo $default_calling_code; ?></span>
	</div>
	
	<select
		class="tf-phone-control--flag--selector noChosen"
		name="<?php echo $name ?>[code]"
		>
		<?php
		foreach ($countries as $key => $country)
		{
			$selected = isset($value['code']) && $value['code'] === $key;
			?><option value="<?php echo $key; ?>" <?php echo $selected ? ' selected' : ''; ?>><?php echo $country['name']; ?></option><?php
		}
		?>
	</select>
	
	<input
		type="tel"
		class="tf-phone-control--number<?php echo !empty($input_class) ? ' ' . $input_class : ''; ?>"
		<?php echo $inputmask ? ' data-imask="' . $inputmask . '"' : ''; ?>
		<?php echo $required ? ' required' : ''; ?>
		<?php echo $readonly ? ' readonly' : ''; ?>
		<?php echo $browserautocomplete ? ' autocomplete="off"' : ''; ?>
		placeholder="<?php echo $placeholder ?>"
		value="<?php echo isset($value['value']) ? $value['value'] : ''; ?>"
		name="<?php echo $name; ?>[value]"
	/>
</div>
