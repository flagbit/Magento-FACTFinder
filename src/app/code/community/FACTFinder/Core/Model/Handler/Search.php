<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Search handler class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

use FACTFinder\Loader as FF;

class FACTFinder_Core_Model_Handler_Search extends FACTFinder_Core_Model_Handler_Abstract
{
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
                $params['productsPerPage'] = $helper->getPageLimit();

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

        if(Mage::getStoreConfigFlag('factfinder/config/personalization')) {
            $params['sid'] = Mage::helper('factfinder_tracking')->getSessionId();
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
            if (!count($result)) {
                Mage::helper('factfinder')->performFallbackRedirect();
            }

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
                Mage::logException(new Exception($error));
                Mage::helper('factfinder')->performFallbackRedirect();
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
                            'position' => $record->getPosition(),
                            'original_position' => $record->getField('__ORIG_POSITION__')
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
     * @return \FACTFinder\Data\Paging
     */
    public function getPaging()
    {
        return $this->_getFacade()->getPaging();
    }


    /**
     * Get sortings object from FF
     *
     * @return \FACTFinder\Data\Sorting
     */
    public function getSorting()
    {
        return $this->_getFacade()->getSorting();
    }


}
