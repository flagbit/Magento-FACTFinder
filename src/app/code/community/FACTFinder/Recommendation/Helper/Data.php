<?php
/**
 * FACTFinder_Recommendation
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
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
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Recommendation_Helper_Data extends Mage_Core_Helper_Abstract
{

    const EXPORT_TRIGGER_DELAY = 90;
    const IMPORT_TYPE = 'recommendation';

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


    /**
     * Trigger recommendation import
     *
     * @param int $storeId
     *
     * @return void
     */
    public function triggerImport($storeId)
    {
        $exportHelper = Mage::helper('factfinder/export');
        $channel = Mage::helper('factfinder')->getPrimaryChannel($storeId);
        /** @var FACTFinder_Recommendation_Model_Facade $facade */
        $facade = Mage::getModel('factfinder_recommendation/facade');
        $facade->setStoreId($storeId);
        $download = !$exportHelper->useFtp($storeId);
        $delay = $exportHelper->getImportDelay(self::IMPORT_TYPE);

        if ($exportHelper->isImportDelayEnabled($storeId)) {
            $pid = pcntl_fork();
            if (!$pid) {
                sleep($delay);
                $facade->triggerRecommendationImport($channel, $download);
                exit(0);
            }
        } else {
            $facade->triggerRecommendationImport($channel, $download);
        }
    }


}