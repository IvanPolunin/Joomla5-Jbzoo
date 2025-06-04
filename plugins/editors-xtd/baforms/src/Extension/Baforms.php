<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.article
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Balbooa\Plugin\EditorsXtd\Forms\Extension;

use Balbooa\Component\Forms\Administrator\Helper\BaformsHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

final class Baforms extends CMSPlugin
{
    public function __construct($subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onDisplay($name)
    {
        $js = BaformsHelper::readFile(JPATH_ROOT . '/plugins/editors-xtd/baforms/assets/js/script.js');
        $js = str_replace('$name', $name, $js);
        $doc = Factory::getDocument();
        $doc->addScriptDeclaration($js);
        $icon = BaformsHelper::readfile(JPATH_ROOT . '/plugins/editors-xtd/baforms/assets/images/icon.svg');
        
        $button = new CMSObject();
        $button->modal = true;
        $button->link = 'index.php?option=com_baforms&amp;view=forms&amp;layout=modal&amp;tmpl=component';
        $button->class = 'btn';
        $button->text = 'Forms';
        $button->name = 'star';
        $button->icon = 'star';
        $button->iconSVG = $icon;
        $button->options = "{handler: 'iframe', size: {x: 740, y: 545}}";

        return $button;
    }
}
