<?php
namespace FACTFinder\Data;

/**
 * Represents one entity of the data indexed by FACT-Finder (typically a
 * product).
 */
class Record
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string[] An associative array of the record's fields (the keys being
     *      field names).
     */
    private $fields;

    /**
     * @var float
     */
    private $similarity;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $seoPath;

    /**
     * @var string[] List-like array of keywords.
     */
    private $keywords;

    /**
     * @param string $id
     * @param string[] fields
     * @param float $similarity
     * @param int $position If the record is part of a result, this indicates
     *        the position within that result.
     * @param string $seoPath
     * @param string[] $keywords
    **/
    public function __construct(
        $id,
        $fields = array(),
        $similarity = 100,
        $position = 0,
        $seoPath = '',
        $keywords = array()
    ) {
        $this->id = trim($id);
        $this->fields = $fields;
        // Clamp similarity to interval [0,100]
        $this->similarity = max(0, min(100, (float)$similarity));
        $this->position = (int)$position;
        $this->seoPath = (string)$seoPath;
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getSimilarity()
    {
        return $this->similarity;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getSeoPath()
    {
        return $this->seoPath;
    }

    /**
     * @param string $name The name of the field to be retrieved.
     * @return string
     */
    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    /**
     * @return string[]
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
}
