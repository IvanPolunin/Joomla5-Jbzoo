<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

$this->app->document->addScript('libraries:jquery/jquery-ui.custom.min.js');
$this->app->document->addStylesheet('libraries:jquery/jquery-ui.custom.css');
$this->app->document->addScript('libraries:jquery/plugins/timepicker/timepicker.js');
$this->app->document->addStylesheet('libraries:jquery/plugins/timepicker/timepicker.css');
$this->app->document->addStylesheet('assets:css/submission.css');
$this->app->document->addScript('assets:js/submission.js');
$this->app->document->addScript('assets:js/placeholder.js');
$this->app->document->addScript('assets:js/item.js');

if ($this->submission->showTooltip()) {
	$this->app->html->_('behavior.tooltip');
}

?>

<?php if ($this->errors): ?>
	<?php $msg = $this->errors > 1 ? Text::_('Oops. There were errors in your submission.') : Text::_('Oops. There was an error in your submission.'); ?>
	<?php $msg .= ' '.Text::_('Please take a look at all highlighted fields, correct your data and try again.'); ?>
	<p class="message"><?php echo $msg; ?></p>
<?php endif; ?>

<form id="item-submission" action="<?php echo Route::_($this->app->route->submission($this->submission, $this->type->id, null, $this->item->id, $this->redirectTo)); ?>" method="post" name="submissionForm" accept-charset="utf-8" enctype="multipart/form-data">

	<?php

		echo $this->renderer->render($this->layout_path, array('item' => $this->item, 'submission' => $this->submission));

		// Captcha support
		if ($this->captcha) {
			$this->app->html->_('behavior.framework');
			echo $this->captcha->display('captcha', 'captcha', 'captcha');
		}
	?>

	<p class="info"><?php echo Text::_('REQUIRED_INFO'); ?></p>

	<div class="submit">
		<button type="submit" id="submit-button" class="button-green"><?php echo $this->item->id ? Text::_('Save') : Text::_('Submit Item'); ?></button>
		<?php if ($this->cancelUrl) : ?>
			<a href="<?php echo Route::_($this->cancelUrl); ?>" id="cancel-button"><?php echo Text::_('Cancel'); ?></a>
		<?php endif; ?>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->app->component->self->name; ?>" />
	<input type="hidden" name="controller" value="submission" />
	<input type="hidden" name="task" value="save" />

	<?php echo $this->app->html->_('form.token'); ?>

</form>

<script type="text/javascript">
	jQuery(function($) {
		$('#item-submission').EditItem();
		$('#item-submission').Submission({ uri: '<?php echo Uri::root(); ?>' });
	});
</script>
