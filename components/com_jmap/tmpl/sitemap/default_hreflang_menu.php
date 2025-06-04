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
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Associations as JMapHelpersAssociations;

$includeExternalLinks =  $this->sourceparams->get ( 'include_external_links', 1 );
$trailingSlash = '/';
$removeHomeSlash = $this->cparams->get('remove_home_slash', 0);

// Get menus object
$menusArray = $this->application->getMenu()->getMenu();

if (count ( $this->source->data )) {
	foreach ( $this->source->data as $elm ) { 
		// Skip menu external links
		if($elm->type == 'url' && !$includeExternalLinks) {
			continue;
		}
		
		// Avoid place link for separator, alias, heading, external url
		if(in_array($elm->type, array('url', 'separator', 'alias', 'heading'))) {
			continue;
		}
		
		// Get language associations for this content, if not found skip and go on
		$associatedMenus = JMapHelpersAssociations::getMenuAssociations($elm->id);
		if(count($associatedMenus) <= 1) {
			continue;
		}
		
		$link = $elm->link;
		if (isset ( $elm->id )) {
			if (strpos ( $link, 'Itemid=' ) === false) {
				$link .= '&Itemid=' . $elm->id;
			}
		}
		
		if (strcasecmp ( substr ( $link, 0, 9 ), 'index.php' ) === 0) {
			$link = \JMapRoute::_ ( $link );
		}
		
		// SEF patch for better match uri con $link override
		if ($elm->type == 'component' && array_key_exists($elm->id, $menusArray)) {
			$link = 'index.php?Itemid=' . $elm->id;
			$link = \JMapRoute::_ ( $link );
		}
		
		if ($elm->home && $removeHomeSlash) { // HOME
			$link = rtrim($link, '/');
			$trailingSlash = '';
		}
		
		// Skip outputting
		if(array_key_exists($link, $this->outputtedLinksBuffer)) {
			continue;
		}
		// Else store to prevent duplication
		$this->outputtedLinksBuffer[$link] = true;
		
		$link = htmlspecialchars($link, ENT_NOQUOTES, 'UTF-8', false);
		?>
<url>
<loc><?php echo preg_match('/^http/i', $link) ? $link : $this->liveSite . (strpos($link, '/') === 0 ? $link : $trailingSlash . $link) ; ?></loc>
<?php foreach ($associatedMenus as $alternate):
$alternateLink = htmlspecialchars(\JMapRoute::_ ( 'index.php?Itemid=' . $alternate->id . '&lang=' . $alternate->sef ), ENT_NOQUOTES, 'UTF-8', false);
$alternateLink = preg_match('/^http/i', $alternateLink) ? $alternateLink : $this->liveSite . (strpos($alternateLink, '/') === 0 ? $alternateLink : '/'.$alternateLink);
?>
<xhtml:link rel="alternate" hreflang="<?php echo $alternate->sef?>" href="<?php echo $alternateLink;?>" />
<?php endforeach;?>
</url>
<?php
		foreach ($associatedMenus as $repetition) {
			// Skip the main default url already added
			if((int)$repetition->id == $elm->id) {
				continue;
			}
			
			$repetitionLink = htmlspecialchars(\JMapRoute::_ ( 'index.php?Itemid=' . $repetition->id . '&lang=' . $repetition->sef ), ENT_NOQUOTES, 'UTF-8', false);
			$repetitionLink = preg_match('/^http/i', $repetitionLink) ? $repetitionLink : $this->liveSite . (strpos($repetitionLink, '/') === 0 ? $repetitionLink : '/'.$repetitionLink);
			?>
<url>
<loc><?php echo $repetitionLink; ?></loc>
<?php foreach ($associatedMenus as $subalternate):
$alternateLink = htmlspecialchars(\JMapRoute::_ ( 'index.php?Itemid=' . $subalternate->id . '&lang=' . $subalternate->sef ), ENT_NOQUOTES, 'UTF-8', false);
$alternateLink = preg_match('/^http/i', $alternateLink) ? $alternateLink : $this->liveSite . (strpos($alternateLink, '/') === 0 ? $alternateLink : '/'.$alternateLink);
?>
<xhtml:link rel="alternate" hreflang="<?php echo $subalternate->sef?>" href="<?php echo $alternateLink;?>" />
<?php endforeach;?>
</url>
<?php 	
		}
	} 
}