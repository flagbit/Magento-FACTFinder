<?php
class FACTFinder_Core_Model_System_Config_Source_Protocol
{

    /**
     * Get possible protocols as Option Array
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
                'value' => 'https',
                'label' => Mage::helper('factfinder')->__('https')
            )
        );
    }

}