<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Parser;

defined('_JEXEC') or die;

/**
 *  RingBuffer
 * 
 *  A circular buffer of fixed length.
 *  This class essentially implements a fixed-size FIFO stack but with 
 *  "convenient" accessor methods compared to manually handling a vanilla PHP array.
 * 
 *  Used by NRFramework\Parser\Parser and NRFramework\Parser\Lexer.
 */
class RingBuffer implements \Countable, \ArrayAccess, \Iterator
{
    /**
     *  Iterator position
     *  @var int
     */
    protected $iterator_position = 0;

    /**
     *  Position of the next element
     *  @var integer
     */
    protected $position = 0;

    /**
     *  Contents buffer
     *  @var \SplFixedArray
     */
    protected $buffer;

    /**
     *  Size of the ring buffer
     *  @var int
     */
    protected $size;

    /**
     *  RingBuffer constructor
     * 
     *  Handles arguments through 'func_get_args' (gotta love PHP)
     *
     *  @param int   $size  Size of the ring buffer
     *  @param array $val   Initial values
     */
    public function __construct()
    {
        //argument checks
        $argv = func_get_args();
	    $argc = count($argv);
		
	    switch($argc)
	    {
            case 1:
                // array
                if (is_array($argv[0]))
                {
                    $this ->size  = count($argv[0]);
                    $this->buffer = \SplFixedArray::fromArray($argv[0]);
                }
                // size
                else if (is_numeric($argv[0]))
                {
                    if ($argv[0] < 1)
                    {
                        throw new \InvalidArgumentException('RingBuffer ctor: size must be greater than zero');
                    }
                    $size = (integer)$argv[0];
                    $this->buffer = \SplFixedArray::fromArray(array_fill(0, $size, null));
                    $this->size	= $size;
                }
                else
                {
                    throw new \InvalidArgumentException("RingBuffer ctor: arguments must be an array ,a numeric size or both");
                }
                break;
            case 2:
                if(is_array($argv[0]) && is_numeric($argv[1]))
                {
                    if ($argv[1] < 1)
                    {
                        throw new \InvalidArgumentException('RingBuffer ctor: size must be greater than zero');
                    }
                    $arr_size = count($argv[0]);
                    $size     = (integer)$argv[1];
                    if ($arr_size == $size)
                    {
                        $this->buffer = \SplFixedArray::fromArray($argv[0]);
                        $this->size   = $size;
                    }
                    else if ($arr_size > $size)
                    {
                        $this->buffer = \SplFixedArray::fromArray(array_slice($argv[0], 0, $size));
                        $this->size	  = $size ;
                    }
                    else // $arr_size  <  $size
                    {
                        $this->buffer   = \SplFixedArray::fromArray(array_merge($argv[0], array_fill(0, $size - $arr_size, null)));
                        $this->size     = $size ;
                        $this->position	= $arr_size ;
                    }
                }
                else
                {
                    throw new \InvalidArgumentException("RingBuffer ctor: arguments must be an array ,a numeric size or both");
                }
                break;
            default:
                throw new \InvalidArgumentException('RingBuffer ctor: no arguments given');
      }
    }

    /**
     *  Returns the internal buffer as an array
     *
     *  @return \SplFixedArray
     */
    public function buffer()
    {
        return $this->buffer;
    }

    /**
     *  'Countable' interface methods
     */

    /**
     *  Returns the size of the buffer
     *
     *  @return int
     */
    public function count() : int
    {
        return $this->size;
    }

    /**
     *  'ArrayAccess' interface methods
     */
    
    protected function offsetOf($offset)
    {
        return ($this->position + $offset) % $this->size;
    }

	
	public function offsetExists($offset) : bool
	{
        return ($offset >= 0) && ($offset < $this->size);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
	{
        // if (!$this->offsetExists($offset))
        if (($offset < 1) && ($offset >= $this->size))
		{
		    throw new \OutOfBoundsException("RingBuffer: invalid offset $offset.");
        }
        return $this->buffer[($this->position + $offset) % $this->size];
    }	
	
	public function offsetUnset($offset) : void
	{
        if (!$this->offsetExists($offset))
		{
		    throw new \OutOfBoundsException("RingBuffer: invalid offset $offset.");
        }
        $this->buffer[$this->offsetOf($offset)] = null;
    }	
	
	public function offsetSet($offset, $value) : void
	{
	    if ($offset === null)
	    {
		    $this->buffer[$this->position] = $value;
			$this->position = ($this->position + 1)%$this->size;
		}
        else if ($this->offsetExists($offset))
        {
            $this->buffer[$this->offsetOf($offset)] = $value;
        }			
        else
        {
            throw new \OutOfBoundsException("RingBuffer: invalid offset $offset.");
        }
	}
	
    /**
     *  'Iterator' interface methods
     */
    public function rewind() : void
    {
        $this->iterator_position = 0;
    }

    public function current() : mixed
    {
        return $this->buffer[$this->offsetOf($this->iterator_position)];
    }

    public function key() : mixed
    {
        return $this->iterator_position;
    }

    public function next() : void
    {
        $this->iterator_position++;
    }

    public function valid() : bool
    {
        return ($this->iterator_position >= 0) && ($this->iterator_position < $this->size);
    }
}
