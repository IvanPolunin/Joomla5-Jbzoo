<?php 
/** 
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage views
 * @subpackage config
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

$fieldSets = $this->params_form->getFieldsets();
?>
<div id="accordion_datasource_plugin_parameters" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_plugin_parameters"><h4><?php echo Text::_('COM_JMAP_PLUGIN_CONFIGURATION' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_plugin_parameters">
		<table  class="admintable">
			<?php foreach ($fieldSets as $name => $fieldSet) :  ?>
				<?php  
				foreach ($this->params_form->getFieldset($name) as $field):
					?>
					<tr>
						<td class="paramlist_key left_title"><?php echo $field->label; ?></td>
						<td class="paramlist_value"><?php echo $field->input; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</table>
	</div>
</div>


