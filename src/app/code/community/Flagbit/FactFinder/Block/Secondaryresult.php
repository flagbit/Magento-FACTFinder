<?php
class Flagbit_FactFinder_Block_Secondaryresult extends Mage_Core_Block_Template
{
    protected function getSecondaryResult($channel)
    {
        $facade = Mage::getSingleton('factfinder/facade');
		
		$result = $facade->getSecondarySearchResult($channel);
		
		return $result;
    }
}