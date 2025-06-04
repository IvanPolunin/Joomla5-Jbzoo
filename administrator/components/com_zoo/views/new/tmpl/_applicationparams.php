<?php use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access'); ?>

<h3 class="toggler"><?php echo Text::_('CONFIGURATION GLOBAL'); ?></h3>
<div class="content">
	<?php echo $this->application->getParamsForm()->setValues($this->params->get('global.config.'))->render('params[config]', 'application-config'); ?>
</div>

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
<div class="content">
	<ul class="parameter-form">
		<li class="parameter">
			<div class="label">&nbsp;</div>
			<div class="field"><?php echo Text::_('SAVE_APPLICATION_FOR_PERMISSIONS'); ?></div>
		</li>
	</ul>
</div>
