<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Model class
 * 
 * Adapter between FACT-Finder API Framework and Magento
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
require_once BP.DS.'lib'.DS.'FACTFinder'.DS.'Loader.php';

class Flagbit_FactFinder_Model_Adapter
{

	/**
	 * FACT-Finder Searchadapter
	 * @var FACTFinder_Abstract_SearchAdapter
	 */
    protected $_searchAdapter = null;
    
	/**
	 * FACT-Finder Config
	 * @var FACTFinder_Abstract_Configuration
	 */    
    protected $_config = null;
    
	/**
	 * FACT-Finder Parameter Parser
	 * @var FACTFinder_ParametersParser
	 */    
    protected $_paramsParser = null;
    
	/**
	 * FACT-Finder Data Provider
	 * @var FACTFinder_Abstract_DataProvider
	 */    
    protected $_dataProvider = null;
    
	/**
	 * FACT-Finder After Search Navigation
	 * @var array
	 */    
    protected $_afterSearchNavigation = null;
    
	/**
	 * FACT-Finder Searchadapter
	 * @var array
	 */    
    protected $_searchResultProductIds = null;

    /**
     * get FactFinder SearchAdapter
     * 
     * @return FACTFinder_Abstract_SearchAdapter
     */
    protected function _getSearchAdapter()
    {
        if ($this->_searchAdapter == null) {
            $config              = $this->_getConfiguration();
            $encodingHandler     = FF::getSingleton('encodingHandler', $config);
            $dataProvider        = $this->_getDataProvider();
            $this->_searchAdapter = FF::getSingleton(
            	'xml65/searchAdapter', 
            	$dataProvider, 
            	$this->_getParamsParser(), 
            	$encodingHandler
            );
			
            // search Helper
            $helper = Mage::helper('factfinder/search');
    		
            // add Default Params
	    	$this->_setParam('idsOnly', 'true')
	    		->_setParam('productsPerPage', $helper->getPageLimit())
	    		->_setParam('query', $helper->getQuery()->getQueryText())
	    		->_setParam('page', $helper->getCurrentPage());
	    	
	    	// add Sorting Param
	    	if($helper->getCurrentOrder() 
	    		&& $helper->getCurrentDirection()
	    		&& $helper->getCurrentOrder() != 'position'){
	    			$this->_setParam('sort'.$helper->getCurrentOrder(), $helper->getCurrentDirection());
	    	}

	    	// add Filter Params
	    	$params = Mage::app()->getRequest()->getParams();
	    	foreach($params as $key => $value){  		
	    		if(strpos($value, '|')){
	    			$param = explode('|', $value);
	    			$this->_setParam('filter'.$param[0], $param[1]);
	    		}
	    	}            
        }
        
        return $this->_searchAdapter;
    }
    
    /**
     * get Search Suggest URL
     * 
     * @return string
     */
    public function getSuggestUrl()
    {
    	$dataprovider = $this->_getDataProvider();
    	$dataprovider->setType('Suggest.ff');
    	$dataprovider->setParams(array());
    				
    	return $dataprovider->getNonAuthenticationUrl(); 	
    }
    
    /**
     * get Search Result Count
     * 
     * @return int
     */
    public function getSearchResultCount()
    {
    	return $this->_getSearchAdapter()->getResult()->getFoundRecordsCount();
    }
    
    /**
     * get After Search Navigation as Array
     * this simulates Magento Filter Attributes with Options
     * 
     * @return array
     */
    public function getAfterSearchNavigation()
    {
    	if($this->_afterSearchNavigation == null){
	    	$this->_afterSearchNavigation = array();    	
	    	$result = $this->_getSearchAdapter()->getAsn();
	
	        if ($result instanceof FACTFinder_Asn 
	        	&& count($result)){
	    		
	        	foreach ($result as $row) {
	        		
	        		$this->_afterSearchNavigation[] = array(
	                	'attribute_code' => $row->getName(),
	                	'name' => $row->getName(),
	                	'items' => $this->_getAttributeOptions($row->getArrayCopy()),
	                	'count' => $row->count(), 
	                	'store_label' => $row->getName() 
	                );
	        	}    		 
	    	}	
    	}

    	return $this->_afterSearchNavigation;
    }
    
    /**
     * get Attribute Options Array from FactFinder FilterGroupItems
     * 
     * @param FACTFinder_AsnFilterItem $options
     * @return array
     */
    protected function _getAttributeOptions($options)
    {	
    	$selectOptions = $this->_getSearchAdapter()->getSearchParams()->getFilters();
    	$attributeOption = array();
    	foreach($options as $option){
    		if (Mage::helper('core/string')->strlen($option->getValue())) {		
				$attributeOption[] = array(
					'label' => $option->getValue(),
					'value' => $option->getField().'|'.$option->getValue().(isset($selectOptions[$option->getField()])?'~'.$selectOptions[$option->getField()]:''),
					'count' => $option->getMatchCount(),
					'selected' => $option->isSelected()
				);
    		}	
		}
		return $attributeOption;    	
    }
    
    /**
     * get Search Result Product Ids
     * 
     * @return array Products Ids
     */
    public function getSearchResultProductIds()
    { 	
    	if($this->_searchResultProductIds == null){ 	
	    	$result = $this->_getSearchAdapter()->getResult();
	    	$this->_searchResultProductIds = array();
	    	if($result instanceof FACTFinder_Result){
	    		foreach ($result AS $record){
					$this->_searchResultProductIds[] = $record->getId();
				}
				$this->_searchResultProductIds = array_unique($this->_searchResultProductIds);
	    	}
    	}    	
    	return $this->_searchResultProductIds;
    }
    
    /**
     * set single parameter, which will be looped through to the FACT-Finder request
     *
     * @param string name
     * @param string value
     */
    protected function _setParam($name, $value)
    {
        $this->_getDataProvider()->setParam($name, $value);
        return $this;
    }
    
    /**
     * get FactFinder DataProvider
     * 
     * @return FACTFinder_Abstract_DataProvider
     */
    protected function _getDataProvider()
    {
        if ($this->_dataProvider == null) {
            $config = $this->_getConfiguration();
            $params = $this->_getParamsParser()->getServerRequestParams();
            $this->_dataProvider = FF::getInstance('http/dataProvider', $params, $config);
        }
        return $this->_dataProvider;
    }
    
    /**
     * get Autentivation URL
     * 
     * @return string
     */
    public function getAuthenticationUrl()
    {	
    	$dataprovider = $this->_getDataProvider();
    	$dataprovider->setType('Management.ff');
    	return $dataprovider->getAuthenticationUrl();   	
    }
    
    /**
     * get FactFinder Params Parser
     * 
     * @return FACTFinder_ParametersParser
     */
    protected function _getParamsParser()
    {
        if ($this->_paramsParser == null) {
            $config = $this->_getConfiguration();
            $encodingHandler = FF::getSingleton('encodingHandler', $config);
            $this->_paramsParser = FF::getInstance('parametersParser', $config, $encodingHandler);
        }
        return $this->_paramsParser;
    }
    
    /**
     * get FactFinder Configuration
     * 
     * @return FACTFinder_Abstract_Configuration config
     */
    protected function _getConfiguration()
    {
        if ($this->_config == null) {
            $this->_config = FF::getSingleton('configuration');
        }
        return $this->_config;
    }
}
