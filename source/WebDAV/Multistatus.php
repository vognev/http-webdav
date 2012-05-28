<?php
require_once 'WebDAV/Propstat.php';

class WebDAV_Multistatus extends DOMDocument
{
    protected $_xpath;

    public function __construct($response)
    {
        $this->loadXML($response);
        $this->_xpath = new DOMXPath($this);
    }

    public function getPropstats()
    {
        $propstat = array(
            // link => propstat
        );

        $hrefs = $this->_xpath->query(
            '//*[local-name() = "multistatus"]'
                . '//*[local-name() = "response"]'
                . '//*[local-name() = "href"]'
        );
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href               = $hrefs->item($i)->nodeValue;
            $propstat[$href]    = $this->getHrefPropstat($href);
        }

        return $propstat;
    }

    public function getHrefPropstat($href)
    {
        $propstat = new WebDAV_Propstat();

        $response = $this->getResponseByHref($href);

        if (!$response) {
            return false;
        }

        $propstats = $this->_xpath->query('./*[local-name() = "propstat"]', $response);

        for($i = 0; $i < $propstats->length; $i++) {

            $status = $this->_xpath->query('./*[local-name() = "status"]', $propstats->item($i));
            if (1 != $status->length) {
                throw new WebDAV_Exception("propstat for href '$href' does not contains 'status' element");
            }

            if (!preg_match('#^HTTP/1\.(?:0|1) (\d+) \w+#i', $status->item(0)->nodeValue, $matches)) {
                throw new WebDAV_Exception("'status' element for url '$href' contains invalid HTTP response code");
            }

            $code = intval($matches[1]);

            // we collect only the succesful props
            if (200 !== $code) continue;

            $props = $this->_xpath->query('./*[local-name() = "prop"]/*', $propstats->item($i));
            for($j = 0; $j < $props->length; $j++)
                $propstat->push($props->item($j));
        }

        return $propstat;
    }

    public function getResponseByHref($href)
    {
        $response = $this->_xpath->query(
            '//*[local-name() = "href" and . = "' . $href . '"]'
                . '/ ..'
        );
        return $response->length ? $response->item(0) : null;
    }
}