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
 * @version   $Id$
 */
class Flagbit_FactFinder_Helper_Debug extends Mage_Core_Helper_Abstract
    implements FACTFinder_Logger_LoggerInterface
{
    /**
     * Module Configuration File
     *
     * @var string
     */
    const MODULE_CONFIG_FILE = 'config.xml';

    /**
     * Module Log File
     *
     * @var string
     */
    const LOG_FILE_NAME = 'factfinder.log';

    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_DEBUG_MODE = 'factfinder/config/debug';

    /**
     * Debug Log to file var/log/factfinder.log
     *
     * @param $message
     * @param $level
     * @param $file
     * @param $forceLog
     */
    public function log($message)
    {
        if (!Mage::getConfig()) {
            return;
        }
        try{
            if(Mage::getStoreConfig(self::XML_CONFIG_PATH_DEBUG_MODE)) {
                return Mage::log($message, null, self::LOG_FILE_NAME, true);
            }
        }catch (Exception $e){}

        return $this;
    }
    
    public function error($error)
    {
        return $this->log('ERROR: ' . $error);
    }
    
    public function info($message)
    {
        return $this->log('INFO: ' . $message);
    }

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

                        try{
                            $reflector = new $instance();
                            if($reflector instanceof $rewriteClassName){
                                continue;
                            }
                        }catch (Exception $e){}

                        $rewriteConflicts[$rewriteClassName] = $instance;
                    }
                }
            }
        }
        return $rewriteConflicts;
    }

}