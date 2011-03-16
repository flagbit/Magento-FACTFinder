<?php
/**
 * this class handles the different issues of encoding between the values of page-url, server-url, result and pagecontent
 *
 * @package FACTFinder\Common
 */
class FACTFinder_EncodingHandler
{
    private $convertMethod;
    
    private $pageContentEncoding;
    private $pageUrlEncoding;
    private $serverUrlEncoding;
    
    public function __construct(FACTFinder_Abstract_Configuration $config)
    {	
        $this->pageContentEncoding = $config->getPageContentEncoding();
        $this->pageUrlEncoding     = $config->getPageUrlEncoding();
        $this->serverUrlEncoding   = $config->getServerUrlEncoding();

        if (function_exists('iconv')) {
            $this->convertMethod = 'iConvert';
        } else {
            $this->convertMethod = 'utf8Convert';
        }
    }
    
    private function iConvert($inCharset, $outCharset, $string)
    {
        return ($inCharset == $outCharset || empty($inCharset) || empty($outCharset)) ? $string : iconv($inCharset, $outCharset, $string);
    }
    
    private function utf8Convert($inCharset, $outCharset, $string)
    {
        if (strtolower($inCharset) != strtolower($outCharset) && !empty($inCharset) && !empty($outCharset)) {
			if (strtolower($inCharset) == 'utf-8') {
				$string = utf8_decode($string);
			} else if (strtolower($outCharset) == 'utf-8') {
				$string = utf8_encode($string);
			} else {
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
                    $key = $this->{$this->convertMethod}($this->pageUrlEncoding, $this->pageContentEncoding, $key);
                    $returnData[$key] = $this->{$this->convertMethod}($this->pageUrlEncoding, $this->pageContentEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->{$this->convertMethod}($this->pageUrlEncoding, $this->pageContentEncoding, $data);
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
                    $key = $this->{$this->convertMethod}($this->serverUrlEncoding, $this->pageUrlEncoding, $key);
                    $returnData[$key] = $this->{$this->convertMethod}($this->serverUrlEncoding, $this->pageUrlEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->{$this->convertMethod}($this->serverUrlEncoding, $this->pageUrlEncoding, $data);
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
                    $key = $this->{$this->convertMethod}('UTF-8', $this->pageContentEncoding, $key);
                    $returnData[$key] = $this->{$this->convertMethod}('UTF-8', $this->pageContentEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->{$this->convertMethod}('UTF-8', $this->pageContentEncoding, $data);
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
                    $key = $this->{$this->convertMethod}($this->pageContentEncoding, $this->serverUrlEncoding, $key);
                    $returnData[$key] = $this->{$this->convertMethod}($this->pageContentEncoding, $this->serverUrlEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->{$this->convertMethod}($this->pageContentEncoding, $this->serverUrlEncoding, $data);
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
                    $key = $this->{$this->convertMethod}($this->pageContentEncoding, $this->pageUrlEncoding, $key);
                    $returnData[$key] = $this->{$this->convertMethod}($this->pageContentEncoding, $this->pageUrlEncoding, $value);
                }
            } else if (is_string($data)) {
                $returnData = $this->{$this->convertMethod}($this->pageContentEncoding, $this->pageUrlEncoding, $data);
            }
        } else {
            $returnData = $data;
        }
        return $returnData;
    }
}