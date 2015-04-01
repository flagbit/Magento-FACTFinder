<?php
namespace FACTFinder\Util;

interface LoggerInterface
{
    public static function getLogger($name);
    public function trace($message);
    public function debug($message);
    public function info($message);
    public function warn($message);
    public function error($message);
    public function fatal($message);
}
