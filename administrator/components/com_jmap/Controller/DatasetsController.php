<?php
namespace JExtstore\Component\JMap\Administrator\Controller;
/**
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use JExtstore\Component\JMap\Administrator\Framework\Controller as JMapController;

/**
 * Controller for Datasets links entity tasks
 * @package JMAP::DATASETS::administrator::components::com_jmap
 * @subpackage controllers
 * @since 2.0
 */
class DatasetsController extends JMapController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return object
	 */
	protected function setModelState($scope = 'default', $ordering = true): object {
		$option = $this->option;
	
		$defaultModel = parent::setModelState($scope, false);
	
		// Get request state
		$filter_order = $this->getUserStateFromRequest( "$option.$scope.filter_order", 'filter_order', 's.name', 'cmd' );
		$filter_order_Dir = $this->getUserStateFromRequest ( "$option.$scope.filter_order_Dir", 'filter_order_Dir', 'asc', 'word' );
		
		// Set model ordering state
		$defaultModel->setState('order', $filter_order);
		$defaultModel->setState ( 'order_dir', $filter_order_Dir );
	
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 *
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Set model state
		$defaultModel = $this->setModelState('datasets');
		
		// Parent construction and view display
		parent::display($cachable);
	}
	
	/**
	 * 
	 * Class Constructor
	 * 
	 * @access public
	 * @param $config
	 * @return Object&
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null, $app = null, $input = null) {
		parent::__construct($config, $factory, $app, $input);
		
		// Register Extra tasks
		$this->registerTask ( 'applyEntity', 'saveEntity' );
		$this->registerTask ( 'saveEntity2New', 'saveEntity' );
		$this->registerTask ( 'unpublish', 'publishEntities' );
		$this->registerTask ( 'publish', 'publishEntities' );
	}
}
?>