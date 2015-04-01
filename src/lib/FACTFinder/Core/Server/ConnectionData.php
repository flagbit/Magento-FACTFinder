<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * Represents all data belonging to a single connection. It holds all data
 * necessary to configure a request and can also be filled with the
 * corresponding response.
 */
class ConnectionData
{
    /**
     * @var \FACTFinder\Util\Parameters
     */
    private $httpHeaderFields;

    /**
     * @var string
     */
    private $action;

    /**
     * @var \FACTFinder\Util\Parameters
     */
    private $parameters;

    /**
     * @var mixed[]
     */
    private $connectionOptions = array();

    /**
     * @var Response
     */
    private $response;

    /**
     * @var string
     */
    private $previousUrl;

    /**
     * Optionally takes a Parameters object to initialize the query parameters.
     * @param \FACTFinder\Util\Parameters $parameters
     * @return type
     */
    public function __construct($parameters = null)
    {
        if (FF::isInstanceOf($parameters, 'Util\Parameters'))
            $this->parameters = $parameters;
        else
            $this->parameters = FF::getInstance('Util\Parameters');

        $this->httpHeaderFields = FF::getInstance('Util\Parameters');
        $this->action = '';
        $this->setNullResponse();
    }

    /**
     * Returns the parameters object used for the connection, on which HTTP
     * header fields can be read and changed.
     *
     * @return \FACTFinder\Util\Parameters
     */
    public function getHttpHeaderFields()
    {
        return $this->httpHeaderFields;
    }

    /**
     * Set the action to be queried on the FACT-Finder server. e.g. "Search.ff".
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get the action to be queried on the FACT-Finder server. e.g. "Search.ff".
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns the parameters object used for the connection, on which query
     * parameters can be read and changed.
     *
     * @return \FACTFinder\Util\Parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set an option for the connection. This can be an arbitrary pair of key
     * and value. How this is used depends on the kind of connection that is set
     * up (e.g. cURL or socket). We simply provide an array to store options
     * specific to this connection.
     *
     * @param mixed $name The option's identifier. This can be any value that is
     *        a valid array index in PHP (hence, it SHOULD be a string or an
     *        integer; all other types will be interpreted as a string).
     * @param mixed $value The option's value.
     */
    public function setConnectionOption($name, $value)
    {
        $this->connectionOptions[$name] = $value;
    }

    /**
     * Sets all given connection options at once. If an option with the same
     * name already exists, the old value will be replaced. Options that are not
     * contained in the $options parameter will retain their value(s).
     * @param mixed[] $options An array of options. For more information on
     *        valid keys and values see setConnectionOption($name, $value).
     */
    public function setConnectionOptions($options)
    {
        // We cannot use array_merge() here, because that does not preserve
        // numeric keys. Implementing this with a loop also has the advantage
        // of not creating a new, third array.
        foreach ($options as $k => $v)
            $this->connectionOptions[$k] = $v;
    }

    /**
     * @param mixed $name The option's identifier.
     * @return bool
     */
    public function issetConnectionOption($name)
    {
        return isset($this->connectionOptions[$name]);
    }

    /**
     * @param mixed $name The option's identifier.
     * @return mixed The option's value.
     */
    public function getConnectionOption($name)
    {
        return $this->connectionOptions[$name];
    }

    /**
     * Get an array of all set connection options.
     * @return mixed[]
     */
    public function getConnectionOptions()
    {
        return $this->connectionOptions;
    }

    /**
     * Set a response for the current connection settings along with the URL
     * which was used to obtain the response.
     * @param Response $response
     * @param string $url The URL corresponding to $response.
     */
    public function setResponse(Response $response, $url)
    {
        $this->response = $response;
        $this->previousUrl = $url;
    }

    /**
     * Set a null response - e.g. if the connection could not even be attempted
     * at all.
     */
    public function setNullResponse()
    {
        $this->response = FF::getInstance('Core\Server\NullResponse');
        $this->previousUrl = null;
    }

    /**
     * Get the response that was most recently set.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the URL corresponding to the currently stored Response.
     *
     * @return string
     */
    public function getPreviousUrl()
    {
        return $this->previousUrl;
    }
}
