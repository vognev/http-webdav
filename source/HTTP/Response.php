<?php
require_once "HTTP/Response/Exception.php";
require_once "HTTP/Container.php";
require_once "HTTP/Request.php";

class HTTP_Response extends HTTP_Container
{
    protected $_responseCode    = 0;

    /**
     * @var HTTP_Request
     */
    protected $_request;

    public function setResponseCode($code)
    {
        $this->_responseCode = $code;
    }

    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    public function setRequest(HTTP_Request $request)
    {
        $this->_request = $request;
    }

    public function getRequest()
    {
        return $this->_request;
    }
}
