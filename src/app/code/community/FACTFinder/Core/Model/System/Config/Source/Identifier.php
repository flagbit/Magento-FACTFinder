<?php
class FACTFinder_Core_Model_System_Config_Source_Identifier
{
    /**
     * Get possible identifiers as Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'entity_id',
                'label' => Mage::helper('factfinder')->__('Product ID (default)')
            ),
            array(
                'value' => 'sku',
                'label' => Mage::helper('factfinder')->__('Product SKU')
            )
        );
    }
}