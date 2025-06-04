<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Plugin\EditorsXtd\Gallery\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;

defined('_JEXEC') or die;


class Bagallery extends CMSPlugin
{
    public function __construct($subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onDisplay($name)
    {
        $js = GalleryHelper::readFile(JPATH_ROOT . '/plugins/editors-xtd/bagallery/assets/js/script.js');
        $icon = GalleryHelper::readfile(JPATH_ROOT . '/plugins/editors-xtd/bagallery/assets/images/icon.svg');
        $js = str_replace('$name', $name, $js);
        $doc = Factory::getDocument();
        $doc->addScriptDeclaration($js);
        $link = 'index.php?option=com_bagallery&amp;view=galleries&amp;layout=modal&amp;tmpl=component';
        $button = new CMSObject();
        $button->modal = true;
        $button->class = 'btn';
        $button->link = $link;
        $button->text = 'Gallery';
        $button->name = 'picture';
        $button->options = "{handler: 'iframe', size: {x: 740, y: 545}}";
        $button->icon = 'picture';
        $button->iconSVG = $icon;

        return $button;
    }
}
