<?php 
/** 
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage analyzer
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td id="alert_append" align="left">
				<span class="input-group">
				  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER_ONPAGE' ); ?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER_ONPAGE' ); ?>:</span>
				  <input type="text" name="searchpage" id="searchpage" value="<?php echo htmlspecialchars($this->searchpageword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-sm" onclick="document.getElementById('searchpage').value='';this.form.submit();"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td>
				<label class="visually-hidden" for="limit"><?php echo Text::_('JGLOBAL_LIST_LIMIT');?></label>
				<?php
					echo $this->getLimitBox();
				?>
			</td>
		</tr>
		<tr>
			<td colspan="100%">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</table>

	<table class="adminlist seospiderlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo Text::_('COM_JMAP_NUM' ); ?>
			</th>
			<th style="width:15%" class="title">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JMAP_SEOSPIDER_CRAWLED_LINK', 'link', @$this->orders['order_Dir'], @$this->orders['order'], 'seospider.display'); ?>
			</th>
			<th style="width:2%" class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_STATUS'); ?>
			</th>
			<th style="width:10%" class="title">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_LINK_TITLE'); ?>
			</th>
			<th style="width:15%" class="title">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_LINK_DESC'); ?>
			</th>
			<th style="width:15%" class="title d-none d-lg-table-cell">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_H1'); ?>
			</th>
			<th style="width:15%" class="title d-none d-lg-table-cell">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_H2'); ?>
			</th>
			<th style="width:15%" class="title d-none d-lg-table-cell">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_H3'); ?>
			</th>
			<th style="width:10%" class="title d-none d-lg-table-cell">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_CANONICAL'); ?>
			</th>
			<th style="width:1%" class="title">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_TITLE_DUPLICATES'); ?>
			</th>
			<th style="width:1%" class="title">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_DESC_DUPLICATES'); ?>
			</th>
			<th style="width:1%" class="title">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS'); ?>
			</th>
			<?php if($this->limitValue <= 100 && $this->limitValue != 0):?>
			<th style="width:1%" class="title d-none d-lg-table-cell">
				<?php echo Text::_('COM_JMAP_SEOSPIDER_PAGELOAD'); ?>
			</th>
			<?php endif;?>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	foreach ( $this->items as $row ) {
		// Evaluate filtering by search page word
		if($this->searchpageword) {
			$isMatching = (stripos($row->loc, $this->searchpageword) !== false);
			if(!$isMatching) {
				continue;
			}
		}
		// Manage waiter for indexing status JS app
		$indexingWaiter = '<img class="hasTooltip" title="' . Text::_('COM_JMAP_SEOSPIDER_INDEXING_WAITER') . '" alt="' . Text::_('COM_JMAP_SEOSPIDER_INDEXING_WAITER') . '" src="' . Uri::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16"/>';
		?>
		<tr data-link="<?php echo $row->loc; ?>">
			<td align="center">
				<?php echo $k + 1; ?>
			</td>
			<td>
				<a data-role="link" href="<?php echo $row->loc; ?>" alt="sitelink" target="_blank">
					<?php echo $row->loc; ?>
					<span class="fas fa-share" aria-hidden="true"></span>
				</a>
			</td>
			<td class="d-none d-md-table-cell">
				<div data-bind="{status}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div data-bind="{title}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div data-bind="{desc}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="d-none d-lg-table-cell">
				<div data-bind="{h1}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="d-none d-lg-table-cell">
				<div data-bind="{h2}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="d-none d-lg-table-cell">
				<div data-bind="{h3}"><?php echo $indexingWaiter;?></div>
			</td>
			<td class="d-none d-lg-table-cell">
				<div data-bind="{canonical}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div class="trigger_dialog" data-bind="{title-duplicates}"><?php echo $indexingWaiter;?></div>
			</td>
			<td>
				<div class="trigger_dialog" data-bind="{desc-duplicates}"><?php echo $indexingWaiter;?></div>
			</td>
			<td align="center">
				<div class="trigger_content_analysis hasLeftTooltip" title="<?php echo Text::_('COM_JMAP_SEOSPIDER_CONTENT_ANALYSIS_TITLE');?>" data-link="<?php echo $row->loc;?>">
					<div class="trigger_content_analysis_red"></div>
					<div class="trigger_content_analysis_yellow"></div>
					<div class="trigger_content_analysis_green"></div>
				</div>
			</td>
			<?php if($this->limitValue <= 100 && $this->limitValue != 0):?>
			<td class="d-none d-lg-table-cell">
				<div data-bind="{pageload}"><?php echo $indexingWaiter;?></div>
			</td>
			<?php endif;?>
		</tr>
		<?php
		$k++;
	}
	// No links showed
	if($k == 0) {
		$this->app->enqueueMessage ( Text::_('COM_JMAP_SEOSPIDER_NOLINKS_ONTHISPAGE') );
	}
	?>
	</tbody>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="seospider.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>