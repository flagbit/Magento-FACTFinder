<?php
namespace FACTFinder\Data;

class AdvisorAnswer extends Item
{
    /**
     * @var AdvisorQuestion[]
     */
    private $followUpQuestions;

    /**
     * @param string $text The answer text to be displayed.
     * @param string $url
     * @param AdvisorQuestion[] $followUpQuestions Optional array of questions
     *        that will follow this answer.
     */
    public function __construct(
        $text,
        $url,
        $followUpQuestions = array()
    ) {
        parent::__construct($text, $url);

        $this->followUpQuestions = $followUpQuestions;
    }

    /**
     * This is just an alias.
     * @see Item::getLabel()
     */
    public function getText()
    {
        return $this->getLabel();
    }

    /**
     * @param AdvisorQuestion[] $followUpQuestions
     */
    public function addFollowUpQuestions(array $followUpQuestions)
    {
        foreach ($followUpQuestions as $question)
            $this->followUpQuestions[] = $question;
    }

    /**
     * @return bool True, if at least one follow-up question exists.
     */
    public function hasFollowUpQuestions()
    {
        return count($this->followUpQuestions) > 0;
    }

    /**
     * @return AdvisorQuestion[]
     */
    public function getFollowUpQuestions()
    {
        return $this->followUpQuestions;
    }
}
