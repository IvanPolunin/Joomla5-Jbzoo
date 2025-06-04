<?php
namespace JExtstore\Component\JMap\Administrator\Table;
/**
 *
 * @package JMAP::USERS::administrator::components::com_jmap
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use JExtstore\Component\JMap\Administrator\Framework\Exception\Exceptions;

/**
 * Tracking of links redirected by the plugin
 *
 * @package JMAP::USERS::administrator::components::com_jmap
 * @subpackage tables
 * @since 1.6
 */
class PatternsTable extends Table {
	use Exceptions;
	
	/**
	 * @var int Primary key
	 */
	var $id = 0;
	
	/**
	 * @var string
	 */
	var $original_text = null;
	
	/**
	 * @var string
	 */
	var $target_text = null;
	
	/**
	 * @var string
	 */
	var $original_text_regex = null;
	
	/**
	 * @var string
	 */
	var $target_text_regex = null;
	
	/**
	 *
	 * @var int
	 */
	public $checked_out = null;
	
	/**
	 *
	 * @var datetime
	 */
	public $checked_out_time = null;
	
	/**
	 * @var int
	 */
	var $published = 1;
	
	/**
	 * Check Table override
	 * @override
	 *
	 * @see JTable::check()
	 */
	public function check() {
		// Fields required
		if (! $this->original_text || ! $this->target_text) {
			$this->setException ( Text::_ ( 'COM_JMAP_VALIDATION_ERROR' ) );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Method to store a row in the database from the JTable instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false) {
		// Do the auto generation for regex from patterns
		$input = Factory::getApplication()->getInput();
		if ($input->getInt('autogenerate') == 1 && $this->original_text && $this->target_text) {
			$tokens = array ();
			$tokens ['TEXT'] = array (
					'([\w0-9-\+\=\!\?\(\)\[\]\{\}\/\&\%\*\#\.,_ ]+)' => '$1'
			);
			$tokens ['SIMPLETEXT'] = array (
					'([\A-Za-z0-9-\+\.,_ ]+)' => '$1'
			);
			$tokens ['IDENTIFIER'] = array (
					'([\w0-9-_]+)' => '$1'
			);
			$tokens ['NUMBER'] = array (
					'([0-9]+)' => '$1'
			);
			$tokens ['ALPHA'] = array (
					'([A-Za-z]+)' => '$1'
			);
			
			$pattern = preg_quote ( $this->original_text, '#' );
			$targetLinkReplacement = $this->target_text;
			
			$m = array ();
			$pad = 0;
			
			if (preg_match_all ( '/\{(' . implode ( '|', array_keys ( $tokens ) ) . ')[0-9]*\}/im', $this->original_text, $m )) {
				foreach ( $m [0] as $n => $token ) {
					$token_type = $m [1] [$n];
					
					reset ( $tokens [strtoupper ( $token_type )] );
					$match = key ( $tokens [strtoupper ( $token_type )] );
					$replace = current ( $tokens [strtoupper ( $token_type )] );
					
					$repad = array ();
					if (preg_match_all ( '/(?<!\\\\)\$([0-9]+)/', $replace, $repad )) {
						$repad = $pad + sizeof ( array_unique ( $repad [0] ) );
						$replace = preg_replace_callback ('/(?<!\\\\)\$([0-9]+)/', function ($matches) use ($pad) {
							$newIndexVal = $matches[1] + $pad;
							return '${' . $newIndexVal . '}';
						}, $replace);
						$pad = $repad;
					}
					
					$pattern = str_replace ( preg_quote ( $token, '#' ), $match, $pattern );
					$targetLinkReplacement = str_replace ( $token, $replace, $targetLinkReplacement );
				}
			}
			
			// if simple pattern not changed but pattern changed - clear simple
			$this->original_text_regex = $pattern;
			
			// if simple replacement not changed but pattern changed - clear simple
			$this->target_text_regex = $targetLinkReplacement;
		}
		
		return parent::store($updateNulls);
	}
	
	/**
	 * Class constructor
	 * @param DatabaseDriver $db DatabaseDriver object.
	 * @param DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * return Object&
	 */
	public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null) {
		parent::__construct ( '#__jmap_text_replacements', 'id', $db, $dispatcher );
		
		// Support null values for datetime field
		$this->_supportNullValue = true;
	}
}