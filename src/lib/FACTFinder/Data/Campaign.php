<?php
namespace FACTFinder\Data;

class Campaign
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var Record[]
     */
    private $pushedProducts = array();

    /**
     * @var string[]
     */
    private $feedback = array();

    /**
     * @var AdvisorQuestion[]
     */
    private $activeQuestions = array();

    /**
     * @var AdvisorQuestion[]
     */
    private $advisorTree = array();

    /**
     * @param string $name
     * @param string $category
     * @param string $redirectUrl
     * @param Record[] $pushedProducts
     * @param array feedback; array of strings with labels as keys
     * @param array activeQuestions; array of FACTFinder_AdvisorQuestion objects
     */
    public function __construct(
        $name,
        $category = '',
        $redirectUrl = '',
        array $pushedProducts = array(),
        array $feedback = array(),
        array $activeQuestions = array(),
        array $advisorTree = array()
    ) {
        $this->name = (string)$name;
        $this->category = (string)$category;
        $this->redirectUrl = (string)$redirectUrl;
        $this->addPushedProducts($pushedProducts);
        $this->addFeedback($feedback);
        $this->addActiveQuestions($activeQuestions);
        $this->addToAdvisorTree($advisorTree);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return bool True, if a redirect link was set.
     */
    public function hasRedirect()
    {
        return !empty($this->redirectUrl);
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param Record[]
     */
    public function addPushedProducts(array $pushedProducts)
    {
        foreach ($pushedProducts as $product)
            $this->pushedProducts[] = $product;
    }

    /**
     * @return bool True, if at least one pushed product exists.
     */
    public function hasPushedProducts()
    {
        return count($this->pushedProducts) > 0;
    }

    /**
     * @return Record[]
     */
    public function getPushedProducts()
    {
        return $this->pushedProducts;
    }

    /**
     * In case of a collision of keys, the existing feedback string will be
     * overwritten.
     *
     * @param string[]
     */
    public function addFeedback(array $feedback)
    {
        foreach($feedback as $label => $text)
            $this->feedback[$label] = (string)$text;
    }

    /**
     * @param string $label Optional label to check.
     * @return bool If $label parameter is given, this returns true if there is
     *         any feedback for that particular label. If $label is ommitted,
     *         this returns true if there is any feedback at all.
     */
    public function hasFeedback($label = null)
    {
        if (is_null($label))
            return count($this->feedback) > 0
                   && implode('', $this->feedback) != '';
        else
            return isset($this->feedback[$label])
                   && $this->feedback[$label] != '';
    }

    /**
     * @param string $label
     * @return string
     */
    public function getFeedback($label)
    {
        if (isset($this->feedback[$label]))
            return $this->feedback[$label];
        else
            return '';
    }

    /**
     * @param AdvisorQuestion[] $activeQuestions
     */
    public function addActiveQuestions(array $activeQuestions)
    {
        foreach ($activeQuestions as $question)
            $this->activeQuestions[] = $question;
    }

    /**
     * @return bool
     */
    public function hasActiveQuestions()
    {
        return count($this->activeQuestions) > 0;
    }

    /**
     * @return AdvisorQuestion[]
     */
    public function getActiveQuestions()
    {
        return $this->activeQuestions;
    }

    /**
     * Add questions to the top level of the advisor tree.
     *
     * @param AdvisorQuestion[] $advisorTree
     */
    public function addToAdvisorTree(array $advisorTree)
    {
        foreach ($advisorTree as $question)
            $this->advisorTree[] = $question;
    }

    /**
     * @return bool
     */
    public function hasAdvisorTree()
    {
        return count($this->advisorTree) > 0;
    }

    /**
     * @return AdvisorQuestion[]
     */
    public function getAdvisorTree()
    {
        return $this->advisorTree;
    }
}
