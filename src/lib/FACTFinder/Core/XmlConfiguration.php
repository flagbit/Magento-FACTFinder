<?php
namespace FACTFinder\Core;

/**
 * Implements ConfigurationInterface by reading from an XML file. Also allows
 * for some values to be changed later on.
 */
class XmlConfiguration implements ConfigurationInterface
{
    const HTTP_AUTHENTICATION     = 'http';
    const SIMPLE_AUTHENTICATION   = 'simple';
    const ADVANCED_AUTHENTICATION = 'advanced';

    /**
     * @var SimpleXMLElement XML representation of the configuration.
     */
    private $configuration;

    private $authenticationType = null;

    private $clientMappings;
    private $serverMappings;
    private $ignoredClientParameters;
    private $ignoredServerParameters;
    private $requiredClientParameters;
    private $requiredServerParameters;

    private $pageContentEncoding = null;
    private $clientUrlEncoding = null;

    /**
     * Create a new configuration from an XML file. The outer element in the XML
     * tree must be named <configuration>. The actual data is read from a child
     * of this element whose name can be specified through the second argument.
     * @param string $fileName Name of configuration file.
     * @param type $element Name of the XML element from which to read
     *                      configuration data.
     * @return XmlConfiguration
     */
    public function __construct($fileName, $element)
    {
        libxml_use_internal_errors(true);
        $xmlData = new \SimpleXMLElement($fileName, 0, true);
        if (!isset($xmlData->$element))
            throw new \Exception("Specified configuration file does not contain section $element");
        $this->configuration = $xmlData->$element;
    }

    public function isDebugEnabled()
    {
        return (string)$this->configuration->debug == 'true';
    }

    public function getCustomValue($name)
    {
        return (string)$this->configuration->$name;
    }

    public function getRequestProtocol()
    {
        return (string)$this->configuration->connection->protocol;
    }

    public function getServerAddress()
    {
        return (string)$this->configuration->connection->address;
    }

    public function getServerPort()
    {
        return (int)$this->configuration->connection->port;
    }

    public function getContext()
    {
        return (string)$this->configuration->connection->context;
    }

    public function getChannel()
    {
        return (string)$this->configuration->connection->channel;
    }

    public function getLanguage()
    {
        return (string)$this->configuration->connection->language;
    }

    public function isHttpAuthenticationType()
    {
        return $this->retrieveAuthenticationType() == self::HTTP_AUTHENTICATION;
    }

    public function isSimpleAuthenticationType()
    {
        return $this->retrieveAuthenticationType() == self::SIMPLE_AUTHENTICATION;
    }

    public function isAdvancedAuthenticationType()
    {
        return $this->retrieveAuthenticationType() == self::ADVANCED_AUTHENTICATION;
    }

    private function retrieveAuthenticationType()
    {
        if (is_null($this->authenticationType))
            $this->authenticationType = (string)$this->configuration
                                                     ->connection
                                                     ->authentication
                                                     ->type;
        return strtolower($this->authenticationType);
    }

    public function makeHttpAuthenticationType()
    {
        $this->authenticationType = self::HTTP_AUTHENTICATION;
    }

    public function makeSimpleAuthenticationType()
    {
        $this->authenticationType = self::SIMPLE_AUTHENTICATION;
    }

    public function makeAdvancedAuthenticationType()
    {
        $this->authenticationType = self::ADVANCED_AUTHENTICATION;
    }

    public function getUserName()
    {
        return (string)$this->configuration->connection->authentication->username;
    }

    public function getPassword()
    {
        return (string)$this->configuration->connection->authentication->password;
    }

    public function getAuthenticationPrefix()
    {
        return (string)$this->configuration->connection->authentication->prefix;
    }

    public function getAuthenticationPostfix()
    {
        return (string)$this->configuration->connection->authentication->postfix;
    }

    public function getClientMappings()
    {
        if ($this->clientMappings == null) {
            $this->clientMappings = $this->retrieveMappings(
                $this->configuration->parameters->client
            );
        }
        return $this->clientMappings;
    }

