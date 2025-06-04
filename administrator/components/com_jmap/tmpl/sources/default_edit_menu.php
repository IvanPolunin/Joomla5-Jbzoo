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
?>
<div id="accordion_datasource_excludemenu" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_excludemenu"><h4><?php echo Text::_('COM_JMAP_MENU_EXCLUSION' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_excludemenu">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="exclusion" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_CHOOSE_MENU_EXCLUSION_DESC');?>"><?php echo Text::_('COM_JMAP_CHOOSE_MENU_EXCLUSION');?></label></span>
				</td>
				<td class="paramlist_value">
					<div aria-describedby="jform_menu_exclusion_arialbl">
						<?php echo $this->lists['exclusion']; ?>
					</div>
					<small id="jform_menu_exclusion_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_CHOOSE_MENU_EXCLUSION_DESC')?></small>
				</td>
			</tr>
		</table>
	</div>
</div>

<div id="accordion_datasource_menupriorities" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_menupriorities"><h4><?php echo Text::_('COM_JMAP_MENU_PRIORITIES' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_menupriorities">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramsmenu_priorities" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ASSIGN_MENU_PRIORITIES_DESC');?>"><?php echo Text::_('COM_JMAP_ASSIGN_MENU_PRIORITIES');?></label></span>
				</td>
				<td class="paramlist_value">
					<?php echo $this->lists['menu_priorities']; ?>
					<?php echo $this->lists['priorities']; ?>
					<div id="controls_grouper" aria-describedby="jform_assign_menu_priorities_arialbl">
						<button data-role="priority_action" data-action="store" data-type="MenuPriorities" class="btn btn-sm btn-primary active"><span class="fas fa-save" aria-hidden="true"></span><?php echo Text::_('COM_JMAP_ASSIGN_MENU_PRIORITIES_BTN');?></button>
						<button data-role="priority_action" data-action="remove" data-type="MenuPriorities" class="btn btn-sm btn-default btn-secondary active"><span class="fas fa-times" aria-hidden="true"></span><?php echo Text::_('COM_JMAP_REMOVE_MENU_PRIORITIES_BTN');?></button>
					</div>
					<small id="jform_assign_menu_priorities_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_CHOOSE_MENU_EXCLUSION_DESC')?></small>
				</td>
			</tr>
		</table>
	</div>
</div>