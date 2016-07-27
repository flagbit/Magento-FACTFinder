<?php
/**
 * FACTFinder_TagCloud
 *
 * @category Mage
 * @package FACTFinder_TagCloud
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * @category Mage
 * @package FACTFinder_TagCloud
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tagcloud_Model_Facade extends FACTFinder_Core_Model_Facade
{


    /**
     * Get tag cloud adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\AbstractAdapter
     */
    public function getTagCloudAdapter($channel = null)
    {
        return $this->_getAdapter("tagCloud", $channel);
    }


    /**
     * Configure tag cloud adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureTagCloudAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "tagCloud", $channel, $id);
    }


    /**
     * Get tag cloud object
     *
     * @param string $channel
     * @param int    $id
     *
     * @return Object
     */
    public function getTagCloud($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("tagCloud", "getTagCloud", $channel, $id);
    }


}
