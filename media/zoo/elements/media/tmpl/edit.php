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

<div id="<?php echo $this->identifier; ?>">

    <div class="row">
		<?php echo $this->app->html->_('control.text', $this->getControlName('file'), $this->get('file', ''), 'placeholder="'.Text::_('File').'" class="file"'); ?>
    </div>

    <div class="row">
        <?php echo $this->app->html->_('control.text', $this->getControlName('url'), $this->get('url', ''), 'placeholder="'.Text::_('URL').'" class="url" size="50" maxlength="255" title="'.Text::_('URL').'"'); ?>
    </div>

	<div class="more-options">
		<div class="trigger">
			<div>
				<div class="file button"><?php echo Text::_('Video/Audio File'); ?></div>
				<div class="url button"><?php echo Text::_('Video Provider'); ?></div>
				<div class="poster button"><?php echo Text::_('Poster'); ?></div>
				<div class="advanced button hide"><?php echo Text::_('Hide Options'); ?></div>
				<div class="advanced button"><?php echo Text::_('Show Options'); ?></div>
			</div>
		</div>

		<div class="poster options">

			<div class="row">
				<?php echo $this->app->html->_('control.text', $this->getControlName('poster_image'), $this->get('poster_image', ''), 'class="image-select image-select-nopreview" title="'.Text::_('Poster image').'"'); ?>
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
				<?php echo $this->app->html->_('select.booleanlist', $this->getControlName('autoplay'), '', $this->get('autoplay', $this->config->get('defaultautoplay', false))); ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(function($) {
		$('#<?php echo $this->identifier; ?> input[name="<?php echo $this->getControlName('file'); ?>"]').Directories({
			mode: 'file',
			url: '<?php echo $this->app->link(array('task' => 'callelement', 'format' => 'raw', 'type' => $this->_item->getType()->id, 'item_id' => $this->_item->id, 'elm_id' => $this->identifier, 'method' => 'files'), false); ?>',
			title: '<?php echo Text::_('Files'); ?>',
			msgDelete: '<?php echo Text::_('Delete'); ?>'
		});
		$('#<?php echo $this->identifier; ?>').ElementMedia();
	});
</script>
