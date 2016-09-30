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
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Model_Observer
{


    /**
     * Add suggest handle to the layout
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function addSuggestHandles($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled('suggest')) {
            return;
        }

        $layout = $observer->getLayout();
        $update = $layout->getUpdate();
        $update->addHandle('factfinder_suggest_enabled');
    }


    /**
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function triggerImportAfterExport($observer)
    {
        $file = $observer->getFile();

        if (!$file instanceof FACTFinder_Core_Model_File || !$file->isValid()) {
            return;
        }

        $storeId = $observer->getStoreId();
        $helper = Mage::helper('factfinder_suggest');
        if ($helper->shouldTriggerImport($storeId)) {
            $helper->triggerImport($storeId);
        }
    }


}
