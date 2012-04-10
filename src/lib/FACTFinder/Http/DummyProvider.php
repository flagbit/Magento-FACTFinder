<?php

/**
 * this data provider loads the data from local static xml files
 * it is only intended to be used for testing
 *
 * @author    Rudolf Batt <rb@omikron.net>, Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: DummyProvider.php 44086 2012-02-29 17:19:43Z martin.buettner $
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Http_DummyProvider extends FACTFinder_Abstract_DataProvider
{
    protected $data;
    protected $previousFileName;
    
    protected $fileLocation;
    
    public function setFileLocation($loc)
    {
        $this->fileLocation = substr($loc, -1) == DS ? $loc : $loc.DS;
    }
    
    /**
     * we just offer this function, for compatibility with the DataProvider API
     *
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param the option key (should be a cURL constant)
     * @param the option value
     * @param boolean whether to overwrite existing options or not. optional, default = true
     * @return void
     */
    public function setCurlOption($option, $value, $overwriteExisting = true) {
        return;
    }

    /**
     * we just offer this function, for compatibility with the DataProvider API
     *
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param array of options
     * @return void
     */
    public function setCurlOptions(array $options) {
        return;
    }

    /**
     * this implementation of the data provider uses the type as request path in addition to the request context path.
     * please ensure that this is the full action name, i.e. "Search.ff"
     *
     * @param string type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     * this implementation returns the data as string
     *
     * @return string data
     */
    public function getData()
    {
        $fileName = $this->getFileName();
        if ($this->data == null || $fileName != $this->previousFileName) {
            $this->previousFileName = $fileName;
            $this->data = $this->loadFileContent($fileName);
        }
        return $this->data;
    }
    
    /**
     * this function loads the correct file and returns its contents
     *
     * @throws Exception on connection error
     * @return response data
     **/
    protected function loadFileContent($fileName)
    {
        return file_get_contents($fileName);
    }
    
    protected function getFileName()
    {
        if ($this->type == null) {
            $this->log->error("Request type missing.");
            throw new Exception('request type not set! can not do a request without knowing the type.');
        }
        
        // Remove ".ff" from the type name
        $fileName = substr_replace($this->type, '', '-3')."_";
        $config = $this->getConfig();
        $params = $this->getParams();
        unset($params["format"]);
        unset($params["user"]);
        unset($params["pw"]);
        unset($params["timestamp"]);
        unset($params["channel"]);

        ksort($params, SORT_STRING);
        $fileName .= http_build_query($params, '', '_');
        $fileName .= ".xml";
        
        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $fileName = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $fileName);
        
        $this->log->info("Trying to load ".$this->fileLocation.$fileName);
        
        return $this->fileLocation.$fileName;
    }
    
    /**
     * get channel from params or config (params override config)
     *
     * @param array parameterse
     * @FACTFinderAbstractConfiguration config
     * @return string channel
     **/
    protected function getChannel($params, $config) {
        $channel = '';
        if (!empty($params['channel'])) {
            $channel = $params['channel'];
        } else if($config->getChannel() != '') {
            $channel = $config->getChannel();
        }
        return $channel;
    }
}