<?php
namespace Google;
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
 * Extension to the regular Google\Model that automatically
 * exposes the items array for iteration, so you can just
 * iterate over the object rather than a reference inside.
 */
class Collection extends Model implements \Iterator, \Countable
{
  protected $collection_key = 'items';
  public $orderBys;
  public $dateRanges;
  public $metrics;
  public $dimensions;

  public function rewind(): void
  {
    if (isset($this->{$this->collection_key})
        && is_array($this->{$this->collection_key})) {
      reset($this->{$this->collection_key});
    }
  }

  // : mixed as of PHP 8.0 for 9.0
  #[\ReturnTypeWillChange]
  public function current()
  {
    $this->coerceType($this->key());
    if (is_array($this->{$this->collection_key})) {
      return current($this->{$this->collection_key});
    }
  }

  // : mixed as of PHP 8.0 for 9.0
  #[\ReturnTypeWillChange]
  public function key()
  {
    if (isset($this->{$this->collection_key}) && is_array($this->{$this->collection_key})) {
      return key($this->{$this->collection_key});
    }
  }

  public function next(): void
  {
    next($this->{$this->collection_key});
  }

  public function valid(): bool
  {
    $key = $this->key();
    return $key !== null && $key !== false;
  }

  public function count(): int
  {
    if (!isset($this->{$this->collection_key})) {
      return 0;
    }
    return count($this->{$this->collection_key});
  }

  public function offsetExists($offset): bool
  {
    if (!is_numeric($offset)) {
      return parent::offsetExists($offset);
    }
    return isset($this->{$this->collection_key}[$offset]);
  }

  public function offsetGet($offset)
  {
    if (!is_numeric($offset)) {
      return parent::offsetGet($offset);
    }
    $this->coerceType($offset);
    return $this->{$this->collection_key}[$offset];
  }

  public function offsetSet($offset, $value): void
  {
    if (!is_numeric($offset)) {
      parent::offsetSet($offset, $value);
      return;
    }
    $this->{$this->collection_key}[$offset] = $value;
  }

  public function offsetUnset($offset): void
  {
    if (!is_numeric($offset)) {
      parent::offsetUnset($offset);
      return;
    }
    unset($this->{$this->collection_key}[$offset]);
  }

  private function coerceType($offset)
  {
    $keyType = $this->keyType($this->collection_key);
    if ($keyType && !is_object($this->{$this->collection_key}[$offset])) {
      $this->{$this->collection_key}[$offset] =
          new $keyType($this->{$this->collection_key}[$offset]);
    }
  }
}
