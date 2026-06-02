<?php
use Joomla\CMS\Language\Text;
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

// get render
$view    = $this->getView();
$render  = $view->renderer;
$tooltip = '';

// load assets
$this->app->jbassets->initTooltip();
$this->app->jbassets->compare();

// render table cells items
$renderedItems = $render->renderFields($view->itemType, $view->appId, $vars['objects']);
$elementList   = $render->getElementList($renderedItems);

// render top compare links
$horizontalUrl = $this->app->jbrouter->compare($view->itemId, 'h', $view->itemType, $view->appId);
$verticalUrl   = $this->app->jbrouter->compare($view->itemId, 'v', $view->itemType, $view->appId);
$clearUrl      = $this->app->jbrouter->compareClear($view->itemId, $view->itemType, $view->appId);
$bootstrap    = $this->app->jbbootstrap;

$html = array();

// Bootstrap 5 navigation tabs
$html[] = '<ul class="nav nav-tabs mb-3" id="compareTabs" role="tablist">';
if ($view->layoutType == 'h') {
    $html[] = '<li class="nav-item" role="presentation">';
    $html[] = '<button class="nav-link" id="vertical-tab" data-bs-toggle="tab" data-bs-target="#vertical" type="button" role="tab" aria-controls="vertical" aria-selected="false">' . Text::_('JBZOO_COMPARE_VERTICAL') . '</button>';
    $html[] = '</li>';
    $html[] = '<li class="nav-item" role="presentation">';
    $html[] = '<button class="nav-link active" id="horizontal-tab" data-bs-toggle="tab" data-bs-target="#horizontal" type="button" role="tab" aria-controls="horizontal" aria-selected="true">' . Text::_('JBZOO_COMPARE_HORIZONTAL') . '</button>';
    $html[] = '</li>';
} else {
    $html[] = '<li class="nav-item" role="presentation">';
    $html[] = '<button class="nav-link active" id="vertical-tab" data-bs-toggle="tab" data-bs-target="#vertical" type="button" role="tab" aria-controls="vertical" aria-selected="true">' . Text::_('JBZOO_COMPARE_VERTICAL') . '</button>';
    $html[] = '</li>';
    $html[] = '<li class="nav-item" role="presentation">';
    $html[] = '<button class="nav-link" id="horizontal-tab" data-bs-toggle="tab" data-bs-target="#horizontal" type="button" role="tab" aria-controls="horizontal" aria-selected="false">' . Text::_('JBZOO_COMPARE_HORIZONTAL') . '</button>';
    $html[] = '</li>';
}
$html[] = '</ul>';

// Tab content
$html[] = '<div class="tab-content" id="compareTabContent">';

// Vertical tab content
$html[] = '<div class="tab-pane fade' . ($view->layoutType == 'v' ? ' show active' : '') . '" id="vertical" role="tabpanel" aria-labelledby="vertical-tab">';
$html[] = '<!-- Debug: renderedItems count = ' . count($renderedItems) . ' -->';
$html[] = '<!-- Debug: layoutType = ' . $view->layoutType . ' -->';
if (count($renderedItems) > 0) {
    $html[] = '<div class="table-responsive">';
    $html[] = '<table class="table table-hover jsCompareTable">';
    $html[] = '<thead><tr><th>&nbsp;</th>';
    $colWidth = 'width' . intval(100 / (count($renderedItems) + 1));

    foreach ($renderedItems as $itemId => $itemHtml) {
        $link   = $this->app->route->item($vars['objects'][$itemId]);
        $title  = $itemHtml['itemname'];
        $html[] = '<th class="' . $colWidth . '"><a href="' . $link . '" title="' . $title . '">' . $title . '</a></th>';
    }
    $html[] = '</tr></thead><tbody>';

    foreach ($elementList as $elementId) {
        if ($elementId != 'itemname') {
            $label       = $render->renderElementLabel($elementId, $view->itemType, $view->appId);
            $element     = $this->app->jbentity->getElement($elementId, $view->itemType, $view->appId);
            $tooltipText = $this->app->jbstring->clean($element->config->get('description'));
            $tooltip     = $tooltipText ? ' <span class="jbtooltip" title="' . $tooltipText . '"></span>&nbsp;&nbsp;' : '';

            $html[] = '<tr><th>' . $tooltip . $label . '</th>';
            foreach ($renderedItems as $itemId => $itemElements) {
                $html[] = '<td class="' . $colWidth . '">' . $itemElements[$elementId] . '</td>';
            }
            $html[] = '</tr>';
        }
    }

    $html[] = '</tbody></table>';
    $html[] = '</div>';
} else {
    $html[] = '<div class="alert alert-info">' . Text::_('JBZOO_COMPARE_ITEMS_NOT_FOUND') . '</div>';
}
$html[] = '</div>';

