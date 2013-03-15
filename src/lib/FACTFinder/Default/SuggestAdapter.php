<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * suggest adapter using the xml interface. expects a xml formated string from the dataprovider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SuggestAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_SuggestAdapter extends FACTFinder_Abstract_Adapter
{
    private $suggestions;

    /**
     * get suggestions created by createSuggesions()
     *
     * @return mixed
     */
    public function getSuggestions()
    {
        if ($this->suggestions == null) {
            $this->suggestions = $this->createSuggestions();
        }
        return $this->suggestions;
    }

    /**
     * create and return suggestions. dependent to the implementation this can be any type
     *
     * @return mixed
     */
    protected function createSuggestions()
    {
        $this->log->debug("Suggest not available before FF 6.0!");
        return array();
    }
}