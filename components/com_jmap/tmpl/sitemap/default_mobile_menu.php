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

$includeExternalLinks =  $this->sourceparams->get ( 'include_external_links', 1 );
$trailingSlash = '/';
$removeHomeSlash = $this->cparams->get('remove_home_slash', 0);
$sefSuffix = $this->application->getConfig()->get('sef_suffix');

// Get menus object
$menusArray = $this->application->getMenu()->getMenu();

if (count ( $this->source->data )) {
	foreach ( $this->source->data as $elm ) { 
		// Skip menu external links
		if($elm->type == 'url' && !$includeExternalLinks) {
			continue;
		}
		
		// Always skip external urls in XML sitemaps
		if($elm->type == 'url' && strpos($elm->link, $this->liveSite) === false) {
			continue;
		}
		
		// Avoid place link for separator and alias
		if(in_array($elm->type, array('separator', 'heading'))) {
			continue;
		}
		
		$link = $elm->link;
		if (isset ( $elm->id )) {
			switch (@$elm->type) {
				case 'separator' :
				case 'alias' :
				case 'heading' :
					break;
				case 'url' :
					if (preg_match ( "#^/?index\.php\?#", $link )) {
						if (strpos ( $link, 'Itemid=' ) === false) {
							if (strpos ( $link, '?' ) === false) {
								$link .= '?Itemid=' . $elm->id;
							} else {
								$link .= '&amp;Itemid=' . $elm->id;
							}
						}
					}
					break;
				default :
					if (strpos ( $link, 'Itemid=' ) === false) {
						$link .= '&amp;Itemid=' . $elm->id;
					}
					break;
			}
		}
		
		if (strcasecmp ( substr ( $link, 0, 9 ), 'index.php' ) === 0 && $elm->type != 'alias') {
			$link = \JMapRoute::_ ( $link );
		}
		
		// SEF patch for better match uri con $link override
		if ($elm->type == 'component' && array_key_exists($elm->id, $menusArray)) {
			$link = 'index.php?Itemid=' . $elm->id;
			$link = \JMapRoute::_ ( $link );
		}
		
		// SEF patch for menu alias
		if ($elm->type == 'alias' && array_key_exists($elm->id, $menusArray)) {
			$menuParams = json_decode($elm->params);
			$link = 'index.php?Itemid=' . $menuParams->aliasoptions;
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
<mobile:mobile/>
</url>
<?php 
	} 
}