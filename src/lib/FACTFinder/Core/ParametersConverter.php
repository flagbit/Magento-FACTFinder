<?php
namespace FACTFinder\Core;

/**
 * Handles the conversion of parameters between requests to the client and
 * requests to the FACT-Finder server (and vice-versa).
 */
class ParametersConverter
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
     *                            implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration Configuration object to use.
     */
    public function __construct(
        $loggerClass,
        ConfigurationInterface $configuration
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;
    }

    /**
     * @param Parameters $clientParameters Parameters obtained from a request to
     *        the client.
     * @return Parameters Parameters ready for use with FACT-Finder.
     */
    public function convertClientToServerParameters($clientParameters)
    {
        $result = clone $clientParameters;
        $this->applyParameterMappings($result, $this->configuration->getServerMappings());
        $this->removeIgnoredParameters($result, $this->configuration->getIgnoredServerParameters());
        $this->ensureChannelParameter($result);
        $this->addRequiredParameters($result, $this->configuration->getRequiredServerParameters());

        return $result;
    }

    /**
     * @param Parameters $clientParameters Parameters obtained from FACT-Finder.
     * @return Parameters Parameters ready for use in requests to the client.
     */
    public function convertServerToClientParameters($serverParameters)
    {
        $result = clone $serverParameters;
        $this->applyParameterMappings($result, $this->configuration->getClientMappings());
        $this->removeIgnoredParameters($result, $this->configuration->getIgnoredClientParameters());
        $this->addRequiredParameters($result, $this->configuration->getRequiredClientParameters());

        return $result;
    }

    /**
     * Changes the keys in a Parameters object according to the given mapping
     * rules.
     * @param Parameters $parameters Parameters to be modified.
     * @param string[] $mappingRules Associative array of mapping rules.
     *        Parameter names will be mapped from keys to values of this array.
     */
    protected function applyParameterMappings($parameters, $mappingRules)
    {
        foreach ($mappingRules as $k => $v)
        {
            if ($k != $v && isset($parameters[$k]))
            {
                $parameters[$v] = $parameters[$k];
                unset($parameters[$k]);
            }
        }
    }

    /**
     * Removes keys from a Parameters object according to the given ignore
     * rules. It basically turns the parameters into the set difference of the
     * parameters and the ignore rules based on keys.
     * @param Parameters $parameters Parameters to be modified.
     * @param bool[] $ignoreRules Array of parameters to be ignored. The keys
     *        are the parameter names, the values are simply "true", but could
     *        technically have any value.
     */
    protected function removeIgnoredParameters($parameters, $ignoreRules)
    {
        foreach ($ignoreRules as $k => $v)
            unset($parameters[$k]);
    }

    /**
     * Ensures that the passed parameters object has a "channel" parameter by
     * adding one if necessary.
     * @param Parameters $parameters Parameters to be modifier.
     */
    protected function ensureChannelParameter($parameters)
    {
        if (!isset($parameters['channel']) || strlen($parameters['channel']) == 0)
            $parameters['channel'] = $this->configuration->getChannel();
    }

    /**
     * Adds keys to an array of parameters according to the given require rules.
     * @param Parameters $parameters Parameters to be modified.
     * @param string[] $ignoreRules Array of required parameters. The keys are
     *        the names of the required parameter, the values are default values
     *        to be used if the parameter is not present.
     */
    protected function addRequiredParameters($parameters, $requireRules)
    {
        foreach ($requireRules as $k => $v)
            if (!isset($parameters[$k]))
                $parameters[$k] = $v;
    }
}
