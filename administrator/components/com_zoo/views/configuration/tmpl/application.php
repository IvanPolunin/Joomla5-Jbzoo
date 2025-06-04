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

// add js
$this->app->html->_('behavior.modal');
$this->app->document->addScript('assets:js/configuration.js');
$this->app->document->addScript('assets:js/alias.js');

$this->app->html->_('behavior.tooltip');

// filter output
OutputFilter::objectHTMLSafe($this->application, ENT_QUOTES, array('params'));

?>

<form class="menu-has-level3" action="index.php" method="post" name="adminForm" id="adminForm" accept-charset="utf-8">

<?php echo $this->partial('menu'); ?>

<div class="box-bottom">
	<div class="col col-left width-60">

		<fieldset class="creation-form">
		<legend><?php echo Text::_('Details'); ?></legend>
		<div class="element element-name">
			<strong><?php echo Text::_('Name'); ?></strong>
			<div id="name-edit">
				<div class="row">
					<input class="inputbox" type="text" name="name" id="name" size="60" value="<?php echo $this->application->name; ?>"/>
					<span class="message-name"><?php echo Text::_('Please enter valid name.'); ?></span>
				</div>
				<div class="slug">
					<span><?php echo Text::_('Slug'); ?>:</span>
					<a class="trigger" href="#" title="<?php echo Text::_('Edit Application Slug');?>"><?php echo (empty($this->application->alias) ? 42 : $this->application->alias); ?></a>
					<div class="panel">
						<input type="text" name="alias" value="<?php echo $this->application->alias; ?>"/>
						<input type="button" class="btn btn-small accept" value="<?php echo Text::_('Accept'); ?>"/>
						<a href="#" class="cancel"><?php echo Text::_('Cancel'); ?></a>
					</div>
				</div>
			</div>
		</div>
		<div class="element element-template">
			<strong><?php echo Text::_('Template'); ?></strong>
			<div><?php echo $this->lists['select_template']; ?></div>
		</div>
		</fieldset>

	</div>

	<div class="col col-right width-40">

		<div id="parameter-accordion">
			<?php echo $this->partial('applicationparams')?>
		</div>

	</div>
</div>

<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="format" value="" />
<input type="hidden" name="changeapp" value="<?php echo $this->application->id; ?>" />
<?php echo $this->app->html->_('form.token'); ?>

</form>

<script type="text/javascript">
	jQuery(function($) {
		$('#adminForm').ApplicationEdit({ application_id: '<?php echo $this->application->id;?>', application_group: '<?php echo $this->application->getGroup();?>' });
		$('#name-edit').AliasEdit({ edit: <?php echo (int) $this->application->id; ?> });
		$('#name-edit').find('input[name="name"]').focus();
	});
	//catch save to remove rules fiels from request
	Joomla.submitbutton = function(task) {
		jQuery("#zoo-permissions select").attr("disabled", "disabled");
		Joomla.submitform(task, document.getElementById("adminForm"));
	};
</script>

<?php echo ZOO_COPYRIGHT;
