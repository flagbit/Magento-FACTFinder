<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * product campaign adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>, Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: ProductCampaignAdapter.php 43440 2012-02-08 12:42:13Z martin.buettner $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_ProductCampaignAdapter extends FACTFinder_Abstract_Adapter
{
    protected $productIds = array();
    protected $isShoppingCartCampaign = false;
    protected $campaignsUpToDate = false;

    protected $campaigns;

    /**
     * @throws Exception if there is no query or no catalog-parameter set at the dataprovider
     */
    protected function getData()
    {
        $params = $this->getDataProvider()->getParams();
        if (empty($params['productNumber'])) {
            $this->log->error("No product number was set.");
            throw new Exception("No product number was set.");
        }
        return $this->getDataProvider()->getData();
    }

    /**
     * Set ids of products to be compared
     *
     * @param array $productIds list of integers
     **/
    public function setProductIds($productIds) {
        $this->productIds = $productIds;
        $this->campaignsUpToDate = false;
        if($this->isShoppingCartCampaign) {
            $this->getDataProvider()->setArrayParam('productNumber',$this->productIds);
        } else {
            $this->getDataProvider()->setParam('productNumber', $this->productIds[0]);
        }
    }

    /**
     * Adds an id to the list of products to be compared
     *
     * @param int $productId
     **/
    public function addProductId($productId) {
        $this->productIds[] = $productId;
        $this->campaignsUpToDate = false;
        if($this->isShoppingCartCampaign) {
            $this->getDataProvider()->setArrayParam('productNumber',$this->productIds);
        } else {
            $this->getDataProvider()->setParam('productNumber', $this->productIds[0]);
        }
    }

    /**
     * Sets the adapter up for fetching campaigns on product detail pages
     **/
    public function makeProductCampaign() {
        $this->isShoppingCartCampaign = false;
        $this->campaignsUpToDate = false;
        $this->getDataProvider()->setParam('do', 'getProductCampaigns');
        $this->getDataProvider()->setParam('productNumber', $this->productIds[0]);
    }

    /**
     * Sets the adapter up for fetching campaigns on shopping cart pages
     **/
    public function makeShoppingCartCampaign() {
        $this->isShoppingCartCampaign = true;
        $this->campaignsUpToDate = false;
        $this->getDataProvider()->setParam('do', 'getShoppingCartCampaigns');
        $this->getDataProvider()->setArrayParam('productNumber',$this->productIds);
    }

    /**
     * returns the campaigns
     *
     * @return FACTFinder_CampaignIterator
     */
    public function getCampaigns() {
        if (!$this->campaigns || $this->campaigns == null) {
            $this->campaigns = $this->createCampaigns();
            $this->campaignsUpToDate == true;
        }
        return $this->campaigns;
    }

    /**
     * create campaigns
     *
     * @return FACTFinder_CampaignIterator
     */
    protected function createCampaigns()
    {
        $this->log->debug("Product Campaigns not supported before FACT-Finder 6.7.");
        $campaigns = array();
        $campaignIterator = FF::getInstance('campaignIterator', $campaigns);
        return $campaignIterator;
    }
}
	