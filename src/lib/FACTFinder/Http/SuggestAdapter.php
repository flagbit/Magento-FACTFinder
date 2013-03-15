<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Http
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this suggest adapter requests the raw suggest data
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SuggestAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_SuggestAdapter extends FACTFinder_Default_SuggestAdapter
{
    /**
     * init
     */
    protected function init()
    {
		$this->log->info("Initializing new suggest adapter.");
        $this->getDataProvider()->setType('Suggest.ff');
        $this->getDataProvider()->setCurlOptions(array(
            CURLOPT_CONNECTTIMEOUT => $this->getDataProvider()->getConfig()->getSuggestConnectTimeout(),
            CURLOPT_TIMEOUT => $this->getDataProvider()->getConfig()->getSuggestTimeout()
        ));
    }

    /**
     * this implementation returns raw suggestions strings
     *
     * @return string raw data
     */
    protected function createSuggestions()
    {
        return $this->getData();
    }
}