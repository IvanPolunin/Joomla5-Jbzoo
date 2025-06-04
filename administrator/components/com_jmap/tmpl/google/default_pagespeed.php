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
use Joomla\String\StringHelper;

$arrayLabels = array (
	'AVERAGE' => '-warning',
	'FAST' => '-success',
	'NONE' => '-primary',
	'SLOW' => '-danger'
);
$strategy = $this->getModel()->getComponentParams()->get('links_analyzer_pagespeed_insights_analysis_strategy', 'desktop');

$successTest = '<span data-bs-content="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUCCESS_DESC') . '" aria-label="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUCCESS_DESC') . '" style="cursor:pointer" class="hasPopover fas fa-check fa-custom-icon-green fa-custom-icon-large"></span>';
$failedTest = '<span data-bs-content="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FAILURE_DESC') . '" aria-label="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FAILURE_DESC') . '" style="cursor:pointer" class="hasPopover fas fa-times fa-custom-icon-red fa-custom-icon-large"></span>';
$notavailableTest = '<span data-bs-content="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_NOTAVAILABLE_DESC') . '" aria-label="' . Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_NOTAVAILABLE_DESC') . '" style="cursor:pointer" class="hasPopover fas fa-minus fa-custom-icon-gray fa-custom-icon-large"></span>';
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="jmap-pagespeed">
	<table class="full headerlist">
		<tr>
			<td>
				<div class="pagespeed-pagelink">
					<span class="input-group">
						<span class="input-group-text" aria-label="<?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_REPORT_PAGE_LINK' ); ?>"><span class="fas fa-link" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_REPORT_PAGE_LINK' ); ?></span>
						<input type="text" class="inputbox-large" name="pagespeed_pageurl" value="<?php echo $this->statsDomain; ?>" data-validation="required url"  class="text_area"/>
					</span>
					<button id="pagespeed_start" class="btn btn-primary btn-sm"><?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_REPORT_TEST_LINK' ); ?></button>
					<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_STRATEGY');?>" class='badge bg-primary hasPopover pull-right'>
						<?php echo $strategy == 'desktop' ? Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_STRATEGY_DESKTOP') : Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_STRATEGY_MOBILE');?>
					</span>
				</div>
			</td>
		</tr>
	</table>
	
	<?php if(isset($this->pageSpeedError)):?>
		<input type="hidden" name="option" value="<?php echo $this->option;?>" />
		<input type="hidden" name="task" value="google.display" />
		<input type="hidden" name="googlestats" value="pagespeedfetch" />
	</form>
	<?php 
	return;
	endif;
	?>
	
	<!-- PAGESPEED OVERVIEW REPORT BY API RESPONSE-->
	<div class="card card-info card-group-google" id="jmap_google_pagespeed_summary_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_google_pagespeed_summary">
			<h4><span class="fas fa-eye" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY' ); ?></h4>
		</div>
		<div id="jmap_google_pagespeed_summary" class="collapse card-body card-block">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th class="title" width="15%">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCREENSHOT' ); ?>
						</th>
						<th class="title" width="15%">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE' ); ?>
						</th>
						<th class="title hidden-xs-phone">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_VOTE' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php 
						// Calculate the score category, range, colors for labels and sliders
						$maxClass = null;
						$absoluteScore = isset($this->googleData['lighthouseResult']['categories']['performance']) ? (int)($this->googleData['lighthouseResult']['categories']['performance']['score'] * 100) : -1;
						if($absoluteScore >= 0 && $absoluteScore <= 49) {
							$summaryScoreVoteText = Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_SLOW');
							$summaryScoreVoteLabel = $arrayLabels['SLOW'];
							if($absoluteScore < 10) {
								$maxClass = 'score-min';
							}
						} elseif ($absoluteScore >= 50 && $absoluteScore <= 89) {
							$summaryScoreVoteText = Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_AVERAGE');
							$summaryScoreVoteLabel = $arrayLabels['AVERAGE'];
						} elseif ($absoluteScore >= 90 && $absoluteScore <= 100) {
							$summaryScoreVoteText = Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_FAST');
							$summaryScoreVoteLabel = $arrayLabels['FAST'];
							if($absoluteScore == 100) {
								$maxClass = 'score-max';
							}
						} else {
							$summaryScoreVoteText = Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_NONE');
							$summaryScoreVoteLabel = $arrayLabels['NONE'];
						}
					?>
					<tr>
						<td class="align-center" style="vertical-align: middle">
							<?php if(isset($this->googleData['lighthouseResult']['audits']['final-screenshot'])):?>
								<img class="pagespeed-preview" src="<?php echo $this->googleData['lighthouseResult']['audits']['final-screenshot']['details']['data'];?>" alt="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCREENSHOT')?>"/>
							<?php endif;?>
						</td>
						<td class="align-center" style="vertical-align: middle">
							<div class="pagespeed-performance-score hasPopover badge bg<?php echo $summaryScoreVoteLabel;?>" data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_DESC');?>">
								<span class="<?php echo $maxClass;?>"><?php echo $absoluteScore;?></span>
							</div>
						</td>
						<td class="align-center hidden-xs-phone" style="vertical-align: middle">
							<div>
								<div class="pagespeed_slider">
									<div class="inner_slider slider<?php echo $summaryScoreVoteLabel;?> hasPopover" style="width: <?php echo $absoluteScore;?>%;" data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_VOTE_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SUMMARY_SCORE_VOTE_DESC');?>"><?php echo $summaryScoreVoteText;?></div>
								</div>
								<div class="pagespeed_slider_legend">
									<span class="pagespeed_slider_legend_danger">0-49</span>
									<span class="pagespeed_slider_legend_warning">50-89</span>
									<span class="pagespeed_slider_legend_success">90-100</span>
								</div>
							</div>
						</td>
				</tbody>
			</table>
		</div>
	</div>
	
	<!-- PAGESPEED PERFORMANCE REPORT BY API RESPONSE-->
	<div class="card card-info card-group-google" id="jmap_google_pagespeed_performance_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_google_pagespeed_performance">
			<h4><span class="fas fa-tachometer-alt" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_PERFORMANCE_REPORT' ); ?></h4>
		</div>
		<div id="jmap_google_pagespeed_performance" class="collapse card-body card-block">
			<table class="adminlist table table-striped table-hover table-centered">
				<thead>
					<tr>
						<th class="title corewebvitals">
							<a href="https://web.dev/lcp/" target="_blank" class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_PERFORMANCE_REPORT_WEBVITALS');?>">
								<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_LCP' ); ?>
							</a>
						</th>
						<th class="title corewebvitals">
							<a href="https://web.dev/cls/" target="_blank" class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_PERFORMANCE_REPORT_WEBVITALS');?>">
								<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_CLS' ); ?>
							</a>
						</th>
						<?php if(isset($this->googleData['loadingExperience']['metrics'])):?>
							<th class="title title-widthed corewebvitals">
								<a href="https://web.dev/fid/" target="_blank" class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_PERFORMANCE_REPORT_WEBVITALS');?>">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FID' ); ?>
								</a>
							</th>
							<th class="title title-widthed corewebvitals">
								<a href="https://web.dev/fid/" target="_blank" class="hasTooltip" title="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_PERFORMANCE_REPORT_WEBVITALS');?>">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_INP' ); ?>
								</a>
							</th>
							<th class="title d-none d-md-table-cell">
								<a href="https://web.dev/first-contentful-paint/" target="_blank">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_AVERAGE_FCP' ); ?>
								</a>
							</th>
						<?php endif;?>
						<?php if(isset($this->googleData['lighthouseResult']['audits'])):?>
							<th class="title hidden-xs-phone d-none d-md-table-cell">
								<a href="https://web.dev/first-contentful-paint/" target="_blank">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FCP' ); ?>
								</a>
							</th>
							<th class="title d-none d-md-table-cell">
								<a href="https://web.dev/speed-index/" target="_blank">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SPEED_INDEX' ); ?>
								</a>
							</th>
							<th class="title d-none d-md-table-cell">
								<a href="https://web.dev/first-meaningful-paint/" target="_blank">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FMP' ); ?>
								</a>
							</th>
							<th class="title d-none d-lg-table-cell">
								<a href="https://web.dev/time-to-first-byte/" target="_blank">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FCI' ); ?>
								</a>
							</th>
							<th class="title d-none d-lg-table-cell">
								<a href="https://web.dev/interactive/" target="_blank">
									<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_TTI' ); ?>
								</a>
							</th>
							<th class="title hidden-smalldesktop">
								<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_JSEXEC' ); ?>
							</th>
						<?php endif;?>
					</tr>
				</thead>

				<tbody>
					<tr>
						<?php 
							if(isset($this->googleData['loadingExperience']['metrics'])) {
								$lcpValue = number_format($this->googleData['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['percentile'] / 1000, 1);
								$lcpScoreVote = $this->googleData['loadingExperience']['metrics']['LARGEST_CONTENTFUL_PAINT_MS']['category'];
								$clsValue = $this->googleData['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] / 100;
								$clsScoreVote = $this->googleData['loadingExperience']['metrics']['CUMULATIVE_LAYOUT_SHIFT_SCORE']['category'];
							} else {
								$lcpValue = number_format($this->googleData['lighthouseResult']['audits']['largest-contentful-paint']['numericValue'] / 1000, 1);
								if($lcpValue <= 2.5) {
									$lcpScoreVote = 'FAST';
								} elseif($lcpValue > 2.5 && $lcpValue <= 4.0) {
									$lcpScoreVote = 'AVERAGE';
								} elseif($lcpValue > 4.0) {
									$lcpScoreVote = 'SLOW';
								}
								$clsValue = number_format($this->googleData['lighthouseResult']['audits']['cumulative-layout-shift']['numericValue'], 3);
								if($clsValue <= 0.1) {
									$clsScoreVote = 'FAST';
								} elseif($clsValue > 0.1 && $clsValue <= 0.25) {
									$clsScoreVote = 'AVERAGE';
								} elseif($clsValue > 0.25) {
									$clsScoreVote = 'SLOW';
								}
							}
						?>
						<td>
							<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_LCP_DESC');?>" class="hasPopover badge bg-primary"><?php echo $lcpValue; ?> s</span>
							<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_LCP_DESC');?>" class="hasPopover badge badge<?php echo $arrayLabels[$lcpScoreVote];?> label-score"><?php echo $lcpScoreVote; ?></span>
						</td>
						<td>
							<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_CLS_DESC');?>" class="hasPopover badge bg-primary"><?php echo $clsValue; ?></span>
							<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_CLS_DESC');?>" class="hasPopover badge badge<?php echo $arrayLabels[$clsScoreVote];?> label-score"><?php echo $clsScoreVote; ?></span>
						</td>
						<?php if(isset($this->googleData['loadingExperience']['metrics'])):?>
							<td>
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FID_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FID');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['percentile']; ?> ms</span>
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FID_DESC');?>" class="hasPopover badge bg<?php echo $arrayLabels[$this->googleData['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category']];?> label-score"><?php echo $this->googleData['loadingExperience']['metrics']['FIRST_INPUT_DELAY_MS']['category']; ?></span>
							</td>
							<td>
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_INP_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_INP');?>" class="hasPopover badge bg-primary bg-nowrap"><?php echo $this->googleData['loadingExperience']['metrics']['INTERACTION_TO_NEXT_PAINT']['percentile']; ?> ms</span>
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_INP_DESC');?>" class="hasPopover badge bg<?php echo $arrayLabels[$this->googleData['loadingExperience']['metrics']['INTERACTION_TO_NEXT_PAINT']['category']];?> label-score"><?php echo $this->googleData['loadingExperience']['metrics']['INTERACTION_TO_NEXT_PAINT']['category']; ?></span>
							</td>
							<td class="d-none d-md-table-cell">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_AVERAGE_FCP_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_AVERAGE_FCP');?>" class="hasPopover badge bg-primary bg-nowrap"><?php echo number_format($this->googleData['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['percentile'] / 1000, 2); ?> s</span>
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_AVERAGE_FCP_DESC');?>" class="hasPopover badge bg<?php echo $arrayLabels[$this->googleData['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['category']];?> label-score"><?php echo $this->googleData['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['category']; ?></span>
							</td>
						<?php endif;?>
						<?php if(isset($this->googleData['lighthouseResult']['audits'])):?>
							<td class="hidden-xs-phone d-none d-md-table-cell">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FCP_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FCP');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['lighthouseResult']['audits']['first-contentful-paint']['displayValue'];?></span>
							</td>
							<td class="d-none d-md-table-cell">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SPEED_INDEX_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SPEED_INDEX');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['lighthouseResult']['audits']['speed-index']['displayValue'];?></span>
							</td>
							<td class="d-none d-md-table-cell">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FMP_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FMP');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['lighthouseResult']['audits']['first-meaningful-paint']['displayValue'];?></span>
							</td>
							<td class="d-none d-lg-table-cell">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FCI_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_FCI');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['lighthouseResult']['audits']['server-response-time']['displayValue'];?></span>
							</td>
							<td class="d-none d-lg-table-cell">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_TTI_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_TTI');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['lighthouseResult']['audits']['interactive']['displayValue'];?></span>
							</td>
							<td class="hidden-smalldesktop">
								<span data-bs-content="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_JSEXEC_DESC');?>" aria-label="<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_JSEXEC');?>" class="hasPopover badge bg-primary"><?php echo $this->googleData['lighthouseResult']['audits']['bootup-time']['displayValue'];?></span>
							</td>
						<?php endif;?>
					</tr>
				</tbody>
			</table>

			<table class="adminlist table table-striped">
				<tbody>
					<tr>
						<th class="title" width="50%">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_AUDIT_TEST_TITLE' ); ?>
						</th>
						<th class="title" width="50%">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_AUDIT_TEST_STATUS' ); ?>
						</th>
					</tr>

					<?php
					$arrayPerformanceReportTests = array(
							array(	'testName' => 'render-blocking-resources',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_RENDER_BLOCKING_RESOURCES',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_RENDER_BLOCKING_RESOURCES_DESC'
							),
							array(	'testName' => 'uses-optimized-images',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_USES_OPTIMIZED_IMAGES',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_USES_OPTIMIZED_IMAGES_DESC'
							),
							array(	'testName' => 'uses-text-compression',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_USES_TEXT_COMPRESSION',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_USES_TEXT_COMPRESSION_DESC'
							),
							array(	'testName' => 'offscreen-images',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_OFFSCREEN_IMAGES',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_OFFSCREEN_IMAGES_DESC'
							),
							array(	'testName' => 'unminified-css',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_UNMINIFIED_CSS',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_UNMINIFIED_CSS_DESC'
							),
							array(	'testName' => 'unminified-javascript',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_UNMINIFIED_JAVASCRIPT',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_UNMINIFIED_JAVASCRIPT_DESC'
							),
							array(	'testName' => 'redirects',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_REDIRECTS',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_REDIRECTS_DESC'
							),
							array(	'testName' => 'server-response-time',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_TTFB',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_TTFB_DESC'
							),
							array(	'testName' => 'max-potential-fid',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_MAX_POTENTIAL_FID',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_MAX_POTENTIAL_FID_DESC'
							),
							array(	'testName' => 'third-party-summary',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_THIRD_PARTY_SUMMARY',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_THIRD_PARTY_SUMMARY_DESC'
							),
							array(	'testName' => 'total-blocking-time',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_TBT',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_TBT_DESC'
							),
							array(	'testName' => 'efficient-animated-content',
									'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_EAC',
									'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_EAC_DESC'
							)
					);
					
					if(isset($this->googleData['lighthouseResult']['audits'])):
						foreach ($arrayPerformanceReportTests as $testToExec):?>
							<tr>
								<td>
									<span data-bs-content="<?php echo Text::_($testToExec['testDescription']);?>" class="hasPopover badge bg-primary label-small"><?php echo Text::_($testToExec['testTitle']); ?></span>
								</td>
								<td>
									<?php if($this->googleData['lighthouseResult']['audits'][$testToExec['testName']]['score'] > 0) {
										echo $successTest;
									} elseif($this->googleData['lighthouseResult']['audits'][$testToExec['testName']]['score'] == '0') {
										echo $failedTest;
									} elseif($this->googleData['lighthouseResult']['audits'][$testToExec['testName']]['score'] == '') {
										echo $notavailableTest;
									}
									?>
								</td>
							</tr>
						<?php
						endforeach;
					endif;
					?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- PAGESPEED ASSETS REPORT BY API RESPONSE-->
	<div class="card card-info card-group-google" id="jmap_google_pagespeed_assets_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_google_pagespeed_assets">
			<h4><span class="fas fa-list" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_ASSETS_REPORT' ); ?></h4>
		</div>
		<div id="jmap_google_pagespeed_assets" class="collapse card-body card-block">
			<table class="adminlist table table-striped table-hover table-sorter">
				<thead>
					<tr>
						<th class="title">
							<span><?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_ASSETS_REPORT_URL' ); ?></span>
						</th>
						<th class="title d-none d-md-table-cell">
							<span><?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_ASSETS_REPORT_TYPE' ); ?></span>
						</th>
						<th class="title d-none d-md-table-cell">
							<span><?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_ASSETS_REPORT_MIMETYPE' ); ?></span>
						</th>
						<th class="title d-none d-md-table-cell">
							<span><?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_ASSETS_REPORT_STATUSCODE' ); ?></span>
						</th>
						<th class="title">
							<span><?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_ASSETS_REPORT_SIZE' ); ?></span>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php
					if(isset($this->googleData['lighthouseResult']['audits'])):
						foreach($this->googleData['lighthouseResult']['audits']['network-requests']['details']['items'] as $asset):?>
							<tr>
								<td class="report-url">
									<?php echo $asset['url'];?>
								</td>
								<td class="d-none d-md-table-cell">
									<span class="badge bg-primary label-small"><?php echo isset($asset['resourceType']) ? Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_ASSET_' . StringHelper::strtoupper(StringHelper::str_ireplace('_', '', $asset['resourceType']))) : Text::_('COM_JMAP_NA'); ?></span>
								</td>
								<td class="d-none d-md-table-cell">
									<span class="badge bg-primary label-small"><?php echo isset($asset['mimeType']) ? $asset['mimeType'] : Text::_('COM_JMAP_NA'); ?></span>
								</td>
								<td class="d-none d-md-table-cell">
									<?php 
										$assetClass = 'primary';
										$statusCode = (int)$asset['statusCode'];
										if($statusCode <= 0) {
											$statusCode = Text::_('COM_JMAP_NA');
											$assetClass = 'primary';
										}elseif($statusCode > 0 && $statusCode < 300) {
											$assetClass = 'success';
										} elseif ($statusCode >= 300 && $statusCode < 500) {
											$assetClass = 'warning';
										} elseif ($statusCode >= 500) {
											$assetClass = 'danger';
										}
									?>
									<span class="badge bg-<?php echo $assetClass;?> label-small"><?php echo $statusCode; ?></span>
								</td>
								
								<td>
									<span class="badge bg-primary label-small"><?php echo number_format($asset['transferSize'] / 1024); ?></span>
								</td>
							</tr>
						<?php
						endforeach;
					endif;
					?>
				</tbody>
			</table>
		</div>
	</div>
	
	<!-- PAGESPEED SEO REPORT BY API RESPONSE-->
	<div class="card card-info card-group-google" id="jmap_google_pagespeed_seo_accordion">
		<div class="card-header accordion-toggle accordion_lightblue" data-bs-toggle="collapse" data-bs-target="#jmap_google_pagespeed_seo">
			<h4><span class="fas fa-sliders-h" aria-hidden="true"></span> <?php echo Text::_ ('COM_JMAP_GOOGLE_PAGESPEED_SEO_REPORT' ); ?></h4>
		</div>
		<div id="jmap_google_pagespeed_seo" class="collapse card-body card-block">
			<table class="adminlist table table-striped table-hover">
					<?php 
					$arraySeoReportTests = array(
						array(	'testName' => 'document-title',
							  	'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_DOCUMENT_TITLE',
							  	'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_DOCUMENT_TITLE_DESC'
						),
						array(	'testName' => 'meta-description',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_META_DESCRIPTION',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_META_DESCRIPTION_DESC'
						),
						array(	'testName' => 'viewport',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_VIEWPORT',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_VIEWPORT_DESC'
						),
						array(	'testName' => 'canonical',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_CANONICAL',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_CANONICAL_DESC'
						),
						array(	'testName' => 'hreflang',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_HREFLANG',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_HREFLANG_DESC'
						),
						array(	'testName' => 'is-crawlable',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_ISCRAWABLE',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_ISCRAWABLE_DESC'
						),
						array(	'testName' => 'modern-image-formats',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_MODERN_IMAGE_FORMATS',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_MODERN_IMAGE_FORMATS_DESC'
						),
						array(	'testName' => 'crawlable-anchors',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_CRAWABLEANCHORS',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_CRAWABLEANCHORS_DESC'
						),
						array(	'testName' => 'no-document-write',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_NODOCUMENTWRITE',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_NODOCUMENTWRITE_DESC'
						),
						array(	'testName' => 'robots-txt',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_ROBOTSTXT',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_ROBOTSTXT_DESC'
						),
						array(	'testName' => 'link-text',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_LINKTEXT',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_LINKTEXT_DESC'
						),
						array(	'testName' => 'http-status-code',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_HTTP_STATUS_CODE',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_HTTP_STATUS_CODE_DESC'
						),
						array(	'testName' => 'image-alt',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_IMAGEALT',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_IMAGEALT_DESC'
						),
						array(	'testName' => 'structured-data',
								'testTitle' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_STRUCTURED_DATA',
								'testDescription' => 'COM_JMAP_GOOGLE_PAGESPEED_REPORT_STRUCTURED_DATA_DESC'
						)
				);?>
				
				<thead>
					<tr>
						<th class="title">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SEO_TEST_TITLE' ); ?>
						</th>
						<th class="title">
							<?php echo Text::_('COM_JMAP_GOOGLE_PAGESPEED_REPORT_SEO_TEST_STATUS' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php
					if(isset($this->googleData['lighthouseResult']['audits'])):
						foreach ($arraySeoReportTests as $testToExec):?>
							<tr>
								<td>
									<span data-bs-content="<?php echo Text::_($testToExec['testDescription']);?>" class="hasPopover badge bg-primary label-small"><?php echo Text::_($testToExec['testTitle']); ?></span>
								</td>
								<td>
									<?php if($this->googleData['lighthouseResult']['audits'][$testToExec['testName']]['score'] > 0) {
										echo $successTest;
									} elseif($this->googleData['lighthouseResult']['audits'][$testToExec['testName']]['score'] == '0') {
										echo $failedTest;
									} elseif($this->googleData['lighthouseResult']['audits'][$testToExec['testName']]['score'] == '') {
										echo $notavailableTest;
									}
									?>
								</td>
							</tr>
						<?php
						endforeach;
					endif;
					?>
				</tbody>
			</table>
		</div>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="google.display" />
	<input type="hidden" name="googlestats" value="pagespeedfetch" />
</form>