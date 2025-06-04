<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

$this->app->html->_('behavior.modal', 'a.modal-button');
$this->app->document->addScript('fields:zoorelateditems.js');

// init vars
$params = $this->app->parameterform->convertParams($parent);
$ids  = (array) $params->get((string) $name, array());
$items = $items = $this->app->table->item->getByIds($ids, false, null, 'ids');

$application = $this->app->zoo->getApplication();
$identifier = uniqid($params->get('name', ''));

$query = array('controller' => 'item', 'task' => 'element', 'tmpl' => 'component', 'func' => 'selectRelateditem', 'object' => $identifier);

// filter types
$type_filter = (string) $node->attributes()->type_filter;
if ($type_filter) {
    foreach (explode(',', $type_filter) as $key => $selectable_type) {
        $query["type_filter[$key]"] = trim($selectable_type);
    }
}

$link = $this->app->link($query);

?>

<div id="<?php echo $identifier; ?>" class="select-relateditems">
    <ul>

        <?php foreach ($items as $item) : ?>

            <li>
                <div>
                    <div class="item-name"><?php echo $item->name; ?></div>
                    <div class="item-sort" title="<?php echo Text::_('Sort Item'); ?>"></div>
                    <div class="item-delete" title="<?php echo Text::_('Delete Item'); ?>"></div>
                    <input type="hidden" name="<?php echo "{$control_name}[{$name}][]"; ?>" value="<?php echo $item->id; ?>"/>
                </div>
            </li>

        <?php endforeach; ?>
    </ul>
    <a class="item-add modal-button" rel="{handler: 'iframe', size: {x: 850, y: 500}}" title="<?php echo Text::_('Add Item'); ?>" href="<?php echo $link; ?>" ><?php echo Text::_('Add Item'); ?></a>
</div>

<script type="text/javascript">
    jQuery(function($) {
        $('#<?php echo $identifier; ?>').ZooRelatedItems({ variable: '<?php echo "{$control_name}[{$name}][]"; ?>', msgDeleteItem: '<?php echo Text::_('Delete Item'); ?>', msgSortItem: '<?php echo Text::_('Sort Item'); ?>' });
    });
</script>
