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
 * Facade that hides FACT-Finder API Framework for Magento
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: Facade.php 906 2011-09-30 14:10:05Z tuerk $
 */
class Flagbit_FactFinder_Model_Facade
{
    /**
     * Two-dimensional array of FACT-Finder adapters
     * First-dimension index corresponds to type
     * Second-dimension index corresponds to channel
     * @var array of FACTFinder_Abstract_Adapter
     */
    protected $_adapters = array();

    /**
     * FACT-Finder search adapter
     * @var FACTFinder_Abstract_SearchAdapter
     */
    protected $_searchAdapter = null;

    /**
     * @var FACTFinder_Abstract_Configuration
     */
    protected $_config = null;

    /**
     * @var FACTFinder_ParametersParser
     */
    protected $_paramsParser = null;

    /**
     * @var FACTFinder_Abstract_DataProvider
     */
    protected $_dataProvider = null;

    /**
     * @var array
     */
    protected $_afterSearchNavigation = null;

    /**
     * @var array
     */
    protected $_searchResultProductIds = null;

	/**
     * @var array
     */
    protected $_currentFactfinderCategoryPath = null;
    
	/**
	 * logger object to log all module internals
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
     * Used to allow and delegate generic methods. Valid signatures:
     * get_____Adapter($channel = null)
     * configure____Adapter($params = array(), $channel = null)
     *
     * @param string $function
     * @param array $arguments
     * @return FACTFinder_Abstract_Adapter|null
     * @throws Exception
     */
    public function __call($function, $arguments)
    {
        $matches = array();
        if (preg_match('/^get(.+)Adapter$/', $function, $matches))
        {
            // We have a get______Adapter($channel) method!
            // The first argument (if any) will be treated as a channel


            $type = $matches[1];
            $type{0} = strtolower($type{0});

            $format = $this->_getFormat($type);

            $channel = null;
            if(count($arguments))
                $channel = $arguments[0];

            return $this->_getAdapter($format, $type, $channel);
        }
        elseif (preg_match('/^configure(.+)Adapter$/', $function, $matches))
        {
            // We have a configure_____Adapter($params, $channel) method!
            // The first argument (if any) will be treated as an array of params as key-value pairs
            // The second argument (if any) will be treated as a channel

            $type = $matches[1];
            $type{0} = strtolower($type{0});

            $format = $this->_getFormat($type);

            $channel = null;
            if(count($arguments) > 1)
                $channel = $arguments[1];

            $adapter = $this->_getAdapter($format, $type, $channel);

            if(count($arguments))
            {
                foreach($arguments[0] as $key => $value)
                    $adapter->setParam($key, $value);
            }

            return null;
        }
        else
        {
            throw new Exception("Call to undefined method ".$function."() in file ".__FILE__." on line ".__LINE__);
        }
    }

    /**
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
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function _________Configuration_Handling__________() { }

    /**
     * @param array $configArray
     */
    public function setConfiguration($configArray)
    {
        $this->_config = FF::getSingleton('configuration', $configArray);
    }

    /**
     * @param $configArray
     * @return FACTFinderCustom_Configuration config
     */
    protected function _getConfiguration($configArray = null)
    {
        if ($this->_config == null) {
            $this->_config = FF::getSingleton('configuration', $configArray);
        }
        return $this->_config;
    }

    /**
     * @param int $storeId
     * @return \Flagbit_FactFinder_Model_Facade
     */
    public function setStoreId($storeId) {
        $this->_getConfiguration()->setStoreId($storeId);

        return $this;
    }
	
	
	
	
	
	
	
	
	
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function _________Data_Provider_Handling__________() { }
	
	/**
	 * @param FACTFinder_Abstract_DataProvider
	 **/
	protected function _setGlobalDataProvider($dataProvider)
	{
		$this->_dataProvider = $dataProvider;
	}

    /**
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
	 * @return FACTFinder_Abstract_DataProvider
	 **/
	protected function _getParallelDataProvider()
	{
		$config = $this->_getConfiguration();
		$params = $this->_getParamsParser()->getServerRequestParams();
		
		$dp = FACTFinder_Http_ParallelDataProvider::getDataProvider($params, $config);
				
		return $dp;
	}

    /**
     * set single parameter, which will be looped through to the FACT-Finder request
     *
     * @param string $name
     * @param string $value
     * @param bool $log
     * @param null|FACTFinder_Abstract_DataProvider $dataProvider
     * @return \Flagbit_FactFinder_Model_Facade
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
                $this->_setParam('idsOnly', $this->_getConfiguration()->getIdsOnly() ? 'true' : 'false', true, $dataProvider)
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
                            $filterKey = '';
                            foreach($categories as $category){
                                $category = str_replace('%2F', '/', str_replace('%2B', '+', $category));
                                $this->_setParam('filtercategoryROOT'.$filterKey, $category, true, $dataProvider);
                                $filterKey .= '/'.str_replace('+', '%2B', str_replace('/', '%2F', $category));
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
                $this->_setParam('idsOnly', $this->_getConfiguration()->getIdsOnly() ? 'true' : 'false', true, $dataProvider)
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
                                $subParam = explode(':', $param[2]);
                                $this->_setParam($subParam[0], $subParam[1], true, $dataProvider);
                                $subParam = explode(':', $param[3]);
                                $this->_setParam($subParam[0], $subParam[1], true, $dataProvider);
                                break;

                            default:
                                if($key == 'Category'){
                                    $categories = array_merge(array_slice(explode('/', $param[0]), 1), array($param[1]));
                                    foreach($categories AS $k => $v) { $categories[$k] = $v; }
                                    $filterKey = '';
                                    foreach($categories as $category){
                         $category = str_replace('%2F', '/', str_replace('%2B', '+', $category));
                                        $this->_setParam('filtercategoryROOT'.$filterKey, $category, true, $dataProvider);
                                        $filterKey .= '/'.str_replace('+', '%2B', str_replace('/', '%2F', $category));
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

    protected function _loadAllData()
    {
        FACTFinder_Http_ParallelDataProvider::loadAllData();
    }
	
	
	
	
	
	
    // This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function ___________FF_Adapter_Getters___________() { }


    /**
     * @param $type
     * @return string
     */
    protected function _getFormat($type)
    {
        $format = 'http';
        if ($type != 'scic' && $type != 'suggest') {
            $version = $this->_getConfiguration()->getFactFinderVersion();
            $format = 'xml' . $version;
            return $format;
        }
        return $format;
    }

