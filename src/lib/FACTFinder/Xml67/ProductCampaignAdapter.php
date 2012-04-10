<?php

/**
 * product campaign adapter using the xml interface
 *
 * @author    Rudolf Batt <rb@omikron.net>, Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: ProductCampaignAdapter.php 43440 2012-02-08 12:42:13Z martin.buettner $
 * @package   FACTFinder\Xml67
 */
class FACTFinder_Xml67_ProductCampaignAdapter extends FACTFinder_Abstract_ProductCampaignAdapter
{
    protected $xmlData = null;
    
    /**
     * {@inheritdoc}
     *
     * @return void
     **/
    public function init()
    {
        $this->log->info("Initializing new product campaign adapter.");
        $this->getDataProvider()->setType('ProductCampaign.ff');
        $this->getDataProvider()->setParam('format', 'xml');
        $this->getDataProvider()->setParam('do', 'getProductCampaigns');
    }

    /**
     * try to parse data as xml
     *
     * @throws Exception of data is no valid XML
     * @return SimpleXMLElement
     */
    protected function getData()
    {
        if ($this->xmlData == null) {
            libxml_use_internal_errors(true);
            $data = parent::getData();
            $this->xmlData = new SimpleXMLElement($data); //throws exception on error
        }
        return $this->xmlData;
    }
    
        /**
     * {@inheritdoc}
     *
     * @return array of FACTFinder_Campaign objects
     */
    protected function createCampaigns()
    {
        $campaigns = array();
        $xmlResult = $this->getData();
        
        if (!empty($xmlResult->campaigns)) {
            $encodingHandler = $this->getEncodingHandler();
            $paramsParser = $this->getParamsParser();
            
            foreach ($xmlResult->campaigns->campaign AS $xmlCampaign) {
                //get redirect
                $redirectUrl = '';
                if ($xmlCampaign->attributes()->flavour == 'REDIRECT') {
                    $redirectUrl = $encodingHandler->encodeServerUrlForPageUrl(strval($xmlCampaign->target->destination));
                }

                $campaign = FF::getInstance('campaign',
                    $encodingHandler->encodeServerContentForPage(strval($xmlCampaign->attributes()->name)),
                    $encodingHandler->encodeServerContentForPage(strval($xmlCampaign->attributes()->category)),
                    $redirectUrl
                );

                // get feedback
                
                if ($xmlCampaign->attributes()->flavour == 'FEEDBACK') {
                
                    // here is the new feature: getting feedback texts from labels instead of number indices, if available
                    if (!empty($xmlCampaign->feedback)) {
                        $feedback = array();
                        foreach ($xmlCampaign->feedback->text as $text) {
                            if(isset($text->attributes()->label)) {
                                $label = trim($text->attributes()->label);
                            } else {
                                $label = trim($text->attributes()->nr);
                            }
                            $feedback[$label] = $encodingHandler->encodeServerContentForPage((string)$text);
                        }
                        $campaign->addFeedback($feedback);
                    }

                    //get pushed products
                    if (!empty($xmlCampaign->pushedProducts)) {
                        $pushedProducts = array();
                        foreach ($xmlCampaign->pushedProducts->product AS $xmlProduct) {
                            $product = FF::getInstance('record', $xmlProduct->attributes()->id, 100);

                            // fetch product values
                            $fieldValues = array();
                            foreach($xmlProduct->field AS $current_field){
                                $currentFieldname = (string) $current_field->attributes()->name;
                                $fieldValues[$currentFieldname] = (string) $current_field;
                            }
                            $product->setValues($encodingHandler->encodeServerContentForPage($fieldValues));
                            $pushedProducts[] = $product;
                        }
                        $campaign->addPushedProducts($pushedProducts);
                    }
                }
                
                //get advisor
                if ($xmlCampaign->attributes()->flavour == 'ADVISOR') {
                    $activeQuestions = array();
                    
                    // The active questions can still be empty if we have already moved down the whole question tree (while the search query still fulfills the campaign condition)
                    if (!empty($xmlCampaign->advisor->activeQuestions)) {
                        foreach($xmlCampaign->advisor->activeQuestions->question AS $xmlQuestion) {
                            $activeQuestions[] = $this->loadAdvisorQuestion($xmlQuestion);
                        }
                    }
                    $campaign->addActiveQuestions($activeQuestions);
                    
                    // Fetch advisor tree if it exists
                    $advisorTree = array();
                    
                    if (!empty($xmlCampaign->advisor->advisorTree)) {
                        foreach($xmlCampaign->advisor->advisorTree->question AS $xmlQuestion) {
                            $advisorTree[] = $this->loadAdvisorQuestion($xmlQuestion, true);
                        }
                    }
                    $campaign->addToAdvisorTree($advisorTree);
                }

                $campaigns[] = $campaign;
            }
        }
        $campaignIterator = FF::getInstance('campaignIterator', $campaigns);
        return $campaignIterator;
    }
    
    protected function loadAdvisorQuestion($xmlQuestion, $recursive = false) {
        $encodingHandler = $this->getEncodingHandler();
        $paramsParser = $this->getParamsParser();
        
        $answers = array();
        
        // Fetch answers. Follow-up questions are ignored here.
        foreach($xmlQuestion->answer AS $xmlAnswer) {
            $text = $encodingHandler->encodeServerContentForPage((string)$xmlAnswer->text);
            $params = $paramsParser->createPageLink($paramsParser->parseParamsFromResultString($xmlAnswer->params));
            $subquestions = array();
            if ($recursive) {
                foreach($xmlAnswer->question AS $xmlSubquestion) {
                    $subquestions[] = $this->loadAdvisorQuestion($xmlSubquestion, true);
                }
            }
            $answer = FF::getInstance('advisorAnswer', $text, $params, $subquestions);
            $answers[] = $answer;
        }
        
        return FF::getInstance('advisorQuestion', $encodingHandler->encodeServerContentForPage((string)$xmlQuestion->text), $answers);
    }
    
}
    