<?php
require_once "HTTP/Client/Exception.php";

require_once "HTTP/Transport/Abstract.php";

require_once "HTTP/Request.php";
require_once "HTTP/Response.php";

class HTTP_Client
{
    protected $_userAgent       = 'HTTP_Client';

    /**
     * @var HTTP_Transport_Abstract
     */
    protected $_transport;

    public function __construct($options = array())
    {
        if (isset($options['transport'])) {
            if ($options['transport'] instanceof HTTP_Transport_Abstract) {
                $this->setTransport($options['transport']);
            } elseif (is_array($options['transport'])) {
                $this->setTransport(
                    HTTP_Transport_Abstract::factory(
                        $options['transport']['class'],
                        $options['transport']['options']
                    )
                );
            }
        }
    }

    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    public function setUserAgent($userAgent)
    {
        $this->_userAgent   = $userAgent;
    }

    public function setTransport(HTTP_Transport_Abstract $transport)
    {
        $this->_transport = $transport;
    }

    public function getTransport()
    {
        if (null === $this->_transport) {
            throw new HTTP_Client_Exception("Before using client you should set transport");
        }
        return $this->_transport;
    }

    public function createRequest($url)
    {
        $request = new HTTP_Request($url);
        $request->setHeader('User-Agent', $this->getUserAgent());
        return $request;
    }

    public function executeRequest(HTTP_Request $request)
    {
        $response = $this->getTransport()->execute($request);
        $response->setRequest($request);
        return $response;
    }
}
