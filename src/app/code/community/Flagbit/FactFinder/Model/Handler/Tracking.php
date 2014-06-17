<?php
/**
 * Handles tracking data
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Nicolai Essig <nicolai.essig@flagbit.de>
 * @version     $Id: Tracking.php 20.08.13 11:35 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_Tracking
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    /**
     * @var FACTFinder_Default_TrackingAdapter (or FACTFinder_Default_ScicAdapter which however has the same necessary methods)
     */
    protected $_trackingAdapter;

    /**
     * setting the facade object MUST only be done if there is no app context that can be used to create the necessary
     * objects. In such case this handler will also care about not using the Mage::* methods that require the app.
     *
     * @param Flagbit_FactFinder_Model_Facade $facade
     * @param FACTFinderCustom_Configuration $config
     * @throws Exception if the config is null but the facade is not
     */
    public function __construct(Flagbit_FactFinder_Model_Facade $facade = null, FACTFinderCustom_Configuration $config)
    {
        if ($facade != null) {
            if ($config == null) {
                throw new Exception("implementation error: config can not be null if facade is not null");
            }
            // no app context available.
            $this->_facade = $facade;
            $this->_config = $config;
        }
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFacade()
    {
        if ($this->useOldTracking()) {
            $this->_trackingAdapter = $this->_getFacade()->getScicAdapter();
        } else if ($this->useLegacyTracking()) {
            $this->_trackingAdapter = $this->_getFacade()->getLegacyTrackingAdapter();
        } else {
            $this->_trackingAdapter = $this->_getFacade()->getTrackingAdapter();
        }

    }

    protected function _getFactFinderVersion()
    {
        if ($this->_config == null) {
            $ffVersion = Mage::getStoreConfig('factfinder/search/ffversion');
        } else {
            $ffVersion = $this->_config->getFactFinderVersion();
        }
        return $ffVersion;
    }

    /**
     * Decide whether old tracking should be used (for every FF version up to 6.7)
     *
     * @return bool
     */
    public function useOldTracking()
    {
        $ffVersion = $this->_getFactFinderVersion();
        return ($ffVersion <= 67);
    }

    /**
     * Decide whether legacy tracking should be used (for the FF versions 6.8 and 6.9)
     *
     * @return bool
     */
    public function useLegacyTracking()
    {
        $ffVersion = $this->_getFactFinderVersion();
        return ($ffVersion == 68 && $ffVersion == 69);
    }

    /**
     * method to setup a single event tracking directly from code. This should only be used if an app-context exists.
     *
     * @param $event string should be one of the FACTFinder_Default_TrackingAdapter::EVENT* constants
     * @param $trackingParams array key-value array of the tracking parameters in the new format. If the old format is
     * necessary it will be mapped automatically
     */
    public function setupTracking($event, $trackingParams)
    {
        if ($this->useOldTracking() || $this->useLegacyTracking()) {
            switch ($event) {
                case FACTFinder_Default_TrackingAdapter::EVENT_INSPECT:
                    $searchHelper = $searchHelper = Mage::helper('factfinder/search');
                    $this->_trackingAdapter->setupClickTracking(
                        $trackingParams['id'],
                        $trackingParams['sid'],
                        $searchHelper->getQuery()->getQueryText(),
                        1, //pos
                        1, //origPos
                        1, //page
                        $trackingParams['product']->getSimilarity(),
                        $trackingParams['product']->getName(),
                        $searchHelper->getPageLimit(),
                        $searchHelper->getDefaultPerPageValue());
                    break;
                case FACTFinder_Default_TrackingAdapter::EVENT_CART:
                    $this->_trackingAdapter->setupCartTracking(
                        $trackingParams['id'],
                        $trackingParams['sid'],
                        $trackingParams['amount'],
                        $trackingParams['price'],
                        $trackingParams['uid']
                    );
                    break;
                case FACTFinder_Default_TrackingAdapter::EVENT_BUY:
                    $this->_trackingAdapter->setupCheckoutTracking(
                        $trackingParams['id'],
                        $trackingParams['sid'],
                        $trackingParams['amount'],
                        $trackingParams['price'],
                        $trackingParams['uid']
                    );
                    break;
                default;
                    // do nothing
            }
        } else {
            $this->_trackingAdapter->setupEventTracking($event, $trackingParams);
        }
    }

    public function setupTrackingFromRequest()
    {
        $this->_trackingAdapter->setupTrackingFromRequest();
    }

    /**
     * Fire tracking request. Depending on which tracking is used, it returns the status.
     *
     * @return mixed|null
     */
    public function applyTracking()
    {
        if ($this->useOldTracking()) {
            return $this->_getFacade()->applyScicTracking();
        } else {
            return $this->_getFacade()->applyTracking();
        }
    }
}