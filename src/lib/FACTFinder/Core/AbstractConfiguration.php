<?php
namespace FACTFinder\Core;

/**
 * Base class for the configuration implementation.
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{

    public function getCustomValue($name)
    {
        return null;
    }

    public function isDebugEnabled()
    {
        return false;
    }

    public function getRequestProtocol()
    {
        return 'http';
    }

    public function getServerAddress()
    {
        return null;
    }

    public function getServerPort()
    {
        return '80';
    }

    public function getContext()
    {
        return null;
    }

    public function getChannel()
    {
        return null;
    }

    public function getLanguage()
    {
        return null;
    }

    public function isHttpAuthenticationType()
    {
        return false;
    }

    public function isSimpleAuthenticationType()
    {
        return false;
    }

    public function isAdvancedAuthenticationType()
    {
        return false;
    }

    private function retrieveType()
    {
        return null;
    }

    public function getUserName()
    {
        return null;
    }

    public function getPassword()
    {
        return null;
    }

    public function getAuthenticationPrefix()
    {
        return null;
    }

    public function getAuthenticationPostfix()
    {
        return null;
    }

    public function getClientMappings()
    {
        return array();
    }

    public function getServerMappings()
    {
        return array();
    }

    public function getIgnoredClientParameters()
    {
        return array();
    }

    public function getIgnoredServerParameters()
    {
        return array();
    }

    public function getWhitelistClientParameters()
    {
        return array();
    }

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

    public function getRequiredClientParameters()
    {
        return array();
    }

    public function getRequiredServerParameters()
    {
        return array();
    }

    public function getDefaultConnectTimeout()
    {
        return 2;
    }

    public function getDefaultTimeout()
    {
        return 4;
    }

    public function getSuggestConnectTimeout()
    {
        return 2;
    }

    public function getSuggestTimeout()
    {
        return 2;
    }

    public function getTrackingConnectTimeout()
    {
        return 2;
    }

    public function getTrackingTimeout()
    {
        return 2;
    }

    public function getImportConnectTimeout()
    {
        return 10;
    }

    public function getImportTimeout()
    {
        return 360;
    }

    public function getPageContentEncoding()
    {
        return 'UTF-8';
    }

    public function getClientUrlEncoding()
    {
        return 'UTF-8';
    }
}