// Horizontal tab content
$html[] = '<div class="tab-pane fade' . ($view->layoutType == 'h' ? ' show active' : '') . '" id="horizontal" role="tabpanel" aria-labelledby="horizontal-tab">';
if (count($renderedItems) > 0) {
    $html[] = '<div class="table-responsive">';
    $html[] = '<table class="table table-hover jsCompareTable">';
    $html[] = '<thead><tr><th>&nbsp;</th>';
    foreach ($elementList as $elementId) {
        $element = $this->app->jbentity->getElement($elementId, $view->itemType, $view->appId);
        if ($element) {
            $tooltipText = $this->app->jbstring->clean($element->config->get('description'));
            $tooltip     = $tooltipText ? ' <span class="jbtooltip" title="' . $tooltipText . '"></span>' : '';
        }

        if ($elementId != 'itemname') {
            $html[] = '<th>' . $render->renderElementLabel($elementId, $view->itemType, $view->appId) . $tooltip . '</th>';
        }
    }
    $html[] = '</tr></thead><tbody>';

    // body
    foreach ($renderedItems as $itemId => $itemElements) {
        $html[] = '<tr>';
        foreach ($itemElements as $elementId => $elementHtml) {
            if ($elementId == 'itemname') {
                $link   = $this->app->route->item($vars['objects'][$itemId]);
                $html[] = '<th><a href="' . $link . '">' . $elementHtml . '</a></th>';
            } else {
                $html[] = '<td class="jbcompare-cell-' . $elementId . '" data-elementid="' . $elementId . '">' . $elementHtml . '</td>';
            }
        }
        $html[] = '</tr>';
    }

    $html[] = '</tbody></table>';
    $html[] = '</div>';
} else {
    $html[] = '<div class="alert alert-info">' . Text::_('JBZOO_COMPARE_ITEMS_NOT_FOUND') . '</div>';
}
$html[] = '</div>';

$html[] = '</div>'; // close tab-content

// Clear button
$html[] = '<div class="mt-3">';
$html[] = '<a href="' . $clearUrl . '" class="btn btn-danger jbcompare-clear">';
$html[] = '<i class="bi bi-trash"></i> ' . Text::_('JBZOO_COMPARE_REMOVEALL');
$html[] = '</a>';
$html[] = '</div>';

echo implode(PHP_EOL, $html);

// Initialize tabs without Bootstrap dependency
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabButtons = document.querySelectorAll("#compareTabs button");
    const tabPanes = document.querySelectorAll("#compareTabContent .tab-pane");
    
    tabButtons.forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            
            // Remove active classes
            tabButtons.forEach(btn => {
                btn.classList.remove("active");
                btn.setAttribute("aria-selected", "false");
            });
            tabPanes.forEach(pane => {
                pane.classList.remove("show", "active");
            });
            
            // Add active classes to clicked button and its target
            this.classList.add("active");
            this.setAttribute("aria-selected", "true");
            
            const targetId = this.getAttribute("data-bs-target");
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add("show", "active");
            }
        });
    });
    
    // Show first tab by default
    const firstActiveTab = document.querySelector("#compareTabs .nav-link.active");
    if (firstActiveTab) {
        const targetId = firstActiveTab.getAttribute("data-bs-target");
        const targetPane = document.querySelector(targetId);
        if (targetPane) {
            targetPane.classList.add("show", "active");
        }
    }
});
</script>';

?>
