<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Post extends SmartTag
{
	/**
     * This is a Pro-only feature
     *
     * @var boolean
     */
    public $proOnly = true;

	/**
	 * Returns the value of a post data as found in the $_POST superglobal array. For example, if you submit a form that consists of the “email” and “name” input fields, you can use {post.email} and {post.name} Smart Tags in the submitted URL to retrieve the value of any form input.
	 * 
	 * @param   string  $key
	 * 
	 * @return  string
	 */
	public function fetchValue($key)
	{
		$filter = $this->parsedOptions->get('filter', 'STRING');
		$default_value = $this->parsedOptions->get('default', '');
		
		return $this->app->input->post->get($key, $default_value, $filter);
	}
}