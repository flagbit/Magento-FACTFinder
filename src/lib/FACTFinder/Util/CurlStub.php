<?php
namespace FACTFinder\Util;

use FACTFinder\Loader as FF;

/**
 * Stubs the cURL interface without issuing any network requests.
 */
class CurlStub implements CurlInterface
{
    // These are a replacement for the newer CURLM error codes that have been
    // added in cURL 7.15.4 and 7.32.1 until PHP adopts them.
    const M_BAD_SOCKET = 5;
    const M_UNKNOWN_OPTION = 6;
    const M_ADDED_ALREADY = 7;

    /**
     * @var int
     * Used to hand out unique indices for handles. This is used both for easy
     * and for multi handles.
     */
    private $lastHandle = -1;

    private $mapOptionCounts = array();
    private $mapOptions = array();
    private $mapResponses = array();
    private $mapErrorCodes = array();
    private $mapInfo = array();

    /**
     * @var CurlHandle[]
     */
    private $handles = array();

    /**
     * @var CurlMultiHandle[]
     */
    private $multiHandles = array();

    /**
     * Easy interface
     */

    public function init($url = null)
    {
        $ch = ++$this->lastHandle;

        $handle = FF::getInstance('Util\CurlHandle');

        $handle->options[CURLOPT_RETURNTRANSFER] = false;

        $this->handles[$ch] = $handle;

        if($url !== null)
            $this->setopt($ch, CURLOPT_URL, $url);

        return $ch;
    }

    public function copy_handle($ch)
    {
        $newCh = ++$this->lastHandle;

        $handle = clone $this->handles[$ch];

        $this->handles[$newCh] = $handle;

        return $newCh;
    }

    public function setopt($ch, $option, $value)
    {
        if(!isset($this->handles[$ch]))
            return false;

        $this->handles[$ch]->options[$option] = $value;

        return true;
    }

    public function setopt_array($ch, $options)
    {
        foreach($options as $option => $value)
        {
            if(!$this->setopt($ch, $option, $value))
                return false;
        }
        return true;
    }

    public function exec($ch)
    {
        if(!isset($this->handles[$ch]))
            return false;

        $handle = $this->handles[$ch];

        $response = $this->getResponse($handle);

        // TODO: Is this really what PHP would do?
        if(is_null($response))
            return false;

        // TODO: Use more of the behavior options like the callbacks
        // WRITEFUNCTION and READFUNCTION.
        if($handle->options[CURLOPT_RETURNTRANSFER])
            return $response;

        echo $response;
        return true;
    }

    public function errno($ch)
    {
        if(!isset($this->handles[$ch]))
            return 0;

        return $this->getErrorCode($this->handles[$ch]);
    }

    public function error($ch)
    {
        $errno = $this->errno($ch);
        return self::$errorLookup[$errno];
    }

    public function getinfo($ch, $opt = 0)
    {
        // TODO: Include logic to build CURLINFO_EFFECTIVE_URL?
        if(!isset($this->handles[$ch]))
            trigger_error(__FUNCTION__.'(): '.$ch.' is not a valid cURL handle resource', E_USER_WARNING);

        if($opt == 0)
            return $this->getInfoArray($this->handles[$ch]);

        return $this->getInformation($this->handles[$ch], $opt);
    }

    public function close($ch)
    {
        // Somehow PHP's cURL implementation ignores the first two calls to
        // curl_close after the handle has been added to a multi handle. In fact
        // it even seems to ignore them once the handle has been removed from
        // the multi handle again. However, if the multi handle is closed
        // without first removing the easy handle, curl_close works again
        // immediately.
        // We don't mimick this behavior exactly. Instead, we simply ignore
        // calls to curl_close while the handle is added to a multi handle and
        // resume listening to them, once the handle is removed or the multi
        // handle is closed (which also removes the handle).
        if (isset($this->handles[$ch])
            && $this->handles[$ch]->inMultiHandle
        ) {
            return;
        }

        unset($this->handles[$ch]);
    }

    /**
     * Multi interface
     */

    public function multi_init()
    {
        $mh = ++$this->lastHandle;

        $handle = FF::getInstance('Util\CurlMultiHandle');

        $this->multiHandles[$mh] = $handle;

        return $mh;
    }

