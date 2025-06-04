<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Table;

use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

class ItemsTable extends Table
{
    function __construct(&$db) 
    {
        parent::__construct('#__bagallery_items', 'id', $db);
    }
}