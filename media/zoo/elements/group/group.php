<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// no direct access
defined('_JEXEC') or die('Restricted access');

// register ElementRepeatable class
App::getInstance('zoo')->loader->register('ElementRepeatable', 'elements:repeatable/repeatable.php');

/*
   Class: ElementGroup
   The group element class
*/
class ElementGroup extends ElementRepeatable implements iSubmittable {

    /*
    Function: bindData
        Set data through data array.

    Parameters:
        $data - array

    Returns:
        Void
*/
    public function bindData($data = array()) {

        // set raw input for textarea
        $post = $this->app->request->get('post:', 'raw');
        foreach ($data as $index => $instance_data) {
            foreach ($this->config->get('element', array()) as $element) {
                if ($element['type'] == 'textarea' && isset($post['elements'][$this->identifier][$index][$element['name']])) {
                    $data[$index][$element['name']] = $post['elements'][$this->identifier][$index][$element['name']];
                }
            }

        }

        parent::bindData($data);

    }

    /*
       Function: getValue
            Returns the element's value.

       Parameters:
            $params - render parameter

        Returns:
            Value
    */
    public function _getValue($params = array()) {
        return $this->_item->elements->find("{$this->identifier}.{$this->key()}", array());
    }

    /*
        Function: getSearchData
            Get elements search data.

        Returns:
            String - Search data
    */
    public function _getSearchData() {
        return join(' ', $this->_getValue());
    }

    /*
        Function: render
            Renders the repeatable element.

       Parameters:
            $params - render parameter

        Returns:
            String - html
    */
    protected function _render($params = array()) {

        $result = array();
        $values = $this->_getValue($params);
        foreach ($this->config->get('element', array()) as $element) {

            if (empty($values[$element['name']])) {
                continue;
            }

            if ($element['type'] == 'image') {
                $result[] = "<img src=\"{$values[$element["name"]]}\">";
            } else {
                $result[] = $values[$element['name']];
            }
        }

        return $this->app->element->applySeparators('tag=[<div>%s</div>]', $result);
    }

    /*
       Function: _edit
           Renders the repeatable edit form field.

       Returns:
           String - html
    */
    protected function _edit() {

        $this->app->document->addScript('elements:image/edit.js');
        $this->app->document->addScriptOptions('media-picker-api', ['apiBaseUrl' => Uri::base() . 'index.php?option=com_media&format=json']);

        return $this->renderLayout($this->getLayout('edit.php'));
    }

    protected function _renderSubmission($params = array())
    {
        return $this->renderLayout($this->getLayout('submission.php'), array('params' => $params));
    }

    public function _validateSubmission($value, $params) {

        $result = [];
        foreach ($this->config->get('element', array()) as $element) {

            if ($element['type'] === 'image') {

                // init vars
                $trusted_mode = $params->get('trusted_mode');

                // get old file value
                $old_file = $this->get($element['name']);

                $file = '';
                // get file from select list
                if ($trusted_mode && $file = $value->get($element['name'])) {

                    if (!$this->_inUploadPath($file) && $file != $old_file) {
                        throw new AppValidatorException(sprintf('This file is not located in the upload directory.'));
                    }

                    if (!File::exists($file)) {
                        throw new AppValidatorException(sprintf('This file does not exist.'));
                    }

                // get file from upload
                } else {

                    try {

                        // get the uploaded file information
                        $userfile = $value->get("userfile_{$element['name']}", null);

                        $max_upload_size = $this->config->get('max_upload_size', '512') * 1024;
                        $max_upload_size = empty($max_upload_size) ? null : $max_upload_size;
                        $file = $this->app->validator
                            ->create('file', array('mime_type_group' => 'image', 'max_size' => $max_upload_size))
                            ->addMessage('mime_type_group', 'Uploaded file is not an image.')
                            ->clean($userfile);

                    } catch (AppValidatorException $e) {
                        if ($e->getCode() != UPLOAD_ERR_NO_FILE) {
                            throw $e;
                        }

                        if (!$trusted_mode && $old_file && $value->get($element['name'])) {
                            $file = $old_file;
                        }

                    }

                }

                $result[$element['name']] = $file;

            } else {

                $result[$element['name']] = $this->app->validator->create('textfilter', array('required' => $params->get('required')))->clean($value->get($element['name']));

            }
        }

        // connect to submission beforesave event
        $this->app->event->dispatcher->connect('submission:beforesave', array($this, 'submissionBeforeSave'));

        return $result;
    }