    public function multi_add_handle($mh, $ch)
    {
        if (!isset($this->multiHandles[$mh]))
            return CURLM_BAD_HANDLE;

        if (!isset($this->handles[$ch]))
            return CURLM_BAD_EASY_HANDLE;

        if ($this->handles[$ch]->inMultiHandle)
            return $this->versionNewerThan(7,32,1)
                   ? self::M_ADDED_ALREADY
                   : CURLM_BAD_EASY_HANDLE;

        $this->multiHandles[$mh]->handles[] = $ch;
        // TODO: Figure out a reasonable way to determine these random
        // durations, so that even for a large number of easy handles it should
        // still possible for them to return in any order. This will probably
        // depend on the number of existing easy handles.
        $this->handles[$ch]->durationLeft = rand(0,5);
        $this->handles[$ch]->inMultiHandle = true;

        return 0;
    }

    public function multi_select($mh, $timeout = 1.0)
    {
        $active = 0;
        foreach ($this->multiHandles[$mh]->handles as $ch)
        {
            $handle = $this->handles[$ch];

            if ($handle->durationLeft > 0)
                --$handle->durationLeft;
            else if ($handle->durationLeft == 0)
                ++$active;
        }

        // TODO: This never returns -1. However, some version of cURL itself
        // are able to return -1 indefinitely, unless you just let your thread
        // sleep some time and then call multi_exec right away. Should this
        // (somewhat buggy) behavior be mimicked to ensure that tested
        // applications are able to deal with these problems?
        // TODO: We are also ignoring the timeout here. Can that cause problems
        // for tested applications? If not, ignoring the timeout seems perfectly
        // reasonable as it cuts down on test run time.
        return $active;
    }

    public function multi_exec($mh, &$still_running)
    {
        // PHP emits a warning if $mh is not a valid handle. Should we mimick
        // this behavior?
        if (!isset($this->multiHandles[$mh]))
            return false;

        $still_running = 0;
        $mhandle = $this->multiHandles[$mh];

        // Mimick cURL's odd CURLM_CALL_MULTI_PERFORM behavior prior to version
        // 7.20.0 by simply returning that signal on every second call to
        // multi_exec. Note that we still have to count the number of active
        // easy handles remaining.
        // TODO: This latter thing could be alleviated by keeping track of that
        // number incrementally.
        if (!$this->versionNewerThan(7, 20, 0))
        {
            if (!$mhandle->performReturned)
            {
                foreach ($mhandle->handles as $ch)
                {
                    if ($this->handles[$ch]->durationLeft == 0)
                        ++$still_running;
                }
                $mhandle->performReturned = true;
                return CURLM_CALL_MULTI_PERFORM;
            }
            else
            {
                $mhandle->performReturned = false;
            }
        }

        foreach ($mhandle->handles as $ch)
        {
            $handle = $this->handles[$ch];

            if ($handle->durationLeft > 0)
                ++$still_running;
            else if ($handle->durationLeft == 0)
            {
                // This handle is done. Add a message to the multi handle's
                // queue.
                array_push(
                    $mhandle->messageQueue,
                    array(
                        'msg' => CURLMSG_DONE,
                        'result' => $this->getErrorCode($handle),
                        'handle' => $ch
                    )
                );

                // Signify that the response is ready to be fetched if
                // CURLOPT_RETURNTRANSFER was set.
                $handle->durationLeft = -1;

                // On the other hand, if that option was not set, fetch the
                // response immediately and print it to stdout.
                if (!$handle->options[CURLOPT_RETURNTRANSFER])
                {
                    $response = $this->getResponse($handle);
                    if (!is_null($response))
                        echo $response;
                }

                // TODO: Use more of the behavior options like the callbacks
                // WRITEFUNCTION and READFUNCTION.
            }
        }

        return CURLM_OK;
    }

    public function multi_info_read($mh, &$msgs_in_queue = null)
    {
        // PHP emits a warning if $mh is not a valid handle. Should we mimick
        // this behavior?
        if (!isset($this->multiHandles[$mh]))
            return false;

        $message = array_shift($this->multiHandles[$mh]->messageQueue);

        if (is_null($message))
            $message = false;

        $msgs_in_queue = count($this->multiHandles[$mh]->messageQueue);

        return $message;
    }

    public function multi_getcontent($ch)
    {
        // PHP emits a warning if $ch is not a valid handle. Should we mimick
        // this behavior?
        if (!isset($this->handles[$ch]))
            return false;

        $handle = $this->handles[$ch];

        if (!$handle->inMultiHandle ||
            $handle->durationLeft != -1 ||
            !$handle->options[CURLOPT_RETURNTRANSFER])
            return null;

        $response = $this->getResponse($handle);

        // TODO: Is this really what PHP would do?
        if(is_null($response))
            return false;

        return $response;
    }

