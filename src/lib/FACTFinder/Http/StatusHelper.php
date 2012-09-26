<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Http
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * tries to query FACT-Finder and aggregates errors and status info from HTTP code, cURL error and FACT-Finder errors
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: StatusHelper.php 2012-09-25 16:19:43Z mb $
 * @package   FACTFinder\Http
 *
 **/
class FACTFinder_Http_StatusHelper
{
    /**
     * @var FACTFinder_Abstract_Configuration
     */
    protected $config;

    /**
     * @var FACTFinder_Abstract_Logger
     */
    protected $log;

    /**
     * @var FACTFinder_Http_DataProvider
     */
    protected $dataProvider;

    /**
     * @var FACTFinder_Xml65_SearchAdapter
     */
    protected $searchAdapter;

    public function __construct(FACTFinder_Abstract_Configuration $config,
                                FACTFinder_Abstract_Logger $log = null,
                                $channel = null)
    {
        if(isset($log))
            $this->log = $log;
        else
            $this->log = FF::getSingleton('nullLogger');
        $this->log->info("Initializing Status Helper.");
        if ($config != null) $this->config = $config;

        $this->dataProvider = FACTFinder_Http_ParallelDataProvider::getDataProvider(
            null,
            $config,
            $log
        );

        $encodingHandler = FF::getInstance('encodingHandler', $config);
        $paramsParser = FF::getInstance('parametersParser', $config, $encodingHandler);

        $this->searchAdapter = FF::getInstance(
            'xml65/searchAdapter',
            $this->dataProvider,
            $paramsParser,
            $encodingHandler
        );

        if($channel !== null)
            $this->searchAdapter->setParam('channel', $channel);

        $this->searchAdapter->setParam('query', 'FACT-Finder Version');
        $this->searchAdapter->setParam('productsPerPage', '1');
        $this->searchAdapter->setParam('verbose', 'true');
    }

    public function getVersionNumber()
    {
        $resultCount = $this->searchAdapter->getResult()->getFoundRecordsCount();
        return intval(substr($resultCount, 0, 2));
    }

    public function getVersionString()
    {
        $versionNumber = ''.$this->getVersionNumber();
        return $versionNumber[0].'.'.$versionNumber[1];
    }

    public function getStatusCode()
    {
        $ffError = $this->searchAdapter->getError();

        $curlErrno = $this->dataProvider->getLastCurlErrno();

        switch($curlErrno)
        {
        case 0: // no cURL error!
            break;
        default:
            return FFE_CURL_ERROR + $this->dataProvider->getLastCurlError();
        }

        // cURL was able to connect to the server, check HTTP Code next

        $httpCode = intval($this->dataProvider->getLastHttpCode());

        switch($httpCode)
        {
        case 200: // success!
            return FFE_OK;
        case 500: // server error, check error output
            break;
        default:
            return FFE_HTTP_ERROR + $httpCode;
        }

        $stackTrace = $this->searchAdapter->getStackTrace();
        preg_match('/^(.+?):?\s/', $stackTrace, $matches);
        $ffException = $matches[1];

        switch($ffException)
        {
        case 'de.factfinder.security.exception.ChannelDoesNotExistException':
            return FFE_CHANNEL_DOES_NOT_EXIST;
        case 'de.factfinder.security.exception.WrongUserPasswordException':
            return FFE_WRONG_CREDENTIALS;
        case 'de.factfinder.security.exception.PasswordExpiredException':
            return FFE_SERVER_TIME_MISMATCH;
        case 'de.factfinder.jni.FactFinderException':
        default:
            return FFE_FACT_FINDER_ERROR;
        }
    }
}

// Possible status/error codes

define('FFE_OK',                        16180000);

define('FFE_CURL_ERROR',                16181000); // add the result of curl_errno() to this

define('FFE_HTTP_ERROR',                16182000); // add the HTTP code to this
define('FFE_WRONG_CONTEXT',             16182404); //

define('FFE_FACT_FINDER_ERROR',         16183000); // unspecified exception from FF; contact support
define('FFE_CHANNEL_DOES_NOT_EXIST',    16183001);
define('FFE_WRONG_CREDENTIALS',         16183002);
define('FFE_SERVER_TIME_MISMATCH',      16183003); // server time is not consistent with FF's server time
