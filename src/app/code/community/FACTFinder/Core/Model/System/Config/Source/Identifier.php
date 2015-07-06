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
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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