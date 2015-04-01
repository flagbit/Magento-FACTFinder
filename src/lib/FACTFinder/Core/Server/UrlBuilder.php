<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * Assembles URLs to the FACT-Finder server for different kinds of
 * authentication based on the given parameters and the configuration.
 */
class UrlBuilder
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;


    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *        implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration
     * @param FACTFinder\Util\Parameters $parameters Optional parameters object
     *        to initialize the UrlBuilder with.
     */
    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->log->info("Initializing URL Builder.");

        $this->configuration = $configuration;
    }

    /**
     * Get URL without authentication data.
     * Note that this method may set a channel parameter if there is none
     * already.
     *
     * @param string $action The action to be targeted on the FACT-Finder
     *        server.
     * @param FACTFinder\Util\Parameters $parameters The parameters object from
     *        which to build the URL.
     *
     * @return string The full URL.
     */
    public function getNonAuthenticationUrl(
        $action,
        \FACTFinder\Util\Parameters $parameters
    ) {
        $configuration = $this->configuration;

        $this->ensureChannelParameter($parameters);

        $url = $this->buildAddress($action)
             . (count($parameters) ? '?' : '') . $parameters->toJavaQueryString();

        return $url;
    }

    /**
     * Returns a full URL with authentication data. The type of authentication
     * is determined from the configuration.
     * Note that this method may set a channel parameter if there is none
     * already.
     *
     * @param string $action The action to be targeted on the FACT-Finder
     *        server.
     * @param FACTFinder\Util\Parameters $parameters The parameters object from
     *        which to build the URL.
     *
     * @return string The full URL.
     *
     * @throws Exception if no valid authentication type was configured.
     */
    public function getAuthenticationUrl(
        $action,
        \FACTFinder\Util\Parameters $parameters
    ) {
        $this->ensureChannelParameter($parameters);

        $c = $this->configuration;
        if ($c->isAdvancedAuthenticationType())
            return $this->getAdvancedAuthenticationUrl($action, $parameters);
        else if ($c->isSimpleAuthenticationType())
            return $this->getSimpleAuthenticationUrl($action, $parameters);
        else if ($c->isHttpAuthenticationType())
            return $this->getHttpAuthenticationUrl($action, $parameters);
        else
            throw new \Exception('Invalid authentication type configured.');
    }

    /**
     * Get URL with advanced authentication encryption.
     *
     * @param string $action The action to be targeted on the FACT-Finder
     *        server.
     * @param FACTFinder\Util\Parameters $parameters The parameters object from
     *        which to build the URL.
     *
     * @return string The full URL.
     */
    protected function getAdvancedAuthenticationUrl(
        $action,
        \FACTFinder\Util\Parameters $parameters
    ) {
        $configuration = $this->configuration;

        $ts         = time() . '000'; //milliseconds needed
        $prefix     = $configuration->getAuthenticationPrefix();
        $postfix    = $configuration->getAuthenticationPostfix();
        $hashedPW   = md5($prefix
                    . $ts
                    . md5($configuration->getPassword())
                    . $postfix);
        $authenticationParameters = 'timestamp=' . $ts
                                  . '&username=' . $configuration->getUserName()
                                  . '&password=' . $hashedPW;

        $url = $this->buildAddress($action)
             . '?' . $parameters->toJavaQueryString()
             . (count($parameters) ? '&' : '') . $authenticationParameters;

        $this->log->info("Request Url: " . $url);
        return $url;
    }

    /**
     * Get URL with simple authentication encryption.
     *
     * @param string $action The action to be targeted on the FACT-Finder
     *        server.
     * @param FACTFinder\Util\Parameters $parameters The parameters object from
     *        which to build the URL.
     *
     * @return string The full URL.
     */
    protected function getSimpleAuthenticationUrl(
        $action,
        \FACTFinder\Util\Parameters $parameters
    ) {
        $configuration = $this->configuration;

        $ts = time() . '000'; //milliseconds needed but won't be considered
        $authenticationParameters = "timestamp=" . $ts
                        . '&username=' . $configuration->getUserName()
                        . '&password=' . md5($configuration->getPassword());

        $url = $this->buildAddress($action)
             . '?' . $parameters->toJavaQueryString()
             . (count($parameters) ? '&' : '') . $authenticationParameters;

        $this->log->info("Request Url: " . $url);
        return $url;
    }

    /**
     * Get URL with HTTP authentication.
     *
     * @param string $action The action to be targeted on the FACT-Finder
     *        server.
     * @param FACTFinder\Util\Parameters $parameters The parameters object from
     *        which to build the URL.
     *
     * @return string The full URL.
     */
    protected function getHttpAuthenticationUrl(
        $action,
        \FACTFinder\Util\Parameters $parameters
    ) {
        $configuration = $this->configuration;

        $authentication = sprintf(
            '%s:%s@',
            $configuration->getUserName(),
            $configuration->getPassword()
        );
        if ($authentication == ':@') $authentication = '';

        $url = $this->buildAddress($action, true)
             . (count($parameters) ? '?' : '') . $parameters->toJavaQueryString();

        $this->log->info("Request Url: " . $url);
        return $url;
    }

    /**
     * If no channel is set, try to fill it from configuration data.
     *
     * @param FACTFinder\Util\Parameters $parameters The parameters object to
     *        check.
     */
    protected function ensureChannelParameter($parameters) {
        if ((!isset($parameters['channel'])
            || $parameters['channel'] == '')
            && $this->configuration->getChannel() != ''
        ) {
            $parameters['channel'] = $this->configuration->getChannel();
        }
    }

    protected function buildAddress($action, $includeHttpAuthentication = false)
    {
        $configuration = $this->configuration;

        $authentication = '';
        if ($includeHttpAuthentication
            && $configuration->getUserName() != ''
            && $configuration->getPassword() != ''
        ) {
            $authentication = sprintf(
                '%s:%s@',
                $configuration->getUserName(),
                $configuration->getPassword()
            );
        }

        return $configuration->getRequestProtocol() . '://'
             . $authentication . $configuration->getServerAddress()
             . ':' . $configuration->getServerPort()
             . '/' . $configuration->getContext()
             . '/' . $action;
    }
}
