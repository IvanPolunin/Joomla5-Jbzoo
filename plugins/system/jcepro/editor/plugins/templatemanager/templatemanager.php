<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

JLoader::registerNamespace('Michelf', WF_EDITOR_PRO_PLUGINS . '/textpattern/vendor/php-markdown/Michelf', false, false, 'psr4');

final class WFTemplateManagerPlugin extends WFMediaManager
{
    protected $_filetypes = 'html=html,htm;text=txt,md';

    protected $name = 'templatemanager';

    public function __construct($config = array())
    {
        $config = array(
            'base_path' => __DIR__
        );
        
        parent::__construct($config);

        // add a request to the stack
        $request = WFRequest::getInstance();
        $request->setRequest(array($this, 'loadTemplate'));
        $request->setRequest(array($this, 'getTemplateList'));

        if ($this->getParam('allow_save', 1)) {
            $request->setRequest(array($this, 'createTemplate'));
            $this->addFileBrowserAction('save', array('action' => 'createTemplate', 'title' => Text::_('WF_TEMPLATEMANAGER_CREATE')));
        }
    }

    /**
     * Display the plugin.
     */
    public function display()
    {
        parent::display();

        // create new tabs instance
        $tabs = WFTabs::getInstance(array(
            'base_path' => __DIR__,
        ));

        // Add tabs
        $tabs->addPanel('default', 1);

        $document = WFDocument::getInstance();

        $document->addScript(
            array(
                'plugins/templatemanager/js/templatemanager'
            ), 
            'pro'
        );

        $document->addStyleSheet(
            array(
                'plugins/templatemanager/css/templatemanager'
            ), 
            'pro'
        );

        $document->addScriptDeclaration('TemplateManager.settings=' . json_encode($this->getSettings()) . ';');
    }

    public function onUpload($file, $relative = '')
    {
        parent::onUpload($file, $relative);

        $app = Factory::getApplication();

        $browser = $this->getFileBrowser();

        // get the relative filesystem path
        $path = $browser->getFileSystem()->toRelative($file);

        // write back if html
        if (preg_match('#\.(htm|html)$#', $file)) {
            $data = $this->processTemplate($path);

            if (!empty($data)) {
                $browser->getFileSystem()->write($path, stripslashes($data));
            }
        }

        if ($app->input->getInt('inline', 0) === 1) {
            $result = array(
                'file' => $relative,
                'name' => WFUtility::mb_basename($file),
            );

            $result['data'] = $this->loadTemplate($path);

            return $result;
        }

        return array();
    }

    public function createTemplate($dir, $name)
    {
        $browser = $this->getFileBrowser();

        $app = Factory::getApplication();

        // check path
        WFUtility::checkPath($dir);

        // check name
        WFUtility::checkPath($name);

        // validate name
        if (WFUtility::validateFileName($name) === false) {
            throw new InvalidArgumentException('Action Failed: The file name is invalid.');
        }

        // get data
        $data = $app->input->post->get('data', '', 'RAW');
        $data = rawurldecode($data);

        $name = File::makeSafe($name) . '.html';
        $path = WFUtility::makePath($dir, $name);

        // Remove any existing template div
        $data = preg_replace('/<div(.*?)class="mceTmpl"([^>]*?)>([\s\S]*?)<\/div>/i', '$3', $data);

        $data = stripslashes($data);

        if (!$browser->getFileSystem()->write($path, $data)) {
            $browser->setResult(Text::_('WF_TEMPLATEMANAGER_WRITE_ERROR'), 'error');
        }

        return $browser->getResult();
    }

    public function replaceValuesToArray()
    {
        $data = array();
        $params = $this->getParam('replace_values');

        if ($params) {
            if (is_string($params)) {
                foreach (explode(',', $params) as $param) {
                    list($key, $value) = preg_split('/[:=]/', $param);

                    $key = trim($key, chr(0x22) . chr(0x27) . chr(0x38));
                    $value = trim($value, chr(0x22) . chr(0x27) . chr(0x38));

                    $data[$key] = trim($value);
                }
            } else {
                foreach ($params as $item) {
                    list($key, $value) = array_values($item);
                    $data[$key] = trim($value);
                }
            }
        }

        return $data;
    }

