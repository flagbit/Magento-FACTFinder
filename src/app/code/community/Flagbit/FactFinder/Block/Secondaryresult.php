<?php
class Flagbit_FactFinder_Block_Secondaryresult extends Mage_Core_Block_Template
{
    protected $_searchHandler;

    protected function _prepareLayout()
    {
        if(Mage::helper('factfinder/search')->getIsEnabled()){
            $this->_searchHandler = Mage::getSingleton('factfinder/handler_secondarySearch');
        }
    }

    protected function getSecondaryResult($channel)
    {
        $result = array();
        if(Mage::helper('factfinder/search')->getIsEnabled()){
            $result = $this->_searchHandler->getSecondarySearchResult($channel);
        }
		
		return $result;
    }

    protected  function getSecondarySearchAdapter($channel)
    {
        $adapter = null;
        if(Mage::helper('factfinder/search')->getIsEnabled()){
            $adapter = $this->_searchHandler->getSecondarySearchAdapter($channel);
        }
        return $adapter;
    }
}