<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Replaces default pager
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Catalog_Product_Pager extends Mage_Page_Block_Html_Pager
{

    /**
     * Whether FF should be used to get paging urls
     *
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
    protected $_pagingUrls = array();


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->_useFF) {
            $this->_handler = Mage::getSingleton('factfinder/handler_search');
        }

        parent::_construct();
    }


    /**
     * Get pager url with specific params
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        if (!$this->getPaging()) {
            return parent::getPagerUrl($params);
        }

        $pageNum = (isset($params['p']) ? $params['p'] : 0);

        if (!isset($this->_pagingUrls[$pageNum])) {
            $this->_pagingUrls[$pageNum] = '';
            /** @var \FACTFinder\Data\Page $pageItem */
            foreach ($this->getPaging() as $pageItem) {
                if ($pageItem->getPageNumber() == $pageNum) {
                    $this->_pagingUrls[$pageNum] = $pageItem->getUrl();
                    break;
                }
            }
        }

        return $this->_pagingUrls[$pageNum];
    }


    /**
     * Get paging from handler or null
     *
     * @return \FACTFinder\Data\Paging|null
     */
    protected function getPaging()
    {
        if (!$this->_handler || !$this->_handler->getPaging()) {
            return null;
        }

        return $this->_handler->getPaging();
    }


    /**
     * Get last page URL
     *
     * @return string
     */
    public function getLastPageUrl()
    {
        if ($this->getPaging() && $this->getPaging()->getLastPage()) {
            return $this->getPaging()->getLastPage()->getUrl();
        }

        return parent::getLastPageUrl();
    }


    /**
     * Get first page URL
     *
     * @return string
     */
    public function getFirstPageUrl()
    {
        if ($this->getPaging() && $this->getPaging()->getFirstPage()) {
            return $this->getPaging()->getFirstPage()->getUrl();
        }

        return parent::getFirstPageUrl();
    }


    /**
     * Get next page URL
     *
     * @return string
     */
    public function getNextPageUrl()
    {
        if ($this->getPaging() && $this->getPaging()->getNextPage()) {
            return $this->getPaging()->getNextPage()->getUrl();
        }

        return parent::getNextPageUrl();
    }


    /**
     * Get previous page URL
     *
     * @return string
     */
    public function getPreviousPageUrl()
    {
        if ($this->getPaging() && $this->getPaging()->getPreviousPage()) {
            return $this->getPaging()->getPreviousPage()->getUrl();
        }

        return parent::getPreviousPageUrl();
    }


}