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
 * @version   $Id: Adapter.php 906 2011-09-30 14:10:05Z tuerk $
 */
class Flagbit_FactFinder_Model_Adapter
{

    /**
     * FACT-Finder search adapter
     * @var FACTFinder_Abstract_SearchAdapter
     */
    protected $_searchAdapter = null;
	
	/**
	 * FACT-Finder search adapters for secondary channels
	 * @var array of FACTFinder_Abstract_SearchAdapter
	 **/
	protected $_secondarySearchAdapters = null;

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
     * FACT-Finder Scic Adapter
     * @var FACTFinder_Abstract_ScicAdapter
     */
    protected $_scicAdapter = null;

    /**
     * FACT-Finder Recommendation Adapter
     * @var FACTFinder_Abstract_RecommendationAdapter
     */
    protected $_recommendationAdapter = null;

    /**
     * FACT-Finder Product Campaign Adapter
     * @var FACTFinder_Abstract_ProductCampaignAdapter
     */
    protected $_productCampaignAdapter = null;

    /**
     * FACT-Finder TagCloudadapter
     * @var FACTFinder_Abstract_TagCloudAdapter
     */
    protected $_tagCloudAdapter = null;

    /**
     * FACT-Finder After Search Navigation
     * @var array
     */
    protected $_afterSearchNavigation = null;

    /**
     * FACT-Finder product IDs of primary search result
     * @var array
     */
    protected $_searchResultProductIds = null;

	/**
     * FACT-Finder secondary search results
     * @var array
     */
    protected $_secondarySearchResults = null;
	
	/**
     * FACT-Finder secondary suggest results
     * @var array
     */
    protected $_secondarySuggestResults = null;

	
    /**
     * current FACT-Finder Category Path
     * @var array
     */
    protected $_currentFactfinderCategoryPath = null;
    
	/**
	 * logger object to log all module interna
	 * @var FACTFinder_Abstract_Logger
	 */
	protected $_logger = null;
    
    public function __construct($arg = null)
    {
        if ($arg != null && $arg instanceof FACTFinder_Abstract_Logger) {
            FF::setLogger($arg);
			$this->_logger = $arg;
        } else {
            $logger = Mage::helper('factfinder/debug');
            FF::setLogger($logger);
			$this->_logger = $logger;
        }
    }

    /**
     * get FactFinder SearchAdapter
     *
     * @return FACTFinder_Abstract_SearchAdapter
     */
    protected function _getSearchAdapter($collectParams = true)
    {
        if ($this->_searchAdapter == null)
		{
			$channels = $this->_getConfiguration()->getSecondaryChannels();
			if(empty($channels))
			{
				$this->_loadSearchAdapter($collectParams);
			}
			else
			{
				$this->_loadAllSearchAdapters($collectParams);
			}
        }

        return $this->_searchAdapter;
    }
	
	/**
     * get a (new) FactFinder SearchAdapter for a secondary channel
     *
     * @return FACTFinder_Abstract_SearchAdapter
     */
    protected function _getSecondarySearchAdapter($channel)
    {
		$config              = $this->_getConfiguration();
		$encodingHandler     = FF::getSingleton('encodingHandler', $config);
		$dataProvider        = $this->_getParallelDataProvider();
		
		// Overwrite the channel set by the configuration
		$dataProvider->setParam('channel', $channel);
		$dataProvider->setParam('query', Mage::helper('factfinder/search')->getQueryText());
		
		$searchAdapter = FF::getInstance(
			'xml'.$this->_getConfiguration()->getFactFinderVersion().'/searchAdapter',
			$dataProvider,
			$this->_getParamsParser(),
			$encodingHandler
		);

        return $searchAdapter;
    }
	
	/**
	 * loads the main search adapter
	 **/
	 
