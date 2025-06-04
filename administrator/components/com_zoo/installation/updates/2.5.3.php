<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class Update253 implements iUpdate {

    /**
     * @inheritDoc
     */
	public function getNotifications($app) {}

    /**
     * @inheritDoc
     */
	public function run($app) {

		// remove obsolete elements
		foreach (array('video', 'gallery', 'facebookilike', 'itempublishup') as $element) {
			if ($folder = $app->path->path('media:zoo/elements/'.$element)) {
				Folder::delete($folder);
			}
		}

		// rename _itempublishup to _itempublish_up in config files
		foreach ($app->path->files('root:', true, '/positions\.config/') as $file) {
			if (preg_match('#renderer\/item\/#', $file)) {
				$changed = false;
				if (!$path = $app->path->path('root:'.$file)) {
					continue;
				}
				$data = $app->data->create(file_get_contents($path));
				if (!empty($data)) {
					foreach ($data as $layout => $positions) {
						foreach ($positions as $position => $elements) {
							foreach ($elements as $index => $element) {
								if (isset($element['element']) && $element['element'] == '_itempublishup') {
									$data[$layout][$position][$index]['element'] = '_itempublish_up';
									$changed = true;
								}
							}
						}
					}
				}
				if ($changed) {
					$data = (string) $data;
					File::write($app->path->path('root:'.$file), $data);
				}
			}
		}

	}
}
