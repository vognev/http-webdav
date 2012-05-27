<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once "HTTP/Client.php";

class HTTP_ClientTest extends PHPUnit_Framework_TestCase
{
    const UA = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/12.0';

    public function testUserAgentOperations()
    {
        $client     = new HTTP_Client();

        $client->setUserAgent(self::UA);
        $this->assertEquals(self::UA, $client->getUserAgent());
    }

    public function testTransportOperations()
    {
        $client     = new HTTP_Client();

        try {
            $client->getTransport();
            $this->fail("HTTP_Client does not throws exceptions when Transport is not set");
        } catch (HTTP_Client_Exception $e) {}

        $transport = $this->getMockForAbstractClass('HTTP_Transport_Abstract');
        $client->setTransport($transport);

        $this->assertEquals($transport, $client->getTransport());
    }

    public function testRequestOperations()
    {
        $client     = new HTTP_Client();
        $transport  = $this->getMockForAbstractClass('HTTP_Transport_Abstract');

        $client->setTransport($transport);

        $request    = $client->createRequest('http://example.tld/');
        $response   = new HTTP_Response();

        $this->assertInstanceOf('HTTP_Request', $request);

        $transport
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($request))
            ->will($this->returnValue($response));

        $client->executeRequest($request);
    }
}