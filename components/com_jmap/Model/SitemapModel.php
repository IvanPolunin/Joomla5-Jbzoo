<?php
namespace JExtstore\Component\JMap\Site\Model;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Category as JTableCategory;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Language\Multilang as JMapMultilang;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Main sitemap model public responsibilities interface
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 */
interface IJMapModelSitemap {
	/**
	 * Get the Data
	 * @return array
	 */
	public function getSitemapData();
}

/**
 * CPanel export XML sitemap responsibility
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IJMapModelExportable {
	/**
	 * Export XML file for sitemap
	 *
	 * @access public
	 * @param string $contents
	 * @param string $fileNameSuffix
	 * @param string $fileNameFormat
	 * @param string $fileNameLanguage
	 * @param string $fileNameDatasetFilter
	 * @param string $fileNameItemidFilter
	 * @param string $mimeType
	 * @param boolean $isFile
	 * @return boolean
	 */
	public function exportXMLSitemap($contents, $fileNameSuffix, $fileNameFormat, $fileNameLanguage, $fileNameDatasetFilter, $fileNameItemidFilter, $mimeType, $isFile = false);
}

/**
 * Main sitemap model class <<testable_behavior>>
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class SitemapModel extends JMapModel implements IJMapModelSitemap, IJMapModelExportable {
	/**
	 * Fallback default site language
	 * @access private
	 * @var string
	 */
	private $fallbackDefaultLanguage;
	
	/**
	 * Fallback default site language RFC format
	 * @access private
	 * @var string
	 */
	private $fallbackDefaultLanguageRFC;
	
	/**
	 * Default site language
	 * @access private
	 * @var string
	 */
	private $langTag;
	
	/**
	 * Default site language
	 * @access private
	 * @var string
	 */
	private $siteLanguageRFC;
	
	/**
	 * Document formats
	 * @access private
	 * @var array
	 */
	private $documentFormat;
	
	/**
	 * Component params with view overrides/merge
	 * @access private
	 * @var array
	 */
	private $cparams;
	
	/**
	 * Supported tables for options components supported to generate
	 * 3PD Google News sitemap
	 * @access private
	 * @var array
	 */
	private $supportedGNewsTablesOptions;
	
	/**
	 * Access level
	 * @access private
	 * @var string
	 */
	private $accessLevel = array();
	
	/**
	 * Main data structure
	 * @access private
	 * @var array
	 */
	private $data = array (); 
	
	/**
	 * Sources array
	 * @access private
	 * @var array
	 */
	private $sources = array (); 
	
	/**
	 * RSS extesions manifest supported
	 * @access private
	 * @var Object
	 */
	private $rssExtensionsManifest;
	
	/**
	 * Calculated limit start for source data query during a precaching process
	 * @access public
	 * @var int
	 */
	public $limitStart;
	
	/**
	 * Calculated limit rows for source data query during a precaching process
	 * @access public
	 * @var int
	 */
	public $limitRows;
	
	/**
	 * Send as attachment download
	 * 
	 * @access public
	 * @param String $contents
	 * @param String $filename Nome del file esportato
	 * @param String $mimeType Mime Type dell'attachment
	 * @param boolean $isFile Se trattare il contenuto come file name o content pronti
	 * @return void
	 */
	private function sendAsBinary($contents, $filename, $mimeType, $isFile = false) {
		if($isFile) {
			$fsize = @filesize ( $contents );
		} else {
			$fsize = strlen($contents);
		}
	
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get ( 'zlib.output_compression' )) {
			ini_set ( 'zlib.output_compression', 'Off' );
		}
		header ( "Pragma: public" );
		header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header ( "Expires: 0" );
		header ( "Content-Transfer-Encoding: binary" );
		header ( 'Content-Disposition: attachment;' . ' filename="' . $filename . '";' . ' size=' . $fsize . ';' ); //RFC2183
		header ( "Content-Type: " . $mimeType); // MIME type
		header ( "Content-Length: " . $fsize );
		@set_time_limit ( 0 );
		
		if(!$isFile) {
			echo $contents; 
		} else {
			$this->readfile_chunked ( $contents );
		}
	
		exit();
	}
	
	/**
	 * Read and send in the output stream the contents of the file in chunks,
	 * Resolving the problems of limitations related to the normal readfile
	 * 
	 * @access private
	 * @param string $nomefile
	 * @return boolean
	 */
	private function readfile_chunked($filename) {
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		$buffer = '';
		$cnt = 0;
		$handle = fopen ( $filename, 'rb' );
		if ($handle === false) {
			return false;
		}
		while ( ! feof ( $handle ) ) {
			$buffer = fread ( $handle, $chunksize );
			echo $buffer;
			@ob_flush ();
			flush ();
		}
		$status = fclose ( $handle );
		return $status;
	}
	
	/**
	 * Pagebreaks detection
	 * 
	 * @access private
	 * @param Object& $article
	 * @return boolean
	 */
	private function addPagebreaks(&$article) {
		$matches = array ();
		if (isset($article->completetext) && preg_match_all ( '/<hr\s*[^>]*?(?:(?:\s*alt="(?P<alt>[^"]+)")|(?:\s*title="(?P<title>[^"]+)"))+[^>]*>/i', $article->completetext, $matches, PREG_SET_ORDER )) {
			foreach ( $matches as $i=>$match ) {
				if (strpos ( $match [0], 'class="system-pagebreak"' ) !== false) {
					if (@$match ['alt']) {
						$title = stripslashes ( $match ['alt'] );
					} elseif (@$match ['title']) {
						$title = stripslashes ( $match ['title'] );
					} else {
						$title = Text::sprintf ( 'Page #', $i );
					}
					$article->expandible[] = $title;
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Preprocessing at runtime for third party extensions generated query
	 * General purpouse function to fil gaps of 3PD extensions at runtime
	 * Maybe in future could migrate to configurable JSON manifest as for wizard
	 * 
	 * @access private
	 * @param Object $source
	 * @param string $query
	 * @param Object $params
	 * @return string The processed query SQL string
	 */
	private function runtimePreProcessing($query, $resultSourceObject) {
		// Switch data source option name
		$sqlqueryManaged = $resultSourceObject->chunks;
		$params = $resultSourceObject->params;
		$option = $resultSourceObject->chunks->option;
		
		switch ($sqlqueryManaged->option) {
			case 'com_virtuemart':
				// If site user language not match default for generated query and is VM 2 with extended lang tables
				if(!stristr($sqlqueryManaged->table_maintable, $this->siteLanguageRFC) && stristr($sqlqueryManaged->table_maintable, 'virtuemart') && JMapMultilang::isEnabled()) {
					// Find language with which generated query has been generated
					$maintableChunked = explode('_', $sqlqueryManaged->table_maintable);
					$langReverseChunks = array();
					$langReverseChunks[] = array_pop($maintableChunked);
					$langReverseChunks[] = array_pop($maintableChunked);
					$originalLang = array_reverse($langReverseChunks);
					$originalLang = implode('_', $originalLang);
					
					// Site language has been changed from default for which query has been generated, so process
					$processedQuery = preg_replace('/(#__virtuemart.*)(_'.$originalLang.')/iU', '$1_'.$this->siteLanguageRFC.'$3', $query);
					
					// Now test if admin has created virtuemart 2 tables for the site user chosen language otherwise proceed with same of generated query
					try {
						$this->dbInstance->setQuery($processedQuery);
						if($this->dbInstance->execute()) {
							$query = $processedQuery;
						}
					} catch (\Exception $e) {
						// No exception throw, just go on
					}
				}
			break;
			
			case 'com_jshopping':
				// If site user language not match default for generated query and is Joomshopping we can go on to replace language value
				if(!stristr($sqlqueryManaged->titlefield, $this->langTag) && stristr($sqlqueryManaged->table_maintable, 'jshopping')) {
					// Find deafult site language with which raw query has been generated
					$maintableChunked = explode('_', $sqlqueryManaged->titlefield);
					$originalLang = array_pop($maintableChunked);

					// Site language has been changed from default for which query has been generated, so process
					$processedQuery = preg_replace('/(name)(_'.$originalLang.')/iU', '$1_'.$this->langTag.'$3', $query);
					$resultSourceObject->chunks->titlefield = str_replace($originalLang, $this->langTag, $resultSourceObject->chunks->titlefield);
					$resultSourceObject->chunks->orderby_maintable = str_replace($originalLang, $this->langTag, $resultSourceObject->chunks->orderby_maintable);
					$resultSourceObject->chunks->field_select_jointable2 = @str_replace($originalLang, $this->langTag, $resultSourceObject->chunks->field_select_jointable2);

					// Now test if admin has created virtuemart 2 tables for the site user chosen language otherwise proceed with same of generated query
					$checkSafeQuery = "SHOW COLUMNS FROM " . $this->dbInstance->quoteName($sqlqueryManaged->table_maintable);
					$this->dbInstance->setQuery($checkSafeQuery);
					$columnsArray = $this->dbInstance->loadColumn();
					if(in_array('name_' . $this->langTag, $columnsArray)) {
						$query = $processedQuery;
					}
				}
			break;
			
			case 'com_j2store':
				// Leave untouched
				break;
		
			// Preprocessing with generic tasks for all extensions
			default:
				// Only if multilanguage is not currently enabled, switch query to select all disregarding language filter 
				if(!JMapMultilang::isEnabled() && stristr($query, '{langtag}')) {
					// Do replacement to avoid language filtering
					$toReplaceString = "{langtag} OR " . $this->dbInstance->quoteName($sqlqueryManaged->table_maintable) . "." . 
														 $this->dbInstance->quoteName('language') . " != ''";
					$query = str_replace("{langtag}", $toReplaceString, $query);
				}
				// Avoid access filtering if ACL disabled
				if($params->get('disable_acl') === 'disabled' && stristr($query, '{aid}')) {
					$toReplaceString = ">= 0";
					$query = str_replace("IN {aid}", $toReplaceString, $query);
				}
		}
		
		// Manage preprocessing query for multi level categorization recursion types level/adiacency/multiadiacency. This let avoid to change backend wizard manifests
		$hasJSitemapCategoryId = false;
		if($params->get('multilevel_categories', 0) && $this->hasCategorization($resultSourceObject)) {
			$manifestConfiguration = $this->loadManifest ($option);
			// Error decoding configuration object, exit and fallback to standard indenting
			if(is_object($manifestConfiguration) && isset($manifestConfiguration->recursion_type) && $manifestConfiguration->recursion_type == 'level') {
				$toReplaceString = "SELECT " . 
									$this->dbInstance->quoteName($manifestConfiguration->categories_table) . "." . 
									$this->dbInstance->quoteName($manifestConfiguration->level_field) . 
									" AS " . $this->dbInstance->quoteName('jsitemap_level') . ", ";
				$query = preg_replace("/SELECT/", $toReplaceString, $query, 1);
				$resultSourceObject->catRecursion = true;
				$resultSourceObject->recursionType = $manifestConfiguration->recursion_type;
			} elseif (is_object($manifestConfiguration) && isset($manifestConfiguration->recursion_type) && $manifestConfiguration->recursion_type == 'adiacency') {
				$toReplaceString = "SELECT " .
									$this->dbInstance->quoteName($manifestConfiguration->categories_table) . "." .
									$this->dbInstance->quoteName($manifestConfiguration->category_table_id_field) .
									" AS " . $this->dbInstance->quoteName('jsitemap_category_id') . ", ";
				$query = preg_replace("/SELECT/", $toReplaceString, $query, 1);
				$resultSourceObject->catRecursion = true;
				$resultSourceObject->recursionType = $manifestConfiguration->recursion_type;
				$hasJSitemapCategoryId = true;
			} elseif (is_object($manifestConfiguration) && 
					  isset($manifestConfiguration->recursion_type) && 
					  $manifestConfiguration->recursion_type == 'multiadiacency' && 
					  !preg_match('/categor|cats|catg/i', $resultSourceObject->chunks->table_maintable)) {
				$toReplaceString = "SELECT " .
									$this->dbInstance->quoteName($manifestConfiguration->item2category_table) . "." .
									$this->dbInstance->quoteName($manifestConfiguration->item2category_catid_field) .
									" AS " . $this->dbInstance->quoteName('jsitemap_category_id') . ", ";
				$query = preg_replace("/SELECT/", $toReplaceString, $query, 1);
				$resultSourceObject->catRecursion = true;
				$resultSourceObject->recursionType = $manifestConfiguration->recursion_type;
				$hasJSitemapCategoryId = true;
			} elseif (is_object($manifestConfiguration) &&
					  isset($manifestConfiguration->recursion_type) &&
					  $manifestConfiguration->recursion_type == 'multiadiacency' &&
					  preg_match('/categor|cats|catg/i', $resultSourceObject->chunks->table_maintable)) {
				$toReplaceString = "SELECT " .
									$this->dbInstance->quoteName($manifestConfiguration->categories_table) . "." .
									$this->dbInstance->quoteName($manifestConfiguration->category_table_id_field) .
									" AS " . $this->dbInstance->quoteName('jsitemap_category_id') . ", ";
				$query = preg_replace("/SELECT/", $toReplaceString, $query, 1);
				$resultSourceObject->catRecursion = true;
				$resultSourceObject->recursionType = $manifestConfiguration->recursion_type;
				$hasJSitemapCategoryId = true;
			}
		}
		
		// Not just injected jsitemap_category_id for the multilevel, param $guessItemid active and not category type data source itself
		$guessItemid = $params->get('guess_sef_itemid', 0);
		if(	!$hasJSitemapCategoryId
			&& $guessItemid
			&& $this->hasCategorization($resultSourceObject, true)
			&& !preg_match('/categor|cats|catg/i', $resultSourceObject->chunks->table_maintable)
			&& $routeManifest = $this->loadRouteManifest($option)) {
			$toReplaceString = "SELECT " .
							   $this->dbInstance->quoteName($routeManifest->categories_table) . "." .
							   $this->dbInstance->quoteName($routeManifest->categories_table_id_field) .
							   " AS " . $this->dbInstance->quoteName('jsitemap_category_id') . ", ";
			$query = preg_replace("/SELECT/", $toReplaceString, $query, 1);
		}
		
		return $query;
	}
	
	/**
	 * Preprocessing at runtime for native content and third party extensions
	 * for the exclusive RSS format feed, thanks to adapter manifest allow to
	 * inject the extra field to extract the feed description
	 *
	 * @access private
	 * @param string $keyIndexTable The extension key to get the field from the manifest
	 * @param string $query The query to process
	 * @return string The processed query SQL string
	 */
	private function runtimeRssPreProcessing($keyIndexTable, $query) {
		// Get the description field based on table key
		$descField = $this->rssExtensionsManifest->{$keyIndexTable};

		// Build the query based on the fulltext field support
		if($this->cparams->get('rss_include_fulltext', 0) && in_array($keyIndexTable, array('c', '#__k2_items', '#__cddir_content'))) {
			$toReplaceString = "SELECT " .
							   "CONCAT_WS(' ', " .
							   $this->dbInstance->quoteName($keyIndexTable) . "." . $this->dbInstance->quoteName($descField) . "," .
							   $this->dbInstance->quoteName($keyIndexTable) . "." . $this->dbInstance->quoteName('fulltext') . ")" .
							   " AS " . $this->dbInstance->quoteName('jsitemap_rss_desc') . ", ";
		} else {
			$toReplaceString = "SELECT " .
							   $this->dbInstance->quoteName($keyIndexTable) . "." .
							   $this->dbInstance->quoteName($descField) .
							   " AS " . $this->dbInstance->quoteName('jsitemap_rss_desc') . ", ";
		}

		$query = preg_replace("/SELECT/", $toReplaceString, $query, 1);

		return $query;
	}
	
	/**
	 * sort a menu view
	 *
	 * @param
	 *        	array the menu
	 * @return array the sorted menu
	 */
	private function sortMenu($m, &$sourceParams) {
		$rootlevel = array ();
		$sublevels = array ();
		$newmenuitems = array();
		$r = 0;
		$s = 0;
		foreach ( $m as $item ) {
			if ($item->parent == 1) {
				// rootlevel
				$item->ebene = 0;
				$rootlevel [$r] = $item;
				$r ++;
			} else {
				// sublevel
				$item->ebene = 1;
				$sublevels [$s] = $item;
				$s ++;
			}
		}
		$maxlevels = $sourceParams->get ( 'maxlevels', '5' );
		$z = 0;
		if ($s != 0 and $maxlevels != 0) {
			foreach ( $rootlevel as $elm ) {
				$newmenuitems [$z] = $elm;
				$z ++;
				$this->sortMenuRecursive ( $z, $elm->id, $sublevels, 1, $maxlevels, $newmenuitems );
			}
		} else {
			$newmenuitems = $rootlevel;
		}
		return $newmenuitems;
	}
	
	/**
	 * sort a menu view Recursive through the tree
	 *
	 * @param
	 *        	int element number to work with
	 * @param
	 *        	int the parent id
	 * @param
	 *        	array the sublevels
	 * @param
	 *        	int the level
	 * @param
	 *        	int the maximun depth for the search
	 * @param
	 *        	array new menu
	 */
	private function sortMenuRecursive(&$z, $id, $sl, $ebene, $maxlevels, &$nm) {
		if ($ebene > $maxlevels) {
			return true;
		}
		foreach ( $sl as $selm ) {
			if ($selm->parent == $id) {
				$selm->ebene = $ebene;
				$nm [$z] = $selm;
				$z ++;
				$nebene = $ebene + 1;
				$this->sortMenuRecursive ( $z, $selm->id, $sl, $nebene, $maxlevels, $nm );
			}
		}
		return true;
	}
	 
	/**
	 * Get the Data for a view
	 * 
	 * @access private
	 * @param object $source
	 * @param array $accessLevels
	 * 
	 * @return Object
	 */
	private function getSourceData($source, $accessLevels) {
		// Create di un nuovo result source object popolato delle properties necessarie alla view e degli items recuperati da DB
		$resultSourceObject = new \stdClass ();
		$resultSourceObject->id = $source->id;
		$resultSourceObject->name = $source->name;
		$resultSourceObject->type = $source->type;
		if($source->sqlquery_managed) {
			$resultSourceObject->chunks = json_decode($source->sqlquery_managed);
		}
		
		// If sitemap format is gnews, allow only content data source and 3PD user data source that are supported as compatible, avoid calculate unuseful data and return immediately
		if($this->documentFormat === 'gnews') {
			if($source->type === 'menu') {
				return false;
			}
			if($source->type === 'user') {
				if(isset($resultSourceObject->chunks)) {
					if(!in_array($resultSourceObject->chunks->table_maintable, $this->supportedGNewsTablesOptions)) {
						return false;
					}
				}
			}
		}
		
		// If sitemap format is rss, allow only content data source and 3PD user data source that are supported as compatible, avoid calculate unuseful data and return immediately
		if($this->documentFormat === 'rss') {
			if($source->type === 'menu' || $source->type === 'links') {
				return false;
			}
			// Load always manifest, both content and third party data sources
			$this->rssExtensionsManifest = $this->loadManifest('rss');

			// If third party data sources check if it's supported, otherwise return false
			if($source->type === 'user' && isset($resultSourceObject->chunks)) {
				// Add dynamic Virtuemart
				$vmProperty = '#__virtuemart_products_' . $this->siteLanguageRFC;
				$this->rssExtensionsManifest->{$vmProperty} = 'product_desc';

				// Skip extensions not supported for RSS feed generation
				if(!property_exists($this->rssExtensionsManifest, $resultSourceObject->chunks->table_maintable)) {
					return false;
				}
			}
		}
		
		// Already a Registry object! Please note object cloning to avoid reference overwriting!
		// Component -> menu view specific level params override
		$resultSourceObject->params = clone($this->cparams); 
		// Item specific level params override
		$resultSourceObject->params->merge(new Registry($source->params ));
		
		// Ensure the current datasource is enabled for the current sitemap format otherwise skip processing
		if(!$resultSourceObject->params->get('htmlinclude', 1) && $this->documentFormat == 'html') {
			return false;
		}
		if(!$resultSourceObject->params->get('xmlinclude', 1) && $this->documentFormat == 'xml') {
			return false;
		}
		if(!$resultSourceObject->params->get('xmlimagesinclude', 1) && $this->documentFormat == 'images') {
			return false;
		}
		if(!$resultSourceObject->params->get('xmlmobileinclude', 1) && $this->documentFormat == 'mobile') {
			return false;
		}
		if(!$resultSourceObject->params->get('gnewsinclude', 1) && $this->documentFormat == 'gnews') {
			return false;
		}
		if(!$resultSourceObject->params->get('rssinclude', 1) && $this->documentFormat == 'rss') {
			return false;
		}
		
		// ACL filtering
		$disableAcl = $resultSourceObject->params->get('disable_acl');

		$sourceItems = array();
		switch ($source->type) {
			case 'user':
				// Get the language param for the user data source and ensure that it's all langs or match the current language, if not skip getting data
				$languageTag = JMapMultilang::getCurrentSefLanguage();
				$dataSourceLanguage = $resultSourceObject->params->get('datasource_language', '*');
				if($dataSourceLanguage != '*' && ($languageTag != $dataSourceLanguage)) {
					// Detected a precaching call, set 0 affected rows to complete correctly the precaching process just now
					if($this->limitRows) {
						$this->setState('affected_rows', 0);
					}
					break;
				}
				
				$query = $source->sqlquery;
				$debugMode = $resultSourceObject->params->get('debug_mode', 0);
				// Do runtime preprocessing if any for selected data source extension
				$query = $this->runtimePreProcessing($query, $resultSourceObject);
				// Se la raw query è stata impostata
				if($query) {
					$query = str_replace('{aid}', '(' . implode(',', $accessLevels) . ')', $query);
					$query = str_replace('{langtag}', $this->dbInstance->quote($this->langTag), $query);
					$query = str_replace('{languagetag}', $this->dbInstance->quote($this->langTag), $query);
					$query = str_replace('{languagetagrfc}', $this->siteLanguageRFC, $query);
					
					// Manage for latest months placeholder if found one
					if(preg_match("/'?{(\d+)months}'?/i", $query, $matches)) {
						$minValidCreatedDate = gmdate ( "Y-m-d H:i:s", strtotime ( "-" . $matches[1] . " months", time()));
						// All items need to be created after the minimum valid created date
						$query = preg_replace("/'?{(\d+)months}'?/i", $this->dbInstance->quote($minValidCreatedDate), $query);
					}
					
					// Runtime preprocessing for RSS description field
					if($this->documentFormat === 'rss') {
						$query = $this->runtimeRssPreProcessing($resultSourceObject->chunks->table_maintable, $query);
					}
					
					try {
						// Check if a limit for query rows has been set, this means we are in precaching process by JS App client
						if(!$this->limitRows) {
							$this->dbInstance->setQuery ( $query );
						} else {
							$dbQueryObject = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
							$dbQueryObject->setQuery ( $query )->setLimit ( $this->limitRows, $this->limitStart );
							$this->dbInstance->setQuery ( $dbQueryObject );
						}
						
						// Security safe check: only SELECT allowed
						if(preg_match('/((?<!`)delete|update|insert|password)/i', $query)) {
							throw new JMapException(sprintf(Text::_('COM_JMAP_QUERY_NOT_ALLOWED_FROM_USER_DATASOURCE' ), $source->name), 'warning');
						}
						$sourceItems = $this->dbInstance->loadObjectList ();

						// Detected a precaching call, so store in the model state the number of affected rows for JS app
						if($this->limitRows) {
							$this->setState('affected_rows', count($sourceItems));
						}
						
						// Start subQueriesPostProcessor if needed for nested multilevel categories
						if($resultSourceObject->params->get('multilevel_categories', 0) && $this->hasCategorization($resultSourceObject)) {
							// Pre assignment
							$resultSourceObject->data = $sourceItems;
							// Start post processor
							$this->subQueriesPostProcessor($resultSourceObject);
						}
					} catch (JMapException $e) {
						if($e->getExceptionLevel() == 'notice' && $debugMode) {
							$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
						} elseif($e->getExceptionLevel() != 'notice') {
							$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
						}
						$resultSourceObject->data = array();
						return $resultSourceObject;
					} catch (\Exception $e) {
						$jmapException = new JMapException($e->getMessage(), 'warning');
						$this->app->enqueueMessage(sprintf(Text::_('COM_JMAP_ERROR_RETRIEVING_DATA_FROM_USER_DATASOURCE' ), $source->name), 'warning');
						if($debugMode) {
							$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
						}
						$resultSourceObject->data = array();
						return $resultSourceObject;
					}
					
					/**
					 * Load croutehelpers manifest - Integration for components performing route helper directly in the main router such as Virtuemart
					 * The component router must be executed BEFORE the SiteRouter::buildSefRoute to allow the Itemid to be already found by the crouter
					 */
					$crouteHelpersManifest = $this->loadManifest('croutehelpers', true);
					if (array_key_exists($resultSourceObject->chunks->option, $crouteHelpersManifest)) {
						$routeHelperPreprocess = $this->app->get('jmap_croute_helpers_preprocess', []);
						$routeHelperPreprocess[$resultSourceObject->chunks->option] = true;
						$this->app->set('jmap_croute_helpers_preprocess', $routeHelperPreprocess);
					}
				}
				break;
				
			case 'menu':
				$menuAccess = null;
				$originalSourceItems = array();
				$autoExcludeNoIndex = null;
				// Unpublished items
				$doUnpublishedItems = $resultSourceObject->params->get('dounpublished', 0);
				// Exclusion menu
				$subQueryExclusion = null;
				$exclusionMenuItems = $resultSourceObject->params->get('exclusion', array());
				if($exclusionMenuItems && !is_array($exclusionMenuItems)) {
					$exclusionMenuItems = array($exclusionMenuItems);
				}
				if(count($exclusionMenuItems) && !in_array('0', $exclusionMenuItems)) {
					$subQueryExclusion = "\n AND menuitems.id NOT IN (" . implode(',', $exclusionMenuItems) . ")";
				}
				
				$queryChunk = null;
				$queryChunkDate = null;
				if(!$doUnpublishedItems) {
					$dbQuery = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
					
					$now = gmdate('Y-m-d H:i:s', time());
					$queryChunk = "\n AND menuitems.published = 1";
					$queryChunkDate = "\n AND ( " . $dbQuery->isNullDatetime('menuitems.publish_up') . " OR menuitems.publish_up <= '$now' )" .
									  "\n AND ( " . $dbQuery->isNullDatetime('menuitems.publish_down') . " OR menuitems.publish_down >= '$now' )";
				}
				
				// Filter by access if ACL option enabled
				if($disableAcl !== 'disabled') {
					$menuAccess = "\n AND menuitems.access IN ( " . implode(',', $accessLevels) . " )";
				}
				
				// Filter by language only if multilanguage is correctly enabled by Joomla! plugin
				$menuLanguageFilter = null;
				if(JMapMultilang::isEnabled()) {
					$menuLanguageFilter = "\n AND ( menuitems.language = " . $this->dbInstance->quote('*') . " OR menuitems.language = " . $this->dbInstance->quote($this->langTag) . " ) ";
				}
				
				// Auto exclude noindex articles from XML sitemaps
				$format = $this->getState('format');
				if($format != 'html' && $this->cparams->get('auto_exclude_noindex', 0)) {
					$autoExcludeNoIndex = "\n AND (menuitems.params NOT REGEXP 'noindex')";
				}
				
				$menuQueryItems = "SELECT menuitems.*, menuitems.parent_id AS parent, menuitems.level AS sublevel, menuitems.title AS name, menupriorities.priority" .
								  "\n FROM #__menu as menuitems" .
								  "\n INNER JOIN #__menu_types AS menutypes" .
								  "\n ON menuitems.menutype = menutypes.menutype" .
								  "\n LEFT JOIN #__jmap_menu_priorities AS menupriorities" .
								  "\n ON menupriorities.id = menuitems.id" .
								  "\n WHERE	menuitems.client_id = 0 AND menuitems.published >= 0" . $queryChunk . $queryChunkDate .
								  $menuAccess .
								  $autoExcludeNoIndex .
								  "\n AND menutypes.title = " . $this->dbInstance->quote($source->name) .
								  $menuLanguageFilter .
								  $subQueryExclusion .
								  "\n ORDER BY menuitems.menutype, menuitems.parent_id, menuitems.level, menuitems.lft";
				try {
					// Check if a limit for query rows has been set, this means we are in precaching process by JS App client
					if(!$this->limitRows) {
						$this->dbInstance->setQuery ( $menuQueryItems );
					} else {
						$dbQueryObject = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
						$dbQueryObject->setQuery ( $menuQueryItems )->setLimit ( $this->limitRows, $this->limitStart );
						$this->dbInstance->setQuery ( $dbQueryObject );
					}
					$originalSourceItems = $this->dbInstance->loadObjectList();

					// Detected a precaching call, so store in the model state the number of affected rows for JS app
					if($this->limitRows) {
						$this->setState('affected_rows', count($originalSourceItems));
					}
					// Recursive ordering for menu rows ONLY if HTML format, otherwise make no sense so save resources and time
					if($this->documentFormat == 'html') {
						$sourceItems = $this->sortMenu ( $originalSourceItems, $resultSourceObject->params);
					} else {
						$sourceItems = $originalSourceItems;
					}
				} catch (JMapException $e) {
					$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
					$resultSourceObject->data = array();
					return $resultSourceObject;
				} catch (\Exception $e) {
					$jmapException = new JMapException($e->getMessage(), 'error');
					$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
					$resultSourceObject->data = array();
					return $resultSourceObject;
				}
				break;
				
			case 'content':
				$access = null;
				$catAccess = null;
				$limitRecent = null;
				$autoExcludeNoIndex = null;
				$dynamicSelectFields = null;
				$dynamicOrdering = null;
				$categoriesJoin = 'RIGHT';
				$defaultOrdering = 'c.ordering';
				$usersTableJoin = null;
				$dbQuery = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
				$exclusionWay =  $resultSourceObject->params->get('choose_exclusion_way', 'exclude');
				$exclusionWayOperator = $exclusionWay == 'exclude' ? 'NOT' : '';
				$now = gmdate('Y-m-d H:i:s', time());
				// Exclusion access for Google News Sitemap and if ACL is disabled
				$format = $this->getState('format');

				// Set select fields only if html sitemap format is detected, save memory if XML and not needed
				if($format == 'html') {
					$dynamicSelectFields = 'c.title AS title, cat.title AS category, cat.level,';
				}
				
				// Set select fields only if RSS sitemap format is detected, save memory if not needed
				if($format == 'rss' || $format == 'gnews') {
					$dynamicSelectFields = 'c.title AS title, cat.title AS category,';
				}
				
				// Check if article images are required to be added to the RSS feed text desc
				if($format == 'rss' && (int)$this->cparams->get('rss_include_images', 0)) {
					$dynamicSelectFields .= 'c.images,';
				}
				
				// Check if article author is required to be added to the RSS feed
				if($format == 'rss' && (int)$this->cparams->get('rss_include_author', 0)) {
					$usersTableJoin = "\n LEFT JOIN " . $this->dbInstance->quoteName('#__users') . " AS u ON c.created_by = u.id";
					$dynamicSelectFields .= 'u.name AS authorname, u.email AS authoremail,';
				}
				
				// Manage content articles order
				if($format != 'html' && (int)$resultSourceObject->params->get('orderbydate', 0) == 1) {
					$dynamicOrdering = "created DESC,";
				}
				
				// Manage content articles order
				if($format != 'rss' && $format != 'html' && (int)$resultSourceObject->params->get('orderbydate', 0) == 2) {
					$dynamicOrdering = "modified DESC,";
				}
				
				// Manage content articles order
				if($format != 'html' && (int)$resultSourceObject->params->get('orderbydate', 0) == 3) {
					$dynamicOrdering = "publish_up DESC,";
				}
				
				// Fallback for rss feed always created desc
				if($format == 'rss' && !$dynamicOrdering) {
					$dynamicOrdering = "created DESC,";
				}
				
				// Manage content articles order for the HTML sitemap format
				if($format == 'html' && $resultSourceObject->params->get('orderbyalpha', 0)) {
					$defaultOrdering = "c.title ASC";
				}
				
				if($format != 'gnews' && $disableAcl !== 'disabled') {
					$access = "\n AND c.access IN ( " . implode(',', $accessLevels) . " )";
					$catAccess = "\n AND cat.access IN ( " . implode(',', $accessLevels) . " )";
				}
				
				// Choose to limit valid articles for Google News Sitemap to last n most recent days
				if($format == 'gnews' && $this->cparams->get('gnews_limit_recent', false)) {
					$validDays = $this->cparams->get('gnews_limit_valid_days', 2);
					$limitRecent = "\n AND UNIX_TIMESTAMP(c.publish_up) > " . (time() - (24 * 60 * 60 * $validDays));
				}
				
				// Choose to limit valid articles for the RSS feed to last n most recent days
				if($format == 'rss' && (int)$this->cparams->get('rss_limit_valid_days', null)) {
					$validDays = (int)$this->cparams->get('rss_limit_valid_days');
					$limitRecent = "\n AND UNIX_TIMESTAMP(c.publish_up) > " . (time() - (24 * 60 * 60 * $validDays));
				}
				
				// Choose to filter only by featured articles
				$innerJoinFeaturedInclusion = null;
				$limitFeatured = null;
				if((int)$resultSourceObject->params->get('limit_featured_articles', 0)) {
					// LEFT JOIN with #__content_frontpage for time featured
					$innerJoinFeaturedInclusion = "\n INNER JOIN " . $this->dbInstance->quoteName('#__content_frontpage') . " AS cf ON c.id = cf.content_id";
					$limitFeatured = "\n AND c.featured = 1" .
									 "\n AND ( " . $dbQuery->isNullDatetime('cf.featured_up') . " OR cf.featured_up <= '$now' )" .
					 				 "\n AND ( " . $dbQuery->isNullDatetime('cf.featured_down') . " OR cf.featured_down >= '$now' )";
				}
				
				// Exclusion categories
				$subQueryCatExclusion = null;
				$subQueryCategoryExclusion = null;
				$exclusionCategories = $resultSourceObject->params->get('catexclusion', array());
				// Normalize select options
				if($exclusionCategories && !is_array($exclusionCategories)) {
					$exclusionCategories = array($exclusionCategories);
				}
				
				// Exclusion children categories da table orm nested set model
				if(count($exclusionCategories)) {
					$categoriesTableNested = new JTableCategory($this->dbInstance);
					$children = array();
					foreach ($exclusionCategories as $topCatID) {
						// Load Children categories se presenti
						$categoriesTableNested->load($topCatID);
						$tempChildren = $categoriesTableNested->getTree();
						if(is_array($tempChildren) && count($tempChildren)) {
							foreach ($tempChildren as $child) {
								if(!in_array($child->id, $children) && !in_array($child->id, $exclusionCategories)) {
									$exclusionCategories[] = $child->id;
								}
							} 
						}
					}
					 
					$subQueryCatExclusion = "\n AND c.catid $exclusionWayOperator IN (" . implode(',', $exclusionCategories) . ")";
					$subQueryCategoryExclusion = "\n AND cat.id $exclusionWayOperator IN (" . implode(',', $exclusionCategories) . ")";
				}
				
				// Exclusion articles
				$subQueryArticleExclusion = null;
				$exclusionArticles = $resultSourceObject->params->get('articleexclusion', array());
				// Normalize select options
				if($exclusionArticles && !is_array($exclusionArticles)) {
					$exclusionArticles = array($exclusionArticles);
				}
				if(count($exclusionArticles)) {
					$subQueryArticleExclusion = "\n AND c.id $exclusionWayOperator IN (" . implode(',', $exclusionArticles) . ")";
				}
				
				// Evaluate content levels to include
				$includeArchived = $this->cparams->get('include_archived', 0);
				$contentLevel = $includeArchived ? ' > 0' : ' = 1';
				
				// Inclusion by stage
				$innerJoinStageInclusion = null;
				$subQueryStageInclusion = null;
				$inclusionStages = $resultSourceObject->params->get('articlestages', array());
				// Normalize select options
				if($inclusionStages && !is_array($inclusionStages)) {
					$inclusionStages = array($inclusionStages);
				}
				if(count($inclusionStages) && !in_array('0', $inclusionStages)) {
					$innerJoinStageInclusion = "\n INNER JOIN #__workflow_associations AS stages ON c.id = stages.item_id";
					$subQueryStageInclusion = "\n AND stages.stage_id IN (" . implode(',', $inclusionStages) . ")" .
											  "\n AND stages.extension = " .  $this->dbInstance->quote('com_content.article');
					$contentLevel = ' >= -2';
				}
				
				// Filter by language only if multilanguage is correctly enabled by Joomla! plugin
				$contentLanguageFilter = null;
				$categoryLanguageFilter = null;
				if(JMapMultilang::isEnabled()) {
					$contentLanguageFilter = "\n AND ( c.language = " . $this->dbInstance->quote('*') . " OR c.language = " . $this->dbInstance->quote($this->langTag) . " ) ";
					$categoryLanguageFilter = "\n AND ( cat.language = " . $this->dbInstance->quote('*') . " OR cat.language = " . $this->dbInstance->quote($this->langTag) . " ) ";
				}
				
				// Check if pagebreaks analysis is required
				$pageBreaksFullText = null;
				if($pageBreaksLinks = $this->cparams->get('show_pagebreaks', 0)) {
					$pageBreaksFullText = "\n ,CONCAT(c.introtext, c.fulltext) AS completetext";
				}
				
				// Check if limit by recent months is set for content data source
				// Manage for latest months placeholder if found one
				$limitLatestItems = null;
				if($monthsLimit = $resultSourceObject->params->get('created_date', null)) {
					$minValidCreatedDate = gmdate ( "Y-m-d H:i:s", strtotime ( "-" . $monthsLimit . " months", time()));
					$limitLatestItems = "\n AND c.created > " . $this->dbInstance->quote($minValidCreatedDate);
					// If we are on a precaching process plus a limit by recent month avoid empty records because of the RIGHT JOIN #__categories
					if($this->limitRows) {
						$categoriesJoin = 'LEFT';
					}
				}

				// Auto exclude noindex articles from XML sitemaps
				if($format != 'html' && $this->cparams->get('auto_exclude_noindex', 0)) {
					$autoExcludeNoIndex = "\n AND (c.metadata NOT REGEXP 'noindex')";
				}
				
				$contentQueryItems = "SELECT c.id, c.alias, c.language, c.publish_up, c.access, c.metakey, catspriorities.priority," .
									 $dynamicSelectFields .
									 "\n cat.id AS catid," .
									 "\n UNIX_TIMESTAMP(c.created) AS created," .
									 "\n UNIX_TIMESTAMP(c.modified) AS modified," .
									 "\n UNIX_TIMESTAMP(cat.modified_time) AS cat_modified," .
									 "\n c.catid AS catslug" .
									 $pageBreaksFullText .
									 "\n FROM " . $this->dbInstance->quoteName('#__content') . " AS c" .
									 "\n LEFT JOIN #__jmap_cats_priorities AS catspriorities ON catspriorities.id = c.catid" .
									 $usersTableJoin .
									 "\n $categoriesJoin JOIN #__categories AS cat ON cat.id = c.catid" .
									 $innerJoinStageInclusion .
									 $innerJoinFeaturedInclusion .
									 "\n AND c.state $contentLevel".
									 "\n AND ( " . $dbQuery->isNullDatetime('c.publish_up') . " OR c.publish_up <= '$now' )" .
									 "\n AND ( " . $dbQuery->isNullDatetime('c.publish_down') . " OR c.publish_down >= '$now' )" .
									 $limitRecent .
									 $limitFeatured .
									 $limitLatestItems .
									 $access .
									 $contentLanguageFilter .
									 $subQueryCatExclusion .
									 $subQueryArticleExclusion .
									 $autoExcludeNoIndex .
									 "\n WHERE cat.published = '1'" .
									 $catAccess .
									 "\n AND cat.extension = " . $this->dbInstance->quote('com_content') .
									 $categoryLanguageFilter .
									 $subQueryCategoryExclusion .
									 $subQueryStageInclusion .
									 "\n ORDER BY $dynamicOrdering cat.lft, $defaultOrdering";
				
				// Runtime preprocessing for RSS description field
				if($this->documentFormat === 'rss') {
					$contentQueryItems = $this->runtimeRssPreProcessing('c', $contentQueryItems);
				}

				try {
					// Check if a limit for query rows has been set, this means we are in precaching process by JS App client
					if(!$this->limitRows) {
						$this->dbInstance->setQuery ( $contentQueryItems );
					} else {
						$dbQueryObject = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
						$dbQueryObject->setQuery ( $contentQueryItems )->setLimit ( $this->limitRows, $this->limitStart );
						$this->dbInstance->setQuery ( $dbQueryObject );
					}
					$sourceItems = $this->dbInstance->loadObjectList ();
					
					// Detected a precaching call, so store in the model state the number of affected rows for JS app
					if($this->limitRows) {
						$this->setState('affected_rows', count($sourceItems));
					}
					
					// Sub article pagebreaks processing
					if($pageBreaksLinks) {
						foreach ($sourceItems as $article) {
							$this->addPagebreaks($article);
						}
					}
				} catch (JMapException $e) {
					$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
					$resultSourceObject->data = array();
					return $resultSourceObject;
				} catch (\Exception $e) {
					$jmapException = new JMapException($e->getMessage(), 'error');
					$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
					$resultSourceObject->data = array();
					return $resultSourceObject;
				}
				break;
				
			case 'plugin':
				// Get the language param for the user data source and ensure that it's all langs or match the current language, if not skip getting data
				$languageTag = JMapMultilang::getCurrentSefLanguage();
				$dataSourceLanguage = $resultSourceObject->params->get('datasource_language', '*');
				if($dataSourceLanguage != '*' && ($languageTag != $dataSourceLanguage)) {
					// Detected a precaching call, set 0 affected rows to complete correctly the precaching process just now
					if($this->limitRows) {
						$this->setState('affected_rows', 0);
					}
					break;
				}
				
				// Call the plugin interface and retrieve data
				$pluginName = strtolower($source->name);
				$className = '\JMapFilePlugin' . ucfirst($source->name);
				try {
					// Check if the plugin interface implementation exists
					if(!file_exists(JPATH_ADMINISTRATOR . '/components/com_jmap/plugins/' . $pluginName . '/' . $pluginName . '.php')) {
						throw new JMapException(sprintf(Text::_('COM_JMAP_ERROR_PLUGIN_DATASOURCE_NOT_EXISTS' ), $pluginName . '.php'), 'warning');
					}
					// Include for multiple instances of this data source
					include_once JPATH_ADMINISTRATOR . '/components/com_jmap/Framework/File/Plugin.php';
					include_once JPATH_ADMINISTRATOR . '/components/com_jmap/plugins/' . $pluginName . '/' . $pluginName . '.php';

					// Check if the concrete class exists now
					if(!class_exists($className)) {
						throw new JMapException(sprintf(Text::_('COM_JMAP_ERROR_PLUGIN_CLASS_NOT_EXISTS' ), $className), 'warning');
					}
					
					// Load the language file for the plugin, manage partial language translations
					$jLang = $this->app->getLanguage();
					$jLang->load($pluginName, JPATH_ADMINISTRATOR . '/components/com_jmap/plugins/' . $pluginName, 'en-GB', true, true);
					if($jLang->getTag() != 'en-GB') {
						$jLang->load($pluginName, JPATH_ADMINISTRATOR . '/components/com_jmap/plugins/' . $pluginName, null, true, false);
					}
					
					// Manage B/C using class aliases for J3 autoloading, thus JSitemap plugins work on J3 and J4+ns
					// Core Joomla classes used throughout data source plugins
					if(!class_exists('JFactory')) {
						class_alias('\\Joomla\\CMS\\Factory', 'JFactory');
					}
					if(!class_exists('JRegistry')) {
						class_alias('\\Joomla\\Registry\\Registry', 'JRegistry');
					}
					if(!class_exists('JDatabase')) {
						class_alias('\\Joomla\\Database\\DatabaseDriver', 'JDatabase');
					}
					if(!class_exists('JComponentHelper')) {
						class_alias('\\Joomla\\CMS\\Component\\ComponentHelper', 'JComponentHelper');
					}
					if(!class_exists('JApplicationHelper')) {
						class_alias('\\Joomla\\CMS\\Application\\ApplicationHelper', 'JApplicationHelper');
					}
					if(!class_exists('JArrayHelper')) {
						class_alias('\\Joomla\\Utilities\\ArrayHelper', 'JArrayHelper');
					}
					if(!class_exists('JString')) {
						class_alias('\\Joomla\\String\\StringHelper', 'JString');
					}
					if(!class_exists('JText')) {
						class_alias('\\Joomla\\CMS\\Language\\Text', 'JText');
					}
					if(!class_exists('JUri')) {
						class_alias('\\Joomla\\CMS\\Uri\\Uri', 'JUri');
					}
					if(!class_exists('JFolder')) {
						class_alias('\\Joomla\\CMS\\Filesystem\\Folder', 'JFolder');
					}
					if(!class_exists('JDate')) {
						class_alias('\\Joomla\\CMS\\Date\\Date', 'JDate');
					}
					// JSitemap framework classes used throughout data source plugins
					if(!class_exists('JMapModel')) {
						class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Model', 'JMapModel');
						class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Exception', 'JMapException');
						class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Language\\Multilang', 'JMapLanguageMultilang');
						class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Route\\Helper', 'JMapRouteHelper');
						class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Route\\Menu', 'JMapRouteMenu');
					}
					
					// Instantiate the plugin class, inject $this class and manage limitRows for precaching in third party plugins
					$pluginInstance = new $className();
					$retrievedData = $pluginInstance->getSourceData($resultSourceObject->params, $this->dbInstance, $this);

					// 1) first structure required: plain list of items -> PLAIN LIST OF ELEMENTS
					if(!array_key_exists('items', $retrievedData)) {
						throw new JMapException(sprintf(Text::_('COM_JMAP_ERROR_PLUGIN_NODATA_RETURNED' ), $pluginName), 'warning');
					}
					$sourceItems = $retrievedData['items'];

					// Check if additional structures for nested categories tree are returned
					// 2) second structure optional: plain list of items grouped by cats -> LIST OF ELEMENTS GROUPED BY PLAIN CATS STRUCTURE
					if(array_key_exists('items_tree', $retrievedData)) {
						$resultSourceObject->itemsTree = $retrievedData['items_tree'];
					}
					// 3) third structure optional: nested tree of cats by parents -> LIST OF ELEMENTS GROUPED BY NESTED CATS STRUCTURE
					if(array_key_exists('categories_tree', $retrievedData)) {
						$resultSourceObject->categoriesTree = $retrievedData['categories_tree'];
					}
				} catch (JMapException $e) {
					if($e->getExceptionLevel() == 'notice' && $debugMode) {
						$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
					} elseif($e->getExceptionLevel() != 'notice') {
						$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
					}
					$resultSourceObject->data = array();
					return $resultSourceObject;
				} catch (\Exception $e) {
					$debugMode = $this->cparams->get('enable_debug', 0);
					$jmapException = new JMapException($e->getMessage(), 'warning');
					$this->app->enqueueMessage(sprintf(Text::_('COM_JMAP_ERROR_RETRIEVING_DATA_FROM_USER_DATASOURCE' ), $source->name), 'warning');
					if($debugMode) {
						$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
					}
					$resultSourceObject->data = array();
					return $resultSourceObject;
				}
				break;
				
			case 'links':
				// Re-initialize the $sourceItems var as an object not an array
				$sourceItems = new \stdClass();
				$sourceItems->title = array();
				$sourceItems->link = array();
				
				// Get the language param for the links data source and ensure that it's all langs or match the current language, if not skip getting data
				$languageTag = JMapMultilang::getCurrentSefLanguage();
				$dataSourceLanguage = $resultSourceObject->params->get('datasource_language', '*');
				if($dataSourceLanguage != '*' && ($languageTag != $dataSourceLanguage)) {
					// Detected a precaching call, set 0 affected rows to complete correctly the precaching process just now
					if($this->limitRows) {
						$this->setState('affected_rows', 0);
					}
					break;
				}
				
				// Check if a limit for query rows has been set, this means we are in precaching process by JS App client
				if(!$this->limitRows) {
					// Get directly the links from the source
					$sourceItems = $resultSourceObject->chunks;
				} else {
					// Get directly the links from the source
					$sourceItems->title = array_slice($resultSourceObject->chunks->title, $this->limitStart, $this->limitRows, false);
					$sourceItems->link = array_slice($resultSourceObject->chunks->link, $this->limitStart, $this->limitRows, false);
					
					// Detected a precaching call, so store in the model state the number of affected rows for JS app
					$this->setState('affected_rows', count($sourceItems->link));
				}
			
				break;
		}
		 
		// Final assignment
		$resultSourceObject->data = $sourceItems;
	 
		return $resultSourceObject;
	}
	
	/**
	 * Get available sitemap source
	 * @access private
	 * @return array
	 */
	private function getSources() {
		$join = null;
		$where = array();
		
		// Check exclude from menu view params data sources IDs
		$filterDataSource = $this->getState('cparams')->get('datasource_filter', array());
		// Only if first array item is not the 'No filter' false first option of multiselect
		if(!empty($filterDataSource) && $filterDataSource[0]) {
			$where[] = "\n v.id IN (" . implode(',', $filterDataSource) . ")";
		}
		
		// Check if JS client app has set some data source restrictions
		if($dataSourceID = $this->getState('datasourceid', null)) {
			$where[] = "\n v.id = " . $this->dbInstance->quote($dataSourceID);
		}
		
		// Check if some restrictions based on dataset filter are found
		$filterDataset = $this->app->getInput()->getInt('dataset', null);
		if(!empty($filterDataset)) {
			$join = "\n INNER JOIN #__jmap_dss_relations AS dss" .
					"\n ON v.id = dss.datasourceid";
			$where[] = "\n dss.datasetid = " . (int)$filterDataset;
		}
		
		// Default for published data sources
		$where[] = "\n v.published = 1";
		
		$query = "SELECT v.*" .
				 "\n FROM #__jmap AS v" .
				 $join .
				 "\n WHERE " . implode(' AND ', $where) .
				 "\n ORDER BY v.ordering ASC";
		$this->dbInstance->setQuery ( $query );
		
		$this->sources = $this->dbInstance->loadObjectList ();
		
		return $this->sources;
	}
	
	/**
	 * Load manifest file for this type of data source
	 * @access private
	 * @return mixed
	 */
	private function loadManifest($option, $asArray = false) {
		// Load configuration manifest file
		$fileName = JPATH_SITE . '/components/com_jmap/manifests/' . $option . '.json';
		
		// Check if file exists and is valid manifest
		if(!file_exists($fileName)) {
			return false;
		}
		
		// Load the manifest serialized file and assign to local variable
		$manifest = file_get_contents($fileName);
		$manifestConfiguration = json_decode($manifest, $asArray);
		
		return $manifestConfiguration;
	}
	
	/**
	 * Load manifest file for this type of data source
	 * @access private
	 * @return mixed
	 */
	private function loadRouteManifest($option) {
		// Load configuration manifest file
		$fileName = JPATH_ADMINISTRATOR . '/components/com_jmap/Framework/Route/manifests/' . $option . '.json';
	
		// Check if file exists and is valid manifest
		if(!file_exists($fileName)) {
			return false;
		}
	
		// Load the manifest serialized file and assign to local variable
		$manifest = file_get_contents($fileName);
		$manifestConfiguration = json_decode($manifest);
	
		return $manifestConfiguration;
	}
	
	/**
	 * Detect if a data source has ideally a categorization active, through title categorization param set and not empty
	 * 
	 * @access private
	 * @param Object $dataSource
	 * @param boolean $ignoreFormat
	 * @return boolean
	 */
	private function hasCategorization($dataSource, $ignoreFormat = false) {
		// Check first of all for right focument format, used only for presentation purpouse for HTML document format
		if(!is_null($this->documentFormat) && $ignoreFormat === false) {
			if($this->documentFormat != 'html') {
				return false;
			}
		}
		
		// Check if data source is elated to categories entities itself
		if(preg_match('/categor|cats|catg|igallery/i', $dataSource->chunks->table_maintable)) {
			return true;
		}
		
		// Check if a valid field has been chosen and activated for category titles
		if(isset($dataSource->chunks->use_category_title_jointable1) && $dataSource->chunks->use_category_title_jointable1) {
			return true;
		}
		if(isset($dataSource->chunks->use_category_title_jointable2) && $dataSource->chunks->use_category_title_jointable2) {
			return true;
		}
		if(isset($dataSource->chunks->use_category_title_jointable3) && $dataSource->chunks->use_category_title_jointable3) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Fetch default table fields used to filter out categories query
	 *
	 * @access protected
	 * @param string $tableName
	 * @return array 
	 */
	protected function fetchAutoInjectDefaultWhereFields($tableName, $manifestConfig, $source) {
		$whereConditions = array();
		$excludeConditions = isset($manifestConfig->category_exclude_condition) ? $manifestConfig->category_exclude_condition : array();
		// Get required maintable table fields, if not valid throw exception
		$columnsQuery = "SHOW COLUMNS FROM " . $this->dbInstance->quoteName($tableName);
		try {
			$this->dbInstance->setQuery($columnsQuery);
			if($tableFields = $this->dbInstance->loadColumn()) {
				// *AUTO WHERE PART* injected fields
				if(is_array($tableFields) && count($tableFields)) {
					// Published field supported
					if(in_array('published', $tableFields) && !(in_array('published', $excludeConditions))) {
						$whereConditions[] = $this->dbInstance->quoteName($tableName) . "." . $this->dbInstance->quoteName('published') . " = " . $this->dbInstance->quote(1);
					} elseif(in_array('state', $tableFields) && !(in_array('state', $excludeConditions))) { // State field supported fallback
						$whereConditions[] = $this->dbInstance->quoteName($tableName) . "." . $this->dbInstance->quoteName('state') . " = " . $this->dbInstance->quote(1);
					}
						
					// Access field supported
					if(in_array('access', $tableFields) && !(in_array('access', $excludeConditions))) {
						if(!$this->accessLevel) {
							// Load access level if not loaded
							$user = $this->app->getIdentity();
							$this->accessLevel = $user->getAuthorisedViewLevels();
						}
						$whereConditions[] = $this->dbInstance->quoteName($tableName) . "." . $this->dbInstance->quoteName('access') . " IN (" . implode(',', $this->accessLevel) . ")";
					}
				
					// Language field supported
					if(in_array('language', $tableFields) && !(in_array('language', $excludeConditions))) {
						$whereConditions[] =  " (" . $this->dbInstance->quoteName($tableName) . "." . $this->dbInstance->quoteName('language') . " = " . $this->dbInstance->quote('*') .
											  " OR " . $this->dbInstance->quoteName($tableName) . "." . $this->dbInstance->quoteName('language') . " = " . $this->dbInstance->quote('') .
											  " OR " . $this->dbInstance->quoteName($tableName) . "." . $this->dbInstance->quoteName('language')  . " = " . $this->dbInstance->quote($this->langTag) . ")";
					}
					
					// Explicit category conditions from manifest config file
					if(isset($manifestConfig->category_condition) && is_array($manifestConfig->category_condition)) {
						foreach ($manifestConfig->category_condition as $condition) {
							if(!empty($source->chunks->where1_maintable) && !empty($source->chunks->where1_value_maintable) && strpos($condition, '$$$')) {
								if(strpos($source->chunks->where1_value_maintable, ',')) {
									$condition = str_replace('@', ' IN(', $condition);
									$condition = str_replace('$$$', $source->chunks->where1_value_maintable . ')', $condition);
								} else {
									$condition = str_replace('@', '=', $condition);
									$condition = str_replace('$$$', $source->chunks->where1_value_maintable, $condition);
								}
							} elseif(strpos($condition, '$$$')) {
								$condition = null;
							}
							
							// Assignment if valid $condition
							if($condition) {
								$whereConditions[] = $condition;
							}
						}
					}
				}
			}
		} catch (\Exception $e) {
			return false;
		}
		
		return $whereConditions;
		
	}
	
	/**
	 * Postprocessing at runtime for third party extensions subqueries
	 * that generate data for nested cats tree
	 * New data will be appended to catsTree property of resultSourceObject
	 *
	 * @access protected
	 * @param Object $source
	 * @return array An array of objects, one for products by cat and one for catChildren by cat, also assigned to $source properties to be used inside view
	 */
	protected function subQueriesPostProcessor($source, $manifest = null) {
		// Replace these variables reading from manifest json file
		if($manifest) {
			$manifestConfiguration = json_decode($manifest);
		} else {
			$manifestConfiguration = $this->loadManifest ($source->chunks->option);
			if($manifestConfiguration === false) {
				return false;
			}	
		}
		
		// Error decoding configuration object, exit and fallback to standard indenting
		if(!is_object($manifestConfiguration)) {
			throw new JMapException(Text::sprintf('COM_JMAP_ERROR_MULTILEVELCATS_MANIFEST_ERROR', $source->name), 'notice');
		}
		
		// Detect if cat recoursion is enabled
		if($manifestConfiguration->catrecursion) {
			// Enable cat recursion for this data source
			$source->catRecursion = true;
		} else {
			return false;
		}
		
		// Recursion type not adiacency, already managed natively by raw sql query compiler and standard indenting by level value
		if(!in_array($manifestConfiguration->recursion_type, array('adiacency', 'multiadiacency'))) {
			return false;
		}
		
		// Init query chunks
		$selectCatName = null;
		$validCategoryCondition = null;
		$categoriesJoinCondition = null;
		$additionalCategoriesTableCondition = null;
		$additionalCategoriesSorting = null;
		$validCategory2CategoryCondition = null;
		
		$asCategoryTableIdField = $manifestConfiguration->category_table_id_field;
		$asCategoryTableNameField = str_replace('{langtag}', $this->langTag, $manifestConfiguration->category_table_name_field);
		
		// Category table
		$categoriesTable = $manifestConfiguration->categories_table;
		// SELECT for catname
		$selectCatName = $this->dbInstance->quoteName($categoriesTable) . "." . $this->dbInstance->quoteName($asCategoryTableNameField) . " AS " . $this->dbInstance->quoteName('catname');
		
		// Parent field
		$asCategoryParentIdField =  $manifestConfiguration->parent_field;
		// Child field #Optional
		$asCategoryChildIdField = $manifestConfiguration->child_field;
		
		// Categories 2 categories table
		$category2categoryTable = $manifestConfiguration->category2category_table;
		
		// Valid category condition
		if($categoryConditions = $this->fetchAutoInjectDefaultWhereFields($categoriesTable, $manifestConfiguration, $source)) {
			$validCategoryCondition = "\n WHERE " . implode("\n AND ", $categoryConditions);
		}
		
		// Detect type of adiacency set model for database tables
		switch($manifestConfiguration->recursion_type) {
			case 'multiadiacency':
				// Additional categories table required for some weird reason from 3PD developers
				// Field cat id su record entities that MUST match, used by multi adiacency instead of jsitemap_category and must be needed from route string, is not unset
				if(isset($manifestConfiguration->additional_categories_table) && isset($manifestConfiguration->additional_categories_table_on_catid_field)) {
					if(strpos($manifestConfiguration->additional_categories_table, '$$$')) {
						$additionalCategoriesTable = str_replace('$$$', '_' . $this->siteLanguageRFC, $manifestConfiguration->additional_categories_table);
						// Check if $additionalCategoriesTable exists otherwise fallback and replace again with default site language hoping that table exists
						$checkTableQuery = "SELECT 1 FROM " . $this->dbInstance->quoteName($additionalCategoriesTable);
						$tableExists = $this->dbInstance->setQuery($checkTableQuery)->loadResult();
						if(!$tableExists) {
							$additionalCategoriesTable = str_replace('$$$', '_' . $this->fallbackDefaultLanguageRFC, $manifestConfiguration->additional_categories_table);
						}
					} else {
						$additionalCategoriesTable = $manifestConfiguration->additional_categories_table;
					}
					
					$additionalCategoriesTableOnCatidField = $manifestConfiguration->additional_categories_table_on_catid_field;
					$selectCatName = $this->dbInstance->quoteName($additionalCategoriesTable) . "." . $this->dbInstance->quoteName($asCategoryTableNameField) . " AS " . $this->dbInstance->quoteName('catname');
					
					if(isset($manifestConfiguration->additional_categories_table_on_fkcatid_field)) {
						$additionalCategoriesTableOnFKCatidField = $manifestConfiguration->additional_categories_table_on_fkcatid_field;
						$categoriesJoinCondition = "\n INNER JOIN " . $this->dbInstance->quoteName($additionalCategoriesTable) .
												   "\n ON " . $this->dbInstance->quoteName($categoriesTable)  . "." .  $this->dbInstance->quoteName($additionalCategoriesTableOnCatidField) . " = " .
												   $this->dbInstance->quoteName($additionalCategoriesTable)  . "." .  $this->dbInstance->quoteName($additionalCategoriesTableOnFKCatidField);
					} else {
						$categoriesJoinCondition = "\n INNER JOIN " . $this->dbInstance->quoteName($additionalCategoriesTable) .
												   "\n ON " . $this->dbInstance->quoteName($categoriesTable)  . "." .  $this->dbInstance->quoteName($additionalCategoriesTableOnCatidField) . " = " .
												   $this->dbInstance->quoteName($additionalCategoriesTable)  . "." .  $this->dbInstance->quoteName($additionalCategoriesTableOnCatidField);
					}
				}
				
				if(isset($manifestConfiguration->additional_categories_table_condition)) {
					$languageID = JMapMultilang::loadLanguageID($this->langTag);
					$additionalCategoriesTableCondition = str_replace('$$$', $languageID, "\n WHERE " . $manifestConfiguration->additional_categories_table_condition);
				}

				if(isset($manifestConfiguration->additional_and_categories_table_condition)) {
					$additionalCategoriesTableCondition = str_replace('$$$', $this->dbInstance->quote($this->langTag), "\n AND " . $manifestConfiguration->additional_and_categories_table_condition);
				}
				
				if(isset($manifestConfiguration->additional_categories_sorting)) {
					$additionalCategoriesSorting = "\n ORDER BY " . $manifestConfiguration->additional_categories_sorting;
				}

				// Valid category 2 category condition
				if(isset($manifestConfiguration->category2category_condition)) {
					if(!empty($source->chunks->where1_maintable) && !empty($source->chunks->where1_value_maintable) && strpos($manifestConfiguration->category2category_condition, '$$$')) {
						if(strpos($source->chunks->where1_value_maintable, ',')) {
							$validCategory2CategoryCondition = str_replace('@', ' IN(', $manifestConfiguration->category2category_condition);
							$validCategory2CategoryCondition = str_replace('$$$', $source->chunks->where1_value_maintable . ')', $validCategory2CategoryCondition);
						} else {
							$validCategory2CategoryCondition = str_replace('@', '=', $manifestConfiguration->category2category_condition);
							$validCategory2CategoryCondition = str_replace('$$$', $source->chunks->where1_value_maintable, $validCategory2CategoryCondition);
						}
					} elseif(strpos($manifestConfiguration->category2category_condition, '$$$')) {
						$validCategory2CategoryCondition = null;
					} else {
						$validCategory2CategoryCondition = $manifestConfiguration->category2category_condition;
					}
				}
				$where = $validCategory2CategoryCondition ? "\n WHERE " . $validCategory2CategoryCondition : null;
				
				$query = "SELECT " .
						$this->dbInstance->quoteName($asCategoryParentIdField) . " AS " . $this->dbInstance->quoteName('parent') . "," .
						$this->dbInstance->quoteName($asCategoryChildIdField) . " AS " . $this->dbInstance->quoteName('child') .
						"\n FROM " . $this->dbInstance->quoteName($category2categoryTable) .
						$where;
				$totalItemsCatsTree = $this->dbInstance->setQuery($query)->loadAssocList('child');
			break;
				
			case 'adiacency';
			default;
				$query = "SELECT " .
						$this->dbInstance->quoteName($asCategoryParentIdField) . " AS " . $this->dbInstance->quoteName('parent') . "," .
						$this->dbInstance->quoteName($asCategoryChildIdField) . " AS " . $this->dbInstance->quoteName('child') .
						"\n FROM " . $this->dbInstance->quoteName($category2categoryTable);
				$totalItemsCatsTree = $this->dbInstance->setQuery($query)->loadAssocList('child');
			break;
		}
		
		// First pass organize items by cats
		$itemsByCats = array();
		if(count($source->data)) {
			foreach ($source->data as $item) {
				$itemsByCats[$item->jsitemap_category_id][] = $item;
			}
		}
		// ASSIGNMENT TO SOURCE
		$source->itemsByCat = $itemsByCats;
		
		// Grab total items cats IDs/Names and inject auto fields
		$query = "SELECT DISTINCT " . 
				$this->dbInstance->quoteName($categoriesTable)  . "." .  $this->dbInstance->quoteName($asCategoryTableIdField) . " AS " . $this->dbInstance->quoteName('id') . "," .
				$selectCatName .
				"\n FROM " . $this->dbInstance->quoteName($categoriesTable) .
				$categoriesJoinCondition .
				$validCategoryCondition .
				$additionalCategoriesTableCondition .
				$additionalCategoriesSorting;
		$totalItemsCats = $this->dbInstance->setQuery($query)->loadAssocList();
		
		// Second pass organize categories by parent - children
		$childrenCats = array();
		if(count($totalItemsCats)) {
			foreach ($totalItemsCats as $childCat) {
				$parentCat = $totalItemsCatsTree[$childCat['id']]['parent'];
				$childrenCats[$parentCat][] = $childCat;
			}
		}
		// ASSIGNMENT TO SOURCE
		$source->catChildrenByCat = $childrenCats;
		
		return array($itemsByCats, $childrenCats);
	}
	  
	/**
	 * Get the Data
	 * @access public
	 * 
	 * @return array
	 */
	public function getSitemapData() : array {
		// Get the view
		$this->sources = $this->getSources ();
		$data = array ();
		$user = $this->app->getIdentity();
		// Getting degli access levels associati all'utente in base ai gruppi di appartenenza
		$this->accessLevel = $user->getAuthorisedViewLevels();
		// Get data for a view
		foreach ( $this->sources as $source ) {
			$sourceData = $this->getSourceData ( $source, $this->accessLevel );
			// Data retrieved for this data source, assign safely
			if($sourceData && !empty($sourceData->data)) {
				$data[] = $sourceData;
			}
		}
		$this->data = $data;
		
		return $data;
	}
	
	/**
	 * Get excluded links from the sitemap
	 * @access public
	 * @param string $liveSite to replace
	 * @return array
	 */
	public function getExcludedLinks($liveSite) {
		// Exclude filtering if the client is the backend metainfo that manages all
		if($this->state->get('metainfojsclient', false)) {
			return array();
		}
		
		$query = "SELECT REPLACE(" . $this->dbInstance->quoteName('linkurl') . ", '" . $liveSite . "', '') AS " . $this->dbInstance->quoteName('linkurl') . " ," .
				 "\n " . $this->dbInstance->quoteName('excluded') .
				 "\n FROM #__jmap_metainfo" .
				 "\n WHERE " . $this->dbInstance->quoteName('excluded') . " = 1" ;
		$this->dbInstance->setQuery ( $query );
		
		$excludedLinks = $this->dbInstance->loadAssocList ('linkurl', 'excluded');
		
		return $excludedLinks;
	}
	
	/**
	 * Get the component params width view override/merge
	 * @access public
	 * @return Object Registry
	 */
	public function getComponentParams(): Registry {
		if(is_object($this->cparams)) {
			return $this->cparams;
		}

		$this->cparams = $this->app->getParams('com_jmap');
		return $this->cparams;
	}
	
	/**
	 * Export XML file for sitemap
	 *
	 * @access public
	 * @param string $contents
	 * @param string $fileNameSuffix
	 * @param string $fileNameFormat
	 * @param string $fileNameLanguage
	 * @param string $fileNameDatasetFilter
	 * @param string $fileNameItemidFilter
	 * @param string $mimeType
	 * @param boolean $isFile
	 * @return boolean
	 */
	public function exportXMLSitemap($contents, $fileNameSuffix, $fileNameFormat, $fileNameLanguage, $fileNameDatasetFilter, $fileNameItemidFilter, $mimeType, $isFile = false) {
		$this->sendAsBinary($contents, 'sitemap_' . $fileNameSuffix . $fileNameLanguage . $fileNameDatasetFilter . $fileNameItemidFilter . '.' . $fileNameFormat, $mimeType, $isFile);
	
		return false;
	}
	 
	/**
	 * Class Constructor
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );
		$this->cparams = $this->app->getParams('com_jmap');
		// Check if a module request is detected, in this case merge module params instead of menu view params: Component -> module specific level params override
		if(isset($config['jmap_module']) && $config['jmap_module']) {
			$query = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
			$query->select('params');
			$query->from('#__modules');
			$query->where($this->dbInstance->quoteName('id') . ' = ' . (int)$config['jmap_module']);
			$strParams = $this->dbInstance->setQuery($query)->loadResult();
			$moduleParams = new Registry($strParams);
			
			// Merge module params in place of view params simulating
			$this->cparams->merge($moduleParams);
			// Ensure all links will open up the parent main window
			$this->cparams->set('opentarget', '_parent');
			$this->setState ( 'jmap_module', $config['jmap_module'] );
		}
		
		$this->setState('cparams', $this->cparams);
		$this->documentFormat = $config['document_format'];
		$this->setState('documentformat', $this->documentFormat);
		
		// Languages installed on this system
		$langManager = $this->app->getLanguage();
		
		$this->fallbackDefaultLanguage = $langManager->getDefault();
		$this->fallbackDefaultLanguageRFC = str_replace('-', '_', strtolower($this->fallbackDefaultLanguage));
		
		$this->langTag = $langManager->getTag();
		$this->siteLanguageRFC = str_replace('-', '_', strtolower($this->langTag));
		
		// Init supported 3PD extensions tables for Google news sitemap
		$this->supportedGNewsTablesOptions = array('#__k2_items',
												   '#__zoo_item',
												   '#__easyblog_post',
												   '#__mt_links',
												   '#__jevents_vevdetail',
												   '#__eb_events',
												   '#__joomrecipe'
		);

		// Calculate limitstart and limitrows if precaching process detected
		if(isset($config['iteration_counter'])) {
			$formatLimitParam = in_array($this->documentFormat, array('images', 'videos')) ? $this->cparams->get('precaching_limit_images', 50) : $this->cparams->get('precaching_limit_xml', 5000);
			$this->limitStart = $config['iteration_counter'] * $formatLimitParam;
			$this->limitRows = $formatLimitParam;
		}
	}
}