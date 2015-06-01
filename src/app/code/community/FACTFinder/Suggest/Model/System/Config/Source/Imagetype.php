<?php
/**
 * Image type source model
 */
class FACTFinder_Suggest_Model_System_Config_Source_Imagetype
{


    /**
     * Get available image types
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach (Mage::getModel('catalog/product')->getMediaAttributes() as $key => $value) {
            $options[] = array(
                'label' => $value->getFrontendLabel(),
                'value' => $key
            );
        }

        return $options;
    }


}

