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
use Joomla\CMS\HTML\HTMLHelper;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;

$exclusionWay = $this->record->params->get('choose_exclusion_way', 'exclude');
$categoryExclusionText = $exclusionWay == 'exclude' ? 'COM_JMAP_CHOOSE_CATEGORIES_EXCLUSION' : 'COM_JMAP_CHOOSE_CATEGORIES_INCLUSION';
$articleExclusionText = $exclusionWay == 'exclude' ? 'COM_JMAP_CHOOSE_ARTICLES_EXCLUSION' : 'COM_JMAP_CHOOSE_ARTICLES_INCLUSION';
?>
<div id="accordion_datasource_excludecats" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_excludecats"><h4><?php echo $exclusionWay == 'exclude' ? Text::_('COM_JMAP_CATEGORIES_EXCLUSION' ) : Text::_('COM_JMAP_CATEGORIES_INCLUSION' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_excludecats">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramschoose_exclusion_way-lbl" for="paramschoose_exclusion_way" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_CHOOSE_EXCLUSION_WAY_DESC');?>"><?php echo Text::_('COM_JMAP_CHOOSE_EXCLUSION_WAY');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_choose_exclusion_way" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_choose_exclusion_way_arialbl">
						<?php 
							$arr = array(
								HTMLHelper::_('select.option',  'include', Text::_('COM_JMAP_INCLUDE' ) ),
								HTMLHelper::_('select.option',  'exclude', Text::_('COM_JMAP_EXCLUDE' ) )
							);
							echo JMapHelpersHtml::radiolist($arr, 'params[choose_exclusion_way]', '', 'value', 'text', $exclusionWay, 'paramschoose_exclusion_way_');
						?>
					</fieldset>
					<small id="jform_datasource_choose_exclusion_way_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_CHOOSE_EXCLUSION_WAY_DESC')?></small>
				</td>
			</tr>
			
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramschoose_catexclusion-lbl" for="paramschoose_catexclusion" class="hasPopover" data-bs-content="<?php echo Text::_($categoryExclusionText . '_DESC');?>"><?php echo Text::_($categoryExclusionText);?></label></span>
				</td>
				<td class="paramlist_value">
					<div aria-describedby="jform_catexclusion_arialbl">
						<?php echo $this->lists['catexclusion']; ?>
					</div>
					<small id="jform_catexclusion_arialbl" class="form-text text-muted"><?php echo Text::_($categoryExclusionText . '_DESC')?></small>
				</td>
			</tr>
		</table>
	</div>
</div>

<?php if(isset($this->lists['articleexclusion'])):?>
<div id="accordion_datasource_excludearticles" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_excludearticles"><h4><?php echo $exclusionWay == 'exclude' ? Text::_('COM_JMAP_ARTICLES_EXCLUSION' ) : Text::_('COM_JMAP_ARTICLES_INCLUSION' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_excludearticles">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramschoose_artexclusion-lbl" for="paramschoose_artexclusion" class="hasPopover" data-bs-content="<?php echo Text::_($articleExclusionText . '_DESC');?>"><?php echo Text::_($articleExclusionText);?></label></span>
				</td>
				<td class="paramlist_value">
					<div aria-describedby="jform_articleexclusion_arialbl">
						<?php echo $this->lists['articleexclusion']; ?>
					</div>
					<small id="jform_articleexclusion_arialbl" class="form-text text-muted"><?php echo Text::_($articleExclusionText . '_DESC')?></small>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php endif;?>

<div id="accordion_datasource_workflowstages" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_workflowstages"><h4><?php echo Text::_('COM_JMAP_WORKFLOW_STAGES_MAINTITLE' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_workflowstages">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramschoose_workflowstages-lbl" for="paramschoose_workflowstages" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_WORKFLOW_STAGES_TITLE_DESC');?>"><?php echo Text::_('COM_JMAP_WORKFLOW_STAGES_TITLE');?></label></span>
				</td>
				<td class="paramlist_value">
					<div aria-describedby="jform_articlestages_arialbl">
						<?php echo $this->lists['articlestages']; ?>
					</div>
					<small id="jform_articlestages_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_WORKFLOW_STAGES_TITLE_DESC')?></small>
				</td>
			</tr>
		</table>
	</div>
</div>

<div id="accordion_datasource_catspriorities" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_catspriorities"><h4><?php echo Text::_('COM_JMAP_CATS_PRIORITIES' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_catspriorities">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramspriorities-lbl" for="paramspriorities" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ASSIGN_CATS_PRIORITIES_DESC');?>"><?php echo Text::_('COM_JMAP_ASSIGN_CATS_PRIORITIES');?></label></span>
				</td>
				<td class="paramlist_value">
					<?php echo $this->lists['cats_priorities']; ?>
					<?php echo $this->lists['priorities']; ?>
					<div id="controls_grouper" aria-describedby="jform_assign_cats_priorities_arialbl">
						<button data-role="priority_action" data-action="store" data-type="CatsPriorities" class="btn btn-sm btn-primary active"><span class="fas fa-save" aria-hidden="true"></span><?php echo Text::_('COM_JMAP_ASSIGN_MENU_PRIORITIES_BTN');?></button>
						<button data-role="priority_action" data-action="remove" data-type="CatsPriorities" class="btn btn-sm btn-danger active"><span class="fas fa-times" aria-hidden="true"></span><?php echo Text::_('COM_JMAP_REMOVE_MENU_PRIORITIES_BTN');?></button>
					</div>
					<small id="jform_assign_cats_priorities_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ASSIGN_CATS_PRIORITIES_DESC')?></small>
				</td>
			</tr>
		</table>
	</div>
</div>