<?php
namespace FACTFinder\Core\Server;

/**
 * Use this to obtain Request objects instead of creating them directly.
 */
interface RequestFactoryInterface
{
    /**
     * Returns a request object all wired up and ready for use.
     * @return Request
     */
    public function getRequest();
}
