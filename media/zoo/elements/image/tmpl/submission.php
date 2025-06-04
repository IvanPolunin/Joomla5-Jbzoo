<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

?>

<div class="<?php echo $this->identifier; ?>">

	<div class="image-select">

		<div class="upload">
			<input type="text" id="filename<?php echo $this->identifier; ?>" readonly="readonly" />
			<div class="button-container">
				<button class="button-grey search" type="button"><?php echo Text::_('Search'); ?></button>
				<input type="file" name="elements_<?php echo $this->identifier; ?>" onchange="javascript: document.getElementById('filename<?php echo $this->identifier; ?>').value = this.value.replace(/^.*[\/\\]/g, '');" />
			</div>
		</div>

		<?php if (isset($lists['image_select'])) : ?>

			<span class="select"><?php echo Text::_('ALREADY UPLOADED'); ?></span><?php echo $lists['image_select']; ?>

		<?php else : ?>

			<input type="hidden" class="image" name="<?php echo $this->getControlName('image'); ?>" value="<?php echo $image ? 1 : ''; ?>">

		<?php endif; ?>

	</div>

	<div class="image-preview">
		<img src="<?php echo $image; ?>" alt="preview">
		<span class="image-cancel" title="<?php Text::_('Remove image'); ?>"></span>
	</div>

	<?php if ($trusted_mode) : ?>

	<div class="more-options">

		<div class="trigger">
			<div>
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

	</div>
	<?php endif; ?>

</div>

<script type="text/javascript">
	jQuery(function($) {
		$('#item-submission .<?php echo $this->identifier; ?>').ImageSubmission({ uri: '<?php echo Uri::root(); ?>' });
	});
</script>
