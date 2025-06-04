<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use JExtstore\Component\JMap\Administrator\Framework\Model as JMapModel;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Html as JMapHelpersHtml;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;
use JExtstore\Component\JMap\Administrator\Framework\Html\Articles;
use JExtstore\Component\JMap\Administrator\Framework\Html\Categories;
use JExtstore\Component\JMap\Administrator\Framework\Html\Menu;
use JExtstore\Component\JMap\Administrator\Framework\Html\Catspriorities;
use JExtstore\Component\JMap\Administrator\Framework\Html\Languages;

/**
 * Sources model responsibilities
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IModelSources {
	/**
	 * Storing entity by ORM table
	 * 
	 * @access public
	 * @return mixed Object on success or false on failure
	 */
	public function storeEntity($forceRegenerate = false, $wizard = false, $wizardModel = null);
	
	/**
	 * Try to load frontend multilevel cats manifest for this component data source
	 * To have option for multi categorization a data source needs 3 requirements:
	 * 1)Have a valid manifest.json for frontend rendering
	 * 2)Have a sqlquery managed field for cat title use
	 * 3)Have a maintable not related to categories itself
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasManifest($record);
	
	/**
	 * Try to load route helper manifest for this component data source
	 * If a manifest is available to execute the routing helper by JSitemap
	 * show the option accordingly in the data source edit
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasRouteManifest($record);
	
	/**
	 * Try to check if the component table has entity ruled by create date,
	 * and if yes filtering by latest months can be shown and afterwards applied at runtime
	 *
	 * @param Object $record
	 * @return boolean
	 */
	public function gethasCreatedDate($record);
}

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class SourcesModel extends JMapModel implements IModelSources {
	/**
	 * Records query result set
	 *
	 * @access private
	 * @var Object[]
	 */
	private $records;
	
	/**
	 * Load table fields on demand
	 *
	 * @access private
	 * @return array
	 */
	private function loadTables() {
		// Tables select list
		$tableOptions = array();
		$queryTables = "SHOW TABLES";
		$this->dbInstance->setQuery($queryTables);
		try {
			$elements = $this->dbInstance->loadColumn();
		}
		catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$elements = array();
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'notice');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
			$elements = array();
		}
		if(is_array($elements)) {
			$options = array();
			$tablePrefix = $this->dbInstance->getPrefix();
			$tableOptions[] = HTMLHelper::_('select.option', null, Text::_('COM_JMAP_SELECTTABLE'));
			foreach ($elements as $element) {
				// Reset table name by prefix
				$userElement = str_ireplace($tablePrefix, '', $element);
				$element = str_ireplace($tablePrefix, '#__', $element);
				$tableOptions[] = HTMLHelper::_('select.option', $element, $userElement);
			}
		}
		return $tableOptions;
	}
	
	/**
	 * Load table fields on demand
	 * 
	 * @access private
	 * @param Object $record
	 * @param string $tableName
	 * @return array
	 */
	private function loadTableFields($record, $tableName) {
		// Option init
		$fieldsOptions = array();
		$fieldsOptions[] = HTMLHelper::_('select.option', null, Text::_('COM_JMAP_SELECTFIELD'));
		if(isset($record->sqlquery_managed->$tableName) && $record->sqlquery_managed->$tableName) {
			$checkTableName = str_replace('#__', $this->dbInstance->getPrefix(), $record->sqlquery_managed->$tableName);
			$queryExistanceTable = "SHOW TABLES LIKE " . $this->dbInstance->quote($checkTableName);
			
			$queryFields = 	"SHOW COLUMNS " .
							"\n FROM " . $this->dbInstance->quoteName($record->sqlquery_managed->$tableName);
			try {
				// Check for table existance, otherwise throw a specific exception
				$this->dbInstance->setQuery($queryExistanceTable);
				$tableExists = $this->dbInstance->loadResult();
				if(!$tableExists) {
					throw new JMapException(Text::sprintf('COM_JMAP_ERROR_BUILDING_QUERY_NOEXTENSION_MAINTABLE_INSTALLED_DETECTED', $checkTableName), 'error');
				}
				
				// Table exists, extension installed so go on
				$this->dbInstance->setQuery($queryFields);
				$elements = $this->dbInstance->loadColumn();
			}
			catch (JMapException $e) {
				$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
				$elements = array();
			} catch (\Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
				$elements = array();
			}
			if(is_array($elements) && count($elements)) {
				foreach ($elements as $element) {
					$fieldsOptions[] = HTMLHelper::_('select.option', $element, $element);
				}
			}
		}
		return $fieldsOptions;
	}
	
	/**
	 * Convert additional fields to SQL string format
	 * 
	 * @access private
	 * @param array& $selectConditions
	 * @param Object $chunksObject
	 * @param string $tableName
	 * @param string $fieldName
	 * @return array
	 */
	 private function convertAdditionalFields(&$selectConditions, $chunksObject, $tableName, $fieldName) {
	 	// Additional fields conversion
		if(!empty($chunksObject->$fieldName)) {
			$additionalParams = explode(PHP_EOL, $chunksObject->$fieldName);
			foreach ($additionalParams as $param) {
				$subCkunks = explode(" ", preg_replace('!\s+!', ' ', $param));
				// Case with AS
				if (count($subCkunks) > 1) {
					$firstSubChunk = $this->dbInstance->quoteName($chunksObject->$tableName) . "." . $this->dbInstance->quoteName($subCkunks[0]);
					$secondSubChunk = $this->dbInstance->quoteName($subCkunks[2]);
					$asAdditionalParam = $subCkunks[2] && $subCkunks[1] === 'AS' ? ' ' . $subCkunks[1] . ' ' . $this->dbInstance->quoteName($subCkunks[2]) : null;
					$selectConditions[] = $firstSubChunk . $asAdditionalParam;
				} else { // Case without AS so consider only first subchunk
					$firstSubChunk = $this->dbInstance->quoteName($chunksObject->$tableName) . "." . $this->dbInstance->quoteName($subCkunks[0]);
					$selectConditions[] = $firstSubChunk;
				}
			}
		}
		
		return $selectConditions;
	}
	
	/**
	 * Build list entities query
	 *
	 * @access protected
	 * @return string
	 */
	protected function buildListQuery() {
		// WHERE
		$where = array ();
		$whereString = null;
		$orderString = null;
		// STATE FILTER
		if ($filter_state = $this->state->get ( 'state' )) {
			if ($filter_state == 'P') {
				$where [] = 's.published = 1';
			} else if ($filter_state == 'U') {
				$where [] = 's.published = 0';
			}
		}
		
		// TYPE FILTER
		if ($this->state->get ( 'type' )) {
			$where [] = "s.type = " . $this->dbInstance->quote($this->state->get ( 'type' ));
		}
		
		// LANGUAGE FILTER
		$language = $this->state->get ( 'languagejmap' );
		if ($language && $language != '*' && $this->getLanguagePluginEnabled()) {
			$where [] = "s.params LIKE " . $this->dbInstance->quote('%"datasource_language":"' . $this->state->get ( 'languagejmap' ) . '"%');
		}
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "s.name LIKE " . $this->dbInstance->quote("%" . $this->state->get ( 'searchword' ) . "%");
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
		if ($this->state->get ( 'order' )) {
			$orderString = "\n ORDER BY " . $this->state->get ( 'order' ) . " ";
		}
		
		// ORDERDIR
		if ($this->state->get ( 'order_dir' )) {
			$orderString .= $this->state->get ( 'order_dir' );
		}
		
		$query = "SELECT s.*, u.name AS editor" . 
				 "\n FROM #__jmap AS s" .
				 "\n LEFT JOIN #__users AS u" .
				 "\n ON s.checked_out = u.id" .
				 $whereString . $orderString;
		return $query;
	}
	
	/**
	 * Building raw query with REQUEST chunks query managed
	 *
	 * @access protected
	 * @param string $chunksString
	 * @param Object& $tableObject
	 * @param boolean $wizardClient
	 * @param Object& $wizardModel
	 * @return boolean
	 */
	protected function buildRawQuery($chunksString, &$tableObject, $wizardClient = false, &$wizardModel = null) {
		// Decode chunkString in a plain stdClass object and a Registry object
		if($chunksString) {
			$chunksObject = json_decode($chunksString);
			$chunksRegistry = new Registry();
			$chunksRegistry->loadObject($chunksObject);
		}

		// Decode params string in a Registry object for params evaluation
		if(isset($tableObject->params)) {
			$paramsRegistry = new Registry($tableObject->params);
		}

		if(is_object($chunksObject)) {
			// Init array containers
			$mainTable = $chunksObject->table_maintable;
			$selectConditions = array();
			$joinConditions = array();
			$whereConditions = array();
			$orderbyConditions = array();
			$groupbyConditions = array();
			
			// Get required maintable table fields, if not valid throw exception
			$columnsQuery = "SHOW COLUMNS FROM " . $this->dbInstance->quoteName($chunksObject->table_maintable);
			$this->dbInstance->setQuery($columnsQuery);
			if(!$tableFields = $this->dbInstance->loadColumn()) {
				throw new JMapException(sprintf(Text::_('COM_JMAP_ERROR_BUILDING_QUERY_NOEXTENSION_MAINTABLE_INSTALLED_DETECTED'), $chunksObject->table_maintable), 'error');
				return false;
			}
			
			//  ****SELECT FIELDS PROCESSING****
			// Explicit required
			$asTitleField = !empty($chunksObject->titlefield_as) ? " AS " . $this->dbInstance->quoteName($chunksObject->titlefield_as) : null;
			$selectConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->titlefield) . $asTitleField;
			
			// Access field supported
			if(in_array('modified', $tableFields)) {
				$selectConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('modified');
			}

			// Explicit required
			if(!empty($tableFields) && isset($chunksObject->use_alias) && !!$chunksObject->use_alias && in_array('alias', $tableFields)) {
				$asIdField = !empty($chunksObject->idfield_as) ? " AS " . $this->dbInstance->quoteName($chunksObject->idfield_as) : " AS " . $this->dbInstance->quoteName($chunksObject->id);
				$selectConditions[] = "CONCAT_WS(':', " . 
									  $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->id) . ", " .
									  $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('alias'). ")" . $asIdField;
			} else {
				$asIdField = !empty($chunksObject->idfield_as) ? " AS " . $this->dbInstance->quoteName($chunksObject->idfield_as) : null;
				$selectConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->id) . $asIdField;
			}
				
			// Explicit optional
			if(!empty($chunksObject->catid)) {
				// If flagged as use cat alias from 1° JOIN TABLE, should be valid conditions: First JOIN table MUST be the same as maintable and ON same field used as catid
				if(isset($chunksObject->use_catalias) && 
					!!$chunksObject->use_catalias &&
					!empty($chunksObject->table_joinfrom_jointable1) &&
					!empty($chunksObject->field_joinfrom_jointable1) &&
					$chunksObject->table_maintable == $chunksObject->table_joinfrom_jointable1 &&
					$chunksObject->catid == $chunksObject->field_joinfrom_jointable1) {
						// Get fields in jointable1
						$columnsQuery = "SHOW COLUMNS FROM " . $this->dbInstance->quoteName($chunksObject->table_joinwith_jointable1);
						$this->dbInstance->setQuery($columnsQuery);
						if(!$joinTableFields = $this->dbInstance->loadColumn()) {
							throw new JMapException(Text::_('COM_JMAP_ERROR_BUILDING_QUERY_RETRIEVING_JOINTABLE1_FIELDS'), 'error');
							return false;
						}
						// If jointable1 contains alias field
						if(in_array('alias', $joinTableFields)) {
							$asCatidField = !empty($chunksObject->catidfield_as) ? " AS " . $this->dbInstance->quoteName($chunksObject->catidfield_as) : " AS " . $this->dbInstance->quoteName($chunksObject->catid);;
							$selectConditions[] = "CONCAT_WS(':', " . 
									  $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->catid) . ", " .
									  $this->dbInstance->quoteName($chunksObject->table_joinwith_jointable1) . "." . $this->dbInstance->quoteName('alias'). ")" . $asCatidField;
						}
				} else {
					$asCatidField = !empty($chunksObject->catidfield_as) ? " AS " . $this->dbInstance->quoteName($chunksObject->catidfield_as) : null;
					$selectConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->catid) . $asCatidField;
				}
			}
			// Implicit optional
			$this->convertAdditionalFields ( $selectConditions, $chunksObject, 'table_maintable' ,'additionalparams_maintable' );

			// ****JOIN TABLES PROCESSING****
			for($jt=1,$maxJoin=3;$jt<=$maxJoin;$jt++) {
				// If not set continue cycle and avoid exceptions
				if(!isset($chunksObject->{'table_joinwith_jointable'.$jt})) {
					continue;
				}
				// Main base condition: 4 fields all compiled otherwise continue
				if(	empty($chunksObject->{'table_joinfrom_jointable'.$jt}) ||
				   	empty($chunksObject->{'table_joinwith_jointable'.$jt}) ||
				   	empty($chunksObject->{'field_joinfrom_jointable'.$jt}) ||
				   	empty($chunksObject->{'field_joinwith_jointable'.$jt})) {
					continue;
				}
				
				// Main JOIN WITH table name
				$joinTable = $chunksObject->{'table_joinwith_jointable'.$jt};
				//  ****SELECT FIELDS PROCESSING****
				if(!empty($chunksObject->{'field_select_jointable'.$jt})) {
					$fieldAsJointable = !empty($chunksObject->{'field_as_jointable'.$jt}) ? " AS " . $this->dbInstance->quoteName($chunksObject->{'field_as_jointable'.$jt}) : null;
					$selectConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'field_select_jointable'.$jt}) . $fieldAsJointable;
				}
				// Implicit optional
				$this->convertAdditionalFields ( $selectConditions, $chunksObject, 'table_joinwith_jointable'.$jt ,'additionalparams_jointable'.$jt );

				//  ****WHERE FIELDS PROCESSING****
				for($wjt=1,$maxWhere=3;$wjt<=$maxWhere;$wjt++) {
					if(!empty($chunksObject->{'where'.$wjt.'_jointable'.$jt})) {
						// Manage IN clause by searching for multiple IDs
						if(strpos($chunksObject->{'where'.$wjt.'_value_jointable'.$jt}, ',')) {
							$jtoperatorPrefix = null;
							if($chunksObject->{'where'.$wjt.'_operator_jointable'.$jt} == '!=') {
								$jtoperatorPrefix = ' NOT';
							}
							$jtoperator = "$jtoperatorPrefix IN ";
							$whereConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $jtoperator . "(" . $chunksObject->{'where'.$wjt.'_value_jointable'.$jt} . ")";
						} else {
							$jtoperator = !empty($chunksObject->{'where'.$wjt.'_operator_jointable'.$jt}) ? " " . $chunksObject->{'where'.$wjt.'_operator_jointable'.$jt} . " " : " = ";
							if(preg_match('/\{\d+months\}/', $chunksObject->{'where'.$wjt.'_value_jointable'.$jt})) {
								$whereConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $jtoperator . $chunksObject->{'where'.$wjt.'_value_jointable'.$jt};
							} else {
								if($jtoperator === ' LIKE ') {
									$whereConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $jtoperator . $this->dbInstance->quote('%' . $chunksObject->{'where'.$wjt.'_value_jointable'.$jt} . '%');
								} else {
									if(StringHelper::strtoupper($chunksObject->{'where'.$wjt.'_value_jointable'.$jt}) === 'NULL') {
										$isJtCondition = $jtoperator === ' = ' ? ' IS ' : ' IS NOT ';
										$whereConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $isJtCondition . $chunksObject->{'where'.$wjt.'_value_jointable'.$jt};
									} else {
										$whereConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wjt.'_jointable'.$jt}) . $jtoperator . $this->dbInstance->quote($chunksObject->{'where'.$wjt.'_value_jointable'.$jt});
									}
								}
							}
						}
					}
				}
				
				// ****JOIN TABLES INNERPROCESSING****
				$joinType = !empty($chunksObject->{'jointype_jointable'.$jt}) ? $chunksObject->{'jointype_jointable'.$jt} . " JOIN" : "JOIN";
				$joinConditions[] = $joinType . " " . $this->dbInstance->quoteName($joinTable) . " ON " . 
									$this->dbInstance->quoteName($chunksObject->{'table_joinfrom_jointable'.$jt}) . "." . $this->dbInstance->quoteName($chunksObject->{'field_joinfrom_jointable'.$jt}) . " = " .
									$this->dbInstance->quoteName($chunksObject->{'table_joinwith_jointable'.$jt}) . "." . $this->dbInstance->quoteName($chunksObject->{'field_joinwith_jointable'.$jt});
				
				//  ****ORDER BY FIELDS PROCESSING****
				if(!empty($chunksObject->{'orderby_jointable'.$jt})) {
					$orderbyDirectionJointable = !empty($chunksObject->{'orderby_direction_jointable'.$jt}) ? " " . $chunksObject->{'orderby_direction_jointable'.$jt} : null;
					array_unshift($orderbyConditions, $this->dbInstance->quoteName($chunksObject->{'table_joinwith_jointable'.$jt}) . "." . $this->dbInstance->quoteName($chunksObject->{'orderby_jointable'.$jt}) . $orderbyDirectionJointable);
				}
					
				//  ****GROUP BY FIELDS PROCESSING****
				if(!empty($chunksObject->{'groupby_jointable'.$jt})) {
					$groupbyConditions[] = $this->dbInstance->quoteName($joinTable) . "." . $this->dbInstance->quoteName($chunksObject->{'groupby_jointable'.$jt});
				}
			}
			
			//  ****WHERE FIELDS PROCESSING****
			// *AUTO WHERE PART* injected fields
			if(is_array($tableFields) && count($tableFields)) {
				// Published field supported
				if(in_array('published', $tableFields)) {
					$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('published') . " = " . $this->dbInstance->quote(1);
				} elseif(in_array('state', $tableFields)) { // State field supported fallback
					$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('state') . " = " . $this->dbInstance->quote(1);
				}
			
				// Access field supported
				if(in_array('access', $tableFields)) {
					$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('access') . " IN {aid}" ;
				}
				
				// Created field supported, set placeholder to limit items to recent months
				if($paramsRegistry->get('created_date', null) && in_array('created', $tableFields)) {
					$latestMonths = $paramsRegistry->get('created_date');
					// Fix for JEvents, mapping #__jevents_vevdetail -> #__jevents_vevent
					if($mainTable == '#__jevents_vevdetail') {
						$whereConditions[] = $this->dbInstance->quoteName('#__jevents_vevent') . "." . $this->dbInstance->quoteName('created') . " > {" . $latestMonths . "months}" ;
					} else {
						$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('created') . " > {" . $latestMonths . "months}" ;
					}
				}
				
				// Language field supported
				if(in_array('language', $tableFields)) {
					$whereConditions[] =  " (" . $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('language') . " = " . $this->dbInstance->quote('*') . 
										 " OR " . $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName('language')  . " = {langtag})";
				}
			}
			// *EXPLICIT WHERE PART*
			for($wmt=1,$maxWhere=3;$wmt<=$maxWhere;$wmt++) {
				if(!empty($chunksObject->{'where'.$wmt.'_maintable'})) {
					// Manage IN clause by searching for multiple IDs
					if(strpos($chunksObject->{'where'.$wmt.'_value_maintable'}, ',')) {
						$operatorPrefix = null;
						if($chunksObject->{'where'.$wmt.'_operator_maintable'} == '!=') {
							$operatorPrefix = ' NOT';
						}
						$operator = "$operatorPrefix IN ";
						$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $operator . "(" . $chunksObject->{'where'.$wmt.'_value_maintable'} . ")";
					} else {
						$operator = !empty($chunksObject->{'where'.$wmt.'_operator_maintable'}) ? " " . $chunksObject->{'where'.$wmt.'_operator_maintable'} . " " : " = ";
						if(preg_match('/\{\d+months\}/', $chunksObject->{'where'.$wmt.'_value_maintable'})) {
							$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $operator . $chunksObject->{'where'.$wmt.'_value_maintable'};
						} else {
							if($operator === ' LIKE ') {
								$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $operator . $this->dbInstance->quote('%' . $chunksObject->{'where'.$wmt.'_value_maintable'} . '%');
							} else {
								if(StringHelper::strtoupper($chunksObject->{'where'.$wmt.'_value_maintable'}) === 'NULL') {
									$isCondition = $operator === ' = ' ? ' IS ' : ' IS NOT ';
									$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $isCondition . $chunksObject->{'where'.$wmt.'_value_maintable'};
								} else {
									$whereConditions[] = $this->dbInstance->quoteName($mainTable) . "." . $this->dbInstance->quoteName($chunksObject->{'where'.$wmt.'_maintable'}) . $operator . $this->dbInstance->quote($chunksObject->{'where'.$wmt.'_value_maintable'});
								}
							}
						}
					}
				}
			}
			
			//  ****ORDER BY FIELDS PROCESSING****
			if(!empty($chunksObject->{'orderby_maintable'})) {
				$orderbyDirectionMaintable = !empty($chunksObject->{'orderby_direction_maintable'}) ? " " . $chunksObject->{'orderby_direction_maintable'} : null;
				array_push($orderbyConditions, $this->dbInstance->quoteName($chunksObject->{'table_maintable'}) . "." . $this->dbInstance->quoteName($chunksObject->{'orderby_maintable'}) . $orderbyDirectionMaintable);
			}	
			
			//  ****GROUP BY FIELDS PROCESSING****
			if(!empty($chunksObject->{'groupby_maintable'})) {
				$groupbyConditions[] = $this->dbInstance->quoteName($chunksObject->{'table_maintable'}) . "." . $this->dbInstance->quoteName($chunksObject->{'groupby_maintable'});
			}
			
			// ****START BUILD CONCATENATE FINAL QUERY STRING****
			// *SELECT* STATEMENT BUILD
			$finalQueryString = "SELECT \n " . implode(", \n ", $selectConditions);
			
			// *FROM* STATEMENT BUILD		
			$finalQueryString .= "\n FROM " . $this->dbInstance->quoteName($chunksObject->table_maintable);
			
			// *JOIN* STATEMENT BUILD
			if(count($joinConditions)) {
				$finalQueryString .= "\n " . implode("\n ", $joinConditions);
			}
			
			// *WHERE* STATEMENT BUILD
			if(count($whereConditions)) {
				$finalQueryString .=  "\n WHERE \n " . implode("\n AND ", $whereConditions);
			}
			
			// *GROUP BY BY* STATEMENT BUILD
			if(count($groupbyConditions)) {
				$finalQueryString .=  "\n GROUP BY \n " . implode(", \n ", $groupbyConditions);
			}
			
			// *ORDER BY* STATEMENT BUILD
			if(count($orderbyConditions)) {
				$finalQueryString .=  "\n ORDER BY \n " . implode(", \n ", $orderbyConditions);
			}
			
		} else {
			throw new JMapException(Text::_('COM_JMAP_ERROR_MANIFEST_FORMAT'), 'error');
			return false;
		}
	
		// Call wizard model substitutions here if is valid object $wizardModel and not wizard client
		if(!$wizardClient && is_object($wizardModel)) {
			$finalQueryString = $wizardModel->getSubstitutionsOnDemand($finalQueryString);
		}
		
		// All well done so final assignment to table object referenced for later store
		$tableObject->sqlquery = $finalQueryString;
		return true;
	}
	
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData(): array {
		// Build query
		$query = $this->buildListQuery ();
		try {
			$dbQuery = method_exists ( $this->dbInstance, 'createQuery' ) ? $this->dbInstance->createQuery () : $this->dbInstance->getQuery ( true );
			$dbQuery->setQuery ( $query )->setLimit ( $this->getState ( 'limit' ), $this->getState ( 'limitstart' ) );
			$this->dbInstance->setQuery ( $dbQuery );
			$result = $this->dbInstance->loadObjectList ();
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$result = array();
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
			$result = array();
		}
		return $result;
	}
	
	/**
	 * Method to get a form object.
	 *
	 * @param array $data
	 *        	the form.
	 * @param boolean $loadData
	 *        	the form is to load its own data (default case), false if not.
	 *
	 * @return mixed \Joomla\CMS\Form\Form object on success, false on failure
	 * @since 1.6
	 */
	public function getFormFields($record) {
		// Avoid forms error on new records
		if(!$record->id) {
			return false;
		}
		$pluginName = strtolower($record->name);

		// Try to generate the form and bind parameters data between Registry and SimpleXMLElement nodes
		try {
			// Add plugin form path
			Form::addFormPath ( JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $pluginName );
	
			// Get the form object instance
			$form = Factory::getContainer()->get(\Joomla\CMS\Form\FormFactoryInterface::class)->createForm('com_jmap.plugin.' . $pluginName, array (
					'control' => 'params',
					'load_data' => false
			));
			$form->loadFile($pluginName, false, '/datasource');
			
			if (empty ( $form )) {
				return false;
			}

			// Get and bind plugin config parameters
			$registryParams = new Registry($record->params);
			$form->bind($registryParams);

			// Load the language file for the plugin
			// Manage partial language translations
			$jLang = $this->app->getLanguage();
			$jLang->load($pluginName, JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $pluginName, 'en-GB', true, true);
			if($jLang->getTag() != 'en-GB') {
				$jLang->load($pluginName, JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $pluginName, null, true, false);
			}
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$elements = array();
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'notice');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
			$elements = array();
		}

		return $form;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public 
	 * @return array
	 */
	public function getFilters(): array {
		$filters ['state'] = HTMLHelper::_ ( 'grid.state', $this->getState ( 'state' ) );
		
		$datasourceTypes = array();
		$datasourceTypes[] = HTMLHelper::_('select.option', null, Text::_('COM_JMAP_ALL_DATASOURCE'));
		$datasourceTypes[] = HTMLHelper::_('select.option', 'user', Text::_('COM_JMAP_USER_DATASOURCE'));
		$datasourceTypes[] = HTMLHelper::_('select.option', 'menu', Text::_('COM_JMAP_MENU_DATASOURCE'));
		$datasourceTypes[] = HTMLHelper::_('select.option', 'content', Text::_('COM_JMAP_CONTENT_DATASOURCE'));
		$datasourceTypes[] = HTMLHelper::_('select.option', 'plugin', Text::_('COM_JMAP_PLUGIN_DATASOURCE'));
		$datasourceTypes[] = HTMLHelper::_('select.option', 'links', Text::_('COM_JMAP_LINKS_DATASOURCE'));
		$filters ['type'] = HTMLHelper::_ ( 'select.genericlist', $datasourceTypes, 'filter_type', 'onchange="Joomla.submitform();" class="form-select"', 'value', 'text', $this->getState ( 'type' ));
		
		$languageOptions = Languages::getAvailableLanguageOptions(true);
		$filters ['languages'] = HTMLHelper::_ ( 'select.genericlist', $languageOptions, 'languagejmap', 'onchange="Joomla.submitform();" class="form-select"', 'value', 'text', $this->getState ( 'languagejmap' ) );
		
		return $filters;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null): array {
		$lists = [];
		// Grid states
		$lists ['published'] = JMapHelpersHtml::booleanlist('published', null, $record->published );
		
		// Components select list
		$queryComponent = "SELECT DISTINCT " . $this->dbInstance->quoteName('element') . " AS value, SUBSTRING(" . $this->dbInstance->quoteName('element') . ", 5) AS text" .
						  "\n FROM #__extensions" .
						  "\n WHERE (" . $this->dbInstance->quoteName('protected') . " = 0" .
						  "\n OR " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->quote('com_content') . // Exception for custom content sources
						  "\n OR " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->quote('com_tags') . // Exception for custom tags sources
						  "\n OR " . $this->dbInstance->quoteName('element') . " = " . $this->dbInstance->quote('com_docman') . ")" . // Exception for docman 2 protected
		 				  "\n AND ". $this->dbInstance->quoteName('type') . " = " . $this->dbInstance->quote('component');
		$this->dbInstance->setQuery($queryComponent);
		try {
			$elements = $this->dbInstance->loadObjectList();
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$elements = array();
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'notice');
			$this->app->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
			$elements = array();
		}
		
		array_unshift($elements, HTMLHelper::_('select.option', '', Text::_('COM_JMAP_SELECTCOMPONENT')));
		$lists ['components'] = HTMLHelper::_ ( 'select.genericlist', $elements, 'sqlquery_managed[option]', 'data-validation="required" aria-required="true"', 'value', 'text', @$record->sqlquery_managed->option);
		
		
		// Tables select list
		$tableOptions = $this->loadTables();
		// Maintable select list
		$lists ['tablesMaintable'] = HTMLHelper::_ ( 'select.genericlist', $tableOptions, 'sqlquery_managed[table_maintable]', 'class="table_maintable" data-validation="required" aria-required="true" data-bind="table_maintable"', 'value', 'text', @$record->sqlquery_managed->table_maintable);
		
		$fieldsOptions = $this->loadTableFields($record, 'table_maintable');
		$lists ['fieldsTitle'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[titlefield]', 'class="table_maintable" data-bind="field_maintable" data-validation="required" aria-required="true"', 'value', 'text', @$record->sqlquery_managed->titlefield);
		$lists ['fieldsID'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[id]', 'class="table_maintable" data-bind="field_maintable" data-validation="required" aria-required="true"', 'value', 'text', @$record->sqlquery_managed->id);
		$lists ['fieldsCatid'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[catid]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->catid);
		$lists ['where1Maintable'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[where1_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->where1_maintable);
		$lists ['where2Maintable'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[where2_maintable]', 'class="table_maintable" data-bind="field_maintable""', 'value', 'text', @$record->sqlquery_managed->where2_maintable);
		$lists ['where3Maintable'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[where3_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->where3_maintable);
		$lists ['orderByMaintable'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[orderby_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->orderby_maintable);

		// Operators select list
		$operatorOptions = array();
		$operatorOptions[] = HTMLHelper::_('select.option', '', '=');
		$arrayOperators = array ('!=', '>', '<', '>=', '<=', 'LIKE');
		foreach ($arrayOperators as $operator) {
			$operatorOptions[] = HTMLHelper::_('select.option', $operator, $operator);
		}
		for($wmo=1,$maxOperators=3;$wmo<=$maxOperators;$wmo++) {
			$lists ['where'.$wmo.'MaintableOperators'] = HTMLHelper::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where'.$wmo.'_operator_maintable]', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where'.$wmo.'_operator_maintable'});
		}
		
		// Order BY direction selectlist
		$directionOptions = array();
		$directionOptions[] = HTMLHelper::_('select.option', '', Text::_('COM_JMAP_SELECTFIELD'));
		$directionOptions[] = HTMLHelper::_('select.option', 'ASC', 'Ascending');
		$directionOptions[] = HTMLHelper::_('select.option', 'DESC', 'Descending');
		$lists ['orderByDirectionMaintable'] = HTMLHelper::_ ( 'select.genericlist', $directionOptions, 'sqlquery_managed[orderby_direction_maintable]', 'class="right_select intermediate"', 'value', 'text', @$record->sqlquery_managed->orderby_direction_maintable);
		$lists ['groupByMaintable'] = HTMLHelper::_ ( 'select.genericlist', $fieldsOptions, 'sqlquery_managed[groupby_maintable]', 'class="table_maintable" data-bind="field_maintable"', 'value', 'text', @$record->sqlquery_managed->groupby_maintable);
		
		
		// Cycle for JoinTable #n elements
		$joinOptions = array();
		$joinOptions[] = HTMLHelper::_('select.option', '', Text::_('COM_JMAP_DEFAULT_JOIN'));
		$joinOptions[] = HTMLHelper::_('select.option', 'LEFT', Text::_('COM_JMAP_LEFT_JOIN'));
		$joinOptions[] = HTMLHelper::_('select.option', 'RIGHT', Text::_('COM_JMAP_RIGHT_JOIN'));
		for($jt=1,$maxJoin=3;$jt<=$maxJoin;$jt++) {
			// Tables select list
			$fieldsJoinFromTableOptions = $this->loadTableFields($record, 'table_joinfrom_jointable'.$jt);
			$fieldsJoinWithTableOptions = $this->loadTableFields($record, 'table_joinwith_jointable'.$jt);
			
			// JoinFromJointables select list
			$lists ['tablesJoinFromJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $tableOptions, 'sqlquery_managed[table_joinfrom_jointable'.$jt.']', 'class="table_joinfrom" data-bind="table_joinfrom_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'table_joinfrom_jointable'.$jt});
			// JoinWithJointables select list
			$lists ['tablesJoinWithJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $tableOptions, 'sqlquery_managed[table_joinwith_jointable'.$jt.']', 'class="table_joinwith" data-bind="table_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'table_joinwith_jointable'.$jt});
			// Join type Jointables
			$lists ['jointypeJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $joinOptions, 'sqlquery_managed[jointype_jointable'.$jt.']', 'class="intermediate_small"', 'value', 'text', @$record->sqlquery_managed->{'jointype_jointable'.$jt});
			
			$lists ['fieldsJoinFromJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinFromTableOptions, 'sqlquery_managed[field_joinfrom_jointable'.$jt.']', 'class="field_joinfrom" data-bind="field_joinfrom_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'field_joinfrom_jointable'.$jt});
			$lists ['fieldsJoinWithJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[field_joinwith_jointable'.$jt.']', 'data-bind="field_joinwith_jointable'.$jt.'" class="field_joinwith right_select"', 'value', 'text', @$record->sqlquery_managed->{'field_joinwith_jointable'.$jt});
			$lists ['fieldsSelectJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[field_select_jointable'.$jt.']', 'data-bind="field_joinwith_jointable'.$jt.'" class="field_joinwith right_select"', 'value', 'text', @$record->sqlquery_managed->{'field_select_jointable'.$jt});
			
			$lists ['where1Jointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[where1_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'where1_jointable'.$jt});
			$lists ['where2Jointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[where2_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'where2_jointable'.$jt});
			$lists ['where3Jointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[where3_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'where3_jointable'.$jt});
			
			$lists ['where1Jointable'.$jt.'Operators'] = HTMLHelper::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where1_operator_jointable'.$jt.']', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where1_operator_jointable'.$jt});
			$lists ['where2Jointable'.$jt.'Operators'] = HTMLHelper::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where2_operator_jointable'.$jt.']', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where2_operator_jointable'.$jt});
			$lists ['where3Jointable'.$jt.'Operators'] = HTMLHelper::_ ( 'select.genericlist', $operatorOptions, 'sqlquery_managed[where3_operator_jointable'.$jt.']', 'class="where_condition_operator"', 'value', 'text', @$record->sqlquery_managed->{'where3_operator_jointable'.$jt});
				
			$lists ['orderByJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[orderby_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'orderby_jointable'.$jt});
			$lists ['orderByDirectionJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $directionOptions, 'sqlquery_managed[orderby_direction_jointable'.$jt.']', 'class="right_select intermediate"', 'value', 'text', @$record->sqlquery_managed->{'orderby_direction_jointable'.$jt});
			$lists ['groupByJointable'.$jt] = HTMLHelper::_ ( 'select.genericlist', $fieldsJoinWithTableOptions, 'sqlquery_managed[groupby_jointable'.$jt.']', 'class="field_joinwith" data-bind="field_joinwith_jointable'.$jt.'"', 'value', 'text', @$record->sqlquery_managed->{'groupby_jointable'.$jt});
		}
		
		// Priority select list
		$options = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JMAP_SELECTPRIORITY')); 
		$arrayPriorities = array ('0.1'=>'10%', '0.2'=>'20%', '0.3'=>'30%', '0.4'=>'40%', '0.5'=>'50%', '0.6'=>'60%', '0.7'=>'70%', '0.8'=>'80%', '0.9'=>'90%', '1.0'=>'100%');
		foreach ($arrayPriorities as $value=>$text) {
			$options[] = HTMLHelper::_('select.option', $value, $text);
		}
		$lists ['priority'] = HTMLHelper::_ ( 'select.genericlist', $options, 'params[priority]', 'style="width:200px"', 'value', 'text', $record->params->get('priority', '0.5'));
		$lists ['priorities'] = HTMLHelper::_ ( 'select.genericlist', $options, 'params[priorities]', 'class="custom-form-select" style="width:200px" size="15"', 'value', 'text', '', 'priorities');
		
		// Change frequency select list
		$options = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_JMAP_SELECTCHANGEFREQ'));
		$arrayPriority = array ('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never');
		foreach ($arrayPriority as $priority) {
			$options[] = HTMLHelper::_('select.option', $priority, $priority);
		}
		$lists ['changefreq'] = HTMLHelper::_ ( 'select.genericlist', $options, 'params[changefreq]', 'style="width:200px";', 'value', 'text', $record->params->get('changefreq', 'daily'));
		
		// Lazy Loading dependency - Use J Element simbolic override for menu multiselect generation specific to single data source menu
		if($record->type == 'menu') {
			$selections = Menu::getMenuItems($record->name);
			$lists['exclusion']	= HTMLHelper::_('select.genericlist', $selections, 'params[exclusion][]', 'class="form-select" size="15" multiple="multiple"', 'value', 'text', $record->params->get('exclusion', array()), 'exclusion' );
			
			$selectionsWithPriority = Menu::getMenuItems($record->name, true);
			$lists['menu_priorities']	= HTMLHelper::_('select.genericlist', $selectionsWithPriority, 'params[menu_priorities]', array('option.attr'=>'style', 'list.attr'=>'data-type="MenuPriorities" class="form-select" size="15"'));
		}
		
		// Lazy Loading dependency - Use J Element simbolic override for menu multiselect generation specific to single data source menu
		if($record->type == 'content') {
			// Get categories exclusion multiselect
			$categoryOptions = Categories::getCategories();
			$lists['catexclusion']	= HTMLHelper::_('select.genericlist', $categoryOptions, 'params[catexclusion][]', 'class="form-select" size="15" multiple="multiple"', 'value', 'text', $record->params->get('catexclusion', array()), 'catexclusion' );
		
			// Get articles exclusion multiselect
			if($this->getComponentParams()->get('enable_articles_exclusions', 1)) {
				$articleOptions = Articles::getArticles();
				$lists['articleexclusion']	= HTMLHelper::_('select.genericlist', $articleOptions, 'params[articleexclusion][]', 'class="form-select" size="15" multiple="multiple"', 'value', 'text', $record->params->get('articleexclusion', array()), 'articleexclusion' );
			}

			// Get articles workflow stages
			$articleStages = Articles::getWorkflowStages();
			$lists['articlestages']	= HTMLHelper::_('select.genericlist', $articleStages, 'params[articlestages][]', 'class="form-select" size="15" multiple="multiple"', 'value', 'text', $record->params->get('articlestages', array()), 'articlestages' );
			
			// Get articles categories priorities
			$selectionsCatsWithPriority = Catspriorities::getCategories();
			$lists['cats_priorities']	= HTMLHelper::_('select.genericlist', $selectionsCatsWithPriority, 'params[cats_priorities]', array('option.attr'=>'style', 'list.attr'=>'data-type="CatsPriorities" class="form-select" size="15"'));
		}
		
		// Check if data source extension has valid GNews inclusion support
		if($this->getHasGnewsSupport($record)) {
			// Get Google News sitemap genres multiselect
			$genresArray = array (''=>'COM_JMAP_NONE',
								  'Blog'=>'COM_JMAP_BLOG',
								  'PressRelease'=>'COM_JMAP_PRESSRELEASE',
								  'Satire'=>'COM_JMAP_SATIRE',
								  'OpEd'=>'COM_JMAP_OPED',
								  'Opinion'=>'COM_JMAP_OPINION',
								  'UserGenerated'=>'COM_JMAP_USERGENERATED');
			foreach ($genresArray as $genre=>$translation) {
				$genresOptions[] = HTMLHelper::_('select.option', $genre, Text::_($translation));
			}
			$lists['gnews_genres'] = HTMLHelper::_('select.genericlist', $genresOptions, 'params[gnews_genres][]', 'multiple="multiple" size="8" class="form-select"', 'value', 'text', $record->params->get('gnews_genres', $this->getComponentParams()->get('gnews_genres', array('Blog'))), 'params_gnews_genres');
		}
		
		// Lazy Loading dependency - Use J Element simbolic override for menu multiselect generation specific to single data source menu
		if($record->type == 'user') {
			$sefItemid = Menu::getMenuItems();
			$lists['sef_itemid'] = HTMLHelper::_('select.genericlist', $sefItemid, 'params[sef_itemid]', 'size="15"', 'value', 'text', $record->params->get('sef_itemid', ''), 'sef_itemid' );
		}

		// Configure a language dropdown if the links source is requested
		if($record->type == 'links' || $record->type == 'user' || $record->type == 'plugin') {
			$languageFilterPluginEnabled = $this->getLanguagePluginEnabled();
			$languageOptions = Languages::getAvailableLanguageOptions(true);
			if(count($languageOptions) >= 2 && $languageFilterPluginEnabled) {
				$currentSelectedLanguage = $record->params->get('datasource_language', '');
				$lists['languages']	= HTMLHelper::_('select.genericlist',   $languageOptions, 'params[datasource_language]', 'style="width: 200px"', 'value', 'text', $currentSelectedLanguage, 'datasource_language' );
				// Append a flag button image if the language is a specific one
				if($currentSelectedLanguage && $currentSelectedLanguage != '*') {
					$lists['languages'] .= '<img id="language_flag_image" src="' . Uri::root(false) . 'media/mod_languages/images/' . StringHelper::str_ireplace('-', '_', $currentSelectedLanguage) . '.gif" alt="language_flag" />';
				}
			}
		}
		
		return $lists;
	}
	
	/**
	 * Try to load frontend multilevel cats manifest for this component data source
	 * To have option for multi categorization a data source needs 3 requirements:
	 * 1)Have a valid manifest.json for frontend rendering
	 * 2)Have a sqlquery managed field for cat title use
	 * 3)Have a maintable not related to categories itself
	 * 
	 * @access public
	 * @param Object $record
	 * @return boolean 
	 */
	public function getHasManifest($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}
		
		// Check if a valid field has been chosen and activated for category titles
		$hasCategoryUseByTitle = false;
		for($i=1,$k=4;$i<$k;$i++) {
			if(isset($record->sqlquery_managed->{'use_category_title_jointable'.$i}) && $record->sqlquery_managed->{'use_category_title_jointable'.$i}) {
				$hasCategoryUseByTitle = true;
				break;
			}
		}
		
		// Check if data source is elated to categories entities itself
		$hasCategoryUseByCatsItself = false;
		if(preg_match('/categor|cats|catg|igallery/i', $record->sqlquery_managed->table_maintable)) {
			$hasCategoryUseByCatsItself = true;
		}
		
		if(!$hasCategoryUseByTitle && !$hasCategoryUseByCatsItself) {
			return false;
		}

		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_SITE . '/manifests/' . $record->sqlquery_managed->option . '.json';
		
		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if the data source has a items categorization
	 * determined by use as title setting
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasCategoryByTitle($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}
	
		if(preg_match('/categor|cats|catg/i', $record->sqlquery_managed->table_maintable)) {
			return false;
		}

		// Check if a valid field has been chosen and activated for category titles
		for($i=1,$k=4;$i<$k;$i++) {
			if(isset($record->sqlquery_managed->{'use_category_title_jointable'.$i}) && $record->sqlquery_managed->{'use_category_title_jointable'.$i}) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Check if the data source is for type category itself
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getIsCategorySource($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}
	
		if(preg_match('/categor|cats|catg|igallery/i', $record->sqlquery_managed->table_maintable)) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Check if the extension data source has support for valid GNews sitemap XML
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasGnewsSupport($record) {
		static $hasGNewsSupport;

		// Return already evaluated support
		if(!is_null($hasGNewsSupport)) {
			return $hasGNewsSupport;
		}

		// Content source always supported by default
		if($record->type == 'content' || $record->type == 'plugin' || $record->type == 'links') {
			$hasGNewsSupport = true;
			return $hasGNewsSupport;
		}

		// Required a valid 'user' data source, no menu or content already evaluated
		if(!isset($record->sqlquery_managed->table_maintable)) {
			$hasGNewsSupport = false;
			return $hasGNewsSupport;
		}

		// Check if valid GNews for type 'user'
		$supportedExtensionTables = array('#__k2_items', '#__zoo_item', '#__easyblog_post', '#__mt_links', '#__jevents_vevdetail');
		$hasGNewsSupport = in_array($record->sqlquery_managed->table_maintable, $supportedExtensionTables);

		return $hasGNewsSupport;
	}
	
	/**
	 * Check if the extension data source has support for valid RSS feed
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasRSSSupport($record) {
		static $hasRSSSupport;
	
		// Return already evaluated support
		if(!is_null($hasRSSSupport)) {
			return $hasRSSSupport;
		}
	
		// Content source always supported by default
		if($record->type == 'content') {
			$hasRSSSupport = true;
			return $hasRSSSupport;
		}
	
		// Required a valid 'user' data source, no menu or content already evaluated
		if(!isset($record->sqlquery_managed->table_maintable)) {
			$hasRSSSupport = false;
			return $hasRSSSupport;
		}

		// Supporting by default virtuemart products
		if(stripos($record->sqlquery_managed->table_maintable, 'virtuemart_products')) {
			$hasRSSSupport = true;
			return $hasRSSSupport;
		}

		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_SITE . '/manifests/rss.json';

		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			// Load the manifest serialized file and assign to local variable
			$manifest = file_get_contents($fileName);
			$supportedExtensionTables = json_decode($manifest);
		}

		// Check if valid GNews for type 'user'
		$hasRSSSupport = property_exists($supportedExtensionTables, $record->sqlquery_managed->table_maintable);
	
		return $hasRSSSupport;
	}
	
	/**
	 * Check if the extension data source has support for valid HReflang sitemap
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasHreflangSupport($record) {
		static $hasHreflangSupport;
	
		// Return already evaluated support
		if(!is_null($hasHreflangSupport)) {
			return $hasHreflangSupport;
		}
	
		// Content and menu source always supported by default
		if($record->type == 'content' || $record->type == 'menu') {
			$hasHreflangSupport = true;
			return $hasHreflangSupport;
		}
		
		// Required a valid 'option' component data source, no plugin/links data source
		if(!isset($record->sqlquery_managed->option)) {
			$hasHreflangSupport = false;
			return $hasHreflangSupport;
		}
	
		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_SITE . '/manifests/hreflang.json';
	
		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			// Load the manifest serialized file and assign to local variable
			$manifest = file_get_contents($fileName);
			$supportedExtensions = json_decode($manifest);
		}
	
		// Check if valid GNews for type 'user'
		$hasHreflangSupport = property_exists($supportedExtensions, $record->sqlquery_managed->option);
	
		return $hasHreflangSupport;
	}
	
	/**
	 * Try to load route helper manifest for this component data source
	 * If a manifest is available to execute the routing helper by JSitemap
	 * show the option accordingly in the data source edit
	 *
	 * @access public
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasRouteManifest($record) {
		if(!$record->id || $record->type != 'user') {
			return false;
		}

		// Load configuration manifest file
		$fileName = JPATH_COMPONENT_ADMINISTRATOR . '/Framework/Route/manifests/' . $record->sqlquery_managed->option . '.json';

		// Check if file exists and is valid manifest
		if(file_exists($fileName)) {
			return true;
		}

		return false;
	}
	
	/**
	 * Try to check if the component table has entity ruled by create date,
	 * and if yes filtering by latest months can be shown and afterwards applied at runtime
	 *
	 * @param Object $record
	 * @return boolean
	 */
	public function getHasCreatedDate($record) {
		// Always true for content type data source
		if($record->type == 'content') {
			return true;
		}

		// Available only for content and user data source that supports created field not newly created
		if(!$record->id || $record->type == 'menu' || $record->type == 'plugin' || $record->type == 'links') {
			return false;
		}

		if(strpos($record->sqlquery_managed->table_maintable, 'categor')) {
			return false;
		}

		if(!isset($record->sqlquery_managed->table_maintable)) {
			return false;
		}

		// Build query
		// Check for table existance, otherwise throw a specific exception
		$checkTableName = str_replace('#__', $this->dbInstance->getPrefix(), $record->sqlquery_managed->table_maintable);
		$queryExistanceTable = "SHOW TABLES LIKE " . $this->dbInstance->quote($checkTableName);
		
		$queryFields = "SHOW COLUMNS FROM " . $this->dbInstance->quoteName($record->sqlquery_managed->table_maintable);
		try {
			$this->dbInstance->setQuery($queryExistanceTable);
			$tableExists = $this->dbInstance->loadResult();
			if(!$tableExists) {
				throw new JMapException(Text::sprintf('COM_JMAP_ERROR_BUILDING_QUERY_NOEXTENSION_MAINTABLE_INSTALLED_DETECTED', $checkTableName), 'error');
			}
			
			$this->dbInstance->setQuery ( $queryFields );
			$tableFields = $this->dbInstance->loadColumn ();
			// Search in fields array we have found the create date reserved field
			if(in_array('created', $tableFields)) {
				return true;
			}
		} catch ( JMapException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getExceptionLevel () );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jmapException->getMessage (), $jmapException->getExceptionLevel () );
			return false;
		}

		return false;
	}
	
	/**
	 * Get the state of the language filter plugin and Joomla multilanguage
	 *
	 * @access public
	 * @return bool
	 */
	public function getLanguagePluginEnabled() {
		$languageFilterPluginEnabled = false;
		// Check if multilanguage dropdown is always active
		if($this->getComponentParams()->get('showalways_language_dropdown', false)) {
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
		
		return $languageFilterPluginEnabled;
	}
			
	/**
	 * Storing entity by ORM table
	 * 
	 * @access public
	 * @param boolean $forceRegenerate
	 * @param boolean $wizard
	 * @param Object $wizardModel
	 * @return mixed Object on success or false on failure
	 */
	public function storeEntity($forceRegenerate = false, $wizard = false, $wizardModel = null) {
		$table = $this->getTable($this->getName(), 'Administrator');
		try {
			$table->bind ($this->requestArray, array(), true);
	
			if (!$table->check ( )) {
				throw new JMapException($table->getException (), 'error');
			}
	
			// Delegate creazione raw query se type=user e new
			if($table->type == 'user' && (!$table->id || $forceRegenerate)) {
				if(!$this->buildRawQuery($table->sqlquery_managed, $table, $wizard, $wizardModel)) {
					throw new JMapException(Text::_('COM_JMAP_ERROR_BUILDING_QUERY'), 'error');
				}
			}
			
			// Update nulls
			if (! $table->store (true)) {
				throw new JMapException($table->getException (), 'error');
			} 
			$table->reorder(); 
		} catch(JMapException $e) {
			$this->setException($e);
			// Rethrow exception if wizard mode to bubble it to wizard controller
			if($wizard) {
				throw $e;
			}
			return false;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setException($jmapException);
			// Rethrow exception if wizard mode to bubble it to wizard controller
			if($wizard) {
				throw $jmapException;
			}
			return false;
		}
		
		// If integration with ActionLogs
		if($this->getComponentParams()->get('actionlogs_integration', 0)) {
			$isNew = $this->requestArray['id'] ? false : true;
			$action = $isNew ? Text::_('COM_JMAP_ACTIONLOGS_ADDED') : Text::_('COM_JMAP_ACTIONLOGS_UPDATED');
			$this->storeActionLog($action, 'sources', Text::sprintf('COM_JMAP_ACTIONLOGS_DATA_SOURCE', $table->name), 'index.php?option=com_jmap&task=sources.editEntity&cid[]=' . $table->id, $table->id);
		}
		return $table;
	}
	
	/**
	 * Copy existing entity
	 *
	 * @param int $id
	 * @access public
	 * @return bool
	 */
	public function copyEntity($ids): bool { 
		if(is_array($ids) && count($ids)) {
			$table = $this->getTable($this->getName(), 'Administrator');
			try {
				foreach ( $ids as $id ) {
					if ($table->load ( ( int ) $id )) {
						$table->id = 0;
						// Don't rename plugin type data source! The name is the unique identifier
						if($table->type != 'plugin') {
							$table->name = Text::_ ( 'COM_JMAP_COPYOF' ) . $table->name;
						}
						$table->published = 0;
						$table->sqlquery_managed = json_encode($table->sqlquery_managed);
						$table->params = $table->params->toString();
						if (! $table->store ()) {
							throw new JMapException($table->getException (), 'error');
						} 
					} else {
						throw new JMapException( Text::_('COM_JMAP_ERROR_RECORD_NOT_FOUND') , 'error');
					}
				}	
				$table->reorder();
			} catch (JMapException $e) {
				$this->setException($e);
				return false;
			} catch (\Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'error');
				$this->setException($jmapException);
				return false;
			}
		}
		
		// If integration with ActionLogs
		if($this->getComponentParams()->get('actionlogs_integration', 0)) {
			$action = Text::_('COM_JMAP_ACTIONLOGS_COPIED');
			$this->storeActionLog($action, 'sources', Text::sprintf('COM_JMAP_ACTIONLOGS_DATA_SOURCE', $table->name), 'index.php?option=com_jmap&task=sources.editEntity&cid[]=' . $table->id, $table->id);
		}
		return true;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids
	 * @access public
	 * @return bool
	 */
	public function deleteEntity($ids): bool {
		$table = $this->getTable ($this->getName(), 'Administrator');

		// Ciclo su ogni entity da cancellare
		if (is_array ( $ids ) && count ( $ids )) {
			foreach ( $ids as $id ) {
				try {
					// Load always to identify if the type is plugin with filesystem resources
					if (! $table->load ( $id )) {
						throw new JMapException ( Text::_('COM_JMAP_ERROR_RECORD_NOT_FOUND'), 'error' );
					}

					// Evaluate if the data source is of type plugin and delete filesystem resources if no more data sources are used
					if($table->type === 'plugin') {
						$otherResourcesQuery = "SELECT COUNT(*)" .
											   "\n FROM " . $this->dbInstance->quoteName('#__jmap') .
											   "\n WHERE" .
											   "\n " . $this->dbInstance->quoteName('type') . " = " . $this->dbInstance->quote('plugin') .
											   "\n AND " . $this->dbInstance->quoteName('name') . " = " . $this->dbInstance->quote($table->name) .
											   "\n AND " . $this->dbInstance->quoteName('id') . " != " . (int)$table->id;
						$otherResourcesCount = $this->dbInstance->setQuery($otherResourcesQuery)->loadResult();
						if(!$otherResourcesCount) {
							$folderPluginName = strtolower($table->name);
							$folderPluginPath = JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $folderPluginName;
							if(is_dir($folderPluginPath)) {
								$deleted = Folder::delete($folderPluginPath);
							}
						}
					}

					if (! $table->delete ( $id )) {
						throw new JMapException ( $table->getException (), 'error' );
					}
					$table->reorder ();
				} catch ( JMapException $e ) {
					$this->setException ( $e );
					return false;
				} catch ( \Exception $e ) {
					$jmapException = new JMapException ( $e->getMessage (), 'error' );
					$this->setException ( $jmapException );
					return false;
				}
			}
		}

		// If integration with ActionLogs
		if($this->getComponentParams()->get('actionlogs_integration', 0)) {
			$action = Text::_('COM_JMAP_ACTIONLOGS_DELETED');
			$this->storeActionLog($action, 'sources', Text::sprintf('COM_JMAP_ACTIONLOGS_DATA_SOURCE', $table->name), 'javascript:void(0)', $id);
		}

		return true;
	}
	
	/**
	 * Change entities ordering
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param int $direction        	
	 * @return bool
	 */
	public function changeOrder($idEntity, $direction): bool {
		if (isset ( $idEntity )) {
			try {
				$table =  $this->getTable ($this->getName(), 'Administrator');
				if (! $table->load ((int) $idEntity)) {
					throw new JMapException ( Text::_('COM_JMAP_ERROR_RECORD_NOT_FOUND'), 'notice' );
				}
				if (! $table->move ( $direction )) {
					throw new JMapException($table->getException (), 'notice');
				}
			} catch(JMapException $e) {
				$this->setException($e);
				return false;
			} catch (\Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->setException($jmapException);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Method to move and reorder
	 *
	 * @access public
	 * @param array $cid        	
	 * @param array $order  
	 * @return bool
	 */
	public function saveOrder($cid, $order): bool {
		if (is_array ( $cid ) && count ( $cid )) {
			try {
				$table =  $this->getTable ($this->getName(), 'Administrator');
				// update ordering values
				for($i = 0; $i < count ( $cid ); $i ++) {
					$table->load ( ( int ) $cid [$i] );
					if ($table->ordering != $order [$i]) {
						$table->ordering = $order [$i];
						if (! $table->store ()) {
							throw new JMapException($table->getException (), 'notice');
						}
					}
				}
			} catch(JMapException $e) {
				$this->setException($e);
				return false;
			} catch (\Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->setException($jmapException);
				return false;
			}
			// All went well
			$table->reorder ();
		}
		return true;
	}
	
	/**
	 * Publishing state changer for entities
	 *
	 * @access public
	 * @param int $idEntity        	
	 * @param string $state        	
	 * @return bool
	 */
	public function publishEntities($idEntity, $state): bool {
		// Table load
		$table = $this->getTable ($this->getName(), 'Administrator');
		if (isset ( $idEntity ) && $idEntity) {
			try {
				if (! $table->load ( $idEntity )) {
					throw new JMapException ( Text::_('COM_JMAP_ERROR_RECORD_NOT_FOUND'), 'notice' );
				}
				switch ($state) {
					case 'unpublish' :
						$table->published = 0;
						break;
						
					case 'publish' :
						$table->published = 1;
						break;
				}
				
				if (! $table->store ( true )) {
					throw new JMapException($table->getException (), 'notice');
				}
			} catch(JMapException $e) {
				$this->setException($e);
				return false;
			} catch (\Exception $e) {
				$jmapException = new JMapException($e->getMessage(), 'notice');
				$this->setException($jmapException);
				return false;
			}
		}
		
		// If integration with ActionLogs
		if($this->getComponentParams()->get('actionlogs_integration', 0)) {
			$isPublished = $table->published == 1 ? true : false;
			$action = $isPublished ? Text::_('COM_JMAP_ACTIONLOGS_PUBLISHED') : Text::_('COM_JMAP_ACTIONLOGS_UNPUBLISHED');
			$this->storeActionLog($action, 'sources', Text::sprintf('COM_JMAP_ACTIONLOGS_DATA_SOURCE', $table->name), 'index.php?option=com_jmap&task=sources.editEntity&cid[]=' . $table->id, $table->id);
		}
		return true;
	}
}