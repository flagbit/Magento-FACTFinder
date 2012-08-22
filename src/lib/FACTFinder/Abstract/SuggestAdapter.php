<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * adapter for factfinder's suggest
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SuggestAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_SuggestAdapter extends FACTFinder_Abstract_Adapter
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
     * create and return suggestions. dependend to the implementation this can be any type
     *
     * @return mixed
     */
    abstract protected function createSuggestions();
}