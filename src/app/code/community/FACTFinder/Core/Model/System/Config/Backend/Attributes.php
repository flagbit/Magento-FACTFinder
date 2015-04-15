<?php

/**
 * Backend for serialized array data
 *
 */
class FACTFinder_Core_Model_System_Config_Backend_Attributes extends Mage_Core_Model_Config_Data
{
    /**
     * Process data after load
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = Mage::helper('factfinder/backend')->unserializeFieldValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $value = Mage::helper('factfinder/backend')->serializeFieldValue($value);
        $this->setValue($value);
    }
}
