<?php

/**
 * search adapter using the xml interface. expects a xml formatted string from the data-provider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SearchAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml64
 */
class FACTFinder_Xml64_SearchAdapter extends FACTFinder_Xml65_SearchAdapter
{
    /**
     * {@inheritdoc}
     * the parameter for the xml result changed in FACT-Finder 6.5, so here it is set different
     */
    protected function init()
    {
        $this->getDataProvider()->setParam('xml', 'true');
        $this->getDataProvider()->setType('Search.ff');
    }

    /**
     * {@inheritdoc}
     * until versio 6.4 of FACT-Finder there are no slider elements
     *
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
                $groupName = $encodingHandler->encodeServerContentForPage((string)$xmlGroup->attributes()->name);
                $groupUnit = '';
                if (isset($xmlGroup->attributes()->unit)) {
                    $groupUnit = strval($xmlGroup->attributes()->unit);
                }

                $group = FF::getInstance('asnGroup',
                    array(),
                    $encodingHandler->encodeServerContentForPage((string)$xmlGroup->attributes()->name),
                    $encodingHandler->encodeServerContentForPage((string)$xmlGroup->attributes()->detailedLinks),
                    $groupUnit,
                    false
                );

                //get filters of the current group
                foreach ($xmlGroup->element AS $xmlFilter) {
                    $filterLink = $this->getParamsParser()->createPageLink(
                        $this->getParamsParser()->parseParamsFromResultString(trim($xmlFilter->searchParams))
                    );
                    $filter = FF::getInstance('asnFilterItem',
                        $encodingHandler->encodeServerContentForPage(trim($xmlFilter->attributes()->name)),
                        $filterLink,
                        strval($xmlFilter->attributes()->selected) == 'true',
                        strval($xmlFilter->attributes()->count),
                        strval($xmlFilter->attributes()->clusterLevel),
                        strval($xmlFilter->attributes()->previewImage)
                    );

                    $group->addFilter($filter);
                }

                $asn[] = $group;
            }
        }
        return FF::getInstance('asn', $asn);
    }

    /**
     * {@inheritdoc}
     * until version 6.4 of FACT-Finder, the products per page options are not delivered, so this method creates an
     * artificial products per page options array, but uses the current set productsPerPage value from the result
     *
     * @return FACTFinder_ProductsPerPageOptions
     */
    protected function createProductsPerPageOptions()
    {
        $pppOptions = array(); //default
        $xmlResult = $this->getData();

        if (!empty($xmlResult->paging)) {
            $params = $this->getParamsParser()->getRequestParams();

            $selectedOption = intval($xmlResult->paging->attributes()->productsPerPage);
            $defaultOption = 12;
            $options = array();

            if ($selectedOption < $defaultOption) {
                $defaultOption = $selectedOption;
            }
            $options[$defaultOption] = $this->getProductsPerPageLink($defaultOption);
            if ($selectedOption != $defaultOption) {
                $options[$selectedOption] = $this->getProductsPerPageLink($selectedOption);
            }

            $pppOptions = FF::getInstance('productsPerPageOptions', $options, $defaultOption, $selectedOption);
        }
        return $pppOptions;
    }

    protected function getProductsPerPageLink($pppOption) {
        $params = $this->getParamsParser()->getRequestParams();
        return $this->getParamsParser()->createPageLink($params, array('productsPerPage' => $pppOption));
    }
}