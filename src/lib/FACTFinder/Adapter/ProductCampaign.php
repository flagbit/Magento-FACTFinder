<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class ProductCampaign extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Data\Result
     */
    private $campaigns;

    /**
     * @var bool
     */
    protected $isShoppingCartCampaign = false;
    private $campaignsUpToDate = false;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder, $encodingConverter);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('ProductCampaign.ff');
        $this->parameters['do'] = 'getProductCampaigns';
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }

    /**
     * Set one or multiple product numbers to get campaigns for, overwriting any
     * numbers previously set. Note that multiple numbers will only be considered for
     * shopping cart campaigns. For product detail campaigns only the first number
     * will be used.
     *
     * @param string|string[] $productNumbers One or more product numbers.
     */
    public function setProductNumbers($productNumbers)
    {
        $parameters = $this->request->getParameters();
        $parameters['productNumber'] = $productNumbers;
        $this->campaignsUpToDate = false;
    }

    /**
     * For product campaigns, FACT-Finder needs the product numbers - not the IDs.
     *
     * @deprecated use setProductNumbers instead
     * @param string|string[] $productIDs
     */
    public function setProductIDs($productIDs)
    {
        // preserve the previous logic
        $this->setProductNumbers($productIDs);
    }

    /**
     * Add one or multiple product numbser to get campaigns for, in addition to any
     * numbers previously set.
     *
     * @param string|string[] $productNumbers One or more product numbers.
     */
    public function addProductNumbers($productNumbers)
    {
        $parameters = $this->request->getParameters();
        $parameters->add('productNumber', $productNumbers);
        $this->campaignsUpToDate = false;
    }

    /**
     * For product campaigns, FACT-Finder needs the product numbers - not the IDs.
     *
     * @deprecated use addProductNumbers instead
     * @param string|string[] $productIDs
     */
    public function addProductIDs($productIDs)
    {
        $this->addProductNumbers($productIDs);
    }

    /**
     * Sets the adapter up for fetching campaigns on product detail pages
     */
    public function makeProductCampaign()
    {
        $this->isShoppingCartCampaign = false;
        $this->campaignsUpToDate = false;
        $this->parameters['do'] = 'getProductCampaigns';
    }

    /**
     * Sets the adapter up for fetching campaigns on shopping cart pages
     */
    public function makeShoppingCartCampaign()
    {
        $this->isShoppingCartCampaign = true;
        $this->campaignsUpToDate = false;
        $this->parameters['do'] = 'getShoppingCartCampaigns';
    }

    /**
     * Returns campaigns for IDs previously specified. If no IDs have been
     * set, there will be a warning raised and an empty result will be returned.
     *
     * @return \FACTFinder\Data\Result
     */
    public function getCampaigns()
    {
        if (is_null($this->campaigns)
            || !$this->campaignsUpToDate
        ) {
            $this->campaigns = $this->createCampaigns();
            $this->campaignsUpToDate = true;
        }

        return $this->campaigns;
    }

    private function createCampaigns()
    {
        $campaigns = array();

        if (!isset($this->parameters['productNumber']))
        {
            $this->log->warn('Product campaigns cannot be loaded without a product ID. '
                           . 'Use setProductIDs() or addProductIDs() first.');
        }
        else
        {
            if ($this->isShoppingCartCampaign)
            {
                $jsonData = $this->getResponseContent();
            }
            else
            {
                // Use only the first product ID
                $productIDs = $this->parameters['productNumber'];
                if (is_array($productIDs))
                    $this->parameters['productNumber'] = $productIDs[0];

                $jsonData = $this->getResponseContent();

                // Restore IDs
                $this->parameters['productNumber'] = $productIDs;
            }

            foreach ($jsonData as $campaignData) {
                $campaign = $this->createEmptyCampaignObject($campaignData);

                $this->fillCampaignWithFeedback($campaign, $campaignData);
                $this->fillCampaignWithPushedProducts($campaign, $campaignData);

                $campaigns[] = $campaign;
            }
        }

        $campaignIterator = FF::getInstance(
            'Data\CampaignIterator',
            $campaigns
        );
        return $campaignIterator;
    }

    /**
     * @param mixed[] $campaignData An associative array corresponding to the
     *        JSON for a single campaign.
     * @return \FACTFinder\Data\Campaign
     */
    private function createEmptyCampaignObject(array $campaignData)
    {
        return FF::getInstance(
            'Data\Campaign',
            $campaignData['name'],
            $campaignData['category'],
            $campaignData['target']['destination']
        );
    }

    /**
     * @param \FACTFinder\Data\Campaign $campaign The campaign object to be
     *        filled.
     * @param mixed[] $campaignData An associative array corresponding to the
     *        JSON for that campaign.
     */
    private function fillCampaignWithFeedback(
        \FACTFinder\Data\Campaign $campaign,
        array $campaignData
    ) {
        if (!empty($campaignData['feedbackTexts']))
        {
            $feedback = array();

            foreach ($campaignData['feedbackTexts'] as $feedbackData)
            {
                // If present, add the feedback to both the label and the ID.
                $label = $feedbackData['label'];
                if ($label !== '')
                    $feedback[$label] = $feedbackData['text'];

                $id = $feedbackData['id'];
                if ($id !== null)
                    $feedback[$id] = $feedbackData['text'];
            }

            $campaign->addFeedback($feedback);
        }
    }

    /**
     * @param \FACTFinder\Data\Campaign $campaign The campaign object to be
     *        filled.
     * @param mixed[] $campaignData An associative array corresponding to the
     *        JSON for that campaign.
     */
    private function fillCampaignWithPushedProducts(
        \FACTFinder\Data\Campaign $campaign,
        array $campaignData
    ) {
        if (!empty($campaignData['pushedProductsRecords']))
        {
            $pushedProducts = array();

            foreach ($campaignData['pushedProductsRecords'] as $recordData)
            {
                $pushedProducts[] = FF::getInstance(
                    'Data\Record',
                    (string)$recordData['id'],
                    $recordData['record']
                );
            }

            $campaign->addPushedProducts($pushedProducts);
        }
    }
}
