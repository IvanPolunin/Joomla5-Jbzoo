<?php

namespace YOOtheme\Framework\User;

use YOOtheme\Framework\Event\EventSubscriberInterface;
use YOOtheme\Framework\Routing\Exception\HttpException;

class AccessListener implements EventSubscriberInterface
{
    public function onRequest($event, $app)
    {
        $access = (array) $event['request']->attributes->get('access');

        foreach ($access as $permission) {
            if (!$app['user']->hasPermission($permission)) {
                throw new HttpException(403, 'Insufficient User Rights.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'request' => ['onRequest', -10],
        ];
    }
}
