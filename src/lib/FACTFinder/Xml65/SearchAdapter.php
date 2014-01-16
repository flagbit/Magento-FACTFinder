<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml65
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * search adapter using the xml interface. expects a xml formated string from the dataprovider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SearchAdapter.php 25985 2010-06-30 15:31:53Z rb $
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_SearchAdapter extends FACTFinder_Default_SearchAdapter
{
    protected $status = null;
    protected $isArticleNumberSearch;
    protected $xmlData = null;

    /**
     * init
     */
    protected function init()
    {
        $this->log->info("Initializing new search adapter.");
        $this->getDataProvider()->setParam('format', 'xml');
        $this->getDataProvider()->setType('Search.ff');
    }

    /**
     * try to parse data as xml
     *
     * @throws Exception of data is no valid XML
     * @return SimpleXMLElement
     */
    protected function getData()
    {
        if ($this->xmlData == null) {
            libxml_use_internal_errors(true);
            $data = parent::getData();
            $this->xmlData = new SimpleXMLElement($data); //throws exception on error
        }
        return $this->xmlData;
    }

    /**
     * get status of the article number search
     *
     * @return string status
     **/
    public function getArticleNumberSearchStatus()
    {
        if ($this->articleNumberSearchStatus == null) {

            $this->isArticleNumberSearch = false;
            $this->articleNumberSearchStatus = self::NO_RESULT;

            if ($this->getStatus() != self::NO_RESULT) {
                $this->loadArticleNumberSearchInformations();
            }
        }
        return $this->articleNumberSearchStatus;
    }

    /**
     * returns true if the search was an article number search
     *
     * @return boolean isArticleNumberSearch
     **/
    public function isArticleNumberSearch()
    {
        if ($this->isArticleNumberSearch === null) {

            $this->isArticleNumberSearch = false;

            if ($this->getStatus() != self::NO_RESULT) {
                $this->loadArticleNumberSearchInformations();
            }
        }
        return $this->isArticleNumberSearch;
    }

    /**
     * fetch article number search status from the xml result
     *
     * @return void
     */
    private function loadArticleNumberSearchInformations()
    {
        $xmlResult = $this->getData();
        switch ($xmlResult->articleNumberSearchStatus) {
            case 'nothingFound':
                $this->isArticleNumberSearch = true;
                $this->articleNumberSearchStatus = self::NOTHING_FOUND;
                break;
            case 'resultsFound':
                $this->isArticleNumberSearch = true;
                $this->articleNumberSearchStatus = self::RESULTS_FOUND;
                break;
            case 'noArticleNumberSearch':
            default:
                $this->isArticleNumberSearch = false;
                $this->articleNumberSearchStatus = self::NO_RESULT;
        }
    }

    /**
     * returns true if the search-process was aborted because of a timeout
     *
     * @return boolean true if search timed out
     **/
    public function isSearchTimedOut()
    {
        $xmlResult = $this->getData();
        if ($xmlResult->searchTimedOut == 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get search status
     *
     * @return string status
     **/
    public function getStatus()
    {
        if ($this->status == null) {
            $xmlResult = $this->getData();
            switch ($xmlResult->searchStatus) {
                case 'nothingFound':
                    $this->status = self::NOTHING_FOUND;
                    break;
                case 'resultsFound':
                    $this->status = self::RESULTS_FOUND;
                    break;
                default:
                    $this->status = self::NO_RESULT;
            }
        }
        return $this->status;
    }

    protected function createSearchParams()
    {
        $breadCrumbTrail = $this->getBreadCrumbTrail();
        if (sizeof($breadCrumbTrail) > 0) {
            $paramString = $breadCrumbTrail[sizeof($breadCrumbTrail) - 1]->getUrl();
            $searchParams = $this->getParamsParser()->getFactfinderParamsFromString($paramString);
        } else {
            $searchParams = $this->getParamsParser()->getFactfinderParams();
        }
        return $searchParams;
    }

    protected function createResult()
    {
        //init default values
        $result = array();
        $resultCount = 0;
        $xmlResult = $this->getData();

        //load result values from the xml element
        if (!empty($xmlResult->results)) {
            $resultCount = (int)$xmlResult->results->attributes()->count;
            $encodingHandler = $this->getEncodingHandler();

            $paging = $this->getPaging();
            $positionOffset = ($paging->getCurrentPageNumber() - 1) * $this->getProductsPerPageOptions()->getSelectedOption()->getValue();

            //load result
            $positionCounter = 1;
            foreach ($xmlResult->results->record AS $currentRecord) {
                // get current position
                $position = $positionOffset + $positionCounter;
                $positionCounter++;

                $result[] = $this->createRecord($currentRecord, $position);
            }
        }
        return FF::getInstance('result', $result, $resultCount);
    }

    protected function createRecord(SimpleXmlElement $rawRecord, $position)
    {
        // fetch record values
        $fieldValues = array();
        foreach ($rawRecord->field AS $current_field) {
            $currentFieldname = (string)$current_field->attributes()->name;
            $fieldValues[$currentFieldname] = (string)$current_field;
        }

        // get original position
        if (isset($fieldValues['__ORIG_POSITION__'])) {
            $origPosition = $fieldValues['__ORIG_POSITION__'];
            unset($fieldValues['__ORIG_POSITION__']);
        } else {
            $origPosition = $position;
        }

        $record = FF::getInstance('record',
            $rawRecord->attributes()->id,
            floatval($rawRecord->attributes()->relevancy),
            $position,
            $origPosition,
            $this->getEncodingHandler()->encodeServerContentForPage($fieldValues)
        );

        return $record;
    }

    /**
     * @return FACTFinder_Asn
     **/
    protected function createAsn()
    {
        $xmlResult = $this->getData();
        $asn = array();

        if (!empty($xmlResult->asn)) {
            $encodingHandler = $this->getEncodingHandler();
            $params = $this->getParamsParser()->getRequestParams();

            foreach ($xmlResult->asn->group AS $xmlGroup) {
                $group = $this->createGroupInstance($xmlGroup, $encodingHandler);

                //get filters of the current group
                foreach ($xmlGroup->element AS $xmlFilter) {
                    $filter = $this->createFilter($xmlFilter, $group, $encodingHandler, $params);

                    $group->addFilter($filter);
                }
                $asn[] = $group;
            }
        }
        return FF::getInstance('asn', $asn);
    }

    protected function createGroupInstance($xmlGroup, $encodingHandler)
    {
        $groupUnit = '';
        if (isset($xmlGroup->attributes()->unit)) {
            $groupUnit = strval($xmlGroup->attributes()->unit);
        }

        return FF::getInstance('asnGroup',
            array(),
            $encodingHandler->encodeServerContentForPage((string)$xmlGroup->attributes()->name),
            $encodingHandler->encodeServerContentForPage((string)$xmlGroup->attributes()->detailedLinks),
            $encodingHandler->encodeServerContentForPage($groupUnit),
            $this->getGroupStyle($xmlGroup)
        );
    }

    protected function getGroupStyle($xmlGroup)
    {
        $style = strval($xmlGroup->attributes()->style);
        return $style == 'SLIDER' ? $style : 'DEFAULT';
    }

    protected function createFilter($xmlFilter, $group, $encodingHandler, $params)
    {
        $filterLink = $this->createLink($xmlFilter);

        if ($group->isSliderStyle()) {
            $filterLink .= '&filter' . strval($xmlFilter->attributes()->field) . '=';

            $filter = FF::getInstance('asnSliderFilter',
                $filterLink,
                strval($xmlFilter->attributes()->absoluteMin),
                strval($xmlFilter->attributes()->absoluteMax),
                strval($xmlFilter->attributes()->selectedMin),
                strval($xmlFilter->attributes()->selectedMax),
                isset($xmlFilter->attributes()->field) ? strval($xmlFilter->attributes()->field) : ''
            );
        } else {
            $filter = FF::getInstance('asnFilterItem',
                $encodingHandler->encodeServerContentForPage(trim($xmlFilter->attributes()->name)),
                $filterLink,
                strval($xmlFilter->attributes()->selected) == 'true',
                strval($xmlFilter->attributes()->count),
                strval($xmlFilter->attributes()->clusterLevel),
                strval($xmlFilter->attributes()->previewImage),
                isset($xmlFilter->attributes()->field) ? strval($xmlFilter->attributes()->field) : ''
            );
        }

        return $filter;
    }

    protected function createLink($item) {
        return $this->getParamsParser()->createPageLink(
            $this->getParamsParser()->parseParamsFromResultString(trim($item->searchParams))
        );
    }

    /**
     * @return array of FACTFinder_SortItem objects
     **/
    protected function createSorting()
    {
        $sorting = array();
        $xmlResult = $this->getData();

        if (!empty($xmlResult->sorting)) {
            $encodingHandler = $this->getEncodingHandler();

            foreach ($xmlResult->sorting->sort AS $xmlSortItem) {
                $sortLink = $this->createLink($xmlSortItem);
                $sortItem = FF::getInstance('item',
                    $encodingHandler->encodeServerContentForPage(trim($xmlSortItem->attributes()->description)),
                    $sortLink,
                    strval($xmlSortItem->attributes()->selected) == 'true'
                );
                $sorting[] = $sortItem;
            }
        }
        return $sorting;
    }

    /**
     * @return array of FACTFinder_Item objects
     **/
    protected function createPaging()
    {
        $paging = null;
        $xmlResult = $this->getData();

        if (!empty($xmlResult->paging)) {
            $paging = FF::getInstance('paging',
                intval(trim($xmlResult->paging->attributes()->currentPage)),
                intval(trim($xmlResult->paging->attributes()->pageCount)),
                $this->getParamsParser()
            );
        } else {
            $paging = FF::getInstance('paging', 1, 1, $this->getParamsParser());
        }
        return $paging;
    }

    /**
     * @return FACTFinder_ProductsPerPageOptions
     */
    protected function createProductsPerPageOptions()
    {
        $pppOptions = array(); //default
        $xmlResult = $this->getData();

        if (!empty($xmlResult->productsPerPageOptions)) {
            $defaultOption = intval(trim($xmlResult->productsPerPageOptions->attributes()->default));
            $selectedOption = intval(trim($xmlResult->productsPerPageOptions->attributes()->selected));

            $options = array();
            foreach ($xmlResult->productsPerPageOptions->option AS $option) {
                $value = intval(trim($option->attributes()->value));
                $url = $this->getParamsParser()->createPageLink(
                    $this->getParamsParser()->parseParamsFromResultString(trim($option->searchParams))
                );
                $options[$value] = $url;
            }
            $pppOptions = FF::getInstance('productsPerPageOptions', $options, $defaultOption, $selectedOption);
        }
        return $pppOptions;
    }

    /**
     * @return array of FACTFinder_BreadCrumbItem objects
     */
    protected function createBreadCrumbTrail()
    {
        $breadCrumbTrail = array();
        $xmlResult = $this->getData();

        if (!empty($xmlResult->breadCrumbTrail)) {
            $encodingHandler = $this->getEncodingHandler();

            $breadCrumbCount = count($xmlResult->breadCrumbTrail->item);
            $i = 1;
            foreach ($xmlResult->breadCrumbTrail->item AS $item) {
                $link = $this->createLink($item);

                $fieldName = '';
                $fieldUnit = '';
                $breadCrumbType = $encodingHandler->encodeServerContentForPage(strval($item->attributes()->type));
                if ($breadCrumbType == 'filter') {
                    $fieldName = $encodingHandler->encodeServerContentForPage(strval($item->attributes()->fieldName));
                    $fieldUnit = $encodingHandler->encodeServerContentForPage(strval($item->attributes()->fieldUnit));
                }

                $breadCrumb = FF::getInstance('breadCrumbItem',
                    $encodingHandler->encodeServerContentForPage(trim($item->attributes()->value)),
                    $link,
                    ($i == $breadCrumbCount),
                    $breadCrumbType,
                    $fieldName,
                    $fieldUnit
                );

                $breadCrumbTrail[] = $breadCrumb;
                $i++;
            }
        }
        return $breadCrumbTrail;
    }


    /**
     * @return array of FACTFinder_Campaign objects
     */
    protected function createCampaigns()
    {
        $campaigns = array();
        $xmlResult = $this->getData();

        if (!empty($xmlResult->campaigns)) {
            $encodingHandler = $this->getEncodingHandler();

            foreach ($xmlResult->campaigns->campaign AS $xmlCampaign) {
                //get redirect
                $redirectUrl = '';
                if (!empty($xmlCampaign->target->destination)) {
                    $redirectUrl = $encodingHandler->encodeServerUrlForPageUrl(strval($xmlCampaign->target->destination));
                }

                $campaign = FF::getInstance('campaign',
                    $encodingHandler->encodeServerContentForPage(strval($xmlCampaign->attributes()->name)),
                    $encodingHandler->encodeServerContentForPage(strval($xmlCampaign->attributes()->category)),
                    $redirectUrl
                );

                //get feedback
                if (!empty($xmlCampaign->feedback)) {
                    $feedback = array();
                    foreach ($xmlCampaign->feedback->text as $text) {
                        $nr = intval(trim($text->attributes()->nr));
                        $feedback[$nr] = $encodingHandler->encodeServerContentForPage((string)$text);
                    }
                    $campaign->addFeedback($feedback);
                }

                //get pushed products
                if (!empty($xmlCampaign->pushedProducts)) {
                    $pushedProducts = array();
                    foreach ($xmlCampaign->pushedProducts->product AS $xmlProduct) {
                        $product = FF::getInstance('record', $xmlProduct->attributes()->id, 100);

                        // fetch product values
                        $fieldValues = array();
                        foreach ($xmlProduct->field AS $current_field) {
                            $currentFieldname = (string)$current_field->attributes()->name;
                            $fieldValues[$currentFieldname] = (string)$current_field;
                        }
                        $product->setValues($encodingHandler->encodeServerContentForPage($fieldValues));
                        $pushedProducts[] = $product;
                    }
                    $campaign->addPushedProducts($pushedProducts);
                }

                $campaigns[] = $campaign;
            }
        }
        $campaignIterator = FF::getInstance('campaignIterator', $campaigns);
        return $campaignIterator;
    }

    /**
     * @return array of FACTFinder_SuggestQuery objects
     */
    protected function createSingleWordSearch()
    {
        $xmlResult = $this->getData();
        $singleWordSearch = array();
        if (isset($xmlResult->singleWordSearch)) {
            $encodingHandler = $this->getEncodingHandler();
            foreach ($xmlResult->singleWordSearch->item AS $item) {
                $query = $encodingHandler->encodeServerContentForPage(strval($item->attributes()->word));
                $singleWordSearch[] = FF::getInstance('suggestQuery',
                    $query,
                    $this->getParamsParser()->createPageLink(array('query' => $query)),
                    intval(trim($item->attributes()->count))
                );
            }
        }
        return $singleWordSearch;
    }

    /**
     * get error if there is one
     *
     * @return string if error exists, else null
     */
    public function getError()
    {
        $error = null;
        $xmlResult = $this->getData();
        if (!empty($xmlResult->error)) {
            $error = trim(strval($xmlResult->error));
        }
        return $error;
    }

    /**
     * get stacktrace if there is one
     *
     * @return string if stacktrace exists, else null
     */
    public function getStackTrace()
    {
        $stackTrace = null;
        $xmlResult = $this->getData();
        if (!empty($xmlResult->stacktrace)) {
            $stackTrace = trim(strval($xmlResult->stacktrace));
        }
        return $stackTrace;
    }
}