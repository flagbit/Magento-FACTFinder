<?php
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
        if (!$this->_handler) {
            return parent::getPagerUrl($params);
        }

        $pageNum = $params['p'];

        if (!isset($this->_pagingUrls[$pageNum])) {
            $this->_pagingUrls[$pageNum] = '';
            /** @var \FACTFinder\Data\Page $pageItem */
            foreach ($this->_handler->getPaging() as $pageItem) {
                if ($pageItem->getPageNumber() == $pageNum) {
                    $this->_pagingUrls[$pageNum] = $pageItem->getUrl();
                    break;
                }
            }
        }

        return $this->_pagingUrls[$pageNum];
    }


}