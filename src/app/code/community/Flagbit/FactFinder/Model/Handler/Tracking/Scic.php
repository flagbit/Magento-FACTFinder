<?php
/**
 * Creates a mapping between new and old tracking methods
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Nicolai Essig <nicolai.essig@flagbit.de>
 * @version     $Id: Scic.php 26.08.13 15:05 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_Tracking_Scic
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected function configureFacade() {}

    /**
     * Mapping method from new -> old tracking
     *
     * @param $event
     * @param $inputParams
     * @return FACTFinder_Default_ScicAdapter
     */
    public function setupEventTracking($event, $inputParams)
    {
        /* @var $scicAdapter FACTFinder_Default_ScicAdapter */
        $scicAdapter = $this->_getFacade()->getScicAdapter();

        switch ($event) {
            case FACTFinder_Default_TrackingAdapter::EVENT_INSPECT:
                $searchHelper = $searchHelper = Mage::helper('factfinder/search');
                $scicAdapter->setupClickTracking(
                    $inputParams['id'],
                    $inputParams['sid'],
                    $searchHelper->getQuery()->getQueryText(),
                    1, //pos
                    1, //origPos
                    1, //page
                    $inputParams['product']->getSimilarity(),
                    $inputParams['product']->getName(),
                    $searchHelper->getPageLimit(),
                    $searchHelper->getDefaultPerPageValue());
                break;
            case FACTFinder_Default_TrackingAdapter::EVENT_CART:
                $scicAdapter->setupCartTracking(
                    $inputParams['id'],
                    $inputParams['sid'],
                    $inputParams['amount'],
                    $inputParams['price'],
                    $inputParams['uid']
                );
                break;
            case FACTFinder_Default_TrackingAdapter::EVENT_BUY:
                $scicAdapter->setupCheckoutTracking(
                    $inputParams['id'],
                    $inputParams['sid'],
                    $inputParams['amount'],
                    $inputParams['price'],
                    $inputParams['uid']
                );
                break;
            case FACTFinder_Default_TrackingAdapter::EVENT_DISPLAY:
            case FACTFinder_Default_TrackingAdapter::EVENT_FEEDBACK:
            case FACTFinder_Default_TrackingAdapter::EVENT_AVAILABILITY_CHECK:
            case FACTFinder_Default_TrackingAdapter::EVENT_CACHE_HIT:
            case FACTFinder_Default_TrackingAdapter::EVENT_SESSION_START:
                // Not implemented yet
                break;
        }

        return $scicAdapter;
    }
}