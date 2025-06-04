<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Plugin\System\Gallery\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Balbooa\Component\Gallery\Site\Helper\GalleryHelper;
 
class Bagallery extends CMSPlugin
{
    public function __construct($subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onAfterInitialise()
    {
        $app = Factory::getApplication();
        if ($app->isClient('site')) {
            $params = ComponentHelper::getParams('com_bagallery');
            GalleryHelper::prepareParams($params);
            if (isset($_GET['fbclid'])) {
                $url = $_SERVER['REQUEST_URI'];
                $pos = strpos($url, 'fbclid');
                $delimiter = $url[$pos - 1];
                $url = str_replace($delimiter.'fbclid='.$_GET['fbclid'], '', $url);
                header('Location: '.$url);exit;
            }
        }
    }
    
    public function onBeforeCompileHead()
    {
        $app = Factory::getApplication();
        $doc = Factory::getDocument();
        $option = $app->input->get('option', '', 'string');
        $a_id = $app->input->get('a_id', '', 'string');
        if ($app->isClient('site') && empty($a_id) && $doc->getType() == 'html' && $option != 'com_config') {
            GalleryHelper::addStyle();
        }
    }

    public function onAfterRender()
    {
        $app = Factory::getApplication();
        $doc = Factory::getDocument();
        if ($app->isClient('site') && $doc->getType() == 'html') {
            $this->setGalleries();
        } else if ($app->isClient('administrator') && $doc->getType() == 'html' && JVERSION >= '4.0.0') {
            $html = $app->getBody();
            $html = str_replace('<body', '<body data-joomla-version="4"', $html);
            $app->setBody($html);
        }
    }

    public function onBeforeRenderGridbox()
    {
        $this->setGalleries();
    }

    public function setGalleries()
    {
        $app = Factory::getApplication();
        $option = $app->input->get('option', '', 'string');
        $a_id = $app->input->get('a_id', '', 'string');
        if (empty($a_id) && $option != 'com_config' && $option != 'com_search' && $option != 'com_finder') {
            $view = $app->input->get('view', '', 'string');
            if (!($option == 'com_sppagebuilder' && $view == 'form')) {
                $html = $app->getBody();
                $pos = strpos($html, '</head>');
                $head = substr($html, 0, $pos);
                $body = substr($html, $pos);
                if (strpos($head, 'name="og:') !== false) {
                    $head = str_replace('name="og:', 'property="og:', $head);
                    if (strpos($head, 'prefix="og: http://ogp.me/ns#"') === false) {
                        $head = str_replace('<html', '<html prefix="og: http://ogp.me/ns#" ', $head);
                    }
                }
                $html = $head . GalleryHelper::renderGalleryHTML($body);
                $app->setBody($html);
            }
        } else if ($option == 'com_search' || $option == 'com_finder') {
            $regex = '/\[gallery ID=+(.*?)\]/i';
            $html = $app->getBody();
            preg_match_all($regex, $html, $matches, PREG_SET_ORDER);
            if ($matches) {
                $html = @preg_replace($regex, '', $html);
                $app->setBody($html);
            }
        }
    }
    
    
}

function gallery_sc(){}