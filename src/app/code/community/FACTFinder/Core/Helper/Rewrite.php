<?php
/**
 * Rewrite.php
 *
 * @category Mage
 * @package magento
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Helper_Rewrite extends Mage_Core_Helper_Abstract
{


    /**
     * Get all factfinder submodules
     * Based on config values so can be a bit not exact
     *
     * @return array
     */
    protected function getAllModules()
    {
        $modules = array(
            'FACTFinder_Core',
        );

        foreach (Mage::app()->getStore()->getConfig('factfinder/modules') as $module => $status) {
            $modules[] = 'FACTFinder_' . ucwords($module);
        }

        return $modules;
    }


    /**
     * Get rewritten blocks or models for module
     *
     * @param string $module
     * @param string $type   blocks|models
     *
     * @return array
     */
    protected function getModuleRewrites($module, $type)
    {
        $result = array();

        if (!Mage::helper('core')->isModuleEnabled($module)) {
            return $result;
        }

        $file = Mage::getModuleDir('etc', $module) . DS  . 'config.xml';
        $configModel = new Varien_Simplexml_Config($file);

        $rewrites = $configModel->getXpath("*/{$type}/*/rewrite");
        if (empty($rewrites)) {
            return $result;
        }

        /** @var Varien_Simplexml_Element $item */
        foreach ( $rewrites as $item) {
            $module = $item->getParent()->getName();
            foreach ($item->children() as $child) {
                $result[$module . '/' . $child->getName()] = $child->asArray();
            }
        }

        return $result;
    }


    /**
     * Check all rewritten blocks and models
     *
     * @return array
     */
    public function checkRewrites()
    {
        $result = array();
        foreach ($this->getAllModules() as $module) {
            // check blocks
            $rewrittenBlocks = $this->getModuleRewrites($module, 'blocks');
            foreach ($rewrittenBlocks as $block => $neededClassName) {
                $actualClassName = Mage::getConfig()->getBlockClassName($block);
                if ($neededClassName !== $actualClassName) {
                    $result[] = $this->getWrongRewriteMessage($neededClassName, $actualClassName);
                }
            }

            // check models
            $rewrittenModels = $this->getModuleRewrites($module, 'models');
            foreach ($rewrittenModels as $model => $neededClassName) {
                $actualClassName = Mage::getConfig()->getModelClassName($model);
                if ($neededClassName !== $actualClassName) {
                    $result[] = $this->getWrongRewriteMessage($neededClassName, $actualClassName);
                }
            }
        }

        return $result;
    }


    /**
     * Get message about wrong class received (not rewritten or rewritten by a wrong module)
     *
     * @return string
     */
    protected function getWrongRewriteMessage($neededClassName, $actualClassName)
    {
        return Mage::helper('factfinder')->__(
            'Rewrite warning: instead of <i>%s</i> got <i>%s</i>',
            $neededClassName,
            $actualClassName
        );
    }


}