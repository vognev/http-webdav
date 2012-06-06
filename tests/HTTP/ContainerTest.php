<?php
require_once 'PHPUnit/Framework/TestCase.php';

class HTTP_ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testHeadersOperations()
    {
        $header     = 'application/octet-stream';
        $container  = new \HTTP\Container();

        $this->assertFalse($container->hasHeader('Content-Type'));

        $container->setHeader('CONTENT-TYPE', 'application/octet-stream');
        $this->assertTrue($container->hasHeader('Content-Type'));
        $this->assertEquals($header, $container->getHeader('Content-Type'));

        $this->assertNull($container->getHeader('x-unknown-header', null));

        $this->assertEquals(
            array('Content-Type: application/octet-stream'),
            $container->getHeaders()
        );
    }

    public function testBodyOperations()
    {
        $stream         = fopen('php://temp', 'r+');
        fwrite($stream,  $content = 'foobar');
        rewind($stream);

        $container      = new \HTTP\Container();

        $container->setBody('');
        $this->assertEquals('', $container->getBodyAsString());

        $container->setBody($stream);
        $this->assertEquals($container->getBody(), $stream);
        $this->assertEquals(fgets($container->getBody()), $content);
        $this->assertEquals($container->getBodyAsString(), $content);

        fclose($stream);
    }
}