<?php

namespace YOOtheme\Framework\Joomla;

use Joomla\CMS\Factory;
use YOOtheme\Framework\Csrf\DefaultCsrfProvider;

class CsrfProvider extends DefaultCsrfProvider
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        return Factory::getSession()->getToken();
    }
}
