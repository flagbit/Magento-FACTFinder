<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * represents the fact-finder product-per-page-options. by iterating over an FACTFinder_ProductsPerPageOptions
 * object, you will get FACTFinder_Item objects, where each represents one products-per-page option.
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: ProductsPerPageOptions.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 **/
class FACTFinder_ProductsPerPageOptions implements IteratorAggregate
{
    private $options;
    private $selectedOption = null;
    private $defaultOption = null;

    /**
     * @param array int to string map; the integer is product-per-page option and the string is the according url
     * @param int default option (default: first option)
     * @param int selected option (default: default option)
     */
    public function __construct(array $options, $defaultOption = -1, $selectedOption = -1) {
        $defaultOption = intval($defaultOption);
        $selectedOption = intval($selectedOption);

        $this->options = new ArrayIterator();
        foreach($options AS $option => $url) {
            $item = FF::getInstance('item', intval($option), $url, ($option == $selectedOption));
            if ($option == $selectedOption) {
                $this->selectedOption = $item;
            }
            if ($option == $defaultOption) {
                $this->defaultOption = $item;
            }
            $this->options->append($item);
        }

        if ($this->defaultOption == null && $this->options->count() > 0) {
            $this->defaultOption = $this->options[0];
        }
        if ($this->selectedOption == null && $this->defaultOption != null) {
            $this->selectedOption = $this->defaultOption;
        }
    }

    /**
     * get iterator to iterate over all products-per-page-options. each item is an object of FACTFinder_Item
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->options;
    }

    /**
     * @return FACTFinder_Item default products per page option
     */
    public function getDefaultOption()
    {
        return $this->defaultOption;
    }

    /**
     * @param FACTFinder_Item
     * @return boolean true, if the set object is the default product-per-page-option
     */
    public function isDefaultOption(FACTFinder_Item $option) {
        return $this->defaultOption->getValue() == $option->getValue();
    }

    /**
     * @return FACTFinder_Item selected products per page option
     */
    public function getSelectedOption()
    {
        return $this->selectedOption;
    }
}