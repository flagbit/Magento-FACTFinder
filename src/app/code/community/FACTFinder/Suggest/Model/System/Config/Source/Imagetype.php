<?php
/**
 * FACTFinder_Suggest
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * Image type source model
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Model_System_Config_Source_Imagetype
{


    /**
     * Get available image types
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach (Mage::getModel('catalog/product')->getMediaAttributes() as $key => $value) {
            $options[] = array(
                'label' => $value->getFrontendLabel(),
                'value' => $key
            );
        }

        return $options;
    }


}

