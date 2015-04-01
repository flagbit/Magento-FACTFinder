<?php
namespace FACTFinder\Data;

class Paging extends \ArrayIterator
{
    /**
     * @var int
     */
    private $pageCount;

    /**
     * @var Page
     */
    private $firstPage;

    /**
     * @var Page
     */
    private $lastPage;

    /**
     * @var Page
     */
    private $previousPage;

    /**
     * @var Page
     */
    private $currentPage;

    /**
     * @var Page
     */
    private $nextPage;

    /**
     * @param Page[] $pages Array of page links.
     * @param int $pageCount
     * @param Page $currentPage
     * @param Page $firstPage
     * @param Page $lastPage
     * @param Page $previousPage
     * @param Page $nextPage
     */
    public function __construct (
        array $pages,
        $pageCount,
        Page $currentPage,
        Page $firstPage = null,
        Page $lastPage = null,
        Page $previousPage = null,
        Page $nextPage = null
    ) {
        parent::__construct($pages);

        $this->pageCount = (int)$pageCount;
        $this->firstPage = $firstPage;
        $this->lastPage = $lastPage;
        $this->previousPage = $previousPage;
        $this->currentPage = $currentPage;
        $this->nextPage = $nextPage;
    }

    /**
     * @var int
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * @return Page can be null
     */
    public function getFirstPage()
    {
        return $this->firstPage;
    }

    /**
     * @return Page can be null
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * @return Page can be null
     */
    public function getPreviousPage()
    {
        return $this->previousPage;
    }

    /**
     * @return Page can be null
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return Page can be null
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }
}
