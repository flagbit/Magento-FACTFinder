<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Image type source model
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Francesco Marangi <f.marangi@mzentrale.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_System_Config_Source_Imagetype
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
