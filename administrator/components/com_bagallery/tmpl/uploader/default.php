<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

if (JVERSION >= '3.4.0') {
    HTMLHelper::_('behavior.formvalidator');
} else {
    HTMLHelper::_('behavior.formvalidation');
}
$pagLimit = array(
    5 => 5,
    10 => 10,
    15 => 15,
    20 => 20,
    25 => 25,
    30 => 30,
    50 => 50,
    100 => 100,
    1 => Text::_('JALL'),
);
$galleryStateStr = GalleryHelper::checkGalleryActivation();
$galleryState = json_decode($galleryStateStr);
?>
<style type="text/css">
    .ba-context-menu:not(.visible-context-menu) {
        display: none;
    }
</style>
<link rel="stylesheet" type="text/css" href="components/com_bagallery/assets/css/ba-admin.css?<?php echo $this->version; ?>">
<script type="text/javascript">
    function makeDrag()
    {
        jQuery("tbody tr").draggable({
            cursor: 'move',
            cancel: null,
            helper: 'clone',
            revert: 'invalid',
            cursorAt: {
                left: 90,
                top: 20
            },
            handle : '.draggable-handler',
            start : function(){
                jQuery('.ba-folder-tree > ul ul').each(function(){
                    if (jQuery(this).closest('li').hasClass('visible-branch')) {
                        jQuery(this).find('> li > span').droppable('enable');
                    } else {
                        jQuery(this).find('> li > span').droppable('disable');
                    }
                })
            }
        }).disableSelection();
        jQuery(".ba-folder-tree li span[data-path], tbody tr:not(.ba-images)").droppable({
            greedy: true,
            hoverClass: "droppable-over",
            tolerance: 'pointer',
            drop: function(event, ui) {
                let str = ui.helper.find('.select-item').val(),
                    path = '',
                    obj = JSON.parse(str),
                    clone = ui.helper.clone();
                if (this.localName == 'tr') {
                    path = this.querySelector('span[data-path]').dataset.path;
                } else {
                    path = this.dataset.path;
                }
                clone.addClass('ba-dropping');
                setTimeout(function(){
                    clone.remove();
                }, 400);
                galleryApp.executeAction({
                    action: 'multipleMove',
                    path: path,
                    array: [obj.path]
                }).then(function(text){
                    top.showNotice(top.app._('SUCCESS_MOVED'));
                    galleryApp.getFoldersTree(text);
                    galleryApp.reloadFolder();
                });
                jQuery('tbody').append(clone);
            }
        });
    }
</script>
<div id="ba-media-manager">
    <form action="<?php echo Route::_('index.php?option=com_bagallery&layout=uploader&id=&tmpl=component'); ?>"
        method="post" autocomplete="off" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
        <div id="create-folder-modal" class="ba-modal-sm modal hide">
            <div class="modal-body">
                <h3><?php echo Text::_('CREATE_FOLDER'); ?></h3>
                <input type="text" maxlength="260" name="new-folder" placeholder="<?php echo Text::_('ENTER_FOLDER_NAME') ?>">
                <span class="focus-underline"></span>
                <input type="hidden" name="current-dir" value="<?php echo $this->_parent; ?>">
            </div>
            <div class="modal-footer">
                <a href="#" class="ba-btn" data-dismiss="modal">
                    <?php echo Text::_('CANCEL') ?>
                </a>
                <a href="#" class="ba-btn-primary" id="add-folder">
                    <?php echo Text::_('JTOOLBAR_APPLY') ?>
                </a>
            </div>
        </div>
        <div id="delete-modal" class="ba-modal-sm modal hide">
            <div class="modal-body">
                <h3><?php echo Text::_('DELETE_ITEM'); ?></h3>
                <p><?php echo Text::_('MODAL_DELETE'); ?></p>
            </div>
            <div class="modal-footer">
                <a href="#" class="ba-btn" data-dismiss="modal">
                    <?php echo Text::_('CANCEL') ?>
                </a>
                <a href="#" class="ba-btn-primary red-btn" id="apply-delete">
                    <?php echo Text::_('DELETE') ?>
                </a>
            </div>
        </div>
        <div id="move-to-modal" class="ba-modal-md modal hide">
            <div class="modal-body">
                <div class="ba-modal-header">
                    <h3><?php echo Text::_('MOVE_TO'); ?></h3>
                    <i data-dismiss="modal" class="zmdi zmdi-close"></i>
                </div>
                <div class="availible-folders">
                    <ul>
                        <li>
                            <span data-path="<?php echo GalleryHelper::$params->image_path; ?>">
                                <i class="zmdi zmdi-folder"></i>
                                <span><?php echo GalleryHelper::$params->image_path; ?></span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="ba-btn" data-dismiss="modal">
                    <?php echo Text::_('CANCEL') ?>
                </a>
                <a href="#" class="ba-btn-primary apply-move">
                    <?php echo Text::_('JTOOLBAR_APPLY') ?>
                </a>
            </div>
        </div>
        <div id="rename-modal" class="ba-modal-sm modal hide">
            <div class="modal-body">
                <h3><?php echo Text::_('RENAME'); ?></h3>
                <input type="text" maxlength="260" class="new-name">
                <span class="focus-underline"></span>
            </div>
            <div class="modal-footer">
                <a href="#" class="ba-btn" data-dismiss="modal">
                    <?php echo Text::_('CANCEL') ?>
                </a>
                <a href="#" class="ba-btn-primary" id="apply-rename">
                    <?php echo Text::_('JTOOLBAR_APPLY') ?>
                </a>
            </div>
        </div>
        <div class ="row-fluid">
            <div class="row-fluid ba-media-header">
                <div class="span12">
                    <i class="zmdi zmdi-fullscreen media-fullscrean"></i>
                    <i class="close-media zmdi zmdi-close"></i>
                </div>
                <div class="span12">
                    <div class="uploader-nav">
                        <div class="ba-breadcrumb">
