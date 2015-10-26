<?php
namespace FACTFinder\Data;

class Page extends Item
{
    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @param int $pageNumber
     * @param string $label
     * @param string $url
     * @param bool $isSelected
     */
    public function __construct(
        $pageNumber,
        $label,
        $url,
        $isSelected = false
    ) {
        parent::__construct($label, $url, $isSelected);

        $this->pageNumber = (int)$pageNumber;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }
}
