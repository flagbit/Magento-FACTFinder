<?php
/**
 * import adapter using the json interface
 **/
class FACTFinder_Json66_ImportAdapter extends FACTFinder_Default_ImportAdapter
{
    /**
     * @return void
     **/
    public function init()
    {
		$this->log->info("Initializing new import adapter.");
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
	 * @param  bool   $download        import files will also be updated if true
	 * @param  string $type   		   determines which import will be triggered. can be 'data', 'suggest' or 'recommendation'
     * @return object $report          import report in xml format
     */
    protected function triggerImport($download, $type = 'data')
    {
        $this->getDataProvider()->setCurlOptions(array(
            CURLOPT_CONNECTTIMEOUT => $this->getDataProvider()->getConfig()->getImportConnectTimeout(),
            CURLOPT_TIMEOUT => $this->getDataProvider()->getConfig()->getImportTimeout()
        ));
        
        $this->getDataProvider()->setParam('download', $download ? 'true' : 'false');
		switch($type)
		{
		case 'suggest':
			$this->getDataProvider()->setType('Import.ff');
			$this->getDataProvider()->setParam('type', 'suggest');
			break;
		case 'recommendation':
			$this->getDataProvider()->setType('Recommender.ff');
			$this->getDataProvider()->setParam('do', 'importData');
			break;
		case 'data':
		default:
			$this->getDataProvider()->setType('Import.ff');
			break;
		}
		
        $report = $this->getData();
		
		// clean up for next import
		switch($type)
		{
		case 'suggest':
			$this->getDataProvider()->unsetParam('type');
			break;
		case 'recommendation':
			$this->getDataProvider()->unsetParam('do');
			break;
		}
		
        return $report;
    }
}
