<?php
namespace FACTFinder\Core\Server;

/**
 * Null Object implementation of the Response class, e.g. to be used if
 * no response could be obtained.
 */
class NullResponse extends Response
{
    public function __construct() {}

    public function getContent() { return ''; }
    public function getHttpCode() { return 0; }
    public function getConnectionErrorNumber() { return 0; }
    public function getConnectionError() { return ''; }
}
