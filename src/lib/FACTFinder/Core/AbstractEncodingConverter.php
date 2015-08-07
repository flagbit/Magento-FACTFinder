<?php
namespace FACTFinder\Core;

use FACTFinder\Loader as FF;

/**
 * Takes care of differences in encoding between different participants of the
 * communication. Internal to the library all strings are encoded as UTF-8, so
 * the methods of this class are only for converting to and from UTF-8. The
 * source and target encodings are determined by the configuration.
 * This abstract class does not specify how the actual conversion of a single
 * string is done. Create a subclass to implement the conversion method.
 * Also note that none of these methods handle URL en- or decoding but only deal
 * with plain character encodings.
 */
abstract class AbstractEncodingConverter
{
    const LIBRARY_ENCODING = 'UTF-8';

    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $pageContentEncoding;
    /**
     * @var string
     */
    protected $clientUrlEncoding;

    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *                            implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration Configuration object to use.
     */
    function __construct(
        $loggerClass,
        ConfigurationInterface $configuration
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->pageContentEncoding = $configuration->getPageContentEncoding();
        $this->clientUrlEncoding = $configuration->getClientUrlEncoding();
    }

    abstract protected function convertString($inCharset, $outCharset, $string);

    /**
     * Converts data from $inCharset to $outCharset.
     * @param mixed $data If a string is given, it's encoding will be converted.
     *        If an associative array is given, keys and values will be
     *        converted recursively. All other data types will be returned
     *        unchanged.
     * @return mixed
     */
    protected function convert($inCharset, $outCharset, $data)
    {
        if (FF::isInstanceOf($data, 'Util\Parameters'))
        {
            $result = FF::getInstance(
                'Util\Parameters',
                $this->convert($inCharset, $outCharset, $data->getArray())
            );
        }
        else if (is_array($data))
        {
            $result = array();
            foreach ($data as $k => $v)
            {
                $k = $this->convert($inCharset, $outCharset, $k);
                $result[$k] = $this->convert($inCharset, $outCharset, $v);
            }
        }
        else if (is_string($data))
        {
            $result = $this->convertString($inCharset, $outCharset, $data);
        }
        else
        {
            $result = $data;
        }

        return $result;
    }

    /**
     * Converts data held by the library for use on the rendered page.
     * Hence, it converts from the library's encoding (UTF-8) to the configured
     * page content encoding.
     * @param mixed $data Could either be a string or an associative array.
     * @return mixed
     */
    public function encodeContentForPage($data)
    {
        return $this->convert(
            self::LIBRARY_ENCODING,
            $this->pageContentEncoding,
            $data
        );
    }

    /**
     * Converts data obtained from the client URL for use within the library.
     * Hence, it converts from the configured client URL encoding to the
     * library's encoding (UTF-8).
     * @param mixed $data Data obtained from the client URL. Note that this
     *        data should already be URL decoded. Could either be a string or an
     *        associative array.
     * @return mixed
     */
    public function decodeClientUrlData($data)
    {
        return $this->convert(
            $this->clientUrlEncoding,
            self::LIBRARY_ENCODING,
            $data
        );
    }

    /**
     * Converts data held by the library for use in a client URL.
     * Hence, it converts from the configured client URL encoding to the
     * library's encoding (UTF-8).
     * @param mixed $data Data to be used in the client URL. Note that this
     *        data should not yet be URL encoded. Could either be a string or an
     *        associative array.
     * @return mixed
     */
    public function encodeClientUrlData($data)
    {
        return $this->convert(
            self::LIBRARY_ENCODING,
            $this->clientUrlEncoding,
            $data
        );
    }
}
