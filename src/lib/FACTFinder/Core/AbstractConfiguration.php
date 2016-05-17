<?php
namespace FACTFinder\Core;

/**
 * Base class for the configuration implementation.
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{
    /**
     * @param string name
     * @return string value
     */
    public function getCustomValue($name)
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getRequestProtocol()
    {
        return 'http';
    }

    /**
     * @return string
     */
    public function getServerAddress()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getServerPort()
    {
        return '80';
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return null;
    }

    /**
     * @return boolean
     */
    public function isHttpAuthenticationType()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isSimpleAuthenticationType()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isAdvancedAuthenticationType()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getAuthenticationPrefix()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getAuthenticationPostfix()
    {
        return null;
    }

    /**
     * Get mappings from server to client parameters.
     *
     * @return array
     */
    public function getClientMappings()
    {
        return array();
    }

    /**
     * Get mappings from client to server parameters.
     *
     * @return array
     */
    public function getServerMappings()
    {
        return array();
    }

    /**
     * Get parameters which should be ignored in client URLs.
     *
     * @return array with string as key and boolean true as value for each item
     */
    public function getIgnoredClientParameters()
    {
        return array();
    }

    /**
     * Get parameters which should be ignored in server URLs.
     *
     * @return array with string as key and boolean true as value for each item
     */
    public function getIgnoredServerParameters()
    {
        return array();
    }

    /**
     * Get parameters which are allowed in client URLs.
     * 
     * @return array with string as key and boolean true as value for each item
     */
    public function getWhitelistClientParameters()
    {
        return array();
    }

    /**
     * Get parameters which are allowed in server URLs.
     * 
     * @return array with string as key and boolean true as value for each item
     */
    public function getWhitelistServerParameters()
    {
        return array(
            '/^filter.*/' => true,
            '/^sort.*/' => true,
            '/^substringFilter.*/' => true,
            'advisorStatus' => true,
            'callback' => true,
            'catalog' => true,
            'channel' => true,
            'cookieId' => true,
            'count' => true,
            'do' => true,
            'event' => true,
            'followSearch' => true,
            'format' => true,
            'id' => true,
            'ids' => true,
            'idsOnly' => true,
            'ignoreForCache' => true,
            'isArticleNumber' => true,
            'log' => true,
            'mainId' => true,
            'masterId' => true,
            'maxRecordCount' => true,
            'maxResults' => true,
            'navigation' => true,
            'noArticleNumberSearch' => true,
            'omitContextName' => true,
            'origPageSize' => true,
            'origPos' => true,
            'page' => true,
            'pageSize' => true,
            'pos' => true,
            'price' => true,
            'productNumber' => true,
            'productsPerPage' => true,
            'query' => true,
            'queryFromSuggest' => true,
            'searchField' => true,
            'sid' => true,
            'simi' => true,
            'title' => true,
            'useAsn' => true,
            'useAso' => true,
            'useCampaigns' => true,
            'useFoundWords' => true,
            'useKeywords' => true,
            'usePersonalization' => true,
            'userId' => true,
            'userInput' => true,
            'useSemanticEnhancer' => true,
            'verbose' => true,
            'wordCount' => true
        );
    }

    /**
     * Get parameters which are required in client URLs.
     *
     * @return array with parameter name as key and default value as value.
     */
    public function getRequiredClientParameters()
    {
        return array();
    }

    /**
     * Get parameters which are required in server URLs.
     *
     * @return array with parameter name as key and default value as value.
     */
    public function getRequiredServerParameters()
    {
        return array();
    }

    /**
     * Get default connect timeout for all adapters.
     *
     * @return int
     */
    public function getDefaultConnectTimeout()
    {
        return 2;
    }

    /**
     * Get default timeout for all adapters.
     *
     * @return int
     */
    public function getDefaultTimeout()
    {
        return 4;
    }

    /**
     * Get connect timeout for Suggest adapter.
     *
     * @return int
     */
    public function getSuggestConnectTimeout()
    {
        return 2;
    }

    /**
     * Get timeout for Suggest adapter.
     *
     * @return int
     */
    public function getSuggestTimeout()
    {
        return 2;
    }

    /**
     * Get connect timeout for Tracking adapter.
     *
     * @return int
     */
    public function getTrackingConnectTimeout()
    {
        return 2;
    }

    /**
     * Get timeout for Tracking adapter.
     *
     * @return int
     */
    public function getTrackingTimeout()
    {
        return 2;
    }

    /**
     * Get connect timeout for Import adapter.
     *
     * @return int
     */
    public function getImportConnectTimeout()
    {
        return 10;
    }

    /**
     * Get timeout for Import adapter-
     *
     * @return int
     */
    public function getImportTimeout()
    {
        return 360;
    }

    /**
     * Get encoding for content to be sent to the browser.
     *
     * @return string
     */
    public function getPageContentEncoding()
    {
        return 'UTF-8';
    }

    /**
     * Get encoding for URLs of the client.
     *
     * @return string
     */
    public function getClientUrlEncoding()
    {
        return 'UTF-8';
    }
}