    /*
        Function: submissionBeforeSave
            Callback before item submission is saved

        Returns:
            void
    */
    public function submissionBeforeSave() {

        foreach ($this->config->get('element', array()) as $element) {
            foreach ($this as $self) {

                if ($element['type'] !== 'image') {
                    continue;
                }

                // get the uploaded file information
                if (($userfile = $this->get($element['name'])) && is_array($userfile)) {
                    // get file name
                    $ext       = $this->app->filesystem->getExtension($userfile['name']);
                    $base_path = JPATH_ROOT . '/' . $this->_getUploadImagePath() . '/';
                    $file      = $base_path . $userfile['name'];
                    $filename  = basename($file, '.' . $ext);

                    $i = 1;
                    while (File::exists($file)) {
                        $file = $base_path . $filename . '-' . $i++ . '.' . $ext;
                    }

                    if (!File::upload($userfile['tmp_name'], $file)) {
                        throw new AppException('Unable to upload file.');
                    }

                    $this->set($element['name'], $this->app->path->relative($file));
                }

            }
        }
    }

    /*
       Function: editElement
          Renders elements elements for form input.

       Parameters:
          $var - form var name
          $num - option order number

       Returns:
          Array
    */
    public function editElement($var, $num, $name = null, $type = null) {
        $options = array();
        foreach (parent::getConfigForm()->getXML('_default')->xpath('param[@name="element"]/option') as $option) {
            $options[] = array('text' => Text::_((string) $option), 'value' => $option->attributes()->name);
        }

        return $this->renderLayout($this->app->path->path("elements:group/tmpl/editelement.php"), compact('var', 'num', 'name', 'type', 'options'));
    }

    /*
        Function: getConfigForm
            Get parameter form object to render input form.

        Returns:
            Parameter Object
    */
    public function getConfigForm() {
        return parent::getConfigForm()->addElementPath(dirname(__FILE__));
    }

    /*
        Function: loadAssets
            Load elements css/js config assets.

        Returns:
            Void
    */
    public function loadConfigAssets() {
        $this->app->document->addScript('elements:group/group.js');
        $this->app->document->addStylesheet('elements:group/group.css');
        return parent::loadConfigAssets();
    }

    public function getImageSelectList($name) {

        $image = $this->get($name);
        $options = array($this->app->html->_('select.option', '', '- '.Text::_('Select Image').' -'));

        if (!empty($image) && !$this->_inUploadPath($image)) {
            $options[] = $this->app->html->_('select.option', $image, '- '.Text::_('No Change').' -');
        }

        $img_ext = str_replace(',', '|', trim(ComponentHelper::getParams('com_media')->get('image_extensions'), ','));

        foreach ($this->app->path->files('root:'.$this->_getUploadImagePath(), false, '/\.('.$img_ext.')$/i') as $file) {
            $options[] = $this->app->html->_('select.option', $this->_getUploadImagePath().'/'.$file, $file);
        }

        return $this->app->html->_('select.genericlist', $options, $this->getControlName($name), 'class="image"', 'value', 'text', $image);
    }

    protected function _inUploadPath($image) {
        return $this->_getUploadImagePath() == dirname($image);
    }

    protected function _getUploadImagePath() {
        return trim(trim($this->config->get('upload_directory', 'images/zoo/uploads/')), '\/');
    }

}
