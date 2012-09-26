<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Http
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * assembles URLs for different kinds of authentication from params and config
 *
 * @author    Martin Buettner <martin.buettner@omikron.net>
 * @version   $Id: UrlBuilder.php 2012-09-17 16:19:43Z mb $
 * @package   FACTFinder\Http
 *
 **/
class FACTFinder_Http_UrlBuilder
{
    protected $params = array();
    protected $config = array();
    protected $action;

    protected $log;


    public function __construct(array $params = null, FACTFinder_Abstract_Configuration $config = null,
                                FACTFinder_Abstract_Logger $log = null)
    {
        if(isset($log))
            $this->log = $log;
        else
            $this->log = FF::getSingleton('nullLogger');
        $this->log->info("Initializing URL Builder.");
        if ($params != null) $this->params = $params;
        if ($config != null) $this->config = $config;
    }

    /**
     * sets factfinder params object
     *
     * @param array params
     * @return void
     **/
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * set single param
     *
     * @param string name
     * @param string value
     * @return void
     **/
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * unset single param
     *
     * @param string name
     * @return void
     **/
    public function unsetParam($name)
    {
        unset($this->params[$name]);
    }

    /**
     * set single param with multiple values
     *
     * @param string name
     * @param array of strings values
     * @return void
     **/
    public function setArrayParam($name, $values)
    {
        $this->params[$name] = $values;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     **/
    public function getParams()
    {
        return $this->params;
    }

    /**
     * get url with advanced authentication encryption
     *
     * @return string url
     */
    public function getAdvancedAuthenticationUrl() {
        $config = $this->config;
        $params = $this->params;

        $channel = $this->getChannel($params, $config);
        if ($channel != '') {
            $params['channel'] = $channel;
        }

        $ts         = time() . '000'; //milliseconds needed
        $prefix     = $config->getAdvancedAuthPrefix();
        $postfix    = $config->getAdvancedAuthPostfix();
        $authParams = "timestamp=$ts&username=".$config->getAuthUser()
            . '&password=' . md5($prefix . $ts . md5($config->getAuthPasswort()) . $postfix);

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/'.$this->action.'?' . http_build_query($params, '', '&')
            . (count($params)?'&':'') . $authParams;

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with simple authentication encryption
     *
     * @return string url
     */
    public function getSimpleAuthenticationUrl() {
        $config = $this->config;
        $params = $this->params;

        $channel = $this->getChannel($params, $config);
        if ($channel != '') {
            $params['channel'] = $channel;
        }

        $ts = time() . '000'; //milliseconds needed but won't be considered
        $authParams = "timestamp=$ts&username=".$config->getAuthUser()
            . '&password=' . md5($config->getAuthPasswort());

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/'.$this->action.'?' . http_build_query($params, '', '&')
            . (count($params)?'&':'') . $authParams;

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with http authentication
     *
     * @return string url
     */
    public function getHttpAuthenticationUrl() {
        $config = $this->config;
        $params = $this->params;

        $channel = $this->getChannel($params, $config);
        if ($channel != '') {
            $params['channel'] = $channel;
        }

        $auth = $config->getAuthUser() . ':' . $config->getAuthPasswort() . '@';
        if ($auth == ':@') $auth = '';

        $url = $config->getRequestProtocol() . '://' . $auth
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/' . $this->action . (count($params)?'?':'')
            . http_build_query($params, '', '&');

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with no authentication.
     *
     * @return string url
     */
    public function getNonAuthenticationUrl() {
        $config = $this->config;
        $params = $this->params;

        $channel = $this->getChannel($params, $config);
        if ($channel != '') {
            $params['channel'] = $channel;
        }

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/' . $this->action . (count($params)?'?':'')
            . http_build_query($params, '', '&');

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        // Include the following line only for debugging purposes
        // This method is called quite often for several checking tasks
        //$this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get channel from params or config (params override config)
     *
     * @param array $params
     * @param FACTFinder_Abstract_Configuration $config
     * @return string channel
     */
    protected function getChannel($params, $config) {
        $channel = '';
        if (isset($params['channel']) && strlen($params['channel']) > 0) {
            $channel = $params['channel'];
        } else if($config->getChannel() != '') {
            $channel = $config->getChannel();
        }
        return $channel;
    }
}
