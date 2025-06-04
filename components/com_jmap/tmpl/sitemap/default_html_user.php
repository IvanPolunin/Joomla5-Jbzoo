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
use Joomla\CMS\Filter\OutputFilter;
use JExtstore\Component\JMap\Administrator\Framework\Route\Helper as JMapRouteHelper;

$sourceTitle = $this->sourceparams->get ( 'title', $this->source->name );
$showtitle = $this->sourceparams->get ( 'showtitle', 1 );
$openTarget = $this->sourceparams->get ( 'opentarget', $this->cparams->get ('opentarget') );
$topLevelCategoryId = null;
$mergeMenuTreeLevels = $this->sourceparams->get ( 'merge_menu_tree_levels', 'toplevel' );

if (! $showtitle) {
	$sourceTitle = '&nbsp;';
}

// Include common template init
include 'default_common_user.php';

// Check if linkable cats is enabled to inject vars into js domain
$sitemapTemplate = $this->cparams->get('sitemap_html_template', '');
$isMindMap = $sitemapTemplate == 'mindmap' ? true : false;
if(($linkableCatsMode = $this->sourceparams->get ( 'linkable_cats', null )) && !$isMindMap && $this->cparams->get('treeview_scripts', 1))  {
	$this->document->getWebAssetManager()->addInlineScript('jmapLinkableCatsSources["' . $targetOption . '"] = "' . $linkableCatsMode . '";');
}

if(($mergeMenuTreeMode = $this->sourceparams->get ( 'merge_menu_tree', null )) && $this->cparams->get('treeview_scripts', 1))  {
	$this->document->getWebAssetManager()->addInlineScript('jmapMergeMenuTree["' . $targetOption . '"] = "' . $mergeMenuTreeMode . '";');
}

