<?php

/**
 * adapter for factfinder's suggest
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
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