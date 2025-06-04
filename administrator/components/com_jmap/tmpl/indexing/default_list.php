<?php 
/** 
 * @package JMAP::INDEXING::administrator::components::com_jmap
 * @subpackage views
 * @subpackage indexing
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="headerlist selectinput">
		<tr>
			<td>
				<span class="input-group input-expanded">
				  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_INDEXING_BYKEYWORDS');?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_INDEXING_BYKEYWORDS' ); ?>:</span>
				  <input type="text" name="search" id="search" value="<?php echo $this->searchword;?>" class="text_area serpcontrol"/>
				  <label id="keywords_suggestion" class="fas fa-question-circle fa-icon-large hasClickPopover" title="<?php echo Text::_('COM_JMAP_INDEXING_KEYWORDS_SUGGESTION');?>" data-role="keywords_suggestion"></label>
				</span>
				<img class="googlelogo d-none d-lg-inline-block" src="<?php echo Uri::base(true);?>/components/com_jmap/images/google-logo.png" alt="google"/>
			</td>
		</tr>
		<tr>
			<td>
				<span class="input-group input-expanded">
					<span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_INDEXING_LANGUAGE');?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_INDEXING_LANGUAGE' ); ?>:</span>
					<?php echo $this->lists['acceptlanguages']; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<span class="input-group input-expanded">
					<span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_INDEXING_COUNTRY');?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_INDEXING_COUNTRY' ); ?>:</span>
					<?php echo $this->lists['countriestld']; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<span class="input-group input-minexpanded">
					<span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_NUMBER_PAGES');?>"><span class="fas fa-file" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_NUMBER_PAGES' ); ?>:</span>
					<?php echo $this->lists['numpages']; ?>
				</span>
				<div class="google_indexing_tester_btns">
					<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_INDEXING_START_SEARCH' ); ?></button>
					<button class="btn btn-primary btn-sm" data-reset="serpcontrol"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
				</div>
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
			<td nowrap="nowrap">
			
			</td>
		</tr>
	</table>

	<?php foreach ($this->items as $index=>$page) :?>
		<div class="card">
			<div class="card-body card-block">
				<span class="badge bg-primary label-icon pull-left">
					<?php echo Text::sprintf('COM_JMAP_INDEXING_CURRENT_SEARCHSERP', $this->serpsearch);?>
				</span>
				<?php if($this->rankedpagekeyword):?>
					<span class="badge bg-primary label-icon pull-left">
						<?php echo Text::sprintf('COM_JMAP_INDEXING_RANKED_PAGE_KEYWORD', $this->rankedpagekeyword);?>
					</span>
				<?php endif; ?>
				<?php if(isset($this->totalPagesValue)):?>
					<span class="badge bg-primary label-icon pull-left">
						<?php echo Text::sprintf('COM_JMAP_INDEXING_TOTAL_RESULTS', $this->totalPagesValue);?>
					</span>
				<?php endif; ?>
				<span class="badge bg-primary label-icon pull-right">
					<img src="<?php echo Uri::base(true);?>/components/com_jmap/images/icon-24-google.png" alt="google"/>
					<?php echo Text::sprintf('COM_JMAP_INDEXING_GOOGLEPAGE', $index);?>
				</span>
				<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th width="2%">
							<?php echo Text::_('COM_JMAP_POSITION' ); ?>
						</th>
						<th width="30%" class="title">
							<?php echo Text::_('COM_JMAP_INDEXING_TITLE'); ?>
						</th>
						<th width="35%" class="title">
							<?php echo Text::_('COM_JMAP_INDEXING_LINK'); ?>
						</th>
						<th width="30%" class="title">
							<?php echo Text::_('COM_JMAP_INDEXING_DESC'); ?>
						</th>
					</tr>
				</thead>
				<?php
				$k = 0;
				for ($i=0, $n=count( $page ); $i < $n; $i++) {
					$row = $page[$i];
					$title = $row['headline'];
					$link = $row['url'];
					$description = $row['description'];
					?>
					<tr>
						<td>
							<?php echo $i + 1; ?>
						</td>
						<td>
							<a class="googlelink" target="_blank" href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_INDEXING_TITLE' ); ?>">
								<h3 class="googletitle"><?php echo $title; ?></h3>
							</a>
						</td>
						<td>
							<a class="googlelink" target="_blank" href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_INDEXING_LINK' ); ?>">
								<?php echo $link; ?>
								<span class="fas fa-share" aria-hidden="true"></span>
							</a>
						</td>
						<td>
							<span class="googledesc"><?php echo $description; ?></span>
						</td>
					</tr>
					<?php
				}
				?>
				</table>
			</div>
		</div>
	<?php endforeach; ?>

	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="indexing.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>