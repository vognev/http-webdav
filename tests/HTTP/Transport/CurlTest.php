<?php
require_once "HTTP/TransportTest.php";
require_once "HTTP/Transport/Curl.php";

class HTTP_Transport_CurlTest extends HTTP_TransportTest
{
    protected $_transportClass = 'HTTP_Transport_Curl';

    public function setUp()
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped("HTTP_Transport_Curl tests skipped due to lack of cURL extension");
        }
        if (!defined('TEST_TRANSPORT_CURL_BASEURL')) {
            $this->markTestSkipped("HTTP_Transport_Curl dynamic tests are not enabled in options.php");
        }
    }

    /**
     * @expectedException HTTP_Transport_Exception
     */
    public function testCanHandleInvalidUrl()
    {
        /** @var $transport HTTP_Transport_Curl */
        $transport      = new $this->_transportClass();
        $request        = new HTTP_Request('');
        $transport->execute($request);
    }
}