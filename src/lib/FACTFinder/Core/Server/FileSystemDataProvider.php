<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * This implementation retrieves the FACT-Finder data from the file system. File
 * names are generated from request parameters. For the naming convention see
 * the getFileName() method.
 * Responses are queried sequentially and lazily and are cached as long as
 * parameters don't change.
 */
class FileSystemDataProvider extends AbstractDataProvider
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $fileLocation;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration
    ) {
        parent::__construct($loggerClass, $configuration);

        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function setConnectTimeout($id, $timeout)
    { }

    public function setTimeout($id, $timeout)
    { }

    public function setFileLocation($path)
    {
        $this->fileLocation = ($path[strlen($path) -1] == DS) ? $path : $path . DS;
    }

    public function loadResponse($id)
    {
        if (!isset($this->connectionData[$id]))
            throw new \InvalidArgumentException('Tried to get response for invalid ID $id.');


        $connectionData = $this->connectionData[$id];

        $action = $connectionData->getAction();
        if (empty($action))
        {
            $this->log->error('Request type missing.');
            $connectionData->setNullResponse();
            return;
        }
        $fileNamePrefix = $this->getFileNamePrefix($connectionData);

        $fileExtension = $this->getFileExtension($connectionData);
        $queryString = $this->getQueryString($connectionData);
        $fileName = $this->getFileName($fileNamePrefix, md5($queryString), $fileExtension);

        if (!$this->hasFileNameChanged($id, $fileName))
            return;

        $this->log->info("Trying to load file: $fileName");
        
        $fileContent = null;
        if(!$fileContent = @file_get_contents($fileName))
        {
            throw new \Exception('File "'.$fileName.' (original: ' . $fileNamePrefix . $queryString . $fileExtension . '" not found');
        }

        $response = FF::getInstance(
            'Core\Server\Response',
            $fileContent,
            200,
            0,
            ''
        );

        $connectionData->setResponse($response, $fileName);
    }

    private function getFileNamePrefix($connectionData)
    {
        $action = $connectionData->getAction();

        // Replace the .ff file extension with a dot.
        $prefix = preg_replace('/[.]ff$/i', '.', $action);
    
        return $prefix;
    }
    
    private function getFileExtension($connectionData)
    {
        $parameters = $connectionData->getParameters();

        $fileExtension = null;
        if (isset($parameters['format']))
            $fileExtension = '.' . $parameters['format'];
        else
            $fileExtension = '.raw';
        
        return $fileExtension;
    }
    
    private function getQueryString($connectionData)
    {
        $parameters = clone $connectionData->getParameters();
  
        unset($parameters['format']);
        unset($parameters['user']);
        unset($parameters['pw']);
        unset($parameters['timestamp']);
        unset($parameters['channel']);
        
        $rawParameters = &$parameters->getArray();
        // We received that array by reference, so we can sort it to sort the
        // Parameters object internally, too.
        ksort($rawParameters, SORT_STRING);
        
        $queryString = $parameters->toJavaQueryString();
        
        return $queryString;
    }

    private function getFileName($prefix, $queryString, $extension)
    {
        return $this->fileLocation . $prefix . $queryString . $extension;
    }
    
    private function hasFileNameChanged($id, $newFileName)
    {
        $connectionData = $this->connectionData[$id];

        if (FF::isInstanceOf($connectionData->getResponse(), 'Core\Server\NullResponse'))
            return true;

        return $newFileName != $connectionData->getPreviousUrl();
    }
}
