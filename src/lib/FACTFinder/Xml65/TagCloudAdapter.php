<?php

/**
 * tag cloud adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_TagCloudAdapter extends FACTFinder_Abstract_TagCloudAdapter
{
    /**
     * {@inheritdoc}
     *
     * @return void
     **/
    public function init()
    {
        $this->getDataProvider()->setType('WhatsHot.ff');
        $this->getDataProvider()->setParam('do', 'getTagCloud');
    }

    /**
     * try to parse data as xml
     *
     * @throws Exception of data is no valid XML
     * @return SimpleXMLElement
     */
    protected function getData()
    {
        libxml_use_internal_errors(true);
        return new SimpleXMLElement(parent::getData()); //throws exception on error
    }

    /**
     * {@inheritdoc}
     *
     * @return array $tagCloud list of FACTFinder_TagQuery items
     **/
    protected function createTagCloud()
    {
        $tagCloud = array();
        $xmlTagCloud = $this->getData();
        if (!empty($xmlTagCloud->entry)) {
            $encodingHandler = $this->getEncodingHandler();
            $ffparams = $this->getParamsParser()->getFactfinderParams();
            foreach($xmlTagCloud->entry AS $xmlEntry) {
                $query = $encodingHandler->encodeServerContentForPage(strval($xmlEntry));
                $tagCloud[] = FF::getInstance('tagQuery',
                    $query,
                    $this->getParamsParser()->createPageLink(array('query' => $query)),
                    ($ffparams->getQuery() == $query),
                    $xmlEntry->attributes()->weight,
                    $xmlEntry->attributes()->searchCount
                );
            }
        }
        return $tagCloud;
    }
}
