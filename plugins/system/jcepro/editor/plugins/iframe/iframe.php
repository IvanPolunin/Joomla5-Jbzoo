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
use Joomla\CMS\Language\Text;

class WFIframePlugin extends WFEditorPlugin
{
    public function __construct($config = array())
    {
        $config = array(
            'base_path' => __DIR__
        );

        parent::__construct($config);
    }
    
    public function display()
    {
        $this->set('colorpicker', true);

        parent::display();

        $document = WFDocument::getInstance();

        // create new tabs instance
        $tabs = WFTabs::getInstance(
            array(
                'base_path' => __DIR__
            )
        );

        $document->addScript(array(
            'plugins/iframe/js/iframe'
        ), 
        'pro');

        $document->addStyleSheet(array(
            'plugins/iframe/css/iframe'
        ), 
        'pro');

        $document->addScriptDeclaration('IframeDialog.settings=' . json_encode($this->getSettings()) . ';');

        // Add tabs
        $tabs->addTab('file');
        $tabs->addTab('advanced', $this->getParam('tabs_advanced', 1));

        $this->aggregators = array();

        // Load video aggregators (Youtube, Vimeo etc)
        $video = new WFAggregatorExtension(array('format' => 'video', 'embed' => 'false'));

        if ($video) {
            $this->aggregators[] = $video;
            $video->display();
        }

        $maps = new WFAggregatorExtension(array('format' => 'maps'));

        if ($maps) {
            $this->aggregators[] = $maps;
            $maps->display();
        }

        if (!empty($this->aggregators)) {
            $tabs->addTab('options', $this->getParam('tabs_options', 1), array('plugin' => $this));
        }
    }

    public function getAggregatorOptions()
    {
        $names = array();

        foreach ($this->aggregators as $aggregator) {
            foreach ($aggregator->getAggregators() as $item) {
                if ($item->getName() === 'video' || $item->getName() === 'audio') {
                    continue;
                }

                $names[] = Text::_($item->getTitle());
            }
        }

        return $names;
    }

    public function getAggregatorTemplate()
    {
        $tpl = '';

        foreach ($this->aggregators as $aggregator) {
            foreach ($aggregator->getAggregators() as $item) {
                if ($item->getName() === 'video' || $item->getName() === 'audio') {
                    continue;
                }

                $data = $aggregator->loadTemplate($item->getName());

                if (!empty($data)) {
                    $tpl .= '<div class="aggregator_option ' . $item->getName() . '" id="' . $item->getName() . '_options"><h4>' . Text::_($item->getTitle()) . '</h4>';
                    $tpl .= $data;
                    $tpl .= '</div>';
                }
            }
        }

        return $tpl;
    }

    public function getSettings($settings = array())
    {
        $profile = $this->getProfile();

        $settings = array(
            'defaults' => $this->getDefaults(),
            'file_browser' => $this->getParam('file_browser', 1) && in_array('browser', explode(',', $profile->plugins)),
        );

        return parent::getSettings($settings);
    }
}
