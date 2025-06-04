<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

use Balbooa\Component\Gallery\Administrator\Helper\GalleryHelper;
use Balbooa\Component\Gallery\Administrator\Helper\GalleryHtmlJgrid;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$user = Factory::getUser();
$sortFields = $this->getSortFields();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$state = $this->state->get('filter.state');
$limit = $this->pagination->limit;
$pagLimit = [
    5 => 5,
    10 => 10,
    15 => 15,
    20 => 20,
    25 => 25,
    30 => 30,
    50 => 50,
    100 => 100,
    0 => Text::_('JALL'),
];
if (!isset($pagLimit[$limit])) {
    $limit = 0;
}
if ($listDirn == 'asc') {
    $dirn = Text::_('JGLOBAL_ORDER_ASCENDING');
} else {
	$listDirn = 'desc';
    $dirn = Text::_('JGLOBAL_ORDER_DESCENDING');
}
if ($listOrder == 'published') {
    $order = Text::_('JSTATUS');
} else if ($listOrder == 'title') {
    $order = Text::_('JGLOBAL_TITLE');
} else {
    $order = Text::_('JGRID_HEADING_ID');
}
if ($state == '') {
    $status = Text::_('SELECT_STATUS');
} else if ($state == '1') {
    $status = Text::_('JPUBLISHED');
} else if ($state == '0') {
    $status = Text::_('JUNPUBLISHED');
} else {
    $status = Text::_('JTRASHED');
}
$uploading = new StdClass();
$uploading->const = Text::_('UPDATING');
$uploading->saving = Text::_('SAVING');
$uploading->installing = Text::_('INSTALLING');
$uploading->url = Uri::root();
$uploading = json_encode($uploading);
$galleryStateStr = GalleryHelper::checkGalleryState();
$galleryState = json_decode($galleryStateStr);
$galleryStateCount = !isset($galleryState->data) ? 1 : 0;
if ($this->about->tag != 'pro') {
    $galleryStateCount = 0;
}
?>
<link rel="stylesheet" type="text/css" href="components/com_bagallery/assets/css/ba-admin.css?<?php echo $this->about->version; ?>">
<script type="text/javascript">
var JUri = '<?php echo Uri::root(); ?>';
<?php echo GalleryHelper::getGalleryLanguage(); ?>
</script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function(){
    Joomla.orderTable = function(){
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.value;
        if (order != '<?php echo $listOrder; ?>') {
            dirn = 'asc';
        } else {
            dirn = direction.value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
    var str = "<div class='btn-wrapper' id='toolbar-language'>";
    str += "<button class='btn btn-small'><span class='icon-star'>";
    str += "</span><?php echo Text::_('LANGUAGE'); ?></button></div>";
    str += "<div class='btn-wrapper ba-dashboard-popover-trigger' id='toolbar-about' data-target='ba-dashboard-about'>";
    str += "<button class='btn btn-small'><span class='icon-bookmark' data-notification='<?php echo $galleryStateCount; ?>'></span>";
    str += "<?php echo Text::_('ABOUT') ?></button></div>";
    str += "<div class='btn-wrapper' id='toolbar-cleanup-images'>";
    str += "<button class='btn btn-small'><span class='icon-trash'>";
    str += "</span><?php echo Text::_('CLEANUP_IMAGES') ?></button></div>";
    jQuery('#toolbar').append(str);
});
</script>
<div class="ba-dashboard-apps-dialog ba-dashboard-about">
    <div class="ba-dashboard-apps-body">
        <div class="ba-gallery-dashboard-row gallery-version-wrapper">
            <i class="zmdi zmdi-info"></i>
            <span>Gallery</span>
            <span class="gallery-version"><?php echo $this->about->version; ?></span>
        </div>
<?php
    if ($this->about->tag == 'pro') {
?>
        <div class="ba-gallery-dashboard-row gallery-deactivate-license"
            <?php echo isset($galleryState->data) ? '' : 'style="display:none;"'; ?>>
            <i class="zmdi zmdi-shield-check"></i>
            <span><?php echo Text::_('YOUR_LICENSE_ACTIVE'); ?></span>
            <a class="deactivate-link dashboard-link-action" href="#"><?php echo Text::_('DEACTIVATE'); ?></a>
        </div>
        <div class="ba-gallery-dashboard-row gallery-activate-license"
            <?php echo !isset($galleryState->data) ? '' : 'style="display:none;"'; ?>>
            <i class="zmdi zmdi-shield-check"></i>
            <span><?php echo Text::_('ACTIVATE_LICENSE'); ?></span>
            <a class="activate-link dashboard-link-action" href="#"><?php echo Text::_('ACTIVATE'); ?></a>
        </div>
<?php
    }
?>
        <div class="ba-gallery-dashboard-row gallery-update-wrapper">
            <i class="zmdi zmdi-check-circle"></i>
            <span><?php echo Text::_('GALLERY_IS_UP_TO_DATE'); ?></span>
        </div>
    </div>
    <div class="ba-dashboard-apps-footer">
        <span>© <?php echo date('Y'); ?> <a href="https://www.balbooa.com/" target="_blink">Balbooa.com</a> All Rights Reserved.</span>
    </div>
</div>
<div id="deactivate-dialog" class="ba-modal-sm modal hide" style="display:none">
    <div class="modal-body">
        <h3><?php echo Text::_('LICENSE_DEACTIVATION'); ?></h3>
        <p class="modal-text can-delete"><?php echo Text::_('ARE_YOU_SURE_DEACTIVATE') ?></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal">
            <?php echo Text::_('CANCEL') ?>
        </a>
        <a href="#" class="ba-btn-primary red-btn" id="apply-deactivate">
            <?php echo Text::_('APPLY') ?>
        </a>
    </div>
</div>
<div id="login-modal" class="ba-modal-sm modal hide" aria-hidden="true" style="display: none;">
    <div class="modal-body">
        
    </div>
</div>
<input type="hidden" value="<?php echo htmlentities($uploading); ?>" id="update-data">
<div id="ba-notification">
    <p></p>
</div>
<div id="cleanup-images-dialog" class="ba-modal-sm modal hide" style="display:none">
    <div class="modal-body">
        <h3><?php echo Text::_('CLEANUP_IMAGES') ?></h3>
        <p class="modal-text"><?php echo Text::_('REMOVE_UNUSED_IMAGES') ?></p>
        <p class="modal-text"><?php echo Text::_('CLEANUP_ATTENTION') ?></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="ba-btn" data-dismiss="modal"><?php echo Text::_('CANCEL'); ?></a>
        <a href="#" class="ba-btn-primary red-btn" id="cleanup-images"><?php echo Text::_('DELETE'); ?></a>
    </div>
</div>
<div id="language-dialog" class="modal hide ba-modal-sm" style="display:none">
    <div class="modal-body">
        <div class="languages-wrapper"></div>
    </div>
</div>
<form action="<?php echo Route::_('index.php?option=com_bagallery'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
        <div class="span12 gallary-view">
            <div id="filter-bar">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                       placeholder="<?php echo Text::_('SEARCH') ?>">
                <i class="zmdi zmdi-search"></i>
                <button>Submit</button>
            <div class="pagination-limit">
                <div class="ba-custom-select">
                    <input readonly value="<?php echo $pagLimit[$limit]; ?>"
                           size="<?php echo strlen($limit); ?>" type="text">
                    <input type="hidden" name="limit" id="limit" 
                           onchange="Joomla.submitform()" value="<?php echo $limit; ?>">
                    <i class="zmdi zmdi-caret-down"></i>
                    <ul>
                        <?php
                        foreach ($pagLimit as $key => $lim) {
                            $str = '<li data-value="'.$key.'">';
                            if ($key == $limit) {
                                $str .= '<i class="zmdi zmdi-check"></i>';
                            }
                            $str .= $lim.'</li>';
                            echo $str;
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="sorting-direction">
                <div class="ba-custom-select">
                    <input readonly value="<?php echo $dirn; ?>"
                           size="<?php echo strlen($dirn); ?>" type="text">
                    <input type="hidden" name="directionTable" id="directionTable" 
                           onchange="Joomla.orderTable()" value="<?php echo $listDirn; ?>">
                    <i class="zmdi zmdi-caret-down"></i>
                    <ul>
                        <li data-value="asc" >
                            <?php echo $listDirn == 'asc' ? '<i class="zmdi zmdi-check"></i>' : ''; ?>
                            <?php echo Text::_('JGLOBAL_ORDER_ASCENDING');?>
                        </li>
                        <li data-value="desc">
                            <?php echo $listDirn == 'desc' ? '<i class="zmdi zmdi-check"></i>' : ''; ?>
                            <?php echo Text::_('JGLOBAL_ORDER_DESCENDING');?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="sorting-table">
                <div class="ba-custom-select">
                    <input readonly value="<?php echo $order; ?>" size="<?php echo strlen($order); ?>" type="text">
                    <input type="hidden" name="sortTable" id="sortTable" 
                           onchange="Joomla.orderTable()" value="<?php echo $listOrder; ?>">
                    <i class="zmdi zmdi-caret-down"></i>
                    <ul>
                        <?php
                        foreach ($sortFields as $key => $field) {
                            $str = '<li data-value="'.$key.'">';
                            if ($key == $listOrder) {
                                $str .= '<i class="zmdi zmdi-check"></i>';
                            }
                            $str .= $field.'</li>';
                            echo $str;
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="filter-state">
                <div class="ba-custom-select">
                    <input readonly value="<?php echo $status; ?>" size="<?php echo strlen($status); ?>" type="text">
                    <input type="hidden" name="filter_state" 
                           onchange="this.form.submit()" value="<?php echo $state; ?>">
                    <i class="zmdi zmdi-caret-down"></i>
                    <ul>
                        <li data-value="">
                            <?php echo $state == '' ? '<i class="zmdi zmdi-check"></i>' : ''; ?>
                            <?php echo Text::_('SELECT_STATUS');?>
                        </li>
                        <li data-value="1" >
                            <?php echo $state == '1' ? '<i class="zmdi zmdi-check"></i>' : ''; ?>
                            <?php echo Text::_('JPUBLISHED');?>
                        </li>
                        <li data-value="0">
                            <?php echo $listDirn == '0' ? '<i class="zmdi zmdi-check"></i>' : ''; ?>
                            <?php echo Text::_('JUNPUBLISHED');?>
                        </li>
                        <li data-value="-2">
                            <?php echo $state == '-2' ? '<i class="zmdi zmdi-check"></i>' : ''; ?>
                            <?php echo Text::_('JTRASHED');?>
                        </li>
                    </ul>
                </div>                
            </div>
            </div>
            <div class="main-table">
                <div class="table-header">
                    <div>
                    <label>
                        <input type="checkbox" name="checkall-toggle" value=""
                               title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        <i class="zmdi zmdi-check-circle check-all"></i>
                    </label>                        
                    </div>
                    <div>
                         <?php echo Text::_('JSTATUS'); ?>
                    </div>
                    <div>
                        <?php echo Text::_('GALLERIES'); ?>
                    </div>
                    <div>
                        <?php echo Text::_('ID'); ?>
                    </div>
                </div>
                <table class="table table-striped">
                    <tbody>
                       <?php foreach ($this->items as $i => $item) : 
                            $canChange  = $user->authorise('core.edit.state', '.galleries.' . $item->id); ?>
                        <tr>
                            <td class="select-td">
                                <label>
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                    <i class="zmdi zmdi-circle-o"></i>
                                    <i class="zmdi zmdi-check"></i>
                                </label>                                
                            </td>
                            <td class="status-td">
                                <?php echo GalleryHtmlJgrid::published($item->published, $i, 'galleries.', $canChange); ?>
                            </td>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_bagallery&task=gallery.edit&id='. $item->id); ?>">
                                    <?php echo $item->title; ?>
                                </a>
                            </td>
                            <td>
                                <?php echo $item->id; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>                
            </div>
<?php
        if ($user->authorise('core.create', 'com_bagallery')) {
?>
            <div class="camera-container" onclick="Joomla.submitbutton('gallery.add')">
                <i class="zmdi zmdi-camera"></i>
                <span class="ba-tooltip ba-top"><?php echo Text::_('CREATE_GALLERY'); ?></span>
            </div>
<?php
        }
            echo $this->pagination->getListFooter();
?>
            <div>
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>