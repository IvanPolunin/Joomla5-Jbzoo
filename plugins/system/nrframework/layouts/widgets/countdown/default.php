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

if ($countdown_type === 'static' && (empty($value) || $value === '0000-00-00 00:00:00'))
{
	return;
}

if ($load_stylesheet)
{
	foreach (\NRFramework\Widgets\Countdown::getCSS($theme) as $path)
	{
		HTMLHelper::stylesheet($path, ['relative' => true, 'version' => 'auto']);
	}
}

if ($load_css_vars && !empty($custom_css))
{
	Factory::getDocument()->addStyleDeclaration($custom_css);
}

foreach (\NRFramework\Widgets\Countdown::getJS() as $path)
{
	HTMLHelper::script($path, ['relative' => true, 'version' => 'auto']);
}
?>
<div class="nrf-widget nrf-countdown<?php echo $css_class; ?>" id="<?php echo $id; ?>" <?php echo $atts; ?>></div>