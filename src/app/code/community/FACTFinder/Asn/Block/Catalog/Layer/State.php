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
 *
 */

/**
 * Class FACTFinder_Asn_Block_Catalog_Layer_Factfinder
 *
 * Replaces default layer filter attribute
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Block_Catalog_Layer_State extends Mage_Catalog_Block_Layer_State
{
    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        $currentParams = $this->getRequest()->getParams();
        foreach($currentParams as $paramKey => $paramValue) {
            if($paramKey != 'q') {
                $currentParams[$paramKey] = null;
            }
        }
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $currentParams;
        $params['_escape']      = true;
        return Mage::getUrl('*/*/*', $params);
    }
}