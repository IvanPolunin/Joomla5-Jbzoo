<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Form\FormField;

class JFormFieldZooSubmission extends FormField {

	protected $type = 'ZooSubmission';

	public function getInput() {

		// load config
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

		return App::getInstance('zoo')->field->render('zoosubmission', $this->fieldname, $this->value, $this->element, array('control_name' => "jform[{$this->group}]", 'parent' => $this->form->getValue('params')));

	}

}
