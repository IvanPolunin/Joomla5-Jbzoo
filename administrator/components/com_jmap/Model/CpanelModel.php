<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
define ('SERVER_REMOTE_URI', 'http://storejextensions.org/dmdocuments/updates/');
define ('UPDATES_FORMAT', '.json');
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use JExtstore\Component\JMap\Administrator\Framework\File;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Html\Languages as JMapHtmlLanguages;

/**
 * CPanel model responsibility
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IModelCpanel {
	/**
	 * Aggiorna i nuovi menu sources aggiunti in menu se non presenti come
	 * risorse sources in #__map
	 * 
	 * @access public
	 * @return boolean
	 */
	public function syncMenuSources();
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity($buffer = null);
}
  
/**
 * CPanel autorefresh menu responsibility
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IModelUpdater {
	/**
	 * Connect to remte server through socket to check if some updates
	 * and related informations are available
	 *
	 * @access public
	 * @return boolean
	 */
	public function getUpdates(Http $httpClient);
}

/**
 * CPanel model concrete implementation
 *
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class CpanelModel extends JMapModel implements IModelCpanel, IModelUpdater {
	/**
	 * MVCFactory instance
	 * 
	 * @access private
	 * @var Object
	 */ 
	private $mvcFactoryInstance;
	
	/**
	 * Costruzione list entities query
	 *
	 * @access private
	 * @param string $field
	 * @param string $value
	 * @return string
	 */
	private function buildListQuery($field, $value, $condition = ' = ', $table = '#__jmap') {
		//Dyna query
		$query = "SELECT COUNT(*)" . 
				 "\n FROM" .
				 "\n" . $this->dbInstance->quoteName($table) . " AS s" . 
				 "\n WHERE " . $this->dbInstance->quoteName($field) . $condition . $this->dbInstance->quote($value);
		return $query;
	}

	/**
	 * Main get data method
	 *
	 * @access public
	 * @return array
	 */
	public function getData(): array {
		$result = array();
		// Build query
		$query = $this->buildListQuery ('published', 1);
		$this->dbInstance->setQuery ( $query );
		$result['publishedDataSource'] = $this->dbInstance->loadResult ();
		
		$query = $this->buildListQuery ('id', 0, ' > ');
		$this->dbInstance->setQuery ( $query );
		$result['totalDataSource'] = $this->dbInstance->loadResult ();
		
		$query = $this->buildListQuery ('type', 'menu');
		$this->dbInstance->setQuery ( $query );
		$result['menuDataSource'] = $this->dbInstance->loadResult ();
		
		$query = $this->buildListQuery ('type', 'user');
		$this->dbInstance->setQuery ( $query );
		$result['userDataSource'] = $this->dbInstance->loadResult ();
		
		$query = $this->buildListQuery ('published', 1, ' = ', '#__jmap_datasets');
		$this->dbInstance->setQuery ( $query );
		$result['datasets'] = $this->dbInstance->loadResult ();
	
		return $result;
	}
	
	/**
	 * Restituisce le select list usate dalla view per l'interfaccia
	 *
	 * @access public
	 * @param Object& $record
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists = [];
	
		$lists['languages'] = null;
		$lists['menu_datasource_filters'] = null;
		$lists['datasets_filters'] = null;
		$languageOptions = JMapHtmlLanguages::getAvailableLanguageOptions();
		$defaultSiteLang = $languageOptions[0]->value;
	
		// Check if multilanguage dropdown is always active
		$cParams = $instance = ComponentHelper::getParams('com_jmap');
		if($cParams->get('showalways_language_dropdown', false)) {
			$languageFilterPluginEnabled = true;
		} else {
			// Detect Joomla Language Filter plugin enabled
			$query = "SELECT " . $this->dbInstance->quoteName('enabled') .
					 "\n FROM #__extensions" .
					 "\n WHERE " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->quote('languagefilter') .
					 "\n OR " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->quote('jfdatabase');
			$this->dbInstance->setQuery($query);
			$languageFilterPluginEnabled = $this->dbInstance->loadResult();
		}
		if(count($languageOptions) >= 2 && $languageFilterPluginEnabled) {
			$lists['languages']	= HTMLHelper::_('select.genericlist',   $languageOptions, 'language_option', 'class="inputbox form-select"', 'value', 'text', $defaultSiteLang, 'language_option' );
		}
		
		// Check if some valid datasets are available
		$query = "SELECT dset.id AS value, dset.name AS text" .
				 "\n FROM " . $this->dbInstance->quoteName('#__jmap_datasets') . " AS dset" .
				 "\n WHERE dset.published = 1";
		$this->dbInstance->setQuery($query);
		$datasetsFilters = $this->dbInstance->loadObjectList();
		if(count($datasetsFilters)) {
			array_unshift($datasetsFilters, HTMLHelper::_('select.option',  null, '- '. Text::_('COM_JMAP_NODATASET_FILTER' ) .' -' ));
			$lists['datasets_filters']	= HTMLHelper::_('select.genericlist',   $datasetsFilters, 'datasets_filters', 'class="inputbox form-select"', 'value', 'text', null, 'datasets_filters' );
		}

		// Get default list for all menu pointing to com_jmap that have filtered data source active
		$dbQuery = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
		
		$now = gmdate('Y-m-d H:i:s', time());
		$query = "SELECT m.id AS value, m.title AS text, m.params" .
				 "\n FROM " . $this->dbInstance->quoteName('#__menu') . " AS m" .
				 "\n INNER JOIN " . $this->dbInstance->quoteName('#__extensions') . " AS e" .
				 "\n ON m.component_id = e.extension_id" .
				 "\n WHERE " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->quote('com_jmap') .
				 "\n AND m.client_id = 0 AND m.published = 1" .
				 "\n AND ( " . $dbQuery->isNullDatetime('m.publish_up') . " OR m.publish_up <= '$now' )" .
				 "\n AND ( " . $dbQuery->isNullDatetime('m.publish_down') . " OR m.publish_down >= '$now' )";
		$this->dbInstance->setQuery($query);
		$menuDataSourceFilters = $this->dbInstance->loadObjectList();
		if(count($menuDataSourceFilters)) {
			foreach ($menuDataSourceFilters as $key=>&$singleMenu) {
				$menuParams = json_decode($singleMenu->params);
				if(!isset($menuParams->datasource_filter[0])) {
					array_splice($menuDataSourceFilters, $key, 1);
				}
			}
		}
		if(count($menuDataSourceFilters)) {
			// Check if multilanguage is enabled and the remove default prefix is active
			$pluginLangFilter = PluginHelper::getPlugin('system', 'languagefilter');
			$removeDefaultPrefix = @json_decode($pluginLangFilter->params)->remove_default_prefix;
			if($lists['languages'] != null && $removeDefaultPrefix) { } else {
				array_unshift($menuDataSourceFilters, HTMLHelper::_('select.option',  null, '- '. Text::_('COM_JMAP_NOMENU_FILTER' ) .' -' ));
				$lists['menu_datasource_filters']	= HTMLHelper::_('select.genericlist',   $menuDataSourceFilters, 'menu_datasource_filters', 'class="inputbox form-select"', 'value', 'text', null, 'menu_datasource_filters' );
			}
		}
		
		return $lists;
	}
	
	/**
	 * Load entity from ORM table
	 *
	 * @access public
	 * @param int $id
	 * @return Object&
	 */
	public function loadEntity($id) {
		try {
			// Set the robots.txt path based on the subfolder parameter, the robots must always be at the top level
			if($this->getComponentParams()->get('robots_joomla_subfolder', 0)) {
				$topRootFolder = dirname(JPATH_ROOT);
			} else {
				$topRootFolder = JPATH_ROOT;
			}
			
			// Update robots.txt add entry Sitemap if not exists
			$targetRobot = null;
			// Try standard robots.txt
			if(File::exists($topRootFolder . '/robots.txt')) {
				$targetRobot = $topRootFolder . '/robots.txt';
			} elseif (File::exists($topRootFolder . '/robots.txt.dist')) { // Fallback on distribution version
				$targetRobot = $topRootFolder . '/robots.txt.dist';
				$this->setState('robots_version', 'distribution');
			} else {
				throw new JMapException(Text::_('COM_JMAP_ROBOTS_NOTFOUND'), 'error');
			}
				
			// Robots.txt found!
			if($targetRobot !== false) {
				// If file permissions ko
				if(!$robotContents = File::read($targetRobot)) {
					throw new JMapException(Text::_('COM_JMAP_ERROR_READING_ROBOTS'), 'error');
				}
			}
				
		} catch(JMapException $e) {
			$this->setException($e);
			return false;
		}  catch(\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jmapException);
			return false;
		}
		
		return $robotContents;
	}
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity($buffer = null) {
		try {
			// Set the robots.txt path based on the subfolder parameter, the robots must always be at the top level
			if($this->getComponentParams()->get('robots_joomla_subfolder', 0)) {
				$topRootFolder = dirname(JPATH_ROOT);
			} else {
				$topRootFolder = JPATH_ROOT;
			}
			
			// Data posted required, otherwise avoid write anything
			if(!$buffer) {
				throw new JMapException(Text::_('COM_JMAP_ROBOTS_NO_DATA'), 'error');
			}
			
			$targetRobot = null;
			// Try standard robots.txt
			if(File::exists($topRootFolder . '/robots.txt')) {
				$targetRobot = $topRootFolder . '/robots.txt';
			} elseif (File::exists($topRootFolder . '/robots.txt.dist')) { // Fallback on distribution version
				$targetRobot = $topRootFolder . '/robots.txt.dist';
			} else {
				throw new JMapException(Text::_('COM_JMAP_ROBOTS_NOTFOUND'), 'error');
			}
			
			// If file permissions ko on rewrite updated contents
			$originalPermissions = null;
			if(!is_writable($targetRobot)) {
				$originalPermissions = intval(substr(sprintf('%o', fileperms($targetRobot)), -4), 8);
				@chmod($targetRobot, 0755);
			}
			if(@!File::write($targetRobot, $buffer)) {
				throw new JMapException(Text::_('COM_JMAP_ERROR_WRITING_ROBOTS'), 'error');
			}
			// Check if permissions has been changed and recover the original in that case
			if($originalPermissions) {
				@chmod($targetRobot, $originalPermissions);
			}
		} catch(JMapException $e) {
			$this->setException($e);
			return false;
		}  catch(\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jmapException);
			return false;
		}
		return true;
	}
	
	/**
	 * Aggiorna i nuovi menu sources aggiunti in menu se non presenti come
	 * risorse sources in #__map e elimina quelli in stato stale
	 * 
	 * @access public
	 * @return boolean
	 */
	public function syncMenuSources() {
		// 1) Seleziona i menu items in #__menu_types
		$query = "SELECT *" .
				 "\n FROM #__menu_types" .
				 "\n WHERE client_id = 0";
		$this->dbInstance->setQuery($query);
		$currentMenus = $this->dbInstance->loadObjectList('title');
		$numCurrentMenus = count($currentMenus); 
		 
		// 2) Seleziona tutte le sources di type=menu in #__jmap
		$query = "SELECT id, name" .
 				 "\n FROM #__jmap" .
		 		 "\n WHERE " .  $this->dbInstance->quoteName('type') . ' = ' . $this->dbInstance->quote('menu');
		$this->dbInstance->setQuery($query);
		$currentMenuSources = $this->dbInstance->loadObjectList('name');
		$numCurrentMenuSources = count($currentMenuSources);

		try {
			// 3) Per differenze determina le sources mancanti o non più presenti
		 	if($numCurrentMenus > $numCurrentMenuSources) { // Sources da inserire
		 		// Se non esiste un array key con il name presente in #__menu_types
		 		$chunksQuery = array();
		 		foreach ($currentMenus as $key=>$menu) {
		 			if(!array_key_exists($menu->title, $currentMenuSources)) {
		 				$chunksQuery[] = "(" .
		 						$this->dbInstance->quote('menu') . ","  .
		 						$this->dbInstance->quote($menu->title) . ","  .
		 						$this->dbInstance->quote($menu->description) . ", 1, 1)";
		 	
		 			}
		 		}
		 		$sql = "INSERT INTO #__jmap (" .
		 				$this->dbInstance->quoteName('type') . ", " .
		 				$this->dbInstance->quoteName('name') . ", " .
		 				$this->dbInstance->quoteName('description') . ", " .
		 				$this->dbInstance->quoteName('published') . ", " .
		 				$this->dbInstance->quoteName('ordering') .
		 				") VALUES " . implode(",\n", $chunksQuery) .
		 				"\n ON DUPLICATE KEY UPDATE " .$this->dbInstance->quoteName('type') . " = " . $this->dbInstance->quote('menu');
		 	
		 		$this->dbInstance->setQuery($sql);
		 		if(!$this->dbInstance->execute()) {
		 			throw new JMapException(Text::_('COM_JMAP_ERRORSYNC_INSERT'), 'notice');
		 		}
					
				// Reorder post insert
		 		$table = $this->mvcFactoryInstance->createTable('Sources', 'Administrator');
				if (! $table->reorder ()) {
					throw new JMapException(Text::_('COM_JMAP_ERRORSYNC_REORDER'), 'notice');
				}
			} elseif($numCurrentMenus < $numCurrentMenuSources) { // Sources stale
				$menuNames = array();
				foreach ($currentMenus as $currentMenuName=>$menuObject) {
					$menuNames[] = $this->dbInstance->quote($currentMenuName);
				}
				$implodedValidMenuSources = implode(",", $menuNames);
				$sql = "DELETE FROM #__jmap" .
					   "\n WHERE " .  $this->dbInstance->quoteName('type') . ' = ' . $this->dbInstance->quote('menu') .
					   "\n AND " .  $this->dbInstance->quoteName('name') . " NOT IN (" . $implodedValidMenuSources . ")";
				$this->dbInstance->setQuery($sql);
				if(!$this->dbInstance->execute()) {
					throw new JMapException(Text::_('COM_JMAP_ERRORSYNC_DELETE'), 'notice');
				}
			} else { // Synced resources, controllo solo se il title/name unique p.key è cambiato
				$currentMenuKeys = array_map('strtolower', array_keys($currentMenus));
				$currentMenuSourcesKeys = array_map('strtolower', array_keys($currentMenuSources));
				asort($currentMenuKeys, SORT_STRING);
				asort($currentMenuSourcesKeys, SORT_STRING);
				$currentMenuKeys = array_values($currentMenuKeys);
				$currentMenuSourcesKeys = array_values($currentMenuSourcesKeys);
				
				// P.key variata, si necessita un update per il sync mantain 
				if($currentMenuKeys !== $currentMenuSourcesKeys) {
					$menuNames = array();
					foreach ($currentMenus as $currentMenuName=>$menuObject) {
						$menuNames[] = $this->dbInstance->quote($currentMenuName);
					}
					$implodedValidMenuSources = implode(",", $menuNames);
					$sql = "DELETE FROM #__jmap" .
							"\n WHERE " .  $this->dbInstance->quoteName('type') . ' = ' . $this->dbInstance->quote('menu') .
							"\n AND " .  $this->dbInstance->quoteName('name') . " NOT IN (" . $implodedValidMenuSources . ")";
					$this->dbInstance->setQuery($sql);
					if(!$this->dbInstance->execute()) {
						throw new JMapException(Text::_('COM_JMAP_ERRORSYNC_DELETE'), 'notice');
					}
					
					// Se non esiste un array key con il name presente in #__menu_types
					$chunksQuery = array();
					foreach ($currentMenus as $key=>$menu) {
						if(!array_key_exists($menu->title, $currentMenuSources)) {
							$chunksQuery[] = "(" .
									$this->dbInstance->quote('menu') . ","  .
									$this->dbInstance->quote($menu->title) . ","  .
									$this->dbInstance->quote($menu->description) . ", 1, 1)";
					
						}
					}
					$sql = "INSERT INTO #__jmap (" .
							$this->dbInstance->quoteName('type') . ", " .
							$this->dbInstance->quoteName('name') . ", " .
							$this->dbInstance->quoteName('description') . ", " .
							$this->dbInstance->quoteName('published') . ", " .
							$this->dbInstance->quoteName('ordering') .
							") VALUES " . implode(",\n", $chunksQuery) .
							"\n ON DUPLICATE KEY UPDATE " .$this->dbInstance->quoteName('type') . " = " . $this->dbInstance->quote('menu');;
					
					$this->dbInstance->setQuery($sql);
					if(!$this->dbInstance->execute()) {
						throw new JMapException(Text::_('COM_JMAP_ERRORSYNC_INSERT'), 'notice');
					}
						
					// Reorder post insert
					$table = $this->mvcFactoryInstance->createTable('Sources', 'Administrator');
					if (! $table->reorder ()) {
						throw new JMapException(Text::_('COM_JMAP_ERRORSYNC_REORDER'), 'notice');
					}
				}
			}
		} catch(JMapException $e) {
			$this->setException($e);
			return false;
		}  catch(\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jmapException);
			return false;
		}
		 
		return true;
	} 
	
	/**
	 * Get by remote server informations for new updates of this extension
	 *
	 * @access public
	 * @return mixed An object json decoded from server if update information retrieved correctly otherwise false
	 */
	public function getUpdates(Http $httpClient) {
		// Check if updates checker is disabled
		if($this->getComponentParams()->get('disable_version_checker', 0)) {
			return false;
		}
		
		// Updates server remote URI
		$option = $this->getState('option');
		if(!$option) {
			return false;
		}
		$url = SERVER_REMOTE_URI . $option . UPDATES_FORMAT;
	
		// Try to get informations
		try {
			$response = $httpClient->get($url)->body;
			if($response) {
				$decodedUpdateInfos = json_decode($response);
			}
			return $decodedUpdateInfos;
		} catch(JMapException $e) {
			return false;
		}  catch(\Exception $e) {
			return false;
		}
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param $config array
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );
		$componentParams = ComponentHelper::getParams($this->option);
		$this->setState('cparams', $componentParams);
		
		$this->mvcFactoryInstance = $factory;
	}
}