    public function multi_remove_handle($mh, $ch)
    {
        // PHP emits a warning if either $mh or $ch is not a valid handle.
        // Should we mimick this behavior?
        if (!isset($this->multiHandles[$mh]))
            return null;
        if (!isset($this->handles[$ch]))
            return false;

        if ($key = array_search($ch, $this->multiHandles[$mh]->handles))
        {
            unset($this->multiHandles[$mh]->handles[$key]);
            $this->handle[$ch]->inMultiHandle = false;
            return CURLM_OK;
        }
        else
        {
            // The given handle was not added to the multi handle anyway.
            return CURLM_BAD_EASY_HANDLE;
        }
    }

    public function multi_close($mh)
    {
        if (isset($this->multiHandles[$mh]))
        {
            foreach ($this->multiHandles[$mh]->handles as $ch)
                $this->handles[$ch]->inMultiHandle = false;

            unset($this->multiHandles[$mh]);
        }
    }

    /**
     *  Miscellaneous
     */

    public function version($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }

    /**
     * Stub configuration
     */

    public function setResponse($expectedResponse, $requiredOptions = array())
    {
        $hash = $this->getHashAndSetOptionMaps($requiredOptions);
        $this->mapResponses[$hash] = $expectedResponse;
    }

    public function setErrorCode($expectedErrorCode, $requiredOptions = array())
    {
        $hash = $this->getHashAndSetOptionMaps($requiredOptions);
        $this->mapErrorCodes[$hash] = $expectedErrorCode;
    }

    public function setInformation($expectedInfo, $requiredOptions = array())
    {
        $hash = $this->getHashAndSetOptionMaps($requiredOptions);
        $this->mapInfo[$hash] = $expectedInfo;
    }

    private function getHashAndSetOptionMaps($requiredOptions)
    {
        ksort($requiredOptions);
        $hash = md5(http_build_query($requiredOptions));

        $this->mapOptionCounts[$hash] = count($requiredOptions);
        arsort($this->mapOptionCounts);
        $this->mapOptions[$hash] = $requiredOptions;
        return $hash;
    }

    /**
     * Helper functions
     */

    private function versionNewerThan($major, $minor = 0, $patch = 0)
    {
        $versionInfo = $this->version();

        $versionParts = explode('.', $versionInfo['version']);

        return $major > $versionParts[0]
            || $major == $versionParts[0] && $minor > $versionParts[1]
            || $major == $versionParts[0] && $minor == $versionParts[1] && $patch >= $versionParts[2];
    }

    private function getResponse($handle)
    {
        $response = false;

        $key = $this->determineKey($handle, "mapResponses");

        if($key !== null)
            $response = $this->mapResponses[$key];

        return $response;
    }

    private function getErrorCode($handle)
    {
        $errorCode = 0;

        $key = $this->determineKey($handle, "mapErrorCodes");

        if($key !== null)
            $errorCode = $this->mapErrorCodes[$key];

        return $errorCode;
    }

    private function getInformation($handle, $opt)
    {
        $info = '';

        $key = $this->determineKey($handle, "mapInfo");

        if($key !== null && isset($this->mapInfo[$key][$opt]))
            $info = $this->mapInfo[$key][$opt];

        return $info;
    }

    private function getInfoArray($handle)
    {
        $infoArray = array();

        foreach(self::$infoLookup as $strKey)
        {
            $infoArray[$strKey] = '';
        }

        $handleKey = $this->determineKey($handle, "mapInfo");

        if($handleKey !== null)
        {
            foreach($this->mapInfo[$handleKey] as $intKey => $value)
            {
                $strKey = self::$infoLookup[$intKey];
                $infoArray[$strKey] = $value;
            }
        }

        return $infoArray;
    }

    private function determineKey($handle, $map)
    {
        // TODO: Treat URL option separately, so that order of parameters does not matter
        // TODO: Treat HEADER option as array of individual options
        $returnValue = null;
        $options = $handle->options;
        foreach($this->mapOptionCounts as $hash => $optionCount)
        {
            foreach($this->mapOptions[$hash] as $option => $value)
            {
                if(!isset($options[$option]) || $options[$option] != $value)
                    continue 2;
            }

            // We need this check, because the element in mapOptionCounts might have been created
            // for a different type of output. In this case, we need to continue searching
            if(!isset($this->{$map}[$hash]))
                continue;

            $returnValue = $hash;
            break;
        }
        return $returnValue;
    }

    /**
     * Lookup tables
     */

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
        28 => 'CURLE_OPERATION_TIMEOUTED', // in the original cURL lib this is called 'CURLE_OPERATION_TIMEDOUT' instead
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
        84 => 'CURLE_FTP_PRET_FAILED',
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
