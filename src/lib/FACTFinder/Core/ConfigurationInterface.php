<?php
namespace FACTFinder\Core;

/**
 * Interface for all necessary configuration data to get the library running
 * (plus, any necessary custom values).
 */
interface ConfigurationInterface
{
    /**
     * @return bool
     */
    public function isDebugEnabled();

    /**
     * @param string name
     * @return string value
     */
    public function getCustomValue($name);

    /**
     * @return string
     */
    public function getRequestProtocol();

    /**
     * @return string
     */
    public function getServerAddress();

    /**
     * @return int
     */
    public function getServerPort();

    /**
     * @return string
     */
    public function getContext();

    /**
     * @return string
     */
    public function getChannel();

    /**
     * @return string
     */
    public function getLanguage();

    /**
     * @return boolean
     */
    public function isHttpAuthenticationType();

    /**
     * @return boolean
     */
    public function isSimpleAuthenticationType();

    /**
     * @return boolean
     */
    public function isAdvancedAuthenticationType();

    /**
     * @return string
     */
    public function getUserName();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getAuthenticationPrefix();

    /**
     * @return string
     */
    public function getAuthenticationPostfix();

    /**
     * Get mappings from server to client parameters.
     *
     * @return array
     */
    public function getClientMappings();

    /**
     * Get mappings from client to server parameters.
     *
     * @return array
     */
    public function getServerMappings();

    /**
     * Get parameters which should be ignored in client URLs.
     *
     * @return array with string as key and boolean true as value for each item
     */
    public function getIgnoredClientParameters();

    /**
     * Get parameters which should be ignored in server URLs.
     *
     * @return array with string as key and boolean true as value for each item
     */
    public function getIgnoredServerParameters();

    /**
     * Get parameters which are required in client URLs.
     *
     * @return array with parameter name as key and default value as value.
     */
    public function getRequiredClientParameters();

    /**
     * Get parameters which are required in server URLs.
     *
     * @return array with parameter name as key and default value as value.
     */
    public function getRequiredServerParameters();

    /**
     * Get default connect timeout for all adapters.
     *
     * @return int
     */
    public function getDefaultConnectTimeout();

    /**
     * Get default timeout for all adapters.
     *
     * @return int
     */
    public function getDefaultTimeout();

    /**
     * Get connect timeout for Suggest adapter.
     *
     * @return int
     */
    public function getSuggestConnectTimeout();

    /**
     * Get timeout for Suggest adapter.
     *
     * @return int
     */
    public function getSuggestTimeout();

    /**
     * Get connect timeout for Tracking adapter.
     *
     * @return int
     */
    public function getTrackingConnectTimeout();

    /**
     * Get timeout for Tracking adapter.
     *
     * @return int
     */
    public function getTrackingTimeout();

    /**
     * Get connect timeout for Import adapter.
     *
     * @return int
     */
    public function getImportConnectTimeout();

    /**
     * Get timeout for Import adapter-
     *
     * @return int
     */
    public function getImportTimeout();

    /**
     * Get encoding for content to be sent to the browser.
     *
     * @return string
     */
    public function getPageContentEncoding();

    /**
     * Get encoding for URLs of the client.
     *
     * @return string
     */
    public function getClientUrlEncoding();
}
