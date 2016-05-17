<?php
namespace FACTFinder\Core;

/**
 * Implements ConfigurationInterface by reading from a given array
 */
class ArrayConfiguration extends AbstractConfiguration
{
    const HTTP_AUTHENTICATION     = 'http';
    const SIMPLE_AUTHENTICATION   = 'simple';
    const ADVANCED_AUTHENTICATION = 'advanced';

    /**
     * @var array the configuration.
     */
    private $configuration;

    private $clientMappings;
    private $serverMappings;
    private $ignoredClientParameters;
    private $ignoredServerParameters;
    private $whitelistClientParameters;
    private $whitelistServerParameters;
    private $requiredClientParameters;
    private $requiredServerParameters;

    /**
     * Create a new configuration from an array
     * @param array $config The configuration data as array
     * @param string $section The configuration section
     * @return ArrayConfiguration
     *
     * @throws \Exception
     */
    public function __construct(array $config, $section)
    {
        if (!isset($config[$section]))
            throw new \Exception("Specified configuration array does not contain section $section");

        $this->configuration = $config[$section];
    }

    public function isDebugEnabled()
    {
        return $this->configuration['debug'] === true;
    }

    public function getCustomValue($name)
    {
        return $this->configuration[$name];
    }

    public function getRequestProtocol()
    {
        return $this->configuration['connection']['protocol'];
    }

    public function getServerAddress()
    {
        return $this->configuration['connection']['address'];
    }

    public function getServerPort()
    {
        return $this->configuration['connection']['port'];
    }

    public function getContext()
    {
        return $this->configuration['connection']['context'];
    }

    public function getChannel()
    {
        return $this->configuration['connection']['channel'];
    }

    public function getLanguage()
    {
        return $this->configuration['connection']['language'];
    }

    public function isHttpAuthenticationType()
    {
        return $this->retrieveAuthenticationType() === self::HTTP_AUTHENTICATION;
    }

    public function isSimpleAuthenticationType()
    {
        return $this->retrieveAuthenticationType() === self::SIMPLE_AUTHENTICATION;
    }

    public function isAdvancedAuthenticationType()
    {
        return $this->retrieveAuthenticationType() === self::ADVANCED_AUTHENTICATION;
    }

    private function retrieveAuthenticationType()
    {
        return strtolower($this->configuration['connection']['authentication']['type']);
    }

    public function makeHttpAuthenticationType()
    {
        $this->configuration['connection']['authentication']['type'] = self::HTTP_AUTHENTICATION;
    }

    public function makeSimpleAuthenticationType()
    {
        $this->configuration['connection']['authentication']['type'] = self::SIMPLE_AUTHENTICATION;
    }

    public function makeAdvancedAuthenticationType()
    {
        $this->configuration['connection']['authentication']['type'] = self::ADVANCED_AUTHENTICATION;
    }

    public function getUserName()
    {
        return $this->configuration['connection']['authentication']['username'];
    }

    public function getPassword()
    {
        return $this->configuration['connection']['authentication']['password'];
    }

    public function getAuthenticationPrefix()
    {
        return $this->configuration['connection']['authentication']['prefix'];
    }

    public function getAuthenticationPostfix()
    {
        return $this->configuration['connection']['authentication']['postfix'];
    }

    public function getClientMappings()
    {
        if ($this->clientMappings == null) {
            $this->clientMappings = $this->retrieveMappings($this->configuration['parameters']['client']);
        }
        return $this->clientMappings;
    }

    public function getServerMappings()
    {
        if ($this->serverMappings == null) {
            $this->serverMappings = $this->retrieveMappings($this->configuration['parameters']['server']);
        }
        return $this->serverMappings;
    }

    private function retrieveMappings($section)
    {
        $mappings = array();
        if (isset($section['mapping']) && is_array($section['mapping'])) {
            //load mappings
            foreach($section['mapping'] as $rule) {
                $mappings[$rule['from']] = $rule['to'];
            }
        }
        return $mappings;
    }

