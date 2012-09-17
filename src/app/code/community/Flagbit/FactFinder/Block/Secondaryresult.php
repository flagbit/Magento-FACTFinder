<?php
class Flagbit_FactFinder_Block_Secondaryresult extends Mage_Core_Block_Template
{
    protected $_searchHandler;

    protected function _prepareLayout()
    {
        $this->_searchHandler = Mage::getSingleton('factfinder/handler_secondarySearch');
    }

    protected function getSecondaryResult($channel)
    {
		$result = $this->_searchHandler->getSecondarySearchResult($channel);
		
		return $result;
    }
}