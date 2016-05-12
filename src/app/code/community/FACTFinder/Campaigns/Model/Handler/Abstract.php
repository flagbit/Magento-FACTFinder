<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Model_Handler_Abstract
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function __construct($productIds)
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
        $params['productNumber'] = $this->_getProductNumberParam();
        $params['idsOnly'] = 'true';

        $this->_getFacade()->configureProductCampaignAdapter($params);
    }


    /**
     * Get name of the method to be executed in the adapter
     *
     * @return string
     */
    abstract protected function _getDoParam();


    /**
     * Get array of product ids
     *
     * @return array
     */
    abstract protected function _getProductNumberParam();


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
        if ($this->_featureAvailable && $this->_campaigns === null && !empty($this->_productIds)) {
            $this->_campaigns = $this->_getFacade()->getProductCampaigns();
        }

        // it's still null (disabled or en error happened)
        if ($this->_campaigns === null) {
            $this->_campaigns = array();
        }

        return $this->_campaigns;
    }


}
