<?php
namespace FACTFinder\Core;

/**
 * Implements the AbstractEncodingConverter using utf8_encode() and
 * utf_decode().
 */
class Utf8EncodingConverter extends AbstractEncodingConverter
{
    function __construct(
        $loggerClass,
        ConfigurationInterface $configuration
    ) {
        parent::__construct($loggerClass, $configuration);
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    protected function convertString($inCharset, $outCharset, $string)
    {
        if (strtolower($inCharset) != strtolower($outCharset)
            && !empty($inCharset)
            && !empty($outCharset)
        ) {
            if (strtolower($inCharset) == 'utf-8')
            {
                if (strtolower($outCharset) != 'iso-8859-1')
                    $this->log->warn(
                        "utf8_decode() does not support $outCharset. If $outCharset is not compatible with ISO-8859-1, "
                      . "the resulting string may contain wrong or invalid characters."
                    );
                $string = utf8_decode($string);
            }
            else if (strtolower($outCharset) == 'utf-8')
            {
                if (strtolower($inCharset) != 'iso-8859-1')
                    $this->log->warn(
                        "utf8_encode() does not support $inCharset. If $inCharset is not compatible with ISO-8859-1, "
                      . "the resulting string may contain wrong characters."
                    );
                $string = utf8_encode($string);
            }
            else
            {
                $this->log->error("Conversion between non-UTF-8 encodings not possible.");
                throw new \InvalidArgumentException("Cannot handle conversion from $inCharset to $outCharset!");
            }
        }
        return $string;
    }
}
