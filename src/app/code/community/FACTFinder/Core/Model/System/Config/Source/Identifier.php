<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
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