    public function getServerMappings()
    {
        if ($this->serverMappings == null) {
            $this->serverMappings = $this->retrieveMappings(
                $this->configuration->parameters->server
            );
        }
        return $this->serverMappings;
    }

    private function retrieveMappings(\SimpleXMLElement $section)
    {
        $mappings = array();
        if (isset($section->mapping)) {
            //load mappings
            foreach($section->mapping as $rule) {
                $mappings[(string)$rule->attributes()->from] =
                    (string)$rule->attributes()->to;
            }
        }
        return $mappings;
    }

    public function getIgnoredClientParameters()
    {
        if ($this->ignoredClientParameters == null) {
            $this->ignoredClientParameters = $this->retrieveIgnoredParameters(
                $this->configuration->parameters->client
            );
        }
        return $this->ignoredClientParameters;
    }

    public function getIgnoredServerParameters()
    {
        if ($this->ignoredServerParameters == null) {
            $this->ignoredServerParameters = $this->retrieveIgnoredParameters(
                $this->configuration->parameters->server
            );
        }
        return $this->ignoredServerParameters;
    }

    private function retrieveIgnoredParameters(\SimpleXMLElement $section)
    {
        $ignoredParameters = array();
        if (isset($section->ignore)) {
            //load ignore rules
            foreach($section->ignore as $rule) {
                $ignoredParameters[(string)$rule->attributes()->name] = true;
            }
        }
        return $ignoredParameters;
    }

    public function getRequiredClientParameters()
    {
        if ($this->requiredClientParameters == null) {
            $this->requiredClientParameters = $this->retrieveRequiredParameters(
                $this->configuration->parameters->client
            );
        }
        return $this->requiredClientParameters;
    }

    public function getRequiredServerParameters()
    {
        if ($this->requiredServerParameters == null) {
            $this->requiredServerParameters = $this->retrieveRequiredParameters(
                $this->configuration->parameters->server
            );
        }
        return $this->requiredServerParameters;
    }

    private function retrieveRequiredParameters(\SimpleXMLElement $section)
    {
        $requiredParameters = array();
        if (isset($section->require)) {
            //load require rules
            foreach($section->require as $rule) {
                $requiredParameters[(string)$rule->attributes()->name] =
                    (string)$rule->attributes()->default;
            }
        }
        return $requiredParameters;
    }

    public function getDefaultConnectTimeout()
    {
        return (int)$this->configuration->connection->timeouts->defaultConnectTimeout;
    }

    public function getDefaultTimeout()
    {
        return (int)$this->configuration->connection->timeouts->defaultTimeout;
    }

    public function getSuggestConnectTimeout()
    {
        return (int)$this->configuration->connection->timeouts->suggestConnectTimeout;
    }

    public function getSuggestTimeout()
    {
        return (int)$this->configuration->connection->timeouts->suggestTimeout;
    }

    public function getTrackingConnectTimeout()
    {
        return (int)$this->configuration->connection->timeouts->trackingConnectTimeout;
    }

    public function getTrackingTimeout()
    {
        return (int)$this->configuration->connection->timeouts->trackingTimeout;
    }

    public function getImportConnectTimeout()
    {
        return (int)$this->configuration->connection->timeouts->importConnectTimeout;
    }

    public function getImportTimeout()
    {
        return (int)$this->configuration->connection->timeouts->importTimeout;
    }

    public function getPageContentEncoding()
    {
        if (is_null($this->pageContentEncoding))
            $this->pageContentEncoding = (string)$this->configuration->encoding->pageContent;

        return $this->pageContentEncoding;
    }

    public function getClientUrlEncoding()
    {
        if (is_null($this->clientUrlEncoding))
            $this->clientUrlEncoding = (string)$this->configuration->encoding->clientUrl;

        return $this->clientUrlEncoding;
    }

    public function setPageContentEncoding($encoding)
    {
        $this->pageContentEncoding = (string)$encoding;
    }

    public function setClientUrlEncoding($encoding)
    {
        $this->clientUrlEncoding = (string)$encoding;
    }
}
