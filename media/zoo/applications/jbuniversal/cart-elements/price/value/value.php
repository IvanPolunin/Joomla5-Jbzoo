<?php
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
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

/**
 * Class JBCartElementPriceValue
 * @since 2.2
 */
class JBCartElementPriceValue extends JBCartElementPrice
{
    /**
     * Check if element has value
     * @param array $params
     * @return bool
     */
    public function hasValue($params = array())
    {
        return true;
    }

    /**
     * Get elements search data
     * @return mixed
     */
    public function getSearchData()
    {
        $prices = $this->getPrices();

        return $prices['total']->val(JBModelConfig::model()->getCurrency());
    }

    /**
     * @param array $params
     * @return mixed|null|string
     */
    public function edit($params = array())
    {
        if ($layout = $this->getLayout('edit.php')) {
            return self::renderEditLayout($layout, array(
                'value' => $this->get('value', '')
            ));
        }

        return null;
    }

    /**
     * @param array $params
     * @return array|mixed|null|string
     */
    public function render($params = array())
    {
        if(!$this->hasValue($params)) {
            return $this->renderWrapper();
        }
        $prices   = $this->getPrices();
        $discount = JBCart::val();
        
        // Try to get discount from JBPrice parent
        $jbPrice = $this->getJBPrice();
        if ($jbPrice) {
            $currentVariant = $jbPrice->getList()->current();
            if ($currentVariant && $discountElement = $currentVariant->get('_discount')) {
                $discountValue = $discountElement->getValue(true);
                if (!empty($discountValue)) {
                    $discount->set($discountValue);
                    
                    // Calculate discounted price and original price
                    // Original price = current price (444)
                    // Discounted price = current price - discount (444 - 55 = 389)
                    $originalPrice = clone $prices['price'];
                    $discountedPrice = clone $prices['price'];
                    $discountedPrice->minus($discount);
                    
                    // Set values for template:
                    $prices['price'] = $originalPrice; // This will be shown as "Цена:" (444) - should be crossed out
                    $prices['total'] = $discountedPrice; // This will be shown as "Было:" (389) - current price
                    $prices['save']->set($discount->val(), $discount->cur())->negative(); // Save amount (-55)
                }
            }
        } elseif ($prices['save']->isNegative()) {
            // Fallback to save calculation
            $discount->set($prices['save']->val(), $prices['save']->cur());
        }

        $total   = $prices['total'];
        $message = Text::_(StringHelper::trim($params->get('empty_text', '')));

        $layout = $params->get('layout', 'full-div');
        if ($total->isEmpty() && !empty($message)) {
            $layout = 'empty';
        }

        if ($layout = $this->getLayout($layout . '.php')) {
            // Debug: Log prices data
            if (defined('JDEBUG') && JDEBUG) {
                error_log('Value render - prices: ' . print_r([
                    'total' => $total->val() . ' ' . $total->cur(),
                    'price' => $prices['price']->val() . ' ' . $prices['price']->cur(),
                    'save' => $prices['save']->val() . ' ' . $prices['save']->cur(),
                    'total_empty' => $total->isEmpty(),
                    'variant' => $this->variant,
                    'item_id' => $this->item_id
                ], true));
                
                // Also log to a specific file
                file_put_contents('X:\\OSPanel\\home\\jbzoo.test\\tmp\\jbzoo_debug.log', 
                    date('[Y-m-d H:i:s] ') . 'Value render: ' . print_r([
                    'total' => $total->val() . ' ' . $total->cur(),
                    'price' => $prices['price']->val() . ' ' . $prices['price']->cur(),
                    'save' => $prices['save']->val() . ' ' . $prices['save']->cur(),
                    'total_empty' => $total->isEmpty(),
                    'variant' => $this->variant,
                    'item_id' => $this->item_id
                ], true) . "\n", FILE_APPEND);
            }
            
            return $this->renderLayout($layout, array(
                'total'    => $total,
                'price'    => $prices['price'],
                'save'     => $prices['save']->abs(true),
                'discount' => $discount->abs(),
                'currency' => $this->currency(),
                'message'  => $message
            ));
        }

        return null;
    }

    /**
     * Helper for cart: compute prices/discount in the same way as render(),
     * but return raw JBCartValue objects instead of HTML.
     *
     * @param array $params
     * @return array|null ['total' => JBCartValue, 'price' => JBCartValue, 'save' => JBCartValue, 'discount' => JBCartValue]
     */
    public function getCartPrices($params = array())
    {
        if (!$this->hasValue($params)) {
            return null;
        }

        $prices   = $this->getPrices();
        $discount = JBCart::val();

        // Повторяем логику скидки как в render()
        $jbPrice = $this->getJBPrice();
        if ($jbPrice) {
            $currentVariant = $jbPrice->getList()->current();
            if ($currentVariant && $discountElement = $currentVariant->get('_discount')) {
                $discountValue = $discountElement->getValue(true);
                if (!empty($discountValue)) {
                    $discount->set($discountValue);

                    // Original price = current price (444)
                    // Discounted price = current price - discount (444 - 55 = 389)
                    $originalPrice   = clone $prices['price'];
                    $discountedPrice = clone $prices['price'];
                    $discountedPrice->minus($discount);

                    // Set values for template:
                    $prices['price'] = $originalPrice;
                    $prices['total'] = $discountedPrice;
                    $prices['save']->set($discount->val(), $discount->cur())->negative();
                }
            }
        } elseif ($prices['save']->isNegative()) {
            // Fallback to save calculation
            $discount->set($prices['save']->val(), $prices['save']->cur());
        }

        $total = $prices['total'];
        $hasDiscount = !$discount->isEmpty();

        return array(
            'total'        => $total,
            'price'        => $prices['price'],
            'save'         => $prices['save'],
            'discount'     => $discount,
            'has_discount' => $hasDiscount,
        );
    }

    /**
     * Check if variant price will modified basic price
     * @return bool
     */
    public function isModifier()
    {
        if ($this->isBasic()) {
            return false;
        }
        $value = $this->get('value', null);

        return $this->getHelper()->isModifier($value);
    }

    /**
     * Get elements value
     * @param string $key      Array key.
     * @param mixed  $default  Default value if data is empty.
     * @param bool   $toString A string representation of the value.
     * @return mixed|string
     */
    public function getValue($toString = false, $key = 'value', $default = null)
    {
        $value = parent::getValue($toString, $key, $default);

        if ($this->isBasic()) {
            $value = $this->clearSymbols($value);
        }

        if ($toString) {
            return $value;
        }

        return JBCart::val($value);
    }

    /**
     * Returns data when variant changes
     * @param array $params
     * @return null
     */
    public function renderAjax($params = array())
    {
        return $this->render($params);
    }

    /**
     * Set data through data array.
     * @param  array  $data
     * @param  string $key
     * @return $this
     */
    public function bindData($data = array(), $key = 'value')
    {
        if (!is_array($data)) {
            $data = array($key => (string)$data);
        }

        foreach ($data as $key => $value) {
            if ($this->isBasic()) {
                $value = $this->clearSymbols($value);
            }
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isEmpty()
    {
        $prices = $this->getPrices();

        return $prices['total']->isEmpty();
    }
}
