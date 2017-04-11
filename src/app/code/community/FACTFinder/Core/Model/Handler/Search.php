<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Search handler class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

use FACTFinder\Loader as FF;
use FACTFinder\Data as FFData;

class FACTFinder_Core_Model_Handler_Search extends FACTFinder_Core_Model_Handler_Abstract
{

    const SEARCH_STATUS_REGISTRY_KEY = 'ff_search_status';
    const ORIGINAL_POSITION_FIELD    = '__ORIG_POSITION__';
    const CAMPAIGN_NAME_FIELD        = '__FFCampaign__';
    const INSTOREADS_PRODUCT_FIELD   = '__FFInstoreAds__';
    const VARIANT_ID_FIELD           = 'id';

    protected $_searchResult;
    protected $_searchResultCount;
    protected $_paging;


    /**
     * Set configuration params to the search adapter
     *
     * @return void
     */
    protected function _configureFacade()
    {
        $params = $this->_collectParams();

        $this->_getFacade()->configureSearchAdapter($params);
    }


    /**
     * Prepare all request parameters for the primary search adapter
     *
     * @return array
     */
    protected function _collectParams()
    {
        // search Helper
        $helper = Mage::helper('factfinder/search');
        $_request = Mage::app()->getRequest();
        $requestParams = $this->_getFacade()->getClientRequestParams();
        $searchParams = $this->_getFacade()->getSearchParams();
        $params = array();

        if (Mage::helper('factfinder')->isInternal()) {
            $params['log'] = 'internal';
        }

        switch ($_request->getModuleName()) {
            case "xmlconnect":
                $_query = $helper->getQueryText();
                $params['idsOnly'] = FF::getSingleton('configuration')->getIdsOnly() ? 'true' : 'false';
                $params['query'] = $_query;

                $count = $searchParams->getProductsPerPage() ? $searchParams->getProductsPerPage() : 0;
                if ($count > 0) {
                    $params['productsPerPage'] = $count;
                    $params['page'] = $searchParams->getCurrentPage();
                }

                // todo: make this work
                // add Sorting Param
                foreach ($searchParams->getSortings() as $key => $value) {
                    if (substr($key, 0, 6) == 'order_') {
                        $key = substr($key, 6);
                        if (!in_array($key, array('position', 'relevance'))) {
                            $params['sort' . $key] = $value;
                        }
                    }
                }

                break;
            case "catalogsearch":
            default:
                // add Default Params
                $params['idsOnly'] = $this->_getFacade()->getConfiguration()->getIdsOnly() ? 'true' : 'false';
                $count = $searchParams->getProductsPerPage() ? $searchParams->getProductsPerPage() : 0;
                if ($count <= 0 && !Mage::helper('factfinder/search')->useResultsPerPageOptions()){                    
                    $params['productsPerPage'] = $helper->getPageLimit();
                }

                if ($_request->getModuleName() == 'catalogsearch') {
                    $params['query'] = $helper->getQueryText();
                }

                $params['page'] = $helper->getCurrentPage();

                if ($seoPath = Mage::app()->getRequest()->getParam('seoPath')) {
                    $params['seoPath'] = $seoPath;
                }

                // add Sorting Param, but only if it was set explicitly via url
                if (isset($requestParams['order'])
                    && $helper->getCurrentOrder()
                    && $helper->getCurrentDirection()
                    && $helper->getCurrentOrder() != 'position'
                    && $helper->getCurrentOrder() != 'relevance'
                ) {
                    $params['sort' . $helper->getCurrentOrder()] = $helper->getCurrentDirection();
                }
        }

        if (Mage::helper('factfinder/debug')->isDebugMode()) {
            $params['verbose'] = 'true';
        }

        $params['sid'] = Mage::helper('factfinder_tracking')->getSessionId();

        return $params;
    }


