<?php
/**
 * @package   ZOO Item
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$media_position = $params->get('media_position', 'top');

?>

<div class="uk-panel">

	<?php if (($media_position == 'left' || $media_position == 'right') && $this->checkPosition('media')) : ?>
	<div class="uk-grid-small uk-flex-middle" uk-grid>
		<div class="uk-width-auto">
			<?php echo $this->renderPosition('media'); ?>
		</div>
		<div class="uk-width-expand">
	<?php elseif (($media_position == 'top') && $this->checkPosition('media')) : ?>
		<div class="uk-margin"><?php echo $this->renderPosition('media'); ?></div>
	<?php endif; ?>

		<?php if ($this->checkPosition('title')) : ?>
		<h4 class="uk-margin-remove"><?php echo $this->renderPosition('title'); ?></h4>
		<?php endif; ?>

		<?php if ($this->checkPosition('meta')) : ?>
		<p class="uk-text-meta uk-margin-remove"><?php echo $this->renderPosition('meta', array('style' => 'comma')); ?></p>
		<?php endif; ?>

		<?php if (($media_position == 'middle') && $this->checkPosition('media')) : ?>
		<div class="uk-margin"><?php echo $this->renderPosition('media'); ?></div>
		<?php endif; ?>

		<?php if ($this->checkPosition('description')) : ?>
			<?php echo $this->renderPosition('description', array('style' => 'uikit_block')); ?>
		<?php endif; ?>

		<?php if (($media_position == 'bottom') && $this->checkPosition('media')) : ?>
		<div class="uk-margin-remove-bottom"><?php echo $this->renderPosition('media'); ?></div>
		<?php endif; ?>

		<?php if ($this->checkPosition('links')) : ?>
		<ul class="uk-subnav uk-subnav-divider">
			<?php echo $this->renderPosition('links', array('style' => 'uikit_subnav')); ?>
		</ul>
		<?php endif; ?>

	<?php if (($media_position == 'left' || $media_position == 'right') && $this->checkPosition('media')) : ?>
		</div>
	</div>
	<?php endif; ?>

</div>
