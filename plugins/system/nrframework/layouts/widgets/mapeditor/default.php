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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

extract($displayData);

$options = isset($options) ? $options : $displayData;

if ($options['load_css_vars'] && !empty($options['custom_css']))
{
	Factory::getDocument()->addStyleDeclaration($options['custom_css']);
}
?>
<div class="nrf-widget tf-map-editor<?php echo $options['css_class']; ?>" id="<?php echo $options['id']; ?>" data-options="<?php echo htmlspecialchars(json_encode($options)); ?>">
	<div class="tf-map-editor--app"><?php echo Text::_('NR_LOADING_MAP'); ?></div>
	<input type="hidden" name="<?php echo $options['name']; ?>" id="<?php echo $options['id']; ?>" value="<?php echo htmlspecialchars(json_encode($options['value'])); ?>" class="tf-map-editor--value<?php echo $options['required'] ? ' required' : ''; ?>"<?php echo $options['required'] ? ' required' : ''; ?> />
</div>