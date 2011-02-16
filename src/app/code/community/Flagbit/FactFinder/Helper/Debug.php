<?php 
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Helper class
 * 
 * This helper class provides some Methods which allows us 
 * to debug Modul specific configurations Problems. 
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: Search.php 619 2011-02-10 08:03:17Z weller $
 */
class Flagbit_FactFinder_Helper_Debug extends Mage_Core_Helper_Abstract {
	
	const MODULE_CONFIG_FILE = "config.xml";
	
	/**
	 * get Class Rewrite Conflicts for the current Modul
	 * 
	 * return array
	 */
	public function getRewriteConflicts()
	{	
		$rewriteConflicts = array();
		$xml = simplexml_load_file(Mage::getConfig()->getModuleDir('etc', $this->_getModuleName()).DS.self::MODULE_CONFIG_FILE);                         
		if ($xml instanceof SimpleXMLElement) {
			$rewriteNodes = $xml->xpath('//rewrite');

            foreach ($rewriteNodes as $n) {
                $nParent = $n->xpath('..');
                $module = (string) $nParent[0]->getName();
                $nParent2 = $nParent[0]->xpath('..');
                $component = (string) $nParent2[0]->getName();
                $pathNodes = $n->children();

                foreach ($pathNodes as $pathNode) {

                    $path = (string) $pathNode->getName();
                    $completePath = $module.'/'.$path;

                    $rewriteClassName = (string) $pathNode;
                        
                    $instance = Mage::getConfig()->getGroupedClassName(
                        substr($component, 0, -1),
                        $completePath
                    );
                    if($instance != $rewriteClassName){
						$rewriteConflicts[$rewriteClassName] = $instance;
                    } 
                }
            }
				
		}
		return $rewriteConflicts;
	}

}