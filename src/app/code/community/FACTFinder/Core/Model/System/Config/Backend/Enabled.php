<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * Status Enabled Config Field Backend
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_System_Config_Backend_Enabled extends Mage_Core_Model_Config_Data
{


    /**
     * Check request for errors found by Helper and Observer. It will print error messages if errors found and
     * in that case set value to 0.
     *
     * @return FACTFinder_Core_Model_System_Config_Backend_Enabled
     */
    public function save()
    {
        parent::save();

        Mage::app()->cleanCache();
        $this->_checkConfiguration();

        return $this;
    }


    /**
     * Add message that ff cant be activated and specify reasons
     *
     * @param string $message
     *
     * @return void
     */
    protected function _addError($message = '')
    {
        $message = Mage::helper('factfinder')->__('FACT-Finder cannot be activated:') . '<br />' . $message;
        Mage::getSingleton('adminhtml/session')->addError($message);
    }


    /**
     * Check if the configuration if valid
     *
     * @return FACTFinder_Core_Model_System_Config_Backend_Enabled
     */
    protected function _checkConfiguration()
    {

        if (!$this->getValue()) {
            return $this;
        }

        $groups = Mage::app()->getRequest()->getPost('groups');
        $errors = Mage::helper('factfinder/backend')->checkConfigData($groups['search']['fields']);
        if ($errors) {
            $this->_addError(implode('<br />', $errors));
            $this->setValue('0');
            $this->save();
        }

        return $this;
    }


}
