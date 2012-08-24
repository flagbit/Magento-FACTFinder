<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml65
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * import adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: TagCloudAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_ImportAdapter extends FACTFinder_Abstract_ImportAdapter
{
    /**
     * @return void
     **/
    public function init()
    {
		$this->log->info("Initializing new import adapter.");
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
	 * @param  bool   $download        import files will also be updated if true
	 * @param  string $type   		   determines which import will be triggered. can be 'data', 'suggest' or 'recommendation'
     * @return object $report          import report in xml format
     */
    protected function triggerImport($download, $type = 'data')
    {
        $this->getDataProvider()->setCurlOptions(array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 360
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
