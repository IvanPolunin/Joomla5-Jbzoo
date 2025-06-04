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

// Check if the third party user data source extension is supported
$extension =  $this->source->chunks->option;
if(!property_exists($this->supportedNativeExtensions, $extension)) {
	return;
}

$tablename =  $this->source->chunks->table_maintable;
$explodedExtensionName = explode('_', $extension);
$classRoutePrefix = ucfirst(array_pop($explodedExtensionName));
$classRouteHelper = '\\Joomla\\Component\\' . $classRoutePrefix . '\\Site\Helper\\Route';
$methodRouteHelper = 'get' . $this->supportedNativeExtensions->{$extension} . 'Route';

if (count ( $this->source->data ) != 0) {
	foreach ( $this->source->data as $elm ) {
		// Element category empty da right join
		if(!$elm->id) {
			continue;
		}
		
		// Get language associations for this content, if not found skip and go on
		$associatedContents = JMapHelpersAssociations::getContentAssociations($extension, $tablename, $extension . '.item', $elm->id);
		if(count($associatedContents) <= 1) {
			continue;
		}
		
		$seolink = \JMapRoute::_ ( $classRouteHelper::$methodRouteHelper ( $elm->id, $elm->catid, $elm->language ) );
		
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
<xhtml:link rel="alternate" hreflang="<?php echo $alternate->sef?>" href="<?php echo $this->liveSite . \JMapRoute::_ ( $classRouteHelper::$methodRouteHelper ( $alternate->id, $alternate->catid, $alternate->language ) );?>" />
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
<loc><?php echo $this->liveSite . \JMapRoute::_ ( $classRouteHelper::$methodRouteHelper ( $repetition->id, $repetition->catid, $repetition->language ) ); ?></loc>
<?php foreach ($associatedContents as $subalternate):?>
<xhtml:link rel="alternate" hreflang="<?php echo $subalternate->sef?>" href="<?php echo $this->liveSite . \JMapRoute::_ ( $classRouteHelper::$methodRouteHelper ( $subalternate->id, $subalternate->catid, $subalternate->language ) );?>" />
<?php endforeach;?>
</url>
<?php
		}
	}
}