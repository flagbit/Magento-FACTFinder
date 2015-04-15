<?php

/**
 * Helper class
 *
 * Helper for backend configurations.
 */
class FACTFinder_Core_Helper_Backend extends Mage_Core_Helper_Abstract
{

    /**
     * Generate a storable representation of a value
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function _serializeValue($value)
    {
        if (is_array($value)) {
            $data = array();
            foreach ($value as $rule => $setup) {
                if ($rule == '__empty') {
                    continue;
                }
                if (!array_key_exists($rule, $data)) {
                    $data[$rule] = $setup;
                }
            }
            return serialize($data);
        } else {
            return '';
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function _unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return unserialize($value);
        } else {
            return array();
        }
    }


    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param mixed
     *
     * @return bool
     */
    protected function _isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        unset($value['__empty']);
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('attribute', $row)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Encode value to be used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array $data
     *
     * @return array
     */
    protected function _encodeArrayFieldValue(array $data)
    {
        $result = array();
        foreach ($data as $set => $value) {
            $_id = Mage::helper('core')->uniqHash('_');
            $result[$_id] = $value;
        }

        return $result;
    }


    /**
     * Decode value from used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array
     *
     * @return array
     */
    protected function _decodeArrayFieldValue(array $data)
    {
        $result = array();
        unset($data['__empty']);
        foreach ($data as $_id => $row) {
            if (!is_array($row) || !array_key_exists('attribute', $row)) {
                continue;
            }

            $result[trim($row['attribute'])] = $row;
        }

        return $result;
    }


    /**
     * Make value readable by Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param mixed $value
     *
     * @return array
     */
    public function unserializeFieldValue($value)
    {
        $value = $this->_unserializeValue($value);
        if (!$this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_encodeArrayFieldValue($value);
        }

        return $value;
    }


    /**
     * Make value ready for store
     *
     * @param mixed $value
     *
     * @return string
     */
    public function serializeFieldValue($value)
    {
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }

        $value = $this->_serializeValue($value);

        return $value;
    }

}