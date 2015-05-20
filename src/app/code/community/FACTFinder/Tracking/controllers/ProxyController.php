<?php
class FACTFinder_Tracking_ProxyController extends Mage_Core_Controller_Front_Action
{
    /**
     * tracking Action
     */
    public function trackingAction()
    {
        $this->getResponse()->setBody(
            Mage::getModel('factfinder_tracking/processor')->handleInAppRequest($this->getFullActionName())
        );
    }

    /**
     * scic Action
     */
    public function scicAction()
    {
        $this->trackingAction();
    }
}