<?php
// Запрещаем прямой доступ к файлу
defined('_JEXEC') or die('Restricted access');

class JBCSVItemUserRelatedItemsMain extends JBCSVItem 
{
	    public function toCSV()
    {
        //$result = array();

        if (isset($this->_value)) {

            $result = $this->_value['item'];

            if (is_array($result)) {
                return implode(JBCSVItem::SEP_CELL, $result);
            } else {
                return $result;
            }

        }

        return null;
    }
/*
    public function fromCSV($value, $position = null)
    {
        $value = JString::trim($value, '/\\');
        $value = JString::trim($value);
        $this->_element->bindData(array('value' => $value));

        return $this->_item;
    }
*/

    public function fromCSV($value, $position = null)
    {
        $data = ($position == 1) ? array() : $data = $this->_element->data();

            $value = explode(JBCSVItem::SEP_CELL, $value);
        
        $this->_element->bindData(array('item' => $value));

        return $this->_item;
    }

}