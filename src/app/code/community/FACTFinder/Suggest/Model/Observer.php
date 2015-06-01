<?php

class FACTFinder_Suggest_Model_Observer
{


    /**
     * Add suggest handle to the layout
     *
     * @param $observer
     */
    public function addSuggestHandles($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled('suggest')) {
            return;
        }

        $layout = $observer->getLayout();
        $update = $layout->getUpdate();
        $update->addHandle('factfinder_suggest_enabled');
    }


}
