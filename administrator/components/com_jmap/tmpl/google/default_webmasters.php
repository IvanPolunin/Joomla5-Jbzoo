<?php 
/** 
 * @package JMAP::OVERVIEW::administrator::components::com_jmap
 * @subpackage views
 * @subpackage overview
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="webmasters_cards">
	<span class='badge bg-primary'><?php echo $this->statsDomain; ?></span> 
	<?php echo $this->hasOwnCredentials ? null : "<span data-bs-content='" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET_DESC') . "' class='badge bg-warning hasPopover google pull-right'>" . Text::_('COM_JMAP_GOOGLE_APP_NOTSET') . "</span>"; ?>
	
	<!-- SITEMAPS STATS AND MANAGEMENT-->
	<div class="card card-info card-group-google" id="jmap_googlestats_webmasters_sitemaps_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_googlestats_webmasters_sitemaps">
			<h4><span class="fas fa-chart-area" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAPS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_sitemaps" class="collapse card-body card-block">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<?php if ($this->user->authorise('core.edit', 'com_jmap')):?>
							<th style="width:1%">
								<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_DELETE' ); ?>
							</th>
							<th style="width:1%">
								<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_RESUBMIT' ); ?>
							</th>
						<?php endif;?>
						<th style="width:15%">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_PATH' ); ?>
						</th>
						<th class="title d-none d-md-table-cell">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS' ); ?>
						</th>
						<th class="title d-none d-md-table-cell">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_SUBMITTED' ); ?>
						</th>
						<th class="title d-none d-md-table-cell">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_FETCHED' ); ?>
						</th>
						<th class="title d-none d-lg-table-cell">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_WARNINGS' ); ?>
						</th>
						<th class="title d-none d-lg-table-cell">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ERRORS' ); ?>
						</th>
						<th class="title d-none d-lg-table-cell" colspan="2">
							<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ISINDEX' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php 
						// Render sitemaps
						if(!empty($this->googleData['sitemaps'])){
							foreach ($this->googleData['sitemaps'] as $sitemap) {
								?>
								<tr>
									<?php if ($this->user->authorise('core.edit', 'com_jmap')):?>
										<td style="text-align:center">
											<a href="javascript:void(0)" data-role="sitemapdelete" data-url="<?php echo $sitemap->getPath();?>">
												<span class="fas fa-times-circle fa-icon-red fa-icon-large" aria-hidden="true"></span>
											</a>
										</td>
										<td style="text-align:center">
											<a href="javascript:void(0)" data-role="sitemapresubmit" data-url="<?php echo $sitemap->getPath();?>">
												<span class="fas fa-sync fa-icon-large" aria-hidden="true"></span>
											</a>
										</td>
									<?php endif;?>
									<td style="font-size: 11px;word-break: break-all"><a target="_blank" class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICK_TO_OPEN_SITEMAP');?>" href="<?php echo $sitemap->getPath();?>"><?php echo $sitemap->getPath();?></a></td>
									<td class="d-none d-md-table-cell">
										<?php echo $sitemap->getIsPending() ? 
										'<span class="badge bg-warning label-small">' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS_PENDING') . '</span>' : 
										'<span class="badge bg-success label-small">' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS_INDEXED') . '</span>';?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php 
											$submittedDate = $sitemap->getLastSubmitted() ? $sitemap->getLastSubmitted() : '';
											$date = Factory::getDate($submittedDate); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(Text::_('DATE_FORMAT_LC2'), true);
										?>
									<td class="d-none d-md-table-cell">
										<?php 
											$fetchedDate = $sitemap->getLastDownloaded() ? $sitemap->getLastDownloaded() : '';
											$date = Factory::getDate($fetchedDate); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(Text::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td class="d-none d-lg-table-cell">
										<?php echo $sitemap->getWarnings() > 0 ? 
										'<span data-bs-content="' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_WARNINGS_DESC') . '" class="hasPopover badge bg-danger label-small">' . $sitemap->getWarnings()  . '</span>' : 
										'<span class="badge bg-success label-small">0</span>';?>
									</td>
									<td class="d-none d-lg-table-cell">
										<?php echo $sitemap->getErrors() > 0 ? 
										'<span data-bs-content="' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ERRORS_DESC') . '" class="hasPopover badge bg-danger label-small">' . $sitemap->getErrors()  . '</span>' : 
										'<span class="badge bg-success label-small">0</span>';?>
									</td>
									<td class="d-none d-lg-table-cell">
										<?php echo $sitemap->getIsSitemapsIndex() ? 
										'<span class="badge bg-primary label-small nowrap">' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_INDEX') . '</span>' : 
										'<span class="badge bg-primary label-small nowrap">' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STANDARD') . '</span>';?>
									</td>
									
									<td class="d-none d-md-table-cell" colspan="3">
										<table class="adminlist table table-striped table-hover">
											<th class="title" width="20%">
												<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_TYPE' ); ?>
											</th>
											<th class="title">
												<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_LINKS_SUBMITTED' ); ?>
											</th>
											<th class="title">
												<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_INDEXED' ); ?>
											</th>
										<?php foreach ($sitemap as $sitemapContents) { ?>
											<tr>
												<td><span class="badge bg-primary label-small"><?php echo $sitemapContents->getType();?></span></td>
												<td>
													<span>
														<?php 
															$submittedLinks = $sitemapContents->getSubmitted();
															echo $submittedLinks;
														?>
													</span>
													<div style="width:100%;height:18px;background-color:#468847" class="slider_submitted"></div>
												</td>
												<td>
													<span>
														<?php 
															$indexedLinks = ($sitemapContents->getIndexed() < $sitemapContents->getSubmitted() / 2) ? (intval($sitemapContents->getSubmitted())) : $sitemapContents->getIndexed();
															$indexedLinks = $indexedLinks > 0 ? $indexedLinks : 1;
															echo $indexedLinks;
															if($submittedLinks == 0) {
																$percentage = 100;
															} else {
																$percentage = intval(($indexedLinks / $submittedLinks) * 100);
															}
														?>
													</span>
													<div style="width:<?php echo $percentage;?>%;height:18px;background-color:#3a87ad" class="slider_indexed"></div>
												</td>
											</tr>
										<?php 
										}
										?>
										</table>
									</td>
								</tr><?php 
								}
							}
						?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- INSPECT REPORT BY API RESPONSE-->
	<div class="card card-info card-group-google" id="jmap_google_inspectionurl_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_google_inspectionurl">
			<h4><span class="fas fa-globe"></span> <?php echo Text::sprintf ('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION', $this->statsDomain ); ?></h4>
		</div>
		<div id="jmap_google_inspectionurl" class="collapse card-body card-block">
			<table class="full headerlist">
				<tr>
					<td align="left" width="80%">
						<span class="input-group pageurl active">
						  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_PAGEURL');?>"><span class="fas fa-filter" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_PAGEURL' ); ?>:</span>
						  <input type="text" name="inspect_pageurl" id="inspect_pageurl" data-validation="url" value="<?php echo $this->inspectionUrl;?>" class="text_area"/>
						</span>
						<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
						<button class="btn btn-primary btn-sm" onclick="document.getElementById('inspect_pageurl').value='';this.form.submit();"><?php echo Text::_('COM_JMAP_RESET' ); ?></button>
					</td>
				</tr>
			</table>
			
			<?php if($this->inspectionUrl && isset($this->googleData['inspect'])):?>
				<table class="adminlist table table-striped table-centered">
					<thead>
						<tr>
							<th class="title" width="50%">
								<span class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_PAGEFETCHSTATE_DESC');?>">
									<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_PAGEFETCHSTATE' ); ?>
								</span>
							</th>
							<th class="title" width="50%">
								<span class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_MOBILE_USABILITY_DESC');?>">
									<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_MOBILE_USABILITY' ); ?>
								</span>
							</th>
						</tr>
					</thead>
					<?php 
						$successResult = '<span class="fas fa-check fa-custom-icon-green fa-custom-icon-large"></span>';
						$errorResult = '<span class="fas fa-times fa-custom-icon-red fa-custom-icon-large"></span>';
						$warningResult = '<span class="fas fa-minus fa-custom-icon-gray fa-custom-icon-large"></span>';
					?>
					<tbody>
						<tr>
							<td>
								<?php
								if(isset($this->googleData['inspect']->inspectionResult->indexStatusResult->pageFetchState)):
									$pageFetchState = StringHelper::strtolower($this->googleData['inspect']->inspectionResult->indexStatusResult->pageFetchState);
									$labelClass = 'bg-primary';
									$iconSpan = '';
									if(StringHelper::strpos($pageFetchState, 'success') !== false) {
										$labelClass = 'bg-success';
										$iconSpan = $successResult;
									} elseif (StringHelper::strpos($pageFetchState, 'error') !== false) {
										$labelClass = 'bg-danger';
										$iconSpan = $errorResult;
									} else {
										$labelClass = 'bg-warning';
										$iconSpan = $warningResult;
									}
								echo $iconSpan;?>
								<span class="badge <?php echo $labelClass;?>"><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_PAGEFETCHSTATE_' . $this->googleData['inspect']->inspectionResult->indexStatusResult->pageFetchState);?></span>
								<?php else:?>
									<span class="fas fa-minus fa-custom-icon-gray fa-custom-icon-large" aria-hidden="true"></span>
								<?php endif;?>
							</td>
							<td>
								<?php
								if(isset($this->googleData['inspect']->inspectionResult->mobileUsabilityResult->verdict)):
									$mobileVerdict = StringHelper::strtolower($this->googleData['inspect']->inspectionResult->mobileUsabilityResult->verdict);
									$labelClass = 'bg-primary';
									$iconSpan = '';
									if(StringHelper::strpos($mobileVerdict, 'pass') !== false) {
										$labelClass = 'bg-success';
										$iconSpan = $successResult;
									} elseif (StringHelper::strpos($pageFetchState, 'fail') !== false) {
										$labelClass = 'bg-danger';
										$iconSpan = $errorResult;
									} else {
										$labelClass = 'bg-warning';
										$iconSpan = $warningResult;
									}
								echo $iconSpan;?>
								<span class="badge <?php echo $labelClass;?>"><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_MOBILE_USABILITY_RESULT_' . $this->googleData['inspect']->inspectionResult->mobileUsabilityResult->verdict);?></span>
								<?php else:?>
									<span class="fas fa-minus fa-custom-icon-gray fa-custom-icon-large" aria-hidden="true"></span>
								<?php endif;?>
							</td>
						</tr>
					</tbody>
				</table>
	
				<table class="adminlist table table-striped">
					<thead>
						<tr>
							<th class="title" width="50%">
								<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_TITLE' ); ?>
							</th>
							<th class="title" width="50%">
								<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_RESULT' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$arrayInspectStats = array(
								array(	'statName' => 'coverageState',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_COVERAGESTATE',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_COVERAGESTATE_DESC'
								),
								array(	'statName' => 'crawledAs',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_CRAWLEDAS',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_CRAWLEDAS_DESC',
										'statTranslation' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_CRAWLEDAS_%s'
								),
								array(	'statName' => 'lastCrawlTime',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_LASTCRAWLTIME',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_LASTCRAWLTIME_DESC'
								),
								array(	'statName' => 'googleCanonical',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_GOOGLECANONICAL',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_GOOGLECANONICAL_DESC'
								),
								array(	'statName' => 'userCanonical',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_USERCANONICAL',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_USERCANONICAL_DESC'
								),
								array(	'statName' => 'indexingState',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_INDEXINGSTATE',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_INDEXINGSTATE_DESC',
										'statTranslation' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_INDEXINGSTATE_%s'
								),
								array(	'statName' => 'verdict',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_VERDICT',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_VERDICT_DESC',
										'statTranslation' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_VERDICT_%s'
								),
								array(	'statName' => 'robotsTxtState',
										'statTitle' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_ROBOTSTXTSTATE',
										'statDescription' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_ROBOTSTXTSTATE_DESC',
										'statTranslation' => 'COM_JMAP_GOOGLE_WEBMASTERS_URLINSPECTION_ROBOTSTXTSTATE_%s'
								)
						);
						
						if(isset($this->googleData['inspect']->inspectionResult->indexStatusResult)):
							foreach ($arrayInspectStats as $statToDisplay):
								if(!isset($this->googleData['inspect']->inspectionResult->indexStatusResult->{$statToDisplay['statName']})) continue;
								?>
								<tr>
									<td>
										<span data-bs-content="<?php echo Text::_($statToDisplay['statDescription']);?>" class="hasPopover badge bg-primary label-small"><?php echo Text::_($statToDisplay['statTitle']); ?></span>
									</td>
									<td>
										<?php
											$statValue = $this->googleData['inspect']->inspectionResult->indexStatusResult->{$statToDisplay['statName']};
											echo isset($statToDisplay['statTranslation']) ? Text::_(sprintf($statToDisplay['statTranslation'], $statValue)) : $statValue;
										?>
									</td>
								</tr>
							<?php
							endforeach;
						endif;
						?>
					</tbody>
				</table>
			<?php endif;?>
		</div>
	</div>
	
	<!-- GOOGLE SEARCH CONSOLE STATS AND METRICS-->
	<div class="card card-info card-group-google" id="jmap_google_search_console_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_google_search_console">
			<h4><span class="fas fa-chart-bar" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_SEARCH_CONSOLE' ); ?></h4>
		</div>
		<div id="jmap_google_search_console" class="collapse card-body card-block">
			
			<table class="full headerlist">
				<tr>
					<td align="left">
						<span class="input-group double active">
						  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER_BY_DATE_FROM');?>"><span class="fas fa-th" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER_BY_DATE_FROM' ); ?>:</span>
						  <input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['from'];?>" class="text_area"/>
						</span>
						<span class="input-group double active">
						  <span class="input-group-text" aria-label="<?php echo Text::_('COM_JMAP_FILTER_BY_DATE_TO');?>"><span class="fas fa-th" aria-hidden="true"></span> <?php echo Text::_('COM_JMAP_FILTER_BY_DATE_TO' ); ?>:</span>
						  <input type="text" name="toperiod" id="toPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['to'];?>" class="text_area"/>
						</span>
						<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_JMAP_GO' ); ?></button>
					</td>
				</tr>
			</table>
	
			<!-- GOOGLE SEARCH CONSOLE STATS KEYWORDS -->
			<div class="card card-warning card-group-google" id="jmap_googleconsole_query_accordion">
				<div class="card-header accordion-toggle accordion_lightyellow" data-bs-toggle="collapse" data-bs-target="#jmap_google_query">
					<h4><span class="fas fa-chart-line" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_QUERY' ); ?></h4>
				</div>
				<div id="jmap_google_query" class="collapse card-body card-block card-overflow card-overflow-large">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th>
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_KEYS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_query'])){
									foreach ($this->googleData['results_query'] as $dataGroupedByQuery) { ?>
										<tr>
											<td data-bind="querystats_keyword">
												<span class="badge bg-info">
													<?php $dataGroupedQuery = $dataGroupedByQuery->getKeys();?>
													<?php echo htmlspecialchars( $dataGroupedQuery[0], ENT_QUOTES, 'UTF-8');?>
												</span>
												<a href="https://www.google.com/#q=<?php echo urlencode($dataGroupedQuery[0]);?>" target="_blank">
													<span class="icon-out"></span>
												</a>
											</td>
											<td data-bind="querystats_clicks">
												<?php echo $dataGroupedByQuery->getClicks();?>
											</td>
											<td data-bind="querystats_impressions">
												<?php echo $dataGroupedByQuery->getImpressions();?>
											</td>
											<td data-bind="querystats_rate">
												<?php echo round(($dataGroupedByQuery->getCtr() * 100), 2) . '%';?>
											</td>
											<td data-bind="querystats_position">
												<?php 
													$serpPosition = (int)$dataGroupedByQuery->getPosition();
													$classLabel = $serpPosition > 30 ? 'bg-danger' : 'bg-success';
												?>
												<span class="badge <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
				<canvas id="querystats-chart"></canvas>
			</div>
			<br/>
			
			<!-- GOOGLE SEARCH CONSOLE STATS PAGES -->
			<div class="card card-warning card-group-google" id="jmap_googleconsole_pages_accordion">
				<div class="card-header accordion-toggle accordion_lightyellow" data-bs-toggle="collapse" data-bs-target="#jmap_google_pages">
					<h4><span class="fas fa-copy" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_PAGES' ); ?></h4>
				</div>
				<div id="jmap_google_pages" class="collapse card-body card-block card-overflow card-overflow-large">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th>
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_PAGES' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_page'])){
									foreach ($this->googleData['results_page'] as $dataGroupedByPage) { ?>
										<tr>
											<td data-bind="pagestats_page">
												<span>
													<?php $dataGroupedKeys = $dataGroupedByPage->getKeys();?>
													<a href="<?php echo $dataGroupedKeys[0];?>" target="_blank">
														<?php echo $dataGroupedKeys[0];?> <span class="icon-out"></span>
													</a>
												</span>
											</td>
											<td data-bind="pagestats_clicks">
												<?php echo $dataGroupedByPage->getClicks();?>
											</td>
											<td data-bind="pagestats_impressions">
												<?php echo $dataGroupedByPage->getImpressions();?>
											</td>
											<td data-bind="pagestats_rate">
												<?php echo round(($dataGroupedByPage->getCtr() * 100), 2) . '%';?>
											</td>
											<td data-bind="pagestats_position">
												<?php 
													$serpPosition = (int)$dataGroupedByPage->getPosition();
													$classLabel = $serpPosition > 30 ? 'bg-danger' : 'bg-success';
												?>
												<span class="badge <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
				<canvas id="pagestats-chart"></canvas>
			</div>
			<br/>
			
			<!-- GOOGLE SEARCH CONSOLE STATS DEVICES -->
			<div class="card card-warning card-group-google" id="jmap_googleconsole_device_accordion">
				<div class="card-header accordion-toggle accordion_lightyellow" data-bs-toggle="collapse" data-bs-target="#jmap_google_device">
					<h4><span class="fas fa-mobile-alt" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_DEVICE' ); ?></h4>
				</div>
				<div id="jmap_google_device" class="collapse card-body card-block card-overflow card-overflow-large">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th style="width:50%">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_DEVICE' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_device'])){
									foreach ($this->googleData['results_device'] as $dataGroupedByDevice) { ?>
										<tr>
											<td data-bind="devicestats_device">
												<?php $dataGroupedKeys = $dataGroupedByDevice->getKeys();?>
												<span class="badge bg-info hasRightPopover" data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_SEARCH_' . strtoupper($dataGroupedKeys[0]) . '_DESC');?>">
													<?php echo ucfirst(strtolower($dataGroupedKeys[0]));?>
												</span>
											</td>
											<td data-bind="devicestats_clicks">
												<?php echo $dataGroupedByDevice->getClicks();?>
											</td>
											<td data-bind="devicestats_impressions">
												<?php echo $dataGroupedByDevice->getImpressions();?>
											</td>
											<td data-bind="devicestats_rate">
												<?php echo round(($dataGroupedByDevice->getCtr() * 100), 2) . '%';?>
											</td>
											<td data-bind="devicestats_position">
												<?php 
													$serpPosition = (int)$dataGroupedByDevice->getPosition();
													$classLabel = $serpPosition > 30 ? 'bg-danger' : 'bg-success';
												?>
												<span class="badge <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
				<canvas id="devicestats-chart"></canvas>
			</div>
			<br/>
			
			<!-- GOOGLE SEARCH CONSOLE STATS COUNTRY -->
			<div class="card card-warning card-group-google" id="jmap_googleconsole_country_accordion">
				<div class="card-header accordion-toggle accordion_lightyellow" data-bs-toggle="collapse" data-bs-target="#jmap_google_country">
					<h4><span class="fas fa-globe" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_COUNTRY' ); ?></h4>
				</div>
				<div id="jmap_google_country" class="collapse card-body card-block card-overflow card-overflow-large">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th style="width:50%">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_COUNTRY' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_country'])){
									foreach ($this->googleData['results_country'] as $dataGroupedByCountry) { ?>
										<tr>
											<td data-bind="countrystats_country">
												<span class="badge bg-info">
													<?php 
														$dataGroupedKeys = $dataGroupedByCountry->getKeys();
														$countryKey = strtoupper($dataGroupedKeys[0]);
													?>
													<?php echo array_key_exists($countryKey, $this->jMapGoogleIsoArray) ? $this->jMapGoogleIsoArray[$countryKey] : ucfirst($dataGroupedKeys[0]);?>
												</span>
											</td>
											<td data-bind="countrystats_clicks">
												<?php echo $dataGroupedByCountry->getClicks();?>
											</td>
											<td data-bind="countrystats_impressions">
												<?php echo $dataGroupedByCountry->getImpressions();?>
											</td>
											<td data-bind="countrystats_rate">
												<?php echo round(($dataGroupedByCountry->getCtr() * 100), 2) . '%';?>
											</td>
											<td data-bind="countrystats_position">
												<?php 
													$serpPosition = (int)$dataGroupedByCountry->getPosition();
													$classLabel = $serpPosition > 30 ? 'bg-danger' : 'bg-success';
												?>
												<span class="badge <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
				<canvas id="countrystats-chart"></canvas>
			</div>
			<br/>
			
			<!-- GOOGLE SEARCH CONSOLE STATS DATE -->
			<div class="card card-warning card-group-google" id="jmap_googleconsole_date_accordion">
				<div class="card-header accordion-toggle accordion_lightyellow" data-bs-toggle="collapse" data-bs-target="#jmap_google_date">
					<h4><span class="fas fa-calendar"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_DATE' ); ?></h4>
				</div>
				<div id="jmap_google_date" class="collapse card-body card-block card-overflow card-overflow-large">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th style="width:50%">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_DATE' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_date'])){
									foreach ($this->googleData['results_date'] as $dataGroupedByDate) { ?>
										<tr>
											<td data-bind="datestats_date">
												<span class="badge bg-info">
													<?php 
														$dataGroupedKeys = $dataGroupedByDate->getKeys();
														$dateKey = strtoupper($dataGroupedKeys[0]);
													?>
													<?php echo $dateKey;?>
												</span>
											</td>
											<td data-bind="datestats_clicks">
												<?php echo $dataGroupedByDate->getClicks();?>
											</td>
											<td data-bind="datestats_impressions">
												<?php echo $dataGroupedByDate->getImpressions();?>
											</td>
											<td data-bind="datestats_rate">
												<?php echo round(($dataGroupedByDate->getCtr() * 100), 2) . '%';?>
											</td>
											<td data-bind="datestats_position">
												<?php 
													$serpPosition = (int)$dataGroupedByDate->getPosition();
													$classLabel = $serpPosition > 30 ? 'bg-danger' : 'bg-success';
												?>
												<span class="badge <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
				<canvas id="datestats-chart"></canvas>
			</div>
		</div>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="google.display" />
	<input type="hidden" name="googlestats" value="webmasters" />
	<input type="hidden" name="sitemapurl" value="" />
	<input type="hidden" name="crawlerrors_category" value="" />
</form>

<!-- MODAL DIALOG FOR GWT SITEMAP DELETION -->
<div id="sitemapDeleteModal" class="jmapmodal modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
		<label data-bs-dismiss="modal" aria-label="Close" class="closeprecaching fas fa-times-circle"></label>
        <h4 class="modal-title"><?php echo Text::_('COM_JMAP_DELETE_THIS_SITEMAP');?></h4>
      </div>
      <div class="modal-body modal-body-padded">
      	<?php echo Text::_('COM_JMAP_DELETE_THIS_SITEMAP_AREYOUSURE');?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('COM_JMAP_CANCEL');?></button>
        <button type="button" data-role="confirm-delete" class="btn btn-primary bg-primary"><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_DELETE');?></button>
      </div>
    </div>
  </div>
</div>