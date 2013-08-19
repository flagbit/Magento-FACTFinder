<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this class handles the parameters conversion between the client url, the links on the webpage and the url for the
 * server. it can be seen as a parameter factory.
 *
 * @package   FACTFinder\Common
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: ParametersParser.php 25893 2010-06-29 08:19:43Z rb $
 */
class FACTFinder_ParametersParser
{
    private static $w283751Done = false;
    private $requestParams;
    private $requestTarget;

    protected $config;
    protected $encodingHandler;
	
	protected $log;

    /**
     * @param FACTFinder_Abstract_IConfiguration config
     * @param FACTFinder_EncodingHandler $encodingHandler
     */
    public function __construct(FACTFinder_Abstract_Configuration $config, FACTFinder_EncodingHandler $encodingHandler, FACTFinder_Abstract_Logger $log = null)
    {
		if(isset($log))
			$this->log = $log;
		else
			$this->log = FF::getSingleton('nullLogger');
        $this->config = $config;
        $this->encodingHandler = $encodingHandler;
    }

    /**
	 * DEPRECATED, because it also might destroy other components of the system which rely on the standard PHP. For example this
	 * method don't manage array-parameters e.g. "foo[0]=bar" like expected.
	 * This method is not in internal use any more
	 *
     * runs a workaround for php to restore the original parameter names from the url respectively $_SERVER['QUERY_STRING'].
     * this method will only run once and change the global variables $GLOBALS, $_GET and $_REQUEST. parameters which are
     * transformed by php will be left at the $_REQUEST array, the $_GET array will only contain the correct parameters
     *
     * @link http://stackoverflow.com/questions/283751/php-replaces-spaces-with-underlines
	 * @deprecated and not in interal use any more
     * @return void
     */
    final public static function runWorkaround283751()
    {
        if (self::$w283751Done === false && isset($_SERVER['QUERY_STRING'])) {
            $params = self::parseParamsFromString($_SERVER['QUERY_STRING']);
            $_GET = array();
            $_GLOBALS['_GET'] = $_GET;
            foreach($params AS $key => $value){
                $_GET[$key] = $value;
                $_REQUEST[$key] = $value;
                $GLOBALS['_GET'][$key] = $value;
                $GLOBALS['_REQUEST'][$key] = $value;
            }
            self::$w283751Done = true;
        }
    }

    /**
     * loads the parameters from request and returns them as string-to-string array
     * also considers the mapping and ignore rules
     *
     * @return array of params
     */
    public function getRequestParams()
    {
        if ($this->requestParams == null) {
			if (isset($_SERVER['QUERY_STRING'])) {
				$requestParams = array_merge($_POST, self::parseParamsFromString($_SERVER['QUERY_STRING']));
			} else if (isset($_GET)) {
				$requestParams = array_merge($_POST, $_GET); // dont use $_REQUEST, because it also contains $_COOKIE;
			} else {
				// for cli
				$requestParams = array();
			}

			$this->requestParams = $this->encodingHandler->encodeUrlForPage($requestParams);
        }
        return $this->requestParams;
    }

    /**
     * @param array parameters. if null, using the request parameters (default: null)
     * @return FACTFinder_Parameters object
     */
    public function getFactfinderParams(array $params = null)
    {
        if ($params == null) {
            $params = $this->getServerRequestParams();
			$params = $this->encodingHandler->encodeServerUrlForPageUrl($params);
        }

        $filters = array();
        $sortings = array();
        foreach($params AS $key => $value) {
            if (strpos($key, 'filter') === 0) {
                $filters[str_replace('filter', '', $key)] = $value;
            } else
            if (strpos($key, 'sort' && ($value == 'asc' || $value == 'desc')) === 0) {
                $sortings[str_replace('sort', '', $key)] = $value;
            }
        }

        return FF::getInstance('parameters',
            isset($params['query']) ? $params['query'] : '',
            $this->config->getChannel(),
            isset($params['productsPerPage']) ? $params['productsPerPage'] : null,
            isset($params['page']) ? $params['page'] : 1,
            $filters,
            $sortings,
            ((isset($params['catalog']) && $params['catalog'] == 'true') ||
             (isset($params['navigation']) && $params['navigation'] == 'true')),
            isset($params['followSearch']) ? $params['followSearch'] : 10000
        );
    }