    public function getIgnoredClientParameters()
    {
        if ($this->ignoredClientParameters == null) {
            $this->ignoredClientParameters = $this->retrieveIgnoredParameters(
                $this->configuration['parameters']['client']
            );
        }
        return $this->ignoredClientParameters;
    }

    public function getIgnoredServerParameters()
    {
        if ($this->ignoredServerParameters == null) {
            $this->ignoredServerParameters = $this->retrieveIgnoredParameters(
                $this->configuration['parameters']['server']
            );
        }
        return $this->ignoredServerParameters;
    }

    private function retrieveIgnoredParameters($section)
    {
        $ignoredParameters = array();
        if (isset($section['ignore']) && is_array($section['ignore'])) {
            //load ignore rules
            foreach($section['ignore'] as $name) {
                $ignoredParameters[$name] = true;
            }
        }
        return $ignoredParameters;
    }

    public function getWhitelistClientParameters()
    {
        if ($this->whitelistClientParameters == null) {
            $this->whitelistClientParameters = $this->retrieveWhitelistParameters(
                $this->configuration['parameters']['client']
            );
        }
        return $this->whitelistClientParameters;
    }

    public function getWhitelistServerParameters()
    {
        if ($this->whitelistServerParameters == null) {
            $this->whitelistServerParameters = $this->retrieveWhitelistParameters(
                $this->configuration['parameters']['server']
            );
            if (empty($this->whitelistServerParameters)) {
                $this->whitelistServerParameters = parent::getWhitelistServerParameters();
            }
        }
        return $this->whitelistServerParameters;
    }

    private function retrieveWhitelistParameters($section)
    {
        $whitelist = array();
        if (isset($section['whitelist']) && is_array($section['whitelist'])) {
            //load whitelist
            foreach($section['whitelist'] as $name) {
                $whitelist[$name] = true;
            }
        }
        return $whitelist;
    }

    public function getRequiredClientParameters()
    {
        if ($this->requiredClientParameters == null) {
            $this->requiredClientParameters = $this->retrieveRequiredParameters(
                $this->configuration['parameters']['client']
            );
        }
        return $this->requiredClientParameters;
    }

    public function getRequiredServerParameters()
    {
        if ($this->requiredServerParameters == null) {
            $this->requiredServerParameters = $this->retrieveRequiredParameters(
                $this->configuration['parameters']['server']
            );
        }
        return $this->requiredServerParameters;
    }

    private function retrieveRequiredParameters($section)
    {
        $requiredParameters = array();
        if (isset($section['require']) && is_array($section['require'])) {
            //load require rules
            foreach($section['require'] as $rule) {
                $requiredParameters[$rule['name']] = $rule['default'];
            }
        }
        return $requiredParameters;
    }

    public function getDefaultConnectTimeout()
    {
        return $this->configuration['connection']['timeouts']['defaultConnectTimeout'];
    }

    public function getDefaultTimeout()
    {
        return $this->configuration['connection']['timeouts']['defaultTimeout'];
    }

    public function getSuggestConnectTimeout()
    {
        return $this->configuration['connection']['timeouts']['suggestConnectTimeout'];
    }

    public function getSuggestTimeout()
    {
        return $this->configuration['connection']['timeouts']['suggestTimeout'];
    }

    public function getTrackingConnectTimeout()
    {
        return $this->configuration['connection']['timeouts']['trackingConnectTimeout'];
    }

    public function getTrackingTimeout()
    {
        return $this->configuration['connection']['timeouts']['trackingTimeout'];
    }

    public function getImportConnectTimeout()
    {
        return $this->configuration['connection']['timeouts']['importConnectTimeout'];
    }

    public function getImportTimeout()
    {
        return $this->configuration['connection']['timeouts']['importTimeout'];
    }

    public function getPageContentEncoding()
    {
        return $this->configuration['encoding']['pageContent'];
    }

    public function getClientUrlEncoding()
    {
        return $this->configuration['encoding']['clientUrl'];
    }

    public function setPageContentEncoding($encoding)
    {
        $this->configuration['encoding']['pageContent'] = $encoding;
    }

    public function setClientUrlEncoding($encoding)
    {
        $this->configuration['encoding']['clientUrl'] = $encoding;
    }
}
