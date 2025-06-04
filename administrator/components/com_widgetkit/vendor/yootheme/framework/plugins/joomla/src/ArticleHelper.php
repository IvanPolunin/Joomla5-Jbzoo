<?php

namespace YOOtheme\Framework\Joomla;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\ArticlesModel;

class ArticleHelper
{
    public function get($params)
    {
        // Ordering
        $direction = null;

        switch ($params['order']) {
            case 'featured':
                $ordering = 'fp.ordering';
                break;
            case 'random':
                $ordering = 'RAND()';
                break;
            case 'date':
                $ordering = 'created';
                break;
            case 'rdate':
                $ordering = 'created';
                $direction = 'DESC';
                break;
            case 'published':
                $ordering = 'publish_up';
                break;
            case 'rpublished':
                $ordering = 'publish_up';
                $direction = 'DESC';
                break;
            case 'modified':
                $ordering = 'modified';
                break;
            case 'rmodified':
                $ordering = 'modified';
                $direction = 'DESC';
                break;
            case 'alpha':
                $ordering = 'title';
                break;
            case 'ralpha':
                $ordering = 'title';
                $direction = 'DESC';
                break;
            case 'hits':
                $ordering = 'hits';
                $direction = 'DESC';
                break;
            case 'rhits':
                $ordering = 'hits';
                break;
            case 'ordering':
            default:
                $ordering = 'a.ordering';
                break;
        }

        $model = new ArticlesModel(['ignore_request' => true]);
        $model->setState('params', ComponentHelper::getParams('com_content'));
        $model->setState('filter.published', 1);
        $model->setState('filter.access', true);
        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $direction);
        $model->setState('list.start', 0);
        $model->setState('list.limit', (int) $params['items']);
        $model->setState('filter.language', Multilanguage::isEnabled());

        // set categories (filter option 0 - "All categories")
        if ($categories = array_filter((array) $params['catid'])) {
            $model->setState('filter.category_id', $categories);
        }

        $model->setState('filter.subcategories', $params['subcategories']);
        $model->setState('filter.max_category_levels', 999);

        // featured, accepted values ('hide' || 'only')
        if (!empty($params['featured'])) {
            $model->setState('filter.featured', $params['featured']);
        }

        return $model->getItems();
    }

    public function getUrl($item)
    {
        return Route::_(RouteHelper::getArticleRoute($item->id, $item->catid));
    }
}
