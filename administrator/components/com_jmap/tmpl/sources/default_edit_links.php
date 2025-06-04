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
?>
<div id="accordion_datasource_raw_links" class="sqlquerier card card-info adminform">
	<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#datasource_raw_links"><h4><?php echo Text::_('COM_JMAP_RAW_SOURCE_LINKS' ); ?></h4></div>
	<div class="card-body card-block collapse" id="datasource_raw_links">
		<table class="admintable rawlinks_table">
			<td class="key left_title">
				<label class="title_label"><?php echo Text::_('COM_JMAP_ALL_LINKS');?></label>
				<input type="checkbox" data-role="selectall" class="form-check-input" value=""/>
			</td>
			<td class="right_details">
				<div id="rawlink_controls_grouper">
					<button data-role="rawlinks_action" data-action="add" class="btn btn-sm btn-success active">
						<span class="fas fa-plus" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_ADD_NEWLINK_BTN');?>
					</button>
					<button data-role="rawlinks_action" data-action="delete" class="btn btn-sm btn-danger active">
						<span class="fas fa-times" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_DELETE_LINKS_BTN');?>
					</button>
				</div>
			</td>
		</table>
		<table class="admintable rawlinks_table rawlinks_table_links">
			<?php if(!empty($this->record->sqlquery_managed->link)):?>
				<?php for ($i=0, $n=count( $this->record->sqlquery_managed->link ); $i < $n; $i++): ?>
					<tr>
						<td class="key left_title">
							<label class="title_label"><?php echo Text::sprintf('COM_JMAP_RAW_SOURCE_LINK', $i + 1);?> </label>
							<input type="checkbox" class="form-check-input" value=""/>
						</td>
						<td class="right_details">
							<label class="as badge bg-primary"><?php echo Text::_('COM_JMAP_LINK_TITLE');?></label>
							<input class="sitemap_rawtitle" type="text" name="sqlquery_managed[title][]" value="<?php echo $this->record->sqlquery_managed->title[$i];?>">
			 
							<label class="as badge bg-primary"><?php echo Text::_('COM_JMAP_LINK_HREF');?></label>
							<input class="sitemap_rawlink" type="text" data-validation="required url" name="sqlquery_managed[link][]" value="<?php echo $this->record->sqlquery_managed->link[$i];?>">
						</td>						
					</tr>
				<?php endfor;?>
			<?php endif;?>
		</table>
	</div>
</div>