<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * handles the issue to create useable object from the data delivered by the dataprovider.
 * this class can also be seen as a factory, but it is rather an adapter, because it converst the responded objects into
 * the useable, unique objects
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Adapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_Adapter
{
    protected $paramsParser;
    protected $dataProvider;
    protected $encodingHandler;
	
	protected $log;
	
	protected $data;
	
    final public function __construct(FACTFinder_Abstract_DataProvider $dataProvider, FACTFinder_ParametersParser $paramsParser,
        FACTFinder_EncodingHandler $encodingHandler, FACTFinder_Abstract_Logger $log = null)
    {
		if(isset($log))
			$this->log = $log;
		else
			$this->log = FF::getSingleton('nullLogger');
        $this->setDataProvider($dataProvider);
        $this->setParamsParser($paramsParser);
        $this->setEncodingHandler($encodingHandler);
        $this->init();
    }

    /**
     * can be overwritten to do initialising issues, that would normaly done by the constructor. it will be called at
     * the end of the constructor
     *
     * @return void
     */
    protected function init(){}

    public function setParam($name, $value)
    {
        $this->getDataProvider()->setParam($name, $value);
    }

    public function setParams($params)
    {
        $this->getDataProvider()->setParams($params);
    }
	
    /**
     * returns the data lazily. if it isn't available yet, it will be requested from the dataprovider.
	 * decorates the dataprovider::getData method so a inheriting class does not have to use the dataprovider
     */
    protected function getData()
    {
		if(!isset($this->data)) {
			$this->data = $this->getDataProvider()->getData();
		}
        return $this->data;
    }
	
	protected function reloadData()
    {
		$this->data = $this->getDataProvider()->getData();
		return $this->data;
    }
	
    /**
     * set data provider
     *
     * @param FACTFinder_Abstract_DataProvider
     * @return void
    **/
    public function setDataProvider(FACTFinder_Abstract_DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * get data provider
     *
     * @return FACTFinder_Abstract_DataProvider
    **/
    protected function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * set parameter parser
     *
     * @param FACTFinder_ParametersParser $paramsParser
     */
    public function setParamsParser(FACTFinder_ParametersParser $paramsParser)
    {
        $this->paramsParser = $paramsParser;
    }

    /**
     * returns the used factfinder params object.
     *
     * @return FACTFinder_ParametersParser
    **/
    protected function getParamsParser()
    {
        return $this->paramsParser;
    }

    /**
     * set encoding handler
     *
     * @param FACTFinder_EncodingHandler $encodingHandler
     */
    public function setEncodingHandler(FACTFinder_EncodingHandler $encodingHandler)
    {
        $this->encodingHandler = $encodingHandler;
    }

    /**
     * returns the used encoding handler
     *
     * @return FACTFinder_EncodingHandler
    **/
    protected function getEncodingHandler()
    {
        return $this->encodingHandler;
    }
}