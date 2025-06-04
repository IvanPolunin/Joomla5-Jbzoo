<?php 
/** 
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
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
</style>
<!-- CPANEL ICONS -->
<form id="adminForm" name="adminForm" class="iframeform" action="index.php" method="post">
	<button class="btn btn-primary active"><span class="fas fa-save" aria-hidden="true"></span>
		<?php echo $this->robotsVersion ? Text::_('COM_JMAP_ROBOTS_DIST_SAVE') : Text::_('COM_JMAP_ROBOTS_SAVE');?>
	</button>
	<button id="fancy_closer" class="btn btn-default btn-secondary active"><span class="fas fa-times" aria-hidden="true"></span>
		<?php echo Text::_('COM_JMAP_ROBOTS_CLOSE');?>
	</button>
	<?php if($this->robotsVersion):?>
		<label data-bs-content="<?php echo Text::_('COM_JMAP_ROBOTS_DIST_WARNING_DESC');?>" data-bs-placement="bottom" class="badge bg-warning hasRobotsPopover">
		<span class="fas fa-exclamation-triangle" aria-hidden="true"></span>
			<?php echo Text::_('COM_JMAP_ROBOTS_DIST_WARNING');?>
		</label>
	<?php endif;?>
	
	<div id="robots_ctrls">
		<label class="badge bg-primary" for="robots_rule"><?php echo Text::_('COM_JMAP_ROBOTS_CHOOSE_RULE');?></label>
		<select id="robots_rule" class="form-select">
			<option value="Disallow: ">Disallow</option>
			<option value="Allow: ">Allow</option>
			<option value="User-agent: ">User agent</option>
		</select>
		<input id="robots_entry" type="text" value=""/>
		<button id="robots_adder" class="btn btn-success active"><span class="fas fa-plus" aria-hidden="true"></span>
			<?php echo Text::_('COM_JMAP_ROBOTS_ADD');?>
		</button>
	</div>
	
	<textarea id="robots_contents" name="robots_contents"><?php echo $this->record;?></textarea>
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value="cpanel.saveEntity"/>
	<input type="hidden" name="tmpl" value="component"/>
</form>
