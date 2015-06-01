<?php
class FACTFinder_Suggest_ProxyController extends Mage_Core_Controller_Front_Action
{


    /**
     * Suggest Action
     */
    public function suggestAction()
    {
        $this->getResponse()->setHeader("Content-Type:", "text/javascript;charset=utf-8", true);
        $this->getResponse()->setBody(
            Mage::getModel('factfinder_suggest/processor')->handleInAppRequest($this->getFullActionName())
        );
    }


}
