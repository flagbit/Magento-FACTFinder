<?php
namespace FACTFinder\Data;

class AdvisorQuestion
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var AdvisorAnswer[]
     */
    private $answer;

    /**
     * @param string $text
     * @param AdvisorAnswer[] $answers
     */
    public function __construct(
        $text,
        array $answers = array()
    ) {
        $this->text = (string)$text;

        $this->answers = $answers;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param AdvisorAnswer[] $answers
     */
    public function addAnswers(array $answers)
    {
        foreach ($answers as $question)
            $this->answers[] = $question;
    }

    /**
     * @return bool True, if at least one answer exists.
     */
    public function hasAnswers()
    {
        return count($this->answers) > 0;
    }

    /**
     * @return AdvisorAnswer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }
}
