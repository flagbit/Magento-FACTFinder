<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * represents a factfinder campaign
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Campaign.php 25985 2010-06-30 15:31:53Z rb $
 * @package   FACTFinder\Common
 */
class FACTFinder_Campaign
{
    private $name;
    private $category;

    private $redirectUrl = null;
    private $pushedProducts = array();
    private $feedback = array();
	private $activeQuestions = array();
	private $advisorTree = array();

    /**
     * @param string name
     * @param string category
     * @param string redirectUrl (default: empty string)
     * @param array pushedProducts; array of records
     * @param array feedback; array of strings with labels as keys
	 * @param array activeQuestions; array of FACTFinder_AdvisorQuestion objects
     */
    public function __construct($name = '', $category = '', $redirectUrl = '', array $pushedProducts = array(), $feedback = array(), $activeQuestions = array(), $advisorTree = array()) {
        $this->name = trim($name);
        $this->category = trim($category);
        $this->redirectUrl = trim($redirectUrl);
        $this->addPushedProducts($pushedProducts);
        $this->addFeedback($feedback);
		$this->addActiveQuestions($activeQuestions);
		$this->addToAdvisorTree($advisorTree);
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
     * add products to this campaign
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
     * @return array of FACTFinder_Record objects
     */
    public function getPushedProducts() {
        return $this->pushedProducts;
    }

    /**
     * set the feedback strings. if a feedback with the same key (label) exist, it will be overwritten
     *
     * @param array of string
     * @return void
     */
    public function addFeedback(array $feedback) {
        foreach($feedback AS $label => $text) {
            $this->feedback[$label] = trim($text);
        }
    }

    /**
     * returns true if feedback exists. if argument is specified, this methods checks, whether there is any
     * feedback text available. if a label is given, then this method only returns true, if there is a feedback text
     * for this label
     *
     * @param string label of feedback (default: null)
     * @return boolean
     */
    public function hasFeedback($label = null) {
        if ($label != null) {
            $hasFeedback = isset($this->feedback[$label]) && $this->feedback[$label] != '';
		} else {
            $hasFeedback = sizeof($this->feedback) > 0 && implode('', $this->feedback) != '';
        }
        return $hasFeedback;
    }

    /**
     * when label is specified, only the desired feedback text will be returned or an empty string, if this text does not exist.
     * if no label is set, the complete feedback array will be returned
     *
     * @param string $label
     * @return array|string
     */
    public function getFeedback($label = null) {
        if ($label === null) {
            return $this->feedback;
        } else if (isset($this->feedback[$label])) {
            return $this->feedback[$label];
        } else {
            return '';
        }
    }
	
	/**
     * add active questions to this campaign
     *
     * @param array of FACTFinder_AdvisorQuestion objects
     * @return void
     */
    public function addActiveQuestions(array $activeQuestions) {
        foreach ($activeQuestions AS $question) {
            $this->activeQuestions[] = $question;
        }
    }

    /**
     * true if advisor questions exist
     *
     * @return boolean
     */
    public function hasActiveQuestions() {
        return sizeof($this->activeQuestions) > 0;
    }

    /**
     * @return array of FACTFinder_AdvisorQuestion objects
     */
    public function getActiveQuestions() {
        return $this->activeQuestions;
    }
	
	/**
     * add questions to the advisor tree (top level) of this campaign
     *
     * @param array of FACTFinder_AdvisorQuestion objects
     * @return void
     */
    public function addToAdvisorTree(array $advisorTree) {
        foreach ($advisorTree AS $question) {
            $this->advisorTree[] = $question;
        }
    }

    /**
     * true if advisor tree exists
     *
     * @return boolean
     */
    public function hasAdvisorTree() {
        return sizeof($this->advisorTree) > 0;
    }

    /**
     * @return array of FACTFinder_AdvisorQuestion objects
     */
    public function getAdvisorTree() {
        return $this->advisorTree;
    }
}