<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

// add js
$this->app->document->addScript('assets:js/import.js');

?>

<form class="configuration-import menu-has-level3" action="index.php" method="post" name="adminForm" id="adminForm" accept-charset="utf-8" enctype="multipart/form-data">

<?php echo $this->partial('menu'); ?>

<div class="box-bottom">
	<div>

		<h2><?php echo Text::_('Import Categories and Items:'); ?></h2>

		<?php if ($this->info['frontpage_count']) : ?>
		<fieldset class="frontpage">

			<legend><?php echo Text::_('Frontpage:'); ?></legend>
			<span>
				<?php
					echo sprintf(Text::_('Import frontpage'), 1);
				?>
			</span>
			<input type="checkbox" name="import-frontpage" checked="checked" />

        <?php if (!empty($this->info['frontpage']['content']) && !empty($this->info['frontpage']['assign'])) : ?>

        <ul>

            <?php
            $options = array($this->app->html->_('select.option', '', Text::_('Ignore')));
            foreach ($this->info['frontpage']['assign'] as $value => $text) {
                $options[] = $this->app->html->_('select.option', $value, $text);
            }
            ?>
            <?php foreach ($this->info['frontpage']['content'] as $name) : ?>
              <li class="assign">
                  <?php echo $this->app->html->_('select.genericlist', $options, 'frontpage-assign['.$name.']', 'class="assign"'); ?>
                <span class="name"><?php echo $name; ?></span>
              </li>
            <?php endforeach; ?>

        </ul>
        </fieldset>
        <?php endif; ?>

		</fieldset>
		<?php endif; ?>

		<?php if ($this->info['category_count']) : ?>
		<fieldset class="categories">

			<legend><?php echo Text::_('Categories:'); ?></legend>
			<span>
				<?php
					if ($this->info['category_count'] == 1) {
						echo sprintf(Text::_('Import %s category'), 1);
					} else {
						echo sprintf(Text::_('Import %s categories'), (int) $this->info['category_count']);
					}
				?>
			</span>
			<input type="checkbox" name="import-categories" checked="checked" />

      <?php if (!empty($this->info['categories']['content']) && !empty($this->info['categories']['assign'])) : ?>
        <ul>

            <?php
            $options = array($this->app->html->_('select.option', '', Text::_('Ignore')));
            foreach ($this->info['categories']['assign'] as $value => $text) {
              $options[] = $this->app->html->_('select.option', $value, $text);
            }
            ?>
            <?php foreach ($this->info['categories']['content'] as $name) : ?>
            <li class="assign">
              <?php echo $this->app->html->_('select.genericlist', $options, 'category-assign['.$name.']', 'class="assign"'); ?>
              <span class="name"><?php echo $name; ?></span>
            </li>
            <?php endforeach; ?>

        </ul>
      </fieldset>
      <?php endif; ?>

		<?php endif; ?>

		<?php foreach ($this->info['items'] as $key => $item_info) : ?>
		<fieldset class="items">
			<legend><?php echo (int) $item_info['item_count']; ?> x <?php echo $key; ?></legend>
			<div class="assign-group">

				<div class="info">
					<label for="type-select<?php echo $key; ?>"><?php echo Text::_('CHOOSE_TYPE_MATCH_DATA'); ?></label>
					<?php
						$options = array($this->app->html->_('select.option', '', '- '.Text::_('Select Type').' -'));
						echo $this->app->html->_('zoo.typelist', $this->application, $options, 'types['.$key.']', 'class="type"', 'value', 'text');
					?>
				</div>

				<ul>
				<?php foreach ($item_info['elements'] as $alias => $element_info) : ?>
					<li class="assign">
						<?php
							foreach ($element_info['assign'] as $type => $assign_elements) {
								$options = array();
								$options[] = $this->app->html->_('select.option', '', Text::_('Ignore'));
								foreach ($assign_elements as $element) {
									$options[] = $this->app->html->_('select.option', $element->identifier, $element->config->get('name') . ' (' . ucfirst($element->getElementType()) . ')');
								}
								echo $this->app->html->_('select.genericlist', $options, 'element-assign['.$key.']['.$alias.']['.$type.']', 'class="assign"');
							}
						?>
						<span class="name"><?php echo $element_info['name']; ?></span>
						<span class="type"><?php echo '('.$element_info['type'].')'; ?></span>
					</li>
				<?php endforeach; ?>
				</ul>

			</div>
		</fieldset>
		<?php endforeach; ?>

		<?php if (!$this->info['frontpage_count'] && !$this->info['category_count'] && empty($this->info['items'])) : ?>
			<div class="creation-form infobox">
				<?php echo Text::_('No content to import!'); ?>
			</div>
		<?php else : ?>
			<button class="button-grey" id="submit-button" type="button"><?php echo Text::_('Import'); ?></button>
		<?php endif; ?>
	</div>
</div>

<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="file" value="<?php echo $this->file; ?>" />
<input type="hidden" name="changeapp" value="<?php echo $this->application->id; ?>" />
<?php echo $this->app->html->_('form.token'); ?>

<script type="text/javascript">
	jQuery(function($) {
		$('#adminForm').Import( {msgSelectWarning: "<?php echo Text::_("MSG_ASSIGN_WARNING"); ?>", msgWarningDuplicate: "<?php echo Text::_("There are duplicate assignments."); ?>"} );
	});
</script>

</form>

<?php echo ZOO_COPYRIGHT;
