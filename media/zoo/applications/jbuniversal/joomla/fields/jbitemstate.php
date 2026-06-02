<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
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

jimport('joomla.form.formfield');

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

/**
 * Class JFormFieldJBItemState
 */
class JFormFieldJBItemState extends FormField
{

    protected $type = 'jbitemstate';

    /**
     * @return string
     */
    public function getInput()
    {
        // get app
        $app = App::getInstance('zoo');

        // create select
        $options = array(
            '0' => Text::_('JBZOO_FIELDS_ALL'),
            '1' => Text::_('JBZOO_FIELDS_ITEMSTATE_ON_PUBLISHED_TIME'),
            '2' => Text::_('JBZOO_FIELDS_ITEMSTATE_ON'),
            '3' => Text::_('JBZOO_FIELDS_ITEMSTATE_OFF'),
        );

        return $app->html->_('select.genericlist', $options, $this->getName($this->fieldname), '', 'value', 'text', $this->value ?? "");
    }

}
