<?php
use FACTFinder\Loader as FF;

class FACTFinder_Core_Model_Handler_Search extends FACTFinder_Core_Model_Handler_Abstract
{
    protected $_searchResult;
    protected $_searchResultCount;

    protected function _configureFacade()
    {
        $params = $this->_collectParams();

        $this->_getFacade()->configureSearchAdapter($params);
    }

    /**
     * Prepare all request parameters for the primary search adapter
     *
     */
    protected function _collectParams()
    {
        // search Helper
        $helper = Mage::helper('factfinder/search');
        $_request = Mage::app()->getRequest();
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
            case "catalog":
                $params = array_merge($params, $this->_getCurrentFactFinderCategoryPath());

                if (Mage::getStoreConfig('factfinder/search/ffversion') >= 69) {
                    $params['navigation'] = 'true';
                } else {
                    $params['catalog'] = 'true';
                }
            // no break
            case "catalogsearch":
            default:
                // add Default Params
                $params['idsOnly'] = $this->_getFacade()->getConfiguration()->getIdsOnly() ? 'true' : 'false';
                $params['productsPerPage'] = $helper->getPageLimit();

                if ($_request->getModuleName() == 'catalogsearch') {
                    $params['query'] = $helper->getQueryText();
                }
                $params['page'] = $helper->getCurrentPage();

                if ($seoPath = Mage::app()->getRequest()->getParam('seoPath')) {
                    $params['seoPath'] = $seoPath;
                }

                // add Sorting Param, but only if it was set explicitly via url
                foreach ($searchParams->getSortings() as $key => $value) {
                    if ($key == 'order'
                        && $helper->getCurrentOrder()
                        && $helper->getCurrentDirection()
                        && $helper->getCurrentOrder() != 'position'
                        && $helper->getCurrentOrder() != 'relevance'
                    ) {
                        $params['sort' . $helper->getCurrentOrder()] = $helper->getCurrentDirection();
                    }
                }
        }

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
            $result = $this->_getFacade()->getSearchResult();
            $error = $this->_getFacade()->getSearchError();

            if ($result === null || $error) {
                Mage::helper('factfinder/search')->registerFailedAttempt();
                Mage::logException(new Exception($error));
            }

            $this->_searchResult = array();

            if ($result instanceof \FACTFinder\Data\Result) {
                foreach ($result AS $record) {
                    if (isset($this->_searchResult[$record->getId()])) {
                        continue;
                    }

                    $this->_searchResult[$record->getId()] = new Varien_Object(
                        array(
                            'similarity' => $record->getSimilarity(),
                            'position' => $record->getPosition()
                        )
                    );
                }
            }
        }

        return $this->_searchResult;
    }
}
