<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * import adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: ImportAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_ImportAdapter extends FACTFinder_Abstract_Adapter
{
    /**
     * trigger a data import
     *
     * @param  bool   $download   import files will also be updated if true
     * @return object $report     import report in xml format
     */
    public function triggerDataImport($download = false) {
        return $this->triggerImport($download, 'data');
    }

    /**
     * trigger a suggest import
     *
     * @param  bool   $download   import files will also be updated if true
     * @return object $report     import report in xml format
     */
    public function triggerSuggestImport($download = false) {
        return $this->triggerImport($download, 'suggest');
    }

    /**
     * trigger a recommendation import
     *
     * @param  bool   $download   import files will also be updated if true
     * @return object $report     import report in xml format
     */
    public function triggerRecommendationImport($download = false) {
        return $this->triggerImport($download, 'recommendation');
    }

    /**
     * @param  bool   $download        import files will also be updated if true
     * @param  string $type              determines which import will be triggered. can be 'data', 'suggest' or 'recommendation'
     * @return object $report          import report in xml format
     */
    protected function triggerImport($download, $type)
    {
        $this->log->debug("Triggering imports is not available before FF 6.0!");
        return null;
    }
}
