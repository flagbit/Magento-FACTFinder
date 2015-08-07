<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

use FACTFinder\Loader as FF;

// Possible status/error codes

define('FFE_OK', 16180000);

define('FFE_CURL_ERROR', 16181000); // add the result of curl_errno() to this

define('FFE_HTTP_ERROR', 16182000); // add the HTTP code to this
define('FFE_WRONG_CONTEXT', 16182404); //

define('FFE_FACT_FINDER_ERROR', 16183000); // unspecified exception from FF; contact support
define('FFE_CHANNEL_DOES_NOT_EXIST', 16183001);
define('FFE_WRONG_CREDENTIALS', 16183002);
define('FFE_SERVER_TIME_MISMATCH', 16183003); // server time is not consistent with FF's server time

/**
 * Status handler
 *
 * Checks whether the configuration is working
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Handler_Status extends FACTFinder_Core_Model_Handler_Search
{

    /**
     * Array of error messages
     *
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * Helper instance
     *
     * @var FACTFinder_Core_Helper_Data
     */
    protected $_helper;

    /**
     * Secondary channels array
     *
     * @var array
     */
    protected $_secondaryChannels;

    /**
     * Mapping of error codes and messages
     *
     * @var array
     */
    protected $_errorMapping = array(
        FFE_WRONG_CONTEXT          => 'FACT-Finder not found on server. Please check your context setting.',
        FFE_CHANNEL_DOES_NOT_EXIST => 'Channel does not exist or the specified user does not have sufficient rights.',
        FFE_WRONG_CREDENTIALS
            => 'Could not log into FACT-Finder with the given settings. Please check username, password, prefix and postfix.',
        FFE_SERVER_TIME_MISMATCH
            => 'Your server\'s clock does not agree with FACT-Finder\'s. Please make sure your clock is set correctly.',
        FFE_CURL_ERROR            => 'Could not establish HTTP connection. cURL Error Code: %s',
        FFE_HTTP_ERROR            => 'Could not contact FACT-Finder. HTTP Status Code: %s',
        FFE_FACT_FINDER_ERROR     => 'There is a problem with FACT-Finder. Please contact FACT-Finder Support.',
    );


    /**
     * prepares all request parameters for the primary search adapter
     *
     * @return array
     */
    protected function _collectParams()
    {
        $this->_secondaryChannels = $this->_getFacade()->getConfiguration()->getSecondaryChannels();

        $params = array();
        $params['channel'] = $this->_getFacade()->getConfiguration()->getChannel();
        $params['query'] = 'FACT-Finder Version';
        $params['productsPerPage'] = '1';
        $params['verbose'] = 'true';

        return $params;
    }


    /**
     * Check status of fact-finder connection
     *
     * @return bool
     */
    public function checkStatus()
    {
        $statusOkay = true;
        $this->_errorMessage = array();

        $primaryStatus = $this->getStatusCode();
        if ($primaryStatus !== FFE_OK) {
            $this->_errorMessages[] = $this->_retrieveErrorMessage($primaryStatus);
            $statusOkay = false;
        }

        foreach ($this->_secondaryChannels as $channel) {
            $secondaryStatus = $this->_getFacade()->getFactFinderStatus($channel);
            if ($secondaryStatus !== FFE_OK) {
                $this->_errorMessages[] = $this->_retrieveErrorMessage($secondaryStatus, $channel);
                $statusOkay = false;
            }
        }

        return $statusOkay;
    }


    /**
     * Retrieve full error message
     *
     * @param int    $statusCode
     * @param string $channel
     *
     * @return string
     */
    protected function _retrieveErrorMessage($statusCode, $channel = null)
    {
        $helper = $this->_getHelper();
        if ($channel === null) {
            $errorMessage = $helper->__('Error in Primary Channel') . ': ';
        } else {
            $errorMessage = $helper->__('Error in Channel') . ' "' . $channel . '": ';
        }

        if (isset ($this->_errorMapping[$statusCode])) {
            $errorMessage .= $helper->__($this->_errorMapping[$statusCode]);
        }

        $codeType = (int) floor($statusCode / 1000) * 1000;

        if (isset ($this->_errorMapping[$codeType])) {
            $errorMessage .= $helper->__($this->_errorMapping[$statusCode], $statusCode - $codeType);
        } else {
            $errorMessage .= $helper->__('An unknown error has occurred. Please contact FACT-Finder Support.');
        }

        return $errorMessage;
    }


    /**
     * Get helper instance
     *
     * @return FACTFinder_Core_Helper_Data
     */
    protected function _getHelper()
    {
        if ($this->_helper === null) {
            $this->_helper = Mage::helper('factfinder');
        }

        return $this->_helper;
    }


    /**
     * Get error messages
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }


    /**
     * Get Fact-finder version number from adapter
     *
     * @return int
     */
    public function getVersionNumber()
    {
        $resultCount = $this->_getFacade()->getSearchAdapter()->getResult()->getFoundRecordsCount();

        return intval(substr($resultCount, 0, 2));
    }


    /**
     * Get full version string
     *
     * @return string
     */
    public function getVersionString()
    {
        $versionNumber = '' . $this->getVersionNumber();

        return $versionNumber[0] . '.' . $versionNumber[1];
    }


    /**
     * Get status code from ff adapter
     *
     * @return int
     */
    public function getStatusCode()
    {
        /* start @todo solve this problem without reflection */
        $resultObj = $this->_getFacade()->getSearchAdapter();

        $reflectionClass = new ReflectionClass('FACTFinder\Adapter\Search');
        $property = $reflectionClass->getProperty('request');
        $property->setAccessible(true);

        $request = $property->getValue($resultObj);
        $response = $request->getResponse();

        $reflectionClass = new ReflectionClass('FACTFinder\Core\Server\Response');
        $httpCode = $reflectionClass->getProperty('httpCode');
        $httpCode->setAccessible(true);
        $connectionError = $reflectionClass->getProperty('connectionError');
        $connectionError->setAccessible(true);
        $connectionErrorCode = $reflectionClass->getProperty('connectionErrorCode');
        $connectionErrorCode->setAccessible(true);
        /* end  */

        $curlErrno = $connectionErrorCode->getValue($response);

        switch ($curlErrno) {
            case 0: // no cURL error!
                break;
            default:
                return FFE_CURL_ERROR + $connectionError->getValue($response);
        }

        // cURL was able to connect to the server, check HTTP Code next

        $httpCode = intval($httpCode->getValue($response));

        switch ($httpCode) {
            case 200: // success!
                return FFE_OK;
            case 500: // server error, check error output
                break;
            default:
                return FFE_HTTP_ERROR + $httpCode;
        }

        $stackTrace = $this->_getFacade()->getSearchAdapter()->getStackTrace();
        Mage::helper('factfinder/debug')->trace($stackTrace);
        preg_match('/^(.+?):?\s/', $stackTrace, $matches);
        $ffException = $matches[1];

        switch ($ffException) {
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
