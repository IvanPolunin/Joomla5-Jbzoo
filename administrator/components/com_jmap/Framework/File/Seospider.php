<?php
namespace JExtstore\Component\JMap\Administrator\Framework\File;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage file
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Error as BaseError;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

// CSV import fields
define ('COM_JMAP_SEOSPIDER_LINK', 0);
define ('COM_JMAP_SEOSPIDER_H1', 1);
define ('COM_JMAP_SEOSPIDER_H2', 2);
define ('COM_JMAP_SEOSPIDER_H3', 3);
define ('COM_JMAP_SEOSPIDER_CANONICAL', 4);
define ('COM_JMAP_SEOSPIDER_FIELD_NUM', 5);

/**
 * Importer class for seospider records
 * 
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage file
 * @since 3.5
 */
class Seospider extends BaseError {
	/**
	 * Database connector
	 * 
	 * @access private
	 * @var Object
	 */
	private $dbo;
	
	/**
	 * Application object
	 *
	 * @access private
	 * @var Object
	 */
	private $app;
	
	/**
	 * Wrapper for the db quote function
	 *
	 * @access private
	 * @return mixed
	 */
	private function dbQuote($val, $skip = false) {
		if(!$skip) {
			return $this->dbo->quote($val);
		}
		
		return $val;
	}
	
