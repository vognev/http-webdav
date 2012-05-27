<?php
require_once "HTTP/Transport/Interface.php";
require_once "HTTP/Transport/Exception.php";

abstract class HTTP_Transport_Abstract implements HTTP_Transport_Interface
{
    protected $_options = array();

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function setOptions(array $options = array())
    {
        $this->_options = $options;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param resource|mixed $headers
     * @return array
     */
    protected function _parseHeaders($headers)
    {
        $aHeaders = array();

        if (is_resource($headers)) {
            rewind($headers);
            $headers = stream_get_contents($headers);
        } else {
            $headers = (string) $headers;
        }

        foreach(explode("\r\n", $headers) as $headerRow) {
            if (empty($headerRow)) continue;
            if (false === strpos($headerRow, ':')) continue;
            list($name, $value) = explode(":", $headerRow, 2);
            $aHeaders[$name] = trim($value);
        }

        return $aHeaders;
    }

    /**
     * @static
     * @param string $name Transport name
     * @param array $options
     * @return HTTP_Transport_Abstract
     * @throws HTTP_Transport_Exception
     */
    public static function factory($name, $options = array())
    {
        $class      = 'HTTP_Transport_' . $name;
        $classFile  = 'HTTP/Transport/' . $name . '.php';

        if (!class_exists($class)) {
            require_once $classFile;
        }

        return new $class($options);
    }
}