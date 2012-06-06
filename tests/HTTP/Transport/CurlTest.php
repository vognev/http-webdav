<?php
require_once "HTTP/TransportTest.php";
require_once "HTTP/Transport/Curl.php";

class HTTP_Transport_CurlTest extends HTTP_TransportTest
{
    protected $_transportClass = 'HTTP\Transport\Curl';

    protected $_body;

    public function setUp()
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped("Transport_Curl tests skipped due to lack of cURL extension");
        }
        if (!defined('TEST_TRANSPORT_CURL_BASEURL')) {
            $this->markTestSkipped("Transport_Curl dynamic tests are not enabled in options.php");
        }

        $this->_body = http_build_query(array('foo' => 'bar', 'baz' => array('foo' => 'bar')));
    }

    /**
     * @expectedException \HTTP\Transport\Exception
     */
    public function testCanHandleInvalidUrl()
    {
        /** @var $transport HTTP\Transport\Curl */
        $transport      = new $this->_transportClass();
        $request        = new \HTTP\Request('');
        $transport->execute($request);
    }

    public function testHead()
    {
        $request = new \HTTP\Request(rtrim(TEST_TRANSPORT_CURL_BASEURL, '/') . '/handler.php');
        $request->setMethod('HEAD');
        $this->execute($request);
    }

    public function testPut()
    {
        $request = new \HTTP\Request(rtrim(TEST_TRANSPORT_CURL_BASEURL, '/') . '/handler.php');
        $request->setMethod('PUT');
        $this->execute($request);
    }

    public function testPOST()
    {
        $request = new \HTTP\Request(rtrim(TEST_TRANSPORT_CURL_BASEURL, '/') . '/handler.php');
        $request->setMethod('POST');
        $request->setBody($this->_body);
        $this->execute($request);

        $fh = fopen('php://temp', 'r+'); fwrite($fh, $this->_body);
        $request->setBody($fh);
        $this->execute($request);
    }

    protected function execute(HTTP\Request $request)
    {
        /** @var $transport HTTP\Transport\Curl */
        $transport      = new $this->_transportClass;
        $response       = $transport->execute($request);
        $this->assertEquals(200, $response->getResponseCode());
    }
}