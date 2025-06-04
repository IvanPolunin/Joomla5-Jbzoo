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
<div id="accordion_datasource_xmlparameters" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_xmlparameters"><h4><?php echo Text::_('COM_JMAP_XMLSITEMAP_PARAMETERS' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_xmlparameters">
		<table  class="admintable">
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsxmlinclude-lbl" for="paramsxmlinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_INCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_xmlinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_xmlinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[xmlinclude]', null,  $this->record->params->get('xmlinclude', 1), 'JYES', 'JNO', 'params_xmlinclude_');?>
					</fieldset>
					<small id="jform_datasource_xmlinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_INCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_PRIORITY_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_PRIORITY');?></label></span>
				</td>
				<td class="paramlist_value">
					<div aria-describedby="jform_elements_priority_arialbl">
						<?php echo $this->lists['priority']; ?>
					</div>
					<small id="jform_elements_priority_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_PRIORITY_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_CHANGEFREQUENCY_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_CHANGEFREQUENCY');?></label></span>
				</td>
				<td class="paramlist_value">
					<div aria-describedby="jform_elements_changefrequency_arialbl">
						<?php echo $this->lists['changefreq']; ?>
					</div>
					<small id="jform_elements_changefrequency_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_CHANGEFREQUENCY_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsxmlmobileinclude-lbl" for="paramsxmlmobileinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_MOBILEINCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_MOBILEINCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_xmlmobileinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_xmlmobileinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[xmlmobileinclude]', null,  $this->record->params->get('xmlmobileinclude', 1), 'JYES', 'JNO', 'params_xmlmobileinclude_');?>
					</fieldset>
					<small id="jform_datasource_xmlmobileinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_MOBILEINCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsxmlimagesinclude-lbl" for="paramsxmlimagesinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_IMAGESINCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_IMAGESINCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_xmlimagesinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_xmlimagesinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[xmlimagesinclude]', null,  $this->record->params->get('xmlimagesinclude', 1), 'JYES', 'JNO', 'params_xmlimagesinclude_');?>
					</fieldset>
					<small id="jform_datasource_xmlimagesinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_IMAGESINCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_IMAGES_FILTERINCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_IMAGES_FILTERINCLUDE');?></label></span>
				</td>
				<td class="paramlist_value">
					<input type="text" name="params[images_filter_include]" aria-describedby="images_filter_include_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('images_filter_include', ''), ENT_QUOTES, 'UTF-8');?>" size="100"/>
					<small id="images_filter_include_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_IMAGES_FILTERINCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_IMAGES_FILTEREXCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_IMAGES_FILTEREXCLUDE');?></label></span>
				</td>
				<td class="paramlist_value">
					<input type="text" name="params[images_filter_exclude]" aria-describedby="images_filter_exclude_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('images_filter_exclude', 'pdf,print,email,templates'), ENT_QUOTES, 'UTF-8');?>" size="100"/>
					<small id="images_filter_exclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_IMAGES_FILTEREXCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsimagetitle_processor-lbl" for="paramsimagetitle_processor" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_IMAGETITLE_PROCESSOR_DESC');?>"><?php echo Text::_('COM_JMAP_IMAGETITLE_PROCESSOR');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_imagetitle_processor" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_imagetitle_processor_arialbl">
						<?php 
						$arr = array(
								HTMLHelper::_('select.option',  '', Text::_( 'JGLOBAL_USE_GLOBAL' ) ),
								HTMLHelper::_('select.option',  'title|alt', Text::_( 'COM_JMAP_TITLE_ALT' ) ), 
								HTMLHelper::_('select.option',  'title', Text::_( 'COM_JMAP_ALWAYS_TITLE' ) ),
								HTMLHelper::_('select.option',  'alt', Text::_( 'COM_JMAP_ALWAYS_ALT' ) )
						);
						echo HTMLHelper::_ ( 'select.genericlist', $arr, 'params[imagetitle_processor]', '', 'value', 'text', $this->record->params->get('imagetitle_processor', ''), 'params_imagetitle_processor_');
						?>
					</fieldset>
					<small id="jform_datasource_imagetitle_processor_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_IMAGETITLE_PROCESSOR_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsxmlvideosinclude-lbl" for="paramsxmlvideosinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOSINCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOSINCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_xmlvideosinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_xmlvideosinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[xmlvideosinclude]', null,  $this->record->params->get('xmlvideosinclude', 1), 'JYES', 'JNO', 'params_xmlvideosinclude_');?>
					</fieldset>
					<small id="jform_datasource_xmlvideosinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOSINCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOS_FILTERINCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOS_FILTERINCLUDE');?></label></span>
				</td>
				<td class="paramlist_value">
					<input type="text" name="params[videos_filter_include]" aria-describedby="videos_filter_include_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('videos_filter_include', ''), ENT_QUOTES, 'UTF-8');?>" size="100"/>
					<small id="videos_filter_include_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOS_FILTERINCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title">
					<span class="editlinktip"><label id="paramstitle-lbl" for="paramstitle" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOS_FILTEREXCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOS_FILTEREXCLUDE');?></label></span>
				</td>
				<td class="paramlist_value">
					<input type="text" name="params[videos_filter_exclude]" aria-describedby="videos_filter_exclude_arialbl" value="<?php echo htmlspecialchars($this->record->params->get('videos_filter_exclude', ''), ENT_QUOTES, 'UTF-8');?>" size="100"/>
					<small id="videos_filter_exclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_VIDEOS_FILTEREXCLUDE_DESC')?></small>
				</td>
			</tr>
			<?php if($this->supportedGNewsExtension):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsgnewsinclude-lbl" for="paramsgnewsinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_GNEWS_INCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_GNEWS_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_gnewsinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_gnewsinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[gnewsinclude]', null,  $this->record->params->get('gnewsinclude', 1), 'JYES', 'JNO', 'params_gnewsinclude_');?>
					</fieldset>
					<small id="jform_datasource_gnewsinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_GNEWS_INCLUDE_DESC')?></small>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsgnews_genres-lbl" for="paramsgnews_genres" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_GNEWS_GENRES_DESC');?>"><?php echo Text::_('COM_JMAP_GNEWS_GENRES');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_gnews_genres" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_gnews_genres_arialbl">
						<?php echo $this->lists['gnews_genres']; ?>
					</fieldset>
					<small id="jform_datasource_gnews_genres_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_GNEWS_GENRES_DESC')?></small>
				</td>
			</tr>
			<?php endif;?>
			
			<?php if($this->supportedHreflangExtension):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramshreflanginclude-lbl" for="paramshreflanginclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_HREFLANG_INCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_HREFLANG_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_hreflanginclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_hreflanginclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[hreflanginclude]', null,  $this->record->params->get('hreflanginclude', 1), 'JYES', 'JNO', 'params_hreflanginclude_');?>
					</fieldset>
					<small id="jform_datasource_hreflanginclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_HREFLANG_INCLUDE_DESC')?></small>
				</td>
			</tr>
			<?php endif;?>
			
			<?php if(in_array($this->record->type, array('content', 'user', 'plugin', 'links'))):?>
			<tr>
				<td class="paramlist_key left_title"><span class="editlinktip"><label id="paramsxmlampinclude-lbl" for="paramsxmlampinclude" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_ELEMENTS_AMP_INCLUDE_DESC');?>"><?php echo Text::_('COM_JMAP_ELEMENTS_AMP_INCLUDE');?></label></span></td>
				<td class="paramlist_value">
					<fieldset id="jform_datasource_xmlampinclude" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_datasource_xmlampinclude_arialbl">
						<?php echo JMapHelpersHtml::booleanlist( 'params[xmlampinclude]', null,  $this->record->params->get('xmlampinclude', 0), 'JYES', 'JNO', 'params_xmlampinclude_');?>
					</fieldset>
					<small id="jform_datasource_xmlampinclude_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_ELEMENTS_AMP_INCLUDE_DESC')?></small>
				</td>
			</tr>
			<?php endif;?>
		</table>
	</div>
</div>