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

$selected = isset($this->_categories) ? $this->_categories : $this->_item->getRelatedCategoryIds();
$multiple = $params->get('multiple', true) ? ' multiple="multiple"' : '';

?>

<div>
	<?php echo $this->app->html->_('zoo.categorylist', $this->_item->getApplication(), array(), $this->getControlName('value', true), 'class="form-select" title="'.Text::_('Category').'" size="'.min($this->_item->getApplication()->getCategoryCount(), 15).'"'.$multiple, 'value', 'text', $selected); ?>
	<?php if ($params->get('primary', false)) : ?>
		<div><?php echo Text::_('Select Primary Category'); ?></div>
		<?php echo $this->app->html->_('zoo.categorylist', $this->_item->getApplication(), array($this->app->html->_('select.option', '', Text::_('COM_ZOO_NONE'))), $this->getControlName('primary'), 'title="'.Text::_('Primary Category').'"', 'value', 'text', $this->_item->getPrimaryCategoryId()); ?>
	<?php endif; ?>
</div>

<script type="text/javascript">
	jQuery(function($) {
		var categories_elem = $('#elements_itemcategoryvalue'), primary_elem = $('#elements_itemcategoryprimary');
		if (!categories_elem || !primary_elem) return;

		categories_elem.on('change', function() {
			var categories = $(this).val() ? $(this).val() : [], primary = primary_elem.val();
			if ($.inArray(primary, categories) == -1) primary_elem.val(categories.length ? categories.shift() : '');
		});

		primary_elem.bind('change', function() {
			var categories = categories_elem.val() ? categories_elem.val() : [], primary = $(this).val();
			if ($.inArray(primary, categories) == -1) {
				categories.push(primary);
				categories_elem.val(categories);
			}
		});
	});
</script>
