<?php
namespace FACTFinder\Util;

/**
 * Implements the cURL interface by simply delegating calls to the built-in cURL functions..
 * See http://www.php.net/manual/en/book.curl.php
 *
 * @codeCoverageIgnore
 * This implementation simply forwards all calls to built-in methods, so there's
 * no need to test it.
 */
class Curl implements CurlInterface
{
    /**
     * Easy interface
     */

    public function close($ch)
    {
        curl_close($ch);
    }

    public function copy_handle($ch)
    {
        return curl_copy_handle($ch);
    }

    public function errno($ch)
    {
        return curl_errno($ch);
    }

    public function error($ch)
    {
        return curl_error($ch);
    }

    public function exec($ch)
    {
        return curl_exec($ch);
    }

    public function getinfo($ch, $opt = 0)
    {
        return curl_getinfo($ch, $opt);
    }

    public function init($url = null)
    {
        return curl_init($url);
    }

    public function setopt_array($ch, $options)
    {
        return curl_setopt_array($ch, $options);
    }

    public function setopt($ch, $option, $value)
    {
        return curl_setopt($ch, $option, $value);
    }

    /**
     * Multi interface
     */

    public function multi_add_handle($mh, $ch)
    {
        return curl_multi_add_handle($mh, $ch);
    }

    public function multi_close($mh)
    {
        curl_multi_close($mh);
    }

    public function multi_exec($mh, &$still_running)
    {
        return curl_multi_exec($mh, $still_running);
    }

    public function multi_getcontent($ch)
    {
        return curl_multi_getcontent($ch);
    }

    public function multi_info_read($mh, &$msgs_in_queue = null)
    {
        return curl_multi_info_read($mh, $msgs_in_queue);
    }

    public function multi_init()
    {
        return curl_multi_init();
    }

    public function multi_remove_handle($mh, $ch)
    {
        return curl_multi_remove_handle($mh, $ch);
    }

    public function multi_select($mh, $timeout = 1.0)
    {
        return curl_multi_select($mh, $timeout);
    }

    /**
     * Miscellaneous
     */

    public function version($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }
}
