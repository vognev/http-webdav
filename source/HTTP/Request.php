<?php

namespace HTTP;

class Request extends Container
{
    /**
     * @var URL
     */
    protected $_url;

    protected $_method  = 'GET';

    public function __construct($url)
    {
        $this->setUrl($url);
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUrl($url)
    {
        if (is_string($url)) {
            $this->_url = new URL($url);
        } elseif ($url instanceof URL) {
            $this->_url = $url;
        } else {
            throw new Request\Exception("Invalid \$url specified");
        }
    }

    public function setMethod($method)
    {
        $this->_method = strtoupper((string) $method);
    }

    public function getMethod()
    {
        return $this->_method;
    }
}