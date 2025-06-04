<?php
namespace Google\Auth\Cache;
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

use Psr\Cache\CacheItemInterface;

/**
 * A cache item.
 */
final class Item implements CacheItemInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var \DateTime|null
     */
    private $expiration;

    /**
     * @var bool
     */
    private $isHit = false;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->isHit() ? $this->value : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        if (!$this->isHit) {
            return false;
        }

        if ($this->expiration === null) {
            return true;
        }

        return $this->currentTime()->getTimestamp() < $this->expiration->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        $this->isHit = true;
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if ($this->isValidExpiration($expiration)) {
            $this->expiration = $expiration;

            return $this;
        }

        $implementationMessage = interface_exists('DateTimeInterface')
            ? 'implement interface DateTimeInterface'
            : 'be an instance of DateTime';

        $error = sprintf(
            'Argument 1 passed to %s::expiresAt() must %s, %s given',
            get_class($this),
            $implementationMessage,
            gettype($expiration)
        );

        $this->handleError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if (is_int($time)) {
            $this->expiration = $this->currentTime()->add(new \DateInterval("PT{$time}S"));
        } elseif ($time instanceof \DateInterval) {
            $this->expiration = $this->currentTime()->add($time);
        } elseif ($time === null) {
            $this->expiration = $time;
        } else {
            $message = 'Argument 1 passed to %s::expiresAfter() must be an ' .
                       'instance of DateInterval or of the type integer, %s given';
            $error = sprintf($message, get_class($this), gettype($time));

            $this->handleError($error);
        }

        return $this;
    }

    /**
     * Handles an error.
     *
     * @param string $error
     * @throws \TypeError
     */
    private function handleError($error)
    {
        if (class_exists('TypeError')) {
            throw new \TypeError($error);
        }

        trigger_error($error, E_USER_ERROR);
    }

    /**
     * Determines if an expiration is valid based on the rules defined by PSR6.
     *
     * @param mixed $expiration
     * @return bool
     */
    private function isValidExpiration($expiration)
    {
        if ($expiration === null) {
            return true;
        }

        // We test for two types here due to the fact the DateTimeInterface
        // was not introduced until PHP 5.5. Checking for the DateTime type as
        // well allows us to support 5.4.
        if ($expiration instanceof \DateTimeInterface) {
            return true;
        }

        if ($expiration instanceof \DateTime) {
            return true;
        }

        return false;
    }

    protected function currentTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