    /**
     * Get number of found products
     *
     * @return int|null
     */
    public function getSearchResultCount()
    {
        if ($this->_searchResultCount === null) {
            if (!$this->isSearchHasResult()) {
                Mage::helper('factfinder')->performFallbackRedirect();
            }

            $result = $this->_getFacade()->getSearchResult();
            if ($result instanceof \FACTFinder\Data\Result) {
                $this->_searchResultCount = $result->getFoundRecordsCount();
            }

            if ($this->_searchResultCount === null) {
                $this->_searchResultCount = 0;
            }
        }

        return $this->_searchResultCount;
    }


    /**
     * Get array of found products
     *
     * @return array
     */
    public function getSearchResult()
    {
        if ($this->_searchResult === null) {
            if (!$this->isSearchHasResult()) {
                Mage::helper('factfinder')->performFallbackRedirect();
            }

            $this->_searchResult = array();

            $result = $this->_getFacade()->getSearchResult();
            if ($result instanceof \FACTFinder\Data\Result) {
                foreach ($result as $record) {
                    if (isset($this->_searchResult[$record->getId()])) {
                        continue;
                    }

                    $this->_searchResult[$record->getId()] = new Varien_Object(
                        array(
                            'similarity'        => $record->getSimilarity(),
                            'position'          => $record->getPosition(),
                            'original_position' => $record->getField(self::ORIGINAL_POSITION_FIELD),
                            'campaign'          => $record->getField(self::CAMPAIGN_NAME_FIELD),
                            'instore_ads'       => $record->getField(self::INSTOREADS_PRODUCT_FIELD),
                            'variant_id'        => $record->getField(self::VARIANT_ID_FIELD)
                        )
                    );
                }
            }
        }

        return $this->_searchResult;
    }


    /**
     * Get pagination object from FF
     *
     * @return \FACTFinder\Data\Paging|null
     */
    public function getPaging()
    {
        if (!Mage::helper('factfinder')->isEnabled() || !$this->isSearchHasResult()) {
            return null;
        }

        return $this->_getFacade()->getPaging();
    }


    /**
     * Get sortings object from FF
     *
     * @return \FACTFinder\Data\Sorting|null
     */
    public function getSorting()
    {
        if (!Mage::helper('factfinder')->isEnabled() || !$this->isSearchHasResult()) {
            return null;
        }

        return $this->_getFacade()->getSorting();
    }
    
    /**
     * Get results per page options object from FF
     *
     * @return \FACTFinder\Data\ResultsPerPageOptions|null
     */
    public function getResultsPerPageOptions()
    {
        if (!Mage::helper('factfinder')->isEnabled() || !$this->isSearchHasResult()) {
            return null;
        }

        return $this->_getFacade()->getResultsPerPageOptions();
    }


    /**
     * Return ArticleNumberSearchStatus
     *
     * @return \FACTFinder\Data\ArticleNumberSearchStatus
     */
    public function getArticleNumberStatus()
    {
        $status = \FACTFinder\Data\ArticleNumberSearchStatus::IsNoArticleNumberResultFound();

        if (!$this->isSearchHasResult()) {
            return $status;
        }

        try {
            $status = $this->_getFacade()->getSearchAdapter()->getArticleNumberStatus();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $status;
    }


    /**
     * Get search status object
     *
     * @return \FACTFinder\Data\SearchStatus
     */
    public function getSearchStatus()
    {
        $status = Mage::registry(self::SEARCH_STATUS_REGISTRY_KEY);
        if ($status === null) {
            $status = \FACTFinder\Data\SearchStatus::NoResult();
            try {
                $status = $this->_getFacade()->getSearchAdapter()->getStatus();
            } catch (Exception $e) {
                Mage::logException($e);
            }

            Mage::register(self::SEARCH_STATUS_REGISTRY_KEY, $status, true);
        }

        return $status;
    }


    /**
     * Check if the seach request contains results
     *
     * @return bool
     */
    public function isSearchHasResult()
    {
        return $this->getSearchStatus() !== \FACTFinder\Data\SearchStatus::NoResult();
    }


    /**
     * Get default option for the "items per page" dropdown
     *
     * @return bool|FFData\Item
     */
    public function getDefaultPerPageOption()
    {
        $option = $this->getResultsPerPageOptions()->getDefaultOption();

        if ($option instanceof FFData\Item) {
            return $option;
        }

        return false;
    }


}
