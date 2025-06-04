<?php 
/** 
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
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
				
				<div class="clr vspacer"></div>
				
				<span class="input-group double active">
				  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER_BY_DATE_FROM' ); ?>"><span class="fas fa-th" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER_BY_DATE_FROM' ); ?>:</span>
				  <input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['from'];?>" class="text_area"/>
				</span>
				<span class="input-group double active">
				  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER_BY_DATE_TO' ); ?>"><span class="fas fa-th" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER_BY_DATE_TO' ); ?>:</span>
				  <input type="text" name="toperiod" id="toPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['to'];?>" class="text_area"/>
				</span>
				<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-sm" onclick="document.getElementById('fromPeriod').value='';document.getElementById('toPeriod').value='';this.form.submit();"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
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
			<th class="title">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_LINKTITLE', 's.title', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th class="title">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_LINKURL', 's.blogurl', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_LINKRSS', 's.rssurl', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_LASTPING', 's.lastping', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
			<th width="1%">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'pingomatic.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=pingomatic.editEntity&cid[]='. $row->id ;
		
		$checked = null;
		if($this->user->authorise('core.edit', 'com_jmap')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ?
						HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'pingomatic.', $canCheckin) . '<input type="checkbox" style="display:none" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>' :
						HTMLHelper::_('grid.id', $i, $row->id);
		} else {
			$checked = null;
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
					echo $row->title;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_EDIT_LINK' ); ?>">
						<span class="fas fa-pen-square" aria-hidden="true"></span>
						<?php echo $row->title; ?>
					</a>
					<?php
				}
				?>
			</td>
			<td>
				<?php echo $row->blogurl; ?>
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo $row->rssurl; ?>
			</td>
			 
			<td class="d-none d-md-table-cell">
				<?php echo $row->lastping ? HTMLHelper::_('date', $row->lastping, Text::_('DATE_FORMAT_LC2')) : Text::_('COM_JMAP_NEVER_PING'); ?>
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
	<input type="hidden" name="task" value="pingomatic.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>