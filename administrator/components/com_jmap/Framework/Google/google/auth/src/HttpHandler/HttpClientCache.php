<?php
namespace Google\Auth\HttpHandler;
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

use GuzzleHttp\ClientInterface;

/**
 * Stores an HTTP Client in order to prevent multiple instantiations.
 */
class HttpClientCache
{
    /**
     * @var ClientInterface|null
     */
    private static $httpClient;

    /**
     * Cache an HTTP Client for later calls.
     *
     * Passing null will unset the cached client.
     *
     * @param ClientInterface|null $client
     * @return void
     */
    public static function setHttpClient(?ClientInterface $client = null)
    {
        self::$httpClient = $client;
    }

    /**
     * Get the stored HTTP Client, or null.
     *
     * @return ClientInterface|null
     */
    public static function getHttpClient()
    {
        return self::$httpClient;
    }
}
