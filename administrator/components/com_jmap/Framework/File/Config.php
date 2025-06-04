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
use Joomla\String\StringHelper;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Error as BaseError;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Manager of the import/export configuration and migration of settings JSON encoded
 * 
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage file
 * @since 4.5
 */
class Config extends BaseError {
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
	 * Store uploaded file to cache folder,
	 * fully manage error messages and ask for database insert
	 * 
	 * @access public
	 * @return boolean 
	 */
	public function import() {
		// Get file info
		$file = $this->app->getInput()->files->get('configurationimport', null, 'raw');
		$tmpFile = $file['tmp_name'];
		$tmpFileName = $file['name'];
		try {
			if(!$tmpFile || !$tmpFileName) {
				throw new JMapException(Text::_('COM_JMAP_NOFILE_SELECTED'), 'error');
			}
			
			$tmpFileSize = $file['size'];
			$allowedFileSize = 2 * 1024 * 1024; // MB->Bytes
			if($tmpFileSize > $allowedFileSize) {
				throw new JMapException(Text::_('COM_JMAP_SIZE_ERROR') .' Max 2MB.', 'error');
			}
			
			$tmpFileExtension = @array_pop(explode('.', $tmpFileName));
			if($tmpFileExtension != 'json') {
				throw new JMapException(Text::_('COM_JMAP_EXT_ERROR'), 'error');
			}

			// Deserialize contents
			$fileContents = file_get_contents($tmpFile);
			if($fileContents) {
				$objectToRestore = json_decode($fileContents);
			}
			
			if(!is_object($objectToRestore)) {
				throw new JMapException(Text::_('COM_JMAP_INVALID_IMPORT_DATA'), 'error');
			}
			
			$queryImport = 	"UPDATE " . $this->dbo->quoteName('#__extensions') .
						   	"\n SET " . $this->dbo->quoteName('params') . " = " . $this->dbo->quote($fileContents) .
						   	"\n WHERE " . $this->dbo->quoteName('type') . " = " . $this->dbo->quote('component') .
							"\n AND " . $this->dbo->quoteName('element') . " = " . $this->dbo->quote('com_jmap') .
							"\n AND " . $this->dbo->quoteName('client_id') . " = 1";
			$this->dbo->setQuery($queryImport);
			$this->dbo->execute();
		}
		catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return true;
	}

	/**
	 * Download uploaded file message
	 * 
	 * @access public
	 * @return boolean
	 */
	public function export($ids = null) { 
		// Load data from DB 
		try {
			$query = "SELECT" . $this->dbo->quoteName('params') .
					 "\n FROM " . $this->dbo->quoteName('#__extensions') .
					 "\n WHERE " . $this->dbo->quoteName('type') . " = " . $this->dbo->quote('component') .
					 "\n AND " . $this->dbo->quoteName('element') . " = " . $this->dbo->quote('com_jmap') .
					 "\n AND " . $this->dbo->quoteName('client_id') . " = 1";
			$this->dbo->setQuery($query);
			$resultInfo = $this->dbo->loadResult();
			if(!$resultInfo) {
				throw new JMapException(Text::_('COM_JMAP_ERROR_NODATA_TOEXPORT'), 'error');
			}
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		$fsize = StringHelper::strlen($resultInfo);
		$cont_dis = 'attachment';
		$mimeType = 'application/json';
		
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get ( 'zlib.output_compression' )) {
			ini_set ( 'zlib.output_compression', 'Off' );
		}
		header ( "Pragma: public" );
		header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header ( "Expires: 0" );
		header ( "Content-Transfer-Encoding: binary" );
		header ( 'Content-Disposition:' . $cont_dis . ';' . ' filename="jsitemap_configuration.json";' . ' size=' . $fsize . ';' ); //RFC2183
		header ( "Content-Type: " . $mimeType ); // MIME type
		header ( "Content-Length: " . $fsize );
		@set_time_limit ( 0 );
		
		// No encoding - we aren't using compression... (RFC1945)
		//header("Content-Encoding: none");
		//header("Vary: none");
		echo $resultInfo;
		
		exit();
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