<?php 
/** 
 * @package JMAP::LINKS::administrator::components::com_jmap
 * @subpackage views
 * @subpackage links
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td class="left">
				<span class="input-group">
					<span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER');?>"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER' ); ?>:</span>
					<input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>
				
				<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-sm" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td>
				<label class="visually-hidden" for="filter_state"><?php echo Text::_('JLIB_HTML_SELECT_STATE');?></label>
				<?php
					echo $this->lists['state'];
				?>
				<label class="visually-hidden" for="limit"><?php echo Text::_('JGLOBAL_LIST_LIMIT');?></label>
				<?php
					echo $this->pagination->getLimitBox();
				?>
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
				<input type="checkbox" name="toggle" value="" class="form-check-input" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" width="15%">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_PATTERNS_REPLACEMENT_ORIGINAL_LINK', 's.original_text', @$this->orders['order_Dir'], @$this->orders['order'], 'patterns.display' ); ?>
			</th>
			<th class="title" width="15%">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_PATTERNS_REPLACEMENT_TARGET_LINK', 's.target_text', @$this->orders['order_Dir'], @$this->orders['order'], 'patterns.display' ); ?>
			</th>
			<th style="width:5%">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'patterns.display' ); ?>
			</th>
			<th class="title" width="2%">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'patterns.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=patterns.editEntity&cid[]='. $row->id ;
		$altPublishing 	= !$row->published ? Text::_( 'Publish' ) : Text::_( 'Unpublish' );
		
		// Access check.
		if($this->user->authorise('core.edit.state')) {
			$taskPublishing	= !$row->published ? 'patterns.publish' : 'patterns.unpublish';
			
			$published = '<a href="javascript:void(0);" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $taskPublishing . '\')">';
			$published .= $row->published ? 
						  '<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16"/>' : 
						  '<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/publish_x.png" width="16" height="16"/>';
			$published .= '</a>';
		} else {
			$altPublishing 	= $row->published ? Text::_( 'Published' ) : Text::_( 'Unpublished' );
			$published = $row->published ? 
						'<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : 
						'<img alt="' . $altPublishing . '" src="' . Uri::base(true) . '/components/com_jmap/images/publish_x.png" width="16" height="16"/>';
		}
		
		$checked = null;
		// Access check.
		if($this->user->authorise('core.edit')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ? 
						HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'patterns.', $canCheckin) . '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>': 
						HTMLHelper::_('grid.id', $i, $row->id);
		} else {
			$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
		}
		?>
		<tr>
			<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if ( ($row->checked_out && ( $row->checked_out != $this->user->id)) || !$this->user->authorise('core.edit') ) {
					echo $row->original_text;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_EDIT_LINK' ); ?>">
						<span class='fas fa-pen-square' aria-hidden='true'></span> 
						<?php echo htmlspecialchars($row->original_text, ENT_COMPAT, 'UTF-8', false); ?>
					</a>
					<?php
				}?>
			</td>
			<td>
				<?php
				if ( ($row->checked_out && ( $row->checked_out != $this->user->id)) || !$this->user->authorise('core.edit') ) {
					echo $row->target_text;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_EDIT_LINK' ); ?>">
						<span class='fas fa-pen-square' aria-hidden='true'></span> 
						<?php echo htmlspecialchars($row->target_text, ENT_COMPAT, 'UTF-8', false); ?>
					</a>
					<?php
				}?>
			</td>
			<td>
				<?php echo $published;?>
			</td>
			<td>
				<?php echo $row->id;?>
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
	<input type="hidden" name="task" value="patterns.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>