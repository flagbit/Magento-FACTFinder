<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

require_once BP.DS.'lib'.DS.'FACTFinder'.DS.'Loader.php';

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
class Flagbit_FactFinder_Model_Adapter
{

	/**
	 * FACT-Finder Searchadapter
	 * @var FACTFinder_Abstract_SearchAdapter
	 */
    protected $_searchAdapter = null;
    
	/**
	 * FACT-Finder Suggestadapter
	 * @var FACTFinder_Abstract_SuggestAdapter
	 */
    protected $_suggestAdapter = null;    
    
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
    protected function _getSearchAdapter($collectParams = true)
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
			
            if($collectParams == true){
            	$this->_collectParams();
            }                    
        }
        
        return $this->_searchAdapter;
    }
    
    protected function _collectParams()
    {
        // search Helper
        $helper = Mage::helper('factfinder/search');    	
    	$_request = Mage::app()->getRequest();
    	
    	switch($_request->getModuleName()){
    		
    		case "xmlconnect":
		    	$this->_setParam('idsOnly', 'true')
		    		->_setParam('productsPerPage', $_request->getParam('count'))
		    		->_setParam('query', $helper->getQuery()->getQueryText())
		    		->_setParam('page', ($_request->getParam('offset') / $_request->getParam('count')) + 1);   
		    		
				// add Sorting Param
				$params = Mage::app()->getRequest()->getParams();		    		
				foreach($params as $key => $value){
					if(substr($key, 0, 6) == 'order_'){
						$key = substr($key, 6);
						if(!in_array($key, array('position', 'relevance'))){
							$this->_setParam('sort'.$key, $value);
						}
					}
				}	

    			 // add Filter Params
		    	$params = Mage::app()->getRequest()->getParams();
		    	foreach($params as $key => $value){
		    		$value = base64_decode($value);  		
		    		if(strpos($value, '|')){
		    			$param = explode('|', $value);
		    			$this->_setParam('filter'.$param[0], $param[1]);
		    		}
		    	}		    		
		    		
    			break;
    			
    		case "catalogsearch":
    		default:	    	
		            // add Default Params
		    	$this->_setParam('idsOnly', 'true')
		    		->_setParam('productsPerPage', $helper->getPageLimit())
		    		->_setParam('query', $helper->getQuery()->getQueryText())
		    		->_setParam('page', $helper->getCurrentPage());
		    	
		    	// add Sorting Param
		    	if($helper->getCurrentOrder() 
		    		&& $helper->getCurrentDirection()
		    		&& $helper->getCurrentOrder() != 'position'
		    		&& $helper->getCurrentOrder() != 'relevance'){		    		
		    			$this->_setParam('sort'.$helper->getCurrentOrder(), $helper->getCurrentDirection());
		    	}
		
		    	// add Filter Params
		    	$params = Mage::app()->getRequest()->getParams();
		    	foreach($params as $key => $value){  		
		    		if(strpos($value, '|')){
		    			$param = explode('|', $value);
		    			switch($param[1]){
		    				
		    				case 'slider':
		    					$subparam = explode(':', $param[2]);
		    					$this->_setParam($subparam[0], $subparam[1]);
		    					$subparam = explode(':', $param[3]);
		    					$this->_setParam($subparam[0], $subparam[1]);	    					
		    					break;
		    					
		    				default:
		    					$this->_setParam('filter'.$param[0], $param[1]);		
		    					break;
		    			}
		    		}
		    	}      			
    			break;
    		
    	}
    }
    
    /**
     * execute search
     */
    public function checkStatus($configarray = null)
    {
        $status = false;
        try {
        	$this->_getConfiguration($configarray);
            $this->_setParam('query', 'FACT-Finder Version');
            $this->_setParam('productsPerPage', '1');

            $status = $this->_getSearchAdapter(false)->getStatus() == 'resultsFound';
        } catch (Exception $e) {
            $status = false;
        }
        return $status;
    }    
    
    /**
     * get Redirect URL if there is set one
     * 
     * @return string
     */
    public function getRedirect()
    {
    	$url = null;
 		$campaigns = $this->_getSearchAdapter()->getCampaigns();
		if (!empty($campaigns) && $campaigns->hasRedirect()) {
			$url = $campaigns->getRedirectUrl();
		} 
    	return $url;	
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
     * get Suggest Adapter
     * 
     * @return FACTFinder_Abstract_SuggestAdapter
     */
    protected function _getSuggestAdapter()
    {
        if ($this->_suggestAdapter == null) {
            $config               = $this->_getConfiguration();
            $encodingHandler      = FF::getSingleton('encodingHandler', $config);
            $params               = $this->_getParamsParser()->getServerRequestParams();
            $dataProvider         = $this->_getDataProvider();
            $this->_suggestAdapter = FF::getSingleton('http/suggestAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
        }
        return $this->_suggestAdapter;
    }

    /**
     * 
     * 
     * @param unknown_type $query
     */
    public function getSuggestResult($query)
    {
		$this->_setParam('query', $query);
		$this->_setParam('format', 'json');

		return Zend_Json_Decoder::decode($this->_getSuggestAdapter()->getSuggestions());		
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
	        			'unit' => $row->getUnit(),
	                	'items' => $this->_getAttributeOptions($row->getArrayCopy()),
	                	'count' => $row->count(), 
	        			'type'	=> $this->_getFilterType($row->getArrayCopy()),
	                	'store_label' => $row->getName() 
	                );
	        	}    		 
	    	}	
    	}

    	return $this->_afterSearchNavigation;
    }
    
    /**
     * get Filter Type by FACT-Finder FilterItem
     * 
     * @param array $options
     * @return string
     */
    protected function _getFilterType($options)
    {	
    	$defaultType = 'item';
    	foreach($options as $option){
    		if(!$option->getType()){
    			continue;
    		}
    		$defaultType = $option->getType();
    		break;
    	}
    	return $defaultType;
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
    		
    		switch ($option->getType()){
				
    			case "slider":
					$attributeOption[] = array(
						'type'	=> $option->getType(),
						'label' => 'slider',
						'value' => $option->getField().'|'.$option->getType().'|'.str_replace(array('&', '='), array('|', ':'), $option->getValue()).'[VALUE]',
						'absolute_min' => $option->getAbsoluteMin(),
						'absolute_max' => $option->getAbsoluteMax(),
						'selected_min' => $option->getSelectedMin(),
						'selected_max' => $option->getSelectedMax(),
						'count' => true,
						'selected' => false //$option->isSelected()
					);
    				break;
    				
    			default:
		    		if (!Mage::helper('core/string')->strlen($option->getValue())) {	
		    			continue;
		    		}	
					$attributeOption[] = array(
						'type'	=> 'attribute',
						'label' => $option->getValue(),
						'value' => $option->getField().'|'.$option->getValue().(isset($selectOptions[$option->getField()])?'~~~'.$selectOptions[$option->getField()]:''),
						'count' => $option->getMatchCount(),
						'selected' => $option->isSelected()
					);
		    			    				
    				break;    				
    			
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
    	Mage::helper('factfinder/debug')->log('set Param:'.$name.' => '.$value);
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
    protected function _getConfiguration($configarray = null)
    {
        if ($this->_config == null) {
            $this->_config = FF::getSingleton('configuration', $configarray);
        }
        return $this->_config;
    }
}
