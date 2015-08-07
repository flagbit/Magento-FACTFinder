<?php
namespace FACTFinder\Util;

/**
 * Implements LoggerInterface by doing nothing (this will be used if no logger
 * is created or no logging is wanted).
 */
class NullLogger implements LoggerInterface
{
    public static function getLogger($name)
    {
        return new NullLogger();
    }

    public function trace($message) {}
    public function debug($message) {}
    public function info($message)  {}
    public function warn($message)  {}
    public function error($message) {}
    public function fatal($message) {}
}
