<?php

if(Mage::helper('factfinder/search')->getIsOnSearchPage()){
	class Flagbit_FactFinder_Model_Layer_Abstract extends Mage_CatalogSearch_Model_Layer {}
}else{
	class Flagbit_FactFinder_Model_Layer_Abstract extends Mage_Catalog_Model_Layer {}	
}