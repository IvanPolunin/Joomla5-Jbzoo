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

trait CacheTrait
{
    private $maxKeyLength = 64;

    /**
     * Gets the cached value if it is present in the cache when that is
     * available.
     */
    private function getCachedValue($k)
    {
        if (is_null($this->cache)) {
            return;
        }

        $key = $this->getFullCacheKey($k);
        if (is_null($key)) {
            return;
        }

        $cacheItem = $this->cache->getItem($key);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }
    }

    /**
     * Saves the value in the cache when that is available.
     */
    private function setCachedValue($k, $v)
    {
        if (is_null($this->cache)) {
            return;
        }

        $key = $this->getFullCacheKey($k);
        if (is_null($key)) {
            return;
        }

        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($v);
        $cacheItem->expiresAfter($this->cacheConfig['lifetime']);
        return $this->cache->save($cacheItem);
    }

    private function getFullCacheKey($key)
    {
        if (is_null($key)) {
            return;
        }

        $key = $this->cacheConfig['prefix'] . $key;

        // ensure we do not have illegal characters
        $key = preg_replace('|[^a-zA-Z0-9_\.!]|', '', $key);

        // Hash keys if they exceed $maxKeyLength (defaults to 64)
        if ($this->maxKeyLength && strlen($key) > $this->maxKeyLength) {
            $key = substr(hash('sha256', $key), 0, $this->maxKeyLength);
        }

        return $key;
    }
}
