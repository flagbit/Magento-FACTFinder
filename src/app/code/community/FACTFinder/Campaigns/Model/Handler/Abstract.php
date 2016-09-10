<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Model_Handler_Abstract
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Campaigns_Model_Handler_Abstract extends FACTFinder_Core_Model_Handler_Search
{
    /**
     * Model used as facade
     *
     * @var string
     */
    protected $_facadeModel = 'factfinder_campaigns/facade';

    /**
     * Product IDs
     *
     * @var array
     */
    protected $_productIds = array();
    
    /**
     * Page IDs
     *
     * @var string
     */
    protected $_pageId = null;


    /**
     * Available campaigns
     *
     * @var array|null
     */
    protected $_campaigns = null;


    /**
     * Flag of feature availability
     *
     * @var bool
     */
    protected $_featureAvailable = true;


    /**
     * Class constructor
     *
     * @param array $productIds
     */
    public function __construct($productIds = null)
    {
        $this->_productIds = $productIds;
        parent::__construct();
    }


    /**
     * Retrieve redirect url from campaign
     *
     * @return string
     */
    public function getRedirect()
    {
        $url = null;
        $campaigns = $this->getCampaigns();

        if (!empty($campaigns) && $campaigns->hasRedirect()) {
            $url = $campaigns->getRedirectUrl();
        }

        return $url;
    }


    /**
     * Configure all facade settings
     */
    protected function _configureFacade()
    {
        $params = array();

        $params['do'] = $this->_getDoParam();
        if ($this instanceof FACTFinder_Campaigns_Model_Handler_Page) {
            $params['pageId'] = $this->_getPageIdParam();
        } else {
            $params['productNumber'] = $this->_getProductNumberParam();
        }
        $params['idsOnly'] = 'true';
        if(Mage::getStoreConfigFlag('factfinder/config/personalization')) {
            $params['sid'] = Mage::helper('factfinder_tracking')->getSessionId();
        }
        $this->_getFacade()->configureProductCampaignAdapter($params);
    }


    /**
     * Get name of the method to be executed in the adapter
     *
     * @return string
     */
    abstract protected function _getDoParam();

    /**
     * Get array of guestions from advisor campaign
     *
     * @return array
     */
    public function getActiveAdvisorQuestions()
    {
        $campaigns = $this->getCampaigns();

        $questions = array();

        if ($campaigns && $campaigns->hasActiveQuestions()) {
            $questions = $campaigns->getActiveQuestions();
        }

        return $questions;
    }


    /**
     * Get array of campaigns available
     *
     * @return array
     */
    public function getCampaigns()
    {
        if ($this->_featureAvailable && $this->_campaigns === null) {
            $this->_campaigns = $this->_getFacade()->getProductCampaigns();
        }

        // it's still null (disabled or en error happened)
        if ($this->_campaigns === null) {
            $this->_campaigns = array();
        }

        return $this->_campaigns;
    }


}