	/**
	 * Store uploaded file to cache folder,
	 * fully manage error messages and ask for database insert
	 * 
	 * @access public
	 * @return boolean 
	 */
	public function import() {
		// Get file info
		$file = $this->app->getInput()->files->get('datasourceimport', null, 'raw');
		$tmpFile = $file['tmp_name'];
		$tmpFileName = $file['name'];
		try {
			if(!$tmpFile || !$tmpFileName) {
				throw new JMapException(Text::_('COM_JMAP_NOFILE_SELECTED'), 'error');
			}
			
			$tmpFileExtension = @array_pop(explode('.', $tmpFileName));
			if($tmpFileExtension != 'csv') {
				throw new JMapException(Text::_('COM_JMAP_SEOSPIDER_EXT_ERROR'), 'error');
			}

			// Deserialize contents
			$fileHandle = fopen($tmpFile, "r");
			if(!is_resource($fileHandle)) {
				throw new JMapException(Text::_('COM_JMAP_DATA_FILE_NOT_READABLE'), 'error');
			}
			
			// Parse the CSV files dataset into an importable array
			$skip = true;
			$dbQueryArray = array();
			while ( $csvRecord = fgetcsv ( $fileHandle, 0, ';', '"' ) ) {
				// Skip prima riga intestazioni
				if($skip) {
					$skip = false;
					continue;
				}
				//Insert
				array_push ( $dbQueryArray, $csvRecord );
			}
			
			// Check if some valid data to import are available
			if(!count($dbQueryArray)) {
				throw new JMapException(Text::_('COM_JMAP_SEOSPIDER_NO_IMPORT_DATA_FOUND'), 'warning');
			}
			
			$allowedTags = null;
			if(ComponentHelper::getParams('com_jmap')->get('seospider_override_headings_html', 0)) {
				$allowedTags = '<p><div><span><a><section><article><img><video><ul><li><br>';
			}
			
			// Prepare the values array
			foreach ($dbQueryArray as $dbRecord) {
				// Ensure at least that the number of csv fields are correct
				if(count($dbRecord) != COM_JMAP_SEOSPIDER_FIELD_NUM) {
					continue;
				}
				
				// Check if the link as primary key already exists in this table
				$selectQuery = "SELECT" . $this->dbo->quoteName('id') .
							   "\n FROM " . $this->dbo->quoteName('#__jmap_headings') .
							   "\n WHERE" .
							   $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]);
				$linkExists = $this->dbo->setQuery ( $selectQuery )->loadResult();
	
				// NULLify fields
				$emptyRow = false;
				$skipQuoteH1 = false;
				$skipQuoteH2 = false;
				$skipQuoteH3 = false;
				if(!$dbRecord[COM_JMAP_SEOSPIDER_H1]) {
					$dbRecord[COM_JMAP_SEOSPIDER_H1] = 'NULL';
					$skipQuoteH1 = true;
				}
				if(!$dbRecord[COM_JMAP_SEOSPIDER_H2]) {
					$dbRecord[COM_JMAP_SEOSPIDER_H2] = 'NULL';
					$skipQuoteH2 = true;
				}
				if(!$dbRecord[COM_JMAP_SEOSPIDER_H3]) {
					$dbRecord[COM_JMAP_SEOSPIDER_H3] = 'NULL';
					$skipQuoteH3 = true;
				}
				
				if($dbRecord[COM_JMAP_SEOSPIDER_H1] == 'NULL' && $dbRecord[COM_JMAP_SEOSPIDER_H2] == 'NULL' && $dbRecord[COM_JMAP_SEOSPIDER_H3] == 'NULL') {
					$emptyRow = true;
				}
				
				// If the link exists just update it, otherwise insert a new one
				if($linkExists) {
					$query = "UPDATE" .
							 "\n " . $this->dbo->quoteName('#__jmap_headings') .
							 "\n SET " .
							 "\n " . $this->dbo->quoteName('h1') . " = " . $this->dbQuote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_H1], $allowedTags), $skipQuoteH1) . "," .
							 "\n " . $this->dbo->quoteName('h2') . " = " . $this->dbQuote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_H2], $allowedTags), $skipQuoteH2) . "," .
							 "\n " . $this->dbo->quoteName('h3') . " = " . $this->dbQuote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_H3], $allowedTags), $skipQuoteH3) .
							 "\n WHERE " .
							 "\n " . $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]);
					$this->dbo->setQuery ( $query );
					$this->dbo->execute ();
					
					if($emptyRow) {
						$query = "DELETE" .
								 "\n FROM " . $this->dbo->quoteName('#__jmap_headings') .
								 "\n WHERE " .
								 "\n " . $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]);
						$this->dbo->setQuery ( $query );
						$this->dbo->execute ();
					}
				} else {
					if(!$emptyRow) {
						$query = "INSERT INTO" .
								 "\n " . $this->dbo->quoteName('#__jmap_headings') . "(" .
								 $this->dbo->quoteName('linkurl') . "," .
								 $this->dbo->quoteName('h1') . "," .
								 $this->dbo->quoteName('h2') . "," .
								 $this->dbo->quoteName('h3') . ") VALUES (" .
								 $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]) . "," .
								 $this->dbQuote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_H1], $allowedTags), $skipQuoteH1) . "," .
								 $this->dbQuote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_H2], $allowedTags), $skipQuoteH2) . "," .
								 $this->dbQuote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_H3], $allowedTags), $skipQuoteH3) . ")";
						$this->dbo->setQuery ( $query );
						$this->dbo->execute ();
					}
				}
				
				// Check if the canonical link as primary key already exists in this table
				$selectQuery = "SELECT" . $this->dbo->quoteName('id') .
							   "\n FROM " . $this->dbo->quoteName('#__jmap_canonicals') .
							   "\n WHERE" .
							   $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]);
				$canonicalLinkExists = $this->dbo->setQuery ( $selectQuery )->loadResult();
				
				// If the link exists just update it, otherwise insert a new one
				$canonicalLink = trim(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_CANONICAL]));
				$validCanonicalLink = filter_var(filter_var($canonicalLink, FILTER_SANITIZE_URL), FILTER_VALIDATE_URL);
				if($canonicalLinkExists) {
					if($validCanonicalLink) {
						$query = "UPDATE" .
								 "\n " . $this->dbo->quoteName('#__jmap_canonicals') .
								 "\n SET " .
								 "\n " . $this->dbo->quoteName('canonical') . " = " . $this->dbo->quote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_CANONICAL])) .
								 "\n WHERE " .
								 "\n " . $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]);
						$this->dbo->setQuery ( $query );
						$this->dbo->execute ();
					}
					if(!$canonicalLink) {
						$query = "DELETE" .
								 "\n FROM " . $this->dbo->quoteName('#__jmap_canonicals') .
								 "\n WHERE " .
								 "\n " . $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]);
						$this->dbo->setQuery ( $query );
						$this->dbo->execute ();
					}
				} else {
					if($canonicalLink && $validCanonicalLink) {
						$query = "INSERT INTO" .
								 "\n " . $this->dbo->quoteName('#__jmap_canonicals') . "(" .
								 $this->dbo->quoteName('linkurl') . "," .
								 $this->dbo->quoteName('canonical') . ") VALUES (" .
								 $this->dbo->quote($dbRecord[COM_JMAP_SEOSPIDER_LINK]) . "," .
								 $this->dbo->quote(strip_tags($dbRecord[COM_JMAP_SEOSPIDER_CANONICAL])) . ")";
						$this->dbo->setQuery ( $query );
						$this->dbo->execute ();
					}
				}
			}
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (\Exception $e) {
			$jmapException = new JMapException(Text::sprintf('COM_JMAP_SEOSPIDER_ERROR_STORING_DATA', $e->getMessage()), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return true;
	}

	/**
	 * Class constructor
	 * 
	 * @access public
	 * @param Object $dbo
	 * @param Object $app
	 * @return Object &
	 */
	public function __construct($dbo, $app) {
		// DB connector
		$this->dbo = $dbo;
		
		// Application
		$this->app = $app;
	}
}