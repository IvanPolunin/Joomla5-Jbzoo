<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class com_zooInstallerScript {


	public function install($parent) {

		// try to set time limit
		@set_time_limit(0);

		// try to increase memory limit
		if ((int) ini_get('memory_limit') < 32) {
			@ini_set('memory_limit', '32M');
		}

		// create applications folder
		if (!Folder::exists(JPATH_ROOT . '/media/zoo/applications/')) {
			Folder::create(JPATH_ROOT . '/media/zoo/applications/');
		}

		// initialize zoo framework
		require_once($parent->getParent()->getPath('extension_administrator').'/config.php');

		// get zoo instance
		$zoo = App::getInstance('zoo');

		// copy checksums file
		if (File::exists($parent->getParent()->getPath('source').'/checksums')) {
			File::copy($parent->getParent()->getPath('source').'/checksums', $zoo->path->path('component.admin:').'/checksums');
		}

		try {

			// clean ZOO installation
			$zoo->modification->clean();

		} catch (Exception $e) {}

		// applications
		foreach (Folder::folders($parent->getParent()->getPath('source').'/media/applications', '.', false, true) as $folder) {
			try {
				if (!$manifest = $zoo->install->findManifest($folder) or !$zoo->install->installApplicationFromFolder($folder)) {
					$zoo->error->raiseNotice(0, Text::sprintf('Unable to install/update app from folder (%s)', $folder));
				}
			} catch (AppException $e) {}
		}

		return true;

	}

	public function uninstall($parent) {

		// remove media folder
		if (Folder::exists(JPATH_ROOT . '/media/zoo/applications/')) {
			Folder::delete(JPATH_ROOT . '/media/zoo/applications/');
		}

		return true;
	}

	public function update($parent) {

		if ($manifest = $parent->getManifest()) {
			if (isset($manifest->install->sql)) {
				if ($parent->getParent()->parseSQLFiles($manifest->install->sql) === false) {
					// Install failed, rollback changes
					$parent->getParent()->abort(Text::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', Factory::getDBO()->stderr(true)));

					return false;
				}
			}
		}

		return $this->install($parent);
	}

	public function preflight($type, $parent) {

        if ('uninstall' == $type) {
            return;
        }

		// check ZOO requirements
		require_once($parent->getParent()->getPath('source').'/admin/installation/requirements.php');

		$requirements = new AppRequirements();
		if (true !== $error = $requirements->checkRequirements()) {
			$parent->getParent()->abort(Text::_('Component').' '.Text::_('Install').': '.Text::sprintf('Minimum requirements not fulfilled (%s: %s).', $error['name'], $error['info']));
			return false;
		}

	}

	public function postflight($type, $parent) {

        if ('uninstall' == $type) {
            return;
        }

		$row = Table::getInstance('extension');
		if ($row->load($row->find(array('element' => 'com_zoo'))) && strlen($row->element)) {
			$row->client_id = 1;
			$row->store();
		}

		// initialize zoo framework
		require_once($parent->getParent()->getPath('extension_administrator').'/config.php');

		// get zoo instance
		$zoo = App::getInstance('zoo');

		// finally update
		if ($zoo->update->required()) {
			$zoo->error->raiseNotice(0, Text::_('ZOO requires an update. Please click <a href="index.php?option=com_zoo">here</a>.'));
		}

	}

}
