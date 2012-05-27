<?php

class WebDAV_Propfind extends DOMDocument
{
    /**
     * build allprop request
     */
    const MODE_ALLPROP  = 'ALLPROP';

    /**
     * build named props request
     */
    const MODE_PROP     = 'PROP';

    /**
     * @var DOMElement
     */
    protected $_root;

    /**
     * operation mode of PROPFIND builder
     * @var string
     */
    protected $_mode;

    public function __construct($mode = self::MODE_PROP)
    {
        $this->_mode = $mode;

        parent::__construct('1.0', 'utf8');

        $this->_root = $this->createElement('d:propfind');
        $this->_root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:d',
            'DAV:'
        );

        $this->appendChild($this->_root);

        switch($this->_mode) {
            case self::MODE_ALLPROP:
                $this->_root->appendChild($this->createElement('d:allprop'));
                break;
            case self::MODE_PROP:
                $this->_root->appendChild($this->createElement('d:prop'));
                $this->_root = $this->_root->firstChild;
                break;
            default:
                throw new WebDAV_Exception("Unknown PROPFIND type '{$this->_mode}'");
        }
    }

    public function isAllProp()
    {
        return self::MODE_ALLPROP === $this->_mode;
    }

    public function isProp()
    {
        return self::MODE_PROP === $this->_mode;
    }

    /**
     * @param string|DOMElement $node
     * @param null $ns
     * @param null $nsURI
     * @throws WebDAV_Exception
     */
    public function setProperty($node, $ns = null, $nsURI = null)
    {
        if (self::MODE_PROP !== $this->_mode) {
            throw new WebDAV_Exception("Custom properties can be added only in PROP mode");
        }

        if ($node instanceof DOMElement) {
            $this->_root->appendChild($node);
            return;
        }

        if (null === $ns && false === strpos($node, 'd:')) {
            $node = 'd:' . $node;
        }

        $property = $this->createElement($node);

        if (null !== $ns) {
            $property->setAttributeNS(
                $nsURI, "xmlns:$ns", $ns
            );
        }

        $this->_root->appendChild($property);
    }

    public function __toString()
    {
        return $this->saveXML();
    }
}