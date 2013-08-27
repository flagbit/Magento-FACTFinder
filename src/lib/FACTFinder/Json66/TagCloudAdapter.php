<?php
/**
 * tag cloud adapter using the json interface
 */
class FACTFinder_Json66_TagCloudAdapter extends FACTFinder_Default_TagCloudAdapter
{
    /**
     * @return void
     **/
    public function init()
    {
		$this->log->info("Initializing new tag cloud adapter.");
        $this->getDataProvider()->setType('WhatsHot.ff');
        $this->getDataProvider()->setParam('do', 'getTagCloud');
		$this->getDataProvider()->setParam('format', 'json');
    }

    /**
     * try to parse data as json
     *
     * @throws Exception of data is no valid JSON
     * @return stdClass
     */
    protected function getData()
    {
        $jsonData = json_decode(parent::getData());
        if ($jsonData === null)
            throw new InvalidArgumentException("json_decode() raised error ".json_last_error());
        return $jsonData;
    }

    /**
     * @return array $tagCloud list of FACTFinder_TagQuery items
     **/
    protected function createTagCloud()
    {
        $tagCloud = array();
        $jsonTagCloud = $this->getData();
        if (!empty($jsonTagCloud)) {
            $encodingHandler = $this->getEncodingHandler();
            $ffparams = $this->getParamsParser()->getFactfinderParams();
            foreach($jsonTagCloud AS $tagQueryData) {
                $query = $encodingHandler->encodeServerContentForPage(strval($tagQueryData->query));
                $tagCloud[] = FF::getInstance('tagQuery',
                    $query,
                    $this->getParamsParser()->createPageLink(array('query' => $query)),
                    ($ffparams->getQuery() == $query),
                    $tagQueryData->weight,
                    $tagQueryData->searchCount
                );
            }
        }
        return $tagCloud;
    }
}
