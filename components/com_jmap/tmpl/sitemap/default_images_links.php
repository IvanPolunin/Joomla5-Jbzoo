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

$priority =  $this->sourceparams->get ( 'priority', '0.5' );
$changefreq = $this->sourceparams->get ( 'changefreq', 'daily' );
$imagetitleProcessorRegexp = $this->sourceparams->get('imagetitle_processor', $this->cparams->get('imagetitle_processor', 'title|alt'));
$fakeImages = $this->cparams->get ( 'fake_images_processor', 0 );
$lazyloadImages = $this->cparams->get ( 'lazyload_images_processor', 0 );
$linkableCatsMode = $this->sourceparams->get ( 'linkable_content_cats', 1 );
$includeDescriptionOnly = $this->cparams->get ( 'include_description_only', 0 );
$cdnProtocol = $this->cparams->get ('cdnprotocol', '');
$filtersMode = $this->cparams->get ('images_filters_mode', 'tag');

// Init exclusion
$imgFilterInclude = array();
if(trim($this->sourceparams->get ( 'images_filter_include', '' ))) {
	$imgFilterInclude = explode(',', $this->sourceparams->get ( 'images_filter_include', '' ));
}
$imgFilterExclude = array();
if(trim($this->sourceparams->get ( 'images_filter_exclude', 'pdf,print,email,templates' ))) {
	$imgFilterExclude = explode(',', $this->sourceparams->get ( 'images_filter_exclude', 'pdf,print,email,templates' ));
}

// Init and merge global inclusions filters data sources wide
if($globalImagesFilterInclude = trim($this->cparams->get ( 'images_global_filter_include', '' ))) {
	$imgGlobalFilterInclude = explode(',', $globalImagesFilterInclude);
	$imgFilterInclude = array_merge($imgFilterInclude, $imgGlobalFilterInclude);
}
// Init and merge global exclusions filters data sources wide
if($globalImagesFilterExclude = trim($this->cparams->get ( 'images_global_filter_exclude', '' ))) {
	$imgGlobalFilterExclude = explode(',', $globalImagesFilterExclude);
	$imgFilterExclude = array_merge($imgFilterExclude, $imgGlobalFilterExclude);
}


