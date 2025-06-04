<?php

$output = $widgetkit->renderWidget(json_decode($params->get('widgetkit', '[]'), true));
echo $output === false ? $widgetkit['translator']->trans('Could not load widget') : $output;
