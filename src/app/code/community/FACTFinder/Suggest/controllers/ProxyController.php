<?php
class FACTFinder_Suggest_ProxyController extends Mage_Core_Controller_Front_Action
{
    /**
     * suggest Action
     */
    public function suggestAction()
    {
        $this->getResponse()->setHeader("Content-Type:", "text/javascript;charset=utf-8", true);
        $this->getResponse()->setBody(
            Mage::getModel('factfinder_suggest/processor')->handleInAppRequest($this->getFullActionName())
        );
    }

}

// jXHR.cb0({"suggestions":[{"attributes":{},"hitCount":42,"image":"","name":"with","priority":166,"searchParams":"/flagbit6.11/Search.ff?query=with\u0026channel=de","type":"searchTerm"},{"attributes":{"id":"446","masterId":"446"},"hitCount":0,"image":"","name":"MP3 Player with Audio","priority":10000,"searchParams":"/flagbit6.11/Search.ff?query=MP3+Player+with+Audio\u0026channel=de","type":"productName"},{"attributes":{"id":"378","masterId":"378"},"hitCount":0,"image":"","name":"Body Wash with Lemon Flower Extract and Aloe Vera","priority":10000,"searchParams":"/flagbit6.11/Search.ff?query=Body+Wash+with+Lemon+Flower+Extract+and+Aloe+Vera\u0026channel=de","type":"productName"}]});
// jXHR.cb0({"suggestions":[{"attributes":{"id":"559","masterId":"559"},"hitCount":0,"image":"","searchParams":"\/index.php\/default\/ff_suggest\/proxy\/suggest\/?q=If%20You%20Were%20by%20Keshco","type":"productName"}]});