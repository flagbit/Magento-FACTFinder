/**
 * FACTFinder_Filters
 *
 * @category Mage
 * @package FACTFinder_Filters
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2018 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */
jQuery(document).ready(function () {
    jQuery('ol.filters-items li.toggle-refinements a').click(function (event) {
        event.stopPropagation();
        event.preventDefault();
        var $this = $(this);
        jQuery($this).parent().toggle();
        jQuery($this).parent().siblings('.toggle-refinements').toggle();
        jQuery($this).parent().siblings('.toggle-item-hidden').slideToggle('fast');
        return false;
    });
});
