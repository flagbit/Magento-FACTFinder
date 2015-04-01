<?php
namespace FACTFinder\Data;

/**
 * This represents all relevant parameters that affect the main search. As it
 * stands, this class is only intended for read purposes outside of the library.
 * This cannot be used, for instance, to change of the parameters. It is merely
 * a convenient way to find out the current query or page etc.
 */
class SearchParameters
{
    /**
     * @var string
     */
    private $query;
    private $channel;

    /**
     * @var int
     */
    private $productsPerPage;
    private $currentPage;
    private $followSearch;

    /**
     * @var string[]
     */
    private $filters;
    private $sortings;

    /**
     * @var bool
     */
    private $navigationEnabled;

    /**
     * @param Parameters $parameters The server parameters used for the current
     *                               request.
     */
    public function __construct(
        \FACTFinder\Util\Parameters $parameters
    ) {
        $this->query = isset($parameters['query']) ? $parameters['query'] : '';

        // Properly prepared server parameters will always have a channel set
        $this->channel = $parameters['channel'];

        $this->productsPerPage = isset($parameters['productsPerPage'])
                                 ? $parameters['productsPerPage']
                                 : -1;
        $this->currentPage     = isset($parameters['page'])
                                 ? $parameters['page']
                                 : 1;
        $this->followSearch    = isset($parameters['followSearch'])
                                 ? $parameters['followSearch']
                                 : 10000;

        $this->navigationEnabled = (isset($parameters['catalog']) && $parameters['catalog'] == 'true')
                                   || (isset($parameters['navigation']) && $parameters['navigation'] == 'true');

        $this->filters  = array();
        $this->sortings = array();

        // TODO: Let Parameters implement the necessary interface so that it
        //       can be used directly in foreach.
        foreach ($parameters->getArray() as $key => $value)
        {
            if (strpos($key, 'filter') === 0)
                $this->filters[substr($key, strlen('filter'))] = $value;
            else if (strpos($key, 'sort') === 0
                     && ($value == 'asc' || $value == 'desc'))
                $this->sortings[substr($key, strlen('sort'))] = $value;
        }
    }



    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getProductsPerPage()
    {
        return $this->productsPerPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return string[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return string[]
     */
    public function getSortings()
    {
        return $this->sortings;
    }

    /**
     * @return bool
     */
    public function isNavigationEnabled()
    {
        return $this->navigationEnabled;
    }

    /**
     * @return int
     */
    public function getFollowSearch()
    {
        return $this->followSearch;
    }
}
