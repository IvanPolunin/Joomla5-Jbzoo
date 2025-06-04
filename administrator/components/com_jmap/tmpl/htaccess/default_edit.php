<?php 
/** 
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage htaccess
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

// Evaluate nonce csp feature
$appNonce = $this->app->get('csp_nonce', null);
$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
?>
<style type="text/css"<?php echo $nonce;?>>
#system-message-container {position: absolute;right: 5px;top: 0; background: transparent; box-shadow: none;}
#system-message-container dl, #system-message-container ul{margin: 0;padding: 0;}
#system-message-container joomla-alert{box-shadow: -5px 5px 5px 1px #888888;}
</style>
<!-- EDITOR ICONS -->
<form id="adminForm" name="adminForm" class="iframeform" action="index.php" method="post">
	<button id="htaccess_save" class="btn btn-primary active"><span class="fas fa-save" aria-hidden="true"></span>
		<?php echo $this->htaccessVersion ? Text::_('COM_JMAP_HTACCESS_TEXTUAL_SAVE') : Text::_('COM_JMAP_HTACCESS_SAVE');?>
	</button>
	<button id="htaccess_prev_versioning" class="btn btn-warning active">
		<span class="fas fa-chevron-left" aria-hidden="true"></span>
		<?php echo Text::_('COM_JMAP_HTACCESS_PREV');?>
		<span class="badge bg-primary" data-bind="versions_counter">0</span>
	</button>
	<button id="htaccess_restore" class="btn btn-warning active"><span class="fas fa-retweet" aria-hidden="true"></span>
		<?php echo Text::_('COM_JMAP_HTACCESS_RESTORE');?>
	</button>
	<button id="fancy_closer" class="btn btn-default btn-secondary active"><span class="fas fa-times" aria-hidden="true"></span>
		<?php echo Text::_('COM_JMAP_HTACCESS_CLOSE');?>
	</button>
	<?php if($this->htaccessVersion):?>
		<label id="htaccess_activate" data-bs-content="<?php echo Text::_('COM_JMAP_HTACCESS_TEXTUAL_WARNING_DESC');?>" data-bs-placement="bottom" class="badge bg-danger hasHtaccessPopover">
		<span class="fas fa-exclamation-triangle" aria-hidden="true"></span>
			<?php echo Text::_('COM_JMAP_HTACCESS_TEXTUAL_WARNING');?>
		</label>
	<?php endif;?>
	
	<div id="htaccess_controls">
		<label class="badge bg-primary" for="htaccess_directive"><?php echo Text::_('COM_JMAP_HTACCESS_DIRECTIVE_TYPE');?></label>
		<select id="htaccess_directive" class="form-select">
			<option data-type="path" data-directive="301" value="301pagefile"><?php echo Text::_('COM_JMAP_HTACCESS_301_PAGEFILE');?></option>
			<option data-type="folder" data-directive="301" value="301pathfolder"><?php echo Text::_('COM_JMAP_HTACCESS_301_PATHFOLDER');?></option>
			<option data-type="path" data-directive="404" value="404pagefile"><?php echo Text::_('COM_JMAP_HTACCESS_404_PAGEFILE');?></option>
			<option data-type="folder" data-directive="404" value="404pathfolder"><?php echo Text::_('COM_JMAP_HTACCESS_404_PATHFOLDER');?></option>
		</select>
		<button id="htaccess_adder" class="btn btn-success active"><span class="fas fa-plus" aria-hidden="true"></span>
			<?php echo Text::_('COM_JMAP_HTACCESS_ADD');?>
		</button>
		
		<div class="paths">
			<label data-role="basic" data-bs-content="<?php echo Text::_('COM_JMAP_HTACCESS_PATH_DESC');?>" class="badge bg-primary hasrightPopover"><?php echo Text::_('COM_JMAP_HTACCESS_OLD_PATH');?></label>
			<input data-role="basic" id="path1" type="text" value=""/>
			
			<label data-role="extended" data-bs-content="<?php echo Text::_('COM_JMAP_HTACCESS_PATH_DESC');?>" class="badge bg-primary hasrightPopover"><?php echo Text::_('COM_JMAP_HTACCESS_NEW_PATH');?></label>
			<input data-role="extended" id="path2" type="text" value=""/>
		</div>
	</div>
	
	<textarea id="htaccess_contents" name="htaccess_contents"><?php echo $this->record;?></textarea>
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value="htaccess.saveEntity"/>
	<input type="hidden" name="restored" value="0"/>
	<input type="hidden" name="tmpl" value="component"/>
</form>