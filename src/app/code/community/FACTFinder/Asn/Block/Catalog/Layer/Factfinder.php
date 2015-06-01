<?php

class FACTFinder_Asn_Block_Catalog_Layer_Factfinder extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    const TYPE_SLIDER = 'slider';


    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'factfinder_asn/layer_filter_factfinder';
    }


    /**
     * Initialize filter model object
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
        }

        return $this;
    }


}