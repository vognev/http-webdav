<?php

namespace HTTP;

class Client
{
    protected $_userAgent       = 'HTTP\Client';

    /**
     * @var \HTTP\Transport
     */
    protected $_transport;

    public function __construct($options = array())
    {
        if (isset($options['transport'])) {
            if ($options['transport'] instanceof Transport) {
                $this->setTransport($options['transport']);
            } elseif (is_array($options['transport'])) {
                $this->setTransport(
                    Transport::factory(
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

    public function setTransport(Transport $transport)
    {
        $this->_transport = $transport;
    }

    public function getTransport()
    {
        if (null === $this->_transport) {
            throw new Client\Exception("Before using client you should set transport");
        }
        return $this->_transport;
    }

    public function createRequest($url)
    {
        $request = new Request($url);
        $request->setHeader('User-Agent', $this->getUserAgent());
        return $request;
    }

    public function executeRequest(Request $request)
    {
        $response = $this->getTransport()->execute($request);
        $response->setRequest($request);
        return $response;
    }
}