	protected function _loadSearchAdapter($collectParams = true, $parallel = false)
	{
		$config					= $this->_getConfiguration();
		$encodingHandler		= FF::getSingleton('encodingHandler', $config);
		if(!$parallel)
		{
			$dataProvider			= $this->_getGlobalDataProvider();
		}
		else
		{
			$dataProvider			= $this->_getParallelDataProvider();
			if($this->_globalDataProviderExists())
				$dataProvider->setParams($this->_getGlobalDataProvider()->getParams());
			$this->_setGlobalDataProvider($dataProvider);
		}
		
		$this->_searchAdapter	= FF::getSingleton(
			'xml'.$this->_getConfiguration()->getFactFinderVersion().'/searchAdapter',
			$dataProvider,
			$this->_getParamsParser(),
			$encodingHandler
		);

		if($collectParams == true){
			$this->_collectParams();
		}
	}
	
	/**
	 * loads the main search adapter and all search adapters for secondary channels
	 *
	 **/
	protected function _loadAllSearchAdapters($collectParams = true)
	{
		$this->_loadSearchAdapter($collectParams, true);
		
		$channels = $this->_getConfiguration()->getSecondaryChannels();
		
		foreach($channels AS $currentChannel)
		{
			try {
				$this->_secondarySearchAdapters[$currentChannel] = $this->_getSecondarySearchAdapter($currentChannel);
			}
			catch (Exception $e) {
				Mage::logException($e);
			}
		}
	}

	/**
	 * prepares all request parameters for the primary search adapter
	 *
	 * @param FACTFinder_Abstract_DataProvider	the data provider object to fill with the params
	 **/
	
