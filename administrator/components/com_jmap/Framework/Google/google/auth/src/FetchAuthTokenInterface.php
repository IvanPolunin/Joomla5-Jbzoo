<?php
namespace Google\Auth;
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

/**
 * An interface implemented by objects that can fetch auth tokens.
 */
interface FetchAuthTokenInterface
{
    /**
     * Fetches the auth tokens based on the current state.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return array a hash of auth tokens
     */
    public function fetchAuthToken(?callable $httpHandler = null);

    /**
     * Obtains a key that can used to cache the results of #fetchAuthToken.
     *
     * If the value is empty, the auth token is not cached.
     *
     * @return string a key that may be used to cache the auth token.
     */
    public function getCacheKey();

    /**
     * Returns an associative array with the token and
     * expiration time.
     *
     * @return null|array {
     *      The last received access token.
     *
     * @var string $access_token The access token string.
     * @var int $expires_at The time the token expires as a UNIX timestamp.
     * }
     */
    public function getLastReceivedToken();
}
