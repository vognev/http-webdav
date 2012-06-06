<?php

namespace HTTP;

class Response extends Container
{
    protected $_responseCode    = 0;

    /**
     * @var \HTTP\Request
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

    public function setRequest(Request $request)
    {
        $this->_request = $request;
    }

    public function getRequest()
    {
        return $this->_request;
    }
}
