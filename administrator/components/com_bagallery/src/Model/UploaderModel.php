<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Administrator\Model;

use Balbooa\Component\Gallery\Administrator\Helper\UploaderHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

defined('_JEXEC') or die;

class UploaderModel extends BaseDatabaseModel
{
    public function getUploader($dir = '')
    {
        $uploader = new UploaderHelper($dir);

        return $uploader;
    }
}
