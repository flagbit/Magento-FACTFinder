<?php
/**
 * FACTFinder_Recommendation
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Class FACTFinder_Recommendation_Helper_Data
 *
 * Default helper class
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Recommendation_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if import should be triggered for store
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function shouldTriggerImport($storeId)
    {
        if (!Mage::getStoreConfigFlag('factfinder/modules/recommendation', $storeId)) {
            return false;
        }

        return Mage::getStoreConfigFlag('factfinder/export/trigger_recommendation_import', $storeId);
    }


}