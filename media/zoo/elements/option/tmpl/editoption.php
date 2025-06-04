<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>

	<div class="name-input">
		<label for="name">Name</label>
		<input type="text" name="<?php echo $var.'[option]['.$num.'][name]'; ?>" value="<?php echo htmlspecialchars($name); ?>" />
	</div>
	<div class="value-input">
		<label for="value">Value</label>
		<input type="text" name="<?php echo $var.'[option]['.$num.'][value]'; ?>" value="<?php echo htmlspecialchars($value); ?>" />
	</div>
	<div class="delete" title="<?php echo Text::_('Delete option'); ?>">
		<img alt="<?php echo Text::_('Delete option'); ?>" src="<?php echo $this->app->path->url('assets:images/delete.png'); ?>"/>
	</div>
	<div class="sort-handle" title="<?php echo Text::_('Sort option'); ?>">
		<img alt="<?php echo Text::_('Sort option'); ?>" src="<?php echo $this->app->path->url('assets:images/sort.png'); ?>"/>
	</div>
