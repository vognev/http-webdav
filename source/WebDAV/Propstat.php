<?php

namespace WebDAV;

class Propstat
{
    protected $_properties  = array();

    protected $_length      = 0;

    /**
     * @param $idx
     * @return Prop
     */
    public function item($idx)
    {
        return $this->_properties[$idx];
    }

    /**
     * @param Prop|\DOMNode $prop
     */
    public function push($prop)
    {
        $this->_properties[] = ($prop instanceof Prop) ? $prop : new Prop($prop);
        $this->_length++;
    }

    /**
     * @param $name
     * @param null|string $namespaceURI
     * @return bool|Prop
     */
    public function getByName($name, $namespaceURI = null)
    {
        for($i = 0; $i < $this->_length; $i++)
            if ($name == $this->item($i)->getLocalName()) {
                if (null !== $namespaceURI &&
                    $namespaceURI != $this->item($i)->getNamespaceURI())
                    continue;
                return $this->item($i);
            }
        return false;
    }
}