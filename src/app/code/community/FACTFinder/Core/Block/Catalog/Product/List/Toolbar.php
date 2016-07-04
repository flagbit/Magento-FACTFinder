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
 * Replaces default toolbar
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    const SORT_PARAM_PATTERN = 'sort([\w\-]*?)';

    /**
     * @var bool
     */
    protected $_useFF = true;

    /**
     * @var FACTFinder_Core_Model_Handler_Search
     */
    protected $_handler;

    /**
     * @var array
     */
    protected $_sortings = array();


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->_useFF && Mage::helper('factfinder')->isEnabled()) {
            $this->_handler = Mage::getSingleton('factfinder/handler_search');
        }

        parent::_construct();

        // reset orders if we use the ones from FF
        if ($this->_handler && Mage::helper('factfinder/search')->useSortings()) {
            $this->_availableOrder = array();
            $sortItems = $this->_handler->getSorting();
            if ($sortItems != null) {
                foreach ($sortItems as $item) {
                    $this->addOrderToAvailableOrders($item->getLabel(), $item->getLabel());
                }
            }
        }
    }


    /**
     * Get page url
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params=array())
    {
        if (empty($this->_handler)) {
            return parent::getPagerUrl($params);
        }

        if (Mage::helper('factfinder/search')->useSortings()) {
            $sortingUrl = $this->getSortingUrl($params);
            if ($sortingUrl) {
                return $sortingUrl;
            }
        }

        $sortingId = $this->_getSortingId($params);

        $this->initSortings($sortingId);

        if (isset($this->_sortings[$sortingId])) {
            return $this->_sortings[$sortingId];
        } else {
            return parent::getPagerUrl($params);
        }
    }


    /**
     * @param int $limit
     *
     * @return string
     */
    public function getLimitUrl($limit)
    {
        $limitUrl = null;
        if ($this->_handler && Mage::helper('factfinder/search')->useResultsPerPageOptions()) {
            $limitUrl = $this->getResultsPerPageOptionUrl($limit);
            if ($limitUrl != null) {
                return $limitUrl;
            }
        }

        $params = array(
            $this->getLimitVarName() => $limit
        );

        $query = http_build_query($params);

        $url = trim(Mage::getBaseUrl('web'), '/');

        // using super global because magento doesn't return real uri
        // but its target like catalog/category/view
        $currentRequest = explode('?', $this->removeBasePathByBaseUrl($_SERVER['REQUEST_URI'], $url));

        if (count($currentRequest) > 1) {
            $params = array_pop($currentRequest);
            $params = $this->_removeParam($params, $this->getLimitVarName());
            $params = $this->_removeParam($params, 'p');

            $currentRequest = array_pop($currentRequest);

            return $url . $currentRequest . '?' . $params . '&' . $query;
        } else {
            return parent::getPagerUrl($params);
        }
    }


    /**
     * Uses a given base URL to remove subfolders from the current request path in case Magento is hosted in a
     * subdirectory.
     *
     * @param string $fullPath The full request path.
     * @param string $baseUrl  The base URL of the Magento installation.
     * @return string
     */
    protected function removeBasePathByBaseUrl($fullPath, $baseUrl)
    {
        $basePath = parse_url($baseUrl, PHP_URL_PATH);
        $pos = strpos($fullPath, $basePath);
        if ($pos !== false) {
            return substr_replace($fullPath, '', $pos, strlen($basePath));
        }

        return $fullPath;
    }


    /**
     * Get sorting id
     *
     * @param array $params
     *
     * @return bool|string
     */
    protected function _getSortingId($params)
    {
        if (isset($params[$this->getOrderVarName()]) && isset($params[$this->getDirectionVarName()])) {
            if ($params[$this->getOrderVarName()] == $this->_orderField) {
                return $this->_orderField;
            }

            return 'sort' . $params[$this->getOrderVarName()] . '=' . $params[$this->getDirectionVarName()];
        }

        return false;
    }


    /**
     * Get sorting Url
     *
     * @param array $params
     *
     * @return bool|string
     */
    protected function getSortingUrl($params)
    {
        if (isset($params[$this->getOrderVarName()])) {
            $sortItems = $this->_handler->getSorting();
            foreach ($sortItems as $sortItem) {
                if ($params[$this->getOrderVarName()] == $sortItem->getLabel()) {
                    return $sortItem->getUrl();
                }
            }
        }

        return false;
    }


    /**
     * Get grit products sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        if (!$this->_handler) {
            return parent::getCurrentOrder();
        }

        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->_orderField;

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        // the only change of this method is here
        $order = $this->_getSelectedOrder();

        if ($order && isset($orders[$order])) {
            if ($order == $defaultOrder) {
                Mage::getSingleton('catalog/session')->unsSortOrder();
            } else {
                $this->_memorizeParam('sort_order', $order);
            }
        } else {
            $order = Mage::getSingleton('catalog/session')->getSortOrder();
        }

        // validate session value
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        $this->setData('_current_grid_order', $order);

        return $order;
    }


    /**
     * Get current order
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function _getSelectedOrder()
    {
        if ($this->_handler && $this->_handler->getSorting()) {
            $sortings = $this->_handler->getSorting();
            if ($sortings != null) {
                $this->getRequest()->getQuery();
                /** @var \FACTFinder\Data\Item $sorting */
                foreach ($sortings as $sorting) {
                    if ($sorting->isSelected()) {
                        if (Mage::helper('factfinder/search')->useSortings()) {
                            return $sorting->getLabel();
                        }

                        $url = $sorting->getUrl();
                        preg_match('/[\?|\&]{1}'.self::SORT_PARAM_PATTERN.'=/', $url, $matches);
                        if (isset($matches[1])) {
                            return $matches[1];
                        }
                    }
                }
            }
        }

        return $this->_orderField;
    }


    /**
     * Get var name for sorting direction
     *
     * @return string
     */
    public function getDirectionVarName()
    {
        if ($this->_handler) {
            if (Mage::helper('factfinder/search')->useSortings()) {
                foreach ($this->getRequest()->getParams() as $key => $value) {
                    if (preg_match('/'.self::SORT_PARAM_PATTERN.'/', $key)) {
                        return $key;
                    }
                }
            }
            return 'sort' . $this->getCurrentOrder();
        }

        return parent::getDirectionVarName();
    }


    /**
     * Remove specific parameter from url parameters string
     *
     * @param string $paramString
     * @param string $paramName
     *
     * @return string
     */
    protected function _removeParam($paramString, $paramName)
    {

        $params = explode('&', $paramString);
        foreach ($params as $key => $part) {
            if (strpos($part, $paramName . '=') === 0) {
                unset($params[$key]);
            }
        }

        return implode('&', $params);
    }


    /**
     * Avoid resetting orders by magento if we should use ours
     *
     * @param array $orders
     *
     * @return $this
     */
    public function setAvailableOrders($orders)
    {
        if (Mage::helper('factfinder/search')->useSortings()) {
            return $this;
        }

        return parent::setAvailableOrders($orders);
    }


    /**
     * Init sortings and map them according to parameters in the query
     *
     * @param string $sortingId
     *
     * @return $this
     */
    protected function initSortings($sortingId)
    {
        if (!$sortingId || !$this->_handler) {
            return $this;
        }

        $sortings = $this->_handler->getSorting();

        // relevance default and has no directions
        if ($sortingId == $this->_orderField) {
            $sorting = $sortings[0];
            $this->_sortings[$sortingId] = $sorting->getUrl();
        } elseif (!isset($this->_sortings[$sortingId])) {
            /** @var \FACTFinder\Data\Item $sorting */
            foreach ($sortings as $sorting) {
                $url = $sorting->getUrl();
                if (strpos($url, $sortingId) !== false) {
                    $this->_sortings[$sortingId] = $sorting->getUrl();
                    break;
                }
            }
        }

        return $this;
    }


    /**
     * Returns url model class name
     *
     * @return string
     */
    protected function _getUrlModelClass()
    {
        return 'factfinder/url';
    }


    /**
     * Retrieve Pager URL
     *
     * @param string $order
     * @param string $direction
     *
     * @return string
     */
    public function getOrderUrl($order, $direction)
    {
        if ($order === null
            && Mage::helper('factfinder/search')->useSortings()
            && $this->getRequest()->getParam($this->getDirectionVarName())
        ) {
            // we use raw value since the one from magento doesn't use redirect url
            $url = trim(Mage::getBaseUrl('web'), '/');
            $request = $this->removeBasePathByBaseUrl($_SERVER['REQUEST_URI'], $url);
            $request = preg_replace(
                "/{$this->getDirectionVarName()}=([^&]+)/",
                $this->getDirectionVarName() . '=' . $direction,
                $request
            );
            return trim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/') . $request;
        }

        return parent::getOrderUrl($order, $direction);
    }


    /**
     * Retrieve available results per page values from search response.
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        if ($this->_handler && Mage::helper('factfinder/search')->useResultsPerPageOptions()) {
            $resultsPerPageOptions = $this->_handler->getResultsPerPageOptions();
            if ($resultsPerPageOptions != null) {
                /** @var \FACTFinder\Data\Item $resultsPerPageOptions */
                foreach ($resultsPerPageOptions as $option) {
                    $this->_availableLimit[] = $option->getLabel();
                }
            }
        }

        if (!empty($this->_availableLimit)) {
            return $this->_availableLimit;
        }

        return parent::getAvailableLimit();
    }


    /**
     * Get specified results per page value.
     *
     * @return string
     */
    public function getLimit()
    {
        if ($this->_handler && Mage::helper('factfinder/search')->useResultsPerPageOptions()) {
            $currentLimit = $this->getRequest()->getParam($this->getLimitVarName());
            if ($currentLimit == null && $this->_handler->getResultsPerPageOptions() != null) {
                $currentLimit = $this->_handler->getResultsPerPageOptions()->getDefaultOption()->getLabel();
            }

            return $currentLimit;
        }

        return parent::getLimit();
    }


    /**
     * Checks if the given results per page value is selected.
     *
     * @param string $limit
     *
     * @return bool
     */
    public function isLimitCurrent($limit)
    {
        if ($this->_handler && Mage::helper('factfinder/search')->useResultsPerPageOptions()) {
            return $this->getResultsPerPageOptionLabel($limit) == $this->getLimit();
        }

        return parent::isLimitCurrent($limit);
    }


    /**
     * Returns the results per page value for a key.
     *
     * @param string $limit
     *
     * @return string
     */
    protected function getResultsPerPageOptionLabel($limit)
    {
        $resultsPerPageOptions = $this->_handler->getResultsPerPageOptions();
        if ($resultsPerPageOptions != null) {
            if (isset($resultsPerPageOptions[$limit])) {
                return $resultsPerPageOptions[$limit]->getLabel();
            }
        }

        return $limit;
    }


    /**
     * Returns the results per page url for a key.
     *
     * @param string $limit
     *
     * @return string
     */
    protected function getResultsPerPageOptionUrl($limit)
    {
        $resultsPerPageOptions = $this->_handler->getResultsPerPageOptions();
        if ($resultsPerPageOptions != null && isset($resultsPerPageOptions[$limit])) {
            return $resultsPerPageOptions[$limit]->getUrl();
        }

        return null;
    }


    /**
     * Returns the default results per page value.
     *
     * @return string
     */
    public function getDefaultPerPageValue()
    {
        if ($this->_handler && Mage::helper('factfinder/search')->useResultsPerPageOptions()
            && $this->_handler->getResultsPerPageOptions() != null
        ) {
            return $this->_handler->getResultsPerPageOptions()->getDefaultOption()->getLabel();
        }

        return parent::getDefaultPerPageValue();
    }


}
