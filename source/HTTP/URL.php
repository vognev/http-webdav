<?php

require_once 'HTTP/URL/Exception.php';

class HTTP_URL
{
    protected $_parts   = array();

    const URL_SCHEME    = 'scheme';
    const URL_HOST      = 'host';
    const URL_PORT      = 'port';
    const URL_USER      = 'user';
    const URL_PASS      = 'pass';
    const URL_PATH      = 'path';
    const URL_QUERY     = 'query';
    const URL_FRAGMENT  = 'fragment';

    protected static $_validParts = array(
        self::URL_SCHEME,
        self::URL_HOST,
        self::URL_PORT,
        self::URL_USER,
        self::URL_PASS,
        self::URL_PATH,
        self::URL_QUERY,
        self::URL_FRAGMENT
    );

    public function __construct($url = null)
    {
        if (null !== $url) {
            $this->setUrl($url);
        }
    }

    public function setUrl($url)
    {
        $this->_parts = parse_url((string) $url);
    }

    public function getUrl()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return $this->getPart(self::URL_SCHEME, '') . '://'
            . $this->getPart(self::URL_USER, '') . ($this->hasPart(self::URL_PASS) ? ':' : '')
            . $this->getPart(self::URL_PASS, '') . ($this->hasPart(self::URL_USER) ? '@' : '')
            . $this->getPart(self::URL_HOST) . (!$this->hasPart(self::URL_PORT) ? '' : ':' . $this->getPart(self::URL_PORT))
            . $this->getPart(self::URL_PATH, '/')
            . ($this->hasPart(self::URL_QUERY) ? '?' . $this->getPart(self::URL_QUERY) : '')
            . ($this->hasPart(self::URL_FRAGMENT) ? '#' . $this->getPart(self::URL_FRAGMENT) : '');
    }

    public function getPart($name, $default = null)
    {
        if (array_key_exists($name, $this->_parts)) {
            return $this->_parts[$name];
        }
        return $default;
    }

    public function setPart($name, $value)
    {
        if (false === array_search($name, self::$_validParts)) {
            throw new HTTP_URL_Exception("Invalid part '$name' specified");
        }
        $this->_parts[$name] = $value;
        return $this;
    }

    public function hasPart($name)
    {
        return array_key_exists($name, $this->_parts);
    }
}