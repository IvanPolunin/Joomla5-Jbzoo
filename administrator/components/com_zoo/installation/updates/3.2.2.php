<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Filesystem\File;

class Update322 implements iUpdate {

    /**
     * @inheritDoc
     */
    public function getNotifications($app) {}

    /**
     * @inheritDoc
     */
    public function run($app) {

        foreach (array('blog', 'business', 'cookbook', 'documentation', 'download', 'movie', 'page', 'product') as $application) {
            foreach (array('media/zoo/applications/'.$application.'/templates/*/renderer/item', 'media/zoo/applications/'.$application.'/templates/*/renderer/item/*') as $dir) {
                foreach((array) glob(JPATH_ROOT.'/'.$dir, GLOB_ONLYDIR) as $folder) {
                    $file = "$folder/positions.xml";
                    if (File::exists($file) and $content = file_get_contents($file) and false === strpos($content, 'positions layout="edit"')) {
                        if (false !== $pos = strpos($content, '</renderer>')) {
$addition = <<<EOD
        <positions layout="edit">
            <position name="content">Content</position>
            <position name="media">Media</position>
            <position name="meta">Meta</position>
            <position name="administration">Administration</position>
        </positions>

EOD;
                            $content = substr($content, 0, $pos) . $addition . substr($content, $pos);
                            File::write($file, $content);
                        }
                    }

                    $file = "$folder/metadata.xml";
                    if (File::exists($file) and $content = file_get_contents($file) and false === strpos($content, 'layout name="edit"')) {
                        if (false !== $pos = strpos($content, '</metadata>')) {
$addition = <<<EOD
        <layout name="edit" type="edit">
            <name>Edit</name>
            <description>An item is rendered with the edit layout in the item edit view.</description>
        </layout>

EOD;
                            $content = substr($content, 0, $pos) . $addition . substr($content, $pos);
                            File::write($file, $content);
                        }
                    }
                }
            }
        }
    }
}
