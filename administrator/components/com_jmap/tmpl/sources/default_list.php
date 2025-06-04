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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Factory;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;

// Ordering drag'n'drop management
$saveOrderingUrl = null;
if ($this->orders['order'] == 's.ordering') {
	$saveOrderingUrl = 'index.php?option=com_jmap&task=sources.saveOrder&format=json&ajax=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td align="left">
				<span class="input-group">
				  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER' ); ?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER' ); ?>:</span>
				  <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-sm" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td>
				<?php if($this->languagePluginEnabled):?>
					<label class="visually-hidden" for="filter_type"><?php echo Text::_('COM_JMAP_LANGUAGES');?></label>
					<?php echo $this->lists['languages'];?>
				<?php endif;?>
				<label class="visually-hidden" for="filter_state"><?php echo Text::_('JLIB_HTML_SELECT_STATE');?></label>
				<?php
					echo $this->lists['state'];
				?>
				<label class="visually-hidden" for="filter_type"><?php echo Text::_('COM_JMAP_ALL_DATASOURCE');?></label>
				<?php 
					echo $this->lists['type'];
				?>
				<label class="visually-hidden" for="limit"><?php echo Text::_('JGLOBAL_LIST_LIMIT');?></label>
				<?php
					echo $this->pagination->getLimitBox();
				?>
			</td>
		</tr>
	</table>

	<table id="adminList" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo Text::_('COM_JMAP_NUM' ); ?>
			</th>
			<th style="width:1%">
				<input type="checkbox" name="checkall-toggle" value="" class="form-check-input" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_NAME', 's.name', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display'); ?>
			</th>
			<th class="title">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_TYPE', 's.type', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_DESCRIPTION'); ?>
			</th>
			<th class="order d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_ORDER', 's.ordering', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display'); ?>
				<?php 
					if(isset($this->orders['order']) && $this->orders['order'] == 's.ordering'):
						echo JMapHelpersHtml::order($this->items, 'filesave.png', 'sources.saveOrder'); 
					endif;
				 ?>
			</th>
			<th style="width:5%">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display' ); ?>
			</th>
			<th style="width:5%" class="d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'sources.display' ); ?>
			</th>
		</tr>
	</thead>
	<tbody <?php if ($saveOrderingUrl) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->orders['order_Dir']); ?>" <?php endif; ?>>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=sources.editEntity&cid[]='. $row->id ;

		if($this->user->authorise('core.edit.state', 'com_jmap')) {
			$taskPublishing	= !$row->published ? 'sources.publish' : 'sources.unpublish';
			$altPublishing 	= !$row->published ? Text::_( 'Publish' ) : Text::_( 'Unpublish' );
			$published = '<a href="javascript:void(0);" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $taskPublishing . '\')">';
			$published .= $row->published ? 
						 '<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16"/>' : 
						 '<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/publish_x.png" width="16" height="16"/>';
			$published .= '</a>';
		} else {
			$altPublishing 	= $row->published ? Text::_( 'Published' ) : Text::_( 'Unpublished' );
			$published = $row->published ? 
						 '<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16"/>' : 
						 '<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/publish_x.png" width="16" height="16"/>';
		}
		
		// Check for the links type data source language
		if($row->type == 'links' || $row->type == 'user' || $row->type == 'plugin') {
			$dataSourceParams = json_decode($row->params);
			if($this->languagePluginEnabled && isset($dataSourceParams->datasource_language) && $dataSourceParams->datasource_language != '*') {
				$published .= '<img style="margin-left:10px" src="' . Uri::root(false) . 'media/mod_languages/images/' . StringHelper::str_ireplace('-', '_', $dataSourceParams->datasource_language) . '.gif" alt="language_flag" />';
			}
		}
		
		$checked = null;
		if($row->type == 'user' || $row->type == 'plugin' || $row->type == 'links' || ($row->type == 'content' && $this->cParams->get('multiple_content_sources', 0))) {
			// Access check.
			if($this->user->authorise('core.edit', 'com_jmap')) {
				$checked = $row->checked_out && $row->checked_out != $this->user->id ? 
							HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'sources.', $canCheckin) . '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>' : 
							HTMLHelper::_('grid.id', $i, $row->id);
			} else {
				$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
			}
		} else {
			if($row->checked_out && $row->checked_out != $this->user->id) {
				$checked = HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'sources.', $canCheckin) . '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
			} else {
				$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
			}
		}
		?>
			<tr>
				<td style="width:1%" align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td style="width:1%" align="center">
					<?php echo $checked; ?>
				</td>
				<td class="datasource-name">
					<?php
					if ( ($row->checked_out && ( $row->checked_out != $this->user->id)) || !$this->user->authorise('core.edit', 'com_jmap') ) {
						echo $row->name;
					} else {
						?>
						<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_EDIT_SOURCE' ); ?>">
							<span class="fas fa-pen-square" aria-hidden="true"></span>
							<?php echo $row->name; ?>
						</a>
						<?php
					}
					?>
				</td>
				<td class="datasource-type">
					<?php echo $row->type; ?>
				</td>
				<td class="d-none d-md-table-cell datasource-description">
					<?php echo $row->description; ?>
				</td>
				
				<td class="order d-none d-md-table-cell sortable-adminlist-small">
					<?php 
					$ordering = $this->orders['order'] == 's.ordering'; 
					$disabled = $ordering ?  '' : 'disabled="disabled"'; 
					
					$iconClass = '';
					if (!$this->user->authorise('core.edit', 'com_jmap')) {
						$iconClass = ' inactive';
					}
					elseif (!$ordering) {
						$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
					}
					?>
					<div style="display:inline-block" class="sortable-handler<?php echo $iconClass ?>">
						<span class="icon-menu" aria-hidden="true"></span>
					</div>
					
					<span class="moveup" aria-hidden="true"><?php echo $this->pagination->orderUpIcon( $i, true, 'sources.moveorder_up', 'COM_JMAP_MOVE_UP', $ordering); ?></span>
					<span class="movedown" aria-hidden="true"><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'sources.moveorder_down', 'COM_JMAP_MOVE_DOWN', $ordering); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"  <?php echo $disabled; ?>  class="ordering_input" style="text-align: center" />
				</td>
						
				<td style="width:5%" align="center">
					<?php echo $published;?>
				</td>
				<td style="width:5%" class="d-none d-md-table-cell">
					<?php echo $row->id; ?>
				</td>
			</tr>
		<?php
	}
	?>
	</tbody>
	<tfoot>
		<td colspan="13">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="sources.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>