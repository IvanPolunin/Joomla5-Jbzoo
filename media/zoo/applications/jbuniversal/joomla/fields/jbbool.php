<?php
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper as Html;
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

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

/**
 * Class JFormFieldJBBool
 */
class JFormFieldJBBool extends FormField
{

    protected $type = 'jbbool';

    /**
     * @return string
     */
    public function getInput()
    {
        $app = App::getInstance('zoo');

        $options = array(
            Html::_('select.option', 0, Joomla\CMS\Language\Text::_('JBZOO_NO')),
            Html::_('select.option', 1, Joomla\CMS\Language\Text::_('JBZOO_YES'))
        );

        $html   = array();
        $html[] = '<div class="jbzoo-complex-field">';
        $html[] = '<fieldset class="radio jbbool">';
        $html[] = $app->jbhtml->radio($options, $this->getName($this->fieldname), '', $this->value, false, false, false);
        $html[] = '</fieldset>';
        $html[] = '</div>';

        return implode(PHP_EOL, $html);
    }
}