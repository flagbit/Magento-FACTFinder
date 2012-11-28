<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml67
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * tag cloud adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: TagCloudAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml68
 */
class FACTFinder_Default_TagCloudAdapter extends FACTFinder_Abstract_Adapter
{
    private $tagCloud;

    /**
     * get tag cloud
     *
     * @return array $tagCloud list of FACTFinder_Tag objects
     */
    public function getTagCloud() {
        if ($this->tagCloud == null) {
            $this->tagCloud = $this->createTagCloud();
        }
        return $this->tagCloud;
    }

    /**
     * @return array $tagCloud list of FACTFinder_Tag objects
     */
    protected function createTagCloud()
    {
        $this->log->debug("Tag cloud not available before FF 6.0!");
        return array();
    }
}