    /**
     * @param string $format
     * @param string $type
     * @param null|string $channel
     * @return FACTFinder_Abstract_Adapter
     */
    public function _getAdapter($format, $type, $channel = null)
    {
        if(!$channel)
            $channel = $this->_getConfiguration()->getChannel();

        if(!isset($this->_adapters[$type][$channel]))
        {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $dataProvider      = $this->_getParallelDataProvider();

            $dataProvider->setParam('channel', $channel);

            $this->_adapters[$type][$channel] = FF::getInstance(
                $format.'/'.$type.'Adapter',
                $dataProvider,
                $this->_getParamsParser(),
                $encodingHandler,
                $this->_logger
            );
        }
        return $this->_adapters[$type][$channel];
    }

    /**
     * @param bool $collectParams
     * @return FACTFinder_Abstract_SearchAdapter
     */
    protected function _getSearchAdapter($collectParams = true)
    {
        if ($this->_searchAdapter == null)
		{
			$this->_loadSearchAdapter($collectParams);
        }

        return $this->_searchAdapter;
    }

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
			$this->_collectParams($dataProvider);
		}
	}
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function ___________FF_Object_Getters____________() { }

    public function applyTracking($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getScicAdapter($channel)->applyTracking();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
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
				$this->_loadAllData();
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
     * @return string
     */
    public function getAuthenticationUrl()
    {
        $dataProvider = $this->_getGlobalDataProvider();
        $dataProvider->setType('Management.ff');
        return $dataProvider->getNonAuthenticationUrl();
    }

    public function getCampaigns()
    {
        $campaigns = null;
        try {
			$searchAdapter = $this->_getSearchAdapter();
            $this->_loadAllData();
            $campaigns = $searchAdapter->getCampaigns();
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
        return $campaigns;
    }

    public function getProductCampaigns($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getProductCampaignAdapter($channel)->getCampaigns();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getRecommendations($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getRecommendationAdapter($channel)->getRecommendations();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    /**
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

    public function getSearchResult($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getResult();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    /**
     * @return int
     */
    public function getSearchResultCount()
    {
        $count = 0;
        try {
			$searchAdapter = $this->_getSearchAdapter();
            $this->_loadAllData();
            $count = $searchAdapter->getResult()->getFoundRecordsCount();
        }
        catch (Exception $e) {
            Mage::logException($e);
        }

        return $count;
    }

    /**
     * get Search Result Product Ids and additional Data
     *
     * @throws Exception
     * @return array Products Ids
     */
    public function getSearchResultProductIds()
    {
        if($this->_searchResultProductIds == null){
            try {
				$searchAdapter = $this->_getSearchAdapter();
				$this->_loadAllData();
				$result = $searchAdapter->getResult();
				$error = $searchAdapter->getError();
				if($error)
					throw new Exception($error);
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
				Mage::helper('factfinder/search')->registerFailedAttempt();
                $this->_searchResultProductIds = array();
            }
        }

        return $this->_searchResultProductIds;
    }

    /**
     * execute search
     */
    public function checkStatus($configArray = null)
    {
        $status = false;
        try {
            $this->_getConfiguration($configArray);
			
			
            $this->_setParam('query', 'FACT-Finder Version');
            $this->_setParam('productsPerPage', '1');
			
			$searchAdapter = $this->_getSearchAdapter(false);
            $this->_loadAllData();
			$status = $searchAdapter->getStatus() == 'resultsFound';
        } catch (Exception $e) {
            $status = false;
        }
        return $status;
    }

    /**
     * @return string
     */
    public function getSuggestUrl()
    {
        $dataProvider = $this->_getGlobalDataProvider();
        $dataProvider->setType('Suggest.ff');
        $dataProvider->setParams(array());

        return $dataProvider->getNonAuthenticationUrl();
    }

    /**
     * @param string $channel
     * @return string
     */
    public function getSuggestions($channel = null)
    {
        try
        {
            $this->_loadAllData();
            return $this->getSuggestAdapter($channel)->getSuggestions();
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            return '';
        }
    }

    /**
     * @param string $channel
     * @return array
     */
    public function getTagCloud($channel = null)
    {
		try {
            $this->_loadAllData();
			return $this->getTagCloudAdapter($channel)->getTagCloud();
		} catch (Exception $e) {
            Mage::logException($e);
			return null;
        }
    }
	
	
	
	
	
	
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function ___________Random_Functions____________() { }

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
     * @param string $unit
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
     * @param bool $all
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
		$this->_loadAllData();
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

                    // handle multi-selectable Attributes
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
                    // handle multi-selectable Attributes
                    if(!empty($selectOptions[$option->getField()])){
                        $value .= '~~~'.$selectOptions[$option->getField()];
                    }
                }
                break;
        }
          return $value;
    }
}
