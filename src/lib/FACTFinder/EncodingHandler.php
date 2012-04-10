<?php

/**
 * this class handles the different issues of encoding between the values of page-url, server-url, result and pagecontent
 *
 * @package FACTFinder\Common
 */
class FACTFinder_EncodingHandler
{
    protected $convertMethod;

    protected $pageContentEncoding;
    protected $pageUrlEncoding;
    protected $serverUrlEncoding;
    
    protected $log;

    public function __construct(FACTFinder_Abstract_Configuration $config)
    {
        $this->log = FF::getLogger();
        
        $this->pageContentEncoding = $config->getPageContentEncoding();
        $this->pageUrlEncoding     = $config->getPageUrlEncoding();
        $this->serverUrlEncoding   = $config->getServerUrlEncoding();

        if (function_exists('iconv')) {
            $this->convertMethod = 'iConvert';
        } else {
            $this->convertMethod = 'utf8Convert';
        }
    }

    /**
     * converts the string from "inCharset" encoding into "outCharset" encoding. if the running php have no iconv support,
     * the utf8_encode/decode are used, so only the encodings "utf-8" and "iso-8859-?" can be used
     *
     * @param input charset
     * @param output charset
     * @param string which should be converted
     * @return string in specified output charset
     * @throws exception for not supported charset
     */
    public function convert($inCharset, $outCharset, $string)
    {
        return $this->{$this->convertMethod}($inCharset, $outCharset, $string);
    }

    /**
     * uses iconvert to convert string
     *
     * @link http://bg.php.net/manual/en/book.iconv.php
     * @param input charset
     * @param output charset
     * @param string which should be converted
     * @return string in specified output charset
     */
    protected function iConvert($inCharset, $outCharset, $string)
    {
        // see http://de2.php.net/manual/de/function.iconv.php to understand '//IGNORE'
        return ($inCharset == $outCharset || empty($inCharset) || empty($outCharset)) ? $string : iconv($inCharset, $outCharset.'//IGNORE', $string);
    }

    /**
     * uses utf8-convert functions to convert string
     *
     * @param input charset
     * @param output charset
     * @param string which should be converted
     * @return string in specified output charset
     * @throws exception for not supported charset
     */
    protected function utf8Convert($inCharset, $outCharset, $string)
    {
        if (strtolower($inCharset) != strtolower($outCharset) && !empty($inCharset) && !empty($outCharset)) {
            if (strtolower($inCharset) == 'utf-8') {
                $string = utf8_decode($string);
            } else if (strtolower($outCharset) == 'utf-8') {
                $string = utf8_encode($string);
            } else {
                $this->log->error("Could not convert between non-UTF-8 charsets.");
                throw new Exception("can not handle $inCharset to $outCharset conversion!");
            }
        }
        return $string;
    }

    /**
     * converts the url data, to display correctly at the page
     *
     * @param string|array data
     * @return string|array converted data
     */
    public function encodeUrlForPage($data)
    {
        // notice: urldecode is not needed, because php decodes the url automatically
        if ($this->pageUrlEncoding != $this->pageContentEncoding) {
            if (is_array($data)) {
                $returnData = array();
                foreach ($data AS $key => $value) {
                    $key = $this->convert($this->pageUrlEncoding, $this->pageContentEncoding, $key);
                    $returnData[$key] = $this->convert($this->pageUrlEncoding, $this->pageContentEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->convert($this->pageUrlEncoding, $this->pageContentEncoding, $data);
            }
        } else {
            $returnData = $data;
        }
        return $returnData;
    }

    /**
     * converts the url data from the server result for the page url
     *
     * @param string|array data
     * @return string|array converted data
     */
    public function encodeServerUrlForPageUrl($data)
    {
        // notice: urldecode is not needed, because the parameters parser is already doing that
        if ($this->serverUrlEncoding != $this->pageUrlEncoding) {
            if (is_array($data)) {
                $returnData = array();
                foreach ($data AS $key => $value) {
                    $key = $this->convert($this->serverUrlEncoding, $this->pageUrlEncoding, $key);
                    $returnData[$key] = $this->convert($this->serverUrlEncoding, $this->pageUrlEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->convert($this->serverUrlEncoding, $this->pageUrlEncoding, $data);
            }
        } else {
            $returnData = $data;
        }
        return $returnData;
    }

    /**
     * converts the data from the server result for the page content
     *
     * @param string|array data
     * @return string|array converted data
     */
    public function encodeServerContentForPage($data)
    {
        // server result data is always utf-8
        if (strtolower($this->pageContentEncoding) != 'utf-8') {
            if (is_array($data)) {
                $returnData = array();
                foreach ($data AS $key => $value) {
                    $key = $this->convert('UTF-8', $this->pageContentEncoding, $key);
                    $returnData[$key] = $this->convert('UTF-8', $this->pageContentEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->convert('UTF-8', $this->pageContentEncoding, $data);
            }
        } else {
            $returnData = $data;
        }
        return $returnData;
    }

    /**
     * converts the data for the server url
     *
     * @param string|array data
     * @return string|array converted data
     */
    public function encodeForServerUrl($data)
    {
        if ($this->pageContentEncoding != $this->serverUrlEncoding) {
            if (is_array($data)) {
                $returnData = array();
                foreach ($data AS $key => $value) {
                    $key = $this->convert($this->pageContentEncoding, $this->serverUrlEncoding, $key);
                    $returnData[$key] = $this->convert($this->pageContentEncoding, $this->serverUrlEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->convert($this->pageContentEncoding, $this->serverUrlEncoding, $data);
            }
        } else {
            $returnData = $data;
        }
        return $returnData;
    }

    /**
     * converts the data from the page for the page url
     *
     * @param string|array data
     * @return string|array converted data
     */
    public function encodeForPageUrl($data)
    {
        if ($this->pageContentEncoding != $this->pageUrlEncoding) {
            if (is_array($data)) {
                $returnData = array();
                foreach ($data AS $key => $value) {
                    $key = $this->convert($this->pageContentEncoding, $this->pageUrlEncoding, $key);
                    $returnData[$key] = $this->convert($this->pageContentEncoding, $this->pageUrlEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->convert($this->pageContentEncoding, $this->pageUrlEncoding, $data);
            }
        } else {
            $returnData = $data;
        }
        return $returnData;
    }
}