<?php
class FACTFinder_Core_Model_Export_Config
{
    const EXPORT_TYPE_CONFIG_PATH = 'factfinder/export';


    /**
     * Array of export types and models which implement FACTFinder_Core_Model_Export_Type_Interface
     *
     * @var array
     */
    protected $_types = array();


    /**
     * Load types from configuration
     *
     * @return $this
     */
    protected function _load()
    {
        $nodes = Mage::getConfig()->getNode(self::EXPORT_TYPE_CONFIG_PATH);

        if (empty($nodes)) {
            return $this;
        }

        foreach ($nodes as $node) {
            foreach ($node as $element) {
                if (isset($element->model)) {
                    $model = Mage::getModel($element->model[0]);
                    if ($model instanceof FACTFinder_Core_Model_Export_Type_Interface && $model->isEnabled()) {
                        $this->_types[$element->getName()] = $model;
                    }
                }
            }
        }

        return $this;
    }


    /**
     * Get array of export types
     *
     * @return array
     */
    public function getTypes()
    {
        if (empty($this->_types)) {
            $this->_load();
        }

        return $this->_types;
    }


    /**
     * Retrieve export model by type
     *
     * @param string $type
     *
     * @return FACTFinder_Core_Model_Export_Type_Interface
     */
    public function getExportModel($type)
    {
        $types = $this->getTypes();

        if (!isset($types[$type])) {
            Mage::throwException(sprintf('Requested export type "%s" is not available', $type));
        }

        return $types[$type];
    }


}
