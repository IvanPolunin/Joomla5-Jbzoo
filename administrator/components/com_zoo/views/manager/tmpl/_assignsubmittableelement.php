<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>
<li class="element hideconfig" data-element="<?php echo $element->identifier; ?>">
	<div class="element-icon edit-element edit-event" title="<?php echo Text::_('Edit element'); ?>"></div>
	<div class="element-icon delete-element delete-event" title="<?php echo Text::_('Delete element'); ?>"></div>
	<div class="name sort-event" title="<?php echo Text::_('Drag to sort'); ?>"><?php echo $element->config->get('name'); ?>
		<span>(<?php echo $element->getMetaData('name'); ?>)</span>
	</div>
	<div class="config">
		<?php echo $element->getConfigForm()->setValues($data)->render($element->identifier, 'submission'); ?>
		<input type="hidden" name="<?php echo $element->identifier; ?>[element]" value="<?php echo $element->identifier; ?>" />
	</div>
</li>
