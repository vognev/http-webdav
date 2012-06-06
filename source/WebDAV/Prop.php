<?php

namespace WebDAV;

class Prop
{
    /** @var \DOMNode */
    protected $_element;

    public function __construct(\DOMNode $element)
    {
        $this->_element     = $element;
    }

    public function getLocalName()
    {
        return $this->_element->localName;
    }

    public function getName()
    {
        return $this->_element->nodeName;
    }

    public function getValue()
    {
        //todo: what if child nodes exist
        return $this->_element->nodeValue;
    }

    public function getNamespaceURI()
    {
        return $this->_element->namespaceURI;
    }

    public function getDomElement()
    {
        return $this->_element;
    }
}