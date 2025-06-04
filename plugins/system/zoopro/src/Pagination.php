<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\PaginationObject;
use Joomla\CMS\Router\Route;

class Pagination extends \Joomla\CMS\Pagination\Pagination
{
    /**
     * @var \AppPagination
     */
    protected $pagination;

    /**
     * @var string
     */
    protected $url;

    public function __construct(\AppPagination $pagination, $url = 'index.php')
    {
        $this->url = $url;
        $this->pagination = $pagination;

        parent::__construct(
            $pagination->total(),
            $pagination->limit() * ($pagination->current() - 1),
            $pagination->limit()
        );
    }

    protected function _buildDataObject()
    {
        $pagination = $this->pagination;
        $url = $this->url;
        $name = $pagination->name();
        return (object) [
            'all' => new PaginationObject('', '', null, Route::_($url)),
            'start' => new PaginationObject('', '', '', Route::_($url)),
            'previous' => new PaginationObject(Text::_('JPREV'), '', $this->pagesCurrent > 1 ?: null, Route::_(
                $this->pagesCurrent - 1 == 1 ? $url : $pagination->link($url, "{$name}=" . ($this->pagesCurrent - 1))
            )),
            'pages' => array_combine(
                range($this->pagesStart, $this->pagesStop),
                array_map(
                    function ($page) use ($pagination, $url, $name) {
                        return new PaginationObject(Text::_($page), '', null, Route::_(
                            $pagination->link($this->url, "{$pagination->name()}=" . $page)
                        ), $this->pagesCurrent === $page);
                    },
                    range($this->pagesStart, $this->pagesStop)
                )
            ),
            'next' => new PaginationObject(Text::_('JNEXT'), '', $this->pagesCurrent < $this->pagesTotal ?: null, Route::_(
                $pagination->link($url, "{$name}=" . ($this->pagesCurrent + 1))
            )),
            'end' => new PaginationObject('', '', '', Route::_($pagination->link($url, "{$name}={$this->pagesTotal}"))),
        ];
    }
}
