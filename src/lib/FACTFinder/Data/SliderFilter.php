<?php
namespace FACTFinder\Data;

/**
 * An After Search Navigation (ASN) filter element that is to be represented by
 * a slider widget.
 */
class SliderFilter extends Filter
{
    /**
     * @var float
     */
    private $absoluteMinimum;

    /**
     * @var float
     */
    private $absoluteMaximum;

    /**
     * @var float
     */
    private $selectedMinimum;

    /**
     * @var float
     */
    private $selectedMaximum;

    /**
     * @param string $baseUrl The URL for this item, WITHOUT the actual filter
     *        parameter. This will be appended programmatically.
     * @param string $fieldName
     * @param float $absoluteMinimum
     * @param float $absoluteMaximum
     * @param float $selectedMinimum
     * @param float $selectedMaximum
     */
    public function __construct(
        $baseUrl,
        $fieldName,
        $absoluteMinimum = 0,
        $absoluteMaximum = 0,
        $selectedMinimum = 0,
        $selectedMaximum = 0
    ) {
        $selected = $selectedMinimum != $absoluteMinimum
                    || $selectedMaximum != $absoluteMaximum;
        parent::__construct('', $baseUrl, $selected, $fieldName);

        $this->absoluteMinimum = $absoluteMinimum;
        $this->absoluteMaximum = $absoluteMaximum;
        $this->selectedMinimum = $selectedMinimum;
        $this->selectedMaximum = $selectedMaximum;
    }

    /**
     * Returns the URL with appended filter parameter but no value. Use this to
     * append the value ('min-max') in JavaScript.
     * @return string
     */
    public function getBaseUrl()
    {
        return parent::getUrl() . '&'
               . 'filter' . $this->getFieldName() . '=';
    }

    /**
     * Returns the full URL for the current configuration (including selected
     * minimum and maximum).
     * @return string
     */
    public function getUrl()
    {
        return $this->getBaseUrl() . $this->selectedMinimum
                             . '-' . $this->selectedMaximum;
    }

    /**
     * @return float
     */
    public function getAbsoluteMinimum()
    {
        return $this->absoluteMinimum;
    }

    /**
     * @return float
     */
    public function getAbsoluteMaximum()
    {
        return $this->absoluteMaximum;
    }

    /**
     * @return float
     */
    public function getSelectedMinimum()
    {
        return $this->selectedMinimum;
    }

    /**
     * @return float
     */
    public function getSelectedMaximum()
    {
        return $this->selectedMaximum;
    }
}
