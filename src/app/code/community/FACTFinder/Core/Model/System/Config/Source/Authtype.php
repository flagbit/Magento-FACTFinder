<?php
/**
 * Model class
 *
 * Provides Authtype Options
 *
 */
class FACTFinder_Core_Model_System_Config_Source_Authtype
{

    /**
     * Get authtypes as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'http',
                'label' => Mage::helper('factfinder')->__('http')
            ),
            array(
                'value' => 'simple',
                'label' => Mage::helper('factfinder')->__('simple')
            ),
            array(
                'value' => 'advanced',
                'label' => Mage::helper('factfinder')->__('advanced')
            )
        );
    }

}
