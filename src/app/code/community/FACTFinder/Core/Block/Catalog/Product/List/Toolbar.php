<?php
class FACTFinder_Core_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

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
     */
    protected function _construct()
    {
        if ($this->_useFF) {
            $this->_handler = Mage::getSingleton('factfinder/handler_search');
        }

        parent::_construct();
    }


    /**
     * Get page url
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params)
    {
        $sortingId = $this->_getSortingId($params);

        if ($sortingId) {
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

            return $this->_sortings[$sortingId];
        }

        return parent::getPagerUrl($params);
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
     * Get grit products sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
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
        $sortings = $this->_handler->getSorting();

        $this->getRequest()->getQuery();
        /** @var \FACTFinder\Data\Item $sorting */
        foreach ($sortings as $sorting) {
            if ($sorting->isSelected()) {
                $url = $sorting->getUrl();
                preg_match('/[\?|\&]{1}sort([a-z\_]*?)=/', $url, $matches);
                if (isset($matches[1])) {
                    return $matches[1];
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
        return 'sort' . $this->getCurrentOrder();
    }


}
