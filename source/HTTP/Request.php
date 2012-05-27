<?php
require_once "HTTP/Request/Exception.php";
require_once "HTTP/Container.php";

require_once "HTTP/URL.php";

class HTTP_Request extends HTTP_Container
{
    /**
     * @var HTTP_URL
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
            $this->_url = new HTTP_URL($url);
        } elseif ($url instanceof HTTP_URL) {
            $this->_url = $url;
        } else {
            throw new HTTP_Request_Exception("Invalid \$url specified");
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