    /**
     * @param String parameters
     * @return FACTFinder_Parameters object
     */
    public function getFactfinderParamsFromString($paramString)
    {
        $params = self::parseParamsFromString($paramString);
        return $this->getFactfinderParams($params);
    }

    /**
     * converts the factfinder parameters object into a params array
     *
     * @return array params
     */
    public function parseFactfinderParams(FACTFinder_Parameters $ffparams)
    {
        $filters = array();
        foreach($ffparams->getFilters() AS $key => $value) {
            $filters['filter'.$key] = $value;
        }

        $sortings = array();
        foreach($ffparams->getSortings() AS $key => $value) {
            $sortings['sort'.$key] = $value;
        }

        return array_merge(
            array(
                'query' => $ffparams->getQuery(),
                'channel' => $ffparams->getChannel(),
                'productsPerPage' => $ffparams->getProductsPerPage(),
                'page' => $ffparams->getPage(),
                'followSearch' => $ffparams->getFollowSearch()
            ),
            $filters,
            $sortings
        );
    }

    /**
     * extracts a parameter array with name=>value pairs from an url string.
     * also only url encoding is done but no further encodings.
	 * this method does not handle array variables such like "foo[0]=bar"
     *
     * @param string url
     * @return array of parameter variables
     */
    public static function parseParamsFromString($paramString)
    {
        if (strpos($paramString, '?') !== false) {
            $paramString = substr($paramString, strpos($paramString, '?')+1);
        }
        $paramsArray = array();
        $a_pairs = explode('&', $paramString);
        foreach($a_pairs AS $s_pair){
            $a_pair = explode('=', $s_pair);
            if(empty($a_pair[0])) continue;
			if(count($a_pair) == 1 || strlen($a_pair[1]) == 0) $a_pair[1] = '';

            $a_pair[0] = urldecode($a_pair[0]);
            $a_pair[1] = urldecode($a_pair[1]);

            $paramsArray[$a_pair[0]] = $a_pair[1];
        }
        return $paramsArray;
    }

    /**
     * the FACT-Finder result is UTF-8 encoded, so this method parses a url string from the request and also does
     * utf-decoding if needed
     *
     * @param string from factfinder result
     * @return array of paramter variables
     */
    public function parseParamsFromResultString($paramString)
    {
        $params = self::parseParamsFromString($paramString);
        $params = $this->encodingHandler->encodeServerUrlForPageUrl($params);
        return $params;
    }

    /**
     * get a single value from the request or the default value, if this value does not exist
     *
     * @param parameter name
     * @param default value (default: null)
     * @return request value of parameter $name or $defaultValue if parameter does not exist
     */
    public function getRequestParam($name, $defaultValue = null)
    {
        $params = $this->getRequestParams();
        return isset($params[$name]) ? trim($params[$name]) : $defaultValue;
    }

    /**
     * returns the params array but with the server mappings and removed ignored server parameters . if params array is
     * not set, the request params will be used
     *
     * @param array parameters (optional)
     * @return array parameters without ignored parameters
     */
    public function getServerRequestParams(array $params = null) {
        if ($params == null) {
            $params = $this->getRequestParams();
        }

        $params = $this->doServerMappings($params);
        $params = $this->removeIgnoredParams($params, $this->config->getIgnoredServerParams());
        $params = $this->addRequiredParams($params, $this->config->getRequiredServerParams());
        $params = $this->encodingHandler->encodeForServerUrl($params);

        return $params;
    }

