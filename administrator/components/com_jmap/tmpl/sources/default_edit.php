<?php 
/** 
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

$formEnctype = $this->record->type == 'plugin' ? 'enctype="multipart/form-data"' : null;
?>
<form <?php echo $formEnctype;?> action="index.php" method="post" name="adminForm" id="adminForm">
	<!-- New Plugin Data source only --> 
	<?php if($this->record->type == 'plugin' && !$this->record->id) {
		echo $this->loadTemplate('edit_pluginimport');
	}?>	
	<!-- Data source details --> 
	<?php echo $this->loadTemplate('edit_details'); ?>
	<!-- Data source parameters --> 
	<?php echo $this->loadTemplate('edit_params'); ?>
	<!-- Data source parameters for XML format --> 
	<?php echo $this->loadTemplate('edit_xmlparams'); ?>
	<!-- Menu Data source only --> 
	<?php if($this->record->type == 'menu') {
		echo $this->loadTemplate('edit_menu');
	}?>
	<!-- Content Data source only --> 
	<?php if($this->record->type == 'content') {
		echo $this->loadTemplate('edit_content');
	}?>
	<!-- User Data source only --> 
	<?php if ($this->record->type == 'user') {
		echo $this->loadTemplate('edit_sqlquery');
	}?>
	<!-- Plugin Data source only --> 
	<?php if($this->record->type == 'plugin' && $this->record->id) {
		echo $this->loadTemplate('edit_pluginparams');
	}?>	
	<!-- Links Data source only --> 
	<?php if($this->record->type == 'links') {
		echo $this->loadTemplate('edit_links');
	}?>	
	<input type="hidden" name="option" value="<?php echo $this->option?>" />
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="ordering" value="<?php echo $this->record->ordering; ?>" />
	<input type="hidden" id="regenerate_query" name="regenerate_query" value="" />
	<input type="hidden" name="task" value="" />
</form>

<!-- Go to bottom -->
<div class="badge bg-secondary" id="gobottom">
	<span class="fas fa-arrow-down" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_GO_TO_BOTTOM');?>
</div>

<!-- Back to top -->
<div class="badge bg-secondary" id="backtop">
	<span class="fas fa-arrow-up" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_BACK_TO_TOP');?>
</div>