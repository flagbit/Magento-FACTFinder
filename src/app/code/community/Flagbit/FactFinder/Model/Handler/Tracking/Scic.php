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
     * @deprecated this class is not necessary anymore and completely replaced by handler/tracking
     * @return FACTFinder_Default_ScicAdapter
     */
    public function setupEventTracking($event, $inputParams)
    {
        Mage::getModel('factfinder/handler_tracking')->setupTracking($event, $inputParams);
    }
}