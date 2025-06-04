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

use Google\Auth\Credentials\GCECredentials;
use Psr\Cache\CacheItemPoolInterface;

/**
 * A class to implement caching for calls to GCECredentials::onGce. This class
 * is used automatically when you pass a `Psr\Cache\CacheItemPoolInterface`
 * cache object to `ApplicationDefaultCredentials::getCredentials`.
 *
 * ```
 * $sysvCache = new Google\Auth\SysvCacheItemPool();
 * $creds = Google\Auth\ApplicationDefaultCredentials::getCredentials(
 *     $scope,
 *     null,
 *     null,
 *     $sysvCache
 * );
 * ```
 */
class GCECache
{
    const GCE_CACHE_KEY = 'google_auth_on_gce_cache';

    use CacheTrait;

    /**
     * @var array
     */
    private $cacheConfig;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param array $cacheConfig Configuration for the cache
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(
        ?array $cacheConfig = null,
        ?CacheItemPoolInterface $cache = null
    ) {
        $this->cache = $cache;
        $this->cacheConfig = array_merge([
            'lifetime' => 1500,
            'prefix' => '',
        ], (array) $cacheConfig);
    }

    /**
     * Caches the result of onGce so the metadata server is not called multiple
     * times.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     * @return bool True if this a GCEInstance, false otherwise
     */
    public function onGce(?callable $httpHandler = null)
    {
        if (is_null($this->cache)) {
            return GCECredentials::onGce($httpHandler);
        }

        $cacheKey = self::GCE_CACHE_KEY;
        $onGce = $this->getCachedValue($cacheKey);

        if (is_null($onGce)) {
            $onGce = GCECredentials::onGce($httpHandler);
            $this->setCachedValue($cacheKey, $onGce);
        }

        return $onGce;
    }
}