    protected function _collectParams($dataProvider)
    {
        // search Helper
        $helper = Mage::helper('factfinder/search');
        $_request = Mage::app()->getRequest();
        $params = $this->_getParamsParser()->getRequestParams();

        if (strpos(Mage::getStoreConfig('factfinder/config/internal_ip'), Mage::helper('core/http')->getRemoteAddr()) !== false) {
            $this->_setParam('log', 'internal', true, $dataProvider);
        }

        switch($_request->getModuleName()){
            
            case "xmlconnect":
                $_query = $helper->getQueryText();
                $this->_setParam('idsOnly', 'true', true, $dataProvider)
                    ->_setParam('query', $_query, true, $dataProvider);
                
                $count = $params['count'];
                if ($count > 0) {
                    $this->_setParam('productsPerPage', $count, true, $dataProvider)
                         ->_setParam('page', ($params['offset'] / $count) + 1, true, $dataProvider);
                }

                // add Sorting Param
                foreach($params as $key => $value){
                    if(substr($key, 0, 6) == 'order_'){
                        $key = substr($key, 6);
                        if(!in_array($key, array('position', 'relevance'))){
                            $this->_setParam('sort'.$key, $value, true, $dataProvider);
                        }
                    }
                }

                 // add Filter Params
                foreach($params as $key => $value){
                    $value = base64_decode($value);
                    if(strpos($value, '|')){
                        $param = explode('|', $value);
                        if($key == 'Category'){
                            $categories = array_merge(array_slice(explode('/', $param[0]), 1), array($param[1]));
                            foreach($categories AS $k => $v) { $categories[$k] = urldecode($v); }
                            $filterkey = '';
                            foreach($categories as $category){
                                $category = str_replace('%2F', '/', str_replace('%2B', '+', $category));
                                $this->_setParam('filtercategoryROOT'.$filterkey, $category, true, $dataProvider);
                                $filterkey .= '/'.str_replace('+', '%2B', str_replace('/', '%2F', $category));
                            }
                        }else{
                            $this->_setParam('filter'.$param[0], $param[1], true, $dataProvider);
                        }
                    }
                }

                break;

            case "catalog":
                $_query = '*';
                if (!isset($params['Category'])) {
                    $params['Category'] = $this->_getCurrentFactfinderCategoryPath();
                }

            case "catalogsearch":
            default:
                if ($_request->getModuleName() == 'catalogsearch') {
                    $_query = $helper->getQueryText();
                }
                
                // add Default Params
                $this->_setParam('idsOnly', 'true', true, $dataProvider)
                    ->_setParam('productsPerPage', $helper->getPageLimit(), true, $dataProvider)
                    ->_setParam('query', $_query, true, $dataProvider)
                    ->_setParam('page', $helper->getCurrentPage(), true, $dataProvider);

                // add Sorting Param, but only if it was set explicit via url
                foreach($params as $key => $value){
                    if($key == 'order'
                    && $helper->getCurrentOrder()
                    && $helper->getCurrentDirection()
                    && $helper->getCurrentOrder() != 'position'
                    && $helper->getCurrentOrder() != 'relevance'){
                        $this->_setParam('sort'.$helper->getCurrentOrder(), $helper->getCurrentDirection(), true, $dataProvider);
                    }
                }

                // add Filter Params
                foreach($params as $key => $value){
                    if(strpos($value, '|')){
                        $param = explode('|', $value);
                        switch($param[1]){

                            case 'slider':
                                $subparam = explode(':', $param[2]);
                                $this->_setParam($subparam[0], $subparam[1], true, $dataProvider);
                                $subparam = explode(':', $param[3]);
                                $this->_setParam($subparam[0], $subparam[1], true, $dataProvider);
                                break;

                            default:
                                if($key == 'Category'){
                                    $categories = array_merge(array_slice(explode('/', $param[0]), 1), array($param[1]));
                                    foreach($categories AS $k => $v) { $categories[$k] = $v; }
                                    $filterkey = '';
                                    foreach($categories as $category){
                         $category = str_replace('%2F', '/', str_replace('%2B', '+', $category));
                                        $this->_setParam('filtercategoryROOT'.$filterkey, $category, true, $dataProvider);
                                        $filterkey .= '/'.str_replace('+', '%2B', str_replace('/', '%2F', $category));
                                    }

                                }else{
                                    $this->_setParam('filter'.$param[0], $param[1], true, $dataProvider);
                                }
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
        $campaigns = $this->getCampaigns();

        if (!empty($campaigns) && $campaigns->hasRedirect()) {
            $url = $campaigns->getRedirectUrl();
        }
        return $url;
    }

    /**
     *
     */
    public function getCampaigns()
    {
        $campaigns = null;
        try {
			$searchAdapter = $this->_getSearchAdapter();
			FACTFinder_Http_ParallelDataProvider::loadAllData();
            $campaigns = $searchAdapter->getCampaigns();
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
        return $campaigns;
    }

    /**
     * get Search Suggest URL
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        $dataprovider = $this->_getGlobalDataProvider();
        $dataprovider->setType('Suggest.ff');
        $dataprovider->setParams(array());

        return $dataprovider->getNonAuthenticationUrl();
    }

    /**
     * get Suggest Adapter
     *
	 * @param	string	$query		query param for FF request
	 * @param	string	$format		format param for FF request
	 * @param	bool	$parallel	use a parallel data provider if true; use the default one, otherwise
	 
     * @return FACTFinder_Abstract_SuggestAdapter
     */
    protected function _getSuggestAdapter($query, $format, $parallel = false)
    {
        if ($this->_suggestAdapter == null) {
            $config					= $this->_getConfiguration();
            $encodingHandler		= FF::getSingleton('encodingHandler', $config);
            $params					= $this->_getParamsParser()->getServerRequestParams();
			if($parallel)
				$dataProvider		= $this->_getParallelDataProvider();
			else
				$dataProvider		= $this->_getGlobalDataProvider();

            $this->_suggestAdapter	= FF::getInstance('http/suggestAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
        }
		
		$dataProvider->setParam('query', $query);
		$dataProvider->setParam('format', $format);

        return $this->_suggestAdapter;
    }
	
	/**
     * get a (new) FactFinder SearchAdapter for a secondary channel
     *
     * @return FACTFinder_Abstract_SuggestAdapter
     */
    protected function _getSecondarySuggestAdapter($channel, $query, $format)
    {
		$config              = $this->_getConfiguration();
		$encodingHandler     = FF::getSingleton('encodingHandler', $config);
		$dataProvider        = $this->_getGlobalDataProvider();
				
		// Overwrite the channel set by the configuration
		$dataProvider->setParam('channel', $channel);
		$dataProvider->setParam('query', $query);
		$dataProvider->setParam('format', $format);
		
		$suggestAdapter = FF::getInstance(
			'http/suggestAdapter',
			$dataProvider,
			$this->_getParamsParser(),
			$encodingHandler
		);

        return $suggestAdapter;
    }

    /**
     * get Suggest Results as Array
     *
     * @param string $query
     * @return array
     */
    public function getSuggestResult($query)
    {
        return Zend_Json_Decoder::decode($this->_getSuggestAdapter($query, 'json', false)->getSuggestions());
    }
	
    /**
     * get Suggest Results as JSON
     *
     * @param string $query
     * @return string json
     */
    public function getSuggestResultJsonp($query, $jqueryCallback)
    {
        return $this->_getSuggestAdapter($query, 'jsonp', false)->getSuggestions();
    }
	
	/**
     * get Suggest Results for primary and all secondary channels in parallel as JSON
     *
     * @param string $query
     * @return string json
     */
    public function getAllSuggestResultsJsonp($query, $jqueryCallback)
    {
		
        $primarySuggestAdapter = $this->_getSuggestAdapter($query, 'json', true);
		
		// load adapters for secondary channels
		
		$channels = $this->_getConfiguration()->getSecondaryChannels();
		
        $secondarySuggestAdapters = array();
			
		foreach($channels AS $channel)
		{
			try {
				$secondarySuggestAdapters[$channel] = $this->_getSecondarySuggestAdapter($channel, $query, 'json');
			}
			catch (Exception $e) {
				Mage::logException($e);
			}
		}
		
		FACTFinder_Http_ParallelDataProvider::loadAllData();
		
		$suggestResult = Zend_Json_Decoder::decode($primarySuggestAdapter->getSuggestions());
		foreach($suggestResult as &$item)
		{
			$item["channel"] = $this->_getConfiguration()->getChannel();
		}
		
		foreach($secondarySuggestAdapters AS $channel => $suggestAdapter)
		{
			try {
				$result = Zend_Json_Decoder::decode($suggestAdapter->getSuggestions());
				foreach($result as &$item)
				{
					$item["channel"] = $channel;
				}
				$suggestResult = array_merge($suggestResult, $result); 
			}
			catch (Exception $e) {
				Mage::logException($e);
			}
		}
		
		return $jqueryCallback.'('.Zend_Json_Encoder::encode($suggestResult).');'; //print_r($suggestResult,true); //
    }
    
    /**
    * get FactFinder TagCloudAdapter
    *
    * @return FACTFinder_Abstract_TagCloudAdapter
    */
    protected function _getTagCloudAdapter()
    {
        if ($this->_tagCloudAdapter == null) {
            $config              = $this->_getConfiguration();
            $encodingHandler     = FF::getSingleton('encodingHandler', $config);
            $dataProvider        = $this->_getDataProvider();
            $this->_tagCloudAdapter = FF::getSingleton(
                'xml'.$this->_getConfiguration()->getFactFinderVersion().'/tagCloudAdapter',
                $dataProvider,
                $this->_getParamsParser(),
                $encodingHandler
            );
        }
    
        return $this->_tagCloudAdapter;
    }

    /**
     * get tag cloud information as Array
     *
     * @param string $query
     * @return array
     */
    public function getTagCloud()
    {
		try {
			return $this->_getTagCloudAdapter()->getTagCloud();
		} catch (Exception $e) {
            Mage::logException($e);
			return array();
        }
    }

    /**
     * get Scic Adapter
     *
     * @return FACTFinder_Abstract_ScicAdapter
     */
    public function getScicAdapter()
    {
        if ($this->_scicAdapter == null) {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $params            = $this->_getParamsParser()->getServerRequestParams();
            $dataProvider      = $this->_getGlobalDataProvider();
            $this->_scicAdapter = FF::getSingleton('http/scicAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
        }
        return $this->_scicAdapter;
    }

    /**
     * get Recommendation Adapter
     *
     * @return FACTFinder_Abstract_RecommendationAdapter
     */
    public function getRecommendationAdapter()
    {
        if ($this->_recommendationAdapter == null) {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $params            = $this->_getParamsParser()->getServerRequestParams();
            $dataProvider      = $this->_getGlobalDataProvider();
            $dataProvider->setParam('idsOnly', 'true');
            $this->_recommendationAdapter = FF::getSingleton('xml'.$this->_getConfiguration()->getFactFinderVersion().'/recommendationAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
        }
        return $this->_recommendationAdapter;
    }
    
    /**
     * get Product Campaign Adapter
     *
     * @return FACTFinder_Abstract_ProductCampaignAdapter
     */
    public function getProductCampaignAdapter()
    {
		// Note: this will only work as long as version numbers are used with the same amount of decimal points
		if ($this->_getConfiguration()->getFactFinderVersion() < 67)
			throw new Exception('Feature not supported by used FACT-Finder version.');
			
        if ($this->_productCampaignAdapter == null) {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $params            = $this->_getParamsParser()->getServerRequestParams();
            $dataProvider      = $this->_getGlobalDataProvider();
            $dataProvider->setParam('idsOnly', 'true');
            $this->_productCampaignAdapter = FF::getSingleton('xml'.$this->_getConfiguration()->getFactFinderVersion().'/productCampaignAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
        }
        return $this->_productCampaignAdapter;
    }

    /**
     * get Search Result Count
     *
     * @return int
     */
    public function getSearchResultCount()
    {
        $count = 0;
        try {
			$searchAdapter = $this->_getSearchAdapter();
			FACTFinder_Http_ParallelDataProvider::loadAllData();
            $count = $searchAdapter->getResult()->getFoundRecordsCount();
        }
        catch (Exception $e) {
            Mage::logException($e);
        }

        return $count;
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

            $result = array();
            try {
				$searchAdapter = $this->_getSearchAdapter();
				FACTFinder_Http_ParallelDataProvider::loadAllData();
                $result = $searchAdapter->getAsn();
            }
            catch (Exception $e) {
                Mage::logException($e);
            }


            if ($result instanceof FACTFinder_Asn
                && count($result)){

                foreach ($result as $row) {
                    $this->_afterSearchNavigation[] = array(
                        'attribute_code' => $row->getName(),
                        'name' => $row->getName(),
                        'unit' => $row->getUnit(),
                        'items' => $this->_getAttributeOptions($row->getArrayCopy(), $row->getUnit()),
                        'count' => $row->count(),
                        'type'    => $this->_getFilterType($row->getArrayCopy()),
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
    protected function _getAttributeOptions($options, $unit = '')
    {
        $attributeOption = array();
        if (!empty($unit)) $unit = ' ' . $unit;
        $_currentCategoryPath = $this->_getCurrentFactfinderCategoryPath(true);
        $helper = Mage::helper('factfinder/search');
        foreach($options as $option){

            switch ($option->getType()){

                case "slider":
                    $attributeOption[] = array(
                        'type'    => $option->getType(),
                        'label' => 'slider',
                        'value' => $this->_getAttributeOptionValue($option),
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
                    // remove Categories from top Level Navigation
                    $_value = $this->_getAttributeOptionValue($option);
                    if(Mage::getStoreConfigFlag('factfinder/activation/navigation')
                        && !$helper->getIsOnSearchPage()
                        && (
                        empty($_value) === true
                        || in_array($_value, $_currentCategoryPath)
                            && $_currentCategoryPath[count($_currentCategoryPath)-1] != $_value
                        )){
                            continue;
                    }

                    $attributeOption[] = array(
                        'type'    => 'attribute',
                        'label' => $option->getValue() . $unit,
                        'value' => $_value,
                        'count' => $option->getMatchCount(),
                        'selected' => $option->isSelected(),
                        'clusterLevel' => $option->getClusterLevel()
                    );
                    break;
            }
        }
        return $attributeOption;
    }

    /**
     * get current FACT-Finder Catgory Path
     *
     * @return string
     */
    protected function _getCurrentFactfinderCategoryPath($all = false)
    {
        $returnValue = '';
        if($this->_currentFactfinderCategoryPath == null && Mage::getStoreConfigFlag('factfinder/activation/navigation') && Mage::registry('current_category')){
            $this->_currentFactfinderCategoryPath = array();
            /* @var $category Mage_Catalog_Model_Category */
            $category = Mage::registry('current_category');

            $pathInStore = $category->getPathInStore();
            $pathIds = array_reverse(explode(',', $pathInStore));

            $categories = $category->getParentCategories();
            $mainCategoriesString = '';
            foreach ($pathIds as $categoryId) {
                if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    if(empty($mainCategoriesString)){
                        $this->_currentFactfinderCategoryPath[] = 'categoryROOT|'.$categories[$categoryId]->getName();
                    }else{
                       $this->_currentFactfinderCategoryPath[] = 'categoryROOT'.$mainCategoriesString.'|'.$categories[$categoryId]->getName();
                    }
                    $mainCategoriesString .= '/'. str_replace('/', '%2F', $categories[$categoryId]->getName());
                }
            }
        } else {
            $this->_currentFactfinderCategoryPath = array();
        }
        
        if($all === false){
            if (isset($this->_currentFactfinderCategoryPath[count($this->_currentFactfinderCategoryPath)-1])) {
                $returnValue = $this->_currentFactfinderCategoryPath[count($this->_currentFactfinderCategoryPath)-1];
            }
            else {
                $returnValue = false;
            }
        } else {
            $returnValue = $this->_currentFactfinderCategoryPath;
        }

        return $returnValue;
    }

    /**
     * get Attribute option Value
     *
     * @param string $option
     * @return string
     */
    protected function _getAttributeOptionValue($option)
    {
		$searchAdapter = $this->_getSearchAdapter();
		FACTFinder_Http_ParallelDataProvider::loadAllData();
        $selectOptions = $searchAdapter->getSearchParams()->getFilters();
        $value = null;
        switch ($option->getType()) {

            // handle Slider Attributes
            case "slider";
                $value = $option->getField().'|'.$option->getType().'|'.str_replace(array('&', '='), array('|', ':'), $option->getValue()).'[VALUE]';
                break;

            // handle default Attributes
            default:
                $value = $option->getField();
                if($option->isSelected()){

                    // handle multiselectable Attributes
                    if(!empty($selectOptions[$option->getField()]) ){
                        if(strpos($option->getField(), 'categoryROOT') === false){
                            $values = explode('~~~', $selectOptions[$option->getField()]);
                            unset($values[array_search($option->getValue(), $values)]);
                            $value .= '|'.implode('~~~', $values);

                        }else{
                            $values = explode('/',str_replace('|'.$selectOptions[$option->getField()], '', $value));
                            $valueCount = count($values);
                            $value = '';
                            if($valueCount > 1){
                                for($i=0 ; $valueCount > $i ; $i++){
                                    $value .= ($i != 0 ? ($i == $valueCount-1 ? '|' : '/') : '').$values[$i];
                                }
                            }
                        }
                    }
                }else{
                    $value .= '|'.$option->getValue();
                    // handle multiselectable Attributes
                    if(!empty($selectOptions[$option->getField()])){
                        $value .= '~~~'.$selectOptions[$option->getField()];
                    }
                }
                break;
        }
          return $value;
    }


    /**
     * get Search Result Product Ids and additional Data
     *
     * @return array Products Ids
     */
    public function getSearchResultProductIds()
    {
        if($this->_searchResultProductIds == null){
            try {
				$searchAdapter = $this->_getSearchAdapter();
				FACTFinder_Http_ParallelDataProvider::loadAllData();
                $result = $searchAdapter->getResult();

                $this->_searchResultProductIds = array();
                if($result instanceof FACTFinder_Result){
                    foreach ($result AS $record){
                        if(isset($this->_searchResultProductIds[$record->getId()])){
                            continue;
                        }
                        $this->_searchResultProductIds[$record->getId()] = new Varien_Object(
                            array(
                                'similarity' => $record->getSimilarity(),
                                'position' => $record->getPosition(),
                                'original_position' => $record->getOriginalPosition()
                            )
                        );
                    }
                }
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_searchResultProductIds = array();
            }
        }

        return $this->_searchResultProductIds;
    }
	
	/**
     * get secondary Search Results
     *
     * @return array Products Ids
     */
    public function getSecondarySearchResult($channel)
    {
		$channels = $this->_getConfiguration()->getSecondaryChannels();
		
		if(!in_array($channel, $channels))
		{
			Mage::logException(new Exception("Tried to query a channel that was not configured as a secondary channel."));
			return array();
		}
			
        if($this->_secondarySearchResults == null)
		{
			$this->_secondarySearchResults = array();
			
			$this->_loadAllSearchAdapters();
			
			FACTFinder_Http_ParallelDataProvider::loadAllData();
			
			foreach($this->_secondarySearchAdapters AS $currentChannel => $searchAdapter)
			{
				try {
					$this->_secondarySearchResults[$currentChannel] = $searchAdapter->getResult();
				}
				catch (Exception $e) {
					Mage::logException($e);
				}
			}
        }
		
		if(!array_key_exists($channel, $this->_secondarySearchResults))
		{
			Mage::logException(new Exception("Result for channel '".$channel."' could not be retrieved."));
			return array();
		}

        return $this->_secondarySearchResults[$channel];
    }

    /**
     * set single parameter, which will be looped through to the FACT-Finder request
     *
     * @param string name
     * @param string value
     */
    protected function _setParam($name, $value, $log = true, $dataProvider = null)
    {
        if($log){
            Mage::helper('factfinder/debug')->log('set Param:'.$name.' => '.$value);
        }
		if($dataProvider == null)
			$this->_getGlobalDataProvider()->setParam($name, $value);
		else
			$dataProvider->setParam($name, $value);
        return $this;
    }
	
	/**
	 * set FactFinder DataProvider
	 *
	 * @param FACTFinder_Abstract_DataProvider
	 **/
	protected function _setGlobalDataProvider($dataProvider)
	{
		$this->_dataProvider = $dataProvider;
	}

    /**
     * gets the global FactFinder DataProvider
     *
     * @return FACTFinder_Abstract_DataProvider
     */
    protected function _getGlobalDataProvider()
    {
        if ($this->_dataProvider == null) {
            $config = $this->_getConfiguration();
            $params = $this->_getParamsParser()->getServerRequestParams();

            $this->_setGlobalDataProvider(FF::getInstance('http/dataProvider', $params, $config));
        }
        return $this->_dataProvider;
    }
	
	protected function _globalDataProviderExists()
	{
		return is_subclass_of($this->_dataProvider, 'FACTFinder_Abstract_DataProvider');
	}
	
	/**
     * gets a new FactFinder DataProvider
     *
     * @return FACTFinder_Abstract_DataProvider
     **/
    protected function _getDataProvider()
    {
        $config = $this->_getConfiguration();
        $params = $this->_getParamsParser()->getServerRequestParams();
		
        return FF::getInstance('http/dataProvider', $params, $config);
    }
	
	/**
	 * get a (new) FactFinder DataProvider that works in parallel
	 *
	 * @return FACTFinder_Abstract_Dataprovider
	 **/
	protected function _getParallelDataProvider()
	{
		$config = $this->_getConfiguration();
		$params = $this->_getParamsParser()->getServerRequestParams();
		
		$dp = FACTFinder_Http_ParallelDataProvider::getDataProvider($params, $config);
				
		return $dp;
	}

    /**
     * get Autentivation URL
     *
     * @return string
     */
    public function getAuthenticationUrl()
    {
        $dataprovider = $this->_getGlobalDataProvider();
        $dataprovider->setType('Management.ff');
        return $dataprovider->getNonAuthenticationUrl();
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
     * set FactFinder Configuration
     *
     * @param array $configarray
     */
    public function setConfiguration($configarray)
    {
        $this->_config = FF::getSingleton('configuration', $configarray);
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


    /**
     * Set StoreId for current configuration
     *
     * @param unknown_type $storeId
     */
    public function setStoreId($storeId) {
        $this->_getConfiguration()->setStoreId($storeId);

        return $this;
    }
}
