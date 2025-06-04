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
<div id="accordion_datasource_details" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_details"><h4><?php echo Text::_('COM_JMAP_DETAILS' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_details">
		<table class="admintable">
		<tbody>
			<tr>
				<td class="key left_title">
					<label for="title">
						<?php echo Text::_('COM_JMAP_NAME' ); ?>:
					</label>
				</td>
				<td>
					<?php $readOnly = in_array($this->record->type, array('menu', 'plugin')) ? 'readonly="readonly"' : '';?>
					<input class="inputbox" type="text" <?php echo $readOnly;?> name="name" id="name" data-validation="required" aria-required="true" size="50" value="<?php echo $this->record->name;?>" />
				</td>
			</tr>
			<tr>
				<td class="key left_title">
					<label for="type">
						<?php echo Text::_('COM_JMAP_TYPE' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" readonly="readonly" name="type" id="type" size="50" value="<?php echo $this->record->type;?>" />
				</td>
			</tr> 
			<tr>
				<td class="key left_title">
					<label for="description">
						<?php echo Text::_('COM_JMAP_DESCRIPTION' ); ?>:
					</label>
				</td>
				<td>
					<?php $readOnly = in_array($this->record->type, array('plugin')) && !$this->record->id ? 'readonly="readonly"' : '';?>
					<textarea class="inputbox" <?php echo $readOnly;?> name="description" id="description" rows="5" cols="80" ><?php echo $this->record->description;?></textarea>
				</td>
			</tr> 
			<tr>
				<td class="key left_title">
					<label for="description">
						<?php echo Text::_('COM_JMAP_PUBLISHED' ); ?>:
					</label>
				</td>
				<td>
					<fieldset class="btn-group radio" data-bs-toggle="buttons">
						<?php echo $this->lists['published']; ?>
					</fieldset>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
</div>