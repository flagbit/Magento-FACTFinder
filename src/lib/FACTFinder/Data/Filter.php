<?php
namespace FACTFinder\Data;

/**
 * Represents a particular clickable filter within the After Search Navigation
 * (ASN).
 */
class Filter extends Item
{
    private $matchCount;
    private $clusterLevel;
    private $previewImage;
    private $fieldName;

    public function __construct(
        $label,
        $url,
        $isSelected = false,
        $fieldName = '',
        $matchCount = 0,
        $clusterLevel = 0,
        $previewImage = ''
    ) {
        parent::__construct($label, $url, $isSelected);
        $this->fieldName = (string)$fieldName;
        $this->matchCount = (int)$matchCount;
        $this->clusterLevel = (int)$clusterLevel;
        $this->previewImage = (string)$previewImage;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return int
     */
    public function getMatchCount()
    {
        return $this->matchCount;
    }

    /**
     * @return int
     */
    public function getClusterLevel()
    {
        return $this->clusterLevel;
    }

    /**
     * @return bool
     */
    public function hasPreviewImage()
    {
        return !empty($this->previewImage);
    }

    /**
     * @return string
     */
    public function getPreviewImage()
    {
        return $this->previewImage;
    }
}
