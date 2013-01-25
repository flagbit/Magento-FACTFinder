<?php
/**
 * Implements the cURL interface by simply delegating calls to the built-in cURL functions..
 * See http://www.php.net/manual/en/book.curl.php
 **/
class FACTFinder_Curl
    implements FACTFinder_CurlInterface
{

    public function curl_close($ch)
    {
        curl_close($ch);
    }

    public function curl_copy_handle($ch)
    {
        return curl_copy_handle($ch);
    }

    public function curl_errno($ch)
    {
        return curl_errno($ch);
    }

    public function curl_error($ch)
    {
        return curl_error($ch);
    }

    public function curl_exec($ch)
    {
        return curl_exec($ch);
    }

    public function curl_getinfo($ch, $opt = 0)
    {
        return curl_getinfo($ch, $opt);
    }

    public function curl_init($url = null)
    {
        return curl_init($url);
    }

    public function curl_multi_add_handle($mh, $ch)
    {
        return curl_multi_add_handle($mh, $ch);
    }

    public function curl_multi_close($mh)
    {
        curl_multi_close($mh);
    }

    public function curl_multi_exec($mh, &$still_running)
    {
        return curl_multi_exec($mh, $still_running);
    }

    public function curl_multi_getcontent($ch)
    {
        return curl_multi_getcontent($ch);
    }

    public function curl_multi_info_read($mh, &$msgs_in_queue = null)
    {
        return curl_multi_info_read($mh, $msgs_in_queue);
    }

    public function curl_multi_init()
    {
        return curl_multi_init();
    }

    public function curl_multi_remove_handle($mh, $ch)
    {
        return curl_multi_remove_handle($mh, $ch);
    }

    public function curl_multi_select($mh, $timeout = 1.0)
    {
        return curl_multi_select($mh, $timeout);
    }

    public function curl_setopt_array($ch, $options)
    {
        return curl_setopt_array($ch, $options);
    }

    public function curl_setopt($ch, $option, $value)
    {
        return curl_setopt($ch, $option, $value);
    }

    public function curl_version($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }
}
