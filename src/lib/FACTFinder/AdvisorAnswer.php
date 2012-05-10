<?php
/**
 * represents an answer to a question of an advisor campaign (see FACTFinder_AdvisorQuestion)
 * relevant for FF versions >= 6.7
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: AdvisorAnswer.php 41893 2012-01-16 13:47:52Z mb $
 * @package   FACTFinder\Common
 */
class FACTFinder_AdvisorAnswer
{
    private $text;
    private $params;
    
    private $subquestions = array();
    
    /**
     * @param string text
     * @param string params
     * @param array subquestions; array of FACTFinder_AdvisorQuestion objects
     */
    public function __construct($text = '', $params = '', $subquestions = array()) {
        $this->text = trim($text);
        $this->params = trim($params);
        $this->addSubquestions($subquestions);
    }
    
    /**
     * @return string text
     */
    public function getText() {
        return $this->text;
    }
    
    /**
     * @return string params
     */
    public function getParams() {
        return $this->params;
    }
    
    /**
     * add follow-up questions to this answer
     *
     * @param array of FACTFinder_AdvisorQuestion objects
     * @return void
     */
    public function addSubquestions(array $subquestions) {
        foreach ($subquestions AS $question) {
            $this->subquestions[] = $question;
        }
    }

    /**
     * true if follow-up questions exist
     *
     * @return boolean
     */
    public function hasSubquestions() {
        return sizeof($this->subquestions) > 0;
    }

    /**
     * @return array of FACTFinder_AdvisorQuestion objects
     */
    public function getSubquestions() {
        return $this->subquestions;
    }
}