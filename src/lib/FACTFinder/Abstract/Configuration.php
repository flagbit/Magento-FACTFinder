<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * interface to access the needed configuration values
 *
 * @package FACTFinder\Abstract
 */
interface FACTFinder_Abstract_Configuration
{
    /**
     * @return string
     */
    function getVersion();

    /**
     * @param string name
     * @return string value
     */
    function getCustomValue($name);

    /**
     * @return string
     */
	function getRequestProtocol();

    /**
     * @return string
     */
    function getServerAddress();

    /**
     * @return int
     */
    function getServerPort();

    /**
     * @return string
     */
    function getContext();

    /**
     * @return string
     */
    function getChannel();

    /**
     * @return string
     */
    function getLanguage();

    /**
     * @return string
     */
    function getAuthUser();

    /**
     * @return string
     */
    function getAuthPasswort();

    /**
     * @return boolean
     */
    function isHttpAuthenticationType();

    /**
     * @return boolean
     */
    function isSimpleAuthenticationType();

    /**
     * @return boolean
     */
    function isAdvancedAuthenticationType();

    /**
     * @return string
     */
    function getAdvancedAuthPrefix();

    /**
     * @return string
     */
    function getAdvancedAuthPostfix();

    /**
     * get mapping rules to map params for the page
     *
     * @return array
     */
    function getPageMappings();

    /**
     * get mapping rules to map params for the server
     *
     * @return array
     */
    function getServerMappings();

    /**
     * returns an array of parameter names as array keys with the boolean value true. this are the ignored page
     * parameters from the configuration
     *
     * @return array with string as key and boolean true as value for each of them
     */
    function getIgnoredPageParams();

    /**
     * returns an array of parameter names as array keys with the boolean value true. this are the ignored server
     * parameters from the configuration
     *
     * @return array with string as key and boolean true as value for each of them
     */
    function getIgnoredServerParams();

    /**
     * returns an array of the required parameters for the page. The array-keys are the parameter names and the array
     * values are the default values of each parameter
     *
     * @return array string to string map (param-name as array-key; default value as array-value)
     */
    function getRequiredPageParams();

    /**
     * returns an array of the required parameters for the server. The array-keys are the parameter names and the array
     * values are the default values of each parameter
     *
     * @return array string to string map (param-name as array-key; default value as array-value)
     */
    function getRequiredServerParams();

    /**
     * get encoding of the page content
     *
     * @return string
     */
    function getPageContentEncoding();

    /**
     * get encoding of the page url
     *
     * @return string
     */
    function getPageUrlEncoding();

    /**
     * get encoding of the server url
     *
     * @return string
     */
    function getServerUrlEncoding();
}