if (count ( $this->source->data )) {
	// If categorization detected for datasource elements according to adiacency/multi adiacency setup, Feature Detection
	if(isset($this->source->catRecursion) && isset($this->source->itemsByCat) && isset($this->source->catChildrenByCat)) {
		$hasCategoryNamespace = (bool)(isset($this->asCategoryTitleField) && $this->asCategoryTitleField) ? '.items' : null;
		echo '<ul class="jmap_filetree" data-hash="' . $targetOption . $hasCategoryNamespace . '"><li><span class="folder">' . $sourceTitle. '</span>';
		// Start building tree
		recurseCats(0, 
					$this->source->itemsByCat, 
					$this->source->catChildrenByCat, 
					0, 
					$this->asCategoryTitleField, 
					$this->liveSite, 
					$targetOption, 
					$targetView,
					$targetViewName,
					$additionalQueryStringParams, 
					$openTarget, 
					$arrayKeysDiff, 
					$titleIdentifier, 
					$idIdentifier, 
					$idURLFilter, 
					$catidIdentifier, 
					$catidURLFilter, 
					$supportedRouterHelperAdapters,
					$guessItemid,
					$mainTable,
					null,
					$this->marginSide,
					$sefLinksReplacements,
					$mergeMenuTreeLevels);
		echo '</li></ul>';
	}
	// If categorization detected for datasource elements group by categories
	elseif(isset($this->source->data[0]->{$this->asCategoryTitleField}) || isset($this->source->data[0]->jsitemap_level)){
		$first = true;
		$catsave = null;
		$catRecursion = false;
		$liIndent = null;
		$hasValidCategoryTitleField = (bool)(isset($this->asCategoryTitleField) && $this->asCategoryTitleField);
		$hasCategoryNamespace = $hasValidCategoryTitleField ? '.items' : null;
		// Manage levels, Feature Detection
		if(isset($this->source->catRecursion) && isset($this->source->data[0]->jsitemap_level) && $this->source->recursionType == 'level') {
			$catRecursion = true;
		}
		echo '<ul class="jmap_filetree" data-hash="' . $targetOption . $hasCategoryNamespace . '"><li><span class="folder">' . $sourceTitle. '</span>';
		foreach ( $this->source->data as $elm ) {
			// Calculate element indentation, Feature Detection
			$indent = $catRecursion ? ($elm->jsitemap_level - 1) * 15 : 0;
			
			// Subitems with categorization in multilevel mode
			if($hasValidCategoryTitleField) {
				if ($elm->{$this->asCategoryTitleField} != $catsave && ! $first) {
					echo '</ul></li></ul>';
					echo '<ul class="jmap_filetree" style="' . $this->marginSide . $indent . 'px"><li><span class="folder">' . $elm->{$this->asCategoryTitleField} . '</span>';
					echo '<ul>';
					$catsave = $elm->{$this->asCategoryTitleField};
				} else {
					if ($first) {
						echo '<ul class="jmap_filetree" style="' . $this->marginSide . $indent . 'px"><li><span class="folder">' . $elm->{$this->asCategoryTitleField} . '</span>';
						echo '<ul>';
						$first = false;
						$catsave = $elm->{$this->asCategoryTitleField};
					}
				}
			} else {
				// Final categories items with categorization in multilevel mode
				$dataHash = null;
				$topLevelCategoryId = $elm->jsitemap_level > 1 ? $topLevelCategoryId : (int)$elm->{$idIdentifier};
				if($targetOption && $targetViewName && $topLevelCategoryId) {
					if($mergeMenuTreeLevels == 'toplevel') {
						$dataHash = 'data-hash="' . $targetOption . '.' . $targetViewName . '.' . $topLevelCategoryId . '"';
					} else {
						$dataHash = 'data-hash="' . $targetOption . '.' . $targetViewName . '.' . (int)$elm->{$idIdentifier} . '"';
					}
				}
				if($catRecursion) {
					$liIndent = ' style="' . $this->marginSide . ($indent) . 'px"';
				}
				if (! $first) {
					echo '</ul>';
					echo '<ul class="jmap_filetree" ' . $dataHash . $liIndent . '>';
				} else {
					if ($first) {
						echo '<ul class="jmap_filetree" ' . $dataHash . $liIndent . '>';
						$first = false;
					}
				}
			}
				
			$title = isset($titleIdentifier) &&  $titleIdentifier != '' ? $elm->{$titleIdentifier} : null;
			// Additional fields
			$additionalParamsQueryString = null;
			$objectVars = array_diff_key(get_object_vars($elm), $arrayKeysDiff);
			// Filter URL safe alias fields id/catid
			if(isset($objectVars[$idIdentifier]) && $idURLFilter) {
				$objectVars[$idIdentifier] = OutputFilter::stringURLSafe($objectVars[$idIdentifier]);
			}
			if(isset($objectVars[$catidIdentifier]) && $catidURLFilter) {
				$objectVars[$catidIdentifier] = OutputFilter::stringURLSafe($objectVars[$catidIdentifier]);
			}
			if(is_array($objectVars) && count($objectVars)) {
				$additionalQueryStringFromObjectProp = '&' . http_build_query($objectVars);
			}
				
			if(isset($supportedRouterHelperAdapters[$targetOption]) && $supportedRouterHelperAdapters[$targetOption]) {
				include 'adapters/'.$targetOption.'.php';
			} else {
				$guessedItemid = null;
				if($guessItemid) {
					$guessedItemid = JMapRouteHelper::getItemRoute($targetOption, $targetViewName, $elm->{$idIdentifier}, $elm, $mainTable);
					if($guessedItemid) {
						$guessedItemid = '&Itemid=' . $guessedItemid;
					}
				}
				$seflink = \JMapRoute::_ ( 'index.php?option=' . $targetOption . $targetView . $additionalQueryStringFromObjectProp . $additionalQueryStringParams . $guessedItemid);
			}
			
			// Manage SEF links replacements
			if($sefLinksReplacements) {
				$seflink = str_replace($sefLinksReplacements['source'], $sefLinksReplacements['target'], $seflink);
			}
			
			echo '<li>' . '<a target="' . $openTarget . '" href="' .  $this->liveSite . $seflink . '" >' . $title . '</a></li>';
		}
		if($hasValidCategoryTitleField) {
			echo '</ul></li></ul>';
		} else {
			echo '</ul>';
		}
		echo '</li></ul>';
	} else { // No categorization detected for datasource elements
		$isCategoryTable = preg_match ( '/categor|cats|catg/i', $mainTable ) ? ' data-hash="' . $targetOption . '"' : null;
		if(!$isCategoryTable && $this->mergeGenericMenuByClass) {
			$isCategoryTable = ' data-hash="' . $targetOption . '.items"';
		}
		echo '<ul' . $isCategoryTable . ' class="jmap_filetree"><li><span class="folder">' . $sourceTitle. '</span><ul>';
		foreach ( $this->source->data as $elm ) {
			$title = isset($titleIdentifier) &&  $titleIdentifier != ''  ? $elm->{$titleIdentifier} : null;
			// Additional fields
			$additionalQueryStringFromObjectProp = null;
			$objectVars = array_diff_key(get_object_vars($elm), $arrayKeysDiff);
			// Filter URL safe alias fields id/catid
			if(isset($objectVars[$idIdentifier]) && $idURLFilter) {
				$objectVars[$idIdentifier] = OutputFilter::stringURLSafe($objectVars[$idIdentifier]);
			}
			if(isset($objectVars[$catidIdentifier]) && $catidURLFilter) {
				$objectVars[$catidIdentifier] = OutputFilter::stringURLSafe($objectVars[$catidIdentifier]);
			}
			if(is_array($objectVars) && count($objectVars)) {
				$additionalQueryStringFromObjectProp = '&' . http_build_query($objectVars);
			}
			
			if(isset($supportedRouterHelperAdapters[$targetOption]) && $supportedRouterHelperAdapters[$targetOption]) {
				include 'adapters/'.$targetOption.'.php';
			} else {
				$guessedItemid = null;
				if($guessItemid) {
					$guessedItemid = JMapRouteHelper::getItemRoute($targetOption, $targetViewName, $elm->{$idIdentifier}, $elm, $mainTable);
					if($guessedItemid) {
						$guessedItemid = '&Itemid=' . $guessedItemid;
					}
				}
				$seflink = \JMapRoute::_ ( 'index.php?option=' . $targetOption . $targetView . $additionalQueryStringFromObjectProp . $additionalQueryStringParams . $guessedItemid);
				
				// Manage SEF links replacements
				if($sefLinksReplacements) {
					$seflink = str_replace($sefLinksReplacements['source'], $sefLinksReplacements['target'], $seflink);
				}
			}
			echo '<li>' . '<a target="' . $openTarget . '" href="' . $this->liveSite . $seflink . '" >' . $title . '</a></li>';
		}
		echo '</ul></li></ul>';
	}
}