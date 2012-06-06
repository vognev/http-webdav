<?php

require_once 'PHPUnit/Framework/TestCase.php';

class HTTP_RequestTest extends PHPUnit_Framework_TestCase
{
    public function testURLOperations()
    {
        $sUrl   = 'http://example.tld/';
        $oUrl   = new \HTTP\URL($sUrl);

        $request = new \HTTP\Request($sUrl);
        $this->assertEquals($sUrl, $request->getUrl()->getUrl());

        $request = new \HTTP\Request($oUrl);
        $this->assertEquals($oUrl, $request->getUrl());

        try {
            new \HTTP\Request(array());
            $this->fail("Request accepts not-allowed URLs");
        } catch (\HTTP\Request\Exception $e) {

        }
    }

    public function testMethodOperations()
    {
        $request        = new \HTTP\Request('http://example.tld');

        $request->setMethod('post');
        $this->assertEquals('POST', $request->getMethod());
    }
}