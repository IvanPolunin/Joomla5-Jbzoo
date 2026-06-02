<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$this->app->jbdebug->mark('template::category::start');

$this->app->jblayout->setView($this);
$currentView = $this->app->jbrequest->get('view', 'category');
$currentTask = $this->app->jbrequest->get('task', 'category');
$page = $this->app->request->getInt('page', 0);
// jimport('joomla.application.module.helper');
$module = JModuleHelper::getModules('catalogmenu');
$attribs['style'] = 'html5';

if (isset($this->category)) {
    if ($currentView == 'frontpage' || $currentTask == 'frontpage') {
        $category = $this->application;
    } else {
        $category = $this->category;
    }
}

if (!$this->app->jbcache->start($this->params->get('config.lastmodified'))) {
    $this->app->jbwrapper->start();

    // category render
    //if (isset($category)) {
    //    echo $this->app->jblayout->render($currentView, $category);
    //}

	$page = $this->app->request->getInt('page', 1);

	$h1 = $category->name;

	if($page > 1) {

		$h1 .= ' ' . JText::_('COM_ZOO_PAGE_WORD') . ' ' . $page;
	}

   echo '<div class="category">';
   echo '<h1 class="title">'.$h1.'</h1>';
   if ($this->app->request->getCmd('Itemid') == '106' || $this->app->request->getCmd('Itemid') == '169') {
      echo JModuleHelper::renderModule($module[0], $attribs);
   }
   if ($page == 0 || $page == 1) {
//echo '<div class="description-full">'.$category->description.'</div>';
        //$str = $category->description;
       // $str = strip_tags($str);
        //if (strlen($str) > 500) {
	 //  $textPrev = substr($str, 0, 500);
	  // $textPrev = rtrim($textPrev, "!,.-");
	   //$textPrev = substr($textPrev, 0, strrpos($textPrev, ' '));
	   //$textNext = substr($str, strlen($textPrev));
	   //echo '<div class="description-full">';
           //echo '<span class="text-prev">'.$textPrev.'</span><span class="text-next">'.$textNext.'</span>';
	   //echo '<a href="#" class="text-more">.���������</a>';
           //echo '</div>';
	//} else {
	//  echo '<div class="description-full">'.$category->description.'</div>';
	//}
        
   }
   echo '</div>';

    // alphaindex render
    if ($this->params->get('template.show_alpha_index', 0)) {
        echo $this->app->jblayout->render('alphaindex', $this->alpha_index);
    }

    // subcategories render
    //if (isset($category)) {
    //    $categories = $this->category->getChildren();
    //    if ($this->params->get('template.subcategory_show', 1) && count($categories)) {
    //        echo $this->app->jblayout->render('subcategories', $categories);
    //    }
    //}

    // category items render
    if ($this->params->get('config.items_show', 1) && count($this->items)) {

        if (isset($category) && $this->params->get('config.show_feed_link', 1) && $currentView == 'category') {
            $link = $this->params->get('config.alternate_feed_link');
            if (!$link && isset($category->application_id)) {
                $link = $this->app->route->feed($category, 'rss');
                $link = JRoute::_($link);

                echo '<a class="rsslink" target="_blank" href="' . $link . '" title="' . JText::_('RSS feed') . '">' .
                    JText::_('RSS feed') . '</a>';

                echo JBZOO_CLR;
            }
        }

        echo $this->app->jblayout->render('items', $this->items);

    } else {
        echo $this->app->jblayout->render('items_empty', $category);
    }

    // pagination render
    if ($this->params->get('template.item_pagination', 1)) {
        echo $this->app->jblayout->render('pagination', $this->pagination, array('link' => $this->pagination_link));
    }
 

    $this->app->jbwrapper->end();
    $this->app->jbcache->stop();

    
    if ($page == 0 || $page == 1) {
        echo '<div class="description-full">'.$category->description.'</div>';
               
           }

}

$this->app->jbdebug->mark('template::category::finish');
