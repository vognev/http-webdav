<?php
require_once "HTTP/HeadersParser.php";

class HTTP_Transport_HeadersParserTest extends PHPUnit_Framework_TestCase
{
    protected $_headers = array(
        'Content-Type: application/xml; charset=utf8',
        'DAV: 1, 2',
        'ALLOW: GET, HEAD, POST, PUT, OPTIONS, PROPFIND, MKCOL, MOVE'
    );

    public function testAbleParseHeaders()
    {
        $rawHeaders     = implode("\r\n", $this->_headers) . "\r\n\r\n";
        $this->_testParserBehaviour($rawHeaders);

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $rawHeaders);

        $this->_testParserBehaviour($fh);
    }

    protected function _testParserBehaviour($headers)
    {
        $parser = new \HTTP\HeadersParser($headers);
        $this->assertEquals(count($parser), count($this->_headers));
        foreach($parser as $k => $v) {
            $this->assertTrue(in_array("$k: $v", $this->_headers));
        }
    }
}