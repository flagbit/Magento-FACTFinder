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
 * Provides Authtype Options
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
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
