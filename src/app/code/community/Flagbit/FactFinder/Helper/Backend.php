<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Helper class
 *
 * Helper for backend configurations.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2011 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Michael Türk <tuerk@flagbit.de>
 * @version   $Id: Enabled.php 647 2011-03-21 10:32:14Z rudolf_batt $
 */
class Flagbit_FactFinder_Helper_Backend extends Mage_Core_Helper_Abstract
{

    /**
     * Check configuration data by contacting FACT-Finder servers.
     *
     * @param unknown_type $configData
     */
    public function checkConfigData($configData) {
        $dataArray = $this->_getCompleteFieldsetData($configData);
        $data = new Varien_Object($dataArray);
        $errors  = array();

        if (stripos($data->getAddress(), 'http://') === 0 || strpos($data->getAddress(), '/') !== false) {
            $errors[] = Mage::helper('factfinder')->__('servername should only contain the IP address or the domain - no "http://" or any slashes!');
        }

        if ($data->getPort() == '') {
            $port = 80;
        }
        elseif (!is_numeric($data->getPort())) {
            $errors[] = Mage::helper('factfinder')->__('the value for "port" must be numeric!');
        }
        elseif(intval($data->getPort()) < 80) { //is there any http port lower 80?
            $errors[] = Mage::helper('factfinder')->__('the value for "port" must be a number greater or equals 80!');
        }

        if ($data->getAuthPassword() != '' && $data->getAuthUser() == '') {
            $errors[] = Mage::helper('factfinder')->__('there must be a username, if a password should be used');
        }

        $conflicts = Mage::helper('factfinder/debug')->getRewriteConflicts();
        if(count($conflicts)){
            foreach($conflicts as $moduleClass => $externalClass){
                $errors[] = Mage::helper('factfinder')->__('There is a Class Rewrite Conflict: "%s" already overwritten by "%s"', $moduleClass, $externalClass);
            }
        }

        if (count($errors) == 0) {
            $adapter = Mage::getSingleton('factfinder/adapter');
            if(!$adapter->checkStatus($dataArray)){
                $errors[] = Mage::helper('factfinder')->__('WARNING: was not able to connect to FACT-Finder.');
            }
        }

        return $errors;
    }



    /**
     * Read data from array given, or if no value given, try to read data from website or global configuration
     *
     * @param array $configData
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
                }
                else {
                    $value = (string) Mage::getConfig()->getNode('default/' . $path);
                }
            }
            else {
                $value = $keyData['value'];
            }

            $data[$key] = $value;
        }

        return $data;
    }
    
    /**
     * Generate a storable representation of a value
     *
     * @param mixed $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        if (is_array($value)) {
            $data = array();
            foreach ($value as $rule => $setup) {
            	if($rule == '__empty') {
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
     * @param array
     * @return array
     */
    protected function _encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $set => $value) {
            $_id = Mage::helper('core')->uniqHash('_');
            $result[$_id] = $value;
        }
        return $result;
    }

    /**
     * Decode value from used in Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
     *
     * @param array
     * @return array
     */
    protected function _decodeArrayFieldValue(array $value)
    {
        $result = array();
        unset($value['__empty']);
        foreach ($value as $_id => $row) {
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
     * @return array
     */
    public function makeArrayFieldValue($value)
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
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $value = $this->_serializeValue($value);
        return $value;
    }     
    
}