<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo;

use function YOOtheme\app;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;
use YOOtheme\Zoo;

class SourceController
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function items(Request $request, Response $response)
    {
        /**
         * @var Zoo $zoo
         */
        $zoo = app(Zoo::class);

        $titles = [];

        $items = $zoo->table->item->getByIds($request('ids'));

        foreach ($items as $item) {
            $titles[$item->id] = $item->name;
        }

        return $response->withJson((object) $titles);
    }
}
