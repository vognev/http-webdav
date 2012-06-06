<?php
require_once "HTTP/Transport/AbstractTransport.php";
require_once 'PHPUnit/Framework/TestCase.php';

class HTTP_ClientTest extends PHPUnit_Framework_TestCase
{
    const UA = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/12.0';

    public function testCreation()
    {
        $transport          = new \HTTP\Transport\AbstractTransport();

        $client = new HTTP\Client(array(
            'transport'     => $transport
        ));

        $this->assertEquals($client->getTransport(), $transport);

        $client = new HTTP\Client(array(
            'transport'     => array(
                'class'     => 'AbstractTransport',
                'options'   => array()
            )
        ));

        $this->assertInstanceOf('\HTTP\Transport\AbstractTransport', $client->getTransport());

        $client = new HTTP\Client();
        try {
            $client->getTransport();
            $this->fail("Client without transport does not throws exception");
        } catch (HTTP\Client\Exception $e) {}
    }

    public function testUserAgentOperations()
    {
        $client     = new \HTTP\Client();

        $client->setUserAgent(self::UA);
        $this->assertEquals(self::UA, $client->getUserAgent());
    }

    public function testRequestOperations()
    {
        $client     = new \HTTP\Client();
        $transport  = $this->getMockForAbstractClass('\HTTP\Transport');

        $client->setTransport($transport);

        $request    = $client->createRequest('http://example.tld/');
        $response   = new \HTTP\Response();

        $this->assertInstanceOf('\HTTP\Request', $request);

        $transport
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($request))
            ->will($this->returnValue($response));

        $client->executeRequest($request);
    }
}