<?php
/**
 * Stubs the cURL interface without issuing any PHP requests.
 **/

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'CurlInterface.php';
require_once 'Curl/Handle.php';

class SAI_CurlStub
    implements SAI_CurlInterface
{
    private $_lastHandle = -1;

    private $_mapOptionCounts = array();
    private $_mapOptions = array();
    private $_mapResponses = array();
    private $_mapErrorCodes = array();
    private $_mapInfo = array();

    /**
     * @var array of SAI_Curl_Handle
     */
    private $_handles = array();

    public function curl_close($ch)
    {
        unset($this->_handles[$ch]);
    }

    public function curl_copy_handle($ch)
    {
        $newCh = ++$this->_lastHandle;

        $handle = clone $this->_handles[$ch];

        $this->_handles[$newCh] = $handle;

        return $newCh;
    }

    public function curl_errno($ch)
    {
        if(!isset($this->_handles[$ch]))
            return 0;

        return $this->_getErrorCode($this->_handles[$ch]);
    }

    public function curl_error($ch)
    {
        $errno = $this->curl_errno($ch);
        return SAI_CurlStub::$errorLookup[$errno];
    }

    public function curl_exec($ch)
    {
        if(!isset($this->_handles[$ch]))
            return false;

        /**
         * @var $handle SAI_Curl_Handle
         */
        $handle = $this->_handles[$ch];

        $response = $this->_getResponse($handle);

        if($response === null)
            return false;

        if($handle->options[CURLOPT_RETURNTRANSFER])
            return $response;

        echo $response;
        return true;
    }

    public function curl_getinfo($ch, $opt = 0)
    {
        // TODO: Include logic to build CURLINFO_EFFECTIVE_URL?
        if(!isset($this->_handles[$ch]))
            trigger_error(__FUNCTION__.'(): '.$ch.' is not a valid cURL handle resource', E_USER_WARNING);

        if($opt == 0)
            return $this->_getInfoArray($this->_handles[$ch]);

        return $this->_getInfo($this->_handles[$ch], $opt);
    }

    public function curl_init($url = null)
    {
        $ch = ++$this->_lastHandle;

        $handle = new SAI_Curl_Handle();

        $handle->options = array(
            CURLOPT_RETURNTRANSFER => false
        );

        $this->_handles[$ch] = $handle;

        if($url !== null)
            $this->curl_setopt($ch, CURLOPT_URL, $url);

        return $ch;
    }

    public function curl_multi_add_handle($mh, $ch)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_close($mh)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_exec($mh, &$still_running)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_getcontent($ch)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_info_read($mh, &$msgs_in_queue = null)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_init()
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_remove_handle($mh, $ch)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_multi_select($mh, $timeout = 1.0)
    {
        throw new Exception("Not yet implemented.");
    }

    public function curl_setopt_array($ch, $options)
    {
        foreach($options as $option => $value)
        {
            if(!$this->curl_setopt($ch, $option, $value))
                return false;
        }
        return true;
    }

    public function curl_setopt($ch, $option, $value)
    {
        if(!isset($this->_handles[$ch]))
            return false;

        $this->_handles[$ch]->options[$option] = $value;

        return true;
    }

    public function curl_version($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }

    public function setResponse($expectedResponse, $requiredOptions = array())
    {
        $hash = $this->_getHashAndSetOptionMaps($requiredOptions);
        $this->_mapResponses[$hash] = $expectedResponse;
    }

    public function setErrorCode($expectedErrorCode, $requiredOptions = array())
    {
        $hash = $this->_getHashAndSetOptionMaps($requiredOptions);
        $this->_mapErrorCodes[$hash] = $expectedErrorCode;
    }

    public function setInfo($expectedInfo, $requiredOptions = array())
    {
        $hash = $this->_getHashAndSetOptionMaps($requiredOptions);
        $this->_mapInfo[$hash] = $expectedInfo;
    }

    private function _getHashAndSetOptionMaps($requiredOptions)
    {
        $hash = md5(http_build_query($requiredOptions));

        $this->_mapOptionCounts[$hash] = count($requiredOptions);
        arsort($this->_mapOptionCounts);
        $this->_mapOptions[$hash] = $requiredOptions;
        return $hash;
    }

    private function _getResponse($handle)
    {
        $response = false;

        $key = $this->_determineKey($handle);

        if($key !== null && isset($this->_mapResponses[$key]))
            $response = $this->_mapResponses[$key];

        return $response;
    }

    private function _getErrorCode($handle)
    {
        $errorCode = 0;

        $key = $this->_determineKey($handle);

        if($key !== null && isset($this->_mapErrorCodes[$key]))
            $errorCode = $this->_mapErrorCodes[$key];

        return $errorCode;
    }

    private function _getInfo($handle, $opt)
    {
        $info = '';

        $key = $this->_determineKey($handle);

        if($key !== null && isset($this->_mapInfo[$key][$opt]))
            $info = $this->_mapInfo[$key][$opt];

        return $info;
    }

    private function _getInfoArray($handle)
    {
        $infoArray = array();

        foreach(self::$infoLookup as $strKey)
        {
            $infoArray[$strKey] = '';
        }

        $handleKey = $this->_determineKey($handle);

        if($handleKey !== null && isset($this->_mapInfo[$handleKey]))
        {
            foreach($this->_mapInfo[$handleKey] as $intKey => $value)
            {
                $strKey = self::$infoLookup[$intKey];
                $infoArray[$strKey] = $value;
            }
        }

        return $infoArray;
    }

    private function _determineKey($handle)
    {
        // TODO: Treat URL separately, so that order of parameters does not matter
        $returnValue = null;
        $options = $handle->options;
        foreach($this->_mapOptionCounts as $hash => $optionCount)
        {
            foreach($this->_mapOptions[$hash] as $option => $value)
            {
                if(!isset($options[$option]) || $options[$option] != $value)
                    continue 2;
            }

            $returnValue = $hash;

            break;
        }
        return $returnValue;
    }

    private static $errorLookup = array(
        0  => 'CURLE_OK',
        1  => 'CURLE_UNSUPPORTED_PROTOCOL',
        2  => 'CURLE_FAILED_INIT',
        3  => 'CURLE_URL_MALFORMAT',
        4  => 'CURLE_URL_MALFORMAT_USER',
        5  => 'CURLE_COULDNT_RESOLVE_PROXY',
        6  => 'CURLE_COULDNT_RESOLVE_HOST',
        7  => 'CURLE_COULDNT_CONNECT',
        8  => 'CURLE_FTP_WEIRD_SERVER_REPLY',
        9  => 'CURLE_REMOTE_ACCESS_DENIED',
        11 => 'CURLE_FTP_WEIRD_PASS_REPLY',
        13 => 'CURLE_FTP_WEIRD_PASV_REPLY',
        14 => 'CURLE_FTP_WEIRD_227_FORMAT',
        15 => 'CURLE_FTP_CANT_GET_HOST',
        17 => 'CURLE_FTP_COULDNT_SET_TYPE',
        18 => 'CURLE_PARTIAL_FILE',
        19 => 'CURLE_FTP_COULDNT_RETR_FILE',
        21 => 'CURLE_QUOTE_ERROR',
        22 => 'CURLE_HTTP_RETURNED_ERROR',
        23 => 'CURLE_WRITE_ERROR',
        25 => 'CURLE_UPLOAD_FAILED',
        26 => 'CURLE_READ_ERROR',
        27 => 'CURLE_OUT_OF_MEMORY',
        28 => 'CURLE_OPERATION_TIMEOUTED', // in the original cURL lib this is called 'CURLE_OPERATION_TIMEOUTED' instead
        30 => 'CURLE_FTP_PORT_FAILED',
        31 => 'CURLE_FTP_COULDNT_USE_REST',
        33 => 'CURLE_RANGE_ERROR',
        34 => 'CURLE_HTTP_POST_ERROR',
        35 => 'CURLE_SSL_CONNECT_ERROR',
        36 => 'CURLE_BAD_DOWNLOAD_RESUME',
        37 => 'CURLE_FILE_COULDNT_READ_FILE',
        38 => 'CURLE_LDAP_CANNOT_BIND',
        39 => 'CURLE_LDAP_SEARCH_FAILED',
        41 => 'CURLE_FUNCTION_NOT_FOUND',
        42 => 'CURLE_ABORTED_BY_CALLBACK',
        43 => 'CURLE_BAD_FUNCTION_ARGUMENT',
        45 => 'CURLE_INTERFACE_FAILED',
        47 => 'CURLE_TOO_MANY_REDIRECTS',
        48 => 'CURLE_UNKNOWN_TELNET_OPTION',
        49 => 'CURLE_TELNET_OPTION_SYNTAX',
        51 => 'CURLE_PEER_FAILED_VERIFICATION',
        52 => 'CURLE_GOT_NOTHING',
        53 => 'CURLE_SSL_ENGINE_NOTFOUND',
        54 => 'CURLE_SSL_ENGINE_SETFAILED',
        55 => 'CURLE_SEND_ERROR',
        56 => 'CURLE_RECV_ERROR',
        58 => 'CURLE_SSL_CERTPROBLEM',
        59 => 'CURLE_SSL_CIPHER',
        60 => 'CURLE_SSL_CACERT',
        61 => 'CURLE_BAD_CONTENT_ENCODING',
        62 => 'CURLE_LDAP_INVALID_URL',
        63 => 'CURLE_FILESIZE_EXCEEDED',
        64 => 'CURLE_USE_SSL_FAILED',
        65 => 'CURLE_SEND_FAIL_REWIND',
        66 => 'CURLE_SSL_ENGINE_INITFAILED',
        67 => 'CURLE_LOGIN_DENIED',
        68 => 'CURLE_TFTP_NOTFOUND',
        69 => 'CURLE_TFTP_PERM',
        70 => 'CURLE_REMOTE_DISK_FULL',
        71 => 'CURLE_TFTP_ILLEGAL',
        72 => 'CURLE_TFTP_UNKNOWNID',
        73 => 'CURLE_REMOTE_FILE_EXISTS',
        74 => 'CURLE_TFTP_NOSUCHUSER',
        75 => 'CURLE_CONV_FAILED',
        76 => 'CURLE_CONV_REQD',
        77 => 'CURLE_SSL_CACERT_BADFILE',
        78 => 'CURLE_REMOTE_FILE_NOT_FOUND',
        79 => 'CURLE_SSH',
        80 => 'CURLE_SSL_SHUTDOWN_FAILED',
        81 => 'CURLE_AGAIN',
        82 => 'CURLE_SSL_CRL_BADFILE',
        83 => 'CURLE_SSL_ISSUER_ERROR',
        84 => 'CURL E_FTP_PRET_FAILED',
        85 => 'CURLE_RTSP_CSEQ_ERROR',
        86 => 'CURLE_RTSP_SESSION_ERROR',
        87 => 'CURLE_FTP_BAD_FILE_LIST',
        88 => 'CURLE_CHUNK_FAILED'
    );

    public static $infoLookup = array(
        CURLINFO_EFFECTIVE_URL => 'url',
        CURLINFO_HTTP_CODE => 'http_code',
        CURLINFO_FILETIME => 'filetime',
        CURLINFO_TOTAL_TIME => 'total_time',
        CURLINFO_NAMELOOKUP_TIME => 'namelookup_time',
        CURLINFO_CONNECT_TIME => 'connect_time',
        CURLINFO_PRETRANSFER_TIME => 'pretransfer_time',
        CURLINFO_STARTTRANSFER_TIME => 'starttransfer_time',
        CURLINFO_REDIRECT_COUNT => 'redirect_count',
        CURLINFO_REDIRECT_TIME => 'redirect_time',
        CURLINFO_SIZE_UPLOAD => 'size_upload',
        CURLINFO_SIZE_DOWNLOAD => 'size_download',
        CURLINFO_SPEED_DOWNLOAD => 'speed_download',
        CURLINFO_SPEED_UPLOAD => 'speed_upload',
        CURLINFO_HEADER_SIZE => 'header_size',
        CURLINFO_HEADER_OUT => 'request_header',
        CURLINFO_REQUEST_SIZE => 'request_size',
        CURLINFO_SSL_VERIFYRESULT => 'ssl_verify_result',
        CURLINFO_CONTENT_LENGTH_DOWNLOAD => 'download_content_length',
        CURLINFO_CONTENT_LENGTH_UPLOAD => 'upload_content_length',
        CURLINFO_CONTENT_TYPE => 'content_type'
    );
}