    /**
     * creates the link-url for the webpage (no html code!). see {@link http://de3.php.net/manual/en/function.array-merge.php array_merge}
     * and {@link http://de3.php.net/manual/en/function.http-build-query.php http_build_query} to know, how the two arrays
     * are merged and how the link will be constructed. additionaly this method will remove parameters which are configured
     * to be ignored
     *
     * @param array of parameters
     * @param array (optional) additional parameters which will overwrite the first parameters if a same key is used
     * @param string (optional) string which will be prepended to the link. if none is given, getRequestTarget() is used
     * @return url for a page link
     */
    public function createPageLink(array $params, array $addParams = array(), $target = null)
    {
        if ($target == null) {
            $target = $this->getRequestTarget();
        }

        $linkParams = array_merge($params, $addParams);

        $linkParams = $this->doPageMappings($linkParams);
        $linkParams = $this->removeIgnoredParams($linkParams, $this->config->getIgnoredPageParams());
        $linkParams = $this->addRequiredParams($linkParams, $this->config->getRequiredPageParams());

        return $target.'?'.http_build_query($linkParams, '', '&');
    }

    /**
     * remove the ignored params from the params array if set
     *
     * @param array params
     * @param array ignored params, where the param names are the array-keys
     * @return array new modified params
     */
    private function removeIgnoredParams($params, $ignoredParams)
    {
        $returnParams = array();
        foreach($params as $key => $value) {
            // copy each param and do not set to null, because mappings are stored as references in the params array
            if(!isset($ignoredParams[$key]) && ((is_array($value) && count($value) > 0) || strlen($value) > 0 )) {
                $returnParams[$key] = $value;
            }
        }
        return $returnParams;
    }

    /**
     * adds the params from the required params map to the params array if not already set
     *
     * @param array params
     * @param array required params as string to string map (array-key = paramname; array-value = default param value)
     * @return array new modified params
     */
    private function addRequiredParams($params, $requiredParams)
    {
        $requestParams = $this->getRequestParams();
        foreach($requiredParams AS $paramName => $defaultValue) {
            if (!isset($params[$paramName])) {
                $params[$paramName] = isset($requestParams[$paramName]) ? $requestParams[$paramName] : $defaultValue;
            }
        }
        return $params;
    }

    /**
     * get target of the current request url, from "$_SERVER['REQUEST_URI']".
     *
     * @return string request target
     */
    protected function getRequestTarget()
    {
        if ($this->requestTarget == null) {
			// workaround for some servers (IIS) which do not provide '$_SERVER['REQUEST_URI']'
			if (!isset($_SERVER['REQUEST_URI'])) {
				$arr = explode("/", $_SERVER['PHP_SELF']);
				$_SERVER['REQUEST_URI'] = "/" . $arr[count($arr)-1];
				if (isset($_SERVER['argv'][0]) && $_SERVER['argv'][0]) {
					$_SERVER['REQUEST_URI'] .= "?" . $_SERVER['argv'][0];
				}
			}

			if (strpos($_SERVER['REQUEST_URI'], '?') === false) {
				$this->requestTarget = $_SERVER['REQUEST_URI'];
			} else {
				$this->requestTarget = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
			}
        }
        return $this->requestTarget;
    }

    public function setRequestTarget($target)
    {
        $this->requestTarget = $target;
    }

    /**
     * do mapping for a params array with the page mapping settings from the config. so this method expects server params
     * and return params for the page
     *
     * @param array paramters
     * @return array mapped parameters
     */
    private function doPageMappings(array $params)
    {
        return $this->doMapping($params, $this->config->getPageMappings());
    }

    /**
     * do mapping for a params array with the server mapping settings from the config. so this method expects page params
     * and return params for the server
     *
     * @param array paramters
     * @return array mapped parameters
     */
    private function doServerMappings(array $params)
	{
        return $this->doMapping($params, $this->config->getServerMappings());
    }

    /**
     * maps the keys in the array using the rules. if a "from" parameter does not exist, but the according "to" parameter
     * exist, the "from" will be create - so this mapping normaly works for both directions
     *
     * @param array paramters
     * @param mixed iterable mapping rules
     * @return array mapped parameters
     */
    private function doMapping(array $params, array $mappingRules)
    {
        foreach($mappingRules AS $from => $to) {
            if (isset($params[$from])) { //"from" is more important..
                $params[$to] = &$params[$from];
            } else if (isset($params[$to])) { //but if it does not exist but "to" exists, then "create" from
                $params[$from] = &$params[$to];
            } else { //if none of them exists, just create the params with a null value
                $params[$from] = null;
                $params[$to] = &$params[$from];
            }
        }
        return $params;
    }
}