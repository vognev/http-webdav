<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once "HTTP/URL.php";

class URLTest extends PHPUnit_Framework_TestCase
{
    public function testCanBeCreatedEmpty()
    {
        $url        = new HTTP_URL();

        $this->assertFalse($url->hasPart(HTTP_URL::URL_SCHEME));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_HOST));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_PORT));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_PATH));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_QUERY));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_FRAGMENT));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_USER));
        $this->assertFalse($url->hasPart(HTTP_URL::URL_PASS));
    }

    public function testCanBeCreatedWithInintializer()
    {
        $url        = new HTTP_URL("http://user:password@example.tld/path/to/file?arg1=val1&agr2=val2#fragment");

        $this->assertEquals($url->getPart(HTTP_URL::URL_SCHEME), 'http');

        $this->assertEquals($url->getPart(HTTP_URL::URL_USER), 'user');
        $this->assertEquals($url->getPart(HTTP_URL::URL_PASS), 'password');

        $this->assertEquals($url->getPart(HTTP_URL::URL_PORT, false), false);

        // change path
        $url->setPart(HTTP_URL::URL_PATH, "/path/to/file.ext");
        $this->assertEquals($url->getPart(HTTP_URL::URL_PATH), "/path/to/file.ext");

        // set unknown part
        try {
            $url->setPart("foo", "bar");
            $this->fail("HTTP_URL::setPart does not failed on unknown part");
        } catch(HTTP_URL_Exception $e) {}

        $this->assertEquals(
            $url->getUrl(),
            "http://user:password@example.tld/path/to/file.ext?arg1=val1&agr2=val2#fragment"
        );
    }
}