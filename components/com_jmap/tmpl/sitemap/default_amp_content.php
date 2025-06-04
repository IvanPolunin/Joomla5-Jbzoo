<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

$priority =  $this->sourceparams->get ( 'priority', '0.5' );
$changefreq = $this->sourceparams->get ( 'changefreq', 'daily' );
$excludePriority = $this->cparams->get ( 'disable_priority', 0 );
$excludeChangefreq = $this->cparams->get ( 'disable_changefreq', 0 );
$showPageBreaks = $this->cparams->get ( 'show_pagebreaks', 1 );

// Get default menu - home and check if a single article is linked, if so skip to avoid duplicated content
$homeArticleID = false;
$defaultMenu = $this->application->getMenu()->getDefault($this->app->getLanguage()->getTag());
if(	isset($defaultMenu->query['option']) &&
	isset($defaultMenu->query['view']) &&
	$defaultMenu->query['option'] == 'com_content' &&
	$defaultMenu->query['view'] == 'article') {
	$homeArticleID = (int)$defaultMenu->query['id'];
}

if (count ( $this->source->data ) != 0) {
	foreach ( $this->source->data as $elm ) {
		// Element category empty da right join
		if(!$elm->id) {
			continue;
		}
		
		// Article found as linked to home, skip and avoid duplicate link
		if((int)$elm->id === $homeArticleID) {
			continue;
		}
		
		$elm->slug = $elm->alias ? ($elm->id . ':' . $elm->alias) : $elm->id;
		$seolink = \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $elm->slug, $elm->catslug, $elm->language ) );

		// Skip outputting
		if(array_key_exists($seolink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$seolink] = true;
		
		// Fallback modified -> created -> current time
		$timestampModified = (isset($elm->modified) && $elm->modified != false && $elm->modified != -1) ? $elm->modified : false;
		$timestampCreated = (isset($elm->created) && $elm->created != false && $elm->created != -1) ? $elm->created : false;
		$timestamp = $timestampModified ? $timestampModified : ($timestampCreated ? $timestampCreated : time());
		$modified = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
		?>
<url>
<loc><?php echo $this->sefSuffixEnabled ? $this->liveSite . str_ireplace('.html', '.' . $this->ampSuffix . '.html', $seolink) : $this->liveSite . $seolink . '/' . $this->ampSuffix; ?></loc>
<lastmod><?php echo $modified; ?></lastmod>
<?php if(!$excludeChangefreq):?>
<changefreq><?php echo $changefreq;?></changefreq>
<?php endif;?>
<?php if(!$excludePriority):?>
<priority><?php echo $elm->priority ? $elm->priority : $priority;?></priority>
<?php endif;?>
</url>
<?php 
	}
}