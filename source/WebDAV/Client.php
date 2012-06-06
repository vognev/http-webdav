<?php

namespace WebDAV;

class Client extends \HTTP\Client
{
    protected $_userAgent   = 'WebDAV\\Client';

    protected $_contentType = 'application/octet-stream';

    public function __construct($options = array())
    {
        if (isset($options['transport'])) {
            if ($options['transport'] instanceof \HTTP\Transport) {
                $this->setTransport($options['transport']);
            } elseif (is_array($options['transport'])) {
                $this->setTransport(
                    \HTTP\Transport::factory(
                        $options['transport']['class'],
                        $options['transport']['options']
                    )
                );
            }
        }
    }

    /**
     * @param string $url
     * @param Propfind $propfind
     * @param string $depth
     * @return \HTTP\Response
     */
    public function propfind($url, Propfind $propfind, $depth = '0')
    {
        $url = new \HTTP\URL($url);

        if (!$this->_getOptions($url, $levels, $options) ||
            !in_array('1', $levels) ||
            !in_array('PROPFIND', $options)) {
            return false;
        }

        $request = $this->createRequest($url);
        $request->setMethod('PROPFIND');
        $request->setHeader('Depth', $depth);
        $request->setHeader('Content-Type', 'text/xml');
        $request->setBody($propfind);

        return $this->executeRequest($request);
    }

    public function get(\HTTP\URL $url)
    {
        if (!$this->_getOptions($url, $levels, $options) ||
            !in_array('1', $levels) ||
            !in_array('GET', $options)) {
            return false;
        }
        $request = $this->createRequest($url);
        $request->setMethod('GET');
        return $this->executeRequest($request);
    }

    public function put(\HTTP\URL $url, $body)
    {
        if (!$this->_getOptions($url, $levels, $options) ||
            !in_array('1', $levels) ||
            !in_array('PUT', $options)) {
            return false;
        }
        $request = $this->createRequest($url);
        $request->setMethod('PUT');
        if (is_resource($body)) {
            $request->setBody($body);
        } else {
            $body = (string) $body;
            $request->setBody($body);
        }

        $response = $this->executeRequest($request);

        switch ($response->getResponseCode()) {
            case 200:
            case 201:
            case 204:
                return true;
            default:
                return false;
        }
    }

    public function mkcol(\HTTP\URL $url)
    {
        if (!$this->_getOptions($url, $levels, $options) ||
            !in_array('1', $levels) ||
            !in_array('MKCOL', $options)) {
            return false;
        }

        $request = $this->createRequest($url);
        $request->setMethod('MKCOL');
        $response = $this->executeRequest($request);

        return $response->getResponseCode() == 201;
    }

    public function rename(\HTTP\URL $old, \HTTP\URL $new)
    {
        if (!$this->_getOptions($old, $levels, $options) ||
            !in_array('1', $levels) ||
            !in_array('MOVE', $options)) {
            return false;
        }

        $request = $this->createRequest($old);
        $request->setMethod('MOVE');
        $request->setHeader('Destination', $new->__toString());
        $response = $this->executeRequest($request);

        switch ($response->getResponseCode()) {
            case 201:
            case 204:
                return true;
            default:
                return false;
        }
    }

    public function delete(\HTTP\URL $url)
    {
        if (!$this->_getOptions($url, $levels, $options) ||
            !in_array('1', $levels) ||
            !in_array('DELETE', $options)) {
            return false;
        }

        $request = $this->createRequest($url);
        $request->setMethod('DELETE');
        $response = $this->executeRequest($request);

        return 204 === $response->getResponseCode();
    }

    public function lock(\HTTP\URL $url, $mode)
    {

    }

    public function createRequest($url)
    {
        $request = new \HTTP\Request($url);
        $request->setHeader('Content-Type', $this->_contentType);
        $request->setHeader('User-Agent',   $this->_userAgent);
        return $request;
    }

    /**
     * @param \HTTP\URL $url
     * @param array|null &$levels
     * @param array|null &$options
     * @return bool
     */
    protected function _getOptions(\HTTP\URL $url, &$levels = null, &$options = null)
    {
        $request    = $this->createRequest($url);

        $request->setMethod('OPTIONS');
        $response = $this->getTransport()->execute($request);

        if (200 !== $response->getResponseCode())  {
            return false;
        }

        if (!$response->hasHeader('DAV')) {
            return false;
        }

        $levels = &array_map('trim', explode(',', $response->getHeader('DAV'))) or array();

        if (!$response->hasHeader('Allow')) {
            return false;
        }

        $options = &array_map('trim', explode(',', $response->getHeader('Allow'))) or array();

        return true;
    }
}