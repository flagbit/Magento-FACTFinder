<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml65
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * tag cloud adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: TagCloudAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_TagCloudAdapter extends FACTFinder_Abstract_TagCloudAdapter
{
    /**
     * @return void
     **/
    public function init()
    {
		$this->log->info("Initializing new tag cloud adapter.");
        $this->getDataProvider()->setType('WhatsHot.ff');
        $this->getDataProvider()->setParam('do', 'getTagCloud');
		$this->getDataProvider()->setParam('format', 'xml');
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
     * @return array $tagCloud list of FACTFinder_TagQuery items
     **/
    protected function createTagCloud()
    {
        $tagCloud = array();
        $xmlTagCloud = $this->getData();
        if (!empty($xmlTagCloud)) {
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
