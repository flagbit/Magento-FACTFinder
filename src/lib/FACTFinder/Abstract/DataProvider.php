<?php

/**
 * abstract data provider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_DataProvider
{
    protected $params = array();
    protected $config = array();
    protected $type;

    public function __construct(array $params = null, FACTFinder_Abstract_Configuration $config = null)
    {
        if ($params != null) $this->setParams($params);
        if ($config != null) $this->setConfig($config);
    }

    /**
     * set type to identify which data should be loaded. that could be a request path or any source identifier
     *
     * @param mixed target
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * return the data for the current config and params; the return type depends on the implementation
     *
     * @return mixed data
     */
    abstract public function getData();

    /**
     * sets factfinder params object
     *
     * @param array params
     * @return void
    **/
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * set single param
     *
     * @param string name
     * @param string value
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @param FACTFinder_Abstract_IConfiguration config
     */
    public function setConfig(FACTFinder_Abstract_Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
    **/
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return FACTFinder_Abstract_IConfiguration
    **/
    protected function getConfig()
    {
        return $this->config;
    }
}