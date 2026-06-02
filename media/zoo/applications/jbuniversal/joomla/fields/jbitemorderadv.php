<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
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

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

/**
 * Class JFormFieldJBItemOrder
 */
class JFormFieldJBItemOrderAdv extends FormField
{

    protected $type = 'jbitemorderadv';

    /**
     * Get input HTML
     * @return string
     */
    public function getInput()
    {
        $app  = App::getInstance('zoo');
        $list = $app->jborder->getListAdv();

        $values     = $app->data->create($this->value);
        $customName = $this->getName($this->fieldname);

        $html   = array();
        $html[] = '<span class="jbzoo-itemorder-label">' . Text::_('JBZOO_SORT_FIELD') . ': </span> ' .
            HTMLHelper::_('select.groupedlist', $list, $customName . '[field]', array(
                'list.select' => $this->value,
                'group.items' => null,
            ));

        $html[] = '<span class="jbzoo-itemorder-label">' . Text::_('JBZOO_SORT_AS') . ': </span> ' .
            $app->jbhtml->select(array(
                's' => Text::_('JBZOO_SORT_AS_STRINGS'),
                'n' => Text::_('JBZOO_SORT_AS_NUMBERS'),
                'd' => Text::_('JBZOO_SORT_AS_DATES'),
            ), $customName . '[mode]', '', $values->get('mode'));

        $html[] = '<span class="jbzoo-itemorder-label">' . Text::_('JBZOO_SORT_ORDER') . ': </span> ' .
            $app->jbhtml->select(array(
                'asc'    => Text::_('JBZOO_SORT_ORDER_ASC'),
                'desc'   => Text::_('JBZOO_SORT_ORDER_DESC'),
                'random' => Text::_('JBZOO_SORT_ORDER_RANDOM'),
            ), $customName . '[order]', '', $values->get('order'));


        return '<div class="jbzoo-complex-field"><div>' . implode("</div><div>\n ", $html) . '</div></div>';
    }
}
