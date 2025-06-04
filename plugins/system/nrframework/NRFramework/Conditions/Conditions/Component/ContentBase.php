<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ContentBase extends ComponentBase
{
    /**
     * Get single page's assosiated categories
     *
     * @param   integer  The Single Page id
	 * 
     * @return  integer
     */
	protected function getSinglePageCategories($id)
	{
		// If the article is not assigned to any menu item, the cat id should be available in the query string. Let's check it.
		if ($requestCatID = $this->app->input->getInt('catid', null))
		{
			return $requestCatID;
		}

		// Apparently, the catid is not available in the Query String. Let's ask Article model.
		$item = $this->getItem($id);

		if (is_object($item) && isset($item->catid)) 
		{
			return $item->catid;
		}
	}
	
	/**
	 *  Load a Joomla article data object.
	 *
	 *  @return  object
	 */
	public function getItem($id = null)
	{
		$id = is_null($id) ? $this->request->id : $id;

		// Sanity check
		if (is_null($id))
		{
			return;
		}

        $hash  = md5('contentItem' . $id);
        $cache = $this->factory->getCache();

        if ($cache->has($hash))
        {
            return $cache->get($hash);
        }

		// Prevent "Article not found" error on J3.
		if (!defined('nrJ4'))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . $db->q((int) $id));
	
			$db->setQuery($query);
	
			if (!$db->loadResult())
			{
				return $cache->set($hash, null);
			}
		}

		// Use try catch to prevent fatal errors in case the article is not found
		try
		{
			$model = $this->getArticleModel();
			$item = $model->getItem($id);

			if ($item)
			{
				$item->images  = is_string($item->images) ? json_decode($item->images) : $item->images;
				$item->urls    = is_string($item->urls) ? json_decode($item->urls) : $item->urls;
				$item->attribs = is_string($item->attribs) ? json_decode($item->attribs) : $item->attribs;
			}

			return $cache->set($hash, $item);
		} catch (\Throwable $th)
		{
			return null;
		}
	}

	/**
	 * Return the Article's model.
	 *
	 * @return object
	 */
    private function getArticleModel()
    {
        if (defined('nrJ4'))
        {
            $mvcFactory = Factory::getApplication()->bootComponent('com_content')->getMVCFactory();
            return $mvcFactory->createModel('Article', 'Administrator');
        }

        // Joomla 3
        BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/models');
		return BaseDatabaseModel::getInstance('Article', 'ContentModel');
    }
}