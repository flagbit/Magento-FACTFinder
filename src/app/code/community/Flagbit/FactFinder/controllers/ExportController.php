<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Controller class
 * 
 * This class the Export Controller
 * It provides a Products, Prices and Stocks Export
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_ExportController extends Mage_Core_Controller_Front_Action {
    
    const XML_AUTH_PASSWORD_PATH    = 'factfinder/search/auth_password';
    
    /**
     * handle Export Authentification
     * 
     * @return Mage_Core_Controller_Varien_Action
     */
    public function preDispatch()
    {
        $this->_getStoreId();
        $password = md5(Mage::getStoreConfig(self::XML_AUTH_PASSWORD_PATH));
        
        if ($password == '' || $password != $this->getRequest()->getParam('key')) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::preDispatch();
    }
    
    /**
     * get current Store ID
     * 
     * @return int
     */
    protected function _getStoreId()
    {
        if ($storeId = $this->getRequest()->getParam('store')) {
            Mage::app()->setCurrentStore($storeId);
        }
        
        return Mage::app()->getStore()->getId();
    }
    
    /**
     * Initialize Product Export 
     */
    public function productAction()
    {        
        $exportModel = Mage::getModel('factfinder/export_product');
        $exportModel->doExport(
            $this->_getStoreId()
        );
    }
    
    /**
     * Initialize Price Export 
     */    
    public function priceAction()
    {    
        $exportModel = Mage::getModel('factfinder/export_price');
        $exportModel->doExport(
            $this->_getStoreId()
        );
    }

    /**
     * Initialize Stock Export 
     */    
    public function stockAction()
    {    
        $exportModel = Mage::getModel('factfinder/export_stock');
        $exportModel->doExport(
            $this->_getStoreId()
        );
    }
}