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
 * dynamicaly extends the core class whether Enterprise Search is enabled or not
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: Layer.php 614 2011-02-01 16:08:47Z weller $
 */

if(Mage::helper('factfinder')->isModuleActive('Enterprise_Search')){
	class Flagbit_FactFinder_Block_Layer_Abstract extends Enterprise_Search_Block_Catalogsearch_Layer {}
}else{
	class Flagbit_FactFinder_Block_Layer_Abstract extends Mage_CatalogSearch_Block_Layer {}	
}




