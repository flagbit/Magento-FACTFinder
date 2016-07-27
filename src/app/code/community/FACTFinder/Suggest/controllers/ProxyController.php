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
 * Controller class
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_ProxyController extends Mage_Core_Controller_Front_Action
{


    /**
     * Suggest Action
     */
    public function suggestAction()
    {
        if (!Mage::helper('factfinder')->isEnabled('suggest')) {
            return;
        }

        $this->getResponse()->setHeader("Content-Type", "application/json;charset=utf-8", true);
        $this->getResponse()->setBody(
            Mage::getModel('factfinder_suggest/processor')->handleInAppRequest($this->getFullActionName())
        );
    }


}
