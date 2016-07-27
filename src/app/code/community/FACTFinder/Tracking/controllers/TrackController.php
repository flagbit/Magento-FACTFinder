<?php
/**
 * ${FILE_NAME}
 *
 * @category Mage
 * @package magento.ee.1.14.0.1.dev
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

class FACTFinder_Tracking_TrackController extends Mage_Core_Controller_Front_Action
{


    /**
     * @return void
     */
    public function recommendationAction()
    {
        if (!$this->getRequest()->getParam('__ajax')) {
//            $this->_redirectReferer();
//            return;
        }

        /**  @var FACTFinder_Tracking_Model_Handler_Tracking $handler */
        $handler = Mage::getModel('factfinder_tracking/handler_tracking');
        $handler->trackRecommendationClick(

        );
        $handler->applyTracking();

        die('Ololo');
    }
}