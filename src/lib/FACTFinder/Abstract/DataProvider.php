<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * abstract data provider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: DataProvider.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_DataProvider
{
    protected $params = array();
    protected $config = array();
    protected $type;
	
	protected $log;

    public function __construct(array $params = null, FACTFinder_Abstract_Configuration $config = null, FACTFinder_Abstract_Logger $log = null)
    {
		if(isset($log))
			$this->log = $log;
		else
			$this->log = FF::getSingleton('nullLogger');
		$this->log->info("Initializing data provider.");
        if ($params != null) $this->setParams($params);
        if ($config != null) $this->setConfig($config);
    }

    /**
     * set type to identify which data should be loaded. that could be a request path or any source identifier
     *
     * @param mixed target
     **/
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * return the data for the current config and params; the return type depends on the implementation
     *
     * @return mixed data
     **/
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
     **/
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }
	
	/**
     * unset single param
     *
     * @param string name
     * @return void
     **/
    public function unsetParam($name)
    {
        unset($this->params[$name]);
    }
	
	/**
     * set single param with multiple values
     *
     * @param string name
     * @param array of strings values
     * @return void
     **/
    public function setArrayParam($name, $values)
    {
        $this->params[$name] = $values;
    }
	
    /**
     * @param FACTFinder_Abstract_IConfiguration config
     **/
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
	 * This is public, so that adapters don't need their own config objects.
	 *
     * @return FACTFinder_Abstract_IConfiguration
     **/
    public function getConfig()
    {
        return $this->config;
    }
}