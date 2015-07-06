<?php
/**
 * FACTFinder_Suggest
 *
 * @category Mage
 * @package FACTFinder_Suggest
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
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Model_Observer
{


    /**
     * Add suggest handle to the layout
     *
     * @param $observer
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


}
