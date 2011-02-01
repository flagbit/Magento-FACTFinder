<?php
/**
 * represents a factfinder campaign
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Common
 */
class FACTFinder_Campaign
{
    private $name;
    private $category;

    private $redirectUrl = null;
    private $pushedProducts = array();
    private $feedback = array();

    /**
     * @param string name
     * @param string category
     * @param string redirectUrl (default: empty string)
     * @param array pushedProducts; array of records
     * @param array feedback; array of strings
     */
    public function __construct($name = '', $category = '', $redirectUrl = '', array $pushedProducts = array(), $feedback = array()) {
        $this->name = trim($name);
        $this->category = trim($category);
        $this->redirectUrl = trim($redirectUrl);
        $this->addPushedProducts($pushedProducts);
        $this->addFeedback($feedback);
    }
    
    /**
     * @return string name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * @return string url
     */
    public function getCategory(){
        return $this->category;
    }
    
    /**
     * true if a redirect link was set
     * 
     * @return boolean
     */
    public function hasRedirect()
    {
        return !empty($this->redirectUrl);
    }
    
    /**
     * @return string url
     */
    public function getRedirectUrl(){
        return $this->redirectUrl;
    }

    /**
     * add products to this campaigns
     *
     * @param array of FACTFinder_Record objects
     * @return void
     */
    public function addPushedProducts(array $pushedProducts) {
        foreach ($pushedProducts AS $product) {
            $this->pushedProducts[] = $product;
        }
    }
    
    /**
     * true if pushed products exist
     * 
     * @return boolean
     */
    public function hasPushedProducts() {
        return sizeof($this->pushedProducts) > 0;
    }
    
    /**
     * @return array of records
     */
    public function getPushedProducts() {
        return $this->pushedProducts;
    }
    
    /**
     * set the feedback strings. if a feedback with the same key (number) exist, it will be overwritten
     *
     * @param array of string
     * @return void
     */
    public function addFeedback(array $feedback) {
        foreach($feedback AS $nr => $text) {
            $this->feedback[$nr] = trim($text);
        }
    }
    
    /**
     * returns true if feedback exists. if no number is given as argument, this methods checks, whether there is any
     * feedback text available. if a number is given, then this method only returns true, if there is a feedback text
     * for this number
     * 
     * @param int number of feedback (default: null)
     * @return boolean
     */
    public function hasFeedback($nr = null) {
        if (is_int($nr)) {
            $hasFeedback = isset($this->feedback[$nr]) && $this->feedback[$nr] != '';
        } else {
            $hasFeedback = sizeof($this->feedback) > 0 && implode('', $this->feedback) != '';
        }
        return $hasFeedback;        
    }
    
    /**
     * when number is given, only the wished feedback text will be returned or an empty string, if this text does not exist.
     * if no number is set, the complete feedback array will be returned
     * 
     * @param int $nr
     * @return array|string
     */
    public function getFeedback($nr = null) {
        if ($nr === null) {
            return $this->feedback;
        } else if (isset($this->feedback[$nr])) {
            return $this->feedback[$nr];
        } else {
            return '';
        }
    }
}