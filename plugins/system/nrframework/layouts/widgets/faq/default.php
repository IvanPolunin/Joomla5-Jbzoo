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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

extract($displayData);

if (!$value || !is_array($value) || !count($value))
{
	return;
}

if (!$readonly && !$disabled)
{
	foreach (\NRFramework\Widgets\FAQ::getJS() as $path)
	{
		HTMLHelper::script($path, ['relative' => true, 'version' => 'auto']);
	}
}

if ($load_stylesheet)
{
	foreach (\NRFramework\Widgets\FAQ::getCSS() as $path)
	{
		HTMLHelper::stylesheet($path, ['relative' => true, 'version' => 'auto']);
	}
}

if ($load_css_vars && !empty($custom_css))
{
	Factory::getDocument()->addStyleDeclaration($custom_css);
}

$open_icon = $close_icon = '';
if ($show_toggle_icon)
{
	switch ($icon)
	{
		case 'arrow':
			$open_icon = '<svg width="20" height="20" viewBox="0 0 20 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 11.4421L0 1.44217L1.44217 0L10 8.58507L18.5578 0.0271949L20 1.46936L10 11.4421Z" fill="currentColor" /></svg>';
			$open_icon = '<svg width="20" height="20" viewBox="0 0 20 9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 9L2.5 1.13436L3.58162 0L10 6.75274L16.4184 0.0213906L17.5 1.15575L10 9Z" fill="currentColor"/></svg>';
			break;
		case 'circle_arrow':
			$open_icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 13.1012L14.2287 8.87247L13.3705 8.04049L10 11.411L6.62954 8.04049L5.77128 8.87247L10 13.1012ZM10.0018 20C8.62946 20 7.3365 19.7375 6.12288 19.2126C4.90927 18.6877 3.84796 17.971 2.93895 17.0624C2.02994 16.1537 1.31285 15.0929 0.787713 13.8798C0.262571 12.6667 0 11.374 0 10.0018C0 8.61867 0.262457 7.31863 0.787371 6.10165C1.31228 4.88464 2.02904 3.82603 2.93764 2.9258C3.84626 2.02555 4.9071 1.31285 6.12017 0.787713C7.33326 0.262571 8.62595 0 9.99824 0C11.3813 0 12.6814 0.262457 13.8984 0.78737C15.1154 1.31228 16.174 2.02465 17.0742 2.92448C17.9744 3.82433 18.6871 4.88248 19.2123 6.09894C19.7374 7.31538 20 8.61514 20 9.99824C20 11.3705 19.7375 12.6635 19.2126 13.8771C18.6877 15.0907 17.9753 16.152 17.0755 17.061C16.1757 17.9701 15.1175 18.6871 13.9011 19.2123C12.6846 19.7374 11.3849 20 10.0018 20ZM10 18.8057C12.4507 18.8057 14.531 17.9481 16.2409 16.2328C17.9508 14.5176 18.8057 12.44 18.8057 10C18.8057 7.54926 17.9508 5.46895 16.2409 3.75909C14.531 2.04924 12.4507 1.19432 10 1.19432C7.56005 1.19432 5.48244 2.04924 3.76719 3.75909C2.05194 5.46895 1.19432 7.54926 1.19432 10C1.19432 12.44 2.05194 14.5176 3.76719 16.2328C5.48244 17.9481 7.56005 18.8057 10 18.8057Z" fill="currentColor" /></svg>';
			break;
		case 'plus_minus':
			$open_icon = '<svg width="20" height="20" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.36966 15V8.13034H2.5V6.86966H9.36966V0H10.6303V6.86966H17.5V8.13034H10.6303V15H9.36966Z" fill="currentColor"/></svg>';
			$close_icon = '<svg width="20" height="20" viewBox="0 0 20 3" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 2.13033V0.869659H18V2.13033H3Z" fill="currentColor"/></svg>';
			break;
		case 'circle_plus_minus':
			$open_icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.46155 15H10.6558V10.6721H15V9.47773H10.6558V5.00001H9.46155V9.47773H5.00001V10.6721H9.46155V15ZM10.0088 20C8.62358 20 7.32624 19.7375 6.11673 19.2126C4.90721 18.6877 3.84796 17.971 2.93895 17.0624C2.02994 16.1537 1.31285 15.0943 0.787713 13.884C0.262571 12.6737 0 11.3753 0 9.9886C0 8.60869 0.262457 7.31163 0.787371 6.09744C1.31228 4.88324 2.02904 3.82603 2.93764 2.9258C3.84626 2.02555 4.90571 1.31285 6.11599 0.787713C7.32627 0.262571 8.62474 0 10.0114 0C11.3913 0 12.6884 0.262457 13.9026 0.78737C15.1168 1.31228 16.174 2.02465 17.0742 2.92448C17.9744 3.82433 18.6871 4.88248 19.2123 6.09894C19.7374 7.31538 20 8.61281 20 9.99124C20 11.3764 19.7375 12.6738 19.2126 13.8833C18.6877 15.0928 17.9753 16.1504 17.0755 17.0562C16.1757 17.962 15.1175 18.679 13.9011 19.2074C12.6846 19.7358 11.3872 20 10.0088 20ZM10.0132 18.8057C12.4551 18.8057 14.531 17.9481 16.2409 16.2328C17.9508 14.5176 18.8057 12.4356 18.8057 9.98684C18.8057 7.54487 17.9524 5.46895 16.2458 3.75909C14.5393 2.04924 12.4573 1.19432 10 1.19432C7.56005 1.19432 5.48244 2.0476 3.76719 3.75417C2.05194 5.46073 1.19432 7.54268 1.19432 10C1.19432 12.44 2.05194 14.5176 3.76719 16.2328C5.48244 17.9481 7.56443 18.8057 10.0132 18.8057Z" fill="currentColor" /></svg>';
			$close_icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.00001 10.5384H15V9.34416H5.00001V10.5384ZM10.0018 20C8.62946 20 7.3365 19.7375 6.12288 19.2126C4.90927 18.6877 3.84796 17.971 2.93895 17.0624C2.02994 16.1537 1.31285 15.0929 0.787713 13.8798C0.262571 12.6667 0 11.374 0 10.0018C0 8.61867 0.262457 7.31863 0.787371 6.10165C1.31228 4.88464 2.02904 3.82603 2.93764 2.9258C3.84626 2.02555 4.9071 1.31285 6.12017 0.787713C7.33326 0.262571 8.62595 0 9.99824 0C11.3813 0 12.6814 0.262457 13.8984 0.78737C15.1154 1.31228 16.174 2.02465 17.0742 2.92448C17.9744 3.82433 18.6871 4.88248 19.2123 6.09894C19.7374 7.31538 20 8.61514 20 9.99824C20 11.3705 19.7375 12.6635 19.2126 13.8771C18.6877 15.0907 17.9753 16.152 17.0755 17.061C16.1757 17.9701 15.1175 18.6871 13.9011 19.2123C12.6846 19.7374 11.3849 20 10.0018 20ZM10 18.8057C12.4507 18.8057 14.531 17.9481 16.2409 16.2328C17.9508 14.5176 18.8057 12.44 18.8057 10C18.8057 7.54926 17.9508 5.46895 16.2409 3.75909C14.531 2.04924 12.4507 1.19432 10 1.19432C7.56005 1.19432 5.48244 2.04924 3.76719 3.75909C2.05194 5.46895 1.19432 7.54926 1.19432 10C1.19432 12.44 2.05194 14.5176 3.76719 16.2328C5.48244 17.9481 7.56005 18.8057 10 18.8057Z" fill="currentColor" /></svg>';
			break;
	}
}

