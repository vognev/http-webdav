<?php

namespace HTTP;

class HeadersParser implements \Iterator, \Countable
{
    protected $_headers = array();

    protected $_key;

    /**
     * @param resource|mixed $headers
     */
    public function __construct($headers)
    {
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
            $this->_headers[$name] = trim($value);
        }
    }

    public function count()
    {
        return count($this->_headers);
    }

    public function rewind()
    {
        reset($this->_headers);
    }

    public function current()
    {
        return current($this->_headers);
    }

    public function key()
    {
        return key($this->_headers);
    }

    public function next()
    {
        return next($this->_headers);
    }

    public function valid()
    {
        return null !== key($this->_headers);
    }
}