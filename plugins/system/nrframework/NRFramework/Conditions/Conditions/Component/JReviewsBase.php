<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class JReviewsBase extends ComponentBase
{
    protected $viewSingle = 'article';

    /**
     * The component's option name
     *
     * @var string
     */
    protected $component_option = 'com_content';

    /**
     *  Indicates whether the page is a category page
     *
     *  @return  boolean
     */
    protected function isCategory()
    {
        return is_null($this->request->task);
    }

    /**
     *  Indicates whether the page is a single page
     *
     *  @return  boolean
     */
    public function isSinglePage()
    {
		if (!class_exists('\ClassRegistry'))
		{
            return;
        }

        if (!$listingModel = \ClassRegistry::getClass('EverywhereComContentModel'))
        {
            return;
        }

        if (!$listing = $listingModel->getListingById($this->request->id))
        {
            return;
        }
        
        return $this->request->view === 'article' && $this->request->option === 'com_content' && $listing;
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
		if (!class_exists('\ClassRegistry'))
		{
            return;
        }

        if (!$listingModel = \ClassRegistry::getClass('EverywhereComContentModel'))
        {
            return;
        }

        if (!$listing = $listingModel->getListingById($id))
        {
            return;
        }

        return isset($listing['Category']['cat_id']) ? $listing['Category']['cat_id'] : null;
    }
}