$total_items = count($value);
$items_per_column = $columns ? ceil($total_items / $columns) : $total_items;
$item_index = 0;
?>
<div class="nrf-widget tf-faq-widget<?php echo $css_class; ?>">
	<?php
	for ($i = 0; $i < $columns; $i++)
	{
		if ($columns > 1)
		{
			?><div class="tf-faq--row"><?php
		}

		for ($j = 0; $j < $items_per_column; $j++)
		{
			if ($item_index >= $total_items)
			{
				continue;
			}

			$faq_item = $value[$item_index];

			$item_atts = '';

			$answer_atts = 'style="height: 0px;"';

			if (($initial_state === 'first-open' && $item_index === 0) || $initial_state === 'all-open')
			{
				$item_atts = 'data-open="true"';
				$answer_atts = '';
			}
			?>
			<div class="tf-faq-widget--item<?php echo $item_css_class ? ' ' . $item_css_class : ''; ?>"<?php echo $item_atts; ?>>
				<?php if (isset($faq_item['question'])): ?>
				<div class="tf-faq-widget--item--question">
					<?php echo $show_toggle_icon && $icon_position === 'left' ? '<div class="tf-faq-widget--item--question--actions">' . $open_icon . $close_icon . '</div>' : ''; ?>
					<div class="tf-faq-widget--item--question--content"><?php echo $faq_item['question']; ?></div>
					<?php echo $show_toggle_icon && $icon_position === 'right' ? '<div class="tf-faq-widget--item--question--actions">' . $open_icon . $close_icon . '</div>' : ''; ?>
				</div>
				<?php endif; ?>
				<?php if (isset($faq_item['answer'])): ?>
				<div class="tf-faq-widget--item--answer"<?php echo $answer_atts; ?>>
					<?php echo nl2br($faq_item['answer']); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php

			$item_index++;

			if ($separator && $item_index !== $total_items)
			{
				?><hr class="tf-faq-separator" /><?php
			}
		}

		if ($columns > 1)
		{
			?></div><?php
		}
	}
	?>
</div>