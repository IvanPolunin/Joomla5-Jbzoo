<?php
/**
 * JBZoo App is universal Joomla CCK, application for YooTheme Zoo component
 * @package     jbzoo
 * @version     2.x Pro
 * @author      JBZoo App http://jbzoo.com
 * @copyright   Copyright (C) JBZoo.com,  All rights reserved.
 * @license     http://jbzoo.com/license-pro.php JBZoo Licence
 * @coder       Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class JBCartElementOrderFieldText
 */
class JBCartElementOrderPhone extends JBCartElementOrder
{
    /**
     * Renders the element in submission
     * @param array $params
     * @return string
     */
    public function renderSubmission($params = array())
    {
		$this->app->jbassets->js('cart-elements:order/upload/assets/js/maskedinput.min.js');
		
        $value          = $this->getUserState($params->get('user_field'));
        $mask           = $this->config->get('mask', '+375 (99) 999-99-99');
        $custommask     = $this->config->get('custommask', '');
        $placeholder    = $this->config->get('placeholder', '+375 (__) ___-__-__');
        $mask = ($mask == 'custom') ? $custommask : $mask;
        return $this->app->html->_(
            'control.text',
            $this->getControlName('value'),
            $this->get('value', $value),
            'size="60" class="phone" maxlength="255" placeholder="'.$placeholder.'"'
        ).'
        <script type="text/javascript">
            jQuery(function($){
                $(".phone").mask("'.$mask.'",{placeholder:"'.$placeholder.'"});
            });
        </script>
        ';
    }	
    public function loadAssets()
		{
			$this->app->jbassets->js('cart-elements:order/phone/assets/js/maskedinput.min.js');
			return parent::loadAssets();
		}

}