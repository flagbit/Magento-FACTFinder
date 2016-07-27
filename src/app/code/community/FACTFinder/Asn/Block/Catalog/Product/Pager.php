<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Asn_Block_Catalog_Product_Pager
 *
 * Replaces default layer pager on navigation
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Block_Catalog_Product_Pager extends FACTFinder_Core_Block_Catalog_Product_Pager
{


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        if (!Mage::helper('factfinder_asn')->isCatalogNavigation()) {
            $this->_useFF = false;
        }

        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getPagerUrl($params = array())
    {
        $url = parent::getPagerUrl($params);

        if ($this->_handler && !Mage::helper('factfinder/search')->getIsOnSearchPage()) {
            $url = Mage::helper('factfinder_asn')->removeCategoryParams($url);
        }

        return $url;
    }


}