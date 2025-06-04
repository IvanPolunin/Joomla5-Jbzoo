<?php
namespace JExtstore\Component\JMap\Administrator\Model;
/**
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Form\Form;
use Joomla\Event\Event;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;
use JExtstore\Component\JMap\Administrator\Framework\Http;
use JExtstore\Component\JMap\Administrator\Framework\Exception as JMapException;

/**
 * Config model responsibilities
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IModelConfig {
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData();
	
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity();
	
	/**
	 * Manage the images and videos crawler test
	 *
	 * @access public
	 * @return boolean
	 */
	public function getCheckCrawler();
}

/**
 * Config model concrete implementation
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class ConfigModel extends FormModel implements IModelConfig {
	use Exceptions;
	
	/**
	 * Variables in request array
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestArray;
	
	/**
	 * App reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $appInstance;
	
	/**
	 * Database reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $dbInstance;
	
	/**
	 * Clean the cache
	 * 
	 * @param string $group
	 *        	The cache group
	 * @param integer $client_id
	 *        	The ID of the client
	 * @return void
	 * @since 11.1
	 */
	private function cleanComponentCache($group = null, $client_id = 0) {
		// Initialise variables;
		$conf = $this->appInstance->getConfig ();
		
		$options = array (
			'defaultgroup' => ($group) ? $group : $this->appInstance->getInput()->get('option'),
			'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get ( 'cache_path', JPATH_CACHE ) 
		);
		
		$cache = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)->createCacheController( 'callback', $options );
		$cache->clean ();
		
		// Trigger the onContentCleanCache event. 
		$this->appInstance->getDispatcher()->dispatch('onContentCleanCache', new Event('onContentCleanCache', $options));
	}
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	private function &getConfigData() {
		$instance = ComponentHelper::getParams ( 'com_jmap' );
		return $instance;
	}
	
	/**
	 * Effettua lo storing dell'asset delle permissions sul component level
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function storePermissionsAsset($data) {
		// Save the rules.
		if (isset ( $data ['params'] ) && isset ( $data ['params'] ['rules'] )) {
			$form = $this->getForm ( $data );
			// Validate the posted data.
			$postedRules = $this->validate ( $form, $data ['params'] );
			
			$rules = new Rules ( $postedRules ['rules'] );
			$asset = new Asset($this->dbInstance);
			
			if (! $asset->loadByName ( $data ['option'] )) {
				$root = new Asset($this->dbInstance);
				$root->loadByName ( 'root.1' );
				$asset->name = $data ['option'];
				$asset->title = $data ['option'];
				$asset->setLocation ( $root->id, 'last-child' );
			}
			$asset->rules = ( string ) $rules;
			
			if (! $asset->check () || ! $asset->store ()) {
				$this->setException ( $asset->getError () );
				return false;
			}
		}
		
		return true;
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
	public function getForm($data = array(), $loadData = true) {
		Form::addFormPath ( JPATH_ADMINISTRATOR . '/components/com_jmap' );
		
		// Get the form.
		$form = $this->loadForm ( 'com_jmap.component', 'config', array (
				'control' => 'params',
				'load_data' => $loadData 
		), false, '/config' );
		
		if (empty ( $form )) {
			return false;
		}
		
		return $form;
	}
	
	/**
	 * Ottiene i dati di configurazione del componente
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData() {
		return $this->getConfigData ();
	}
	
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity() {
		$table = new Extension($this->dbInstance);
		
		// Replace SEF images links
		$base = Uri::root(true) . '/';
		$protocols = '[a-zA-Z0-9]+:';
		$regex = '#(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		
		try {
			// Found as installed extension
			if (! $extensionID = $table->find ( array (
					'element' => 'com_jmap' 
			) )) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			if (! $table->load ( $extensionID )) {
				throw new JMapException ( Text::_('COM_JMAP_ERROR_RECORD_NOT_FOUND'), 'error' );
			}
			
			// Translate posted jform array to params for ORM table binding
			$post = $this->appInstance->getInput()->post;
			
			$table->bind ( $post->getArray ( $this->requestArray ) );
			
			// Unserialize and replace HTML param as RAW no filter
			$unserializedParams = json_decode($table->params);
			$unserializedParams->custom_404_page_text = $this->requestArray['params']['custom_404_page_text'];
			$unserializedParams->custom_404_page_text = preg_replace($regex, "$1=\"$base\$2\"", $unserializedParams->custom_404_page_text);
			
			// Check to avoid buffer underrun for images sitemap precaching limit and general limit
			if($unserializedParams->max_images_requests < $unserializedParams->precaching_limit_images) {
				$unserializedParams->precaching_limit_images = $unserializedParams->max_images_requests;
			}
			
			$table->params = json_encode($unserializedParams);
			
			// pre-save checks
			if (! $table->check ()) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			// save the changes
			if (! $table->store ()) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			// save the changes
			if (! $this->storePermissionsAsset ( $post->getArray ( $this->requestArray ) )) {
				throw new JMapException ( Text::_ ( 'COM_JMAP_ERROR_STORING_PERMISSIONS' ), 'error' );
			}
		} catch ( JMapException $e ) {
			$this->setException ( $e );
			return false;
		} catch ( \Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setException ( $jmapException );
			return false;
		}
		
		// Clean the cache.
		$this->cleanComponentCache ( '_system', 0 );
		$this->cleanComponentCache ( '_system', 1 );
		return true;
	}
	
	public function getCheckCrawler() {
		$cParams = ComponentHelper::getParams('com_jmap');
		$uriInstance = Uri::getInstance();
			
		$customDomain = trim($cParams->get('custom_sitemap_domain', ''));
		$getDomain = $customDomain ? rtrim($customDomain, '/') : rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');
			
		$customHttpPort = trim($cParams->get('custom_http_port', ''));
		$getPort = $customHttpPort ? ':' . $customHttpPort : '';
			
		$liveSiteCrawler = rtrim($getDomain . $getPort, '/');

		// Check if we have a subdomain to append
		$rootUri = trim(Uri::root(), '/');
		$subDomain = trim(str_ireplace($getDomain, '', $rootUri), '/');
		if($subDomain) {
			$liveSiteCrawler .= '/' . $subDomain;
		}
		// Always append a final trailing slash to avoid redirects
		$liveSiteCrawler .= '/';
		try {
			$httpClient = new Http();
			$result = $httpClient->get($liveSiteCrawler, array('Accept'=>'text/html', 'User-Agent'=>'JSitemapbot/1.0'));

			// Manage 301 redirects, follow Location header
			if(in_array($result->code, array(301, 303)) && array_key_exists('Location', $result->headers)) {
				$result = $httpClient->get($result->headers['Location'], array('Accept'=>'text/html', 'User-Agent'=>'JSitemapbot/1.0'));
			}
		} catch (JMapException $e) {
			$this->appInstance->enqueueMessage($e->getMessage(), $e->getExceptionLevel());
			$result = null;
		} catch (\Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->appInstance->enqueueMessage($jmapException->getMessage(), $jmapException->getExceptionLevel());
			$result = null;
		}
		return $result;
	}
	
	/**
	 * Class contructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null) {
		parent::__construct ( $config, $factory );
		
		// App reference
		$this->appInstance = Factory::getApplication ();
		$this->requestArray = &$_POST;
		
		// Joomla 4.2+
		if(method_exists($this, 'getDatabase')) {
			$this->dbInstance = $this->getDatabase();
		} else {
			$this->dbInstance = $this->getDbo();
		}
	}
}