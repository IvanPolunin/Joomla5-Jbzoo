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

$class = $submission ? 'class="form-control" ' : '';

?>

<div>

	<?php echo $this->app->html->_('control.text', $this->getControlname('value'), $this->get('value', ''), $class .'size="60" title="'.Text::_('Email').'"'); ?>

	<?php if ($trusted_mode) : ?>

	<div class="more-options">
		<div class="trigger">
			<div>
				<div class="advanced button hide"><?php echo Text::_('Hide Options'); ?></div>
				<div class="advanced button"><?php echo Text::_('Show Options'); ?></div>
			</div>
		</div>

		<div class="advanced options">
			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('text'), $this->get('text', ''), 'size="60" title="'.Text::_('Link Text').'" placeholder="'.Text::_('Link Text').'"'); ?>
			</div>

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('subject'), $this->get('subject', ''), 'size="60" title="'.Text::_('Subject').'" placeholder="'.Text::_('Subject').'"'); ?>
			</div>

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('body'), $this->get('body', ''), 'size="60" title="'.Text::_('Body').'" placeholder="'.Text::_('Body').'"'); ?>
			</div>
		</div>
	</div>

	<?php endif; ?>

</div>
