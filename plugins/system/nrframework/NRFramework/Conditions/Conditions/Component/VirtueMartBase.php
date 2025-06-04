<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class VirtueMartBase extends EcommerceBase
{
    /**
     * The component's Single Page view name
     *
     * @var string
     */
    protected $viewSingle = 'productdetails';

    /**
     * The component's option name
     *
     * @var string
     */
    protected $component_option = 'com_virtuemart';

    /**
	 * The request ID used to retrieve the ID of the product
	 * 
	 * @var  string
	 */
    protected $request_id = 'virtuemart_product_id';

	/**
	 * The request ID used to retrieve the ID of the product category.
	 * 
	 * @var  string
	 */
	protected $category_request_id = 'virtuemart_category_id';

    /**
     * Class Constructor
     *
     * @param object $options
     * @param object $factory
     */
    public function __construct($options, $factory)
	{
		parent::__construct($options, $factory);
		
        $this->request->id = $this->app->input->getInt($this->request_id, $this->app->input->getInt($this->category_request_id));
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
            ->select('virtuemart_category_id')
            ->from('#__virtuemart_product_categories')
            ->where($db->quoteName($this->request_id) . '=' . $db->q($id));

        $db->setQuery($query);

        return $db->loadColumn();
	}

	/**
     * Returns Virtuemart cart data
     *
     * @return  mixed
     */
	protected function getCart()
	{
		// load the configuration wherever required as its not available everywhere
		if (!class_exists('VmConfig'))
		{
			@include_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
			
			\VmConfig::loadConfig();
		}
		
		@include_once JPATH_SITE . '/components/com_virtuemart/helpers/cart.php';
		
		if (!class_exists('VirtueMartCart'))
		{
			return;
		}
		
		$cart = \VirtueMartCart::getCart();
		$cart->prepareCartData();

		return $cart;
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

		return $cart->products;
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
            ->select('created_on')
            ->from('#__virtuemart_orders')
            ->where('order_status IN ("C", "S", "F")')
            ->where('virtuemart_user_id = ' . (int) $user_id)
            ->order('created_on DESC')
            ->setLimit(1);

        $db->setQuery($query);

        return strtotime($db->loadResult());
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

		return $this->getProductById($this->request->id);
	}

	protected function getProductById($id = null)
	{
		if (!$id)
		{
			return;
		}

		if (!class_exists('VmModel'))
		{
			return;
		}
		
		return \VmModel::getModel('Product')->getProduct($id);
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
            'id' => $product->virtuemart_product_id,
            'price' => isset($product->prices['salesPrice']) ? (float) $product->prices['salesPrice'] : 0
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

		
		if (!$product = $this->getProductById($id))
		{
			return;
		}

		return $product->product_in_stock;
	}

	/*
	 *  Returns all parent rows
	 *
	 *  @param   integer  $id      Row primary key
	 *  @param   string   $table   Table name
	 *  @param   string   $parent  Parent column name
	 *  @param   string   $child   Child column name
	 *
	 *  @return  array             Array with IDs
	 */
	public function getParentIds($id = 0, $table = 'virtuemart_categories', $parent = 'category_parent_id', $child = 'virtuemart_category_id')
	{
		return parent::getParentIds($id, $table, $parent, $child);
	}
}