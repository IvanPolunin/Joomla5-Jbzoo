<?php 
/** 
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage views
 * @subpackage metainfo
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;
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
				
				<span class="input-group-text input-checkbox hasTooltip" title="<?php echo Text::_('COM_JMAP_METAINFO_EXACT_MATCH');?>" aria-label="<?php echo Text::_('COM_JMAP_METAINFO_EXACT_MATCH');?>">
					<input type="checkbox" id="exactsearchpage" name="exactsearchpage" value="1" <?php echo $this->exactsearchpage;?>/>
  					<label class="input-group" for="exactsearchpage"><?php echo Text::_('COM_JMAP_METAINFO_EXACT_MATCH_LABEL');?></label>
				</span>
			</td>
			<td>
				<label class="visually-hidden" for="filter_excludestate"><?php echo Text::_('COM_JMAP_ALL_METAINFO_LINKS');?></label>
				<?php
					echo $this->lists['excludestate'];
				?>
				<label class="visually-hidden" for="filter_state"><?php echo Text::_('JLIB_HTML_SELECT_STATE');?></label>
				<?php
					echo $this->lists['state'];
				?>
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

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="metainfo.display" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
	<input type="hidden" name="sitemaplanguage" value="<?php echo $this->sitemapLanguage;?>" />
	
	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo Text::_('COM_JMAP_NUM' ); ?>
			</th>
			<th style="width:15%" class="title">
				<?php echo HTMLHelper::_('grid.sort', 'COM_JMAP_METAINFO_LINK', 'link', @$this->orders['order_Dir'], @$this->orders['order'], 'metainfo.display'); ?>
			</th>
			<th style="width:15%">
				<?php echo Text::_('COM_JMAP_METATITLE' ); ?>
			</th>
			<th style="width:15%">
				<?php echo Text::_('COM_JMAP_METADESC' ); ?>
			</th>
			<th style="width:10%">
				<?php echo Text::_('COM_JMAP_METAROBOTS' ); ?>
			</th>
			<th style="width:10%">
				<?php echo Text::_('COM_JMAP_METAIMAGE' ); ?>
			</th>
			<th style="width:15%">
				<?php echo Text::_('COM_JMAP_SAVE_DELETE' ); ?>
			</th>
			<th style="width:2%">
				<?php echo Text::_('COM_JMAP_STATUS' ); ?>
			</th>
			<th class="d-none d-md-table-cell" style="width:2%">
				<?php echo Text::_('COM_JMAP_EXCLUDED' ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr class="subtitles">
			<td>
			</td>
			<td class="title">
				<?php echo Text::_('COM_JMAP_SEARCH_ENGINES_LINK_DESC' ); ?>
			</td>
			<td>
				<?php echo Text::_('COM_JMAP_SEARCH_ENGINES_TITLE_DESC' ); ?>
			</td>
			<td>
				<?php echo Text::_('COM_JMAP_SEARCH_ENGINES_DESCRIPTION_DESC' ); ?>
			</td>
			<td>
				<?php echo Text::_('COM_JMAP_SEARCH_ENGINES_ROBOTS_DESC' ); ?>
			</td>
			<td>
				<?php echo Text::_('COM_JMAP_METAIMAGE_DESC' ); ?>
			</td>
			<td>
				<?php echo Text::_('COM_JMAP_SEARCH_ENGINES_SAVE_DELETE_DESC' ); ?>
			</td>
			<td>
				<?php echo Text::_('COM_JMAP_STATUS_DESC' ); ?>
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo Text::_('COM_JMAP_EXCLUDED_COMPLETELY_FROM_SITEMAP_DESC' ); ?>
			</td>
		</tr>
	<?php
	$indexingWaiter = '<img class="hasTooltip metainfo-waiter" title="' . Text::_('COM_JMAP_METAINFO_COUNTERS_WAITER') . '" alt="' . Text::_('COM_JMAP_METAINFO_COUNTERS_WAITER') . '" src="' . Uri::base(true) . '/components/com_jmap/images/loading.gif" border="0" />';
	$k = 0;
	foreach ( $this->items as $row ) {
		?>
		<tr>
			<td align="center">
				<?php echo $k + 1; ?>
			</td>
			<td class="link_loc">
				<a data-role="link" data-linkidentifier="<?php echo $k + 1;?>" href="<?php echo $row->loc; ?>" alt="sitelink" target="_blank" data-linkidentifier="<?php echo $k + 1;?>">
					<?php echo $row->loc; ?>
					<span class="fas fa-share" aria-hidden="true"></span>
				</a>
			</td>
			<td>
				<textarea data-bind="{title}" class="metainfo metatitle" data-titleidentifier="<?php echo $k + 1;?>"><?php echo isset($row->metainfos->meta_title) ? $row->metainfos->meta_title : null;?></textarea>
				<?php echo $indexingWaiter;?>
			</td>
			<td>
				<textarea data-bind="{desc}" class="metainfo metadesc" data-descidentifier="<?php echo $k + 1;?>"><?php echo isset($row->metainfos->meta_desc) ? $row->metainfos->meta_desc : null;?></textarea>
				<?php echo $indexingWaiter;?>
			</td>
			<td align="center">
				<select class="robots_directive form-select" data-bind="{robots}" id="jmap_metainfo_robots_<?php echo $k + 1;?>" data-robotsidentifier="<?php echo $k + 1;?>">
					<option value="">
						<?php echo Text::_('COM_JMAP_USE_GLOBAL');?>
					</option>
					<option value="index, follow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'index, follow' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_INDEX_FOLLOW');?>
					</option>
					<option value="noindex, follow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'noindex, follow' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_NOINDEX_FOLLOW');?>
					</option>
					<option value="index, nofollow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'index, nofollow' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_INDEX_NOFOLLOW');?>
					</option>
					<option value="noindex, nofollow" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'noindex, nofollow' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_NOINDEX_NOFOLLOW');?>
					</option>
					<option value="noarchive" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'noarchive' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_NOARCHIVE');?>
					</option>
					<option value="nosnippet" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'nosnippet' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_NOSNIPPET');?>
					</option>
					<option value="max-snippet:-1,max-image-preview:large,max-video-preview:-1" <?php echo isset($row->metainfos->robots) && $row->metainfos->robots == 'max-snippet:-1,max-image-preview:large,max-video-preview:-1' ? 'selected="selected"' : null;?>>
						<?php echo Text::_('COM_JMAP_USE_OPTIN');?>
					</option>
				</select>
			</td>
			<td class="metaimage">
				<?php
					$this->mediaField->value = null;
					if(isset($row->metainfos)) {
						$this->mediaField->value = $row->metainfos->meta_image;
					}
					$this->mediaField->id = 'jform_media_identifier_' . ($k + 1);
					$this->mediaField->name = 'jform_media_identifier_' . ($k + 1);
					$this->mediaField->{'data-identifier'} = ($k + 1);
					if(isset($this->mediaFieldReflection)) {
						$this->mediaFieldReflection->setValue($this->mediaField, []);
					}
					echo $this->mediaField->renderField();
				?>
			</td>
			<td>
				<button class="btn btn-primary" data-action="savemeta" data-save="<?php echo $k + 1;?>"><span class="fas fa-save" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_METAINFO_SAVE');?></button>
				<button class="btn btn-danger" data-action="deletemeta" data-delete="<?php echo $k + 1;?>"><span class="fas fa-times-circle" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_METAINFO_DELETE');?></button>
			</td>
			<td>
				<fieldset class="btn-group radio" data-bs-toggle="buttons" data-action="statemeta" data-state="<?php echo $k + 1;?>">
					<?php 
						$published = isset($row->metainfos->published) ? $row->metainfos->published : 1;
						echo JMapHelpersHtml::booleanlist( 'published' . $k, null, $published);
					?>
				</fieldset>
			</td>
			<td class="d-none d-md-table-cell">
				<fieldset class="btn-group radio" data-bs-toggle="buttons" data-action="excludedmeta" data-state="<?php echo $k + 1;?>">
					<?php 
						$excluded = isset($row->metainfos->excluded) ? $row->metainfos->excluded : 0;
						echo JMapHelpersHtml::booleanlist( 'excluded' . $k, null, $excluded);
					?>
				</fieldset>
			</td>
		</tr>
		<?php
		$k++;
	}
	// No links showed
	if($k == 0) {
		$this->app->enqueueMessage ( Text::_('COM_JMAP_METAINFO_NOLINKS_ONTHISPAGE') );
	}
	?>
	</tbody>
	</table>
</form>