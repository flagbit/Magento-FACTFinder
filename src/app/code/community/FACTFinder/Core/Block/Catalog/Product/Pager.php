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
 * Replaces default pager
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        if (!$this->_handler || !$this->_handler->getPaging()) {
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