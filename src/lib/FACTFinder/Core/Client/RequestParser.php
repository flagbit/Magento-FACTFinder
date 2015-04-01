<?php
namespace FACTFinder\Core\Client;

use FACTFinder\Loader as FF;

/**
 * Extracts several data from the request made to the client.
 */
class RequestParser
{
    protected $clientRequestParameters;
    protected $serverRequestParameters;
    protected $requestTarget;

    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var AbstractEncodingConverter
     */
    protected $encodingConverter;

    /**
     * @var ParametersConverter
     */
    protected $parametersConverter;

    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *                            implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration
     * @param AbstractEncodingConverter $encodingConverter
     */
    function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;
        $this->encodingConverter = $encodingConverter;
        $this->parametersConverter = FF::getInstance(
            'Core\ParametersConverter',
            $loggerClass,
            $configuration
        );
    }

    /**
     * Loads parameters from the request and returns a Parameter object.
     * Also takes care of encoding conversion if necessary. Finally it will make
     * all necessary conversions according to the ignore/require/mapping
     * directives given in the configuration for the server. Use this method
     * for any other part of the library that needs the request parameters.
     *
     * @return Parameters Array of UTF-8 encoded and converted parameters
     */
    public function getRequestParameters()
    {
        if (is_null($this->serverRequestParameters))
        {
            $clientParameters = $this->getClientRequestParameters();
            $this->serverRequestParameters =
                $this->parametersConverter->convertClientToServerParameters(
                    $clientParameters
                );
        }

        return $this->serverRequestParameters;
    }

    /**
     * Loads parameters from the request and returns a Parameter object.
     * Also takes care of encoding conversion if necessary. However, the
     * parameters themselves are not converted (that is ignore, require and
     * mapping directives in the configuration are not taken into account).
     *
     * You won't usually need this method unless you really want to get access
     * to some parameters that would be ignored or mapped otherwise.
     *
     * For use with any other part of the library, use getRequestParameters()
     * instead, which converts the parameters for usage with the server.
     *
     * @return Parameters Array of UTF-8 encoded parameters
     */
    public function getClientRequestParameters()
    {
        if (is_null($this->clientRequestParameters))
        {
            if (isset($_SERVER['QUERY_STRING']))
            {
                // TODO: Respect variables_order so that conflicting variables
                //       lead to the same result as in $_REQUEST (save for
                //       $_COOKIE variables). This todo also goes for the second
                //       alternative.
                $parameters = FF::getInstance(
                    'Util\Parameters',
                    $_SERVER['QUERY_STRING']
                );

                $data = $_POST;

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if (is_array($value)) {
                            unset($data[$key]);
                        }
                    }
                }

                $parameters->setAll($data);
            }
            else if (isset($_GET))
            {
                $this->log->warn('$_SERVER[\'QUERY_STRING\'] is not available. '
                               . 'Using $_GET instead. This may cause problems '
                               . 'if the query string contains parameters with '
                               . 'non-[a-zA-Z0-9_] characters.');

                // Don't use $_REQUEST, because it also contains $_COOKIE.
                // Note that we don't have to URL decode here, because _GET is
                // already URL decoded.
                $parameters = FF::getInstance(
                    'Util\Parameters',
                    array_merge($_POST, $_GET)
                );
            }
            else
            {
                // For CLI use:
                $parameters = FF::getInstance('Util\Parameters');
            }

            // Convert encoding and then the parameters themselves
            $this->clientRequestParameters = $this->encodingConverter != null ? $this->encodingConverter->decodeClientUrlData($parameters) : $parameters;
           
        }

        return $this->clientRequestParameters;
    }

    /**
     * Get target of the current request.
     *
     * @return string request target
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget === null)
        {
            // Workaround for some servers (IIS) which do not provide
            // $_SERVER['REQUEST_URI']. Taken from
            // http://php.net/manual/en/reserved.variables.server.php#108186
            if(!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
                if(isset($_SERVER['QUERY_STRING'])) {
                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            if (strpos($_SERVER['REQUEST_URI'], '?') === false)
                $this->requestTarget = $_SERVER['REQUEST_URI'];
            else
            {
                $parts = explode('?', $_SERVER['REQUEST_URI']);
                $this->requestTarget = $parts[0];
            }

            // Use rawurldecode() so that +'s are not converted to spaces.
            $this->requestTarget = rawurldecode($this->requestTarget);
            if ($this->encodingConverter != null)
            {
                $this->requestTarget = $this->encodingConverter ->decodeClientUrlData($this->requestTarget);
            }
        }
        return $this->requestTarget;
    }
}
