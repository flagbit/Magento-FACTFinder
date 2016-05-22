<?php
/**
 * Url.php
 *
 * @category Mage
 * @package FACTFinder
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Url extends Mage_Core_Model_Url
{


    /**
     * Build url by requested path and parameters
     *
     * FF uses spaces in the parameters names, which get transformed into underscores when they come to $_GET
     * Standard magento function uses these modified parameter names, which causes to incorrect FF links.
     * This function is a small newly invented bicycle to create more compatibility between Magento and FF
     *
     * @param string|null $routePath
     * @param array|null  $routeParams
     *
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        // we only have to do something is the url is supposed to contain current parameters
        if (!empty($routeParams['_current'])) {
            // get raw query string since the one from magento contains underscores
            $queryString = $_SERVER['QUERY_STRING'];
            if (!empty($routeParams['_query'])) {
                // remove parameters from current query string, which will be now set
                $query = $routeParams['_query'];
                foreach ($query as $key => $value) {
                    $pattern = '/' . $key . '=[^&]{1,}/';
                    $queryString = preg_replace($pattern, '', $queryString);
                }

                // no question marks, no extra ampersands
                $queryString = str_replace('?', '', $queryString);
                $queryString = trim($queryString, '&');
            }

            // to avoid getting underscored parameters we tell magento to skip them
            $routeParams['_current'] = false;
            $url = parent::getUrl($routePath, $routeParams);

            // and now add our nice and correct query string
            $url .= strpos($queryString, '?') !== false ? '?' : '&';
            $url .= $queryString;

            return $url;
        }

        return parent::getUrl($routePath, $routeParams);
    }


}