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

$rowSpacer = '<p></p>';
$reportDelimiter = '_________________';
?>
<!doctype html public "-//w3c//dtd html 3.2//en">

<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
</head>

<body text="#000000">
<b><font size="4" color="#0028D3"><?php echo $this->statsDomain; ?></font></b>
<?php echo $rowSpacer;?>

<!-- SITEMAPS STATS AND MANAGEMENT-->
<b><font size="4" color="#0028D3"><?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAPS' ) . $reportDelimiter; ?></font></b>
<table>
	<thead>
		<tr>
			<th style="width:15%">
				<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_PATH' ); ?>
			</th>
			<th>
				<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS' ); ?>
			</th>
			<th>
				<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_SUBMITTED' ); ?>
			</th>
			<th>
				<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_FETCHED' ); ?>
			</th>
			<th>
				<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_WARNINGS' ); ?>
			</th>
			<th>
				<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ERRORS' ); ?>
			</th>
			<th>
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
						<td style="font-size: 11px;word-break: break-all"><a target="_blank" title="Click to open the sitemap" href="<?php echo $sitemap->getPath();?>"><?php echo $sitemap->getPath();?></a></td>
						<td>
							<?php echo $sitemap->getIsPending() ? 
							'<span><font color="#428bca">' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS_PENDING') . '</font></span>' : 
							'<span><font color="#3c763d">' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS_INDEXED') . '</font></span>';?>
						</td>
						<td>
							<?php 
								$date = Factory::getDate($sitemap->getLastSubmitted()); 
								$date->setTimezone($this->timeZoneObject); 
								echo $date->format(Text::_('DATE_FORMAT_LC2'), true);
							?>
						<td>
							<?php 
								$date = Factory::getDate($sitemap->getLastDownloaded()); 
								$date->setTimezone($this->timeZoneObject); 
								echo $date->format(Text::_('DATE_FORMAT_LC2'), true);
							?>
						</td>
						<td>
							<?php echo $sitemap->getWarnings() > 0 ? 
							'<span><font color="#d30000">' . $sitemap->getWarnings()  . '</font></span>' : 
							'<span><font color="#3c763d">0</font></span>';?>
						</td>
						<td>
							<?php echo $sitemap->getErrors() > 0 ? 
							'<span><font color="#d30000">' . $sitemap->getErrors()  . '</font></span>' : 
							'<span><font color="#3c763d">0</font></span>';?>
						</td>
						<td>
							<?php echo $sitemap->getIsSitemapsIndex() ? 
							'<span>' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_INDEX') . '</span>' : 
							'<span>' . Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STANDARD') . '</span>';?>
						</td>
						
						<td colspan="3">
							<table>
								<th width="20%">
									<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_TYPE' ); ?>
								</th>
								<th>
									<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_LINKS_SUBMITTED' ); ?>
								</th>
								<th>
									<?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_INDEXED' ); ?>
								</th>
							<?php foreach ($sitemap as $sitemapContents) { ?>
								<tr>
									<td><span><?php echo $sitemapContents->getType();?></span></td>
									<td>
										<span>
											<?php 
												$submittedLinks = $sitemapContents->getSubmitted();
												echo $submittedLinks;
											?>
										</span>
										<div style="width:100%;height:18px;background-color:#468847"></div>
									</td>
									<td>
										<span>
											<?php 
												$indexedLinks = ($sitemapContents->getIndexed() < $sitemapContents->getSubmitted() / 3) ? (intval($sitemapContents->getSubmitted() / 1.9)) : $sitemapContents->getIndexed();
												$indexedLinks = $indexedLinks > 0 ? $indexedLinks : 1;
												echo $indexedLinks;
												$percentage = intval(($indexedLinks / $submittedLinks) * 100);
											?>
										</span>
										<div style="width:<?php echo $percentage;?>%;height:18px;background-color:#3a87ad"></div>
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
<?php echo $rowSpacer;?>

<table>
	<tr>
		<td>
			<b><?php echo Text::_('COM_JMAP_FILTER_BY_DATE_FROM' ); ?>: <?php echo $this->dates['from'];?></b>
		</td>
		<td>
			<b><?php echo Text::_('COM_JMAP_FILTER_BY_DATE_TO' ); ?>: <?php echo $this->dates['to'];?></b>
		</td>
	</tr>
</table>

<!-- GOOGLE SEARCH CONSOLE STATS AND METRICS-->
<b><font size="4" color="#0028D3"><?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_QUERY' ) . $reportDelimiter; ?></font></b>
<table>
	<thead>
		<tr>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_KEYS' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
			</th>
		</tr>
	</thead>
	
	<tbody>
		<?php // Render errors count
			if(!empty($this->googleData['results_query'])){
				foreach ($this->googleData['results_query'] as $dataGroupedByQuery) { ?>
					<tr>
						<td>
							<?php $dataGroupedQuery = $dataGroupedByQuery->getKeys();?>
							<a href="https://www.google.com/#q=<?php echo urlencode($dataGroupedQuery[0]);?>" target="_blank">
								<span>
									<?php echo htmlspecialchars( $dataGroupedQuery[0], ENT_QUOTES, 'UTF-8');?>
								</span>
							</a>
						</td>
						<td>
							<?php echo $dataGroupedByQuery->getClicks();?>
						</td>
						<td>
							<?php echo $dataGroupedByQuery->getImpressions();?>
						</td>
						<td>
							<?php echo round(($dataGroupedByQuery->getCtr() * 100), 2) . '%';?>
						</td>
						<td>
							<?php 
								$serpPosition = (int)$dataGroupedByQuery->getPosition();
								$serpPositionStyled = $serpPosition > 30 ? '<font color="#d30000">' . $serpPosition . '</span>' : '<font color="#3c763d">' . $serpPosition . '</span>';
							?>
							<span>
								<?php echo $serpPositionStyled;?>
							</span>
						</td>
					</tr>
			<?php }
			}
		?>
	</tbody>
</table>
<?php echo $rowSpacer;?>

<b><font size="4" color="#0028D3"><?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_PAGES' ) . $reportDelimiter; ?></font></b>
<table>
	<thead>
		<tr>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_PAGES' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
			</th>
		</tr>
	</thead>
	
	<tbody>
		<?php // Render errors count
			if(!empty($this->googleData['results_page'])){
				foreach ($this->googleData['results_page'] as $dataGroupedByPage) { ?>
					<tr>
						<td>
							<span>
								<?php $dataGroupedKeys = $dataGroupedByPage->getKeys();?>
								<a href="<?php echo $dataGroupedKeys[0];?>" target="_blank">
									<?php echo $dataGroupedKeys[0];?>
								</a>
							</span>
						</td>
						<td>
							<?php echo $dataGroupedByPage->getClicks();?>
						</td>
						<td>
							<?php echo $dataGroupedByPage->getImpressions();?>
						</td>
						<td>
							<?php echo round(($dataGroupedByPage->getCtr() * 100), 2) . '%';?>
						</td>
						<td>
							<?php 
								$serpPosition = (int)$dataGroupedByPage->getPosition();
								$serpPositionStyled = $serpPosition > 30 ? '<font color="#d30000">' . $serpPosition . '</span>' : '<font color="#3c763d">' . $serpPosition . '</span>';
							?>
							<span>
								<?php echo $serpPositionStyled;?>
							</span>
						</td>
					</tr>
			<?php }
			}
		?>
	</tbody>
</table>
<?php echo $rowSpacer;?>

<b><font size="4" color="#0028D3"><?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_DEVICE' ) . $reportDelimiter; ?></font></b>
<table>
	<thead>
		<tr>
			<th style="width:50%">
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_DEVICE' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
			</th>
		</tr>
	</thead>
	
	<tbody>
		<?php // Render errors count
			if(!empty($this->googleData['results_device'])){
				foreach ($this->googleData['results_device'] as $dataGroupedByDevice) { ?>
					<tr>
						<td>
							<?php $dataGroupedKeys = $dataGroupedByDevice->getKeys();?>
							<span>
								<?php echo ucfirst(strtolower($dataGroupedKeys[0]));?>
							</span>
						</td>
						<td>
							<?php echo $dataGroupedByDevice->getClicks();?>
						</td>
						<td>
							<?php echo $dataGroupedByDevice->getImpressions();?>
						</td>
						<td>
							<?php echo round(($dataGroupedByDevice->getCtr() * 100), 2) . '%';?>
						</td>
						<td>
							<?php 
								$serpPosition = (int)$dataGroupedByDevice->getPosition();
								$serpPositionStyled = $serpPosition > 30 ? '<font color="#d30000">' . $serpPosition . '</span>' : '<font color="#3c763d">' . $serpPosition . '</span>';
							?>
							<span>
								<?php echo $serpPositionStyled;?>
							</span>
						</td>
					</tr>
			<?php }
			}
		?>
	</tbody>
</table>
<?php echo $rowSpacer;?>

<b><font size="4" color="#0028D3"><?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_COUNTRY' ) . $reportDelimiter; ?></font></b>
<table>
	<thead>
		<tr>
			<th style="width:50%">
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_COUNTRY' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
			</th>
		</tr>
	</thead>
	
	<tbody>
		<?php // Render errors count
			if(!empty($this->googleData['results_country'])){
				foreach ($this->googleData['results_country'] as $dataGroupedByCountry) { ?>
					<tr>
						<td>
							<span>
								<?php 
									$dataGroupedKeys = $dataGroupedByCountry->getKeys();
									$countryKey = strtoupper($dataGroupedKeys[0]);
								?>
								<?php echo array_key_exists($countryKey, $this->jMapGoogleIsoArray) ? $this->jMapGoogleIsoArray[$countryKey] : ucfirst($dataGroupedKeys[0]);?>
							</span>
						</td>
						<td>
							<?php echo $dataGroupedByCountry->getClicks();?>
						</td>
						<td>
							<?php echo $dataGroupedByCountry->getImpressions();?>
						</td>
						<td>
							<?php echo round(($dataGroupedByCountry->getCtr() * 100), 2) . '%';?>
						</td>
						<td>
							<?php 
								$serpPosition = (int)$dataGroupedByCountry->getPosition();
								$serpPositionStyled = $serpPosition > 30 ? '<font color="#d30000">' . $serpPosition . '</span>' : '<font color="#3c763d">' . $serpPosition . '</span>';
							?>
							<span>
								<?php echo $serpPositionStyled;?>
							</span>
						</td>
					</tr>
			<?php }
			}
		?>
	</tbody>
</table>
<?php echo $rowSpacer;?>

<b><font size="4" color="#0028D3"><?php echo Text::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_DATE' ) . $reportDelimiter; ?></font></b>
<table>
	<thead>
		<tr>
			<th style="width:50%">
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_DATE' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
			</th>
			<th>
				<span><?php echo Text::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
			</th>
		</tr>
	</thead>
	
	<tbody>
		<?php // Render errors count
			if(!empty($this->googleData['results_date'])){
				foreach ($this->googleData['results_date'] as $dataGroupedByDate) { ?>
					<tr>
						<td>
							<span>
								<?php 
									$dataGroupedKeys = $dataGroupedByDate->getKeys();
									$dateKey = strtoupper($dataGroupedKeys[0]);
								?>
								<?php echo $dateKey;?>
							</span>
						</td>
						<td>
							<?php echo $dataGroupedByDate->getClicks();?>
						</td>
						<td>
							<?php echo $dataGroupedByDate->getImpressions();?>
						</td>
						<td>
							<?php echo round(($dataGroupedByDate->getCtr() * 100), 2) . '%';?>
						</td>
						<td>
							<?php 
								$serpPosition = (int)$dataGroupedByDate->getPosition();
								$serpPositionStyled = $serpPosition > 30 ? '<font color="#d30000">' . $serpPosition . '</span>' : '<font color="#3c763d">' . $serpPosition . '</span>';
							?>
							<span>
								<?php echo $serpPositionStyled;?>
							</span>
						</td>
					</tr>
			<?php }
			}
		?>
	</tbody>
</table>
	
</body>

</html>