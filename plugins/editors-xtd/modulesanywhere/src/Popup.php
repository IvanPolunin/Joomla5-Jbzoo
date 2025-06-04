<?php
/**
 * @package         Modules Anywhere
 * @version         8.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2024 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\EditorButton\ModulesAnywhere;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form as JForm;
use Joomla\Component\Modules\Administrator\Model\ModulesModel as JModulesModel;
use RegularLabs\Component\AdvancedModules\Administrator\Model\ModulesModel as AdvancedModuleManagerModulesModel;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonPopup as RL_EditorButtonPopup;
use RegularLabs\Library\Input as RL_Input;
use RegularLabs\Library\RegEx as RL_RegEx;

class Popup extends RL_EditorButtonPopup
{
    public    $items;
    public    $filterForm;
    public    $pagination;
    public    $state;
    protected $extension         = 'modulesanywhere';
    protected $require_core_auth = false;

    public function init()
    {
        $option = class_exists('\RegularLabs\Component\AdvancedModules\Administrator\Model\ModulesModel')
            ? 'com_advancedmodules'
            : 'com_modules';

        $model = $option == 'com_advancedmodules'
            ? new AdvancedModuleManagerModulesModel
            : new JModulesModel;

        @define('JPATH_COMPONENT', JPATH_ADMINISTRATOR . '/components/' . $option);
        @define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);

        $limitstart = RL_Input::getInt('limitstart', 0);

        $this->state = $model->getState();
        $model->setState('client_id', 0);

        if ($limitstart)
        {
            $model->setState('list.start', $limitstart);
        }

        $this->items      = $model->getItems();
        $this->filterForm = $model->getFilterForm();
        $this->pagination = $model->getPagination();

        $this->filterForm->removeField('client_id');

        $xmlfile = JPATH_SITE . '/plugins/editors-xtd/modulesanywhere/forms/popup.xml';

        $this->form = new JForm('modulesanywhere');
        $this->form->loadFile($xmlfile, 1);
    }

    protected function loadScripts()
    {
        $params = $this->getParams();

        $this->editor_name = RL_Input::getString('editor', 'text');
        // Remove any dangerous character to prevent cross site scripting
        $this->editor_name = RL_RegEx::replace('[\'\";\s]', '', $this->editor_name);

        RL_Document::scriptOptions([
            'module_tag'     => $params->module_tag ?? 'module',
            'modulepos_tag'  => $params->modulepos_tag ?? 'modulepos',
            'tag_characters' => explode('.', $params->tag_characters),
            'editor_name'    => $this->editor_name,
        ],
            'modulesanywhere_button'
        );

        RL_Document::usePreset('choicesjs');
        RL_Document::useScript('webcomponent.field-fancy-select');

        RL_Document::script('regularlabs.regular');
        RL_Document::script('regularlabs.admin-form');
        RL_Document::script('regularlabs.admin-form-descriptions');
        RL_Document::script('modulesanywhere.popup');

        $script = "document.addEventListener('DOMContentLoaded', function(){RegularLabs.ModulesAnywherePopup.init()});";
        RL_Document::scriptDeclaration($script, 'Modules Anywhere Button', true, 'after');
    }
}
