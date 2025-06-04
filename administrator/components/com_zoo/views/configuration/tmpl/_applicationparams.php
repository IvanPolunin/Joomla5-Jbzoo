<?php use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access'); ?>

<?php $form = $this->application->getParamsForm()->setValues($this->params->get('global.config.')); ?>
<?php if ($form->getParamsCount('application-config')) : ?>
    <h3 class="toggler"><?php echo Text::_('CONFIGURATION GLOBAL'); ?></h3>
    <div class="content">
        <?php echo $form->render('params[config]', 'application-config'); ?>
    </div>
<?php endif; ?>

<h3 class="toggler"><?php echo Text::_('TEMPLATE GLOBAL'); ?></h3>
<div class="content">
    <?php
    if ($template = $this->application->getTemplate()) {
        if ($params_form = $template->getParamsForm()) {
            echo $params_form->setValues($this->params->get('global.template.'))->render('params[template]', 'category');
            echo $params_form->setValues($this->params->get('global.template.'))->render('params[template]', 'item');
        }
    } else {
        echo '<em>'.Text::_('Please select a Template').'</em>';
    }
    ?>
</div>

<?php foreach ($this->application->getAddonParamsForms() as $name => $params_form) : ?>
    <h3 class="toggler"><?php echo Text::_($name); ?></h3>
    <div class="content">
        <?php
        echo $params_form->setValues($this->params->get('global.'.strtolower($name).'.'))->render('addons['.strtolower($name).']');
        ?>
    </div>
<?php endforeach; ?>

<h3 class="toggler"><?php echo Text::_('PERMISSIONS'); ?></h3>
<div id="zoo-permissions" class="content">
    <ul class="parameter-form">
        <li class="parameter">
            <div class="label"><label class="hasTip" title="Permissions"><?php echo Text::_('APPLICATION'); ?></label></div>
            <div class="field">
                <a href="#rules-modal" data-toggle="modal" data-bs-toggle="modal"><?php echo $this->application->name; ?></a>
            </div>
        </li>
        <li>
            <div class="label"><label class="hasTip" title="Permissions">&nbsp;</label></div>
            <div class="field">
            </div>
        </li>
        <li>
            <div class="label"><label class="hasTip" title="Permissions"><?php echo Text::_('TYPES'); ?></label></div>
            <div class="field">
                <?php foreach ($this->assetPermissions as $permissionName => $permissions) : ?>
                    <a href="#<?php echo $permissionName; ?>-rules-modal" data-toggle="modal" data-bs-toggle="modal"><?php echo ucfirst($permissionName); ?></a>
                    <br/>
                <?php endforeach; ?>
            </div>
        </li>
    </ul>
    <div id="rules-modal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Application</h3>
                </div>
                <div class="modal-body">
                    <div style="padding: 1rem;">
                        <?php echo str_replace(['onchange="sendPermissions.call(this, event)"', 'data-onchange-task="permissions.apply"'], '', $this->permissions->getInput('rules_application')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php foreach ($this->assetPermissions as $permissionName => $permissions) : ?>
        <div id="<?php echo $permissionName; ?>-rules-modal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title"><?php echo ucfirst($permissionName); ?></h3>
                    </div>
                    <div class="modal-body">
                        <div style="padding: 1rem;">
                            <?php echo str_replace(
                                array('permission-', 'onchange="sendPermissions.call(this, event)"', 'data-onchange-task="permissions.apply"'),
                                array('permission-' . $permissionName . '-', 'onchange=""', ''),
                                $permissions->getInput('rules_' . $permissionName));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
