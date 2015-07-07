<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Block_Advisory_Search
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Block_Advisory_Search extends FACTFinder_Campaigns_Block_Abstract
{

    /**
     * @var string
     */
    protected $_handlerModel = 'factfinder_campaigns/handler_search';


    /**
     * Get Campaign Text
     *
     * @return array
     */
    public function getActiveQuestions()
    {
        $questions = array();

        $_campaigns = $this->_getHandler()->getCampaigns();
        if ($_campaigns && $_campaigns->hasActiveQuestions()) {
            $questions = $_campaigns->getActiveQuestions();
        }

        return $questions;
    }


}
