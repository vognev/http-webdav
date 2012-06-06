<?php
require_once 'PHPUnit/Framework/TestCase.php';

class HTTP_ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponseCodeOperations()
    {
        $response = new \HTTP\Response();
        $response->setResponseCode(200);

        $this->assertEquals(200, $response->getResponseCode());
    }

    public function testRequestOperations()
    {
        $response = new \HTTP\Response();
        $this->assertEquals(null, $response->getRequest());

        $response->setRequest($request = new \HTTP\Request('http://example.tld/'));
        $this->assertEquals($request, $response->getRequest());
    }
}
