<?php
namespace FACTFinder\Data;

/**
 * A collection of all Campaign objects. Provides some convenience methods to
 * query all campaigns at once.
 */
class CampaignIterator extends \ArrayIterator
{
    /**
     * @param Campaign[] $campaigns
     */
    public function __construct(array $campaigns)
    {
        parent::__construct($campaigns);
    }

    /**
     * @see Campaign::hasRedirect()
     * @return bool
     */
    public function hasRedirect()
    {
        foreach ($this as $campaign)
            if ($campaign->hasRedirect())
                return true;

        return false;
    }

    /**
     * Gets the first redirect URL if any campaign has a redirect. Returns null
     * otherwise.
     *
     * @see Campaign::getRedirectUrl()
     * @return string
     */
    public function getRedirectUrl()
    {
        foreach ($this as $campaign)
            if ($campaign->hasRedirect())
                return $campaign->getRedirectUrl();

        return null;
    }

    /**
     * @see Campaign::hasFeedback()
     * @return bool
     */
    public function hasFeedback()
    {
        foreach ($this as $campaign)
            if ($campaign->hasFeedback())
                return true;

        return false;
    }

    /**
     * Returns all feedback texts for the given label, concatenated with
     * PHP_EOL.
     *
     * @see Campaign::getFeedback()
     * @param string $label
     * @return string
     */
    public function getFeedback($label)
    {
        $feedback = array();
        foreach ($this as $campaign)
            if ($campaign->hasFeedback($label))
                $feedback[] = $campaign->getFeedback($label);

        return implode(PHP_EOL, $feedback);
    }

    /**
     * @see Campaign::hasPushedProducts()
     * @return bool
     */
    public function hasPushedProducts()
    {
        foreach ($this as $campaign)
            if ($campaign->hasPushedProducts())
                return true;

        return false;
    }

    /**
     * Returns an array with the pushed products from all campaigns. Note that
     * if a product shows up in multiple campaigns, it will be duplicated in the
     * result of this function.
     *
     * @see Campaign::getPushedProducts()
     * @return Record[]
     */
    public function getPushedProducts()
    {
        $pushedProducts = array();
        foreach ($this as $campaign)
            if ($campaign->hasPushedProducts())
                foreach ($campaign->getPushedProducts() as $product)
                    $pushedProducts[] = $product;

        return $pushedProducts;
    }

    /**
     * @see Campaign::hasActiveQuestions()
     * @return bool
     */
    public function hasActiveQuestions()
    {
        foreach ($this as $campaign)
            if ($campaign->hasActiveQuestions())
                return true;

        return false;
    }

    /**
     * Returns an array with the active questions from all campaigns.
     *
     * @see Campaign::getActiveQuestions()
     * @return Record[]
     */
    public function getActiveQuestions()
    {
        $activeQuestions = array();
        foreach ($this as $campaign)
            if ($campaign->hasActiveQuestions())
                foreach ($campaign->getActiveQuestions() as $question)
                    $activeQuestions[] = $question;

        return $activeQuestions;
    }

    /**
     * @see Campaign::hasAdvisorTree()
     * @return bool
     */
    public function hasAdvisorTree()
    {
        foreach ($this as $campaign)
            if ($campaign->hasAdvisorTree())
                return true;

        return false;
    }

    /**
     * Returns an array with the root questions of the advisor trees from all
     * campaigns.
     *
     * @see Campaign::getAdvisorTree()
     * @return Record[]
     */
    public function getAdvisorTree()
    {
        $advisorTree = array();
        foreach ($this as $campaign)
            if ($campaign->hasAdvisorTree())
                foreach ($campaign->getAdvisorTree() as $question)
                    $advisorTree[] = $question;

        return $advisorTree;
    }
}
