<?php
namespace FACTFinder\Data;


/**
 * Represents any kind of selectable item on the page. This includes links and
 * some form elements. This class really just groups a label, a URL and a
 * boolean indicating whether the item is already selected. Subclasses may add
 * further fields or logic to implement different kinds of items.
 */
class Item
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $selected;

    /**
     * @param string $label
     * @param string $url
     * @param bool $isSelected
     */
    public function __construct(
        $label,
        $url,
        $isSelected = false
    ) {
        $this->label = (string)$label;
        $this->url = (string)$url;
        $this->selected = (bool)$isSelected;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }
}
