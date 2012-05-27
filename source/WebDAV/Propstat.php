<?php
require_once 'WebDAV/Prop.php';

class WebDAV_Propstat
{
    protected $_properties  = array();

    protected $_length      = 0;

    /**
     * @param $idx
     * @return WebDAV_Prop
     */
    public function item($idx)
    {
        return $this->_properties[$idx];
    }

    /**
     * @param WebDAV_Prop|DOMNode $prop
     */
    public function push($prop)
    {
        $this->_properties[] = ($prop instanceof WebDAV_Prop) ? $prop : new WebDAV_Prop($prop);
        $this->_length++;
    }

    /**
     * @param $name
     * @param null|string $namespaceURI
     * @return bool|WebDAV_Prop
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