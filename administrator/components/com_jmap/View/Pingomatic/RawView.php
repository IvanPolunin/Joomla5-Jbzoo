<?php 
namespace JExtstore\Component\JMap\Administrator\View\Pingomatic;
/**
 * @author Joomla! Extensions Store
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * Config view
 *
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @since 1.0
 */
class RawView extends JMapView {
	// Template view variables
	protected $pingomaticStats;
	
	/**
	 * Render object/embed element for stats data 
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		// Fetch data from Pingomatic remote server from model
		$this->pingomaticStats = $this->getModel()->getPingomaticStats($this->get('httpclient'));
		
		if(!$this->pingomaticStats) {
			$this->pingomaticStats = Text::_('COM_JMAP_IMPOSSIBLE_FETCH_PINGOMATIC_STATS');
		}
		
		// Display stats object
		echo $this->pingomaticStats;
	}
}