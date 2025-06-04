<?php
/**
 * @package         Modules Anywhere
 * @version         8.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2024 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\LayoutHelper as JLayout;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Input as RL_Input;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

defined('_JEXEC') or die;

RL_Document::style('regularlabs.admin-form');

$listOrder = RL_String::escape($this->state->get('list.ordering'));
$listDirn  = RL_String::escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');

$editor_name = RL_Input::getString('editor', 'text');
// Remove any dangerous character to prevent cross site scripting
$editor_name = RL_RegEx::replace('[\'\";\s]', '', $editor_name);

$cols = 5;
?>

<div class="container-fluid container-main">
    <form action="index.php" id="adminForm" name="adminForm" method="post"
          class="rl-form labels-sm">

        <div class="alert alert-info">
            <?php echo RL_String::html_entity_decoder(JText::_(
                'MA_CLICK_ON_ONE_OF_THE_MODULES_LINKS,'
                . '<span class="rl-code">{module id="..."}</span>&comma; '
                . '<span class="rl-code">{module title="..."}</span> '
                . strtolower(JText::_('JOR'))
                . ' <span class="rl-code">{modulepos position="..."}</span>'
            )); ?>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->form->renderFieldset('attributes'); ?>
            </div>
        </div>
        <?php
        // Search tools bar
        echo JLayout::render('joomla.searchtools.default', ['view' => $this]);
        ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="w-1 text-nowrap text-center">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="">
                        <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-3 text-nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'a.position', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-3 text-nowrap d-none d-md-table-cell">
                        <?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'a.module', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-1 text-nowrap text-center d-none d-md-table-cell">
                        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($this->items)): ?>
                    <tr>
                        <td colspan="<?php echo $cols; ?>">
                            <?php echo JText::_('RL_NO_ITEMS_FOUND'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <?php
                        $id    = $item->id;
                        $title = htmlspecialchars($item->title);

                        if ($this->params->add_title_to_id)
                        {
                            $id .= '#' . $title;
                        }
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center text-nowrap">
                                <button
                                    onclick="RegularLabs.ModulesAnywherePopup.insert('id', <?php echo (int) $item->id; ?>);"
                                    type="button" class="btn btn-secondary btn-sm text-left">
                                    <span class="fa fa-file-code me-1" aria-hidden="true"></span>
                                    <?php echo (int) $item->id; ?>
                                </button>
                            </td>
                            <td>
                                <button
                                    onclick="RegularLabs.ModulesAnywherePopup.insert('title', '<?php echo RL_String::escape($item->title); ?>');"
                                    type="button" class="btn btn-secondary btn-sm text-left">
                                    <span class="fa fa-file-code me-1" aria-hidden="true"></span>
                                    <?php echo RL_String::escape($item->title); ?>
                                </button>
                                <?php if ( ! empty($item->note)) : ?>
                                    <p class="small">
                                        <?php echo JText::sprintf('JGLOBAL_LIST_NOTE', htmlspecialchars($item->note)); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if ($item->position) : ?>
                                    <button
                                        onclick="RegularLabs.ModulesAnywherePopup.insert('position', '<?php echo RL_String::escape($item->position); ?>');"
                                        type="button" class="btn btn-secondary btn-sm text-left">
                                        <span class="fa fa-file-code me-1" aria-hidden="true"></span>
                                        <?php echo RL_String::escape($item->position); ?>
                                    </button>
                                <?php else : ?>
                                    <span class="small text-muted">
                                        <?php echo JText::_('JNONE'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap d-none d-md-table-cell small">
                                <?php echo $item->name ?: JText::_('JNONE'); ?>
                            </td>
                            <td class="text-center d-none d-md-table-cell">
                                <?php
                                echo $item->published
                                    ? '<span class="icon-publish text-success" aria-hidden="true"></span><div role="tooltip">' . JText::_('JPUBLISHED') . '</div>'
                                    : '<span class="icon-unpublish text-muted" aria-hidden="true"></span><div role="tooltip">' . JText::_('JUNPUBLISHED') . '</div>';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php echo $this->pagination->getListFooter(); ?>

        <input type="hidden" name="rl_qp" value="1">
        <input type="hidden" name="tmpl" value="component">
        <input type="hidden" name="class" value="Plugin.EditorButton.ModulesAnywhere.Popup">
        <input type="hidden" name="editor" value="<?php echo $editor_name; ?>">
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
    </form>
</div>
