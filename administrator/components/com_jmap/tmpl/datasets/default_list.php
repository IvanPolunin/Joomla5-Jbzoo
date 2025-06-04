<?php 
/** 
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage datasets
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td align="left">
				<span class="input-group">
				  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER');?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER' ); ?>:</span>
				  <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-sm" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<label class="visually-hidden" for="limit"><?php echo Text::_('JGLOBAL_LIST_LIMIT');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th width="1%">
				<?php echo Text::_('COM_JMAP_NUM' ); ?>
			</th>
			<th width="1%">
				<input type="checkbox" name="checkall-toggle" value="" class="form-check-input" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" width="20%">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_DATASET_NAME', 's.name', @$this->orders['order_Dir'], @$this->orders['order'], 'datasets.display' ); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_DATASET_DESC'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_DATASET_DATASOURCES'); ?>
			</th>
			<th style="width:5%">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'datasets.display' ); ?>
			</th>
			<th width="1%">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'datasets.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=datasets.editEntity&cid[]='. $row->id ;
		
		// Access check.
		if($this->user->authorise('core.edit.state', 'com_jmap')) {
			$taskPublishing	= !$row->published ? 'datasets.publish' : 'datasets.unpublish';
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
		
		$checked = null;
		// Access check.
		if($this->user->authorise('core.edit', 'com_jmap')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ?
						HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'datasets.', $canCheckin) . '<input type="checkbox" style="display:none" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>' :
						HTMLHelper::_('grid.id', $i, $row->id);
		} else {
			$checked = '<input type="checkbox" style="display:none" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
		}
		?>
		<tr>
			<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if ( ($row->checked_out && ( $row->checked_out != $this->user->id)) || !$this->user->authorise('core.edit', 'com_jmap') ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_EDIT_DATASET' ); ?>">
						<span class="fas fa-pen-square" aria-hidden="true"></span>
						<?php echo $row->name; ?>
					</a>
					<?php
				}
				?>
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo $row->description; ?>
			</td>
			<td class="d-none d-md-table-cell">
				<?php
					$wrappedNames = array_map(
						function ($el) {
							return "<label class='badge bg-primary label-sources'>$el</label>";
						},
						$row->sourcesNames
					);
					echo implode('', $wrappedNames);
				?>
			</td>
			<td align="center">
				<?php echo $published;?>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
	}
	?>
	<tfoot>
		<td colspan="13">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="datasets.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>