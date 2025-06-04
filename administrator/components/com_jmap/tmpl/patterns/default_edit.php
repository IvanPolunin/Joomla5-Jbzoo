<?php 
/** 
 * @package JMAP::PATTERNS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage patterns
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<div id="accordion_replacements_details" class="card card-info accordion-group">
		<div class="card-header accordion-toggle accordion_lightblue noaccordion" data-bs-target="#replacements_details"><h4><?php echo Text::_('COM_JMAP_PATTERNS_LINK_PATTERN_DETAILS' ); ?></h4></div>
		<div id="replacements_details" class="card-body card-block">
			<table class="admintable admintable-pattern">
			<tbody>
				<tr>
					<td class="key left_title_pattern">
						<label for="original_text" data-bs-content="<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_ORIGINAL_LINK_DESC' ); ?>" class="hasPopover">
							<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_ORIGINAL_LINK' ); ?>:
						</label>
					</td>
					<td class="center_td">
						<input class="inputbox inputbox-regex" type="text" name="original_text" id="original_text" data-validation="required" value="<?php echo $this->record->original_text;?>"/>
					</td>
					<td>
						<div class="example">
							<div class="jmap-sample">
								<div class="jmap-sample-heading"><b><?php echo Text::_('COM_JMAP_PATTERNS_LINK_PATTERN_EXAMPLE' ); ?>:</b></div>
								<div class="jmap-sample-text">Lorem Ipsum is {IDENTIFIER1} {IDENTIFIER2} text of the printing and typesetting industry.</div>
							</div>	
						</div>
					</td>
				</tr> 
				
				<tr>
					<td class="key left_title_pattern">
						<label for="target_text" data-bs-content="<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_TARGET_LINK_DESC' ); ?>" class="hasPopover">
							<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_TARGET_LINK' ); ?>:
						</label>
					</td>
					<td class="center_td">
						<input class="inputbox inputbox-regex" type="text" name="target_text" id="target_text" data-validation="required" value="<?php echo $this->record->target_text;?>"/>
					</td>
					<td>
						<div class="example">
							<div class="jmap-sample">
								<div class="jmap-sample-heading"><b><?php echo Text::_('COM_JMAP_PATTERNS_LINK_PATTERN_EXAMPLE' ); ?>:</b></div>
								<div class="jmap-sample-text">This {IDENTIFIER1} text is a {IDENTIFIER2} one used as a random text.</div>
							</div>	
						</div>
					</td>
				</tr> 
				
				<tr>
					<td class="key left_title_pattern">
						<label>
							<?php echo Text::_('COM_JMAP_PUBLISHED' ); ?>:
						</label>
					</td>
					<td class="center_td" colspan="100%">
						<fieldset class="radio btn-group" data-bs-toggle="buttons">
							<?php echo $this->lists['published']; ?>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<td class="key left_title_pattern">
						<label data-bs-content="<?php echo Text::_('COM_JMAP_PATTERNS_LABEL_DESC' ); ?>" class="hasPopover">
							<?php echo Text::_('COM_JMAP_PATTERNS_LABEL' ); ?>:
						</label>
					</td>
					<td class="center_td center_td_regex" colspan="100%">
						<p><?php echo Text::_('COM_JMAP_PATTERNS_IDENTIFIER'); ?></p>
						<p><?php echo Text::_('COM_JMAP_PATTERNS_ALPHA'); ?></p>
						<p><?php echo Text::_('COM_JMAP_PATTERNS_NUMBER'); ?></p>
						<p><?php echo Text::_('COM_JMAP_PATTERNS_TEXT'); ?></p>
						<p><?php echo Text::_('COM_JMAP_PATTERNS_SIMPLETEXT'); ?></p>
						<p><?php echo Text::_('COM_JMAP_PATTERNS_HINT'); ?></p>
					</td>
				</tr>
				
			</tbody>
			</table>
		</div>
	</div>
	
	<div id="accordion_replacements_regex" class="card card-info accordion-group">
		<div class="card-header accordion-toggle accordion_lightblue noaccordion" data-bs-target="#replacements_regex"><h4><?php echo Text::_('COM_JMAP_PATTERNS_LINK_REGEX_DETAILS' ); ?></h4></div>
		<div id="replacements_regex" class="card-body card-block">
			<table class="admintable admintable-pattern">
			<tbody>
				<tr>
					<td class="key left_title_pattern">
						<label for="original_text_regex" data-bs-content="<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_ORIGINAL_LINK_REGEX_DESC' ); ?>" class="hasPopover">
							<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_ORIGINAL_LINK_REGEX' ); ?>:
						</label>
					</td>
					<td class="center_td">
						<input class="inputbox inputbox-regex" type="text" name="original_text_regex" id="original_text_regex" value="<?php echo $this->record->original_text_regex;?>"/>
					</td>
					<td>
						<div class="example">
							<div class="jmap-sample">
								<div class="jmap-sample-heading"><b><?php echo Text::_('COM_JMAP_PATTERNS_LINK_PATTERN_EXAMPLE' ); ?>:</b></div>
								<div class="jmap-sample-text">Lorem Ipsum is ([a-zA-Z0-9].?) ([a-zA-Z0-9].?) text of the printing and typesetting industry.</div>
							</div>	
						</div>
					</td>
				</tr> 
				
				<tr>
					<td class="key left_title_pattern">
						<label for="target_text_regex" data-bs-content="<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_TARGET_LINK_REGEX_DESC' ); ?>" class="hasPopover">
							<?php echo Text::_('COM_JMAP_PATTERNS_REPLACEMENT_TARGET_LINK_REGEX' ); ?>:
						</label>
					</td>
					<td class="center_td">
						<input class="inputbox inputbox-regex" type="text" name="target_text_regex" id="target_text_regex" value="<?php echo $this->record->target_text_regex;?>"/>
					</td>
					<td>
						<div class="example">
							<div class="jmap-sample">
								<div class="jmap-sample-heading"><b><?php echo Text::_('COM_JMAP_PATTERNS_LINK_PATTERN_EXAMPLE' ); ?>:</b></div>
								<div class="jmap-sample-text">This ${1} text is a ${2} one used as a random text.</span></div>
							</div>	
						</div>
					</td>
				</tr> 
			</tbody>
			</table>
		</div>
	</div>
	
	<div class="clr"></div>
 
	<input type="hidden" name="option" value="<?php echo $this->option;?>" /> 
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="autogenerate" value="0" />
	<input type="hidden" name="task" value="" /> 
</form>