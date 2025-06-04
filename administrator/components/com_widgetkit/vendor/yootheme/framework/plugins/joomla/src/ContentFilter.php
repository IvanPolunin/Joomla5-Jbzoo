<?php

namespace YOOtheme\Framework\Joomla;

use Joomla\CMS\HTML\Helpers\Content;
use YOOtheme\Framework\Filter\FilterInterface;

class ContentFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        return Content::prepare($value);
    }
}
