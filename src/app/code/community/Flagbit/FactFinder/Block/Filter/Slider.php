<?php 
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class
 * 
 * Add Slider Javascript to HTML Head 
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Filter_Slider extends Mage_Core_Block_Abstract {
	
	/**
	 * get Slider Javascript
	 * 
	 *  @return string
	 */
	protected function _toHtml()
	{
		if(Mage::helper('factfinder/search')->getIsEnabled()){
    		return '<script type="text/javascript" language="javascript" src="http://static.express.fact-finder.com/onetouchslider-1.0/de.factfinder.asn.slider.OneTouchSlider.nocache.js"></script>'."\n".
				   '<script type="text/javascript" language="javascript"> oneTouchSliderOnLoad = function(){ document.fire("ffslider:init");}</script>'."\n";
			
    	} 		
	}
}