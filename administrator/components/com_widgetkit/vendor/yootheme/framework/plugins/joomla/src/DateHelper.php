<?php

namespace YOOtheme\Framework\Joomla;

use Joomla\CMS\Date\Date;

class DateHelper
{
    /**
     * @var array
     */
    protected $formats = [
        'full' => 'l, F d, y',
        'long' => 'F d, y',
        'medium' => 'M d, Y',
        'short' => 'n/d/y',
    ];

    /**
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param array $formats
     */
    public function setFormats(array $formats)
    {
        $this->formats = array_replace($this->formats, $formats);
    }

    /**
     * Formats a time/date.
     *
     * @param  mixed  $date
     * @param  string $format
     * @return string
     */
    public function format($date, $format = 'medium')
    {
        $date = new Date($date);

        if (isset($this->formats[$format])) {
            $format = $this->formats[$format];
        }

        return $date->format($format);
    }
}
