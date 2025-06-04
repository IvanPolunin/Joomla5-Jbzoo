<?php 
/** 
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap
 * @subpackage views
 * @subpackage aigenerator
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;
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
			<td nowrap="nowrap">
				<?php if($this->languagePluginEnabled):?>
					<label class="visually-hidden" for="filter_type"><?php echo Text::_('COM_JMAP_LANGUAGES');?></label>
					<?php echo $this->lists['languages'];?>
				<?php endif;?>
				
				<label class="visually-hidden" for="filter_type"><?php echo Text::_('COM_JMAP_AIGENERATOR_API');?></label>
				<?php echo $this->lists['contentsapi'];?>
				
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
			<th width="15%" class="title">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_AIGENERATOR_KEYWORDS_PHRASE', 's.keywords_phrase', @$this->orders['order_Dir'], @$this->orders['order'], 'aigenerator.display' ); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_AIGENERATOR_CONTENTS_EXCERPT', 's.contents', @$this->orders['order_Dir'], @$this->orders['order'], 'aigenerator.display' ); ?>
			</th>
			<th class="title d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_AIGENERATOR_NUM_CONTENTS'); ?>
			</th>
			<th class="title d-none d-lg-table-cell">
				<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_AIGENERATOR_API', 's.api', @$this->orders['order_Dir'], @$this->orders['order'], 'aigenerator.display' ); ?>
			</th>
			
			<?php if($this->languagePluginEnabled):?>
				<th width="5%"  class="title d-none d-md-table-cell">
					<?php echo HTMLHelper::_('grid.sort',  'COM_JMAP_DATASOURCE_LANGUAGE', 's.language', @$this->orders['order_Dir'], @$this->orders['order'], 'aigenerator.display' ); ?>
				</th>
			<?php endif;?>
			<th width="1%">
				<?php echo HTMLHelper::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'aigenerator.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=aigenerator.editEntity&cid[]='. $row->id ;
		
		$checked = null;
		if($this->user->authorise('core.edit', 'com_jmap')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ?
						HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'aigenerator.', $canCheckin) . '<input type="checkbox" style="display:none" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>' :
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
					echo $row->keywords_phrase;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_JMAP_EDIT_LINK' ); ?>">
						<span class="fas fa-pen-square" aria-hidden="true"></span>
						<?php echo $row->keywords_phrase; ?>
					</a>
					<?php
				}
				?>
			</td>
			<td class="d-none d-md-table-cell">
				<?php if($row->api == 'openai'):?>
					<?php 
					$openAiListContent = preg_replace('/{title}.*{\/title}/iU', '', $row->contents);
					echo HTMLHelper::_('string.truncate', StringHelper::str_ireplace(['{content}', '{/content}', '{contentdivider}'], '', strip_tags($openAiListContent)), 300); ?>
				<?php else:?>
					<?php echo $row->contents ? HTMLHelper::_('string.truncate', StringHelper::str_ireplace(['{title}','{/title}', '{content}', '{/content}', '{contentdivider}'], '', strip_tags($row->contents)), 300) : '-'; ?>
				<?php endif;?>
			
			</td>
			<td class="d-none d-md-table-cell">
				<span class="badge bg-primary"><?php echo $row->contents ? substr_count($row->contents, '{contentdivider}') : '-';?></span>
			</td>
			<td class="d-none d-lg-table-cell">
				<span class="badge bg-warning"><?php echo StringHelper::ucfirst($row->api);?></span>
			</td>
			<?php if($this->languagePluginEnabled):?>
				<td class="d-none d-md-table-cell">
					<img style="margin-left:10px" src="<?php echo Uri::root(false) . 'media/mod_languages/images/' .  StringHelper::str_ireplace('-', '_', $row->language) . '.gif';?>" alt="language_flag" />
				</td>
			<?php endif;?>
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
	<input type="hidden" name="task" value="aigenerator.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>