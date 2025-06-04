<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikaShopBase extends EcommerceBase
{
    /**
     * The component's Single Page view name
     *
     * @var string
     */
    protected $viewSingle = 'product';

    /**
     * The component's option name
     *
     * @var string
     */
    protected $component_option = 'com_hikashop';

	/**
	 * The request ID used to retrieve the ID of the product
	 * 
	 * @var  string
	 */
    protected $request_id = 'product_id';
    
    /**
     * Class Constructor
     *
     * @param object $options
     * @param object $factory
     */
    public function __construct($options, $factory)
	{
		parent::__construct($options, $factory);
        $this->request->id = $this->app->input->get('cid', $this->app->input->getInt('product_id'));
    }

    /**
     * Get single page's assosiated categories
     *
     * @param   Integer  The Single Page id
	 * 
     * @return  array
     */
	protected function getSinglePageCategories($id)
	{
        $db = $this->db;
        
        $query = $db->getQuery(true)
            ->select('category_id')
            ->from('#__hikashop_product_category')
            ->where($db->quoteName('product_id') . '=' . $db->q($id));

		$db->setQuery($query);
		
		return $db->loadColumn();
	}

	/**
     * Returns Hikashop cart data
     *
     * @return  mixed
     */
	protected function getCart()
	{
        @include_once(implode(DIRECTORY_SEPARATOR, [JPATH_ADMINISTRATOR, 'components', 'com_hikashop', 'helpers', 'helper.php']));
		@include_once(implode(DIRECTORY_SEPARATOR, [JPATH_ADMINISTRATOR, 'components', 'com_hikashop', 'helpers', 'checkout.php']));

		if (!class_exists('hikashopCheckoutHelper'))
		{
			return;
		}

		$checkoutHelper = \hikashopCheckoutHelper::get();
		return $checkoutHelper->getCart(true);
	}

	/**
	 * Returns the products in the cart
	 * 
	 * @return  array
	 */
	protected function getCartProducts()
	{
		if (!$cart = $this->getCart())
		{
			return [];
		}

		return $cart->cart_products;
	}

	/**
	 * Returns the current user's last purchase date in format: d/m/Y H:i:s and in UTC.
	 * 
	 * @param   int     $user_id
	 * 
	 * @return  string
	 */
	protected function getLastPurchaseDate($user_id = null)
	{
		if (!$user_id)
		{
			return;
		}

        $db = $this->db;

        $query = $this->db->getQuery(true)
            ->clear()
            ->select('o.order_created')
            ->from('#__hikashop_order_product AS op')
            ->leftJoin('#__hikashop_order AS o ON o.order_id = op.order_id')
            ->leftJoin('#__hikashop_user AS u ON u.user_id = o.order_user_id')
            ->where('o.order_status IN ("confirmed", "shipped")')
            ->where('u.user_cms_id = ' . (int) $user_id)
            ->order('o.order_created DESC')
            ->setLimit(1);

        $db->setQuery($query);

        return $db->loadResult();
	}

	/**
	 * Returns the current product.
	 * 
	 * @return  object
	 */
	protected function getCurrentProduct()
	{
		if (!$this->request->id)
		{
			return;
		}
        
        if (!function_exists('hikashop_get'))
        {
            return;
        }

        $productClass = hikashop_get('class.product');
        return $productClass->get($this->request->id);
	}

	/**
	 * Returns the current product data.
	 * 
	 * @return  object
	 */
	protected function getCurrentProductData()
	{
		if (!$product = $this->getCurrentProduct())
		{
			return;
		}

        return [
            'id' => $product->product_id,
            'price' => (float) $product->product_msrp
        ];
	}

	/**
	 * Returns the product stock.
	 * 
	 * @param   int  $id
	 * 
	 * @return  int
	 */
	public function getProductStock($id = null)
	{
		if (!$id)
		{
			return;
		}

        if (!function_exists('hikashop_get'))
        {
            return;
        }

        $productClass = hikashop_get('class.product');
        if (!$product = $productClass->get($id))
        {
            return;
        }

        // Means infinite
        if ($product->product_quantity === -1)
        {
            return PHP_INT_MAX;
        }
        
        return (int) $product->product_quantity;
	}
}