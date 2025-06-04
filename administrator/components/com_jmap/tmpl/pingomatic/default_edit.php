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
?>
<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<div id="accordion_pingomatic_details" class="card card-info  adminform">
		<div class="card-header accordion-toggle accordion_lightblue noaccordion" data-bs-target="#pingomatic_details"><h4><?php echo Text::_('COM_JMAP_PINGOMATIC_DETAILS' ); ?></h4></div>
		<div class="card-body card-block" id="pingomatic_details">
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="title">
								<?php echo Text::_('COM_JMAP_LINKTITLE' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="title" id="title" data-validation="required" aria-required="true" value="<?php echo $this->record->title;?>" />
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label for="linkurl">
								<?php echo Text::_('COM_JMAP_LINKURL' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="blogurl" id="linkurl" data-validation="required url" value="<?php echo $this->record->blogurl;?>" />
							<label class="as badge bg-primary hasClickPopover" data-bs-title="<?php echo Text::_('COM_JMAP_PICKURL_DESC');?>" data-bs-content="<?php echo Text::_('COM_JMAP_LOADING');?>"><?php echo Text::_('COM_JMAP_PICKURL');?></label>
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label for="rssurl">
								<?php echo Text::_('COM_JMAP_RSSURL' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="rssurl" id="rssurl" data-validation="url" value="<?php echo $this->record->rssurl;?>" />
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label>
								<?php echo Text::_('COM_JMAP_LASTPING' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<?php if($this->record->lastping):?>
								<label class="badge bg-warning" id="lastping">
									<?php echo HTMLHelper::_('date', $this->record->lastping, Text::_('DATE_FORMAT_LC2')); ?>
								</label>
							<?php else:?>
								<label class="badge bg-warning" id="lastping"><?php echo Text::_('COM_JMAP_NEVER_PING');?></label>
							<?php endif;?>
						</td>
					</tr> 
				</tbody>
			</table>
		</div>
	</div>
	
	<div id="accordion_pingomatic_services" class="card card-info adminform">
		<div class="card-header accordion-toggle accordion_lightblue noaccordion" data-bs-target="#pingomatic_services"><h4><?php echo Text::_('COM_JMAP_PINGOMATIC_SERVICES' ); ?></h4></div>
		<div class="card-body card-block" id="pingomatic_services">	
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="description">
								<?php echo Text::_('COM_JMAP_SERVICES_LIST' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<div class="card">
								<div class="card-header accordion-toggle accordion_lightgreen noaccordion">
							  		<?php echo Text::_('COM_JMAP_COMMON_SERVICES'); ?>
							  	</div>
								<div class="card-body card-block">
								    <div id="common">
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_google'];?></fieldset><a href="https://www.google.com" target="_blank"><label class="as badge bg-info">Google</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_bing'];?></fieldset><a href="https://www.bing.com" target="_blank"><label class="as badge bg-info">Bing</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_yandex'];?></fieldset><a href="https://yandex.com" target="_blank"><label class="as badge bg-info">Yandex</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_entireweb'];?></fieldset><a href="https://www.entireweb.com" target="_blank"><label class="as badge bg-info">Entireweb</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_viesearch'];?></fieldset><a href="https://viesearch.com" target="_blank"><label class="as badge bg-info">Viesearch</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_webcrawler'];?></fieldset><a href="https://www.webcrawler.com" target="_blank"><label class="as badge bg-info">WebCrawler</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_yahoo'];?></fieldset><a href="https://www.yahoo.com" target="_blank"><label class="as badge bg-info">Yahoo</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_duckduckgo'];?></fieldset><a href="https://duckduckgo.com" target="_blank"><label class="as badge bg-info">DuckDuckGo</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_ask'];?></fieldset><a href="https://www.ask.com" target="_blank"><label class="as badge bg-info">Ask</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_indexnowbing'];?></fieldset><a href="https://www.indexnow.org" target="_blank"><label class="as badge bg-info">IndexNow Bing</label></a></div>
										<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['ajs_indexnowyandex'];?></fieldset><a href="https://www.indexnow.org" target="_blank"><label class="as badge bg-info">IndexNow Yandex</label></a></div>
									</div>
								</div>
							</div>
							<div class="card ms-3">
								<div class="card-header accordion-toggle accordion_lightgreen noaccordion">
							  		<?php echo Text::_('COM_JMAP_SPECIALIZED_SERVICES');?>
							  	</div>
								<div class="card-body card-block">
									<div id="specialized">
									<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['chk_blogs'];?></fieldset><a href="http://blo.gs/" target="_blank"><label class="as badge bg-info">Blo.gs</label></a></div>
								    	<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['chk_feedburner'];?></fieldset><a href="http://feedburner.com/" target="_blank"><label class="as badge bg-info">Feed Burner</label></a></div>
								    	<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['chk_tailrank'];?></fieldset><a href="http://spinn3r.com/" target="_blank"><label class="as badge bg-info">Spinn3r</label></a></div>
								    	<div class="service_control"><fieldset class="radio btn-group" data-bs-toggle="buttons"><?php echo $this->lists['chk_superfeedr'];?></fieldset><a href="http://superfeedr.com/" target="_blank"><label class="as badge bg-info">Superfeedr</label></a></div>
									</div>
								</div>
							</div>
						</td>
					</tr> 
				</tbody>
			</table>
		</div>
	</div>
		
	<input type="hidden" name="option" value="<?php echo $this->option?>" />
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="lastping" value="<?php echo $this->record->lastping;?>" />
	<input type="hidden" name="task" value="" />
</form>

<iframe src="<?php echo $this->urischeme;?>://pingomatic.com/" id="pingomatic_iframe" name="pingomatic_iframe"></iframe>
<div id="pingomatic_ajaxloader"></div>