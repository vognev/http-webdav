<?php

namespace HTTP;

abstract class Transport implements TransportInterface
{
    protected $_options = array();

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function setOptions(array $options = array())
    {
        $this->_options = $options;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @static
     * @param string $name Transport name
     * @param array $options
     * @return Transport
     * @throws Transport\Exception
     */
    public static function factory($name, $options = array())
    {
        $class      = 'HTTP\\Transport\\' . $name;
        return new $class($options);
    }
}