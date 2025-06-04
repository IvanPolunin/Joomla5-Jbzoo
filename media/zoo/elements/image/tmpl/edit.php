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

<div>

	<div class="row">
        <?php echo $this->app->html->_('control.text', $this->getControlName('file'), $this->get('file', ''), 'class="image-select" title="'.Text::_('File').'"'); ?>
    	<span class="image-cancel"></span>
    	<button type="button" class="image-select"><?php echo Text::_('Select Image'); ?></button>
    	<div class="image-preview"></div>
    </div>

	<div class="more-options">

		<div class="trigger">
			<div>
				<div class="spotlight button"><?php echo Text::_('Spotlight'); ?></div>
				<div class="lightbox button"><?php echo Text::_('Lightbox'); ?></div>
				<div class="link button"><?php echo Text::_('Link'); ?></div>
				<div class="title button"><?php echo Text::_('Title'); ?></div>
			</div>
		</div>

		<div class="title options">

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('title'), $this->get('title', ''), 'maxlength="255" title="'.Text::_('Title').'" placeholder="'.Text::_('Title').'"'); ?>
			</div>

		</div>

		<div class="link options">

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('link'), $this->get('link', ''), 'size="60" maxlength="255" title="'.Text::_('Link').'" placeholder="'.Text::_('Link').'"'); ?>
			</div>

			<div class="row">
				<strong><?php echo Text::_('New window'); ?></strong>
				<?php echo $this->app->html->_('select.booleanlist', $this->getControlName('target'), $this->get('target'), $this->get('target')); ?>
			</div>

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('rel'), $this->get('rel', ''), 'size="60" maxlength="255" title="'.Text::_('Lightbox').'" placeholder="'.Text::_('Lightbox').'"'); ?>
			</div>

		</div>

		<div class="lightbox options">

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('lightbox_image'), $this->get('lightbox_image', ''), 'class="image-select image-select-nopreview" title="'.Text::_('Lightbox image').'"'); ?>
			</div>

		</div>

		<div class="spotlight options">
			<div class="row">
				<?php echo $this->app->html->_('control.arraylist', array(
					'' => 'None',
					'default' => 'Default',
					'top' => 'Top',
					'bottom' => 'Bottom',
					'left' => 'Left',
					'right' => 'Right',
					'fade' => 'Fade'
				), array(), $this->getControlName('spotlight_effect'), null, 'value', 'text', $this->get('spotlight_effect')); ?>
			</div>

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('caption'), $this->get('caption', ''), 'title="'.Text::_('Caption').'" placeholder="'.Text::_('Caption').'"'); ?>
			</div>

		</div>
	</div>
</div>
