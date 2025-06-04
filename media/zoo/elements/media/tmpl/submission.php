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
        <?php echo $this->app->html->_('control.text', $this->getControlName('url'), $this->get('url'), 'class="url" size="50" maxlength="255" title="'.Text::_('URL').'" placeholder="'.Text::_('URL').'"'); ?>
    </div>

    <?php if ($trusted_mode) : ?>

	<div class="more-options">
		<div class="trigger">
			<div>
				<div class="advanced button hide"><?php echo Text::_('Hide Options'); ?></div>
				<div class="advanced button"><?php echo Text::_('Show Options'); ?></div>
			</div>
		</div>

		<div class="advanced options">
			<div class="row short">
				<?php echo $this->app->html->_('control.text', $this->getControlName('width'), $this->get('width', $this->config->get('defaultwidth')), 'maxlength="4" title="'.Text::_('Width').'" placeholder="'.Text::_('Width').'"'); ?>
			</div>

			<div class="row short">
				<?php echo $this->app->html->_('control.text', $this->getControlName('height'), $this->get('height', $this->config->get('defaultheight')), 'maxlength="4" title="'.Text::_('Height').'" placeholder="'.Text::_('Height').'"'); ?>
			</div>

			<div class="row">
				<strong><?php echo Text::_('AutoPlay'); ?></strong>
				<?php echo $this->app->html->_('select.booleanlist', $this->getControlName('autoplay'), '', $this->get('autoplay', $this->config->get('defaultautoplay', false))) ?>
			</div>
		</div>
	</div>
    <?php endif; ?>

</div>
