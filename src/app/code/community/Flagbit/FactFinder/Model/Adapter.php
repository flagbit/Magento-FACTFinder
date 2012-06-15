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
     * FACT-Finder Searchadapter
     * @var array
     */
    protected $_searchResultProductIds = null;

    /**
     * current FACT-Finder Category Path
     * @var array
     */
    protected $_currentFactfinderCategoryPath = null;
    
    
    public function __construct($arg = null)
    {
        if ($arg != null && $arg instanceof FACTFinder_Logger_LoggerInterface) {
            FF::setLogger($arg);
        } else {
            $logger = Mage::helper('factfinder/debug');
            FF::setLogger($logger);
        }
    }

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
                'xml67/searchAdapter',
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
        $params = $this->_getParamsParser()->getRequestParams();

        if (strpos(Mage::getStoreConfig('factfinder/config/internal_ip'), Mage::helper('core/http')->getRemoteAddr()) !== false) {
            $this->_setParam('log', 'internal');
        }

        switch($_request->getModuleName()){
            
            case "xmlconnect":
                $_query = $helper->getQueryText();
                $this->_setParam('idsOnly', 'true')
                    ->_setParam('query', $_query);
                
                $count = $params['count'];
                if ($count > 0) {
                    $this->_setParam('productsPerPage', $count)
                         ->_setParam('page', ($params['offset'] / $count) + 1);
                }

                // add Sorting Param
                foreach($params as $key => $value){
                    if(substr($key, 0, 6) == 'order_'){
                        $key = substr($key, 6);
                        if(!in_array($key, array('position', 'relevance'))){
                            $this->_setParam('sort'.$key, $value);
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
                                $this->_setParam('filtercategoryROOT'.$filterkey, $category);
                                $filterkey .= '/'.str_replace('+', '%2B', str_replace('/', '%2F', $category));
                            }
                        }else{
                            $this->_setParam('filter'.$param[0], $param[1]);
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
                $this->_setParam('idsOnly', 'true')
                    ->_setParam('productsPerPage', $helper->getPageLimit())
                    ->_setParam('query', $_query)
                    ->_setParam('page', $helper->getCurrentPage());

                // add Sorting Param, but only if it was set explicit via url
                foreach($params as $key => $value){
                    if($key == 'order'
                    && $helper->getCurrentOrder()
                    && $helper->getCurrentDirection()
                    && $helper->getCurrentOrder() != 'position'
                    && $helper->getCurrentOrder() != 'relevance'){
                        $this->_setParam('sort'.$helper->getCurrentOrder(), $helper->getCurrentDirection());
                    }
                }

                // add Filter Params
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
                                if($key == 'Category'){
                                    $categories = array_merge(array_slice(explode('/', $param[0]), 1), array($param[1]));
                                    foreach($categories AS $k => $v) { $categories[$k] = $v; }
                                    $filterkey = '';
                                    foreach($categories as $category){
                         $category = str_replace('%2F', '/', str_replace('%2B', '+', $category));
                                        $this->_setParam('filtercategoryROOT'.$filterkey, $category);
                                        $filterkey .= '/'.str_replace('+', '%2B', str_replace('/', '%2F', $category));
                                    }

                                }else{
                                    $this->_setParam('filter'.$param[0], $param[1]);
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
            $campaigns = $this->_getSearchAdapter()->getCampaigns();
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
     * get Suggest Results as Array
     *
     * @param string $query
     * @return array
     */
    public function getSuggestResult($query)
    {
        $this->_setParam('query', $query);
        $this->_setParam('format', 'json');

        return Zend_Json_Decoder::decode($this->_getSuggestAdapter()->getSuggestions());
    }

    /**
     * get Suggest Results as JSON
     *
     * @param string $query
     * @return string json
     */
    public function getSuggestResultJsonp($query, $jqueryCallback)
    {
        $this->_setParam('query', $query, false);
        $this->_setParam('format', 'jsonp', false);

        return $this->_getSuggestAdapter()->getSuggestions();
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
                'xml67/tagCloudAdapter',
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
        return $this->_getTagCloudAdapter()->getTagCloud();
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
            $dataProvider      = $this->_getDataProvider();
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
            $dataProvider      = $this->_getDataProvider();
            $dataProvider->setParam('idsOnly', 'true');
            $this->_recommendationAdapter = FF::getSingleton('xml67/recommendationAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
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
        if ($this->_productCampaignAdapter == null) {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $params            = $this->_getParamsParser()->getServerRequestParams();
            $dataProvider      = $this->_getDataProvider();
            $dataProvider->setParam('idsOnly', 'true');
            $this->_productCampaignAdapter = FF::getSingleton('xml67/productCampaignAdapter', $dataProvider, $this->_getParamsParser(), $encodingHandler);
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
            $count = $this->_getSearchAdapter()->getResult()->getFoundRecordsCount();
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
                $result = $this->_getSearchAdapter()->getAsn();
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
        $selectOptions = $this->_getSearchAdapter()->getSearchParams()->getFilters();
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
                $result = $this->_getSearchAdapter()->getResult();

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
     * set single parameter, which will be looped through to the FACT-Finder request
     *
     * @param string name
     * @param string value
     */
    protected function _setParam($name, $value, $log = true)
    {
        if($log){
            Mage::helper('factfinder/debug')->log('set Param:'.$name.' => '.$value);
        }
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
