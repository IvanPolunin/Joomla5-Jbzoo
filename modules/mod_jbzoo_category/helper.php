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

require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
require_once JPATH_BASE . '/media/zoo/applications/jbuniversal/framework/jbzoo.php';
require_once JPATH_BASE . '/media/zoo/applications/jbuniversal/framework/classes/jbmodulehelper.php'; // TODO move to bootstrap


/**
 * Class JBModuleHelperCategory
 */
class JBModuleHelperCategory extends JBModuleHelper
{
    /**
     * @var array|null
     */
    protected $_categoryCounts = null;

    /**
     * @param JRegistry $params
     * @param stdClass  $module
     */
    public function __construct(\Joomla\Registry\Registry $params, $module)
    {
        parent::__construct($params, $module);

        $this->_initParams();
    }

    /**
     * Load module assets
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();

        $this->_jbassets->less('mod_jbzoo_category:assets/styles.less');
    }

    /**
     * @return array|null
     */
    public function getCategories()
    {
        $renderCat  = array();
        $appId      = (int)$this->_params->get('application', false);
        $menuItem   = (int)$this->_params->get('menu_item', 0);
        $categories = $this->_getCategories();
        $curCatId   = $this->getCurrentCategory();
        $showCount  = (int)$this->_params->get('display_count_items', 1);
        $showItems  = (int)$this->_params->get('display_items', 1);
        $counts     = $showCount ? $this->_getCategoryItemCounts($categories) : array();

        if ($appId && !empty($categories)) {

            foreach ($categories as $category) {

                if ($menuItem) {
                    $catUrl = str_replace('?f=1', '', $this->app->route->category($category, true, $menuItem));
                } else {
                    $catUrl = $this->app->route->category($category);
                }

                $currentCat = array(
                    'active_class'  => ($curCatId == $category->id) ? 'category-active' : '',
                    'cat_link'      => $catUrl,
                    'category_name' => $category->name,
                    'item_count'    => null,
                    'desc'          => null,
                    'image'         => null,
                    'items'         => array(),
                );

                if ($showCount) {
                    $currentCat['item_count'] = isset($counts[$category->id]) ? $counts[$category->id] : 0;
                }

                if ((int)$this->_params->get('category_display_image', 1) && $image = $category->getImage('content.category_teaser_image')) {
                    $currentCat['image'] = $this->_getImageSettings($image);
                    $currentCat['attr']  = $this->_getImageSettings($image, true);
                }

                if ($showItems) {
                    $currentCat['items'] = $this->_getItems($category->id);
                }

                if ((int)$this->_params->get('category_display_description', false)) {
                    $currentCat['desc'] = $category->getText($category->params->get('content.category_teaser_text'));
                }

                $renderCat[$category->id] = $currentCat;
            }
        }

        return $renderCat;
    }

    /**
     * Get published item counts for all rendered categories in one query.
     *
     * @param array $categories
     * @return array
     */
    protected function _getCategoryItemCounts($categories)
    {
        if ($this->_categoryCounts !== null) {
            return $this->_categoryCounts;
        }

        $categoryIds = array();
        foreach ($categories as $category) {
            $categoryIds[] = (int) $category->id;
        }

        $categoryIds = array_filter(array_unique($categoryIds));

        if (empty($categoryIds)) {
            $this->_categoryCounts = array();
            return $this->_categoryCounts;
        }

        $db   = $this->app->database;
        $user = $this->app->user->get();
        $date = $this->app->date->create();
        $now  = $db->Quote($date->toSQL());
        $null = $db->Quote($db->getNullDate());

        $query = "SELECT ci.category_id, COUNT(DISTINCT i.id) AS item_count"
            ." FROM ".ZOO_TABLE_CATEGORY_ITEM." AS ci"
            ." JOIN ".ZOO_TABLE_ITEM." AS i ON ci.item_id = i.id"
            ." WHERE ci.category_id IN (".implode(',', $categoryIds).")"
            ." AND i.application_id = ".(int) $this->_params->get('app_id')
            ." AND i.".$this->app->user->getDBAccessString($user)
            ." AND i.state = 1"
            ." AND (i.publish_up = ".$null." OR i.publish_up <= ".$now.")"
            ." AND (i.publish_down = ".$null." OR i.publish_down >= ".$now.")"
            ." GROUP BY ci.category_id";

        $rows   = $db->queryObjectList($query);
        $counts = array_fill_keys($categoryIds, 0);

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $counts[(int) $row->category_id] = (int) $row->item_count;
            }
        }

        $this->_categoryCounts = $counts;

        return $this->_categoryCounts;
    }

    /**
     * @param      $image
     * @param bool $attr
     * @return string
     */
    protected function _getImageSettings($image, $attr = false)
    {
        $imgAttrs = array(
            'src'    => $image['src'],
            'width'  => $image['width'],
            'height' => $image['height'],
        );

        if ((int)$this->_params->get('category_image_width') || (int)$this->_params->get('category_image_height')) {

            $width  = (int)$this->_params->get('category_image_width', 100);
            $height = (int)$this->_params->get('category_image_height', 100);
            $image  = $this->app->jbimage->resize($image['path'], $width, $height);

            $imgAttrs = array_merge($imgAttrs, array(
                'src'    => $image->url,
                'width'  => $image->width,
                'height' => $image->height,
            ));
        }

        return ($attr) ? $imgAttrs : '<img ' . $this->app->jbhtml->buildAttrs($imgAttrs) . ' />';
    }

    /**
     * Get category list
     * @return array
     */
    protected function _getCategories()
    {
        $categories = JBModelCategory::model()->getList(
            $this->_params->get('app_id'),
            array(
                'limit'     => $this->_params->get('category_limit'),
                'parent'    => $this->_params->get('cat_id'),
                'order'     => $this->_params->get('category_order'),
                'published' => 1,
            )
        );
        return $categories;
    }

    /**
     * Get items
     * @param $catId
     * @return mixed
     */
    protected function _getItems($catId)
    {
        $items = JBModelItem::model()->getList(
            $this->_params->get('app_id'),
            $catId,
            $this->_params->get('type_id', false),
            array(
                'limit'     => $this->_params->get('items_limit'),
                'published' => 1,
                'order'     => $this->_params->get('item_order'),
            )
        );

        return $items;
    }

    /**
     * Set mixed params for module
     */
    protected function _initParams()
    {
        list($appId, $catId) = explode(':', $this->_params->get('application', '0:0'));
        $itemsLimit    = (int)$this->_params->get('items_limit', 4);
        $categoryLimit = (int)$this->_params->get('category_limit', 0);

        ($itemsLimit == 0) ? $this->_params->set('items_limit', null) : $this->_params->set('items_limit', $itemsLimit);
        ($categoryLimit == 0) ? $this->_params->set('category_limit', null) : $this->_params->set('category_limit', $categoryLimit);

        $this->_params->set('app_id', (int)$appId);
        $this->_params->set('cat_id', (int)$catId);

    }

    /**
     * @return int
     */
    public function getCurrentCategory()
    {
        return $this->app->jbrequest->getSystem('category', 0);
    }
}
