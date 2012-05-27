<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once "HTTP/Request.php";

class HTTP_RequestTest extends PHPUnit_Framework_TestCase
{
    public function testURLOperations()
    {
        $sUrl   = 'http://example.tld/';
        $oUrl   = new HTTP_URL($sUrl);

        $request = new HTTP_Request($sUrl);
        $this->assertEquals($sUrl, $request->getUrl()->getUrl());

        $request = new HTTP_Request($oUrl);
        $this->assertEquals($oUrl, $request->getUrl());

        try {
            new HTTP_Request(array());
            $this->fail("HTTP_Request accepts not-allowed URLs");
        } catch (HTTP_Request_Exception $e) {

        }
    }

    public function testMethodOperations()
    {
        $request        = new HTTP_Request('http://example.tld');

        $request->setMethod('post');
        $this->assertEquals('POST', $request->getMethod());
    }
}