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
        $this->_configArray['language'] = 'en';
        FF::getSingleton('configuration', $this->_configArray);

        $params = array();

        $params['query'] = 'FACT-Finder Version';
        $params['productsPerPage'] = '1';
        $params['verbose'] = 'true';

        $this->_secondaryChannels = FF::getSingleton('configuration')->getSecondaryChannels();

        $this->_getFacade()->configureSearchAdapter($params);
        foreach($this->_secondaryChannels AS $channel)
            $this->_getFacade()->configureSearchAdapter($params, $channel);

    }

    public function checkStatus()
    {
        $statusOkay = true;
        $this->_errorMessage = array();
        $primaryStatus = $this->_getFacade()->getSearchStatus();
        if($primaryStatus == "noResult")
        {
            $this->_errorMessages[] = $this->_retrieveErrorMessage();
            $statusOkay = false;
        }
        foreach($this->_secondaryChannels AS $channel)
        {
            $secondaryStatus = $this->_getFacade()->getSearchStatus($channel);
            if($secondaryStatus == "noResult")
            {
                $this->_errorMessages[] = $this->_retrieveErrorMessage($channel);
                $statusOkay = $false;
            }
            $statusOkay = $statusOkay && $this->_getFacade()->getSearchStatus($channel) == 'resultsFound';
        }

        return $statusOkay;
    }

    protected function _retrieveErrorMessage($channel = null)
    {
        $helper = $this->_getHelper();
        if($channel === null)
            $errorMessage = $helper->__('Error in Primary Channel') . ': ';
        else
            $errorMessage = $helper->__('Error in Channel').' "'.$channel.'": ';
        $error = $this->_getFacade()->getSearchError($channel);
        $stackTrace = $this->_getFacade()->getSearchStackTrace($channel);
        $matches = array();
        preg_match('/^(.+?):?\s/', $stackTrace, $matches);
        $errorMessage .= $this->_getMessageFromErrorAndException($error, $matches[1]);
        return $errorMessage;
    }

    protected function _getHelper()
    {
        if($this->_helper === null)
            $this->_helper = Mage::helper('factfinder');
        return $this->_helper;
    }

    public function getErrorMessage()
    {
        return $this->_errorMessages;
    }

    protected function _getMessageFromErrorAndException($error, $exceptionName)
    {
        /*if(strpos($error, "Ihnen fehlen die nötigen Rechte") !== false)
        {
            return $this->_getHelper()->__('The given user does not have permission to access this channel');
        }*/
        switch($exceptionName)
        {
        case 'de.factfinder.security.exception.ChannelDoesNotExistException':
            return $this->_getHelper()->__('Channel does not exist.');
        case 'de.factfinder.security.exception.WrongUserPasswordException':
            return $this->_getHelper()->__('Either the login details are wrong or this user has no permission to access this channel.');
        case 'de.factfinder.security.exception.PasswordExpiredException':
            return $this->_getHelper()->__('Your server time does not conform to FACT-Finder\s. Please make sure yours is correct.');
        case 'de.factfinder.jni.FactFinderException':
        default:
            return $this->_getHelper()->__('There is a problem with FACT-Finder. Please contact the FACT-Finder support.');
        }
    }
}
