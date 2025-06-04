<?php
namespace JExtstore\Component\JMap\Administrator\Framework;
/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage view
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * Base view for all display core
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage view
 * @since 2.0
 */
class View extends HtmlView {
	/**
	 * User object for ACL authorise check
	 *
	 * @access protected
	 * @var Object
	 */
	protected $user;
	
	/**
	 * Document object, needed by views to inject
	 * CSS/JS tags into document output
	 *
	 * @access public
	 * @var Object
	 */
	public $document;
	
	/**
	 * Reference to option executed
	 *
	 * @access public
	 * @var string
	 */
	public $option;
	
	/**
	 * Reference to application
	 *
	 * @access public
	 * @var Object
	 */
	public $app;
	
	/**
	 * Find the field flagged to be used as category title from that chosen in the select field
	 * in one of the valid jointable for a single user defined data source
	 *
	 * @access protected
	 * @param Object $source        	
	 * @return mixed The field string to use as title for categorization or false if no value found
	 */
	protected function findAsCategoryTitleField($source) {
		// ****JOIN TABLES PROCESSING****
		for($jt = 1, $maxJoin = 3; $jt <= $maxJoin; $jt ++) {
			// Main base condition: 4 fields all compiled otherwise continue
			if (empty ( $source->chunks->{'table_joinfrom_jointable' . $jt} ) || empty ( $source->chunks->{'table_joinwith_jointable' . $jt} ) || empty ( $source->chunks->{'field_joinfrom_jointable' . $jt} ) || empty ( $source->chunks->{'field_joinwith_jointable' . $jt} )) {
				continue;
			}
			if (! empty ( $source->chunks->{'field_select_jointable' . $jt} )) {
				$objectProperty = $source->chunks->{'field_select_jointable' . $jt};
				$objectProperty = ! empty ( $source->chunks->{'field_as_jointable' . $jt} ) ? $source->chunks->{'field_as_jointable' . $jt} : $objectProperty;
				if (! empty ( $source->chunks->{'use_category_title_jointable' . $jt} ) && ! ! $source->chunks->{'use_category_title_jointable' . $jt}) {
					return $objectProperty;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Inject language constant into JS Domain maintaining same name mapping
	 *
	 * @access protected
	 * @param $translations array     	
	 * @param $document Object&        	
	 * @return void
	 */
	protected function injectJsTranslations($translations, $document): void {
		$jsInject = null;
		// Do translations
		foreach ( $translations as $translation ) {
			$jsTranslation = strtoupper ( $translation );
			$translated = Text::_ ( $jsTranslation, true );
			$jsInject .= <<<JS
							var $translation = '{$translated}'; 
JS;
		}
		$document->getWebAssetManager()->addInlineScript ( $jsInject );
	}
	
	/**
	 * Manage injecting jQuery framework into document with class inheritance support
	 *
	 * @access protected
	 * @param Object& $doc
	 * @param boolean $fullStack         	
	 * @return void
	 */
	protected function loadJQuery($document, $fullStack = true): void {
		$wa = $document->getWebAssetManager();
		if($fullStack) {
			$wa->useScript('jquery');
			$wa->useScript('jquery-noconflict');
			array_map ( function ($script) use ($wa) {
				$wa->useScript ( 'bootstrap.' . $script );
			}, [ 
					'collapse',
					'modal',
					'popover',
					'tab'
			] );
		} else {
			$wa->useScript('jquery');
			$wa->useScript('jquery-noconflict');
		}
		
		$wa->useScript('core');
		
		// jQuery foundation framework and class support
		$wa->registerAndUseScript('jmap.classnative', 'administrator/components/com_jmap/js/classnative.js', [], [], ['jquery']);
		$wa->registerAndUseScript('jmap.jstorage', 'administrator/components/com_jmap/js/jstorage.min.js', [], [], ['jquery']);
	}
	
	/**
	 * Manage injecting Bootstrap framework into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadBootstrap($document): void {
		// Main styles for JSitemap admin interface
		$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.boostrap-interface', 'administrator/components/com_jmap/css/bootstrap-interface.css');
		
		if(version_compare(JVERSION, '5', '>=')) {
			$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.dark-theme', 'administrator/components/com_jmap/css/dark-theme.css');
		}
		
		// Main JS file for JSitemap admin interface
		$document->getWebAssetManager()->registerAndUseScript ( 'jmap.boostrap-interface', 'administrator/components/com_jmap/js/bootstrap-interface.js', [], [], ['jquery']);
	}
	
	/**
	 * Manage injecting valildation plugin into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadValidation($document): void {
		$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.simplevalidation', 'administrator/components/com_jmap/css/simplevalidation.css');
		
		$document->getWebAssetManager()->registerAndUseScript ( 'jmap.simplevalidation', 'administrator/components/com_jmap/js/jquery.simplevalidation.js', [], [], ['jquery']);
	}
	
	/**
	 * Manage injecting jQuery UI framework into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadJQueryUI($document): void {
		$document->getWebAssetManager()->registerAndUseStyle ( 'jmap.jqueryui', 'administrator/components/com_jmap/css/jqueryui/jquery-ui.custom.min.css');
		
		$document->getWebAssetManager()->registerAndUseScript ( 'jmap.jqueryui', 'administrator/components/com_jmap/js/jquery-ui.min.js', [], [], ['jquery']);
	}
	
	/**
	 * Class constructor
	 *
	 * @param array $config
	 *        	return Object
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		
		$this->app = Factory::getApplication ();
		$this->user = $this->app->getIdentity ();
		$this->document = $this->app->getDocument ();
		$this->option = $this->app->getInput()->get ( 'option' );
	}
}