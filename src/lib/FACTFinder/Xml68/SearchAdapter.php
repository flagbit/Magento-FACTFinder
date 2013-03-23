<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * search adapter using the xml interface. expects a xml formated string from the dataprovider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SearchAdapter.php 25985 2010-06-30 15:31:53Z rb $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Xml68_SearchAdapter extends FACTFinder_Xml67_SearchAdapter
{
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
                    strval($xmlGroup->attributes()->style)
                );

                //get filters of the current group
                foreach ($xmlGroup->element AS $xmlFilter) {
                    $filterLink = $this->getParamsParser()->createPageLink(
                        $this->getParamsParser()->parseParamsFromResultString(trim($xmlFilter->searchParams))
                    );

                    if ($group->isSliderStyle()) {
                        // get last (empty) parameter from the search params property
                        $params = $this->getParamsParser()->parseParamsFromResultString(trim($xmlFilter->searchParams));
                        end($params);
                        $filterLink .= '&'.key($params).'=';

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

                    $group->addFilter($filter);
                }

                $asn[] = $group;
            }
        }
        return FF::getInstance('asn', $asn);
    }
}