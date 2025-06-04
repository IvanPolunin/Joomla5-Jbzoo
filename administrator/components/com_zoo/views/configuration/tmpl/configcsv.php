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

<form class="menu-has-level3" action="index.php" method="post" name="adminForm" id="adminForm" accept-charset="utf-8" enctype="multipart/form-data">

<?php echo $this->partial('menu'); ?>

<div class="box-bottom">

	<div class="col col-left width-60">

		<h2><?php echo Text::_('CSV Import'); ?>:</h2>
		<fieldset class="csv-details creation-form">
			<legend><?php echo Text::_('File Details:'); ?></legend>
			<div class="element element-contains-headers">
				<strong><?php echo Text::_('Contains Headers'); ?></strong>
				<input type="checkbox" name="contains-headers" checked="checked">
			</div>
			<div class="element element-field-separator">
				<strong><?php echo Text::_('Field Separator'); ?></strong>
				<div class="row">
					<input type="text" name="field-separator" value=",">
				</div>
			</div>
			<div class="element element-field-enclosure">
				<strong><?php echo Text::_('Field Enclosure'); ?></strong>
				<div class="row">
					<input type="text" name="field-enclosure" value="&quot;">
				</div>
			</div>
		</fieldset>

		<button class="button-grey" id="submit-button" type="submit"><?php echo Text::_('Next'); ?></button>

	</div>
</div>

<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
<input type="hidden" name="task" value="importcsv" />
<input type="hidden" name="file" value="<?php echo $this->file; ?>" />
<input type="hidden" name="changeapp" value="<?php echo $this->application->id; ?>" />
<?php echo $this->app->html->_('form.token'); ?>

</form>

<?php echo ZOO_COPYRIGHT;
