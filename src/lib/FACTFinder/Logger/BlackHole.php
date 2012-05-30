<?php

class FACTFinder_Logger_BlackHole implements FACTFinder_Logger_LoggerInterface
{
    public function error($error) {}
    public function info($message) {}
}