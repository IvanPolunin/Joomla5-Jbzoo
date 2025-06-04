<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class ItemsTable extends Table
{
    function __construct(&$db) 
    {
        parent::__construct('#__baforms_items', 'id', $db);
    }
}