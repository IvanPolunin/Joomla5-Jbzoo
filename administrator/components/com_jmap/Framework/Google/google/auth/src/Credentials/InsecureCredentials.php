<?php
namespace Google\Auth\Credentials;
/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

use Google\Auth\FetchAuthTokenInterface;

/**
 * Provides a set of credentials that will always return an empty access token.
 * This is useful for APIs which do not require authentication, for local
 * service emulators, and for testing.
 */
class InsecureCredentials implements FetchAuthTokenInterface
{
    /**
     * @var array
     */
    private $token = [
        'access_token' => ''
    ];

    /**
     * Fetches the auth token. In this case it returns an empty string.
     *
     * @param callable $httpHandler
     * @return array A set of auth related metadata, containing the following
     * keys:
     *   - access_token (string)
     */
    public function fetchAuthToken(?callable $httpHandler = null)
    {
        return $this->token;
    }

    /**
     * Returns the cache key. In this case it returns a null value, disabling
     * caching.
     *
     * @return string|null
     */
    public function getCacheKey()
    {
        return null;
    }

    /**
     * Fetches the last received token. In this case, it returns the same empty string
     * auth token.
     *
     * @return array
     */
    public function getLastReceivedToken()
    {
        return $this->token;
    }
}
