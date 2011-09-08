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
}