<?php 
                        echo $this->uploader->getbreadcrumb();
?>
                        </div>
                        <div class="ba-media-manager-search-wrapper">
                            <input type="text" class="ba-media-manager-search-input"
                                placeholder="<?php echo Text::_('SEARCH'); ?>">
                            <i class="ba-media-manager-search-icon zmdi zmdi-search"></i>
                        </div>
                        <div class="control-toolbar">
                            <label class="media-manager-apply-wrapper">
                                <i class="zmdi zmdi-plus" id="ba-apply"></i>
                                <span class="ba-tooltip ba-top"><?php echo Text::_('INSERT_SELECTED_ITEMS'); ?></span>
                            </label>
                            <label>
                                <i class="zmdi zmdi-cloud-upload" id="show-upload"></i>
                                <span class="ba-tooltip ba-bottom"><?php echo Text::_('UPLOAD_IMAGE'); ?></span>
                            </label>
                            <label>
                                <i class="zmdi zmdi-folder" id="show-folder"></i>
                                <span class="ba-tooltip ba-bottom"><?php echo Text::_('CREATE_FOLDER'); ?></span>
                            </label>
                            <label>
                                <i class="zmdi zmdi-forward" id="move-to"></i>
                                <span class="ba-tooltip ba-bottom"><?php echo Text::_('MOVE_TO'); ?></span>
                            </label>
                            <label>
                                <i class="zmdi zmdi-delete" id="delete-items"></i>
                            </label>
                            <div class="pagination-limit">
                                <div class="ba-custom-select pagination-limit-select">
                                    <input readonly value="<?php echo $pagLimit[$this->_limit]; ?>"
                                       data-value="<?php echo $this->_limit; ?>"
                                       size="<?php echo strlen($this->_limit); ?>" type="text">
                                    <i class="zmdi zmdi-caret-down"></i>
                                    <ul>
<?php
                                    foreach ($pagLimit as $key => $title) {
?>
                                        <li data-value="<?php echo $key; ?>"
                                            class="<?php echo $key == $this->_limit ? 'selected' : ''; ?>">
<?php
                                        if ($key == $this->_limit) {
?>
                                            <i class="zmdi zmdi-check"></i>
<?php
                                        }
                                        echo $title;
?>
                                        </li>
<?php
                                    }
?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-fluid ba-media-manager">
                <div class="ba-folder-tree" style="width: 30%;">
<?php
                    echo $this->uploader->getFoldersTree();
?>
                </div>
                <div class="ba-work-area" style="width: 70%;">
                    <div class="table-head">
                        <div class="files-name">
                            <label>
                                <input type="checkbox" name="ba-rm[]" value="" id="check-all">
                                <i class="zmdi zmdi-check-circle check-all"></i>
                            </label>
                            <?php echo Text::_('NAME'); ?>
                        </div>
                        <div class="files-size">
                            <?php echo Text::_('FILE_SIZE'); ?>
                        </div>
                    </div>
                    <div class="table-body">
<?php
                        echo $this->uploader->getItemsTable();
?>
                    </div>
                    <div class="pagination">
<?php
                        echo $this->uploader->getPaginator();
?>
                    </div>
                </div>
            </div>
        </div>
        <div class="ba-context-menu empty-context-menu">
            <span class="upload-file ba-group-element"><i class="zmdi zmdi-cloud-upload"></i><?php echo Text::_('UPLOAD_IMAGE'); ?></span>
            <span class="create-folder"><i class="zmdi zmdi-folder"></i><?php echo Text::_('CREATE_FOLDER'); ?></span>
        </div>
        <div class="ba-context-menu files-context-menu">
<?php
        if ($this->about->tag == 'pro' && isset($galleryState->data)) {
?>
            <span class="edit-image"><i class="zmdi zmdi-camera-alt"></i><?php echo Text::_('PHOTO_EDITOR'); ?></span>
<?php
        }
?>
            <span class="rename"><i class="zmdi zmdi-edit"></i><?php echo Text::_('RENAME'); ?></span>
            <span class="move-to"><i class="zmdi zmdi-forward"></i><?php echo Text::_('MOVE_TO'); ?>...</span>
            <span class="download"><i class="zmdi zmdi-download"></i><?php echo Text::_('DOWNLOAD'); ?></span>
            <span class="delete ba-group-element"><i class="zmdi zmdi-delete"></i><?php echo Text::_('DELETE'); ?></span>
        </div>
        <div class="ba-context-menu folders-context-menu">
            <span class="rename"><i class="zmdi zmdi-edit"></i><?php echo Text::_('RENAME'); ?></span>
            <span class="move-to"><i class="zmdi zmdi-forward"></i><?php echo Text::_('MOVE_TO'); ?>...</span>
            <span class="delete ba-group-element"><i class="zmdi zmdi-delete"></i><?php echo Text::_('DELETE'); ?></span>
        </div>
        <input type="hidden" name="task" value="gallery.uploader" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<div id="file-upload-form" style="display: none;">
    <form enctype="multipart/form-data" method="post"
        action="<?php echo Uri::base(); ?>index.php?option=com_bagallery&task=uploader.formUpload">
        <input type="file" multiple name="files[]">
    </form>
</div>