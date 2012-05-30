<?php
/**
 * represents a question of an advisor campaign
 * relevant for FF versions >= 6.7
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: AdvisorQuestion.php 41893 2012-01-16 13:47:52Z mb $
 * @package   FACTFinder\Common
 */
class FACTFinder_AdvisorQuestion
{
    private $text;

    private $answers = array();
    
    /**
     * @param string question text
     * @param array answers; array of FACTFinder_AdvisorAnswer objects
     */
    public function __construct($text = '', $answers = array()) {
        $this->text = trim($text);
        $this->addAnswers($answers);
    }
    
    /**
     * @return string text
     */
    public function getText() {
        return $this->text;
    }
    
    /**
     * add answers to this question
     *
     * @param array of FACTFinder_AdvisorAnswer objects
     * @return void
     */
    public function addAnswers(array $answers) {
        foreach ($answers AS $answer) {
            $this->answers[] = $answer;
        }
    }

    /**
     * true if answers exist
     *
     * @return boolean
     */
    public function hasAnswers() {
        return sizeof($this->answers) > 0;
    }

    /**
     * @return array of FACTFinder_AdvisorAnswer objects
     */
    public function getAnswers() {
        return $this->answers;
    }
}