<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

	// add script
	$this->app->document->addScript('assets:js/alias.js');
	$this->app->document->addScript('assets:js/submission.js');

	// filter output
    OutputFilter::objectHTMLSafe($this->submission, ENT_QUOTES, array('params'));

?>

<form action="index.php" method="post" name="adminForm" id="adminForm" accept-charset="utf-8">

	<?php echo $this->partial('menu'); ?>

	<div class="box-bottom">
		<div class="col col-left width-60">

			<fieldset class="creation-form">
				<legend><?php echo Text::_('Details'); ?></legend>
				<div class="element element-name">
					<strong><?php echo Text::_('Name'); ?></strong>
					<div id="name-edit">
						<div class="row">
							<input class="inputbox" type="text" name="name" id="name" size="60" value="<?php echo $this->submission->name; ?>" />
							<span class="message-name"><?php echo Text::_('Please enter valid name.'); ?></span>
						</div>
						<div class="slug">
							<span><?php echo Text::_('Slug'); ?>:</span>
							<a class="trigger" href="#" title="<?php echo Text::_('Edit Submission Slug');?>"><?php echo $this->submission->alias; ?></a>
							<div class="panel">
								<input type="text" name="alias" value="<?php echo $this->submission->alias; ?>" />
								<input type="button" class="btn btn-small accept" value="<?php echo Text::_('Accept'); ?>"/>
								<a href="#" class="cancel"><?php echo Text::_('Cancel'); ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="element element-published">
					<strong><?php echo Text::_('Published'); ?></strong>
					<?php echo $this->lists['select_published']; ?>
				</div>
				<div class="element element-tooltip">
					<strong><?php echo Text::_('Tooltip'); ?></strong>
					<?php echo $this->lists['select_tooltip']; ?>
				</div>
				<div class="element element-notification">
					<strong class="hasTip" title="<?php echo Text::_('EMAIL_NOTIFICATION_DESCRIPTION'); ?>"><?php echo Text::_('Email Notification'); ?></strong>
					<div><input type="text" name="params[email_notification]" value="<?php echo $this->submission->getParams()->get('email_notification', ''); ?>" /></div>
				</div>
				<?php if($this->lists['select_item_captcha']): ?>
				<div class="element element-item-captcha">
					<strong class="hasTip" title="<?php echo Text::_('CAPTCHA_DESCRIPTION'); ?>"><?php echo Text::_('Use Captcha'); ?></strong>
					<div>
						<?php echo $this->lists['select_item_captcha']; ?>
						<div class="guests-only">
							<input id="guests-only" type="checkbox" name="params[captcha_guest_only]" <?php echo $this->submission->getParams()->get('captcha_guest_only', false) ? 'checked="checked"' : ''; ?> />
							<label for="guests-only"><?php echo Text::_('Guests only'); ?></label>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</fieldset>
		   <fieldset class="creation-form">
			   <legend><?php echo Text::_('Security'); ?></legend>
				<div class="element element-access-level">
					<strong><?php echo Text::_('Access level'); ?></strong>
					<div><?php echo $this->lists['select_access']; ?></div>
				</div>
				<div class="element element-max-submissions">
					<strong class="hasTip" title="<?php echo Text::_('Max Submissions per User'); ?>"><?php echo Text::_('Submissions Limit'); ?></strong>
					<div><input type="text" name="params[max_submissions]" value="<?php echo $this->submission->getParams()->get('max_submissions', '0'); ?>" /></div>
				</div>
				<div class="element element-trusted-mode">
					<strong><?php echo Text::_('Trusted Mode'); ?></strong>
					<div>
						<input id="trusted-mode" type="checkbox" name="params[trusted_mode]" class="trusted" <?php echo $this->submission->isInTrustedMode() ? 'checked="checked"' : ''; ?> />
						<label for="trusted-mode"><?php echo Text::_('TRUSTED_MODE_DESCRIPTION'); ?></label>
					</div>
				</div>
		   </fieldset>
		   <fieldset>
				<legend><?php echo Text::_('Types'); ?></legend>
				<?php if (count($this->types)) : ?>
				<table class="admintable">
					<thead>
						<tr>
							<th class="type">
								<?php echo Text::_('Type'); ?>
							</th>
							<th class="layout">
								<?php echo Text::_('Layout'); ?>
							</th>
							<th class="category">
								<?php echo Text::_('SORT INTO CATEGORY ONLY IN NONE TRUSTED MODE'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->types as $type) : ?>
						<tr>
							<td class="name">
								<?php echo $type['name'];?>
							</td>
							<td class="layout">
								<?php echo $type['select_layouts'];?>
							</td>
							<td class="category">
								<?php echo $type['select_categories']?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
					<span class="no-types"><?php echo Text::_('No submission layouts available'); ?></span>
				<?php endif; ?>
		   </fieldset>

		</div>
		<div class="col col-right width-40">

			<div id="parameter-accordion">
				<?php $form = $this->application->getParamsForm()->setValues($this->submission->getParams()->get('content.')); ?>
				<?php if ($form->getParamsCount('submission-content')) : ?>
					<h3 class="toggler"><?php echo Text::_('Content'); ?></h3>
					<div class="content">
						<?php echo $form->render('params[content]', 'submission-content'); ?>
					</div>
				<?php endif; ?>
				<?php $form = $this->application->getParamsForm()->setValues($this->submission->getParams()->get('config.')); ?>
				<?php if ($form->getParamsCount('submission-config')) : ?>
					<h3 class="toggler"><?php echo Text::_('Config'); ?></h3>
					<div class="content">
						<?php echo $form->render('params[config]', 'submission-config'); ?>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>

<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="cid[]" value="<?php echo $this->submission->id; ?>" />
<input type="hidden" name="changeapp" value="<?php echo $this->application->id; ?>" />
<?php echo $this->app->html->_('form.token'); ?>

</form>

<script type="text/javascript">
	jQuery(function($) {
		$('#name-edit').AliasEdit({ edit: <?php echo (int) $this->submission->id; ?> });
		$('#name-edit').find('input[name="name"]').focus();
	});
</script>

<?php echo ZOO_COPYRIGHT;
