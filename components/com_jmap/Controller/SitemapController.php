<?php
namespace JExtstore\Component\JMap\Site\Controller;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Archive\Archive;
use Joomla\Filesystem\Stream as FilesystemStream;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;
use JExtstore\Component\JMap\Administrator\Framework\File;
use JExtstore\Component\JMap\Administrator\Framework\Xml\Splitter as XmlSplitter;
use JExtstore\Component\JMap\Administrator\Framework\Xml\Precacher as XmlPrecacher;

/**
 * Main controller class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class SitemapController extends JMapController {
	/**
	 * Display the Sitemap
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get REQUEST vars all used to makeId for cache handler
		$option = $this->option;
		$format = $this->app->getInput()->get ( 'format', 'html' );
		$language = $this->app->getInput()->get ( 'lang', null );
		
		$Itemid = $this->app->getInput()->getInt ( 'Itemid', null );
		$ItemidFilter = null;
		if($Itemid) {
			$loadedMenuItem = Factory::getApplication()->getMenu()->getItem($Itemid);
			if(	$loadedMenuItem->home != 1 ) {
				$ItemidFilter = $Itemid ? '_menuid' . $Itemid : null;
			}
		}
		
		$xslt = $this->app->getInput()->getInt ( 'xslt', null );
		$dataset = $this->app->getInput()->getInt ( 'dataset', null );
		
		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'default';
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, null, array (
				'document_format' => $format,
				'jmap_module' => $this->app->getInput()->getInt ( 'jmap_module', null )
		) )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'xslt', $xslt );
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		
		// Display the view checking for cache feature
		$componentConfig = $model->getComponentParams ();
		$cachable = $componentConfig->get ( 'enable_view_cache', false );
		$preCaching = $componentConfig->get ( 'enable_precaching', false );
		
		if($format != 'html' && $componentConfig->get ( 'remove_sitemap_serp', false )) {
			$this->app->setHeader('X-Robots-Tag', 'noindex');
		}

		/**
		 * Order of priorities:
		 * precached sitemap if any -->
		 * Joomla cache if any -->
		 * standard realtime generation
		 */
		// First requirement: a file exists for this requested precached sitemap
		$langString = $language ? '_' . $language : null;
		$datasetFilter = $dataset ? '_dataset' . $dataset : null;
		$precachedSitemapFileName = JPATH_COMPONENT . '/precache/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
		if($preCaching && $format != 'html' && file_exists($precachedSitemapFileName)) {
			// Read the file and stream out directly
			$precachedBuffer = File::read($precachedSitemapFileName);
			// Is requested an xslt formatted view, so manage xsl adding
			if($xslt) {
				$xslFormat = $format == 'xml' ? null : $format . '-';
				$xslFile = "<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL . "<?xml-stylesheet type='text/xsl' href='" . Uri::root() . "components/com_jmap/xslt/xml-" . $xslFormat . "sitemap.xsl'?>";
				// Now add replace xsl to sitemap file
				$precachedBuffer = preg_replace(
									"/<\?xml version='1.0' encoding='UTF-8'\?>/i",
									$xslFile,
									$precachedBuffer);
			}
			// Finally stream out sitemap data
			$document->setMimeEncoding('application/xml');
			echo $precachedBuffer;
		} elseif ($cachable) {
			$registeredurlparams = new \stdClass ();
			$registeredurlparams->lang = 'CMD';
			$registeredurlparams->dataset = 'INT';
			$registeredurlparams->Itemid = 'INT';
			$registeredurlparams->xslt = 'INT';
			$this->app->registeredurlparams = $registeredurlparams;
			if ($format != 'html') {
				$document->setMimeEncoding ( 'application/xml' );
			}
			
			$cache = $this->getExtensionCache ();
			$cache->get ( $view, 'display' );
		} else {
			$view->display ( $format );
		}
	}
	
	/**
	 * Export XML sitemap file
	 *
	 * @access public
	 * @return void
	 */
	public function exportXML() {
		// Get REQUEST vars
		$option = $this->option;
		$format = $this->app->getInput()->get ( 'format', 'xml' );
		$jsClient = $this->app->getInput()->get ( 'jsclient', false );
		$metainfoJsClient = $this->app->getInput()->get ( 'metainfojsclient', false );
		$seospiderJsClient = $this->app->getInput()->get ( 'seospiderjsclient', false );
		$cronjobClient = $this->app->getInput()->get ( 'cronjobclient', false );
		
		// Manage language file string naming
		$lang = $this->app->getInput()->get ( 'lang', null );
		$langString = $lang ? '_' . $lang : null;
		
		$Itemid = $this->app->getInput()->getInt ( 'Itemid', null );
		$ItemidFilter = null;
		if($Itemid) {
			$loadedMenuItem = Factory::getApplication()->getMenu()->getItem($Itemid);
			if(	$loadedMenuItem->home != 1 ) {
				$ItemidFilter = $Itemid ? '_menuid' . $Itemid : null;
			}
		}
		$dataset = $this->app->getInput()->getInt ( 'dataset', null );
		$datasetFilter = $dataset ? '_dataset' . $dataset : null;
		
		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'default';
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, null, array (
				'document_format' => $format 
		) )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}

		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'metainfojsclient', $metainfoJsClient );
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		
		$cParams = ComponentHelper::getParams ( 'com_jmap' );
		// Display the view checking for cache feature
		$componentConfig = $model->getComponentParams ();
		$cachable = $cParams->get ( 'enable_view_cache', false );
		$preCaching = $cParams->get ( 'enable_precaching', false );
		
		// Start XML buffer
		ob_start ();
			/**
			 * Order of priorities:
			 * precached sitemap if any -->
			 * Joomla cache if any -->
			 * standard realtime generation
			 */
			// First requirement: a file exists for this requested precached sitemap
			$precachedSitemapFileName = JPATH_COMPONENT . '/precache/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			$precachedSitemapDirectFile = false;
			if($preCaching && file_exists($precachedSitemapFileName)) {
				$precachedSitemapDirectFile = $precachedSitemapFileName;
				// Read the file and stream out directly
				$precachedBuffer = File::read($precachedSitemapFileName);
				echo $precachedBuffer;
			} elseif ($cachable) {
				$registeredurlparams = new \stdClass ();
				$registeredurlparams->lang = 'CMD';
				$registeredurlparams->dataset = 'INT';
				$registeredurlparams->Itemid = 'INT';
				$registeredurlparams->xslt = 'INT';
				$this->app->registeredurlparams = $registeredurlparams;
				$cache = $this->getExtensionCache ();
				$cache->get ( $view, 'display' );
			} else {
				$view->display ( $format, true );
			}
			$xmlSitemap = ob_get_contents ();
		ob_end_clean ();
		
		// Choose if split sitemap, exclude rss and videos (including CDATA parsed from XML) and every kind of js client used for analyzer and metainfo
		if ($cParams->get ( 'split_sitemap', false ) && !$jsClient && !$metainfoJsClient && !$seospiderJsClient && !$cronjobClient && $format != 'rss' && $format != 'videos') {
			// Split the sitemap
			$splitter = new XmlSplitter ( $format, $langString, $datasetFilter, $ItemidFilter );
			$splitter->chunkXMLString ( $xmlSitemap, 'url', $cParams->get ( 'split_chunks', 5 ), $precachedSitemapDirectFile );
			// Check if chunks was generated
			if ($xmlChunkFiles = $splitter->getChunks ()) {
				// Create ZIP archive and pass contents of written file to export
				$archiveManager = new Archive();
				$archiver = $archiveManager->getAdapter ( 'zip' );
				$pathForArchive = JPATH_COMPONENT_ADMINISTRATOR . '/cache/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.zip';
				$archiver->create ( $pathForArchive, $xmlChunkFiles );
				
				// Export download as attachment
				if (! $model->exportXMLSitemap ( $pathForArchive, $format, 'zip', $langString, $datasetFilter, $ItemidFilter, 'application/zip', true )) {
					$msg = 'COM_JMAP_ERROR_EXPORTING_SITEMAP';
					$this->setRedirect ( "index.php?option=$option&task=sitemap.display", Text::_ ( $msg ) );
				}
			}
		} elseif ($jsClient) {
			$pathForFile = JPATH_COMPONENT_ADMINISTRATOR . '/cache/analyzer/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			// Write the current demanded sitemap in the cache analyzer folder
			$fileWritten = File::write($pathForFile, $xmlSitemap);
			
			// Response JSON object
			$jsAppResponse = new \stdClass ();
			$jsAppResponse->result = $fileWritten;
			$document->setMimeEncoding('application/json');
			echo json_encode($jsAppResponse);
		} elseif ($metainfoJsClient) {
			$pathForFile = JPATH_COMPONENT_ADMINISTRATOR . '/cache/metainfo/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			// Write the current demanded sitemap in the cache analyzer folder
			$fileWritten = File::write($pathForFile, $xmlSitemap);
			
			// Response JSON object
			$jsAppResponse = new \stdClass ();
			$jsAppResponse->result = $fileWritten;
			$document->setMimeEncoding('application/json');
			echo json_encode($jsAppResponse);
		} elseif ($seospiderJsClient) {
			$pathForFile = JPATH_COMPONENT_ADMINISTRATOR . '/cache/seospider/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			// Write the current demanded sitemap in the cache analyzer folder
			$fileWritten = File::write($pathForFile, $xmlSitemap);
			
			// Response JSON object
			$jsAppResponse = new \stdClass ();
			$jsAppResponse->result = $fileWritten;
			$document->setMimeEncoding('application/json');
			echo json_encode($jsAppResponse);
		} elseif ($cronjobClient) {
			$fileName = 'sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			$pathForFile = JPATH_ROOT . '/' . $fileName;
			
			if ($cParams->get ( 'split_sitemap', false ) && $format != 'rss' && $format != 'videos') {
				// Split the sitemap
				$splitter = new \JExtstore\Component\JMap\Administrator\Framework\Xml\Splitter ( $format, $langString, $datasetFilter, $ItemidFilter );
				$splitter->chunkXMLString ( $xmlSitemap, 'url', $cParams->get ( 'split_chunks', 5 ), $precachedSitemapDirectFile );
				// Check if chunks was generated
				if ($xmlChunkFiles = $splitter->getChunks ()) {
					if(!empty($xmlChunkFiles)) {
						foreach ($xmlChunkFiles as $sitemapFile) {
							$chunkFilePath = JPATH_ROOT . '/' . $sitemapFile['name'];
							$chunkFileData = $sitemapFile['data'];
							$fileName = $sitemapFile['name'];
							$pathForFile = $chunkFilePath;
							$fileWritten = File::write($chunkFilePath, $chunkFileData);
						}
					}
				}
			} else {
				// Write the current demanded sitemap to the website root
				$fileWritten = File::write($pathForFile, $xmlSitemap);
			}
			
			// XML Response to the client
			$document->setMimeEncoding('application/xml');
			if($fileWritten) {
				echo Text::sprintf('COM_JMAP_CRONJOB_FILEWRITTEN_SUCCESS', $fileName, 
									JPATH_ROOT,
									Uri::root(false) . $fileName,
									Uri::root(false) . $fileName,
									$pathForFile);
			} else {
				echo Text::sprintf('COM_JMAP_CRONJOB_FILEWRITTEN_ERROR', $fileName, 
									JPATH_ROOT);
			}
		} else {
			if (! $model->exportXMLSitemap ( $xmlSitemap, $format, 'xml', $langString, $datasetFilter, $ItemidFilter, 'application/xml' )) {
				$msg = 'COM_JMAP_ERROR_EXPORTING_SITEMAP';
				$this->setRedirect ( "index.php?option=$option&task=sitemap.display", Text::_ ( $msg ) );
			}
		}
	}
	
	/**
	 * Start a single iteration of a whole precaching process
	 * This controller.task is execute by a JS App Client and the number of
	 * iterations are managed by JS App
	 *
	 * @access public
	 * @return void
	 */
	public function doPreCaching() {
		// Get REQUEST vars
		$option = $this->option;
		$format = $this->app->getInput()->get ( 'format', 'xml' );
		// Manage language file string naming
		$lang = $this->app->getInput()->get ( 'lang', null );
		$langString = $lang ? '_' . $lang : null;
		
		$Itemid = $this->app->getInput()->getInt ( 'Itemid', null );
		$ItemidFilter = null;
		if($Itemid) {
			$loadedMenuItem = Factory::getApplication()->getMenu()->getItem($Itemid);
			if(	$loadedMenuItem->home != 1 ) {
				$ItemidFilter = $Itemid ? '_menuid' . $Itemid : null;
			}
		}
		
		$dataset = $this->app->getInput()->getInt ( 'dataset', null );
		$datasetFilter = $dataset ? '_dataset' . $dataset : null;
		
		// Params from javascript application async
		$dataSourceID = $this->app->getInput()->getInt('datasource_id');
		$iterationCounter = $this->app->getInput()->getInt('iteration_counter');

		// Get sitemap model and view core
		$document = Factory::getApplication()->getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getName ();
		$viewLayout = 'default';

		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );

		// Get/Create the model
		if ($model = $this->getModel ( $coreName, null, array (
				'document_format' => $format,
				'iteration_counter' => $iterationCounter
		) )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}

		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'datasourceid', $dataSourceID );
			
		// Set the layout
		$view->setLayout ( $viewLayout );

		$cParams = ComponentHelper::getParams ( 'com_jmap' );
		// Start XML buffer
		$xmlSitemap = null;
		if($this->app->getInput()->get ( 'process_status' ) != 'end') {
			ob_start ();
				$view->display ( $format, true );
				$xmlSitemap = ob_get_contents ();
			ob_end_clean ();
		}

		// Instance and <<use>> Precacher object
		$fileStreamWriter = new FilesystemStream();
		$preCacher = new XmlPrecacher('sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml', $fileStreamWriter);
		$jsAppResponse = $preCacher->mergeSitemap($xmlSitemap);

		// Format json object for response, get exceptions from model and Precacher class
		$jsAppResponse->affected_rows = $model->getState( 'affected_rows' );

		$document->setMimeEncoding('application/json');
		echo json_encode($jsAppResponse);
	}
	
	/**
	 * Class Constructor
	 * 
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
		$this->registerTask ( 'view', 'display' );
	}
}