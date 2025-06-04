<?php 
/** 
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filter\OutputFilter;
?>

<div class="row">
	<div class="col col-md-12">
		<div class="cpanelrow d-lg-flex">
			<!-- CPANEL ICONS -->
			<div class="col col-lg-6" id="accordion_cpanel_icons">
				<div class="card card-default">
				    <div class="card-header accordion-toggle accordion_lightblue noaccordion">
						<h4 class="card-title">
							<span class="fas fa-tasks" aria-hidden="true"></span>
							<?php echo Text::_('COM_JMAP_ICONS');?>
						</h4>
				    </div>
				    <div id="jmap_icons" class="card-block-whitebg>">
						<div class="card-body card-block">
							<?php echo $this->icons; ?>
							<div id="updatestatus">
								<?php 
								if(is_object($this->updatesData)) {
									if(version_compare($this->updatesData->latest, $this->currentVersion, '>')) {
										$updatesACLClass = $this->app->getIdentity()->authorise('core.manage', 'com_installer') ? 'bg-danger' : 'bg-warning';?>
										<a href="http://storejextensions.org/extensions/jsitemap_professional.html" target="_blank" alt="storejoomla link">
											<label data-bs-content="<?php echo Text::sprintf('COM_JMAP_GET_LATEST', $this->currentVersion, $this->updatesData->latest, $this->updatesData->relevance);?>" class="badge <?php echo $updatesACLClass;?> hasPopover">
												<label class="fas fa-exclamation-triangle" aria-hidden="true"></label>
												<?php echo Text::sprintf('COM_JMAP_OUTDATED', $this->updatesData->latest);?>
											</label>
										</a>
									<?php } else { ?>
										<label data-bs-content="<?php echo Text::sprintf('COM_JMAP_YOUHAVE_LATEST', $this->currentVersion);?>" class="badge bg-success hasPopover">
											<label class="fas fa-check-circle" aria-hidden="true"></label>
											<?php echo Text::sprintf('COM_JMAP_UPTODATE', $this->updatesData->latest);?>
										</label>	
									<?php }
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- RIGHT ACCORDION -->
			<div class="col col-lg-6" id="accordion_cpanel">
				<!-- SEO STATS -->
				<?php if($this->componentParams->get('seostats_enabled', '1')): ?>
				<div class="card card-default">
				    <div class="card-header accordion-toggle" data-bs-toggle="collapse" data-bs-target="#seo_stats">
						<h4 class="card-title">
							<span class="fas fa-chart-area" aria-hidden="true"></span>
							<?php echo Text::sprintf('COM_JMAP_SEO_STATS', $this->componentParams->get('seostats_custom_link', Uri::root()));?>
						</h4>
				    </div>
				    <div id="seo_stats" data-bs-parent="#accordion_cpanel" class="card-body card-block card-block-whitebg collapse">
						<?php
							// Backwards compat
							if($this->componentParams->get('seostats_service') == 'alexa') {
								$this->componentParams->set('seostats_service', 'statscrop');
							}
							echo $this->loadTemplate($this->componentParams->get('seostats_service', 'statscrop'));
						?>
					</div>
				</div>
				<?php endif; ?>

				<!-- SITEMAP STATS -->
				<div class="card card-default">
				    <div class="card-header accordion-toggle" data-bs-toggle="collapse"  data-bs-target="#jmap_status">
						<h4 class="card-title">
							<span class="fas fa-chart-bar" aria-hidden="true"></span>
							<?php echo Text::_('COM_JMAP_QUICK_STATS');?>
						</h4>
				    </div>
				    <div id="jmap_status" data-bs-parent="#accordion_cpanel" class="card-body card-block card-block-whitebg collapse">
						<!-- COMPONENT STATUS INDICATOR -->
						<ul class="cpanelinfo nav nav-pills">
						  <li class="nav-item">
						    <a class="nav-link active" href="javascript:void(0);">
						      <span class="badge rounded-pill pull-right"><?php echo $this->infodata['publishedDataSource']?></span>
						      <?php echo Text::_('COM_JMAP_NUM_PUBLISHED_DATA_SOURCES');?>
						    </a>
						  </li>
						 
						  <li class="nav-item">
						    <a class="nav-link active" href="javascript:void(0);">
						      <span class="badge rounded-pill pull-right"><?php echo $this->infodata['totalDataSource']?></span>
						      <?php echo Text::_('COM_JMAP_NUM_TOTAL_DATA_SOURCES');?>
						    </a>
						  </li>
						  
						  <li class="nav-item">
						    <a class="nav-link active" href="javascript:void(0);">
						      <span class="badge rounded-pill pull-right"><?php echo $this->infodata['menuDataSource']?></span>
						      <?php echo Text::_('COM_JMAP_NUM_MENU_DATA_SOURCES');?>
						    </a>
						  </li>
						  
						  <li class="nav-item">
						    <a class="nav-link active" href="javascript:void(0);">
						      <span class="badge rounded-pill pull-right"><?php echo $this->infodata['userDataSource']?></span>
						      <?php echo Text::_('COM_JMAP_NUM_USER_DATA_SOURCES');?>
						    </a>
						  </li>
						  
						  <li class="nav-item">
						    <a class="nav-link active" href="javascript:void(0);">
						      <span class="badge rounded-pill pull-right"><?php echo $this->infodata['datasets']?></span>
						      <?php echo Text::_('COM_JMAP_NUM_PUBLISHED_DATASETS');?>
						    </a>
						  </li>
						</ul>
						
						<canvas id="chart_canvas"></canvas>
				    </div>
				</div>
				
				<!-- ABOUT-->
				<div class="card card-default">
				    <div class="card-header accordion-toggle" data-bs-toggle="collapse" data-bs-target="#jmap_about">
						<h4 class="card-title">
							<span class="fas fa-question-circle" aria-hidden="true"></span>
							<?php echo Text::_('COM_JMAP_ABOUT');?>
						</h4>
				    </div>
				    <div id="jmap_about" data-bs-parent="#accordion_cpanel" class="card-body card-block card-block-whitebg collapse">
						<div class="single_container">
					 		<label class="badge bg-warning"><?php echo Text::sprintf('COM_JMAP_VERSION', $this->currentVersion);?></label>
				 		</div>
				 		
				 		<div class="single_container">
					 		<label class="badge bg-primary"><?php echo Text::_('COM_JMAP_AUTHOR_COMPONENT');?></label>
				 		</div>
				 		
				 		<div class="single_container">
					 		<label class="badge bg-primary"><?php echo Text::_('COM_JMAP_SUPPORTLINK');?></label>
				 		</div>
				 		
				 		<div class="single_container">
					 		<label class="badge bg-primary"><?php echo Text::_('COM_JMAP_DEMOLINK');?></label>
				 		</div>
				    </div>
				</div>
			</div>
		</div>
		<div class="row">
			<!-- SEO CONTROL PANEL -->
			<div class="col col-md-12" id="accordion_cpanel_seo">
				<div class="card card-default">
				    <div class="card-header accordion-toggle accordion_lightblue noaccordion">
						<h4 class="card-title">
							<span class="fas fa-tachometer-alt" aria-hidden="true"></span>
							<?php echo Text::_('COM_JMAP_JMAP_INFO_STATUS');?>
						</h4>
				    </div>
				    <div id="jmap_seo" class="card-block-whitebg">
						<div class="card-body card-block">
							<!-- COMPONENT LINKS -->
							<div class="single_container">
					 			<label class="badge bg-primary"><?php echo Text::_('COM_JMAP_HTML_LINK')?></label>
					 			<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
					 				<input data-role="sitemap_links" data-html="1" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_HTML_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap');?>" />
					 			<?php else:?>
					 				<input data-role="sitemap_links_sef" data-html="1" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_HTML_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=html' . $this->siteItemid));?>"/>
					 			<?php endif;?>
					 		</div>
				 			<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo Text::_('COM_JMAP_XML_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=xml' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
								<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo Text::_('COM_JMAP_XML_IMAGES_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_IMAGES_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_IMAGES_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=images' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo Text::_('COM_JMAP_XML_GNEWS_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_GNEWS_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews');?>" />
						 		<?php else:?>	
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_GNEWS_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=gnews' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div> 
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_XML_MOBILE_DISCLAIMER');?>"><?php echo Text::_('COM_JMAP_XML_MOBILE_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_MOBILE_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_MOBILE_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=mobile' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div> 
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo Text::_('COM_JMAP_XML_VIDEOS_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_VIDEOS_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_VIDEOS_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=videos' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>

							<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_XML_HREFLANG_DISCLAIMER');?>"><?php echo Text::_('COM_JMAP_XML_HREFLANG_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" data-language="1" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_HREFLANG_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" data-language="1" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_HREFLANG_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=hreflang' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		
					 		<?php if($this->componentParams->get('amp_sitemap_enabled', 0) && trim($this->componentParams->get('amp_suffix', ''))):?>
					 		<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_XML_AMP_DISCLAIMER');?>"><?php echo Text::_('COM_JMAP_XML_AMP_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_AMP_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=amp');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_AMP_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=amp');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=amp' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=amp') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=amp') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=amp') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=amp') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
								<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="fas fa-download hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" aria-label="<?php echo Text::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="badge bg-danger hasTooltip" title="<?php echo Text::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo Text::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		<?php endif;?>
					 		
					 		<?php if($this->componentParams->get('geositemap_enabled', 0) && trim($this->componentParams->get('geositemap_address', ''))):?>
					 		<div class="single_container xmlcontainer">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_XML_GEOSITEMAP_DISCLAIMER');?>"><?php echo Text::_('COM_JMAP_XML_GEOSITEMAP_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" data-language="1" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_GEOSITEMAP_LINK')?>" value="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" data-language="1" class="sitemap_links" type="text" aria-label="<?php echo Text::_('COM_JMAP_XML_GEOSITEMAP_LINK')?>" data-valuenosef="<?php echo OutputFilter::ampReplace($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml');?>" value="<?php echo $this->livesitesef . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=geositemap&format=xml' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger fas fa-bolt' href='https://www.google.com/search?q=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_GOOGLE') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.bing.com/indexnow?key=" . $this->indexnowApikey . "&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BING') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger fas fa-bolt' href='https://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_YANDEX') . "</span></a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger fas fa-bolt' href='https://www.baidu.com/s?wd=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'><span class='pinger-text'>" . Text::_('COM_JMAP_PING_BAIDU') . "</span></a>";
						 		?>
						 		<label class="fas fa-bolt hasClickPopover" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" aria-label="<?php echo Text::_('COM_JMAP_PING_SITEMAP');?>" data-bs-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="fas fa-pencil-alt hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" aria-label="<?php echo Text::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
						 		<a class="fas fa-map-marker-alt hasTooltip fancybox" aria-hidden="true" title="<?php echo Text::_('COM_JMAP_OPEN_GEOLOCATION_MAP');?>" href="#gmap" data-role="opengmap"></a><div id="gmap"></div>
							</div>
					 		<?php endif;?>

					 		<!-- LANGUAGE SELECT LIST -->
					 		<?php if($this->lists['languages']):?>
					 		<div class="single_container filters">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_CHOOSE_LANGUAGE');?>"><?php echo Text::_('COM_JMAP_CHOOSE_LANG')?></label>
						 		<?php echo $this->lists['languages'];?>
					 		</div>
					 		<?php endif;?>
					 		
					 		<!-- DATASETS SELECT LIST -->
					 		<?php if($this->lists['datasets_filters']):?>
					 		<div class="single_container filters">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_CHOOSE_DATASET_DESC');?>"><?php echo Text::_('COM_JMAP_CHOOSE_DATASET')?></label>
						 		<?php echo $this->lists['datasets_filters'];?>
					 		</div>
					 		<?php endif;?>
					 		
					 		<!-- MENU FILTERS SELECT LIST -->
					 		<?php if($this->lists['menu_datasource_filters']):?>
					 		<div class="single_container filters">
						 		<label class="badge bg-primary hasPopover" data-bs-content="<?php echo Text::_('COM_JMAP_CHOOSE_MENU_DESC');?>"><?php echo Text::_('COM_JMAP_CHOOSE_MENU')?></label>
						 		<?php echo $this->lists['menu_datasource_filters'] ;?>
					 		</div>
					 		<?php endif;?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<form name="adminForm" id="adminForm" action="index.php">
		<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
		<input type="hidden" name="task" value=""/>
	</form>
</div>