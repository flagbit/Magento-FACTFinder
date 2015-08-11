<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Asn_Helper_Data
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * Parse url and return array of parameters
     *
     * @param string $url
     *
     * @return array
     */
    public function getQueryParams($url)
    {
        $queryParams = array();

        //conserve url encoded spaces, since parse_str replaces them with underscores
        $url = str_replace('%20', 'XXX', $url);

        $parseUrl = parse_url($url);
        if (isset($parseUrl['query'])) {
            parse_str($parseUrl['query'], $queryParams);
        }

        // recover spaces
        // we use not encoded values since they will be encoded with Mage::getUrl()
        $result = array();
        foreach ($queryParams as $key => $value) {
            $key = str_replace('XXX', ' ', $key);
            $value = str_replace('XXX', ' ', $value);
            $result[$key] = $value;
        }

        return $result;
    }


    /**
     * Check is catalog navigation replacement is enabled
     *
     * @return bool
     */
    public function isCatalogNavigation()
    {
        return (bool) Mage::app()->getStore()->getConfig('factfinder/modules/catalog_navigation');
    }


    /**
     * Remove category filter params if they are the save as the current category ones
     *
     * On catalog navigation if we use all the params from ff we have unnecessary ugly params
     * which we don't need. This function removes them
     *
     * @param string $url
     *
     * @return mixed
     */
    public function removeCategoryParams($url)
    {
        $categoryPath = Mage::getSingleton('factfinder_asn/handler_search')->getCurrentFactFinderCategoryPath();
        $query = http_build_query($categoryPath);
        $query = str_replace('+', '%20', $query);
        $url = str_replace($query, '', $url);
        //remove redundant &
        $url = str_replace(array('?&', '&&'), array('?', '&'), $url);

        return $url;
    }


}