    protected function replaceVars($matches)
    {
        $key = $matches[1];

        switch ($key) {
            case 'modified':
                return WFUtility::formatDate($this->getParam('mdate_format', 'Y-m-d H:i:s'));
                break;
            case 'created':
                return WFUtility::formatDate($this->getParam('cdate_format', 'Y-m-d H:i:s'));
                break;
            case 'username':
            case 'usertype':
            case 'name':
            case 'email':
                $user = Factory::getUser();

                return isset($user->$key) ? $user->$key : $key;
                break;
            default:

                // Replace other pre-defined variables
                $values = $this->replaceValuesToArray();

                if (isset($values[$key])) {
                    return $values[$key];
                }

                // return raw variable for user replacement
                return $matches[0];

                break;
        }
    }

    private function processTemplate($file)
    {
        $browser = $this->getFileBrowser();

        // check path
        WFUtility::checkPath($file);

        // read content
        $content = $browser->getFileSystem()->read($file);

        if (empty($content)) {
            return '';
        }

        // Remove body etc.
        if (preg_match('/<body[^>]*>([\s\S]+?)<\/body>/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        return $content;
    }

    public function loadTemplate($file)
    {
        $content = $this->processTemplate($file);

        $ext = WFUtility::getExtension($file, true);

        // process markdown
        if ($ext === 'md') {
            $content = \Michelf\Markdown::defaultTransform($content);
        }
        
        // normalize variables to use ${var} syntax
        $content = preg_replace('/\{\$(.*?)\}/', '${$1}', $content);

        // Replace variables
        $content = preg_replace_callback('/\$\{(.+?)\}/i', array($this, 'replaceVars'), $content);

        return $content;
    }

    public function getViewable()
    {
        return $this->getFileTypes('list');
    }

    protected function getFileBrowserConfig($config = array())
    {
        $config['expandable'] = false;
        $config['position'] = 'bottom';

        return parent::getFileBrowserConfig($config);
    }

    public function getTemplateList()
    {
        $list = array();

        $templates = $this->getParam('templates', array());

        if (is_string($templates)) {
            $templates = json_decode(htmlspecialchars_decode($templates), true);
        }

        if (!empty($templates)) {
            foreach ($templates as $template) {
                $value = "";
                $thumbnail = "";

                // ensure an array
                $template = (array) $template;

                // must have a name
                if (!isset($template['name'])) {
                    continue;
                }
                // check for thumbnail (optional)
                if (!isset($template['thumbnail'])) {
                    $template['thumbnail'] = '';
                }
                // check for url (optional)
                if (!isset($template['url'])) {
                    $template['url'] = '';
                }
                // check for html (optional)
                if (!isset($template['html'])) {
                    $template['html'] = '';
                }

                extract($template);

                $url = trim($url);
                $html = trim($html);

                // some values must be set
                if (empty($url) && empty($html)) {
                    continue;
                }

                if (!empty($url)) {
                    if (preg_match("#\.(htm|html|txt)$#", $url) && strpos('://', $url) === false) {
                        $url = trim($url, '/');

                        $file = JPATH_SITE . '/' . $url;

                        if (is_file($file)) {
                            $value = Uri::root() . $url;

                            $filename = WFUtility::stripExtension($url);

                            if (!$thumbnail && is_file(JPATH_SITE . '/' . $filename . '.jpg')) {
                                $thumbnail = $filename . '.jpg';
                            }
                        }
                    }
                } else if (!empty($html)) {
                    $value = htmlspecialchars_decode($html);
                }

                if ($thumbnail) {
                    $thumbnail = Uri::root(true) . '/' . $thumbnail;
                }

                $list[$name] = array(
                    'data' => $value,
                    'image' => $thumbnail,
                );
            }
        }

        // try files list
        if (empty($list)) {
            $browser = $this->getFileBrowser();
            $filesystem = $browser->getFileSystem();

            // skip for external filesystems
            if (!$filesystem->get('local')) {
                return $list;
            }

            // get items
            $items = $browser->getItems('', 0);

            foreach ($items['files'] as $item) {
                if ($item['name'] === "index.html") {
                    continue;
                }

                $name = WFUtility::getFilename($item['name']);
                $value = $item['properties']['preview'];

                $list[$name] = array(
                    'data' => $value,
                    'image' => '',
                );
            }
        }

        return $list;
    }
}
