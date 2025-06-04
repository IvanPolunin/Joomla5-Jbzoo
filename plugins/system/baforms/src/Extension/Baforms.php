<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Plugin\System\Forms\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Balbooa\Component\Forms\Site\Helper\BaformsHelper;
 
class Baforms extends CMSPlugin
{
    public function __construct($subject, $config)
    {
        parent::__construct($subject, $config);
    }
    
    public function onAfterRender()
    {
        $app = Factory::getApplication();
        $doc = Factory::getDocument();
        if ($app->isClient('site') && $doc->getType() == 'html') {
            $this->setForms();
        }
    }

    public function onBeforeRenderGridbox()
    {
        $this->setForms();
    }

    public function setForms()
    {
        /**
         * @var Joomla\CMS\Application\SiteApplication
         */
        $app = Factory::getApplication();
        $a_id = $app->input->get('a_id');
        $option = $app->input->get('option', '', 'string');
        if (empty($a_id) && $option != 'com_config') {
            BaformsHelper::prepareHelper();
            $html = $app->getBody();
            $pos = strpos($html, '</head>');
            $head = substr($html, 0, $pos);
            $body = substr($html, $pos);
            include JPATH_ROOT.'/components/com_baforms/tmpl/form/click-trigger.min.php';
            $body = str_replace('</body>', $out.'</body>', $body);
            $content = $this->getContent($body);
            $html = $head.$content;
            $app->setBody($html);
        }
    }
    
    public function getContent($body)
    {
        $body = BaformsHelper::renderFormHTML($body);

        return $body;
    }
}