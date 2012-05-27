<?php
require_once 'HTTP/Client.php';
require_once 'WebDAV/Client/Exception.php';

require_once 'WebDAV/Propfind.php';
require_once 'WebDAV/Multistatus.php';

class WebDAV_Client extends HTTP_Client
{
    protected $_userAgent   = 'WebDAV_Client';

    protected $_contentType = 'application/octet-stream';

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

    /**
     * @param string $url
     * @param WebDAV_Propfind $propfind
     * @param string $depth
     * @return HTTP_Response
     */
    public function propfind($url, WebDAV_Propfind $propfind, $depth = '0')
    {
        $url = new HTTP_URL($url);

        if (!in_array('PROPFIND', (array) $this->_getOptions($url))) {
            return false;
        }

        $request = $this->createRequest($url);
        $request->setMethod('PROPFIND');
        $request->setHeader('Depth', $depth);
        $request->setHeader('Content-Type', 'text/xml');
        $request->setBody($propfind);

        return $this->executeRequest($request);
    }

    public function get(HTTP_URL $url)
    {
        if (!in_array('GET', (array) $this->_getOptions($url))) {
            return false;
        }
        $request = $this->createRequest($url);
        $request->setMethod('GET');
        return $this->executeRequest($request);
    }

    public function put(HTTP_URL $url, $body)
    {
        if (!in_array('PUT', (array) $this->_getOptions($url))) {
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

    public function mkcol(HTTP_URL $url)
    {
        if (!in_array('MKCOL', (array) $this->_getOptions($url))) {
            return false;
        }

        $request = $this->createRequest($url);
        $request->setMethod('MKCOL');
        $response = $this->executeRequest($request);

        return $response->getResponseCode() == 201;
    }

    public function rename(HTTP_URL $old, HTTP_URL $new)
    {
        if (!in_array('MOVE', (array) $this->_getOptions($old))) {
            return false;
        }

        $request = $this->createRequest($old);
        $request->setMethod('MOVE');
        $request->setHeader('Destination', $new->__toString());
        $response = $this->executeRequest($request);

        var_dump($response->getBodyAsString());

        switch ($response->getResponseCode()) {
            case 201:
            case 204:
                return true;
            default:
                return false;
        }
    }

    public function delete($url)
    {
        $url = new HTTP_URL($url);

        if (!in_array('DELETE', (array) $this->_getOptions($url))) {
            return false;
        }

        // todo: lock token

        $request = $this->createRequest($url);
        $request->setMethod('DELETE');
        $response = $this->executeRequest($request);

        return 204 === $response->getResponseCode();
    }

    public function createRequest($url)
    {
        $request = new HTTP_Request($url);
        $request->setHeader('Content-Type', $this->_contentType);
        $request->setHeader('User-Agent',   $this->_userAgent);
        return $request;
    }

    /**
     * @param HTTP_URL $url
     * @return array|bool
     */
    protected function _getOptions(HTTP_URL $url)
    {
        $request    = $this->createRequest($url);

        $request->setMethod('OPTIONS');
        $response = $this->getTransport()->execute($request);

        if (200 !== $response->getResponseCode())  {
            return false;
        }

        if ($response->hasHeader('DAV')) {
            $levels = array_map('trim', explode(',', $response->getHeader('DAV'))) or array();
            if (false === array_search("1", $levels)) {
                return false;
            }
        }

        $options = array_map('trim', explode(',', $response->getHeader('Allow'))) or array();

        return $options;
    }
}