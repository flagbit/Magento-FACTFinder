<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Helper class
 *
 * Helper for backend configurations.
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
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
        if (!is_array($value)) {
            return '';
        }

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
     * @param mixed $value
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
     * @param array $data
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


    /**
     * Check configuration data by contacting FACT-Finder servers.
     *
     * @param array $configData
     *
     * @return array
     */
    public function checkConfigData($configData)
    {
        $dataArray = $this->_getCompleteFieldsetData($configData);
        $data = new Varien_Object($dataArray);
        $errors = array();

        if (stripos($data->getAddress(), 'http://') === 0 || strpos($data->getAddress(), '/') !== false) {
            $errors[] = Mage::helper('factfinder')
                ->__('The server name should contain only the IP address or the domain - no "http://" or any slashes!');
        } elseif (!is_numeric($data->getPort())) {
            $errors[] = Mage::helper('factfinder')->__('The value for "port" must be an integer!');
        } elseif (intval($data->getPort()) < 80) { //is there any http port lower 80?
            $errors[] = Mage::helper('factfinder')->__('The value for "port" must be an integer greater or equals 80!');
        }

        if ($data->getAuthPassword() != '' && $data->getAuthUser() == '') {
            $errors[] = Mage::helper('factfinder')->__('A user name must be provided if a password is to be used.');
        }

        if (count($errors) == 0) {
            $checkStatusHandler = Mage::getSingleton('factfinder/handler_status', $dataArray);
            if (!$checkStatusHandler->checkStatus()) {
                $errors = $checkStatusHandler->getErrorMessages();
            }
        }

        return $errors;
    }


    /**
     * Read data from array given, or if no value given, try to read data from website or global configuration
     *
     * @param array $configData
     *
     * @return array
     */
    protected function _getCompleteFieldsetData($configData)
    {
        $data = array();
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        $storeCode = Mage::app()->getRequest()->getParam('store');

        foreach ($configData as $key => $keyData) {
            if (!isset($keyData['value'])) {
                $path = 'factfinder/search/' . $key;

                if ($storeCode) {
                    $value = Mage::app()->getWebsite($websiteCode)->getConfig($path);
                } else {
                    $value = (string) Mage::getConfig()->getNode('default/' . $path);
                }
            } else {
                $value = $keyData['value'];
            }

            $data[$key] = $value;
        }

        return $data;
    }


}
