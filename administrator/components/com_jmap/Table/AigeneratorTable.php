<?php
namespace JExtstore\Component\JMap\Administrator\Table;
/**
 *
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;

/**
 * ORM Table for AIGenerator urls
 *
 * @package JMAP::AIGENERATOR::administrator::components::com_jmap
 * @subpackage tables
 * @since 2.0
 */
class AigeneratorTable extends Table {
	use Exceptions;
	
	/**
	 * @var int
	 */
	public $id = 0;
	
	/**
	 * @var string
	 */
	public $keywords_phrase = '';
	
	/**
	 * @var string
	 */
	public $contents = null;
	
	/**
	 * @var string
	 */
	public $api = 'bing';
	
	/**
	 * @var int
	 */
	public $maxresults = 10;
	
	/**
	 * @var string
	 */
	public $language = '';
	
	/**
	 * @var string
	 */
	public $removeimgs = 0;
	
	/**
	 * @var string
	 */
	public $temperature = '0.5';
	
	/**
	 * @var int
	 */
	public $checked_out = null;
	
	/**
	 * @var datetime
	 */
	public $checked_out_time = null;

	/**
	 * Check Table override
	 * @override
	 * 
	 * @see Table::check()
	 */
	public function check() {
		// Title required
		if (! $this->keywords_phrase) {
			$this->setException ( Text::_('COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Load Table override
	 * @override
	 *
	 * @see Table::load()
	 */
	public function load($idEntity = null, $reset = true) {
		// If not $idEntity set return empty object
		if($idEntity) {
			if(!parent::load ( $idEntity )) {
				return false;
			}
		}
		
		// Unserialize contents if any
		if($this->contents) {
			$deserializedContents = [];
			$contentsArray = explode('{contentdivider}', $this->contents);
			if(count($contentsArray)) {
				foreach ($contentsArray as $singleContent) {
					preg_match('/{title}(.*){\/title}/im', $singleContent, $matchesTitle);
					preg_match('/{content}(.*){\/content}/im', $singleContent, $matchesContent);
					if(isset($matchesTitle[1]) && isset($matchesContent[1])) {
						$deserializedContents[] = ['title'=>$matchesTitle[1], 'content'=>$matchesContent[1]];
					}
				}
			}
			$this->contents = $deserializedContents;
		}
		
		return true;
	}
	
	/**
	 * Class constructor
	 * @param DatabaseDriver $db DatabaseDriver object.
	 * @param DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * return Object&
	 */
	public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null) {
		parent::__construct ( '#__jmap_aigenerator', 'id', $db, $dispatcher );
		
		// Set the default max results number
		$this->maxresults = ComponentHelper::getParams('com_jmap')->get('aigenerator_default_results', 10);
		
		// Support null values for datetime field
		$this->_supportNullValue = true;
	}
}