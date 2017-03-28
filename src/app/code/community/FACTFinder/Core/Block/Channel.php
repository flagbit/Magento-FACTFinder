<?php
/**
* FACTFinder_Core
*
* @category Mage
* @package FACTFinder_Core
* @author Flagbit Magento Team <magento@flagbit.de>
* @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
* @license https://opensource.org/licenses/MIT  The MIT License (MIT)
* @link http://www.flagbit.de
*
*/
?>
<?php

/**
 * Class FACTFinder_Core_Block_Channel
 *
 * This class can be extended for being able to retrieve results of separate
 * channels and load specific entities like CMS Page, Reviews etc
 */
class FACTFinder_Core_Block_Channel extends Mage_Core_Block_Template
{


    /**
     * Get search results array
     *
     * This method can be used or extended for loading specific entities.
     *
     * @return array
     */
    public function getResults()
    {
        /** @var  FACTFinder_Core_Model_Facade $facade */
        $facade = Mage::getSingleton('factfinder/facade');
        $facade->configureSearchAdapter(array(
            'idsOnly' => false,
            'channel' => $this->getChannel()
        ));

        return (array) $facade->getSearchResult($this->getChannel());
    }


}