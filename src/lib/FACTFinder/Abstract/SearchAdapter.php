<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * adapter for the factfinder search
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SearchAdapter.php 25935 2010-06-29 15:04:45Z rb $
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_SearchAdapter extends FACTFinder_Abstract_Adapter
{
    private $searchParams;
    private $result;
    private $asn;
    private $sorting;
    private $paging;
    private $productsPerPageOptions;
    private $breadCrumbTrail;
    private $campaigns;
    private $singleWordSearch;

	const NO_QUERY      = 'noQuery';
    const NO_RESULT     = 'noResult';
    const RESULTS_FOUND = 'resultsFound';
    const NOTHING_FOUND = 'nothingFound';

	/**
	 * @throws Exception if there is no query or no catalog-parameter set at the dataprovider
	 */
	protected function getData()
    {
		$params = $this->getDataProvider()->getParams();
		if ((!isset($params['query']) || strlen($params['query']) == 0)
			&& (!isset($params['seoPath']) || strlen($params['seoPath']) == 0)
			&& (!isset($params['catalog']) || $params['catalog'] != 'true')) {
			$this->log->error("No query was set.");
			throw new Exception(self::NO_QUERY);
		}
        return $this->getDataProvider()->getData();
    }

    /**
     * returns the search status of the article number search, which is one of this class constants:
     *   NO_RESULT it was not an article number search
     *   NOTHING_FOUND is article number search, but nothing found
     *   RESULTS_FOUND found article by article number
     * returns null, if no article number search was done
     *
     * @return string status
    **/
    abstract public function getArticleNumberSearchStatus();

    /**
     * returns true if the query matches the article number search regex, else false. also returns false, when there
     * was any error
     *
     * @return boolean isArticleNumberSearch
    **/
    abstract public function isArticleNumberSearch();

    /**
     * returns true when search timed out. even if true, nevertheless result records may exist
     *
     * @return boolean
    **/
    abstract public function isSearchTimedOut();

    /**
     * returns the search status of the search, which is one of this class constants:
     *   NO_RESULT: there seems to be any error. no search done
     *   NOTHING_FOUND: search done, but nothing found; maybe campaigns are available
     *   RESULTS_FOUND: results found
     * returns null, if no article number search was done
     *
     * @return string status
    **/
    abstract public function getStatus();

    /**
     * creates the result object
     *
     * @return FACTFinder_Result
    **/
    abstract protected function createResult();

    /**
     * creates the asn object
     *
     * @return FACTFinder_Asn
    **/
    abstract protected function createAsn();

    /**
     * creates the sorting object
     *
     * @return array of FACTFinder_Item objects
    **/
    abstract protected function createSorting();

    /**
     * creates the paging object
     *
     * @return FACTFinder_Paging object
    **/
    abstract protected function createPaging();

    /**
     * creates the paging object
     *
     * @return FACTFinder_ProductsPerPageOptions object
    **/
    abstract protected function createProductsPerPageOptions();

    /**
     * create breadcrumbtrail
     *
     * @return array of FACTFinder_BreadCrumbItem objects
     */
    abstract protected function createBreadCrumbTrail();

    /**
     * create campaigns
     *
     * @return FACTFinder_CampaignIterator
     */
    abstract protected function createCampaigns();

    /**
     * create sindle word search
     *
     * @return array of FACTFinder_SuggestQuery objects
     */
    abstract protected function createSingleWordSearch();

    /**
     * returns the search params object
     *
     * @return FACTFinder_Parameters result
    **/
    abstract protected function createSearchParams();

    /**
     * returns the search params object
     *
     * @return FACTFinder_Parameters result
    **/
    public function getSearchParams() {
        if ($this->searchParams == null) {
            $this->searchParams = $this->createSearchParams();
        }
        return $this->searchParams;
    }

    /**
     * returns the result object
     *
     * @return FACTFinder_Result result
    **/
    public function getResult() {
        if ($this->result == null) {
            $this->result = $this->createResult();
        }
        return $this->result;
    }

    /**
     * returns the asn object
     *
     * @return FACTFinder_Asn
    **/
    public function getAsn() {
        if ($this->asn == null) {
            $this->asn = $this->createAsn();
        }
        return $this->asn;
    }

    /**
     * returns the sorting
     *
     * @return array of FACTFinder_SortItem objects
    **/
    public function getSorting() {
        if ($this->sorting == null) {
            $this->sorting = $this->createSorting();
        }
        return $this->sorting;
    }

    /**
     * returns the paging
     *
     * @return FACTFinder_Paging object
    **/
    public function getPaging() {
        if ($this->paging == null) {
            $this->paging = $this->createPaging();
        }
        return $this->paging;
    }

    /**
     * return products-per-page-options
     *
     * @return FACTFinder_ProductsPerPageOptions object
     */
    public function getProductsPerPageOptions() {
        if ($this->productsPerPageOptions == null) {
            $this->productsPerPageOptions = $this->createProductsPerPageOptions();
        }
        return $this->productsPerPageOptions;
    }

    /**
     * returns the breadcrumbtrail
     *
     * @return array of FACTFinder_BreadCrumbItem objects
     */
    public function getBreadCrumbTrail() {
        if ($this->breadCrumbTrail == null) {
            $this->breadCrumbTrail = $this->createBreadCrumbTrail();
        }
        return $this->breadCrumbTrail;
    }

    /**
     * returns the campaigns
     *
     * @return FACTFinder_CampaignIterator
     */
    public function getCampaigns() {
        if ($this->campaigns == null) {
            $this->campaigns = $this->createCampaigns();
        }
        return $this->campaigns;
    }

    /**
     * if the result was not good and there are more than one queries used for it, this method will return an array of
     * FACTFinder_SuggestQuery objects, for each word a single item. by clicking at a singleWordSearch item, the result
     * will get better
     * please notice, that this feature has to be actived in the FACT-Finder search environment, so this method returns
	 * an empty array, if there are no singleWordSearch items
     *
     * @return array of FACTFinder_SuggestQuery objects
     */
    public function getSingleWordSearch() {
        if ($this->singleWordSearch == null) {
            $this->singleWordSearch = $this->createSingleWordSearch();
        }
        return $this->singleWordSearch;
    }
}
