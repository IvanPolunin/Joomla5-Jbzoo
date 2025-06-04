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

    <?php echo $this->app->html->_('control.text', $this->getControlName('value'), $this->get('value', ''), $class .'size="60" title="'.Text::_('Link').'"'); ?>

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
				<?php echo $this->app->html->_('control.text', $this->getControlName('text'), $this->get('text', ''), 'size="60" title="'.Text::_('Text').'" placeholder="'.Text::_('Text').'"'); ?>
			</div>

			<div class="row">
				<strong><?php echo Text::_('New window'); ?></strong>
				<?php echo $this->app->html->_('select.booleanlist', $this->getControlName('target'), '', $this->get('target', $this->config->get('default_target'))) ?>
			</div>

			<div class="row short">
				<?php echo $this->app->html->_('control.text', $this->getControlName('custom_title'), $this->get('custom_title', ''), 'size="60" title="'.Text::_('Title').'" placeholder="'.Text::_('Title').'"'); ?>
			</div>

			<div class="row short">
				<?php echo $this->app->html->_('control.text', $this->getControlName('rel'), $this->get('rel', ''), 'size="60" title="'.Text::_('Lightbox').'" placeholder="'.Text::_('Lightbox').'"'); ?>
			</div>

		</div>
	</div>

    <?php endif; ?>

</div>

<?php if ($this->config->get('add_protocol', 1)) : ?>
<script type="text/javascript">
	jQuery(function($) {
		$('input[name="<?php echo $this->getControlName('value'); ?>"]').blur(function(){
			var link = $(this).val();
			if ((link.length > 0) && (link.indexOf(':') == -1)) {
				$(this).val('http://' + link);
			}
		});
	});
</script>
<?php endif;
