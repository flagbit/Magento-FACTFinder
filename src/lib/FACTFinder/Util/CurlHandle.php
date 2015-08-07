<?php
namespace FACTFinder\Util;

/**
 * Struct-like class that represents all data associated with one curl handle
 */
class CurlHandle
{
    /**
     * @var mixed[] map-like array of options that are set on the easy handle.
     *              Option-identifying integers as keys, mixed-type option
     *              values as values.
     */
    public $options = array();

    /**
     * @var bool
     * Indicates if this easy handle is currently being used in a multi handle
     * (@see CurlMultiHandle).
     */
    public $inMultiHandle = false;

    /**
     * @var int
     * If this handle is used in a multi handle (@see CurlMultiHandle), this
     * indicates how many more times multi_select and multi_exec has to be
     * called until this easy handle returns its response. This value will be
     * set by the CurlStub when the handle is added to a multi handle. -1 is
     * used while this easy handle is not used in a multi handle or when this
     * handle has already returned its response while part of a multi handle.
     */
    public $durationLeft = -1;
}
