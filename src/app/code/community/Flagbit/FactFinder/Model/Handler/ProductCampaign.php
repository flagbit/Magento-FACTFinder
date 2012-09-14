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
class Flagbit_FactFinder_Model_Handler_ProductCampaign
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_productIds = array();

    protected $_campaigns;

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
        $adapter = $this->_getFacade()->getProductCampaignAdapter();
        $adapter->makeProductCampaign();
        $adapter->setParam('idsOnly', 'true');
        $adapter->setProductIds($this->_productIds);
    }

    public function getCampaigns()
    {
        if($this->_campaigns === null)
        {
            $this->_campaigns = $this->_getFacade()->getProductCampaigns();
            if ($this->_campaigns === null)
                $this->_campaigns = array();
        }
        return $this->_campaigns;
    }
}
