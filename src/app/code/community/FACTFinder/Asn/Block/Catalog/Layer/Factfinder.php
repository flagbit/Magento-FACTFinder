<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Class FACTFinder_Asn_Block_Catalog_Layer_Factfinder
 *
 * Replaces default layer filter attribute
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Block_Catalog_Layer_Factfinder extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    const TYPE_SLIDER = 'slider';


    /**
     * Class constructor
     * Here we just set our own model as filter model
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'factfinder_asn/layer_filter_factfinder';
    }


    /**
     * Initialize filter model object
     * The only thing we need here is to set our own template for price filter
     *
     * @return FACTFinder_Asn_Block_Catalog_Layer_Factfinder
     */
    public function init()
    {
        parent::init();

        $attribute = $this->getAttributeModel();
        if ($attribute->getType() == self::TYPE_SLIDER) {
            $this->setTemplate('factfinder/asn/layer/filter/slider.phtml');
            $this->setData((current($attribute->getItems())));
            $this->setUnit($attribute->getUnit());
            $this->setLinkCount($attribute->getLinkCount());
        }

        return $this;
    }


}