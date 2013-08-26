<?php
/**
 * Search adapter using the json interface.
 */
class FACTFinder_Json69_SearchAdapter extends FACTFinder_Json68_SearchAdapter
{
    protected $refKey = null;

    protected function createLink($item) {
        if ($this->refKey == null)
            $this->refKey = $this->getResultFromRawResult($this->getData())->getRefKey();

        return $this->getParamsParser()->createPageLink(
            $this->getParamsParser()->parseParamsFromResultString(trim($item['searchParams'])),
            array('sourceRefKey' => $this->refKey)
        );
    }

    protected function getResultFromRawResult($jsonData) {
        $result = parent::getResultFromRawResult($jsonData);

        if (isset($jsonData['searchResult']['refKey'])) {
            $result->setRefKey($jsonData['searchResult']['refKey']);
        }

        return $result;
    }

    /**
     * @return array of FACTFinder_Item objects
     **/
    protected function createPaging()
    {
        $paging = parent::createPaging();

        $jsonData = $this->getData();
        if (!empty($jsonData['searchResult']['paging']) && isset($jsonData['searchResult']['refKey']))
            $paging->setSourceRefKey($jsonData['searchResult']['refKey']);

        return $paging;
    }

    /**
     * @return FACTFinder_ProductsPerPageOptions
     */
    protected function createProductsPerPageOptions()
    {
        $pppOptions = array(); //default
        $jsonData = $this->getData();

        if (!empty($jsonData['searchResult']['resultsPerPageList']))
        {
            $defaultOption = -1;
            $selectedOption = -1;
            $options = array();
            foreach ($jsonData['searchResult']['resultsPerPageList'] AS $optionData) {
                $value = $optionData['value'];

                if($optionData['default'])
                    $defaultOption = $value;
                if($optionData['selected'])
                    $selectedOption = $value;

                $searchParams = $this->getParamsParser()->parseParamsFromResultString(trim($optionData['searchParams']));
                $searchParams['sourceRefKey'] = $jsonData['searchResult']['refKey'];
                $url = $this->getParamsParser()->createPageLink($searchParams);

                $options[$value] = $url;
            }
            $pppOptions = FF::getInstance('productsPerPageOptions', $options, $defaultOption, $selectedOption);
        }
        return $pppOptions;
    }
}
