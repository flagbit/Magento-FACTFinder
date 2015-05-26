<?php

abstract class FACTFinder_Campaigns_Model_Handler_Abstract extends FACTFinder_Core_Model_Handler_Search
{
    protected $_facadeModel = 'factfinder_campaigns/facade';

    protected $_productIds = array();

    protected $_campaigns;

    protected $_featureAvailable = true;

    public function __construct($productIds)
    {
        $this->_productIds = $productIds;
        parent::__construct();
    }

    public function getRedirect()
    {
        $url = null;
        $campaigns = $this->getCampaigns();

        if (!empty($campaigns) && $campaigns->hasRedirect()) {
            $url = $campaigns->getRedirectUrl();
        }

        return $url;
    }

    protected function _configureFacade()
    {
        $params = array();

        $params['do'] = $this->_getDoParam();
        $params['productNumber'] = $this->_getProductNumberParam();
        $params['idsOnly'] = 'true';

        $this->_getFacade()->configureProductCampaignAdapter($params);
    }

    abstract protected function _getDoParam();

    abstract protected function _getProductNumberParam();

    public function getActiveAdvisorQuestions()
    {
        $campaigns = $this->getCampaigns();

        $questions = array();

        if ($campaigns && $campaigns->hasActiveQuestions()) {
            $questions = $campaigns->getActiveQuestions();
        }

        return $questions;
    }

    public function getCampaigns()
    {
        if (!$this->_featureAvailable) {
            $this->_campaigns = array();
        }

        if ($this->_campaigns === null) {
            $this->_campaigns = $this->_getFacade()->getProductCampaigns();
            if ($this->_campaigns === null) {
                $this->_campaigns = array();
            }
        }

        return $this->_campaigns;
    }

}