<?php
namespace FACTFinder\Util;

/**
 * Struct-like class that represents all data associated with one curl multi
 * handle.
 */
class CurlMultiHandle
{
    /**
     * @var int[] List-like array of easy handles that have been added to this
     *            multi handle. Note that we don't add CurlHandle objects but
     *            only integers, which are the $ch the CurlStub hands out in
     *            place of resources.
     */
    public $handles = array();

    /**
     * @var mixed[] Map-like array of options that are set on the multi handle
     *              itself. Option-identifying integers as keys, mixed-type
     *              option values as values.
     * This feature is not yet supported, since PHP's cURL implementation did
     * not add curl_multi_setopt until PHP 5.5.0.
     */
    public $options = array();

    /**
     * @var array[] Queue-like array of messages to be returned by
     *              multi_info_read.
     */
    public $messageQueue = array();

    /**
     * @var bool
     * This is only used to mimick cURL's behavior prior to version 7.20.0. In
     * these versions, cURL could return CURLM_CALL_MULTI_PERFORM from a
     * multi_exec call, indicating that multi_exec should be called again right
     * away. For these cURL versions, the stub simply returns that code once
     * and then signals CURLM_OK. This flag is used to track which state we are
     * in.
     */
    public $performReturned = false;
}
