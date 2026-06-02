<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Folder;

// load config
require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

/**
 * Class JFormFieldJBFileName
 */
class JFormFieldJBFileName extends FormField
{

    protected $type = 'jbfilename';

    /**
     * Render control HTML
     * @return mixed
     */
    public function getInput()
    {
        // get app
        $app  = App::getInstance('zoo');
        $ext  = (string)$this->element->attributes()->ext;
        $path = Path::clean(JPATH_ROOT . $this->element->attributes()->path);

        $options = array();

        if (is_dir($path)) {
            if ($ext) {
                foreach (Folder::files($path, '^([-_A-Za-z0-9]*)\.' . $ext) as $tmpl) {
                    $tmpl      = basename($tmpl, '.' . $ext);
                    $options[] = $app->html->_('select.option', $tmpl, ucwords($tmpl));
                }
            } else {
                foreach (Folder::files($path) as $tmpl) {
                    $options[] = $app->html->_('select.option', $tmpl, ucwords($tmpl));
                }
            }

        }

        return $app->html->_(
            'select.genericlist',
            $options,
            $this->getName($this->fieldname),
            '',
            'value',
            'text',
            $this->value
        );
    }

}