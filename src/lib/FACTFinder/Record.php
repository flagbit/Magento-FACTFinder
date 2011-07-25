<?php

/**
 * represents a FACT-Finder data record
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Common
**/
class FACTFinder_Record
{
    protected $id;
    protected $similarity;
    protected $position;
    protected $origPosition;
    protected $fieldValues;
    protected $fieldNames;
	
	/**
	 * new values since FACT-Finder 6.6
	 */
	protected $seoPath = '';
	protected $keywords = array();

    /**
     * class constructor - creates a record using the given values. if the array contains fieldnames as array-keys, they
     * could be used to get the values again
     *
     * @param string id
     * @param double similarity
     * @param int originalPosition (optional)
     * @param array fieldValues (optional)
    **/
    public function __construct($id, $similarity, $position = 0, $origPosition = 0, array $fieldValues = null)
    {
        $this->id = trim($id);
        $this->similarity = doubleval($similarity);
        if ($this->similarity > 100.0) {
            $this->similarity = 100.0;
        } else if ($this->similarity < 0.0) {
            $this->similarity = 0.0;
        }
        
        $this->position = intval($position);
        $this->origPosition = intval($origPosition);
        
        if (empty($fieldValues)) {
            $this->fieldNames = array();
            $this->fieldValues = array();
        } else {
            $this->setValues($fieldValues);
        }
    }
    
    /**
     * @return string id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * get FACT-Finder similarity, which lays between 0.0 and 100.0, but normaly is greater then the set minSimilarity
     *
     * @return double similarity
     */
    public function getSimilarity()
    {
        return $this->similarity;
    }
    
    /**
     * get original position or 0 if there is no original position
     *
     * @return int original position
     */
    public function getOriginalPosition()
    {
        return $this->origPosition;
    }
        
    /**
     * get position or 0 if there is no position
     *
     * @return int position
     */
    public function getPosition()
    {
        return $this->position;
    }
	
	/**
	 * get array of keywords for this record. this keywords can be used as meta-keywords on the website
	 * for seo-optimization.
	 * by default (if used with an older FF-version or when the seo features are not enabled) the keywords are an empty array
	 *
	 * @version since FF6.6
	 * @return array
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}
    
	/**
	 * returnes the seo path which can be used to fetch the data from this record from FACT-Finder.
	 * this can be used as link to the detail page of this product.
	 * by default (if used with an older FF-version or when the seo features are not enabled) this path is an empty string
	 *
	 * @version since FF6.6
	 * @return string seoPath to this record
	 */
	public function getSeoPath()
	{
		return $this->seoPath;
	}
	
    /**
     * get a value from a field defined by the argument $field, which can be a fieldnumber or a fieldname
     * of the field does not exist, null will be returned
     *
     * @throws Exception if the argument $field is either an integer nor a string
     * @param int|string fieldnumber or fieldname
     * @return string fieldvalue or null if field does not exist
     */
    public function getValue($field)
    {
        $returnValue = null;
        if (is_int($field)) {
            $returnValue = isset($this->fieldValues[$field]) ? $this->fieldValues[$field] : null;
        } else if (is_string($field)) {
            //get value by number and the number by name (mapping from name to value)
            $returnValue = isset($this->fieldNames[$field]) ? $this->fieldValues[$this->fieldNames[$field]] : null;
        }
        return $returnValue;
    }
    
    /**
     * proxy method for getValue()
     * @see FACTFinder_Record::getValue()
     */
    public function __get($name)
    {
        return $this->getValue($name);
    }
    
	/**
	 * set seo path for this record. this seo path can't be set by the constructor, because it was added
	 * in a later version.
	 * 
	 * @version since FF6.6
	 * @param string seo path
	 */
	public function setSeoPath($seoPath)
	{
		if ($seoPath != null) {
			$this->seoPath = strval($seoPath);
		}
	}
	
	/**
	 * add keyword to this record.
	 *
	 * @version since FF6.6
	 * @see FACTFinder_Record::getKeywords()
	 * @param string keyword
	 */
	public function addKeyword($keyword) {
		$this->keywords[] = $keyword;
	}
	
	/**
	 * add keywords to this record.
	 * the keys of the array are ignored, so only the values are used to set the keywords.
	 *
	 * @version since FF6.6
	 * @see FACTFinder_Record::getKeywords()
	 * @param array of strings/keywords
	 */
	public function addKeywords(array $keywords) {
		$this->keywords += array_values($keywords);
	}
	
	/**
	 * set keywords for this record.
	 * the keys of the array are ignored, so only the values are used to set the keywords.
	 *
	 * @version since FF6.6
	 * @see FACTFinder_Record::getKeywords()
	 * @param array of strings/keywords
	 */
	public function setKeywords(array $keywords)
	{
		$this->keywords = array_values($keywords);
	}
	
    /**
     * set a value to field defined by the argument $field, which can be a fieldnumber or a fieldname
     *
     * @throws Exception if the argument $field is either an integer nor a string
     * @param int|string fieldnumber or fieldname
     * @param string fieldvalue
     * @return void
     */
    public function setValue($field, $value)
    {
        if (is_int($field)) {
            $this->fieldValues[$field] = $value;
        } else if (is_string($field)) {
            if (!isset($this->fieldNames[$field])) {
                // create a new field
                $this->fieldNames[$field] = sizeof($this->fieldValues);
                $this->fieldValues[] = $value;
            } else {
                $this->fieldValues[$this->fieldNames[$field]] = $value;
            }
        } else {
            throw new Exception("it is not (yet) possible to refer to a field using ".gettype($field));
        }
    }
    
    /**
     * proxy method for setValue()
     * @see FACTFinder_Record::setValue()
     *
     * @throws Exception if the argument $field is either an integer nor a string
     * @param int|string fieldnumber or fieldname
     * @param string fieldvalue
     * @return void
     */
    public function __set($name, $value)
    {
        return $this->setValue($name, $value);
    }
    
    /**
     * set a bulk of values. if the array contains fieldnames as array-keys, they
     * could be used to get the values again
     *
     * @param array fieldvalues with fieldnames as key
     * @return void
     */
    public function setValues(array $fieldValues)
    {
        foreach ($fieldValues AS $name => $value) {
            $this->setValue($name, $value);
        }
    }
}
