<?php
/**
 * Handles product campaign data on shopping cart page
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: ProductCampaign.php 14.09.12 08:42 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_ShoppingCartCampaign
    extends Flagbit_FactFinder_Model_Handler_ProductCampaign
{
    protected function _getDoParam()
    {
        return 'getShoppingCartCampaigns';
    }

    protected function _getProductNumberParam()
    {
        if(is_array($this->_productIds))
            return $this->_productIds;
        else
            return array($this->_productIds);
    }
}
