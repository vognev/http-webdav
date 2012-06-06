<?php

namespace HTTP;

class Container
{
    protected $_headers = array();

    /**
     * @var resource
     */
    protected $_body;

    public function setHeader($name, $value)
    {
        $name = $this->_normalizeHeaderName($name);
        $this->_headers[$name] = $value;
    }

    public function getHeader($name, $default = null)
    {
        $name = $this->_normalizeHeaderName($name);
        if (array_key_exists($name, $this->_headers)) {
            return $this->_headers[$name];
        }
        return $default;
    }

    public function hasHeader($name)
    {
        $name = $this->_normalizeHeaderName($name);
        return array_key_exists($name, $this->_headers);
    }

    public function getHeaders()
    {
        $headers = array();
        foreach($this->_headers as $k => $v)
            $headers[] = "$k: $v";
        return $headers;
    }

    protected function _normalizeHeaderName($name)
    {
        return implode('-', array_map('ucfirst', explode('-', strtolower($name))));
    }

    public function setBody($body)
    {
        $this->_body = is_resource($body) ? $body : (string) $body;
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function getBodyAsString()
    {
        if (is_resource($this->_body)) {
            rewind($this->_body);
            return stream_get_contents($this->_body);
        } else {
            return (string) $this->_body;
        }
    }
}