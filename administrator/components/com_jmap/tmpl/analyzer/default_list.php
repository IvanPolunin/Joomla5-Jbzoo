<?php 
/** 
 * @package JMAP::ANALYZER::administrator::components::com_jmap
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
				
				<span class="input-group-text input-checkbox hasTooltip" title="<?php echo Text::_('COM_JMAP_LINKSANALYZER_EXACT_MATCH');?>" aria-label="<?php echo Text::_('COM_JMAP_LINKSANALYZER_EXACT_MATCH');?>">
					<input type="checkbox" id="exactsearchpage" name="exactsearchpage" value="1" <?php echo $this->exactsearchpage;?>/>
  					<label class="input-group" for="exactsearchpage"><?php echo Text::_('COM_JMAP_LINKSANALYZER_EXACT_MATCH_LABEL');?></label>
				</span>
			</td>
			<td>
				<label class="visually-hidden" for="filter_type"><?php echo Text::_('COM_JMAP_ANALYZER_ALL');?></label>
				<?php
					echo $this->lists['type'];
				?>
				<label class="visually-hidden" for="limit"><?php echo Text::_('JGLOBAL_LIST_LIMIT');?></label>
				<?php
					echo $this->getLimitBox();
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist analyzerlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo Text::_('COM_JMAP_NUM' ); ?>
			</th>
			<th class="title">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JMAP_ANALYZER_LINK', 'link', @$this->orders['order_Dir'], @$this->orders['order'], 'analyzer.display'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_ANALYZER_COMPONENT'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_ANALYZER_MENUTITLE'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_ANALYZER_MENUID'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_ANALYZER_LASTMOD'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_ANALYZER_CHANGEFREQ'); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_ANALYZER_PRIORITY'); ?>
			</th>
			<th class="title">
				<?php echo Text::_('COM_JMAP_ANALYZER_VALID'); ?>
			</th>
			<?php if($this->cparams->get('linksanalyzer_indexing_analysis', 1)):?>
				<th class="title d-none d-md-table-cell">
					<?php echo Text::_('COM_JMAP_ANALYZER_INDEXED'); ?>
				</th>
			<?php endif;?>
			<?php if($this->cparams->get('linksanalyzer_indexing_analysis', 1) && $this->cparams->get('links_analyzer_pagespeed_insights_analysis', 0)):?>
				<th class="title d-none d-md-table-cell">
					<?php echo Text::_('COM_JMAP_ANALYZER_GOOGLE_PAGESPEED_INSIGHTS'); ?>
				</th>
			<?php endif;?>
			<th class="title d-none d-sm-table-cell">
				<?php
					if($this->validationType == 2):
						echo HTMLHelper::_('grid.sort', 'COM_JMAP_ANALYZER_STATUS_CODE', 'httpstatus', @$this->orders['order_Dir'], @$this->orders['order'], 'analyzer.display');
					else:
						echo Text::_('COM_JMAP_ANALYZER_STATUS_CODE');
					endif;
				?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	foreach ( $this->items as $row ) {
		// Only valid if the validation mode of the Analyzer is the default one
		if(isset($row->httpstatus)) {
			// Apply filtering if any
			if($this->link_type && $row->httpstatus != $this->link_type) {
				continue;
			}
			
			// Manage semaphore icons
			$linkValidation = null;
			switch ((int)$row->httpstatus) {
				case (int)$row->httpstatus > 200 && (int)$row->httpstatus < 400:
					$statusAlt = Text::_('COM_JMAP_ANALYZER_LINKVALID_REDIRECT');
					$valid = '<img class="hasTooltip" title="' . $statusAlt . '" alt="' . $statusAlt . '" src="' . Uri::base(true) . '/components/com_jmap/images/publish_y.png" width="16" height="16"/>';
					$linkValidation = false;
					break;
	
				case (int)$row->httpstatus > 400;
					$statusAlt = Text::_('COM_JMAP_ANALYZER_LINK_NOVALID');
					$valid = '<img class="hasTooltip" title="' . $statusAlt . '" alt="' . $statusAlt . '" src="' . Uri::base(true) . '/components/com_jmap/images/publish_x.png" width="16" height="16"/>';
					$linkValidation = false;
				break;
	
				case (int)$row->httpstatus == 200:
				default:
					$statusAlt = Text::_('COM_JMAP_ANALYZER_LINKVALID');
					$valid = '<img class="hasTooltip" title="' . $statusAlt . '" alt="' . $statusAlt . '" src="' . Uri::base(true) . '/components/com_jmap/images/icon-16-tick.png" width="16" height="16"/>';
					$linkValidation = true;
				break;
			}
		} else {
			$valid = '<img data-role="validation_status" class="hasTooltip" title="' . Text::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" alt="' . Text::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" src="' . Uri::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16"/>';
			$linkValidation = true;
		}

		// Manage waiter for indexing status JS app
		$indexingWaiter = '<img data-role="indexing_status" class="hasTooltip" title="' . Text::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" alt="' . Text::_('COM_JMAP_ANALYZER_INDEXING_WAITER') . '" src="' . Uri::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16"/>';
		$pagespeedWaiter = '<img data-role="pagespeed_status" class="hasTooltip" title="' . Text::_('COM_JMAP_ANALYZER_PAGESPEED_WAITER') . '" alt="' . Text::_('COM_JMAP_ANALYZER_PAGESPEED_WAITER') . '" src="' . Uri::base(true) . '/components/com_jmap/images/loading.gif" width="16" height="16" border="0" />';
		?>
		<tr>
			<td align="center">
				<?php echo $k + 1; ?>
			</td>
			<td class="linkwrap">
				<a data-role="<?php echo $linkValidation ? $this->dataRole : 'neutral';?>" href="<?php echo $row->loc; ?>" alt="sitelink" target="_blank">
					<?php echo $row->loc; ?>
					<span class="fas fa-share" aria-hidden="true"></span>
				</a>
			</td>
			<td class="d-none d-md-table-cell">
				<span class="badge bg-primary"><?php echo str_replace('com_', '', $row->component); ?></span>
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo $row->menuTitle; ?>
			</td>
			<td class="d-none d-md-table-cell">
				<span class="badge bg-info"><?php echo $row->menuId; ?></span>
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo isset($row->lastmod) ? $row->lastmod : '-'; ?>
			</td>
			<td class="d-none d-md-table-cell">
				<span class="badge bg-warning"><?php echo $row->changefreq; ?></span>
			</td>
			<td class="d-none d-md-table-cell">
				<span class="badge bg-warning"><?php echo (floatval($row->priority) * 100) . '%'; ?></span>
			</td>
			<td>
				<?php echo $valid; ?>
			</td>
			<?php if($this->cparams->get('linksanalyzer_indexing_analysis', 1)):?>
				<td class="title d-none d-md-table-cell">
					<?php echo $linkValidation ? $indexingWaiter : $valid; ?>
				</td>
			<?php endif; ?>
			<?php if($this->cparams->get('linksanalyzer_indexing_analysis', 1) && $this->cparams->get('links_analyzer_pagespeed_insights_analysis', 0)):?>
				<td class="title d-none d-md-table-cell">
					<?php echo $linkValidation ? $pagespeedWaiter : $valid; ?>
				</td>
			<?php endif;?>
			<td class="d-none d-sm-table-cell">
				<span class="badge bg-primary"><?php echo isset($row->httpstatus) ? ($row->httpstatus == '200' ? $row->httpstatus : '<span class="errorcode">' . $row->httpstatus . '</span>') : '<span class="httpcode"></span>'; ?></span>
			</td>
		</tr>
		<?php
		$k++;
	}
	// No links showed
	if($k == 0) {
		$this->app->enqueueMessage ( Text::_('COM_JMAP_ANALYZER_NOLINKS_ONTHISPAGE') );
	}
	?>
	<tfoot>
		<td colspan="100%">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="analyzer.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>