<?php
use Joomla\CMS\Language\Text;
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
 * Class JFormFieldJBSpacer
 */
class JFormFieldJBDelimiter extends FormField
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var string
     */
    private $uniq;

    /**
     * @var string
     */
    protected $type = 'jbdelimiter';

    /**
     * Class constructor
     * @param null|JForm $form
     */
    public function __construct($form = null)
    {
        parent::__construct($form);

        $this->app     = App::getInstance('zoo');
        $this->uniq    = $this->app->jbstring->getId('delimiter-');
    }

    /**
     * Renders field HTML
     * @return string
     */
    public function getInput()
    {
        $this->app->jbassets->initJBDelimiter('#' . $this->uniq);
        $group = $this->element->attributes()->group;

        return '<div id="' . $this->uniq . '" data-group="' . $group . '">
                ' . $this->_getInput() . '
                </div>';
    }

    /**
     * Remove label from field
     * @return null
     */
    public function getLabel()
    {
        return null;
    }

    /**
     * Check if not empty default value
     * @return bool|string
     */
    protected function _getInput()
    {
        $value = Text::_($this->element->attributes()->default);
        if(!empty($value)) {
            return '<strong class="jbdelimiter"> - = ' . $value . ' = -</strong>';
        }

        return false;
    }

}