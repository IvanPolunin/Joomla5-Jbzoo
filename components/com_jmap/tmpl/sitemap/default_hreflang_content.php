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
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Associations as JMapHelpersAssociations;

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
		
		// Get language associations for this content, if not found skip and go on
		$associatedContents = JMapHelpersAssociations::getContentAssociations('com_content', '#__content', 'com_content.item', $elm->id);
		if(count($associatedContents) <= 1) {
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
		?>
<url>
<loc><?php echo $this->liveSite . $seolink; ?></loc>
<?php foreach ($associatedContents as $alternate):?>
<xhtml:link rel="alternate" hreflang="<?php echo $alternate->sef?>" href="<?php echo $this->liveSite . \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $alternate->id, $alternate->catid, $alternate->language ) );?>" />
<?php endforeach;?>
</url>
<?php
		foreach ($associatedContents as $repetition) {
			// Skip the main default url already added
			if((int)$repetition->id == $elm->id) {
				continue;
			}
			?>
<url>
<loc><?php echo $this->liveSite . \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $repetition->id, $repetition->catid, $repetition->language ) ); ?></loc>
<?php foreach ($associatedContents as $subalternate):?>
<xhtml:link rel="alternate" hreflang="<?php echo $subalternate->sef?>" href="<?php echo $this->liveSite . \JMapRoute::_ ( ContentRouteHelper::getArticleRoute ( $subalternate->id, $subalternate->catid, $subalternate->language ) );?>" />
<?php endforeach;?>
</url>
<?php
		}
	}
}