// Inject items links
if (isset($this->source->data->link) && count ( $this->source->data->link ) != 0) {  
	foreach ( $this->source->data->link as $index=>$link ) {
		// Check to ensure is counting valid request
		if(!$this->HTTPClient->isValidRequest()) {
			break;
		}
		
		// Skip outputting
		$relativeLink = str_replace($this->liveSite, '', $link);
		if(array_key_exists($relativeLink, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$relativeLink] = true;
		$this->imagesOutputtedLinksBuffer = array();
		
		// HTTP Request to remote URL '$seolink' to get images
		$headers = array('Accept'=>'text/html', 'User-Agent'=>'JSitemapbot/1.0');
		$HTTPResponse = $this->HTTPClient->get($link, $headers);
		$pageHtml = $HTTPResponse->body;
		// Images RegExp extraction
		$imagesArrayResults = array();
		preg_match_all ( $this->mainImagesRegex, $pageHtml, $imagesArrayResults );
		$imagesTags = $imagesArrayResults[0];
		$imagesLinks = $imagesArrayResults[4];
		
		// Crawl and merge images also from dummy gallery images?
		if($fakeImages) {
			preg_match_all ( '/(<a)([^>])*(href=["\']([^"\']+\.(jpg|gif|png|svg|webp))["\'])([^>])*/i', $pageHtml, $dummyImagesArrayResults );
			$dummyImagesTags = $dummyImagesArrayResults[0];
			$dummyImagesLinks = $dummyImagesArrayResults[4];
			$imagesTags = array_merge($imagesTags, $dummyImagesTags);
			$imagesLinks = array_merge($imagesLinks, $dummyImagesLinks);
		}
		
		// Crawl and merge images also from dummy gallery images?
		if($lazyloadImages) {
			preg_match_all ( '/(<img)([^>])*((data-src|data-lazyload)=["\']([^"\']+\.(jpg|gif|png|svg|webp))["\'])([^>])*/i', $pageHtml, $lazyloadImagesArrayResults );
			$lazyloadImagesTags = $lazyloadImagesArrayResults[0];
			$lazyloadImagesLinks = $lazyloadImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $lazyloadImagesTags);
			$imagesLinks = array_merge($imagesLinks, $lazyloadImagesLinks);
		}
		
		// Custom images tags and attributes
		if($this->validCustomImagesProcessor) {
			$dynamicRegex = '/(<' . implode('|<', $this->validExplodedTags) . ')([^>])*((' . implode('|', $this->validExplodedAttributes) . ')=["\']([^"\']+\.(jpg|gif|png|svg|webp))["\'])([^>])*/i';
			preg_match_all ( $dynamicRegex, $pageHtml, $customFoundImagesArrayResults );
			$customFoundImagesTags = $customFoundImagesArrayResults[0];
			$customFoundImagesLinks = $customFoundImagesArrayResults[5];
			$imagesTags = array_merge($imagesTags, $customFoundImagesTags);
			$imagesLinks = array_merge($imagesLinks, $customFoundImagesLinks);
		}
		
$bufferImages = null;
$bufferExclusions = $filtersMode == 'tag' ? $imagesTags : $imagesLinks;
if(!empty($imagesLinks)):
ob_start();
foreach ($imagesLinks as $index=>$imageLink):
// Skip outputting
if(array_key_exists($imageLink, $this->imagesOutputtedLinksBuffer)) {
	continue;
}
$validImage = true;
$found = false;
$optionalImageTitle = false;
// Extended images filtering include
if(is_array($imgFilterInclude) && count($imgFilterInclude)):
	foreach ($imgFilterInclude as $filterInclude) :
		if(strstr($bufferExclusions[$index], trim($filterInclude))) {
			$found = true;
			break;
		}
	endforeach;
if(!$found):
$validImage = false;
endif;
endif;

// Extended images filtering exclude
if(is_array($imgFilterExclude) && count($imgFilterExclude)):
	foreach ($imgFilterExclude as $filterExclude) :
		if(strstr($bufferExclusions[$index], trim($filterExclude))) {
			$validImage = false;
			break;
		}
	endforeach;
endif;
// Image not valid so don't insert in sitemap
if(!$validImage)
	continue;
// Check for optional image title
if(stristr($imagesTags[$index], 'title=') || stristr($imagesTags[$index], 'alt=')) {
	$optionalTitleMatches = array();
	// Crawl and merge images also from dummy gallery images?
	if($this->validCustomImagesProcessor) {
		$dynamicTitlesRegex = '/(<img|<a|<' . implode('|<', $this->validExplodedTags) . ')([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU';
		preg_match($dynamicTitlesRegex, $imagesTags[$index], $optionalTitleMatches);
	} elseif($fakeImages) {
		preg_match('/(<img|<a)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	} else {
		preg_match('/(<img)([^>])*(('.$imagetitleProcessorRegexp.')=(["\'])([^\5]*)\5)/iU', $imagesTags[$index], $optionalTitleMatches);
	}
	if(!empty($optionalTitleMatches[6])) {
		$optionalImageTitle = '<image:title>' . htmlspecialchars(html_entity_decode($optionalTitleMatches[6], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8', false) . '</image:title>' . PHP_EOL;
	}
}
// Check if image description is required and missing
if(!$optionalImageTitle && $includeDescriptionOnly) {
	continue;
}
$this->imagesOutputtedLinksBuffer[$imageLink] = true;
?>
<image:image>
<image:loc><?php echo htmlspecialchars(preg_match('/^http|^\/\//i', $imageLink) ? ($cdnProtocol && strpos($imageLink, 'http') === false ? $cdnProtocol . $imageLink : $imageLink) : $this->liveSite . '/' . ltrim($imageLink, '/'), ENT_NOQUOTES, 'UTF-8', false);?></image:loc>
<?php echo $optionalImageTitle;?>
</image:image>
<?php 
endforeach;
$bufferImages = ob_get_clean();
endif;

// Se sono presenti immagini
if(isset($bufferImages) && !empty($bufferImages)):
?>
<url>
<loc><?php echo htmlspecialchars($link, ENT_NOQUOTES, 'UTF-8', false); ?></loc>
<?php echo $bufferImages; ?>
</url>
<?php 
endif;
	}
}