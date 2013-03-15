<?php
/**
 * Handles product campaign data
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: ProductCampaign.php 14.09.12 08:42 $
 *
 **/
abstract class Flagbit_FactFinder_Model_Handler_ProductCampaign
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_productIds = array();

    protected $_campaigns;

    protected $_featureAvailable = true;

    public function __construct($productIds)
    {
        $this->_productIds = $productIds;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFacade()
    {
        $params = array();
        $params['do'] = $this->_getDoParam();
        $params['productNumber'] = $this->_getProductNumberParam();
        $params['idsOnly'] = 'true';

        try {
            $adapter = $this->_getFacade()->configureProductCampaignAdapter($params);
        } catch(Exception $e) {
            Mage::helper('factfinder/debug')->log('Product Campaigns not available before FACT-Finder 6.7.');
            $this->_featureAvailable = false;
            return;
        }
    }

    abstract protected function _getDoParam();
    abstract protected function _getProductNumberParam();

    public function getActiveAdvisorQuestions()
    {
        $campaigns = $this->getCampaigns();

        $questions = array();

        if($campaigns && $campaigns->hasActiveQuestions()){
            $questions = $campaigns->getActiveQuestions();
        }

        return $questions;
    }

    public function getCampaigns()
    {
        if(!$this->_featureAvailable)
            $this->_campaigns = array();

        if($this->_campaigns === null)
        {
            $this->_campaigns = $this->_getFacade()->getProductCampaigns();
            if ($this->_campaigns === null)
                $this->_campaigns = array();
        }
        return $this->_campaigns;
    }
}
