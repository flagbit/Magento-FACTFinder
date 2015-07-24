<?php
require_once 'Mage/CatalogSearch/controllers/ResultController.php';
class Flagbit_FactFinder_CatalogSearch_ResultController extends Mage_CatalogSearch_ResultController
{
    /**
     * Display search result
     */
    public function indexAction()
    {
        if(!Mage::helper('factfinder/search')->getIsEnabled()) {
            return parent::indexAction();
        }

        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText() != '') {
            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        } else {
            $this->_redirectReferer();
        }

    }
}