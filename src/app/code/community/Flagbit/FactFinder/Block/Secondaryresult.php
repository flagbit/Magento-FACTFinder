<?php
class Flagbit_FactFinder_Block_Secondaryresult extends Mage_Core_Block_Template
{
    protected function getSecondaryResult($channel)
    {
        $adapter = Mage::getSingleton('factfinder/adapter');
		
		$result = $adapter->getSecondarySearchResult($channel);
		
		return $result;
    }
}