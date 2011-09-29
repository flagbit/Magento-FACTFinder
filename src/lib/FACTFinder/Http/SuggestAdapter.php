<?php

/**
 * this suggest adapter requests the raw suggest data
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_SuggestAdapter extends FACTFinder_Abstract_SuggestAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->getDataProvider()->setType('Suggest.ff');
        $this->getDataProvider()->setCurlOptions(array(
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 2
        ));
    }

    /**
     * {@inheritdoc}
     * this implementation returns raw suggestions strings
     *
     * @return string raw data
     */
    protected function createSuggestions()
    {
        return $this->getData();
    }
}