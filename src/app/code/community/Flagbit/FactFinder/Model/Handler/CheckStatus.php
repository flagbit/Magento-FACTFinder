<?php
/**
 * Checks whether the configuration is working
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: CheckStatus.php 17.09.12 15:00 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_CheckStatus
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_configArray;

    protected $_errorMessages = array();

    protected $_helper;

    protected $_secondaryChannels;

    public function __construct($configArray = null)
    {
        $this->_configArray = $configArray;
        parent::__construct();
    }

    protected function configureFacade()
    {
        FF::getSingleton('configuration', $this->_configArray);

        $this->_secondaryChannels = FF::getSingleton('configuration')->getSecondaryChannels();

        $this->_getFacade()->configureStatusHelper();
        foreach($this->_secondaryChannels AS $channel)
            $this->_getFacade()->configureStatusHelper($channel);

    }

    public function checkStatus($configuredVersion)
    {
        ob_start();
        $statusOkay = true;
        $this->_errorMessage = array();

        $primaryStatus = $this->_getFacade()->getFactFinderStatus();
        if($primaryStatus !== FFE_OK)
        {
            $this->_errorMessages[] = $this->_retrieveErrorMessage($primaryStatus);
            $statusOkay = false;
        }
        foreach($this->_secondaryChannels AS $channel)
        {
            $secondaryStatus = $this->_getFacade()->getFactFinderStatus($channel);
            if($secondaryStatus !== FFE_OK)
            {
                $this->_errorMessages[] = $this->_retrieveErrorMessage($secondaryStatus, $channel);
                $statusOkay = false;
            }
        }

        $actualVersion = $this->_getFacade()->getActualFactFinderVersion();
        $actualVersionString = $this->_getFacade()->getActualFactFinderVersionString();

        if($statusOkay && $actualVersion < $configuredVersion)
        {
            $this->_errorMessages[] = $this->_getHelper()->__(
                'The configured FACT-Finder version is higher than the actual version of your FACT-Finder. '.
                'Consider upgrading your FACT-Finder, or reduce the configured version to '
            ).$actualVersionString;
            $statusOkay = false;
        }

        $this->_errorMessages[] = ob_get_clean();

        return $statusOkay;
    }

    protected function _retrieveErrorMessage($statusCode, $channel = null)
    {
        $helper = $this->_getHelper();
        if($channel === null)
            $errorMessage = $helper->__('Error in Primary Channel') . ': ';
        else
            $errorMessage = $helper->__('Error in Channel').' "'.$channel.'": ';

        switch($statusCode)
        {
        case FFE_WRONG_CONTEXT:
            $errorMessage .= $helper->__('FACT-Finder not found on server. Please check your context setting.');
            return $errorMessage;
        case FFE_CHANNEL_DOES_NOT_EXIST:
            $errorMessage .= $helper->__('Channel does not exist.');
            return $errorMessage;
        case FFE_WRONG_CREDENTIALS:
            $errorMessage .= $helper->__('Could not log into FACT-Finder with the given settings. Please check username, password, prefix and postfix.');
            return $errorMessage;
        case FFE_SERVER_TIME_MISMATCH:
            $errorMessage .= $helper->__('Your server time does not conform to FACT-Finder\'s. Please make sure yours is set correctly.');
            return $errorMessage;
        }

        $codeType = floor($statusCode / 1000) * 1000;

        switch($codeType)
        {
        case FFE_CURL_ERROR:
            $errorMessage .= $helper->__('Could not establish HTTP connection.');
            $errorMessage .= ' cURL Error Code: '.($statusCode - $codeType);
            break;
        case FFE_HTTP_ERROR:
            $errorMessage .= $helper->__('Could not reach FACT-Finder.');
            $errorMessage .= ' HTTP Status Code: '.($statusCode - $codeType);
            break;
        case FFE_FACT_FINDER_ERROR:
            $errorMessage .= $helper->__('There is a problem with FACT-Finder. Please contact the FACT-Finder support.');
            break;
        default:
            $errorMessage .= $helper->__('There has been an unknown error. Please contact the FACT-Finder support.');
            break;
        }

        return $errorMessage;
    }

    protected function _getHelper()
    {
        if($this->_helper === null)
            $this->_helper = Mage::helper('factfinder');
        return $this->_helper;
    }

    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }
}
