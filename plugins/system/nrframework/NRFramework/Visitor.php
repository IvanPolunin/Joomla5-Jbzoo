<?php 

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

class Visitor
{
	/**
	 * The name of the cookie used to identify that a visitor is persistent.
	 * 
	 * @var  string
	 */
	private $persistent_cookie_name = 'tvp';

	/**
	 * Represents the maximum age of the visitor's persistent cookie in seconds.
	 * 
	 * Default value set to 1 year.
	 *
	 * @var  int
	 */
	private $persistent_cookie_expire = 31536000;

	/**
	 * The name of the cookie used to identify that a visitor is new.
	 * 
	 * @var  string
	 */
	private $session_cookie_name = 'tvs';
	
	/**
	 * Represents the maximum age of the visitor's session cookie in seconds.
	 *
	 * Default value set to 20 minutes.
	 * 
	 * @var  int
	 */
	private $session_cookie_expire = 1200;

	/**
	 * The Cookies instance.
	 * 
	 * @var  object
	 */
	private $cookies;
	
	public function __construct()
	{
        $this->cookies = Factory::getApplication()->input->cookie;
	}

	/**
	 * Creates or updates cookies of the visitor.
	 * 
	 * - It will only create & update the tvs (visitor session cookie) when the user is considered new.
	 * - It will always update the tvp (visitor persistent cookie).
	 * 
	 * @return  void
	 */
	public function createOrUpdateCookie()
	{
		if ($this->isNew())
		{
			// Update the session cookie
			$this->cookies->set($this->session_cookie_name, 1, time() + $this->session_cookie_expire, '/', '', true);
		}

		// Update the persistent cookie
		$this->cookies->set($this->persistent_cookie_name, 1, time() + $this->persistent_cookie_expire, '/', '', true);
	}

	/**
	 * Checks whether the user is considered new.
	 * 
	 * A user is considered new when the following criteria are met:
	 * 
	 * - visitor persistent and session cookies are not met
	 * OR
	 * - visitor session cookie is set
	 * 
	 * @return  bool
	 */
	public function isNew()
	{
		$tvp = $this->cookies->get($this->persistent_cookie_name);
		$tvs = $this->cookies->get($this->session_cookie_name);

		return (!$tvp && !$tvs) || $tvs;
	}
}