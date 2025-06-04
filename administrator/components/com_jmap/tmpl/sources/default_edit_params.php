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
?>
<div id="accordion_datasource_parameters" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_parameters"><h4><?php echo Text::_('COM_JMAP_Parameters' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_parameters">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsopentarget-lbl" for="paramsopentarget" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_OPEN_TARGET_DESC');?>"><?php echo Text::_('COM_JMAP_OPEN_TARGET');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_opentarget" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_opentarget_arialbl">
						<?php 
							$arr = array(
								HTMLHelper::_('select.option',  '', Text::_('JGLOBAL_USE_GLOBAL' ) ),
								HTMLHelper::_('select.option',  '_self', Text::_('COM_JMAP_SELF_WINDOW' ) ),
								HTMLHelper::_('select.option',  '_blank', Text::_('COM_JMAP_BLANK_WINDOW' ) ),
								HTMLHelper::_('select.option',  '_parent', Text::_('COM_JMAP_PARENT_WINDOW' ) )
							);
							echo JMapHelpersHtml::radiolist( $arr, 'params[opentarget]', '', 'value', 'text', $this->record->params->get('opentarget', ''), 'params_opentarget_');
						?>
					</fieldset>
					<small id="jform_datasource_opentarget_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_OPEN_TARGET_DESC')?></small>
				</td>
			</tr>
			<?php if($this->record->type != 'links'):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsdisable_acl-lbl" for="paramsdisable_acl" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_DISABLE_ACL_DESC');?>"><?php echo Text::_('COM_JMAP_DISABLE_ACL');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_disable_acl" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_disable_acl_arialbl">
						<?php 
							$arr = array(
								HTMLHelper::_('select.option',  '', Text::_('JGLOBAL_USE_GLOBAL' ) ),
								HTMLHelper::_('select.option',  'enabled', Text::_('JENABLED' ) ),
								HTMLHelper::_('select.option',  'disabled', Text::_('JDISABLED' ) )
							);
							echo JMapHelpersHtml::radiolist( $arr, 'params[disable_acl]', '', 'value', 'text', $this->record->params->get('disable_acl', ''), 'params_disable_acl');
						?>
					</fieldset>
					<small id="jform_datasource_disable_acl_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_DISABLE_ACL_DESC')?></small>
				</td>
			</tr>
			<?php endif; ?>
			<?php if(($this->record->type == 'links' || $this->record->type == 'user' || $this->record->type == 'plugin') && array_key_exists('languages', $this->lists)):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramslanguages-lbl" for="paramslanguages" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_DATASOURCE_LANGUAGE_DESC');?>"><?php echo Text::_('COM_JMAP_DATASOURCE_LANGUAGE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_languages" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_languages_arialbl">
						<?php echo $this->lists['languages'];?>
					</fieldset>
					<small id="jform_datasource_languages_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_DATASOURCE_LANGUAGE_DESC')?></small>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramshtmlinclude-lbl" for="paramshtmlinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_HTML_ELEMENTS_INCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_HTML_ELEMENTS_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_htmlinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_htmlinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[htmlinclude]', null,  $this->record->params->get('htmlinclude', 1), 'JYES', 'JNO', 'params_htmlinclude_');?>
					</fieldset>
					<small id="jform_datasource_htmlinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_HTML_ELEMENTS_INCLUDE_DESC')?></small>
				</td>
			</tr>
			<!-- RSS feed include if supported extension --> 
			<?php if($this->supportedRSSExtension || $this->record->type == 'plugin'):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsrssinclude-lbl" for="paramsrssinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_RSS_ELEMENTS_INCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_RSS_ELEMENTS_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_rssinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_rssinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[rssinclude]', null,  $this->record->params->get('rssinclude', 1), 'JYES', 'JNO', 'params_rssinclude_');?>
					</fieldset>
					<small id="jform_datasource_rssinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_RSS_ELEMENTS_INCLUDE_DESC')?></small>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SHOWED_SOURCE_TITLE_DESC');?>"><?php echo Text::_('COM_JMAP_SHOWED_SOURCE_TITLE');?></label></span>
				</td>
				<td class="paramlist_value">
					<input type="text" name="params[title]" id="paramstitle" aria-describedby="paramstitle_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('title', ''), ENT_QUOTES, 'UTF-8');?>" class="text_area" size="50">
					<small id="paramstitle_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SHOWED_SOURCE_TITLE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsshow_title-lbl" for="paramsshow_title" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SHOW_SOURCE_TITLE_DESC');?>"><?php echo Text::_('COM_JMAP_SHOW_SOURCE_TITLE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_showtitle" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_showtitle_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[showtitle]', null,  $this->record->params->get('showtitle', 1), 'JYES', 'JNO', 'params_showtitle_');?>
					</fieldset>
					<small id="jform_datasource_showtitle_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SHOW_SOURCE_TITLE_DESC')?></small>
				</td>
			</tr>
			<!-- User Data source --> 
			<?php if($this->hasCreatedDate):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramscreated_date-lbl" for="paramscreated_date" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_CREATED_DATE_DESC');?>"><?php echo Text::_('COM_JMAP_CREATED_DATE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_created_date" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_created_date_arialbl">
						<?php 
							$monthsOptions = array();
							$monthsOptions[] = HTMLHelper::_('select.option',  '', Text::_('COM_JMAP_NO_DATE_LIMITS' ));
							for($months=1,$maxmonths=60;$months<=$maxmonths;$months++) {
								if($months > 1) {
									$monthsOptions[] = HTMLHelper::_('select.option',  $months, Text::sprintf('COM_JMAP_LAST_MONTHS', $months));
								} else {
									$monthsOptions[] = HTMLHelper::_('select.option',  $months, Text::_('COM_JMAP_LAST_MONTH'));
								}
							}
							echo HTMLHelper::_('select.genericlist',  $monthsOptions, 'params[created_date]', '', 'value', 'text', $this->record->params->get('created_date', ''), 'params_created_date');
						?>
					</fieldset>
					<small id="jform_datasource_created_date_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_CREATED_DATE_DESC')?></small>
				</td>
			</tr>
			<?php endif;?>
			<?php if($this->hasManifest):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsmultilevel_tree-lbl" for="params_multilevel_categories" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_MULTILEVEL_CATEGORIES_DESC');?>"><?php echo Text::_('COM_JMAP_MULTILEVEL_CATEGORIES');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_multilevel_categories" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_multilevel_categories_arialbl">
						<?php 
							$arr = array(
								HTMLHelper::_('select.option',  '', Text::_('JGLOBAL_USE_GLOBAL' ) ),
								HTMLHelper::_('select.option',  1, Text::_('JENABLED' ) ),
								HTMLHelper::_('select.option',  0, Text::_('JDISABLED' ) )
							);
							echo JMapHelpersHtml::radiolist( $arr, 'params[multilevel_categories]', '', 'value', 'text', $this->record->params->get('multilevel_categories', ''), 'params_multilevel_categories_');
						?>
					</fieldset>
					<small id="jform_datasource_multilevel_categories_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_MULTILEVEL_CATEGORIES_DESC')?></small>
				</td>
			</tr>
			<?php endif;?>
			<?php if($this->record->type == 'content'):?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_orderbydate-lbl" for="params_orderbydate" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ORDERBYDATE_DESC');?>"><?php echo Text::_('COM_JMAP_ORDERBYDATE');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_params_orderbydate" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_params_orderbydate_arialbl">
							<?php 
							$arr = array(
									HTMLHelper::_('select.option',  0, Text::_('JNO' ) ),
									HTMLHelper::_('select.option',  1, Text::_('COM_JMAP_YES_BYDATE_CREATED' ) ),
									HTMLHelper::_('select.option',  2, Text::_('COM_JMAP_YES_BYDATE_MODIFIED' ) ),
									HTMLHelper::_('select.option',  3, Text::_('COM_JMAP_YES_BYDATE_PUBLISHED' ) )
							);
							echo JMapHelpersHtml::radiolist( $arr, 'params[orderbydate]', '', 'value', 'text', $this->record->params->get('orderbydate', 0), 'params_orderbydate_');?>
						</fieldset>
						<small id="jform_params_orderbydate_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ORDERBYDATE_DESC')?></small>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_orderbyalpha-lbl" for="params_orderbyalpha" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ORDERBYALPHA_DESC');?>"><?php echo Text::_('COM_JMAP_ORDERBYALPHA');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_params_orderbyalpha" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_params_orderbyalpha_arialbl">
							<?php echo JMapHelpersHtml::booleanlist( 'params[orderbyalpha]', null,  $this->record->params->get('orderbyalpha', 0), 'JYES', 'JNO', 'params_orderbyalpha_');?>
						</fieldset>
						<small id="jform_params_orderbyalpha_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ORDERBYALPHA_DESC')?></small>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_limit_featured_articles-lbl" for="params_limit_featured_articles" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_LIMITFEATURED_ARTICLES_DESC');?>"><?php echo Text::_('COM_JMAP_LIMITFEATURED_ARTICLES');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_params_limit_featured_articles" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_params_limit_featured_articles_arialbl">
							<?php echo JMapHelpersHtml::booleanlist( 'params[limit_featured_articles]', null,  $this->record->params->get('limit_featured_articles', 0), 'JYES', 'JNO', 'params_limit_featured_articles_');?>
						</fieldset>
						<small id="jform_params_limit_featured_articles_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_LIMITFEATURED_ARTICLES_DESC')?></small>
					</td>
				</tr>
				<!-- Rule expand state by data source level --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_show_content_expanded-lbl" for="params_show_content_expanded" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SHOW_CONTENT_EXPANDED_DESC');?>"><?php echo Text::_('COM_JMAP_SHOW_CONTENT_EXPANDED');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_show_content_expanded" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_show_content_expanded_arialbl">
							<?php 
								$arr = array(
									HTMLHelper::_('select.option',  '', Text::_('JGLOBAL_USE_GLOBAL' ) ),
									HTMLHelper::_('select.option',  2, Text::_('JENABLED' ) ),
									HTMLHelper::_('select.option',  1, Text::_('JDISABLED' ) )
								);
								echo JMapHelpersHtml::radiolist( $arr, 'params[show_content_expanded]', '', 'value', 'text', $this->record->params->get('show_content_expanded', ''), 'show_content_expanded_');
							?>
						</fieldset>
						<small id="jform_show_content_expanded_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SHOW_CONTENT_EXPANDED_DESC')?></small>
					</td>
				</tr>
				
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_exclude_all_articles-lbl" for="params_exclude_all_articles" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_EXCLUDE_ALL_ARTICLES_DESC');?>"><?php echo Text::_('COM_JMAP_EXCLUDE_ALL_ARTICLES');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_params_excludeallarticles" class="radio btn-group" data-bs-toggle="buttons" aria-describedby="jform_params_excludeallarticles_arialbl">
							<?php echo JMapHelpersHtml::booleanlist( 'params[exclude_all_articles]', null,  $this->record->params->get('exclude_all_articles', 0), 'JYES', 'JNO', 'params_exclude_all_articles_');?>
						</fieldset>
						<small id="jform_params_excludeallarticles_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_EXCLUDE_ALL_ARTICLES_DESC')?></small>
					</td>
				</tr>
			<?php endif; ?>
			<?php if($this->record->type == 'content' || $this->record->type == 'plugin'):?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramslinkablecontentcats-lbl" for="paramslinkablecontentcats" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_LINKABLE_CONTENT_CATS_DESC');?>"><?php echo Text::_('COM_JMAP_LINKABLE_CONTENT_CATS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_linkable_cats" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_linkable_cats_arialbl">
							<?php echo JMapHelpersHtml::booleanlist( 'params[linkable_content_cats]', null,  $this->record->params->get('linkable_content_cats', 0), 'JYES', 'JNO', 'params_linkable_content_cats_');?>
						</fieldset>
						<small id="jform_datasource_linkable_cats_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_LINKABLE_CONTENT_CATS_DESC')?></small>
					</td>
				</tr>
			<?php endif; ?>
			<?php if($this->record->type == 'content' || ($this->record->type == 'user' && $this->isCategorySource && $this->record->params->get('view', null))): ?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsmergemenutree-lbl" for="paramsmergemenutree" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_MERGE_MENU_TREE_DESC');?>"><?php echo Text::_('COM_JMAP_MERGE_MENU_TREE');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_merge_menu_tree" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_merge_menu_tree_arialbl">
							<?php 
								$arr = array(
									HTMLHelper::_('select.option', '', Text::_('JNO' ) ),
									HTMLHelper::_('select.option', 'yes', Text::_('JYES' ) ),
									HTMLHelper::_('select.option', 'yeshide', Text::_('COM_JMAP_YES_HIDE' ) )
								);
								echo JMapHelpersHtml::radiolist( $arr, 'params[merge_menu_tree]', '', 'value', 'text', $this->record->params->get('merge_menu_tree', ''), 'merge_menu_tree_');
							?>
						</fieldset>
						<small id="jform_datasource_merge_menu_tree_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_MERGE_MENU_TREE_DESC')?></small>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsmergemenutreelevels-lbl" for="paramsmergemenutreelevels" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_MERGE_MENU_TREE_LEVELS_DESC');?>"><?php echo Text::_('COM_JMAP_MERGE_MENU_TREE_LEVELS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_merge_menu_tree_levels" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_merge_menu_tree_levels_arialbl">
							<?php 
								$arr = array(
									HTMLHelper::_('select.option', 'toplevel', Text::_('COM_JMAP_MERGE_TOPLEVEL' ) ),
									HTMLHelper::_('select.option', 'childlevels', Text::_('COM_JMAP_MERGE_CHILDLEVEL' ) ),
									HTMLHelper::_('select.option', 'topchildlevels', Text::_('COM_JMAP_MERGE_TOPCHILDLEVEL' ) )
								);
								echo JMapHelpersHtml::radiolist( $arr, 'params[merge_menu_tree_levels]', '', 'value', 'text', $this->record->params->get('merge_menu_tree_levels', 'toplevel'), 'merge_menu_tree_levels_');
							?>
						</fieldset>
						<small id="jform_datasource_merge_menu_tree_levels_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_MERGE_MENU_TREE_LEVELS_DESC')?></small>
					</td>
				</tr>
			<?php endif;?>
			<?php if($this->record->type == 'user'):?>
				<?php if($this->hasItemsCategorization): ?>
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramslinkablecats-lbl" for="paramslinkablecats" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_LINKABLE_CATS_DESC');?>"><?php echo Text::_('COM_JMAP_LINKABLE_CATS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_linkable_cats" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_linkable_cats_arialbl">
							<?php 
								$arr = array(
									HTMLHelper::_('select.option', '', Text::_('JNO' ) ),
									HTMLHelper::_('select.option', 'yes', Text::_('JYES' ) ),
									HTMLHelper::_('select.option', 'yeshide', Text::_('COM_JMAP_YES_HIDE' ) )
								);
								echo JMapHelpersHtml::radiolist( $arr, 'params[linkable_cats]', '', 'value', 'text', $this->record->params->get('linkable_cats', ''), 'params_linkable_cats_');
							?>
						</fieldset>
						<small id="jform_datasource_linkable_cats_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_LINKABLE_CATS_DESC')?></small>
					</td>
				</tr>
				<?php endif;?>
				<!-- Rule expand state by data source level --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_show_content_expanded-lbl" for="params_show_content_expanded" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SHOW_CONTENT_EXPANDED_DESC');?>"><?php echo Text::_('COM_JMAP_SHOW_CONTENT_EXPANDED');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_show_content_expanded" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_show_content_expanded_arialbl">
							<?php 
								$arr = array(
									HTMLHelper::_('select.option',  '', Text::_('JGLOBAL_USE_GLOBAL' ) ),
									HTMLHelper::_('select.option',  2, Text::_('JENABLED' ) ),
									HTMLHelper::_('select.option',  1, Text::_('JDISABLED' ) )
								);
								echo JMapHelpersHtml::radiolist( $arr, 'params[show_content_expanded]', '', 'value', 'text', $this->record->params->get('show_content_expanded', ''), 'show_content_expanded_');
							?>
						</fieldset>
						<small id="jform_show_content_expanded_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SHOW_CONTENT_EXPANDED_DESC')?></small>
					</td>
				</tr>
				<!-- Parameters section to perform replacements in the final SEF rewritten links --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="params_enable_sef_links_replacements-lbl" for="params_enable_sef_links_replacements" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ENABLE_SEF_LINKS_REPLACEMENTS_DESC');?>"><?php echo Text::_('COM_JMAP_ENABLE_SEF_LINKS_REPLACEMENTS');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_enable_sef_links_replacements" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_enable_sef_links_replacements_arialbl">
							<?php echo JMapHelpersHtml::booleanlist( 'params[enable_sef_links_replacements]', null,  $this->record->params->get('enable_sef_links_replacements', 0), 'JYES', 'JNO', 'params_enable_sef_links_replacements_');?>
						</fieldset>
						<small id="jform_enable_sef_links_replacements_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ENABLE_SEF_LINKS_REPLACEMENTS_DESC')?></small>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title">
						<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_SOURCE_DESC');?>"><?php echo Text::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_SOURCE');?></label></span>
					</td>
					<td class="paramlist_value">
						<input type="text" name="params[sef_links_replacements_source]" id="params_sef_links_replacements_source" aria-describedby="params_sef_links_replacements_source_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('sef_links_replacements_source', ''), ENT_QUOTES, 'UTF-8');?>" class="text_area" size="50">
						<small id="params_sef_links_replacements_source_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_SOURCE_DESC')?></small>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key left_title">
						<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_TARGET_DESC');?>"><?php echo Text::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_TARGET');?></label></span>
					</td>
					<td class="paramlist_value">
						<input type="text" name="params[sef_links_replacements_target]" id="params_sef_links_replacements_target" aria-describedby="params_sef_links_replacements_target_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('sef_links_replacements_target', ''), ENT_QUOTES, 'UTF-8');?>" class="text_area" size="50">
						<small id="params_sef_links_replacements_target_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SEF_LINKS_REPLACEMENTS_TARGET_DESC')?></small>
					</td>
				</tr>
				<!-- Debug SQL data source --> 
				<tr>
					<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsdebug_mode-lbl" for="paramsdebug_mode" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_DEBUG_MODE_DESC');?>"><?php echo Text::_('COM_JMAP_DEBUG_MODE');?></label></span></td>
					<td class="paramlist_value">
						<fieldset id="jform_datasource_debugmode" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_debugmode_arialbl">
							<?php echo JMapHelpersHtml::booleanlist( 'params[debug_mode]', null,  $this->record->params->get('debug_mode', 0), 'JYES', 'JNO', 'params_debugmode_');?>
						</fieldset>
						<small id="jform_datasource_debugmode_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_DEBUG_MODE_DESC')?></small>
					</td>
				</tr>
			<?php endif;?>
			<!-- Menu Data source --> 
			<?php if($this->record->type == 'menu'):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsdounpublished-lbl" for="paramsdounpublished" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_DOUPUBLISHED_DESC');?>"><?php echo Text::_('COM_JMAP_DOUPUBLISHED');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_dounpublished" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_dounpublished_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[dounpublished]', null,  $this->record->params->get('dounpublished', 0), 'JYES', 'JNO', 'params_dounpublished_');?>
					</fieldset>
					<small id="jform_datasource_dounpublished_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_DOUPUBLISHED_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsinclude_external_links-lbl" for="paramsinclude_external_links" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_INCLUDE_EXTERNAL_LINKS_DESC');?>"><?php echo Text::_('COM_JMAP_INCLUDE_EXTERNAL_LINKS');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_include_external_links" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_include_external_links_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[include_external_links]', null,  $this->record->params->get('include_external_links', 1), 'JYES', 'JNO', 'params_include_external_links_');?>
					</fieldset>
					<small id="jform_datasource_include_external_links_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_INCLUDE_EXTERNAL_LINKS_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label for="paramsmaxlevels" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_MAXLEVELS_DESC');?>"><?php echo Text::_('COM_JMAP_MAXLEVELS');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_maxlevels" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_maxlevels_arialbl">
						<?php echo HTMLHelper::_('select.integerlist', 1, 100, 1, 'params[maxlevels]', null,  $this->record->params->get('maxlevels', 5));?>
					</fieldset>
					<small id="jform_datasource_maxlevels_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_MAXLEVELS_DESC')?></small>
				</td>
			</tr>
			<?php endif;?>
		</table>
		<input type="hidden" name="params[datasource_extension]" value="<?php echo $this->record->params->get('datasource_extension', '');?>"/>
	</div>
</div> 