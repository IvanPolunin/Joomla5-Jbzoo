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

	<div class="name-input">
		<label for="name">Name</label>
		<input type="text" name="<?php echo $var.'[element]['.$num.'][name]'; ?>" value="<?php echo $name; ?>" />
	</div>
	<div class="type-input">
		<label for="type">Type</label>
        <select name="<?php echo $var.'[element]['.$num.'][type]'; ?>">
            <?php foreach ($options as $option) : ?>
            <option value="<?php echo $option['value']; ?>"<?php echo $option['value'] == $type ? ' selected' : ''; ?>><?php echo $option['text']; ?></option>
            <?php endforeach; ?>
        </select>
	</div>
	<div class="delete" title="<?php echo Text::_('Delete element'); ?>">
		<img alt="<?php echo Text::_('Delete element'); ?>" src="<?php echo $this->app->path->url('assets:images/delete.png'); ?>"/>
	</div>
	<div class="sort-handle" title="<?php echo Text::_('Sort element'); ?>">
		<img alt="<?php echo Text::_('Sort element'); ?>" src="<?php echo $this->app->path->url('assets:images/sort.png'); ?>"/>
	</div>
