<?php
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

        $this->getResponse()->setHeader("Content-Type:", "application/json;charset=utf-8", true);
        $this->getResponse()->setBody(
            Mage::getModel('factfinder_suggest/processor')->handleInAppRequest($this->getFullActionName())
        );
    }


}
