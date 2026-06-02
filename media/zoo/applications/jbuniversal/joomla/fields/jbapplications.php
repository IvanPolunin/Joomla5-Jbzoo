<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldJBApplications extends FormField
{
    protected $type = 'JBApplications';

    protected function getInput()
    {
        $app = \App::getInstance('zoo');
        $appList = $app->table->application->all();
        $basketOnly = (int)$this->getAttribute('cart_only', 0);

        $options = [];
        foreach ($appList as $appItem) {
            if ($basketOnly) {
                if ((int)$appItem->getParams()->get('global.jbzoo_cart_config.enable', 0)) {
                    $options[$appItem->id] = $appItem->name;
                }
            } else {
                $options[$appItem->id] = $appItem->name;
            }
        }

        $attrs = [
            'id' => $this->id,
            'class' => 'form-select',
            'style' => 'width: 250px;'
        ];

        if ($this->multiple) {
            $attrs['multiple'] = 'multiple';
            $attrs['size'] = '5';
            $this->name .= '[]';
        }

        return HTMLHelper::_('select.genericlist', $options, $this->name, $attrs, 'value', 'text', $this->value, $this->id);
    }
}
