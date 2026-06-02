<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class JBCacheHelper
 */
class JBCacheHelper extends AppHelper
{
    /**
     * Stack of active output cache buffers.
     *
     * @var array<int, array<string, mixed>|null>
     */
    protected $_outputStack = array();

    /**
     * Check config, is enabled joomla caching
     * @return int
     */
    public function isEnabled()
    {
        $config = JFactory::getConfig();
        return (int)$config->get('caching', 0);
    }

    /**
     * Set data to cache storage by key
     * @param string $key
     * @param mixed  $data
     * @param string $group
     * @param bool   $isForce
     * @param array  $params
     * @return bool
     */
    public function set($key, $data, $group = 'default', $isForce = false, array $params = array())
    {
        $group = str_replace('-', '_', $group);
        $cache = JFactory::getCache('jbzoo_' . $group, 'output');
        $key   = $this->_simpleHash($key);
        if ($isForce) {
            $cache->setCaching(true);
        }

        if (isset($params['ttl']) && (int)$params['ttl'] > 0) {
            $cache->setLifeTime((int)$params['ttl']);
        }

        return $cache->store($data, $key);
    }

    /**
     * Get cache data by key
     * @param string $key
     * @param string $group
     * @param bool   $isForce
     * @param array  $params
     * @return null
     */
    public function get($key, $group = 'default', $isForce = false, array $params = array())
    {
        $group = str_replace('-', '_', $group);
        $cache = JFactory::getCache('jbzoo_' . $group, 'output');
        $key   = $this->_simpleHash($key);
        if ($isForce) {
            $cache->setCaching(true);
        }

        if (isset($params['ttl']) && (int)$params['ttl'] > 0) {
            $cache->setLifeTime((int)$params['ttl']);
        }

        return $cache->get($key);
    }

    /**
     * Clear cache
     * @param $group
     */
    public function clear($group)
    {
        $file = JPATH_SITE . '/cache/jbzoo/' . $group;
        if (JFile::exists($file)) {
            JFile::delete($file);
        }
    }

    /**
     * Create simple hash from var
     * @param mixed $var
     * @return string
     */
    protected function _simpleHash($var)
    {
        return md5(serialize($var)) . (int)JDEBUG;
    }

    /**
     * Create simple hash from var
     * @param mixed $var
     * @return string
     */
    public function hash($var)
    {
        return $this->_simpleHash($var);
    }

    /**
     * @deprecated
     */
    public function start($key = null, $group = 'default', $isForce = true, array $params = array())
    {
        $group = str_replace('-', '_', $group ?: 'default');

        $cacheKey = $this->_buildOutputCacheKey($key, $group, $params);
        $cached   = $this->get($cacheKey, $group, $isForce, $params);

        if ($cached !== false && $cached !== null) {
            $this->_outputStack[] = null;
            echo $cached;

            return true;
        }

        $this->_outputStack[] = array(
            'key'     => $cacheKey,
            'group'   => $group,
            'force'   => $isForce,
            'params'  => $params,
        );

        ob_start();

        return false;
    }

    /**
     * @deprecated
     */
    public function stop()
    {
        $state = array_pop($this->_outputStack);

        if ($state === null) {
            return null;
        }

        if (!is_array($state)) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            return null;
        }

        $output = ob_get_clean();
        $this->set($state['key'], $output, $state['group'], $state['force'], $state['params']);

        echo $output;

        return $output;
    }

    /**
     * Build a stable output-cache key for the current request context.
     *
     * @param mixed  $key
     * @param string $group
     * @param array  $params
     * @return string
     */
    protected function _buildOutputCacheKey($key, $group, array $params = array())
    {
        $user = JFactory::getUser();
        $uri  = class_exists('JUri') ? JUri::getInstance()->toString() : (isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '');

        return $this->_simpleHash(array(
            'group'      => $group,
            'key'        => $key,
            'params'     => $params,
            'uri'        => $uri,
            'user_id'    => (int)$user->id,
            'user_groups'=> method_exists($user, 'getAuthorisedGroups') ? $user->getAuthorisedGroups() : array(),
            'language'   => JFactory::getLanguage() ? JFactory::getLanguage()->getTag() : '',
            'debug'      => (int)JDEBUG,
        ));
    }

    /**
     * @param $cachePath
     * @param $hash
     * @return bool
     */
    public function checkAsset($cachePath, $hash)
    {
        if (JFile::exists($cachePath)) {

            $firstLine = $this->app->jbfile->firstLine($cachePath);
            if (preg_match('#' . $this->_simpleHash($hash) . '#i', $firstLine)) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param $cachePath
     * @param $data
     * @param $hash
     */
    public function saveAsset($cachePath, $data, $hash)
    {
        $data = '/* cacheid:' . $this->_simpleHash($hash) . ' */' . PHP_EOL . $data;

        $this->app->jbfile->save($cachePath, $data);
    }

    /**
     * @param string $origFull
     * @return string
     */
    public function getFileName($origFull)
    {
        $newPath = JPath::clean($origFull);
        $newPath = str_replace(JPATH_ROOT, '', $newPath);
        $newPath = str_replace(array('/', '\\', '.', ':'), '_', $newPath);
        $newPath = trim($newPath, '_');

        return $newPath;
    }
}
