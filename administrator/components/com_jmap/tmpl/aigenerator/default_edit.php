<?php 
/** 
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap
 * @subpackage views
 * @subpackage aigenerator
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;
?>
<div id="accordion_aigenerator_details" class="card card-info  adminform">
	<div class="card-header accordion-toggle accordion_lightblue noaccordion" data-bs-target="#aigenerator_details"><h4><?php echo Text::_('COM_JMAP_AIGENERATOR_CONTENT_DETAILS' ); ?></h4></div>
	<div class="card-body card-block" id="aigenerator_details">
		<form action="index.php" method="post" name="adminForm" id="adminForm"> 
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<span class="editlinktip">
								<label for="keywords_phrase" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AIGENERATOR_KEYWORDS_PHRASE_DETAILS');?>">
									<?php echo Text::_('COM_JMAP_AIGENERATOR_KEYWORDS_PHRASE' ); ?>:
								</label>
							</span>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox inputbox-large-fluid" name="keywords_phrase" id="keywords_phrase" data-validation="required" aria-required="true" value="<?php echo $this->record->keywords_phrase;?>" />
							<small id="jform_keywords_phrase_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_AIGENERATOR_KEYWORDS_PHRASE_DETAILS')?></small>
						</td>
					</tr>
					
					<tr>
						<td class="paramlist_key left_title">
							<span class="editlinktip">
								<label for="api" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AIGENERATOR_API_DETAILS');?>">
									<?php echo Text::_('COM_JMAP_AIGENERATOR_API');?>
								</label>
							</span>
						</td>
						<td class="paramlist_value">
							<fieldset id="jform_api" class="btn-group radio ai-api-fieldset" data-bs-toggle="buttons" aria-describedby="jform_api_arialbl">
								<?php 
									echo $this->lists ['ai_generator_contents_api']
								?>
							</fieldset>
							<small id="jform_api_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_AIGENERATOR_API_DETAILS')?></small>
						</td>
					</tr>
					
					<tr>
						<td class="paramlist_key left_title">
							<span class="editlinktip">
								<label for="maxresults" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SELECT_MAX_AI_RESULTS_DETAILS');?>">
									<?php echo Text::_('COM_JMAP_SELECT_MAX_AI_RESULTS');?>
								</label>
							</span>
						</td>
						<td class="paramlist_value">
							<fieldset id="jform_max_results" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_maxresults_arialbl">
								<?php 
									echo $this->lists ['ai_generator_max_results']
								?>
							</fieldset>
							<small id="jform_maxresults_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SELECT_MAX_AI_RESULTS_DETAILS')?></small>
						</td>
					</tr>
					
					<?php if(array_key_exists('languages', $this->lists)):?>
						<tr>
							<td class="paramlist_key left_title">
								<span class="editlinktip">
									<label for="languages" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SELECT_LANGUAGE_AI_DETAILS');?>">
										<?php echo Text::_('COM_JMAP_SELECT_LANGUAGE_AI');?>
									</label>
								</span>
							</td>
							<td class="paramlist_value">
								<fieldset id="jform_languages" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_language_arialbl">
									<?php 
										echo $this->lists ['languages'];
									?>
								</fieldset>
								<small id="jform_language_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SELECT_LANGUAGE_AI_DETAILS')?></small>
							</td>
						</tr>
					<?php else:?>
						<tr>
							<td class="paramlist_key left_title">
								<span class="editlinktip">
									<label class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_SELECT_LANGUAGE_AI_DETAILS');?>">
										<?php echo Text::_('COM_JMAP_SELECT_LANGUAGE_AI');?>
									</label>
								</span>
							</td>
							<td class="paramlist_value">
								<?php echo '<img id="language_flag_image" src="' . Uri::root(false) . 'media/mod_languages/images/' . StringHelper::strtolower(StringHelper::str_ireplace('-', '_', $this->defaultLanguageCode)) . '.gif" alt="language_flag" />';?>
								<input type="hidden" name="language" value="<?php echo $this->defaultSefLanguageCode;?>"/>
								<small id="jform_language_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_SELECT_LANGUAGE_AI_DETAILS')?></small>
							</td>
						</tr>
					<?php endif;?>
					
					<tr>
						<td class="paramlist_key left_title">
							<span class="editlinktip">
								<label id="removeimgs-lbl" for="jform_removeimgs" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AI_REMOVE_IMAGES_DETAILS');?>">
									<?php echo Text::_('COM_JMAP_AI_REMOVE_IMAGES');?>
								</label>
							</span>
						</td>
						<td class="paramlist_value">
							<fieldset id="jform_removeimgs" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_removeimgs_arialbl">
								<?php 
									$arr = array(
										HTMLHelper::_('select.option',  1, Text::_('JENABLED' ) ),
										HTMLHelper::_('select.option',  0, Text::_('JDISABLED' ) )
									);
									echo JMapHelpersHtml::radiolist( $arr, 'removeimgs', '', 'value', 'text', $this->record->removeimgs, 'removeimgs_');
								?>
							</fieldset>
							<small id="jform_removeimgs_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_AI_REMOVE_IMAGES_DETAILS')?></small>
						</td>
					</tr>
					
					<tr class="openai-params">
						<td class="paramlist_key left_title">
							<span class="editlinktip">
								<label id="removeimgs-lbl" for="jform_removeimgs" class="hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AI_TEMPERATURE_DETAILS');?>">
									<?php echo Text::_('COM_JMAP_AI_TEMPERATURE');?>
								</label>
							</span>
						</td>
						
						<td class="paramlist_value">
							<fieldset id="jform_temperature" class="btn-group radio" data-bs-toggle="buttons" aria-describedby="jform_temperature_arialbl">
								<?php 
									echo $this->lists ['temperature'];
								?>
							</fieldset>
							<small id="jform_temperature_arialbl" class="form-text text-muted"><?php echo Text::_('COM_JMAP_AI_TEMPERATURE_DETAILS')?></small>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="option" value="<?php echo $this->option?>" />
			<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
			<input type="hidden" name="task" value="" />
		</form>
	</div>
</div>
	
<div id="accordion_aigenerator_contents_results" class="card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue noaccordion" data-bs-target="#contents_results"><h4><?php echo Text::_('COM_JMAP_AIGENERATOR_GENERATED_CONTENTS' ); ?></h4></div>
	<?php if(!$this->record->contents):?>
		<div class="card-body card-block d-block" id="contents_results">	
			<div class="px-4 py-5 my-5 text-center">
		        <span class="fa-8x mb-4 icon-list" aria-hidden="true"></span>
		        <h1 class="display-5 fw-bold"><?php echo Text::_('COM_JMAP_AIGENERATOR_NOCONTENTS')?></h1>
		        <div class="col-lg-6 mx-auto">
		            <p class="lead mb-4">
		            	<?php echo Text::_('COM_JMAP_AIGENERATOR_NOCONTENTS_DETAILS')?>   
					</p>
		            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
 						 <a id="generatebutton" class="btn btn-primary btn-lg px-4 me-sm-3 emptystate-btnadd"><?php echo Text::_('COM_JMAP_AIGENERATOR_GENERATE_CONTENTS')?></a>
					</div>
		        </div>
		    </div>
		</div>
	<?php else:?>
		<div class="card-body card-block d-flex" id="contents_results">	
			<?php foreach ($this->record->contents as $content):?>
				<div class="card text-black bg-light">
					<div class="card-header hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AIGENERATOR_COPY_CONTENT');?>">
						<span class="icon-copy"></span><h4> <?php echo $content['title']; ?></h4>
					</div>
					<div class="card-body card-block">
						<?php echo $content['content'];?>
					</div>
					<div class="card-footer">
						<button class="btn btn-primary btn-sm create-article hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AIGENERATOR_CREATE_ARTICLE_DESC');?>"><span class="icon-add"></span> <?php echo Text::_('COM_JMAP_AIGENERATOR_CREATE_ARTICLE');?></button>
						<button class="btn btn-primary btn-sm create-module hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_AIGENERATOR_CREATE_MODULE_DESC');?>"><span class="icon-add"></span> <?php echo Text::_('COM_JMAP_AIGENERATOR_CREATE_MODULE');?></button>
					</div>
				</div>
			<?php endforeach;?>
		</div>
	<?php endif;?>
</div>